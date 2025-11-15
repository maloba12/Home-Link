// HomeLink - Smart Recommendation System
// This file implements a JavaScript-based property recommendation engine

class SmartRecommendation {
    constructor() {
        this.searchHistory = [];
        this.userPreferences = {
            priceRange: [],
            locations: [],
            propertyTypes: []
        };
    }
    
    // Store search history
    recordSearch(searchData) {
        const searchRecord = {
            timestamp: new Date(),
            query: searchData.search || '',
            city: searchData.city || '',
            minPrice: searchData.min_price || 0,
            maxPrice: searchData.max_price || Infinity,
            type: searchData.type || '',
            propertyType: searchData.property_type || ''
        };
        
        this.searchHistory.push(searchRecord);
        
        // Update preferences based on search history
        this.updatePreferences(searchRecord);
        
        // Show recommendations if user is on home page
        if (window.location.pathname === '/' || window.location.pathname === '/index.php') {
            this.loadRecommendations();
        }
    }
    
    // Update user preferences based on search patterns
    updatePreferences(search) {
        // Track price preferences
        if (search.minPrice > 0) {
            this.userPreferences.priceRange.push(search.minPrice);
        }
        if (search.maxPrice < Infinity) {
            this.userPreferences.priceRange.push(search.maxPrice);
        }
        
        // Track location preferences
        if (search.city) {
            this.userPreferences.locations.push(search.city);
        }
        
        // Track property type preferences
        if (search.propertyType) {
            this.userPreferences.propertyTypes.push(search.propertyType);
        }
    }
    
    // Calculate recommendation score for a property
    calculateScore(property, recentSearches) {
        let score = 0;
        
        if (recentSearches.length === 0) {
            return 0;
        }
        
        // Average of last 5 searches
        const recentFiveSearches = recentSearches.slice(-5);
        
        recentFiveSearches.forEach(search => {
            // Location matching (30 points)
            if (search.city && property.city.toLowerCase().includes(search.city.toLowerCase())) {
                score += 30;
            }
            
            // Price range matching (25 points)
            if (search.maxPrice >= property.price && search.minPrice <= property.price) {
                score += 25;
            } else if (search.minPrice > 0 && property.price <= search.minPrice * 1.2) {
                score += 15; // Close to budget
            }
            
            // Property type matching (20 points)
            if (search.propertyType && property.property_type === search.propertyType) {
                score += 20;
            }
            
            // Type matching (rent/sale) (15 points)
            if (search.type && property.type === search.type) {
                score += 15;
            }
            
            // Keyword matching in title/description (10 points)
            if (search.query) {
                const query = search.query.toLowerCase();
                const title = property.title.toLowerCase();
                const city = property.city.toLowerCase();
                
                if (title.includes(query) || city.includes(query)) {
                    score += 10;
                }
            }
        });
        
        // Normalize score (max would be 100 * number of searches)
        score = score / recentFiveSearches.length;
        
        return score;
    }
    
    // Load and display recommendations
    async loadRecommendations() {
        const recentSearches = this.searchHistory.slice(-10); // Get last 10 searches
        
        if (recentSearches.length === 0) {
            return; // No search history
        }
        
        try {
            // Get all approved properties
            const response = await fetch('/api/get_properties.php');
            const properties = await response.json();
            
            if (!properties || properties.length === 0) {
                return;
            }
            
            // Score each property
            const scoredProperties = properties.map(property => ({
                ...property,
                score: this.calculateScore(property, recentSearches)
            }));
            
            // Sort by score and get top 3
            const topRecommendations = scoredProperties
                .filter(p => p.score > 20) // Only show if score > 20
                .sort((a, b) => b.score - a.score)
                .slice(0, 3);
            
            if (topRecommendations.length > 0) {
                this.displayRecommendations(topRecommendations);
            }
        } catch (error) {
            console.error('Error loading recommendations:', error);
        }
    }
    
    // Display recommendations on the page
    displayRecommendations(recommendations) {
        const container = document.getElementById('recommendations-container');
        const section = document.getElementById('smart-recommendations');
        
        if (!container || recommendations.length === 0) {
            return;
        }
        
        // Show the recommendations section
        section.style.display = 'block';
        
        // Clear existing recommendations
        container.innerHTML = '';
        
        // Generate HTML for each recommendation
        recommendations.forEach(property => {
            const propertyCard = this.createPropertyCard(property);
            container.appendChild(propertyCard);
        });
    }
    
    // Create property card HTML
    createPropertyCard(property) {
        const card = document.createElement('div');
        card.className = 'property-card';
        card.setAttribute('data-property-id', property.property_id);
        
        const image = property.primary_image 
            ? `<img src="${property.primary_image}" alt="${property.title}" class="property-image">`
            : `<div class="property-image-placeholder"><i class="fas fa-home"></i></div>`;
        
        card.innerHTML = `
            ${image}
            <div class="property-info">
                <div class="recommendation-badge">
                    <i class="fas fa-star"></i> Recommended for you
                </div>
                <h3 class="property-title">${property.title}</h3>
                <p class="property-location">
                    <i class="fas fa-map-marker-alt"></i> 
                    ${property.address}, ${property.city}
                </p>
                <div class="property-details">
                    <span><i class="fas fa-bed"></i> ${property.bedrooms} Beds</span>
                    <span><i class="fas fa-bath"></i> ${property.bathrooms} Baths</span>
                </div>
                <div class="property-price">
                    <strong>$${Number(property.price).toLocaleString()}</strong>
                    <span class="property-type-badge">${property.type.charAt(0).toUpperCase() + property.type.slice(1)}</span>
                </div>
                <div class="property-actions">
                    <a href="/property_details.php?id=${property.property_id}" class="btn btn-secondary">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                </div>
            </div>
        `;
        
        return card;
    }
}

// Initialize recommendation system when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Create global instance
    window.smartRecommendation = new SmartRecommendation();
    
    // Record current search if on index page
    const urlParams = new URLSearchParams(window.location.search);
    const hasSearchParams = urlParams.has('search') || 
                           urlParams.has('city') || 
                           urlParams.has('min_price') || 
                           urlParams.has('max_price');
    
    if (hasSearchParams) {
        const searchData = {
            search: urlParams.get('search') || '',
            city: urlParams.get('city') || '',
            min_price: urlParams.get('min_price') || '',
            max_price: urlParams.get('max_price') || '',
            type: urlParams.get('type') || '',
            property_type: urlParams.get('property_type') || ''
        };
        
        window.smartRecommendation.recordSearch(searchData);
    }
    
    // Load recommendations for logged in users
    // (In a real implementation, this would be stored server-side)
    if (document.querySelector('nav .nav-menu a[href*="profile.php"]')) {
        // User is logged in
        window.smartRecommendation.loadRecommendations();
    }
});

// Add recommendation badge CSS
const style = document.createElement('style');
style.textContent = `
    .recommendation-badge {
        background: linear-gradient(135deg, #f59e0b, #f97316);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        margin-bottom: 0.5rem;
        display: inline-block;
    }
    
    .recommendation-badge i {
        margin-right: 0.25rem;
    }
`;
document.head.appendChild(style);


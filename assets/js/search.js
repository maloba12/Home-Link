// HomeLink - Search Functionality

// Enhanced search with debouncing
class PropertySearch {
    constructor() {
        this.searchInput = null;
        this.debounceTimer = null;
    }
    
    init() {
        this.setupSearchInput();
        this.setupFilters();
    }
    
    setupSearchInput() {
        const searchInputs = document.querySelectorAll('input[type="text"][name="search"]');
        
        searchInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.performSearch(e.target.value);
                }, 300);
            });
        });
    }
    
    setupFilters() {
        const filterInputs = document.querySelectorAll('select, input[type="number"]');
        
        filterInputs.forEach(input => {
            input.addEventListener('change', () => {
                this.applyFilters();
            });
        });
    }
    
    performSearch(query) {
        if (query.length < 3) {
            return; // Don't search for very short queries
        }
        
        // In a real implementation, this would make an AJAX call to the server
        console.log('Searching for:', query);
    }
    
    applyFilters() {
        // Filter properties in real-time
        const searchForm = document.querySelector('.search-form');
        if (searchForm) {
            searchForm.submit();
        }
    }
}

// Initialize search functionality
document.addEventListener('DOMContentLoaded', function() {
    const search = new PropertySearch();
    search.init();
});

// Price range slider (if implemented)
function initPriceSlider() {
    const minPriceInput = document.querySelector('input[name="min_price"]');
    const maxPriceInput = document.querySelector('input[name="max_price"]');
    
    if (minPriceInput && maxPriceInput) {
        // Add range validation
        minPriceInput.addEventListener('change', function() {
            if (maxPriceInput.value && parseInt(this.value) > parseInt(maxPriceInput.value)) {
                alert('Minimum price cannot be greater than maximum price');
                this.value = maxPriceInput.value;
            }
        });
        
        maxPriceInput.addEventListener('change', function() {
            if (minPriceInput.value && parseInt(this.value) < parseInt(minPriceInput.value)) {
                alert('Maximum price cannot be less than minimum price');
                this.value = minPriceInput.value;
            }
        });
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initPriceSlider();
});


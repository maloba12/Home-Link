<?php
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';

$pageTitle = 'How It Works - HomeLink';
include 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-question-circle"></i> How It Works</h1>
        <p>Your guide to using HomeLink effectively</p>
    </div>
</div>

<div class="container">
    <div class="how-it-works-content">
        <!-- For Buyers/Renters -->
        <section class="process-section">
            <h2><i class="fas fa-user"></i> For Buyers & Renters</h2>
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <i class="fas fa-user-plus step-icon"></i>
                    <h3>Create an Account</h3>
                    <p>Sign up for free and create your buyer profile. Tell us about your preferences and budget.</p>
                </div>
                
                <div class="step-card">
                    <div class="step-number">2</div>
                    <i class="fas fa-search step-icon"></i>
                    <h3>Search Properties</h3>
                    <p>Browse our extensive listings or use our smart search filters to find properties that match your needs.</p>
                </div>
                
                <div class="step-card">
                    <div class="step-number">3</div>
                    <i class="fas fa-heart step-icon"></i>
                    <h3>Save Favorites</h3>
                    <p>Like properties you're interested in. Our AI will learn your preferences and suggest similar homes.</p>
                </div>
                
                <div class="step-card">
                    <div class="step-number">4</div>
                    <i class="fas fa-calendar-check step-icon"></i>
                    <h3>Book Viewings</h3>
                    <p>Schedule property viewings directly through the platform. Connect with sellers easily.</p>
                </div>
                
                <div class="step-card">
                    <div class="step-number">5</div>
                    <i class="fas fa-handshake step-icon"></i>
                    <h3>Make a Deal</h3>
                    <p>Found your dream home? Contact the seller and finalize the deal with confidence.</p>
                </div>
            </div>
        </section>

        <!-- For Sellers -->
        <section class="process-section">
            <h2><i class="fas fa-home"></i> For Sellers</h2>
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <i class="fas fa-user-tie step-icon"></i>
                    <h3>Register as Seller</h3>
                    <p>Create a seller account and complete your profile to start listing properties.</p>
                </div>
                
                <div class="step-card">
                    <div class="step-number">2</div>
                    <i class="fas fa-upload step-icon"></i>
                    <h3>List Your Property</h3>
                    <p>Upload property details, photos, and set your price. Make your listing stand out!</p>
                </div>
                
                <div class="step-card">
                    <div class="step-number">3</div>
                    <i class="fas fa-check-circle step-icon"></i>
                    <h3>Get Approved</h3>
                    <p>Our team reviews your listing to ensure quality. Approval usually takes 24-48 hours.</p>
                </div>
                
                <div class="step-card">
                    <div class="step-number">4</div>
                    <i class="fas fa-users step-icon"></i>
                    <h3>Connect with Buyers</h3>
                    <p>Receive booking requests and messages from interested buyers. Manage everything in one place.</p>
                </div>
                
                <div class="step-card">
                    <div class="step-number">5</div>
                    <i class="fas fa-dollar-sign step-icon"></i>
                    <h3>Close the Deal</h3>
                    <p>Negotiate and finalize the sale or rental agreement with your chosen buyer.</p>
                </div>
            </div>
        </section>

        <!-- Key Features -->
        <section class="features-highlight">
            <h2><i class="fas fa-star"></i> Key Features</h2>
            <div class="features-list">
                <div class="feature-item">
                    <i class="fas fa-brain"></i>
                    <div>
                        <h3>Smart Recommendations</h3>
                        <p>Our AI-powered system learns your preferences and suggests properties you'll love.</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-shield-alt"></i>
                    <div>
                        <h3>Verified Listings</h3>
                        <p>All properties are verified by our team to ensure authenticity and prevent fraud.</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-mobile-alt"></i>
                    <div>
                        <h3>Mobile Friendly</h3>
                        <p>Access HomeLink on any device - desktop, tablet, or mobile phone.</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-comments"></i>
                    <div>
                        <h3>Direct Communication</h3>
                        <p>Chat directly with sellers or buyers through our secure messaging system.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <h2>Ready to Get Started?</h2>
            <p>Join thousands of users who have found their perfect homes through HomeLink.</p>
            <div class="cta-buttons">
                <a href="register.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Sign Up Now
                </a>
                <a href="properties.php" class="btn btn-secondary">
                    <i class="fas fa-building"></i> Browse Properties
                </a>
            </div>
        </section>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<?php
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';

$pageTitle = 'About Us - HomeLink';
include 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-info-circle"></i> About HomeLink</h1>
        <p>Your trusted partner in finding the perfect home</p>
    </div>
</div>

<div class="container">
    <div class="about-content">
        <section class="about-section">
            <h2><i class="fas fa-bullseye"></i> Our Mission</h2>
            <p>HomeLink is dedicated to revolutionizing the way people find and connect with their ideal homes. We believe that everyone deserves a place they can call home, and we're here to make that journey as smooth and enjoyable as possible.</p>
        </section>

        <section class="about-section">
            <h2><i class="fas fa-eye"></i> Our Vision</h2>
            <p>To become the most trusted and innovative housing platform in Zambia, connecting buyers, sellers, and renters through smart technology and personalized service.</p>
        </section>

        <section class="about-section">
            <h2><i class="fas fa-star"></i> Why Choose HomeLink?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Verified Listings</h3>
                    <p>All properties are verified by our team to ensure authenticity and quality.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-brain"></i>
                    <h3>Smart Recommendations</h3>
                    <p>Our AI-powered system suggests properties that match your preferences.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-users"></i>
                    <h3>Trusted Community</h3>
                    <p>Join thousands of satisfied users who found their homes through HomeLink.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-headset"></i>
                    <h3>24/7 Support</h3>
                    <p>Our dedicated support team is always ready to assist you.</p>
                </div>
            </div>
        </section>

        <section class="about-section">
            <h2><i class="fas fa-chart-line"></i> Our Impact</h2>
            <div class="stats-row">
                <div class="stat-box">
                    <h3>1000+</h3>
                    <p>Properties Listed</p>
                </div>
                <div class="stat-box">
                    <h3>5000+</h3>
                    <p>Happy Users</p>
                </div>
                <div class="stat-box">
                    <h3>500+</h3>
                    <p>Successful Deals</p>
                </div>
                <div class="stat-box">
                    <h3>50+</h3>
                    <p>Cities Covered</p>
                </div>
            </div>
        </section>

        <section class="about-section cta-section">
            <h2>Ready to Find Your Home?</h2>
            <p>Join HomeLink today and discover properties that match your dreams.</p>
            <div class="cta-buttons">
                <a href="/properties.php" class="btn btn-primary">
                    <i class="fas fa-building"></i> Browse Properties
                </a>
                <a href="/register.php" class="btn btn-secondary">
                    <i class="fas fa-user-plus"></i> Create Account
                </a>
            </div>
        </section>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

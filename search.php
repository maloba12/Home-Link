<?php
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';

$pageTitle = 'Search - HomeLink';
include 'includes/header.php';
?>

<div class="container">
    <h1 class="page-title"><i class="fas fa-search"></i> Advanced Search</h1>
    
    <div class="search-container">
        <form method="GET" action="/index.php" class="advanced-search-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="search"><i class="fas fa-search"></i> Keywords</label>
                    <input type="text" id="search" name="search" placeholder="Search by title, location...">
                </div>
                
                <div class="form-group">
                    <label for="city"><i class="fas fa-map-marker-alt"></i> City</label>
                    <input type="text" id="city" name="city" placeholder="Enter city">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="type"><i class="fas fa-tag"></i> Type</label>
                    <select id="type" name="type">
                        <option value="">All Types</option>
                        <option value="rent">Rent</option>
                        <option value="sale">Sale</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="property_type"><i class="fas fa-home"></i> Property Type</label>
                    <select id="property_type" name="property_type">
                        <option value="">All Property Types</option>
                        <option value="apartment">Apartment</option>
                        <option value="house">House</option>
                        <option value="condo">Condo</option>
                        <option value="townhouse">Townhouse</option>
                        <option value="studio">Studio</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="min_price"><i class="fas fa-dollar-sign"></i> Minimum Price</label>
                    <input type="number" id="min_price" name="min_price" placeholder="0">
                </div>
                
                <div class="form-group">
                    <label for="max_price"><i class="fas fa-dollar-sign"></i> Maximum Price</label>
                    <input type="number" id="max_price" name="max_price" placeholder="No limit">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="bedrooms"><i class="fas fa-bed"></i> Bedrooms</label>
                    <select id="bedrooms" name="bedrooms">
                        <option value="">Any</option>
                        <option value="1">1+</option>
                        <option value="2">2+</option>
                        <option value="3">3+</option>
                        <option value="4">4+</option>
                        <option value="5">5+</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="bathrooms"><i class="fas fa-bath"></i> Bathrooms</label>
                    <select id="bathrooms" name="bathrooms">
                        <option value="">Any</option>
                        <option value="1">1+</option>
                        <option value="1.5">1.5+</option>
                        <option value="2">2+</option>
                        <option value="2.5">2.5+</option>
                        <option value="3">3+</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-large">
                <i class="fas fa-search"></i> Search Properties
            </button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>


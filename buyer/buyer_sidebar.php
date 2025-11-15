<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="buyer-sidebar">
    <ul class="sidebar-menu">
        <li>
            <a href="/buyer/dashboard.php" class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="/properties.php">
                <i class="fas fa-search"></i>
                <span>Browse Properties</span>
            </a>
        </li>
        <li>
            <a href="/buyer/favorites.php" class="<?php echo $currentPage === 'favorites.php' ? 'active' : ''; ?>">
                <i class="fas fa-heart"></i>
                <span>My Favorites</span>
            </a>
        </li>
        <li>
            <a href="/buyer/bookings.php" class="<?php echo $currentPage === 'bookings.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i>
                <span>My Bookings</span>
            </a>
        </li>
        <li>
            <a href="/buyer/recommendations.php" class="<?php echo $currentPage === 'recommendations.php' ? 'active' : ''; ?>">
                <i class="fas fa-magic"></i>
                <span>Recommendations</span>
            </a>
        </li>
        <li>
            <a href="/profile.php">
                <i class="fas fa-user-edit"></i>
                <span>Profile Settings</span>
            </a>
        </li>
    </ul>
    
    <div class="sidebar-footer">
        <a href="/index.php" class="sidebar-link">
            <i class="fas fa-globe"></i>
            <span>View Site</span>
        </a>
    </div>
</aside>

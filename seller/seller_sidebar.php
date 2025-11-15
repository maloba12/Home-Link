<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="seller-sidebar">
    <ul class="sidebar-menu">
        <li>
            <a href="/seller/dashboard.php" class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="/seller/my_properties.php" class="<?php echo $currentPage === 'my_properties.php' ? 'active' : ''; ?>">
                <i class="fas fa-building"></i>
                <span>My Properties</span>
            </a>
        </li>
        <li>
            <a href="/seller/upload_property.php" class="<?php echo $currentPage === 'upload_property.php' ? 'active' : ''; ?>">
                <i class="fas fa-plus-circle"></i>
                <span>Add Property</span>
            </a>
        </li>
        <li>
            <a href="/seller/bookings.php" class="<?php echo $currentPage === 'bookings.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i>
                <span>Bookings</span>
            </a>
        </li>
        <li>
            <a href="/seller/analytics.php" class="<?php echo $currentPage === 'analytics.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
                <span>Analytics</span>
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

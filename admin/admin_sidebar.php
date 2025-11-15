<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
    <ul class="sidebar-menu">
        <li>
            <a href="/admin/dashboard.php" class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="/admin/manage_properties.php" class="<?php echo $currentPage === 'manage_properties.php' ? 'active' : ''; ?>">
                <i class="fas fa-building"></i>
                <span>Properties</span>
            </a>
        </li>
        <li>
            <a href="/admin/manage_users.php" class="<?php echo $currentPage === 'manage_users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
        </li>
        <li>
            <a href="/admin/analytics.php" class="<?php echo $currentPage === 'analytics.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
                <span>Analytics</span>
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

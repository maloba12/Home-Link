<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="agent-sidebar">
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="managed_properties.php" class="<?php echo $currentPage === 'managed_properties.php' ? 'active' : ''; ?>">
                <i class="fas fa-building"></i>
                <span>Managed Properties</span>
            </a>
        </li>
        <li>
            <a href="add_property.php" class="<?php echo $currentPage === 'add_property.php' ? 'active' : ''; ?>">
                <i class="fas fa-plus-circle"></i>
                <span>Add Property</span>
            </a>
        </li>
        <li>
            <a href="clients.php" class="<?php echo $currentPage === 'clients.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>My Clients</span>
            </a>
        </li>
        <li>
            <a href="deals.php" class="<?php echo $currentPage === 'deals.php' ? 'active' : ''; ?>">
                <i class="fas fa-handshake"></i>
                <span>Deals & Commissions</span>
            </a>
        </li>
        <li>
            <a href="communications.php" class="<?php echo $currentPage === 'communications.php' ? 'active' : ''; ?>">
                <i class="fas fa-comments"></i>
                <span>Communications</span>
            </a>
        </li>
        <li>
            <a href="../profile.php">
                <i class="fas fa-user-edit"></i>
                <span>Profile Settings</span>
            </a>
        </li>
    </ul>
    
    <div class="sidebar-footer">
        <a href="../index.php" class="sidebar-link">
            <i class="fas fa-globe"></i>
            <span>View Site</span>
        </a>
    </div>
</aside>

<?php
if (!isset($pageTitle)) {
    $pageTitle = 'HomeLink - Smart Housing Platform';
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <a href="index.php">
                    <i class="fas fa-home"></i> HomeLink
                </a>
            </div>
            
            <button class="mobile-menu-toggle" id="mobileMenuToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <ul class="nav-menu" id="navMenu">
                <?php 
                // Check if current page is login or register
                $currentPage = basename($_SERVER['PHP_SELF']);
                $isAuthPage = in_array($currentPage, ['login.php', 'register.php', 'forgot-password.php', 'reset-password.php']);
                
                // Show main navigation only if NOT on auth pages
                if (!$isAuthPage): 
                ?>
                    <li><a href="index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> Home
                    </a></li>
                    <li><a href="about.php" class="<?php echo $currentPage === 'about.php' ? 'active' : ''; ?>">
                        <i class="fas fa-info-circle"></i> About Us
                    </a></li>
                    <li><a href="properties.php" class="<?php echo $currentPage === 'properties.php' ? 'active' : ''; ?>">
                        <i class="fas fa-building"></i> Browse Properties
                    </a></li>
                    <li><a href="how-it-works.php" class="<?php echo $currentPage === 'how-it-works.php' ? 'active' : ''; ?>">
                        <i class="fas fa-question-circle"></i> How It Works
                    </a></li>
                    <li><a href="contact.php" class="<?php echo $currentPage === 'contact.php' ? 'active' : ''; ?>">
                        <i class="fas fa-envelope"></i> Contact Us
                    </a></li>
                <?php endif; ?>
                
                <?php if (isLoggedIn()): ?>
                    <?php if (isSeller()): ?>
                        <li><a href="seller/dashboard.php" class="<?php echo $currentPage === 'dashboard.php' && strpos($_SERVER['REQUEST_URI'], '/seller/') !== false ? 'active' : ''; ?>">
                            <i class="fas fa-store"></i> My Dashboard
                        </a></li>
                        <li><a href="seller/upload_property.php">
                            <i class="fas fa-plus-circle"></i> Upload Property
                        </a></li>
                    <?php elseif (isAgent()): ?>
                        <li><a href="agent/dashboard.php" class="<?php echo $currentPage === 'dashboard.php' && strpos($_SERVER['REQUEST_URI'], '/agent/') !== false ? 'active' : ''; ?>">
                            <i class="fas fa-briefcase"></i> Agent Dashboard
                        </a></li>
                        <li><a href="agent/add_property.php">
                            <i class="fas fa-plus-circle"></i> Add Property
                        </a></li>
                    <?php elseif (isBuyer()): ?>
                        <li><a href="buyer/dashboard.php" class="<?php echo $currentPage === 'dashboard.php' && strpos($_SERVER['REQUEST_URI'], '/buyer/') !== false ? 'active' : ''; ?>">
                            <i class="fas fa-th-large"></i> My Dashboard
                        </a></li>
                    <?php endif; ?>
                    <li><a href="profile.php">
                        <i class="fas fa-user"></i> Profile
                    </a></li>
                    <li><a href="logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a></li>
                    <li><a href="register.php" class="btn-register">
                        <i class="fas fa-user-plus"></i> Register
                    </a></li>
                <?php endif; ?>
                
                <!-- Dark Mode Toggle -->
                <li class="theme-toggle">
                    <button id="themeToggle" class="theme-toggle-btn" title="Toggle Dark Mode">
                        <i class="fas fa-moon"></i>
                    </button>
                </li>
            </ul>
        </div>
    </nav>

    <?php 
    // Hide chatbox on login, register, and other auth pages
    $currentPage = basename($_SERVER['PHP_SELF']);
    if (!in_array($currentPage, ['login.php', 'register.php', 'forgot-password.php', 'reset-password.php'])): 
    ?>
    <!-- Chatbox -->
    <div id="chatbox" class="chatbox">
        <div class="chatbox-header">
            <i class="fas fa-robot"></i> HomeLink Assistant
            <button class="chatbox-toggle">&times;</button>
        </div>
        <div class="chatbox-messages">
            <div class="message bot-message">
                <i class="fas fa-robot"></i>
                <div class="message-content">
                    <p>Hello! I'm your HomeLink Assistant. How can I help you find your perfect property today?</p>
                </div>
            </div>
        </div>
        <div class="chatbox-input">
            <input type="text" placeholder="Ask me about properties, locations, or anything else...">
            <button><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
    <?php endif; ?>

    <main class="main-content">

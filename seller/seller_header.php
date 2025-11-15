<?php
if (!isset($pageTitle)) {
    $pageTitle = 'Seller Dashboard - HomeLink';
}

// Get current page for active menu highlighting
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
    <link rel="stylesheet" href="/assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="seller-body">
    <!-- Top Navbar -->
    <nav class="seller-navbar">
        <div class="seller-navbar-content">
            <div class="seller-brand">
                <i class="fas fa-store"></i>
                <span>Seller Portal</span>
            </div>
            <div class="seller-navbar-right">
                <span class="seller-user">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['username'] ?? 'Seller'); ?>
                </span>
                <a href="/index.php" class="seller-home-btn" title="View Site">
                    <i class="fas fa-home"></i>
                </a>
                <a href="/logout.php" class="seller-logout-btn" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </nav>

<?php
if (!isset($pageTitle)) {
    $pageTitle = 'Agent Dashboard - HomeLink';
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
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="agent-body">
    <!-- Top Navbar -->
    <nav class="agent-navbar">
        <div class="agent-navbar-content">
            <div class="agent-brand">
                <i class="fas fa-briefcase"></i>
                <span>Agent Portal</span>
            </div>
            <div class="agent-navbar-right">
                <span class="agent-user">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['username'] ?? 'Agent'); ?>
                </span>
                <a href="../index.php" class="agent-home-btn" title="View Site">
                    <i class="fas fa-home"></i>
                </a>
                <a href="../logout.php" class="agent-logout-btn" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </nav>

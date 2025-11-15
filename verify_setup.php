<?php
/**
 * HomeLink Setup Verification Script
 * Run this file to check if your setup is correct
 * Access via: http://localhost/homelink/verify_setup.php
 */

echo "<!DOCTYPE html>";
echo "<html><head><title>HomeLink Setup Verification</title>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
    .success { color: #10b981; padding: 10px; background: #d1fae5; border-radius: 5px; margin: 10px 0; }
    .error { color: #ef4444; padding: 10px; background: #fee2e2; border-radius: 5px; margin: 10px 0; }
    .info { color: #3b82f6; padding: 10px; background: #dbeafe; border-radius: 5px; margin: 10px 0; }
    h1, h2 { color: #1e293b; }
</style></head><body>";

echo "<h1>🏠 HomeLink Setup Verification</h1>";

$checks = [];
$allPassed = true;

// Check PHP version
$phpVersion = phpversion();
$checks[] = ['PHP Version', version_compare($phpVersion, '7.4', '>='), "Current: $phpVersion (Need: 7.4+)"];
if (version_compare($phpVersion, '7.4', '<')) {
    $allPassed = false;
}

// Check required PHP extensions
$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json'];
foreach ($requiredExtensions as $ext) {
    $exists = extension_loaded($ext);
    $checks[] = ["PHP Extension: $ext", $exists, $exists ? 'OK' : 'Missing'];
    if (!$exists) $allPassed = false;
}

// Check database connection
try {
    require_once 'includes/db_connect.php';
    $checks[] = ['Database Connection', true, 'Connected successfully'];
    
    // Check if database has tables
    $tables = ['users', 'properties', 'images', 'favorites', 'bookings'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            $checks[] = ["Table: $table", true, "$count records found"];
        } catch (PDOException $e) {
            $checks[] = ["Table: $table", false, 'Table missing'];
            $allPassed = false;
        }
    }
} catch (Exception $e) {
    $checks[] = ['Database Connection', false, $e->getMessage()];
    $allPassed = false;
}

// Check directories
$directories = [
    'assets/uploads' => 'Property image uploads',
    'assets/images' => 'Static images',
    'assets/css' => 'Stylesheets',
    'assets/js' => 'JavaScript files',
    'includes' => 'PHP includes',
    'admin' => 'Admin panel',
    'api' => 'API endpoints'
];

foreach ($directories as $dir => $desc) {
    $exists = is_dir($dir) && is_readable($dir);
    $checks[] = ["Directory: $dir", $exists, $desc];
    if (!$exists) $allPassed = false;
}

// Check write permissions
$writableDirs = ['assets/uploads'];
foreach ($writableDirs as $dir) {
    $writable = is_dir($dir) && is_writable($dir);
    $checks[] = ["Write Permission: $dir", $writable, $writable ? 'Writable' : 'Not writable'];
    if (!$writable) $allPassed = false;
}

// Check critical files
$criticalFiles = [
    'index.php',
    'login.php',
    'register.php',
    'includes/db_connect.php',
    'includes/auth.php',
    'includes/header.php',
    'includes/footer.php',
    'assets/css/style.css',
    'assets/js/main.js',
    'sql/homelink.sql'
];

foreach ($criticalFiles as $file) {
    $exists = file_exists($file);
    $checks[] = ["File: $file", $exists, $exists ? 'Found' : 'Missing'];
    if (!$exists) $allPassed = false;
}

// Display results
echo "<h2>Verification Results</h2>";

foreach ($checks as $check) {
    list($item, $passed, $message) = $check;
    $icon = $passed ? '✅' : '❌';
    $class = $passed ? 'success' : 'error';
    
    if (!$passed && strpos($message, 'records found') === false) {
        echo "<div class='$class'>$icon <strong>$item:</strong> $message</div>";
    } else if ($passed) {
        echo "<div class='$class'>$icon <strong>$item:</strong> $message</div>";
    } else {
        echo "<div class='error'>$icon <strong>$item:</strong> $message</div>";
    }
}

echo "<hr>";

if ($allPassed) {
    echo "<div class='success'><h2>🎉 All checks passed! Your setup is ready.</h2>";
    echo "<p><a href='index.php' style='color: #065f46; font-weight: bold;'>→ Go to HomeLink</a></p></div>";
} else {
    echo "<div class='error'><h2>⚠️ Some checks failed. Please fix the issues above.</h2>";
    echo "<p>Common fixes:</p><ul>";
    echo "<li>Import <code>sql/homelink.sql</code> to create the database</li>";
    echo "<li>Update database credentials in <code>includes/db_connect.php</code></li>";
    echo "<li>Set write permissions: <code>chmod 755 assets/uploads</code></li>";
    echo "</ul></div>";
}

echo "<div class='info'><h3>📝 Next Steps:</h3>";
echo "<ol>";
echo "<li>Make sure MySQL is running</li>";
echo "<li>Import <code>sql/homelink.sql</code> into your MySQL database</li>";
echo "<li>Configure <code>includes/db_connect.php</code> with your database credentials</li>";
echo "<li>Set write permissions on <code>assets/uploads</code> directory</li>";
echo "<li>Access the site: <a href='index.php'>index.php</a></li>";
echo "<li>Login with admin/admin123 to get started</li>";
echo "</ol></div>";

echo "</body></html>";
?>


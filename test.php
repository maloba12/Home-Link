<!DOCTYPE html>
<html>
<head>
    <title>System Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success {
            color: green;
            font-size: 24px;
            font-weight: bold;
        }
        .info {
            margin: 20px 0;
            padding: 15px;
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
        }
    </style>
</head>
<body>
    <div class="test-box">
        <h1 class="success">✅ System is Working!</h1>
        
        <div class="info">
            <h3>PHP Information:</h3>
            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
            <p><strong>Server Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
            <p><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
        </div>
        
        <div class="info">
            <h3>Database Connection:</h3>
            <?php
            require_once 'includes/db_connect.php';
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
                $result = $stmt->fetch();
                echo "<p class='success'>✅ Database Connected!</p>";
                echo "<p><strong>Total Users:</strong> " . $result['count'] . "</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
            }
            ?>
        </div>
        
        <div class="info">
            <h3>File System:</h3>
            <p><strong>CSS File:</strong> <?php echo file_exists('assets/css/style.css') ? '✅ Exists' : '❌ Missing'; ?></p>
            <p><strong>JS File:</strong> <?php echo file_exists('assets/js/theme.js') ? '✅ Exists' : '❌ Missing'; ?></p>
            <p><strong>Header File:</strong> <?php echo file_exists('includes/header.php') ? '✅ Exists' : '❌ Missing'; ?></p>
        </div>
        
        <div class="info">
            <h3>Quick Links:</h3>
            <ul>
                <li><a href="index.php">Home Page</a></li>
                <li><a href="login.php">Login Page</a></li>
                <li><a href="register.php">Register Page</a></li>
                <li><a href="properties.php">Properties Page</a></li>
            </ul>
        </div>
        
        <div class="info" style="background: #fff3cd; border-color: #ffc107;">
            <h3>⚠️ If you see a white screen:</h3>
            <ol>
                <li><strong>Clear Browser Cache:</strong> Press Ctrl+Shift+Delete (or Cmd+Shift+Delete on Mac)</li>
                <li><strong>Hard Refresh:</strong> Press Ctrl+F5 (or Cmd+Shift+R on Mac)</li>
                <li><strong>Try Incognito Mode:</strong> Ctrl+Shift+N (or Cmd+Shift+N on Mac)</li>
                <li><strong>Check Browser Console:</strong> Press F12, go to Console tab, look for errors</li>
                <li><strong>Try Different Browser:</strong> Chrome, Firefox, Edge, etc.</li>
            </ol>
        </div>
    </div>
</body>
</html>

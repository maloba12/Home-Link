<?php
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: /index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fullName = sanitizeInput($_POST['full_name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'buyer';
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required!';
    } elseif (!validateEmail($email)) {
        $error = 'Invalid email format!';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match!';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters!';
    } else {
        try {
            // Check if username exists
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error = 'Username or email already exists!';
            } else {
                // Insert new user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, full_name, phone) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashedPassword, $role, $fullName, $phone]);
                
                $success = 'Registration successful! You can now login.';
                header('Location: /login.php?success=1');
                exit();
            }
        } catch (PDOException $e) {
            $error = 'Registration failed: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Register - HomeLink';
include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <h2><i class="fas fa-user-plus"></i> Create Account</h2>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" class="auth-form">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="full_name"><i class="fas fa-id-card"></i> Full Name</label>
                <input type="text" id="full_name" name="full_name">
            </div>
            
            <div class="form-group">
                <label for="phone"><i class="fas fa-phone"></i> Phone</label>
                <input type="text" id="phone" name="phone">
            </div>
            
            <div class="form-group">
                <label for="role"><i class="fas fa-users"></i> I want to</label>
                <select id="role" name="role" required>
                    <option value="buyer">Buy/Rent Properties</option>
                    <option value="seller">Sell/Rent Properties</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Register</button>
        </form>
        
        <p class="auth-link">Already have an account? <a href="/login.php">Login here</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>


<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
requireAdmin();

$pageTitle = 'Manage Users - HomeLink';

$success = '';
$error = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = $_POST['user_id'] ?? 0;
    
    if ($action && $userId && $userId != getUserId()) {
        try {
            if ($action === 'delete') {
                $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
                $stmt->execute([$userId]);
                $success = 'User deleted successfully!';
            } elseif ($action === 'change_role') {
                $newRole = $_POST['role'] ?? '';
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE user_id = ?");
                $stmt->execute([$newRole, $userId]);
                $success = 'User role updated successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Action failed: ' . $e->getMessage();
        }
    }
}

// Get users
$filter = $_GET['filter'] ?? 'all';
$sql = "SELECT * FROM users WHERE 1=1";

if ($filter === 'buyer') {
    $sql .= " AND role = 'buyer'";
} elseif ($filter === 'seller') {
    $sql .= " AND role = 'seller'";
} elseif ($filter === 'admin') {
    $sql .= " AND role = 'admin'";
} elseif ($filter === 'agent') {
    $sql .= " AND role = 'agent'";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->query($sql);
$users = $stmt->fetchAll();

include '../includes/admin_header.php';
?>

<div class="admin-layout">
    <?php include 'admin_sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-page-header">
            <h1><i class="fas fa-users"></i> Manage Users</h1>
            <p>Manage user accounts and permissions</p>
        </div>
    
    <?php if ($success): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?php echo addslashes($success); ?>',
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '<?php echo addslashes($error); ?>'
            });
        </script>
    <?php endif; ?>
    
    <div class="filter-tabs">
        <a href="?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">All Users</a>
        <a href="?filter=buyer" class="filter-tab <?php echo $filter === 'buyer' ? 'active' : ''; ?>">Buyers</a>
        <a href="?filter=seller" class="filter-tab <?php echo $filter === 'seller' ? 'active' : ''; ?>">Sellers</a>
        <a href="?filter=agent" class="filter-tab <?php echo $filter === 'agent' ? 'active' : ''; ?>">Agents</a>
        <a href="?filter=admin" class="filter-tab <?php echo $filter === 'admin' ? 'active' : ''; ?>">Admins</a>
    </div>
    
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8" class="text-center">No users found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                            <td>
                                <?php if ($user['user_id'] != getUserId()): ?>
                                    <form method="POST" style="display: inline-block;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                        <input type="hidden" name="action" value="change_role">
                                        <select name="role" onchange="this.form.submit()">
                                            <option value="buyer" <?php echo $user['role'] === 'buyer' ? 'selected' : ''; ?>>Buyer</option>
                                            <option value="seller" <?php echo $user['role'] === 'seller' ? 'selected' : ''; ?>>Seller</option>
                                            <option value="agent" <?php echo $user['role'] === 'agent' ? 'selected' : ''; ?>>Agent</option>
                                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        </select>
                                    </form>
                                <?php else: ?>
                                    <span class="status-badge"><?php echo ucfirst($user['role']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if ($user['user_id'] != getUserId()): ?>
                                    <button onclick="confirmDelete(<?php echo $user['user_id']; ?>)" 
                                            class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    </main>
</div>

<script>
function confirmDelete(userId) {
    Swal.fire({
        title: 'Delete User?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete!'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="user_id" value="${userId}">
                <input type="hidden" name="action" value="delete">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

<?php include '../includes/admin_footer.php'; ?>


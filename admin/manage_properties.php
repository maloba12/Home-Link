<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
requireAdmin();

$pageTitle = 'Manage Properties - HomeLink';

$success = '';
$error = '';

// Handle approve/reject action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $propertyId = $_POST['property_id'] ?? 0;
    
    if ($action && $propertyId) {
        try {
            if ($action === 'approve') {
                $stmt = $pdo->prepare("UPDATE properties SET status = 'approved' WHERE property_id = ?");
                $stmt->execute([$propertyId]);
                $success = 'Property approved successfully!';
            } elseif ($action === 'reject') {
                $stmt = $pdo->prepare("UPDATE properties SET status = 'rejected' WHERE property_id = ?");
                $stmt->execute([$propertyId]);
                $success = 'Property rejected successfully!';
            } elseif ($action === 'delete') {
                $stmt = $pdo->prepare("DELETE FROM properties WHERE property_id = ?");
                $stmt->execute([$propertyId]);
                $success = 'Property deleted successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Action failed: ' . $e->getMessage();
        }
    }
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$sql = "SELECT p.*, u.username as seller_name, u.email as seller_email
        FROM properties p 
        LEFT JOIN users u ON p.seller_id = u.user_id 
        WHERE 1=1";

if ($filter === 'pending') {
    $sql .= " AND p.status = 'pending'";
} elseif ($filter === 'approved') {
    $sql .= " AND p.status = 'approved'";
} elseif ($filter === 'rejected') {
    $sql .= " AND p.status = 'rejected'";
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->query($sql);
$properties = $stmt->fetchAll();

include '../includes/admin_header.php';
?>

<div class="admin-layout">
    <?php include 'admin_sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-page-header">
            <h1><i class="fas fa-building"></i> Manage Properties</h1>
            <p>Review and manage all property listings</p>
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
        <a href="?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
        <a href="?filter=pending" class="filter-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>">Pending</a>
        <a href="?filter=approved" class="filter-tab <?php echo $filter === 'approved' ? 'active' : ''; ?>">Approved</a>
        <a href="?filter=rejected" class="filter-tab <?php echo $filter === 'rejected' ? 'active' : ''; ?>">Rejected</a>
    </div>
    
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Seller</th>
                    <th>Location</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($properties)): ?>
                    <tr>
                        <td colspan="8" class="text-center">No properties found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($properties as $property): ?>
                        <tr>
                            <td><?php echo $property['property_id']; ?></td>
                            <td>
                                <a href="../property_details.php?id=<?php echo $property['property_id']; ?>">
                                    <?php echo htmlspecialchars($property['title']); ?>
                                </a>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($property['seller_name']); ?>
                                <br>
                                <small><?php echo htmlspecialchars($property['seller_email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($property['city'] . ', ' . $property['state']); ?></td>
                            <td>K<?php echo number_format($property['price']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $property['status']; ?>">
                                    <?php echo ucfirst($property['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($property['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="../property_details.php?id=<?php echo $property['property_id']; ?>" 
                                       class="btn btn-sm btn-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($property['status'] === 'pending'): ?>
                                        <form method="POST" style="display: inline-block;">
                                            <input type="hidden" name="property_id" value="<?php echo $property['property_id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline-block;">
                                            <input type="hidden" name="property_id" value="<?php echo $property['property_id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn btn-sm btn-warning" title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <button onclick="confirmDelete(<?php echo $property['property_id']; ?>)" 
                                            class="btn btn-sm btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
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
function confirmDelete(propertyId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="property_id" value="${propertyId}">
                <input type="hidden" name="action" value="delete">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>


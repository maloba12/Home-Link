<?php
require_once dirname(__DIR__) . '/includes/db_connect.php';
require_once dirname(__DIR__) . '/includes/auth.php';

// Check if user is logged in and is a seller
if (!isLoggedIn()) {
    header('Location: /login.php');
    exit();
}

if (!isSeller()) {
    header('Location: /index.php');
    exit();
}

$pageTitle = 'My Properties - HomeLink';
$userId = $_SESSION['user_id'];

// Get filter parameters
$status = $_GET['status'] ?? '';
$type = $_GET['type'] ?? '';

// Build query
$sql = "SELECT p.*, 
        (SELECT image_url FROM images WHERE property_id = p.property_id AND is_primary = TRUE LIMIT 1) as primary_image,
        (SELECT COUNT(*) FROM bookings WHERE property_id = p.property_id) as booking_count
        FROM properties p 
        WHERE p.seller_id = ?";

$params = [$userId];

if (!empty($status)) {
    $sql .= " AND p.status = ?";
    $params[] = $status;
}

if (!empty($type)) {
    $sql .= " AND p.type = ?";
    $params[] = $type;
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll();

include 'seller_header.php';
?>

<div class="admin-layout">
    <?php include 'seller_sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-page-header">
            <h1><i class="fas fa-building"></i> My Properties</h1>
            <p>Manage your property listings</p>
            <a href="/seller/upload_property.php" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Add New Property
            </a>
        </div>
        
        <!-- Filters -->
        <div class="admin-section">
            <form method="GET" action="" class="filter-form">
                <div class="filter-row">
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        <option value="sold" <?php echo $status === 'sold' ? 'selected' : ''; ?>>Sold</option>
                        <option value="rented" <?php echo $status === 'rented' ? 'selected' : ''; ?>>Rented</option>
                    </select>
                    
                    <select name="type">
                        <option value="">All Types</option>
                        <option value="rent" <?php echo $type === 'rent' ? 'selected' : ''; ?>>For Rent</option>
                        <option value="sale" <?php echo $type === 'sale' ? 'selected' : ''; ?>>For Sale</option>
                    </select>
                    
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="/seller/my_properties.php" class="btn btn-outline">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Properties Table -->
        <div class="admin-section">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Bookings</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($properties)): ?>
                            <tr>
                                <td colspan="7" class="text-center">
                                    <div class="empty-state">
                                        <i class="fas fa-home"></i>
                                        <p>No properties found.</p>
                                        <a href="/seller/upload_property.php" class="btn btn-primary">Add Your First Property</a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($properties as $property): ?>
                                <tr>
                                    <td>
                                        <div class="property-cell">
                                            <?php if ($property['primary_image']): ?>
                                                <img src="/<?php echo htmlspecialchars($property['primary_image']); ?>" alt="Property" class="property-thumb">
                                            <?php else: ?>
                                                <div class="property-thumb-placeholder">
                                                    <i class="fas fa-home"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="property-info">
                                                <strong><?php echo htmlspecialchars($property['title']); ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <?php echo htmlspecialchars($property['address'] . ', ' . $property['city']); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge"><?php echo ucfirst($property['type']); ?></span>
                                        <br>
                                        <small><?php echo ucfirst($property['property_type']); ?></small>
                                    </td>
                                    <td><strong>K<?php echo number_format($property['price']); ?></strong></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $property['status']; ?>">
                                            <?php echo ucfirst($property['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary"><?php echo $property['booking_count']; ?></span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($property['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="/property_details.php?id=<?php echo $property['property_id']; ?>" 
                                               class="btn btn-sm btn-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($property['status'] === 'pending' || $property['status'] === 'rejected'): ?>
                                                <a href="/seller/edit_property.php?id=<?php echo $property['property_id']; ?>" 
                                                   class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($property['status'] === 'approved'): ?>
                                                <button class="btn btn-sm btn-success" title="Mark as Sold/Rented" 
                                                        onclick="markAsSold(<?php echo $property['property_id']; ?>)">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-danger" title="Delete" 
                                                    onclick="deleteProperty(<?php echo $property['property_id']; ?>)">
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
        </div>
    </main>
</div>

<style>
.property-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.property-thumb {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
}

.property-thumb-placeholder {
    width: 60px;
    height: 60px;
    background: #f3f4f6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
}

.property-info strong {
    display: block;
    margin-bottom: 4px;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: #6b7280;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    color: #d1d5db;
}

.action-buttons {
    display: flex;
    gap: 4px;
}

.filter-form {
    background: #f8fafc;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.filter-row {
    display: flex;
    gap: 12px;
    align-items: center;
}

.filter-row select {
    min-width: 150px;
}
</style>

<script>
function markAsSold(propertyId) {
    Swal.fire({
        title: 'Mark Property as Sold/Rented',
        input: 'select',
        inputOptions: {
            'sold': 'Sold',
            'rented': 'Rented'
        },
        inputPlaceholder: 'Select status',
        showCancelButton: true,
        confirmButtonText: 'Update',
        confirmButtonColor: '#10b981'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            // Submit form to update property status
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/seller/update_property_status.php';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'property_id';
            idInput.value = propertyId;
            
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = result.value;
            
            form.appendChild(idInput);
            form.appendChild(statusInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function deleteProperty(propertyId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This property will be permanently deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit form to delete property
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/seller/delete_property.php';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'property_id';
            idInput.value = propertyId;
            
            form.appendChild(idInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>

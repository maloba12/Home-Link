<?php
require_once dirname(__DIR__) . '/includes/db_connect.php';
require_once dirname(__DIR__) . '/includes/auth.php';

// Check if user is logged in and is an agent
if (!isLoggedIn()) {
    header('Location: /login.php');
    exit();
}

if (!isAgent()) {
    header('Location: /index.php');
    exit();
}

$pageTitle = 'Managed Properties - HomeLink';
$userId = $_SESSION['user_id'];

// Get filter parameters
$status = $_GET['status'] ?? '';
$type = $_GET['type'] ?? '';

// Build query - Agents can see all properties they manage
$sql = "SELECT p.*, u.username as seller_name,
        (SELECT image_url FROM images WHERE property_id = p.property_id AND is_primary = TRUE LIMIT 1) as primary_image,
        (SELECT COUNT(*) FROM bookings WHERE property_id = p.property_id) as booking_count
        FROM properties p 
        LEFT JOIN users u ON p.seller_id = u.user_id
        WHERE 1=1";

$params = [];

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

include 'agent_header.php';
?>

<div class="admin-layout">
    <?php include 'agent_sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-page-header">
            <h1><i class="fas fa-building"></i> Managed Properties</h1>
            <p>Properties you are managing for clients</p>
            <a href="/agent/add_property.php" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Add New Property
            </a>
        </div>
        
        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo count($properties); ?></h3>
                    <p>Total Properties</p>
                </div>
            </div>
            
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo count(array_filter($properties, fn($p) => $p['status'] === 'approved')); ?></h3>
                    <p>Active Listings</p>
                </div>
            </div>
            
            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo count(array_filter($properties, fn($p) => $p['status'] === 'pending')); ?></h3>
                    <p>Pending Approval</p>
                </div>
            </div>
            
            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo array_sum(array_column($properties, 'booking_count')); ?></h3>
                    <p>Total Bookings</p>
                </div>
            </div>
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
                    <a href="/agent/managed_properties.php" class="btn btn-outline">
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
                            <th>Seller</th>
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
                                <td colspan="8" class="text-center">
                                    <div class="empty-state">
                                        <i class="fas fa-home"></i>
                                        <p>No properties found.</p>
                                        <a href="/agent/add_property.php" class="btn btn-primary">Add Your First Property</a>
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
                                        <?php if ($property['seller_name']): ?>
                                            <span class="seller-name"><?php echo htmlspecialchars($property['seller_name']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">No seller</span>
                                        <?php endif; ?>
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
                                            <a href="/agent/edit_property.php?id=<?php echo $property['property_id']; ?>" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-info" title="View Bookings" 
                                                    onclick="viewPropertyBookings(<?php echo $property['property_id']; ?>)">
                                                <i class="fas fa-calendar-check"></i>
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

.seller-name {
    font-weight: 600;
    color: #3b82f6;
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
    flex-wrap: wrap;
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
function viewPropertyBookings(propertyId) {
    Swal.fire({
        title: 'Property Bookings',
        text: `View all bookings for property ID: ${propertyId}`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'View Bookings',
        confirmButtonColor: '#3b82f6'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `/agent/property_bookings.php?id=${propertyId}`;
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>

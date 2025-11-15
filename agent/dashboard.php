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

$pageTitle = 'Agent Dashboard - HomeLink';
$userId = $_SESSION['user_id'];

// Get agent statistics
$stats = [];

// Total managed properties (properties where agent_id = user_id)
// Note: You may need to add an agent_id column to properties table
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM properties WHERE seller_id = ?");
$stmt->execute([$userId]);
$stats['managed_properties'] = $stmt->fetch()['total'];

// Active listings
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM properties WHERE seller_id = ? AND status = 'approved'");
$stmt->execute([$userId]);
$stats['active_listings'] = $stmt->fetch()['total'];

// Total clients (unique buyers who booked agent's properties)
$stmt = $pdo->prepare("SELECT COUNT(DISTINCT b.buyer_id) as total 
                       FROM bookings b
                       JOIN properties p ON b.property_id = p.property_id
                       WHERE p.seller_id = ?");
$stmt->execute([$userId]);
$stats['total_clients'] = $stmt->fetch()['total'];

// Total deals (completed bookings)
$stmt = $pdo->prepare("SELECT COUNT(*) as total 
                       FROM bookings b
                       JOIN properties p ON b.property_id = p.property_id
                       WHERE p.seller_id = ?");
$stmt->execute([$userId]);
$stats['total_deals'] = $stmt->fetch()['total'];

// Total commission (placeholder - you'll need a commissions table)
$stats['total_commission'] = 0;

// Pending deals
$stats['pending_deals'] = $stats['total_deals']; // Placeholder

// Get managed properties
$propertiesStmt = $pdo->prepare("SELECT p.*, 
                                 (SELECT image_url FROM images WHERE property_id = p.property_id AND is_primary = TRUE LIMIT 1) as primary_image,
                                 (SELECT COUNT(*) FROM bookings WHERE property_id = p.property_id) as booking_count
                                 FROM properties p 
                                 WHERE p.seller_id = ?
                                 ORDER BY p.created_at DESC 
                                 LIMIT 6");
$propertiesStmt->execute([$userId]);
$managedProperties = $propertiesStmt->fetchAll();

// Get recent clients
$clientsStmt = $pdo->prepare("SELECT u.user_id, u.username, u.email, MAX(b.created_at) as last_contact
                               FROM users u
                               JOIN bookings b ON u.user_id = b.buyer_id
                               JOIN properties p ON b.property_id = p.property_id
                               WHERE p.seller_id = ?
                               GROUP BY u.user_id, u.username, u.email
                               ORDER BY last_contact DESC
                               LIMIT 5");
$clientsStmt->execute([$userId]);
$recentClients = $clientsStmt->fetchAll();

// Get recent deals
$dealsStmt = $pdo->prepare("SELECT b.*, p.title as property_title, p.price, u.username as client_name
                            FROM bookings b
                            JOIN properties p ON b.property_id = p.property_id
                            JOIN users u ON b.buyer_id = u.user_id
                            WHERE p.seller_id = ?
                            ORDER BY b.booking_date DESC
                            LIMIT 5");
$dealsStmt->execute([$userId]);
$recentDeals = $dealsStmt->fetchAll();

include 'agent_header.php';
?>

<div class="admin-layout">
    <?php include 'agent_sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-page-header">
            <h1><i class="fas fa-briefcase"></i> Agent Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>! Manage properties and clients efficiently.</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['managed_properties']; ?></h3>
                    <p>Managed Properties</p>
                </div>
            </div>

            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['active_listings']; ?></h3>
                    <p>Active Listings</p>
                </div>
            </div>

            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['total_clients']; ?></h3>
                    <p>Total Clients</p>
                </div>
            </div>

            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['pending_deals']; ?></h3>
                    <p>Pending Deals</p>
                </div>
            </div>

            <div class="stat-card stat-secondary">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-details">
                    <h3>K<?php echo number_format($stats['total_commission']); ?></h3>
                    <p>Total Commission</p>
                </div>
            </div>

            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['total_deals']; ?></h3>
                    <p>Completed Deals</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="admin-section">
            <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
            <div class="action-buttons-grid">
                <a href="/agent/add_property.php" class="action-btn btn-primary">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Property</span>
                </a>
                <a href="/agent/managed_properties.php" class="action-btn btn-success">
                    <i class="fas fa-building"></i>
                    <span>View Properties</span>
                </a>
                <a href="/agent/clients.php" class="action-btn btn-info">
                    <i class="fas fa-users"></i>
                    <span>Manage Clients</span>
                </a>
                <a href="/agent/deals.php" class="action-btn btn-warning">
                    <i class="fas fa-handshake"></i>
                    <span>Track Deals</span>
                </a>
            </div>
        </div>

        <!-- Managed Properties -->
        <div class="admin-section">
            <div class="section-header">
                <h2><i class="fas fa-building"></i> Managed Properties</h2>
                <a href="/agent/managed_properties.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            
            <?php if (empty($managedProperties)): ?>
                <div class="empty-state">
                    <i class="fas fa-building"></i>
                    <p>No properties managed yet.</p>
                    <a href="/agent/add_property.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Add Property
                    </a>
                </div>
            <?php else: ?>
                <div class="properties-grid">
                    <?php foreach ($managedProperties as $property): ?>
                        <div class="property-card">
                            <?php
                            $primaryImage = $property['primary_image'] ?? '';
                            if ($primaryImage && strpos($primaryImage, 'assets/') !== 0 && strpos($primaryImage, '/') !== 0) {
                                $primaryImage = 'assets/images/' . ltrim($primaryImage, '/');
                            }
                            $hasImage = false;
                            if (!empty($primaryImage)) {
                                $fsPath = dirname(__DIR__) . '/' . ltrim($primaryImage, '/');
                                if (file_exists($fsPath)) {
                                    $hasImage = true;
                                }
                            }
                            ?>
                            <?php if ($hasImage): ?>
                                <img src="<?php echo htmlspecialchars('/' . ltrim($primaryImage, '/')); ?>" alt="<?php echo htmlspecialchars($property['title']); ?>" class="property-image">
                            <?php else: ?>
                                <div class="property-image-placeholder">
                                    <i class="fas fa-home"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="property-info">
                                <h3 class="property-title"><?php echo htmlspecialchars($property['title']); ?></h3>
                                <p class="property-location">
                                    <i class="fas fa-map-marker-alt"></i> 
                                    <?php echo htmlspecialchars($property['city']); ?>
                                </p>
                                
                                <div class="property-price">
                                    <strong>K<?php echo number_format($property['price']); ?></strong>
                                    <span class="status-badge status-<?php echo $property['status']; ?>">
                                        <?php echo ucfirst($property['status']); ?>
                                    </span>
                                </div>
                                
                                <div class="property-stats">
                                    <span><i class="fas fa-eye"></i> <?php echo $property['booking_count']; ?> inquiries</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Clients -->
        <div class="admin-section">
            <div class="section-header">
                <h2><i class="fas fa-users"></i> Recent Clients</h2>
                <a href="/agent/clients.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            
            <?php if (empty($recentClients)): ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <p>No clients yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>Email</th>
                                <th>Inquiries</th>
                                <th>Last Contact</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentClients as $client): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($client['username']); ?></td>
                                    <td><?php echo htmlspecialchars($client['email']); ?></td>
                                    <td><?php echo $client['booking_count']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($client['last_contact'])); ?></td>
                                    <td>
                                        <a href="mailto:<?php echo htmlspecialchars($client['email']); ?>" class="btn btn-sm btn-primary" title="Email">
                                            <i class="fas fa-envelope"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Deals -->
        <div class="admin-section">
            <div class="section-header">
                <h2><i class="fas fa-handshake"></i> Recent Deals</h2>
                <a href="/agent/deals.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            
            <?php if (empty($recentDeals)): ?>
                <div class="empty-state">
                    <i class="fas fa-handshake"></i>
                    <p>No deals yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Client</th>
                                <th>Price</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentDeals as $deal): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($deal['property_title']); ?></td>
                                    <td><?php echo htmlspecialchars($deal['client_name']); ?></td>
                                    <td><strong>K<?php echo number_format($deal['price']); ?></strong></td>
                                    <td><?php echo date('M d, Y', strtotime($deal['booking_date'])); ?></td>
                                    <td><span class="status-badge status-pending">Pending</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>

<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
requireAdmin();

$pageTitle = 'Admin Dashboard - HomeLink';

// Get statistics
$stats = [];

$statStmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $statStmt->fetch()['total'];

$statStmt = $pdo->query("SELECT COUNT(*) as total FROM properties");
$stats['total_properties'] = $statStmt->fetch()['total'];

$statStmt = $pdo->query("SELECT COUNT(*) as total FROM properties WHERE status = 'approved'");
$stats['approved_properties'] = $statStmt->fetch()['total'];

$statStmt = $pdo->query("SELECT COUNT(*) as total FROM properties WHERE status = 'pending'");
$stats['pending_properties'] = $statStmt->fetch()['total'];

$statStmt = $pdo->query("SELECT COUNT(*) as total FROM bookings");
$stats['total_bookings'] = $statStmt->fetch()['total'];

$statStmt = $pdo->query("SELECT SUM(price) as total FROM properties WHERE status = 'approved'");
$totalValue = $statStmt->fetch()['total'] ?? 0;
$stats['total_value'] = $totalValue;

// Get data for charts
$roleStmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$roleData = $roleStmt->fetchAll(PDO::FETCH_KEY_PAIR);

$statusStmt = $pdo->query("SELECT status, COUNT(*) as count FROM properties GROUP BY status");
$statusData = $statusStmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Get recent properties
$recentStmt = $pdo->query("SELECT p.*, u.username as seller_name 
                           FROM properties p 
                           LEFT JOIN users u ON p.seller_id = u.user_id 
                           ORDER BY p.created_at DESC 
                           LIMIT 5");
$recentProperties = $recentStmt->fetchAll();

include '../includes/admin_header.php';
?>

<div class="admin-layout">
    <?php include 'admin_sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-page-header">
            <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['total_users']; ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['total_properties']; ?></h3>
                    <p>Total Properties</p>
                </div>
            </div>
            
            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['approved_properties']; ?></h3>
                    <p>Approved</p>
                </div>
            </div>
            
            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['pending_properties']; ?></h3>
                    <p>Pending Approval</p>
                </div>
            </div>
            
            <div class="stat-card stat-secondary">
                <div class="stat-icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['total_bookings']; ?></h3>
                    <p>Total Bookings</p>
                </div>
            </div>
            
            <div class="stat-card stat-danger">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-details">
                    <h3>K<?php echo number_format($stats['total_value']); ?></h3>
                    <p>Total Property Value</p>
                </div>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="charts-grid">
            <div class="chart-card">
                <h3><i class="fas fa-users"></i> Users by Role</h3>
                <canvas id="usersChart"></canvas>
            </div>
            
            <div class="chart-card">
                <h3><i class="fas fa-building"></i> Properties by Status</h3>
                <canvas id="propertiesChart"></canvas>
            </div>
        </div>
        
        <!-- Recent Properties Table -->
        <div class="admin-section">
            <h2><i class="fas fa-clock"></i> Recent Properties</h2>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Seller</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentProperties)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No properties yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentProperties as $property): ?>
                                <tr>
                                    <td><?php echo $property['property_id']; ?></td>
                                    <td><?php echo htmlspecialchars($property['title']); ?></td>
                                    <td><?php echo htmlspecialchars($property['seller_name']); ?></td>
                                    <td><span class="badge"><?php echo ucfirst($property['type']); ?></span></td>
                                    <td>K<?php echo number_format($property['price']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $property['status']; ?>">
                                            <?php echo ucfirst($property['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($property['created_at'])); ?></td>
                                    <td>
                                        <a href="../property_details.php?id=<?php echo $property['property_id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
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

<script>
// Users by Role Chart
const usersCtx = document.getElementById('usersChart').getContext('2d');
new Chart(usersCtx, {
    type: 'doughnut',
    data: {
        labels: ['Buyers', 'Sellers', 'Admins'],
        datasets: [{
            data: [
                <?php echo $roleData['buyer'] ?? 0; ?>,
                <?php echo $roleData['seller'] ?? 0; ?>,
                <?php echo $roleData['admin'] ?? 0; ?>
            ],
            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Properties by Status Chart
const propertiesCtx = document.getElementById('propertiesChart').getContext('2d');
new Chart(propertiesCtx, {
    type: 'bar',
    data: {
        labels: ['Approved', 'Pending', 'Rejected'],
        datasets: [{
            label: 'Properties',
            data: [
                <?php echo $statusData['approved'] ?? 0; ?>,
                <?php echo $statusData['pending'] ?? 0; ?>,
                <?php echo $statusData['rejected'] ?? 0; ?>
            ],
            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

<?php include '../includes/admin_footer.php'; ?>


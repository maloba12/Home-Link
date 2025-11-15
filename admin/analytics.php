<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
requireAdmin();

$pageTitle = 'Analytics - HomeLink';

// Get statistics
$stats = [];

// Total users by role
$roleStats = [];
$roleStmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
foreach ($roleStmt->fetchAll() as $row) {
    $roleStats[$row['role']] = $row['count'];
}

// Properties by status
$statusStmt = $pdo->query("SELECT status, COUNT(*) as count FROM properties GROUP BY status");
$statusStats = $statusStmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Properties by type
$typeStmt = $pdo->query("SELECT type, COUNT(*) as count FROM properties GROUP BY type");
$typeStats = $typeStmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Top sellers
$topSellersStmt = $pdo->query("SELECT u.username, COUNT(p.property_id) as property_count
                               FROM users u
                               LEFT JOIN properties p ON u.user_id = p.seller_id
                               WHERE u.role = 'seller'
                               GROUP BY u.user_id
                               ORDER BY property_count DESC
                               LIMIT 5");
$topSellers = $topSellersStmt->fetchAll();

include '../includes/admin_header.php';
?>

<div class="admin-layout">
    <?php include 'admin_sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-page-header">
            <h1><i class="fas fa-chart-line"></i> Analytics</h1>
            <p>Detailed insights and statistics</p>
        </div>
    
    <!-- Charts Grid -->
    <div class="charts-grid">
        <div class="chart-card">
            <h3><i class="fas fa-users"></i> Users by Role</h3>
            <canvas id="usersRoleChart"></canvas>
        </div>
        
        <div class="chart-card">
            <h3><i class="fas fa-building"></i> Properties by Status</h3>
            <canvas id="propertiesStatusChart"></canvas>
        </div>
        
        <div class="chart-card">
            <h3><i class="fas fa-tag"></i> Properties by Type</h3>
            <canvas id="propertiesTypeChart"></canvas>
        </div>
        
        <div class="chart-card">
            <h3><i class="fas fa-trophy"></i> Top Sellers</h3>
            <div class="top-sellers-list">
                <?php if (empty($topSellers)): ?>
                    <p class="text-center text-muted">No sellers yet.</p>
                <?php else: ?>
                    <?php foreach ($topSellers as $index => $seller): ?>
                        <div class="seller-item">
                            <span class="rank">#<?php echo $index + 1; ?></span>
                            <span class="name"><?php echo htmlspecialchars($seller['username']); ?></span>
                            <span class="count badge"><?php echo $seller['property_count']; ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    </main>
</div>

<script>
// Users by Role Chart
const usersRoleCtx = document.getElementById('usersRoleChart').getContext('2d');
new Chart(usersRoleCtx, {
    type: 'pie',
    data: {
        labels: ['Buyers', 'Sellers', 'Admins'],
        datasets: [{
            data: [
                <?php echo $roleStats['buyer'] ?? 0; ?>,
                <?php echo $roleStats['seller'] ?? 0; ?>,
                <?php echo $roleStats['admin'] ?? 0; ?>
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
const propertiesStatusCtx = document.getElementById('propertiesStatusChart').getContext('2d');
new Chart(propertiesStatusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Approved', 'Pending', 'Rejected'],
        datasets: [{
            data: [
                <?php echo $statusStats['approved'] ?? 0; ?>,
                <?php echo $statusStats['pending'] ?? 0; ?>,
                <?php echo $statusStats['rejected'] ?? 0; ?>
            ],
            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
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

// Properties by Type Chart
const propertiesTypeCtx = document.getElementById('propertiesTypeChart').getContext('2d');
new Chart(propertiesTypeCtx, {
    type: 'bar',
    data: {
        labels: ['For Rent', 'For Sale'],
        datasets: [{
            label: 'Properties',
            data: [
                <?php echo $typeStats['rent'] ?? 0; ?>,
                <?php echo $typeStats['sale'] ?? 0; ?>
            ],
            backgroundColor: ['#8b5cf6', '#ec4899'],
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

<?php include '../includes/footer.php'; ?>


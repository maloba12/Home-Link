<?php
require_once dirname(__DIR__) . '/includes/db_connect.php';
require_once dirname(__DIR__) . '/includes/auth.php';

// Check if user is logged in and is a seller
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if (!isSeller()) {
    header('Location: index.php');
    exit();
}

$pageTitle = 'Analytics - HomeLink';
$userId = $_SESSION['user_id'];

// Get analytics data
$analytics = [];

// Properties by status
$stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM properties WHERE seller_id = ? GROUP BY status");
$stmt->execute([$userId]);
$propertyStatusData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Properties by type
$stmt = $pdo->prepare("SELECT property_type, COUNT(*) as count FROM properties WHERE seller_id = ? GROUP BY property_type");
$stmt->execute([$userId]);
$propertyTypeData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Monthly property submissions (last 6 months)
$stmt = $pdo->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
                       FROM properties WHERE seller_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                       GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month");
$stmt->execute([$userId]);
$monthlySubmissions = $stmt->fetchAll();

// Monthly bookings (last 6 months)
$stmt = $pdo->prepare("SELECT DATE_FORMAT(b.created_at, '%Y-%m') as month, COUNT(*) as count 
                       FROM bookings b
                       JOIN properties p ON b.property_id = p.property_id
                       WHERE p.seller_id = ? AND b.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                       GROUP BY DATE_FORMAT(b.created_at, '%Y-%m') ORDER BY month");
$stmt->execute([$userId]);
$monthlyBookings = $stmt->fetchAll();

// Top performing properties (by bookings)
$stmt = $pdo->prepare("SELECT p.property_id, p.title, p.price, COUNT(b.booking_id) as booking_count
                       FROM properties p
                       LEFT JOIN bookings b ON p.property_id = b.property_id
                       WHERE p.seller_id = ?
                       GROUP BY p.property_id, p.title, p.price
                       ORDER BY booking_count DESC, p.created_at DESC
                       LIMIT 5");
$stmt->execute([$userId]);
$topProperties = $stmt->fetchAll();

// Total views simulation (since we don't have views table, we'll use booking count as proxy)
$stmt = $pdo->prepare("SELECT COUNT(*) as total_views FROM properties WHERE seller_id = ?");
$stmt->execute([$userId]);
$totalViews = $stmt->fetch()['total_views'] * 10; // Simulate 10x bookings as views

// Average property price
$stmt = $pdo->prepare("SELECT AVG(price) as avg_price FROM properties WHERE seller_id = ? AND status = 'approved'");
$stmt->execute([$userId]);
$avgPrice = $stmt->fetch()['avg_price'] ?? 0;

// Total property value
$stmt = $pdo->prepare("SELECT SUM(price) as total_value FROM properties WHERE seller_id = ? AND status = 'approved'");
$stmt->execute([$userId]);
$totalValue = $stmt->fetch()['total_value'] ?? 0;

include 'seller_header.php';
?>

<div class="admin-layout">
    <?php include 'seller_sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-page-header">
            <h1><i class="fas fa-chart-line"></i> Analytics</h1>
            <p>Track your property performance and business insights</p>
        </div>
        
        <!-- Key Metrics -->
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo number_format($totalViews); ?></h3>
                    <p>Total Views</p>
                </div>
            </div>
            
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo array_sum($propertyStatusData); ?></h3>
                    <p>Total Properties</p>
                </div>
            </div>
            
            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $propertyStatusData['approved'] ?? 0; ?></h3>
                    <p>Active Listings</p>
                </div>
            </div>
            
            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-details">
                    <h3>K<?php echo number_format($avgPrice, 0); ?></h3>
                    <p>Average Price</p>
                </div>
            </div>
            
            <div class="stat-card stat-secondary">
                <div class="stat-icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div class="stat-details">
                    <h3>K<?php echo number_format($totalValue); ?></h3>
                    <p>Total Value</p>
                </div>
            </div>
            
            <div class="stat-card stat-danger">
                <div class="stat-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-details">
                    <h3><?php 
                    $approved = $propertyStatusData['approved'] ?? 0;
                    $total = array_sum($propertyStatusData);
                    $approvalRate = $total > 0 ? round(($approved / $total) * 100, 1) : 0;
                    echo $approvalRate; ?>%
                    </h3>
                    <p>Approval Rate</p>
                </div>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="charts-grid">
            <div class="chart-card">
                <h3><i class="fas fa-chart-pie"></i> Properties by Status</h3>
                <canvas id="statusChart"></canvas>
            </div>
            
            <div class="chart-card">
                <h3><i class="fas fa-chart-bar"></i> Properties by Type</h3>
                <canvas id="typeChart"></canvas>
            </div>
        </div>
        
        <div class="charts-grid">
            <div class="chart-card">
                <h3><i class="fas fa-chart-line"></i> Monthly Submissions</h3>
                <canvas id="submissionsChart"></canvas>
            </div>
            
            <div class="chart-card">
                <h3><i class="fas fa-calendar-alt"></i> Monthly Bookings</h3>
                <canvas id="bookingsChart"></canvas>
            </div>
        </div>
        
        <!-- Top Performing Properties -->
        <div class="admin-section">
            <h2><i class="fas fa-trophy"></i> Top Performing Properties</h2>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Property Title</th>
                            <th>Price</th>
                            <th>Bookings</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topProperties)): ?>
                            <tr>
                                <td colspan="4" class="text-center">No properties with performance data yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($topProperties as $property): ?>
                                <tr>
                                    <td>
                                        <a href="../property_details.php?id=<?php echo $property['property_id']; ?>" class="property-link">
                                            <?php echo htmlspecialchars($property['title']); ?>
                                        </a>
                                    </td>
                                    <td><strong>K<?php echo number_format($property['price']); ?></strong></td>
                                    <td>
                                        <span class="badge badge-primary"><?php echo $property['booking_count']; ?></span>
                                    </td>
                                    <td>
                                        <?php if ($property['booking_count'] > 5): ?>
                                            <span class="performance-badge excellent">Excellent</span>
                                        <?php elseif ($property['booking_count'] > 2): ?>
                                            <span class="performance-badge good">Good</span>
                                        <?php elseif ($property['booking_count'] > 0): ?>
                                            <span class="performance-badge fair">Fair</span>
                                        <?php else: ?>
                                            <span class="performance-badge poor">No Bookings</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Insights Section -->
        <div class="admin-section">
            <h2><i class="fas fa-lightbulb"></i> Business Insights</h2>
            <div class="insights-grid">
                <div class="insight-card">
                    <div class="insight-icon">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div class="insight-content">
                        <h4>Listing Performance</h4>
                        <p>Your approval rate is <?php echo $approvalRate; ?>%. 
                        <?php if ($approvalRate >= 80): ?>
                            Great job! Your listings meet quality standards.
                        <?php elseif ($approvalRate >= 60): ?>
                            Good performance. Consider improving property details and photos.
                        <?php else: ?>
                            Focus on better property descriptions and images to improve approval rate.
                        <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <div class="insight-card">
                    <div class="insight-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="insight-content">
                        <h4>Property Portfolio</h4>
                        <p>You have <?php echo array_sum($propertyStatusData); ?> properties with 
                        <?php echo $propertyStatusData['approved'] ?? 0; ?> active listings. 
                        <?php if (($propertyStatusData['approved'] ?? 0) < 3): ?>
                            Consider adding more properties to increase visibility.
                        <?php else: ?>
                            Your portfolio is well diversified.
                        <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <div class="insight-card">
                    <div class="insight-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="insight-content">
                        <h4>Price Strategy</h4>
                        <p>Your average property price is K<?php echo number_format($avgPrice, 0); ?>.
                        <?php if ($avgPrice > 1000000): ?>
                            You're focusing on premium properties.
                        <?php elseif ($avgPrice > 500000): ?>
                            Good mid-range pricing strategy.
                        <?php else: ?>
                            You're targeting affordable housing market.
                        <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
.performance-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.performance-badge.excellent {
    background-color: #10b981;
    color: white;
}

.performance-badge.good {
    background-color: #3b82f6;
    color: white;
}

.performance-badge.fair {
    background-color: #f59e0b;
    color: white;
}

.performance-badge.poor {
    background-color: #ef4444;
    color: white;
}

.property-link {
    color: #3b82f6;
    text-decoration: none;
}

.property-link:hover {
    text-decoration: underline;
}

.insights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.insight-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.insight-icon {
    background: #3b82f6;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.insight-content h4 {
    margin: 0 0 8px 0;
    color: #1f2937;
    font-size: 16px;
}

.insight-content p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
    line-height: 1.5;
}
</style>

<script>
// Properties by Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Approved', 'Pending', 'Rejected'],
        datasets: [{
            data: [
                <?php echo $propertyStatusData['approved'] ?? 0; ?>,
                <?php echo $propertyStatusData['pending'] ?? 0; ?>,
                <?php echo $propertyStatusData['rejected'] ?? 0; ?>
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
const typeCtx = document.getElementById('typeChart').getContext('2d');
new Chart(typeCtx, {
    type: 'bar',
    data: {
        labels: ['Apartment', 'House', 'Condo', 'Townhouse', 'Studio'],
        datasets: [{
            label: 'Properties',
            data: [
                <?php echo $propertyTypeData['apartment'] ?? 0; ?>,
                <?php echo $propertyTypeData['house'] ?? 0; ?>,
                <?php echo $propertyTypeData['condo'] ?? 0; ?>,
                <?php echo $propertyTypeData['townhouse'] ?? 0; ?>,
                <?php echo $propertyTypeData['studio'] ?? 0; ?>
            ],
            backgroundColor: '#3b82f6',
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

// Monthly Submissions Chart
const submissionsCtx = document.getElementById('submissionsChart').getContext('2d');
new Chart(submissionsCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($monthlySubmissions, 'month')); ?>,
        datasets: [{
            label: 'Properties Submitted',
            data: <?php echo json_encode(array_column($monthlySubmissions, 'count')); ?>,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            fill: true
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

// Monthly Bookings Chart
const bookingsCtx = document.getElementById('bookingsChart').getContext('2d');
new Chart(bookingsCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($monthlyBookings, 'month')); ?>,
        datasets: [{
            label: 'Bookings Received',
            data: <?php echo json_encode(array_column($monthlyBookings, 'count')); ?>,
            borderColor: '#f59e0b',
            backgroundColor: 'rgba(245, 158, 11, 0.1)',
            tension: 0.4,
            fill: true
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

<?php include '../includes/seller_footer.php'; ?>

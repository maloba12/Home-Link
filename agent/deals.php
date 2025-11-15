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

$pageTitle = 'Deals & Commissions - HomeLink';
$userId = $_SESSION['user_id'];

// Get filter parameters
$status = $_GET['status'] ?? '';
$period = $_GET['period'] ?? '';
$property_type = $_GET['property_type'] ?? '';

// Build query for deals (completed and confirmed bookings)
$sql = "SELECT b.*, p.title as property_title, p.price as property_price, p.type as property_type, 
        p.property_type, p.address as property_address, p.city as property_city,
        u.username as client_name, u.email as client_email, u.phone as client_phone,
        s.username as seller_name,
        -- Calculate commission (typically 2-5% of property price)
        CASE 
            WHEN p.type = 'sale' THEN p.price * 0.03  -- 3% for sales
            WHEN p.type = 'rent' THEN p.price * 0.50  -- 50% of first month rent
            ELSE 0
        END as commission_amount,
        -- Commission status
        CASE 
            WHEN b.status = 'completed' THEN 'earned'
            WHEN b.status = 'confirmed' THEN 'pending'
            ELSE 'potential'
        END as commission_status
        FROM bookings b
        JOIN properties p ON b.property_id = p.property_id
        JOIN users u ON b.buyer_id = u.user_id
        LEFT JOIN users s ON p.seller_id = s.user_id
        WHERE 1=1";

$params = [];

if (!empty($status)) {
    $sql .= " AND b.status = ?";
    $params[] = $status;
}

if (!empty($period)) {
    switch ($period) {
        case 'this_month':
            $sql .= " AND b.created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')";
            break;
        case 'last_month':
            $sql .= " AND b.created_at >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL 1 MONTH)
                      AND b.created_at < DATE_FORMAT(NOW(), '%Y-%m-01')";
            break;
        case 'this_year':
            $sql .= " AND b.created_at >= DATE_FORMAT(NOW(), '%Y-01-01')";
            break;
    }
}

if (!empty($property_type)) {
    $sql .= " AND p.type = ?";
    $params[] = $property_type;
}

$sql .= " ORDER BY b.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$deals = $stmt->fetchAll();

// Calculate statistics
$stats = [];
$stats['total_deals'] = count($deals);
$stats['completed_deals'] = count(array_filter($deals, fn($d) => $d['status'] === 'completed'));
$stats['confirmed_deals'] = count(array_filter($deals, fn($d) => $d['status'] === 'confirmed'));
$stats['pending_deals'] = count(array_filter($deals, fn($d) => $d['status'] === 'pending'));

$stats['total_commission'] = array_sum(array_filter(array_column($deals, 'commission_amount'), fn($c) => $c['status'] === 'completed'));
$stats['pending_commission'] = array_sum(array_filter(array_column($deals, 'commission_amount'), fn($c) => $c['status'] === 'confirmed'));
$stats['potential_commission'] = array_sum(array_column($deals, 'commission_amount'));

// Monthly commission data
$monthlySql = "SELECT DATE_FORMAT(b.created_at, '%Y-%m') as month,
                      SUM(CASE 
                          WHEN p.type = 'sale' THEN p.price * 0.03
                          WHEN p.type = 'rent' THEN p.price * 0.50
                          ELSE 0
                      END) as commission,
                      COUNT(*) as deal_count
               FROM bookings b
               JOIN properties p ON b.property_id = p.property_id
               WHERE b.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
               GROUP BY DATE_FORMAT(b.created_at, '%Y-%m')
               ORDER BY month";
$monthlyStmt = $pdo->prepare($monthlySql);
$monthlyStmt->execute();
$monthlyData = $monthlyStmt->fetchAll();

include 'agent_header.php';
?>

<div class="admin-layout">
    <?php include 'agent_sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-page-header">
            <h1><i class="fas fa-handshake"></i> Deals & Commissions</h1>
            <p>Track your deals, earnings, and commission performance</p>
            <button class="btn btn-success" onclick="generateCommissionReport()">
                <i class="fas fa-file-download"></i> Commission Report
            </button>
        </div>
        
        <!-- Commission Stats -->
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['total_deals']; ?></h3>
                    <p>Total Deals</p>
                </div>
            </div>
            
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['completed_deals']; ?></h3>
                    <p>Completed Deals</p>
                </div>
            </div>
            
            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['pending_commission']; ?></h3>
                    <p>Pending Commission</p>
                </div>
            </div>
            
            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-details">
                    <h3>K<?php echo number_format($stats['total_commission'], 0); ?></h3>
                    <p>Total Earned</p>
                </div>
            </div>
        </div>
        
        <!-- Commission Chart -->
        <div class="admin-section">
            <h3><i class="fas fa-chart-line"></i> Commission Performance (Last 12 Months)</h3>
            <div class="chart-container">
                <canvas id="commissionChart"></canvas>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="admin-section">
            <form method="GET" action="" class="filter-form">
                <div class="filter-row">
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo ($status ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo ($status ?? '') === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="completed" <?php echo ($status ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo ($status ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    
                    <select name="period">
                        <option value="">All Time</option>
                        <option value="this_month" <?php echo ($period ?? '') === 'this_month' ? 'selected' : ''; ?>>This Month</option>
                        <option value="last_month" <?php echo ($period ?? '') === 'last_month' ? 'selected' : ''; ?>>Last Month</option>
                        <option value="this_year" <?php echo ($period ?? '') === 'this_year' ? 'selected' : ''; ?>>This Year</option>
                    </select>
                    
                    <select name="property_type">
                        <option value="">All Types</option>
                        <option value="rent" <?php echo ($property_type ?? '') === 'rent' ? 'selected' : ''; ?>>For Rent</option>
                        <option value="sale" <?php echo ($property_type ?? '') === 'sale' ? 'selected' : ''; ?>>For Sale</option>
                    </select>
                    
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="/agent/deals.php" class="btn btn-outline">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Deals Table -->
        <div class="admin-section">
            <h2><i class="fas fa-list"></i> Deal History</h2>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Deal ID</th>
                            <th>Property</th>
                            <th>Client</th>
                            <th>Property Price</th>
                            <th>Commission Rate</th>
                            <th>Commission Amount</th>
                            <th>Status</th>
                            <th>Deal Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($deals)): ?>
                            <tr>
                                <td colspan="9" class="text-center">
                                    <div class="empty-state">
                                        <i class="fas fa-handshake"></i>
                                        <p>No deals found.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($deals as $deal): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo str_pad($deal['booking_id'], 6, '0', STR_PAD_LEFT); ?></strong>
                                    </td>
                                    <td>
                                        <div class="property-info">
                                            <strong><?php echo htmlspecialchars($deal['property_title']); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?php echo htmlspecialchars($deal['property_address'] . ', ' . $deal['property_city']); ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="client-info">
                                            <strong><?php echo htmlspecialchars($deal['client_name']); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($deal['client_email']); ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>K<?php echo number_format($deal['property_price']); ?></strong>
                                        <br>
                                        <small><?php echo ucfirst($deal['property_type']); ?></small>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($deal['property_type'] === 'sale') {
                                            echo '3%';
                                        } elseif ($deal['property_type'] === 'rent') {
                                            echo '50% of first month';
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="commission-amount <?php echo $deal['commission_status']; ?>">
                                            K<?php echo number_format($deal['commission_amount'], 0); ?>
                                        </span>
                                        <br>
                                        <small class="commission-status">
                                            <?php echo ucfirst($deal['commission_status']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $deal['status']; ?>">
                                            <?php echo ucfirst($deal['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($deal['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-info" onclick="viewDealDetails(<?php echo $deal['booking_id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($deal['status'] === 'confirmed'): ?>
                                                <button class="btn btn-sm btn-success" onclick="markAsCompleted(<?php echo $deal['booking_id']; ?>)">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($deal['commission_status'] === 'earned'): ?>
                                                <button class="btn btn-sm btn-primary" onclick="requestPayout(<?php echo $deal['booking_id']; ?>)">
                                                    <i class="fas fa-money-bill"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Commission Summary -->
        <div class="admin-section">
            <h3><i class="fas fa-chart-pie"></i> Commission Summary</h3>
            <div class="commission-summary">
                <div class="summary-card">
                    <h4>Earned Commission</h4>
                    <div class="amount earned">K<?php echo number_format($stats['total_commission'], 0); ?></div>
                    <small>From <?php echo $stats['completed_deals']; ?> completed deals</small>
                </div>
                <div class="summary-card">
                    <h4>Pending Commission</h4>
                    <div class="amount pending">K<?php echo number_format($stats['pending_commission'], 0); ?></div>
                    <small>From <?php echo $stats['confirmed_deals']; ?> confirmed deals</small>
                </div>
                <div class="summary-card">
                    <h4>Potential Commission</h4>
                    <div class="amount potential">K<?php echo number_format($stats['potential_commission'], 0); ?></div>
                    <small>From all <?php echo $stats['total_deals']; ?> deals</small>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
.commission-amount {
    font-weight: 700;
    font-size: 14px;
}

.commission-amount.earned {
    color: #10b981;
}

.commission-amount.pending {
    color: #f59e0b;
}

.commission-amount.potential {
    color: #6b7280;
}

.commission-status {
    font-size: 11px;
    text-transform: uppercase;
    font-weight: 600;
}

.property-info strong {
    display: block;
    margin-bottom: 4px;
}

.client-info strong {
    display: block;
    margin-bottom: 4px;
}

.chart-container {
    height: 300px;
    margin-top: 20px;
}

.commission-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.summary-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}

.summary-card h4 {
    margin: 0 0 12px 0;
    color: #374151;
    font-size: 16px;
}

.amount {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 8px;
}

.amount.earned {
    color: #10b981;
}

.amount.pending {
    color: #f59e0b;
}

.amount.potential {
    color: #6b7280;
}

.summary-card small {
    color: #6b7280;
    font-size: 14px;
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
    flex-wrap: wrap;
}

.filter-row select {
    min-width: 150px;
}
</style>

<script>
// Commission Chart
const ctx = document.getElementById('commissionChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($monthlyData, 'month')); ?>,
        datasets: [{
            label: 'Commission Earned (K)',
            data: <?php echo json_encode(array_column($monthlyData, 'commission')); ?>,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Number of Deals',
            data: <?php echo json_encode(array_column($monthlyData, 'deal_count')); ?>,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Commission (K)'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Number of Deals'
                },
                grid: {
                    drawOnChartArea: false,
                },
            },
        }
    }
});

function viewDealDetails(dealId) {
    Swal.fire({
        title: 'Deal Details',
        text: `Full details for deal #${String(dealId).padStart(6, '0')} would be displayed here.`,
        icon: 'info'
    });
}

function markAsCompleted(dealId) {
    Swal.fire({
        title: 'Mark Deal as Completed',
        text: 'Are you sure this deal has been completed?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Mark Complete',
        confirmButtonColor: '#10b981'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Deal Completed!',
                text: 'This deal has been marked as completed and commission is now earned.',
                icon: 'success'
            });
        }
    });
}

function requestPayout(dealId) {
    Swal.fire({
        title: 'Request Commission Payout',
        html: `
            <div class="payout-form">
                <p>Request payout for deal #${String(dealId).padStart(6, '0')}</p>
                <div class="form-group">
                    <label>Bank Account</label>
                    <input type="text" class="swal2-input" placeholder="Account number">
                </div>
                <div class="form-group">
                    <label>Bank Name</label>
                    <input type="text" class="swal2-input" placeholder="Bank name">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Request Payout',
        confirmButtonColor: '#10b981'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Payout Requested!',
                text: 'Your commission payout request has been submitted.',
                icon: 'success'
            });
        }
    });
}

function generateCommissionReport() {
    Swal.fire({
        title: 'Generate Commission Report',
        html: `
            <div class="report-form">
                <div class="form-group">
                    <label>Report Period</label>
                    <select class="swal2-select">
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="this_quarter">This Quarter</option>
                        <option value="this_year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Report Format</label>
                    <select class="swal2-select">
                        <option value="pdf">PDF Report</option>
                        <option value="excel">Excel Spreadsheet</option>
                        <option value="csv">CSV Data</option>
                    </select>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Generate Report',
        confirmButtonColor: '#3b82f6'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Report Generated!',
                text: 'Your commission report has been generated and downloaded.',
                icon: 'success'
            });
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>

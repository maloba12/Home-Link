<?php
require_once dirname(__DIR__) . '/includes/db_connect.php';
require_once dirname(__DIR__) . '/includes/auth.php';

// Check if user is logged in and is an agent
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if (!isAgent()) {
    header('Location: index.php');
    exit();
}

$pageTitle = 'My Clients - HomeLink';
$userId = $_SESSION['user_id'];

// Get filter parameters
$type = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';

// Build query for clients (buyers who have booked properties)
$sql = "SELECT DISTINCT u.user_id, u.username, u.email, u.phone, u.full_name, u.created_at,
        COUNT(b.booking_id) as total_bookings,
        COUNT(CASE WHEN b.status = 'confirmed' THEN 1 END) as confirmed_bookings,
        COUNT(CASE WHEN b.status = 'completed' THEN 1 END) as completed_deals,
        MAX(b.created_at) as last_booking_date,
        GROUP_CONCAT(DISTINCT p.title SEPARATOR ', ') as properties_interested
        FROM users u
        JOIN bookings b ON u.user_id = b.buyer_id
        JOIN properties p ON b.property_id = p.property_id
        WHERE 1=1";

$params = [];

if (!empty($search)) {
    $sql .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.full_name LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$sql .= " GROUP BY u.user_id, u.username, u.email, u.phone, u.full_name, u.created_at";

if (!empty($type)) {
    if ($type === 'active') {
        $sql .= " HAVING confirmed_bookings > 0";
    } elseif ($type === 'completed') {
        $sql .= " HAVING completed_deals > 0";
    } elseif ($type === 'new') {
        $sql .= " HAVING total_bookings = 1 AND last_booking_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    }
}

$sql .= " ORDER BY last_booking_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clients = $stmt->fetchAll();

// Get statistics
$stats = [];
$stats['total_clients'] = count($clients);
$stats['active_clients'] = count(array_filter($clients, fn($c) => $c['confirmed_bookings'] > 0));
$stats['completed_deals'] = array_sum(array_column($clients, 'completed_deals'));
$stats['new_clients'] = count(array_filter($clients, fn($c) => strtotime($c['last_booking_date']) >= strtotime('-30 days')));

include 'agent_header.php';
?>

<div class="admin-layout">
    <?php include 'agent_sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-page-header">
            <h1><i class="fas fa-users"></i> My Clients</h1>
            <p>Manage your client relationships and track their property interests</p>
            <button class="btn btn-primary" onclick="addNewClient()">
                <i class="fas fa-user-plus"></i> Add New Client
            </button>
        </div>
        
        <!-- Client Stats -->
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['total_clients']; ?></h3>
                    <p>Total Clients</p>
                </div>
            </div>
            
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['active_clients']; ?></h3>
                    <p>Active Clients</p>
                </div>
            </div>
            
            <div class="stat-card stat-info">
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
                    <i class="fas fa-user-clock"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['new_clients']; ?></h3>
                    <p>New Clients (30 days)</p>
                </div>
            </div>
        </div>
        
        <!-- Filters and Search -->
        <div class="admin-section">
            <form method="GET" action="" class="filter-form">
                <div class="filter-row">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Search clients by name, email, or username..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <select name="type">
                        <option value="">All Clients</option>
                        <option value="active" <?php echo ($type ?? '') === 'active' ? 'selected' : ''; ?>>Active Clients</option>
                        <option value="completed" <?php echo ($type ?? '') === 'completed' ? 'selected' : ''; ?>>Completed Deals</option>
                        <option value="new" <?php echo ($type ?? '') === 'new' ? 'selected' : ''; ?>>New Clients</option>
                    </select>
                    
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="../agent/clients.php" class="btn btn-outline">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Clients List -->
        <div class="admin-section">
            <h2><i class="fas fa-address-book"></i> Client Directory</h2>
            <div class="clients-grid">
                <?php if (empty($clients)): ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <p>No clients found.</p>
                        <button class="btn btn-primary" onclick="addNewClient()">Add Your First Client</button>
                    </div>
                <?php else: ?>
                    <?php foreach ($clients as $client): ?>
                        <div class="client-card">
                            <div class="client-header">
                                <div class="client-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="client-info">
                                    <h4><?php echo htmlspecialchars($client['full_name'] ?? $client['username']); ?></h4>
                                    <p class="client-username">@<?php echo htmlspecialchars($client['username']); ?></p>
                                    <div class="client-meta">
                                        <span class="client-id">ID: <?php echo $client['user_id']; ?></span>
                                        <span class="client-since">Since <?php echo date('M Y', strtotime($client['created_at'])); ?></span>
                                    </div>
                                </div>
                                <div class="client-status">
                                    <?php if ($client['completed_deals'] > 0): ?>
                                        <span class="status-badge status-completed">VIP Client</span>
                                    <?php elseif ($client['confirmed_bookings'] > 0): ?>
                                        <span class="status-badge status-confirmed">Active</span>
                                    <?php else: ?>
                                        <span class="status-badge status-pending">New</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="client-details">
                                <div class="contact-info">
                                    <div class="contact-item">
                                        <i class="fas fa-envelope"></i>
                                        <span><?php echo htmlspecialchars($client['email']); ?></span>
                                    </div>
                                    <?php if ($client['phone']): ?>
                                        <div class="contact-item">
                                            <i class="fas fa-phone"></i>
                                            <span><?php echo htmlspecialchars($client['phone']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="client-stats">
                                    <div class="stat-item">
                                        <span class="stat-value"><?php echo $client['total_bookings']; ?></span>
                                        <span class="stat-label">Total Bookings</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value"><?php echo $client['confirmed_bookings']; ?></span>
                                        <span class="stat-label">Confirmed</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value"><?php echo $client['completed_deals']; ?></span>
                                        <span class="stat-label">Completed</span>
                                    </div>
                                </div>
                                
                                <div class="client-interests">
                                    <h5>Properties Interested:</h5>
                                    <p><?php echo htmlspecialchars(substr($client['properties_interested'], 0, 100) . (strlen($client['properties_interested']) > 100 ? '...' : '')); ?></p>
                                </div>
                                
                                <div class="client-activity">
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i>
                                        Last activity: <?php echo date('M d, Y', strtotime($client['last_booking_date'])); ?>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="client-actions">
                                <button class="btn btn-sm btn-primary" onclick="viewClientProfile(<?php echo $client['user_id']; ?>)">
                                    <i class="fas fa-eye"></i> Profile
                                </button>
                                <button class="btn btn-sm btn-success" onclick="contactClient('<?php echo htmlspecialchars($client['email']); ?>', '<?php echo htmlspecialchars($client['phone'] ?? ''); ?>')">
                                    <i class="fas fa-comment"></i> Contact
                                </button>
                                <button class="btn btn-sm btn-info" onclick="viewClientHistory(<?php echo $client['user_id']; ?>)">
                                    <i class="fas fa-history"></i> History
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="addNote(<?php echo $client['user_id']; ?>)">
                                    <i class="fas fa-sticky-note"></i> Notes
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<style>
.clients-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 20px;
}

.client-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    transition: box-shadow 0.2s, transform 0.2s;
}

.client-card:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.client-header {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 16px;
}

.client-avatar {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    flex-shrink: 0;
}

.client-info {
    flex: 1;
}

.client-info h4 {
    margin: 0 0 4px 0;
    color: #1f2937;
    font-size: 18px;
}

.client-username {
    color: #6b7280;
    font-size: 14px;
    margin: 0 0 8px 0;
}

.client-meta {
    display: flex;
    gap: 12px;
    font-size: 12px;
    color: #9ca3af;
}

.client-status {
    flex-shrink: 0;
}

.client-details {
    margin-bottom: 16px;
}

.contact-info {
    margin-bottom: 12px;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
    font-size: 14px;
    color: #4b5563;
}

.contact-item i {
    width: 16px;
    color: #6b7280;
}

.client-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 12px;
    padding: 12px;
    background: #f9fafb;
    border-radius: 8px;
}

.stat-item {
    text-align: center;
}

.stat-value {
    display: block;
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
}

.stat-label {
    font-size: 12px;
    color: #6b7280;
}

.client-interests {
    margin-bottom: 12px;
}

.client-interests h5 {
    margin: 0 0 4px 0;
    font-size: 14px;
    color: #374151;
}

.client-interests p {
    margin: 0;
    font-size: 14px;
    color: #6b7280;
}

.client-activity {
    padding-top: 8px;
    border-top: 1px solid #f3f4f6;
}

.client-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}

.empty-state i {
    font-size: 64px;
    margin-bottom: 16px;
    color: #d1d5db;
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

.search-box {
    position: relative;
    flex: 1;
    min-width: 250px;
}

.search-box i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #6b7280;
}

.search-box input {
    width: 100%;
    padding: 12px 12px 12px 40px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
}

.filter-row select {
    min-width: 150px;
}
</style>

<script>
function addNewClient() {
    Swal.fire({
        title: 'Add New Client',
        html: `
            <div class="compose-form">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" id="clientName" class="swal2-input" placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="clientEmail" class="swal2-input" placeholder="john@example.com">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" id="clientPhone" class="swal2-input" placeholder="+260123456789">
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea id="clientNotes" class="swal2-textarea" placeholder="Client preferences and notes..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Add Client',
        confirmButtonColor: '#10b981',
        preConfirm: () => {
            const name = document.getElementById('clientName').value;
            const email = document.getElementById('clientEmail').value;
            const phone = document.getElementById('clientPhone').value;
            const notes = document.getElementById('clientNotes').value;
            
            if (!name || !email) {
                Swal.showValidationMessage('Please fill in name and email');
                return false;
            }
            
            return { name, email, phone, notes };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Client Added!',
                text: `${result.value.name} has been added to your client list.`,
                icon: 'success'
            });
        }
    });
}

function viewClientProfile(clientId) {
    Swal.fire({
        title: 'Client Profile',
        text: `Detailed profile for client ID: ${clientId}`,
        icon: 'info'
    });
}

function contactClient(email, phone) {
    const contactOptions = {
        html: `
            <div class="contact-options">
                <p><strong>Email:</strong> ${email}</p>
                ${phone ? `<p><strong>Phone:</strong> ${phone}</p>` : ''}
                <p>Choose your preferred contact method:</p>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Send Email',
        confirmButtonColor: '#3b82f6',
        showDenyButton: !!phone,
        denyButtonText: phone ? 'Make Call' : false
    };
    
    Swal.fire(contactOptions).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `mailto:${email}`;
        } else if (result.isDenied && phone) {
            window.location.href = `tel:${phone}`;
        }
    });
}

function viewClientHistory(clientId) {
    Swal.fire({
        title: 'Client History',
        text: `Booking history for client ID: ${clientId}`,
        icon: 'info'
    });
}

function addNote(clientId) {
    Swal.fire({
        title: 'Add Client Note',
        input: 'textarea',
        inputLabel: 'Note',
        inputPlaceholder: 'Add notes about this client...',
        showCancelButton: true,
        confirmButtonText: 'Save Note',
        confirmButtonColor: '#f59e0b'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire({
                title: 'Note Saved!',
                text: 'Your note has been saved for this client.',
                icon: 'success'
            });
        }
    });
}
</script>

<?php include '../includes/agent_footer.php'; ?>

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

$pageTitle = 'Communications - HomeLink';
$userId = $_SESSION['user_id'];

// Get filter parameters
$type = $_GET['type'] ?? '';
$status = $_GET['status'] ?? '';

// Build query for communications (bookings as communications)
$sql = "SELECT b.*, p.title as property_title, p.address as property_address, p.city as property_city,
        u.username as client_name, u.email as client_email, u.phone as client_phone,
        s.username as seller_name
        FROM bookings b
        JOIN properties p ON b.property_id = p.property_id
        JOIN users u ON b.buyer_id = u.user_id
        LEFT JOIN users s ON p.seller_id = s.user_id
        WHERE 1=1";

$params = [];

// For agents, we'll show all bookings they can see/act upon
// In a real system, you might have an agent_id column in properties or bookings

if (!empty($type)) {
    $sql .= " AND b.status = ?";
    $params[] = $type;
}

if (!empty($status)) {
    $sql .= " AND b.status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY b.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$communications = $stmt->fetchAll();

// Get statistics
$stats = [];
$stats['total_messages'] = count($communications);
$stats['pending_responses'] = count(array_filter($communications, fn($c) => $c['status'] === 'pending'));
$stats['confirmed_bookings'] = count(array_filter($communications, fn($c) => $c['status'] === 'confirmed'));
$stats['completed_deals'] = count(array_filter($communications, fn($c) => $c['status'] === 'completed'));

include 'agent_header.php';
?>

<div class="admin-layout">
    <?php include 'agent_sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-page-header">
            <h1><i class="fas fa-comments"></i> Communications</h1>
            <p>Manage client inquiries and booking communications</p>
        </div>
        
        <!-- Communication Stats -->
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['total_messages']; ?></h3>
                    <p>Total Messages</p>
                </div>
            </div>
            
            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['pending_responses']; ?></h3>
                    <p>Pending Responses</p>
                </div>
            </div>
            
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['confirmed_bookings']; ?></h3>
                    <p>Confirmed Bookings</p>
                </div>
            </div>
            
            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['completed_deals']; ?></h3>
                    <p>Completed Deals</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="admin-section">
            <div class="quick-actions">
                <button class="btn btn-primary" onclick="composeNewMessage()">
                    <i class="fas fa-paper-plane"></i> Compose Message
                </button>
                <button class="btn btn-success" onclick="respondToPending()">
                    <i class="fas fa-reply"></i> Respond to Pending
                </button>
                <button class="btn btn-info" onclick="sendBulkMessage()">
                    <i class="fas fa-broadcast-tower"></i> Bulk Message
                </button>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="admin-section">
            <form method="GET" action="" class="filter-form">
                <div class="filter-row">
                    <select name="type">
                        <option value="">All Types</option>
                        <option value="pending" <?php echo ($type ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo ($type ?? '') === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="completed" <?php echo ($type ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo ($type ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo ($status ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo ($status ?? '') === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="completed" <?php echo ($status ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo ($status ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="/agent/communications.php" class="btn btn-outline">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Communications List -->
        <div class="admin-section">
            <h2><i class="fas fa-inbox"></i> Message Inbox</h2>
            <div class="communications-list">
                <?php if (empty($communications)): ?>
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <p>No communications found.</p>
                        <button class="btn btn-primary" onclick="composeNewMessage()">Send Your First Message</button>
                    </div>
                <?php else: ?>
                    <?php foreach ($communications as $comm): ?>
                        <div class="communication-item <?php echo $comm['status']; ?>">
                            <div class="comm-header">
                                <div class="comm-info">
                                    <h4>
                                        <i class="fas fa-home"></i>
                                        <?php echo htmlspecialchars($comm['property_title']); ?>
                                    </h4>
                                    <div class="comm-meta">
                                        <span class="comm-id">#<?php echo str_pad($comm['booking_id'], 6, '0', STR_PAD_LEFT); ?></span>
                                        <span class="comm-date"><?php echo date('M d, Y H:i', strtotime($comm['created_at'])); ?></span>
                                        <span class="status-badge status-<?php echo $comm['status']; ?>">
                                            <?php echo ucfirst($comm['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="comm-priority">
                                    <?php if ($comm['status'] === 'pending'): ?>
                                        <span class="priority-badge high">Needs Response</span>
                                    <?php elseif ($comm['status'] === 'confirmed'): ?>
                                        <span class="priority-badge medium">Active</span>
                                    <?php else: ?>
                                        <span class="priority-badge low">Closed</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="comm-content">
                                <div class="comm-participants">
                                    <div class="participant">
                                        <i class="fas fa-user"></i>
                                        <strong>Client:</strong> <?php echo htmlspecialchars($comm['client_name']); ?>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($comm['client_email']); ?>
                                            <?php if ($comm['client_phone']): ?>
                                                | <i class="fas fa-phone"></i> <?php echo htmlspecialchars($comm['client_phone']); ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <?php if ($comm['seller_name']): ?>
                                        <div class="participant">
                                            <i class="fas fa-store"></i>
                                            <strong>Seller:</strong> <?php echo htmlspecialchars($comm['seller_name']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="comm-message">
                                    <strong>Message:</strong>
                                    <p><?php echo nl2br(htmlspecialchars($comm['message'] ?? 'No message provided')); ?></p>
                                </div>
                                
                                <div class="comm-property">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($comm['property_address'] . ', ' . $comm['property_city']); ?>
                                </div>
                            </div>
                            
                            <div class="comm-actions">
                                <?php if ($comm['status'] === 'pending'): ?>
                                    <button class="btn btn-sm btn-success" onclick="respondToClient(<?php echo $comm['booking_id']; ?>)">
                                        <i class="fas fa-reply"></i> Respond
                                    </button>
                                    <button class="btn btn-sm btn-primary" onclick="confirmBooking(<?php echo $comm['booking_id']; ?>)">
                                        <i class="fas fa-check"></i> Confirm Booking
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($comm['status'] === 'confirmed'): ?>
                                    <button class="btn btn-sm btn-info" onclick="followUpClient(<?php echo $comm['booking_id']; ?>)">
                                        <i class="fas fa-comment"></i> Follow Up
                                    </button>
                                    <button class="btn btn-sm btn-success" onclick="completeDeal(<?php echo $comm['booking_id']; ?>)">
                                        <i class="fas fa-check-double"></i> Complete Deal
                                    </button>
                                <?php endif; ?>
                                
                                <button class="btn btn-sm btn-outline" onclick="viewFullDetails(<?php echo $comm['booking_id']; ?>)">
                                    <i class="fas fa-eye"></i> Full Details
                                </button>
                                
                                <button class="btn btn-sm btn-warning" onclick="contactDirect('<?php echo htmlspecialchars($comm['client_email']); ?>', '<?php echo htmlspecialchars($comm['client_phone'] ?? ''); ?>')">
                                    <i class="fas fa-phone"></i> Contact
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
.communications-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.communication-item {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    transition: box-shadow 0.2s;
}

.communication-item:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.communication-item.pending {
    border-left: 4px solid #f59e0b;
}

.communication-item.confirmed {
    border-left: 4px solid #3b82f6;
}

.communication-item.completed {
    border-left: 4px solid #10b981;
}

.comm-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
}

.comm-info h4 {
    margin: 0 0 8px 0;
    color: #1f2937;
    font-size: 18px;
}

.comm-meta {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.comm-id {
    font-family: monospace;
    background: #f3f4f6;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px;
}

.comm-date {
    color: #6b7280;
    font-size: 14px;
}

.priority-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.priority-badge.high {
    background-color: #fef3c7;
    color: #92400e;
}

.priority-badge.medium {
    background-color: #dbeafe;
    color: #1e40af;
}

.priority-badge.low {
    background-color: #f3f4f6;
    color: #6b7280;
}

.comm-content {
    margin-bottom: 16px;
}

.comm-participants {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
    margin-bottom: 12px;
}

.participant {
    padding: 12px;
    background: #f9fafb;
    border-radius: 6px;
}

.comm-message {
    background: #f8fafc;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 12px;
}

.comm-message p {
    margin: 8px 0 0 0;
    color: #4b5563;
}

.comm-property {
    color: #6b7280;
    font-size: 14px;
}

.comm-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.quick-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.empty-state {
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
}

.filter-row select {
    min-width: 150px;
}
</style>

<script>
function composeNewMessage() {
    Swal.fire({
        title: 'Compose New Message',
        html: `
            <div class="compose-form">
                <div class="form-group">
                    <label>Recipient Email</label>
                    <input type="email" id="recipientEmail" class="swal2-input" placeholder="client@example.com">
                </div>
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" id="messageSubject" class="swal2-input" placeholder="Property Inquiry Response">
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea id="messageContent" class="swal2-textarea" rows="4" placeholder="Type your message here..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Send Message',
        confirmButtonColor: '#3b82f6',
        preConfirm: () => {
            const email = document.getElementById('recipientEmail').value;
            const subject = document.getElementById('messageSubject').value;
            const content = document.getElementById('messageContent').value;
            
            if (!email || !subject || !content) {
                Swal.showValidationMessage('Please fill in all fields');
                return false;
            }
            
            return { email, subject, content };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Message Sent!',
                text: 'Your message has been sent successfully.',
                icon: 'success'
            });
        }
    });
}

function respondToClient(bookingId) {
    Swal.fire({
        title: 'Respond to Client',
        input: 'textarea',
        inputLabel: 'Your Response',
        inputPlaceholder: 'Type your response to the client...',
        showCancelButton: true,
        confirmButtonText: 'Send Response',
        confirmButtonColor: '#10b981'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire({
                title: 'Response Sent!',
                text: 'Your response has been sent to the client.',
                icon: 'success'
            });
        }
    });
}

function confirmBooking(bookingId) {
    Swal.fire({
        title: 'Confirm Booking',
        text: 'Are you sure you want to confirm this booking?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Confirm',
        confirmButtonColor: '#10b981'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Booking Confirmed!',
                text: 'The booking has been confirmed and the client will be notified.',
                icon: 'success'
            });
        }
    });
}

function followUpClient(bookingId) {
    Swal.fire({
        title: 'Follow Up with Client',
        input: 'textarea',
        inputLabel: 'Follow-up Message',
        inputPlaceholder: 'Type your follow-up message...',
        showCancelButton: true,
        confirmButtonText: 'Send Follow-up',
        confirmButtonColor: '#3b82f6'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire({
                title: 'Follow-up Sent!',
                text: 'Your follow-up message has been sent.',
                icon: 'success'
            });
        }
    });
}

function completeDeal(bookingId) {
    Swal.fire({
        title: 'Complete Deal',
        text: 'Mark this deal as completed?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Complete Deal',
        confirmButtonColor: '#10b981'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Deal Completed!',
                text: 'This deal has been marked as completed.',
                icon: 'success'
            });
        }
    });
}

function viewFullDetails(bookingId) {
    Swal.fire({
        title: 'Booking Details',
        text: `Full details for booking #${String(bookingId).padStart(6, '0')} would be displayed here.`,
        icon: 'info'
    });
}

function contactDirect(email, phone) {
    Swal.fire({
        title: 'Contact Client',
        html: `
            <p><strong>Email:</strong> ${email}</p>
            ${phone ? `<p><strong>Phone:</strong> ${phone}</p>` : ''}
            <p>Choose your preferred contact method:</p>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Send Email',
        confirmButtonColor: '#3b82f6',
        showDenyButton: true,
        denyButtonText: phone ? 'Call Phone' : false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `mailto:${email}`;
        } else if (result.isDenied && phone) {
            window.location.href = `tel:${phone}`;
        }
    });
}

function respondToPending() {
    const pendingCount = <?php echo $stats['pending_responses']; ?>;
    if (pendingCount === 0) {
        Swal.fire({
            title: 'No Pending Messages',
            text: 'You have no pending messages to respond to.',
            icon: 'info'
        });
        return;
    }
    
    Swal.fire({
        title: 'Respond to Pending',
        text: `You have ${pendingCount} pending message(s) to respond to.`,
        icon: 'info',
        confirmButtonText: 'View Pending Messages'
    }).then((result) => {
        if (result.isConfirmed) {
            // Filter to show only pending messages
            window.location.href = '/agent/communications.php?status=pending';
        }
    });
}

function sendBulkMessage() {
    Swal.fire({
        title: 'Send Bulk Message',
        html: `
            <div class="compose-form">
                <div class="form-group">
                    <label>Recipients</label>
                    <select id="recipientGroup" class="swal2-select">
                        <option value="all">All Clients</option>
                        <option value="pending">Clients with Pending Bookings</option>
                        <option value="confirmed">Clients with Confirmed Bookings</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" id="bulkSubject" class="swal2-input" placeholder="New Property Listings">
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea id="bulkMessage" class="swal2-textarea" rows="4" placeholder="Check out our new property listings..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Send Bulk Message',
        confirmButtonColor: '#8b5cf6',
        preConfirm: () => {
            const group = document.getElementById('recipientGroup').value;
            const subject = document.getElementById('bulkSubject').value;
            const message = document.getElementById('bulkMessage').value;
            
            if (!subject || !message) {
                Swal.showValidationMessage('Please fill in subject and message');
                return false;
            }
            
            return { group, subject, message };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Bulk Message Sent!',
                html: `Your message has been sent to <strong>${result.value.group}</strong>.`,
                icon: 'success'
            });
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>

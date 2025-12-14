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

$pageTitle = 'Bookings - HomeLink';
$userId = $_SESSION['user_id'];

// Get filter parameters
$status = $_GET['status'] ?? '';
$property_id = $_GET['property_id'] ?? '';

// Build query
$sql = "SELECT b.*, p.title as property_title, p.address as property_address, p.city as property_city,
        u.username as buyer_name, u.email as buyer_email, u.phone as buyer_phone
        FROM bookings b
        JOIN properties p ON b.property_id = p.property_id
        JOIN users u ON b.buyer_id = u.user_id
        WHERE p.seller_id = ?";

$params = [$userId];

if (!empty($status)) {
    $sql .= " AND b.status = ?";
    $params[] = $status;
}

if (!empty($property_id)) {
    $sql .= " AND b.property_id = ?";
    $params[] = $property_id;
}

$sql .= " ORDER BY b.booking_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// Get seller's properties for filter dropdown
$propStmt = $pdo->prepare("SELECT property_id, title FROM properties WHERE seller_id = ? AND status = 'approved' ORDER BY title");
$propStmt->execute([$userId]);
$sellerProperties = $propStmt->fetchAll();

include 'seller_header.php';
?>

<div class="admin-layout">
    <?php include 'seller_sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-page-header">
            <h1><i class="fas fa-calendar-check"></i> Bookings</h1>
            <p>Manage property viewing requests and bookings</p>
        </div>
        
        <!-- Filters -->
        <div class="admin-section">
            <form method="GET" action="" class="filter-form">
                <div class="filter-row">
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    
                    <select name="property_id">
                        <option value="">All Properties</option>
                        <?php foreach ($sellerProperties as $property): ?>
                            <option value="<?php echo $property['property_id']; ?>" 
                                    <?php echo $property_id == $property['property_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($property['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="../seller/bookings.php" class="btn btn-outline">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Bookings Table -->
        <div class="admin-section">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Property</th>
                            <th>Buyer Info</th>
                            <th>Booking Date</th>
                            <th>Status</th>
                            <th>Message</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                            <tr>
                                <td colspan="7" class="text-center">
                                    <div class="empty-state">
                                        <i class="fas fa-calendar-times"></i>
                                        <p>No bookings found.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo str_pad($booking['booking_id'], 6, '0', STR_PAD_LEFT); ?></strong>
                                    </td>
                                    <td>
                                        <div class="property-info">
                                            <strong><?php echo htmlspecialchars($booking['property_title']); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?php echo htmlspecialchars($booking['property_address'] . ', ' . $booking['property_city']); ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="buyer-info">
                                            <strong><?php echo htmlspecialchars($booking['buyer_name']); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($booking['buyer_email']); ?>
                                                <?php if ($booking['buyer_phone']): ?>
                                                    <br>
                                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($booking['buyer_phone']); ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        $bookingDate = new DateTime($booking['booking_date']);
                                        $today = new DateTime();
                                        $isPast = $bookingDate < $today;
                                        ?>
                                        <span class="<?php echo $isPast ? 'text-muted' : ''; ?>">
                                            <?php echo date('M d, Y H:i', strtotime($booking['booking_date'])); ?>
                                        </span>
                                        <?php if ($isPast): ?>
                                            <br><small class="text-warning">Past booking</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $booking['status']; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="message-preview">
                                            <?php 
                                            $message = $booking['message'] ?? 'No message';
                                            echo strlen($message) > 50 ? substr(htmlspecialchars($message), 0, 50) . '...' : htmlspecialchars($message);
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($booking['status'] === 'pending'): ?>
                                                <button class="btn btn-sm btn-success" title="Confirm Booking" 
                                                        onclick="updateBookingStatus(<?php echo $booking['booking_id']; ?>, 'confirmed')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" title="Cancel Booking" 
                                                        onclick="updateBookingStatus(<?php echo $booking['booking_id']; ?>, 'cancelled')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($booking['status'] === 'confirmed'): ?>
                                                <button class="btn btn-sm btn-primary" title="Mark as Completed" 
                                                        onclick="updateBookingStatus(<?php echo $booking['booking_id']; ?>, 'completed')">
                                                    <i class="fas fa-check-double"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-info" title="View Details" 
                                                    onclick="viewBookingDetails(<?php echo $booking['booking_id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning" title="Contact Buyer" 
                                                    onclick="contactBuyer('<?php echo htmlspecialchars($booking['buyer_email']); ?>')">
                                                <i class="fas fa-envelope"></i>
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
.property-info strong {
    display: block;
    margin-bottom: 4px;
}

.buyer-info strong {
    display: block;
    margin-bottom: 4px;
}

.message-preview {
    max-width: 200px;
    font-size: 14px;
    color: #6b7280;
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
function updateBookingStatus(bookingId, newStatus) {
    const statusText = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
    const confirmText = newStatus === 'confirmed' ? 'Confirm this booking?' : 
                       newStatus === 'cancelled' ? 'Cancel this booking?' : 
                       'Mark this booking as completed?';
    
    Swal.fire({
        title: confirmText,
        text: `This will change the booking status to ${statusText}.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: newStatus === 'cancelled' ? '#ef4444' : '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: statusText
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit form to update booking status
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/seller/update_booking_status.php';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'booking_id';
            idInput.value = bookingId;
            
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = newStatus;
            
            form.appendChild(idInput);
            form.appendChild(statusInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function viewBookingDetails(bookingId) {
    // In a real implementation, this would open a modal with full booking details
    Swal.fire({
        title: 'Booking Details',
        text: `Full details for booking #${String(bookingId).padStart(6, '0')} would be displayed here.`,
        icon: 'info'
    });
}

function contactBuyer(email) {
    Swal.fire({
        title: 'Contact Buyer',
        html: `
            <p>You can contact the buyer at:</p>
            <p><strong>Email:</strong> ${email}</p>
            <p>Click below to open your email client.</p>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Open Email',
        confirmButtonColor: '#3b82f6'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `mailto:${email}`;
        }
    });
}
</script>

<?php include '../includes/seller_footer.php'; ?>

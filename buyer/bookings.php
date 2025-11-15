<?php
require_once dirname(__DIR__) . '/includes/db_connect.php';
require_once dirname(__DIR__) . '/includes/auth.php';

// Check if user is logged in and is a buyer
if (!isLoggedIn()) {
    header('Location: /login.php');
    exit();
}

if (!isBuyer()) {
    header('Location: /index.php');
    exit();
}

$pageTitle = 'My Bookings - HomeLink';
$userId = $_SESSION['user_id'];

// Get all bookings
try {
    $bookingsStmt = $pdo->prepare("SELECT b.*, p.title as property_title, p.address, p.city, p.price,
                                   u.username as seller_name, u.email as seller_email, u.phone as seller_phone,
                                   (SELECT image_url FROM images WHERE property_id = p.property_id AND is_primary = TRUE LIMIT 1) as primary_image
                                   FROM bookings b
                                   JOIN properties p ON b.property_id = p.property_id
                                   JOIN users u ON p.seller_id = u.user_id
                                   WHERE b.user_id = ?
                                   ORDER BY b.booking_date DESC");
    $bookingsStmt->execute([$userId]);
    $bookings = $bookingsStmt->fetchAll();
} catch (PDOException $e) {
    $bookings = [];
    $error = "Could not load bookings";
}

include 'buyer_header.php';
?>

<div class="admin-layout">
    <?php include 'buyer_sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-page-header">
            <h1><i class="fas fa-calendar-check"></i> My Bookings</h1>
            <p>Your property viewing requests and appointments</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="admin-section">
            <div class="section-header">
                <h2><i class="fas fa-list"></i> All Bookings (<?php echo count($bookings); ?>)</h2>
                <a href="/properties.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Book New Viewing
                </a>
            </div>
            
            <?php if (empty($bookings)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar"></i>
                    <p>You haven't made any bookings yet.</p>
                    <a href="/properties.php" class="btn btn-primary">
                        <i class="fas fa-search"></i> Browse Properties
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Location</th>
                                <th>Price</th>
                                <th>Seller Contact</th>
                                <th>Booking Date</th>
                                <th>Viewing Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td>
                                        <div class="property-cell">
                                            <?php
                                            $primaryImage = $booking['primary_image'] ?? '';
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
                                                <img src="<?php echo htmlspecialchars('/' . ltrim($primaryImage, '/')); ?>" alt="Property" class="property-thumb">
                                            <?php else: ?>
                                                <div class="property-thumb-placeholder">
                                                    <i class="fas fa-home"></i>
                                                </div>
                                            <?php endif; ?>
                                            <span><?php echo htmlspecialchars($booking['property_title']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($booking['city']); ?></td>
                                    <td><strong>K<?php echo number_format($booking['price']); ?></strong></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking['seller_name']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($booking['seller_email']); ?></small>
                                        <?php if ($booking['seller_phone']): ?>
                                            <br><small><i class="fas fa-phone"></i> <?php echo htmlspecialchars($booking['seller_phone']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                    <td>
                                        <?php if ($booking['viewing_date']): ?>
                                            <?php echo date('M d, Y', strtotime($booking['viewing_date'])); ?>
                                            <?php if ($booking['viewing_time']): ?>
                                                <br><small><?php echo date('h:i A', strtotime($booking['viewing_time'])); ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Not scheduled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $booking['status']; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/property_details.php?id=<?php echo $booking['property_id']; ?>" class="btn btn-sm btn-primary" title="View Property">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
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

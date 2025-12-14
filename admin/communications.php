<?php
require_once dirname(__DIR__) . "/includes/db_connect.php";
require_once dirname(__DIR__) . "/includes/auth.php";

// Check if user is logged in and is a buyer
if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

if (!isBuyer()) {
    header("Location: ../index.php");
    exit();
}

$pageTitle = "Communications - HomeLink";
$userId = $_SESSION["user_id"];

// Get filter parameters
$type = $_GET["type"] ?? "";
$status = $_GET["status"] ?? "";

// Build query for communications (bookings as communications)
$sql = "SELECT b.*, p.title as property_title, p.address as property_address, p.city as property_city,
        u.username as seller_name, u.email as seller_email, u.phone as seller_phone
        FROM bookings b
        JOIN properties p ON b.property_id = p.property_id
        JOIN users u ON p.seller_id = u.user_id
        WHERE b.buyer_id = ?";

$params = [$userId];

if (!empty($type)) {
    $sql .= " AND b.status = ?";
    $params[] = $type;
}

if (!empty($status)) {
    $sql .= " AND b.status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY b.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $communications = $stmt->fetchAll();
} catch (PDOException $e) {
    $communications = [];
    $error = "Could not load communications";
}

include "buyer_header.php";
?>

<div class="admin-layout">
    <?php include "buyer_sidebar.php"; ?>
    
    <main class="admin-content">
        <div class="admin-page-header">
            <h1><i class="fas fa-comments"></i> Communications</h1>
            <p>Manage your property viewing requests and messages</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="admin-section">
            <div class="section-header">
                <h2><i class="fas fa-list"></i> Booking Requests (<?php echo count($communications); ?>)</h2>
                <div class="filter-controls">
                    <select id="statusFilter" class="filter-select">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status === "pending" ? "selected" : ""; ?>>Pending</option>
                        <option value="confirmed" <?php echo $status === "confirmed" ? "selected" : ""; ?>>Confirmed</option>
                        <option value="cancelled" <?php echo $status === "cancelled" ? "selected" : ""; ?>>Cancelled</option>
                        <option value="completed" <?php echo $status === "completed" ? "selected" : ""; ?>>Completed</option>
                    </select>
                </div>
            </div>
            
            <?php if (empty($communications)): ?>
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <p>No communications found.</p>
                    <a href="properties.php" class="btn btn-primary">
                        <i class="fas fa-search"></i> Browse Properties
                    </a>
                </div>
            <?php else: ?>
                <div class="communications-list">
                    <?php foreach ($communications as $comm): ?>
                        <div class="communication-item">
                            <div class="communication-header">
                                <div class="property-info">
                                    <h3><i class="fas fa-home"></i> <?php echo htmlspecialchars($comm["property_title"]); ?></h3>
                                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($comm["property_address"] . ", " . $comm["property_city"]); ?></p>
                                </div>
                                <div class="communication-status">
                                    <span class="status-badge status-<?php echo $comm["status"]; ?>">
                                        <?php echo ucfirst($comm["status"]); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="communication-details">
                                <div class="booking-info">
                                    <p><i class="fas fa-calendar"></i> <strong>Requested:</strong> <?php echo date("M j, Y g:i A", strtotime($comm["booking_date"])); ?></p>
                                    <?php if (!empty($comm["message"])): ?>
                                        <p><i class="fas fa-comment"></i> <strong>Message:</strong> <?php echo htmlspecialchars($comm["message"]); ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="seller-info">
                                    <p><i class="fas fa-user"></i> <strong>Seller:</strong> <?php echo htmlspecialchars($comm["seller_name"]); ?></p>
                                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($comm["seller_email"]); ?></p>
                                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($comm["seller_phone"]); ?></p>
                                </div>
                            </div>
                            
                            <div class="communication-actions">
                                <a href="../property_details.php?id=<?php echo $comm["property_id"]; ?>" class="btn btn-secondary">
                                    <i class="fas fa-eye"></i> View Property
                                </a>
                                <?php if ($comm["status"] === "pending"): ?>
                                    <button class="btn btn-danger" onclick="cancelBooking(<?php echo $comm["booking_id"]; ?>)">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
function cancelBooking(bookingId) {
    if (confirm("Are you sure you want to cancel this booking?")) {
        fetch("../api/cancel_booking.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                booking_id: bookingId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Booking cancelled successfully!");
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred while cancelling the booking.");
        });
    }
}

// Filter functionality
document.getElementById("statusFilter").addEventListener("change", function() {
    const status = this.value;
    const url = new URL(window.location);
    if (status) {
        url.searchParams.set("status", status);
    } else {
        url.searchParams.delete("status");
    }
    window.location.href = url.toString();
});
</script>

<?php include "admin_footer.php"; ?>

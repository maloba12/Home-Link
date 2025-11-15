<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isBuyer()) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $propertyId = $_POST['property_id'] ?? 0;
    $bookingDate = $_POST['booking_date'] ?? '';
    $message = sanitizeInput($_POST['message'] ?? '');
    
    if (!$propertyId || !$bookingDate) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit();
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO bookings (buyer_id, property_id, booking_date, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([getUserId(), $propertyId, $bookingDate, $message]);
        
        echo json_encode(['success' => true, 'message' => 'Booking submitted successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>


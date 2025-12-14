<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $propertyId = $input['property_id'] ?? 0;
    
    try {
        // Check if favorite exists
        $stmt = $pdo->prepare("SELECT favorite_id FROM favorites WHERE user_id = ? AND property_id = ?");
        $stmt->execute([getUserId(), $propertyId]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Remove favorite
            $stmt = $pdo->prepare("DELETE FROM favorites WHERE favorite_id = ?");
            $stmt->execute([$existing['favorite_id']]);
            echo json_encode(['success' => true, 'action' => 'removed']);
        } else {
            // Add favorite
            $stmt = $pdo->prepare("INSERT INTO favorites (user_id, property_id) VALUES (?, ?)");
            $stmt->execute([getUserId(), $propertyId]);
            echo json_encode(['success' => true, 'action' => 'added']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>


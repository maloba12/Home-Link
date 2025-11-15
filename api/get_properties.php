<?php
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT p.*, 
                        (SELECT image_url FROM images WHERE property_id = p.property_id AND is_primary = TRUE LIMIT 1) as primary_image
                        FROM properties p 
                        WHERE p.status = 'approved'
                        ORDER BY p.created_at DESC");
    
    $properties = $stmt->fetchAll();
    echo json_encode($properties);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>


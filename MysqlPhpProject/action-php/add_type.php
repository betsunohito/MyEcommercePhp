<?php
header('Content-Type: application/json');
require '../db.php'; // your PDO DB connection

$categoryId = $_POST['category_id'] ?? '';
$typeName = $_POST['type_name'] ?? '';

if (!$categoryId || !$typeName) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

try {
    $stmt = $pdo->prepare("CALL add_product_type(?, ?)");
    $success = $stmt->execute([$categoryId, $typeName]);

    // Important: close cursor to allow next query
    $stmt->closeCursor();

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Type added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add type']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

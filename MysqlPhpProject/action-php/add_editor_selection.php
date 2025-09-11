<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';

$product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$selected   = isset($_POST['selected']) ? (int) $_POST['selected'] : 0;

if ($product_id <= 0) {
    echo json_encode(['error' => true, 'message' => 'Invalid product_id']);
    exit;
}

try {
    if ($selected === 1) {
        // Add selection via procedure
        $stmt = $pdo->prepare("CALL product_editor_selection_add(:product_id)");
        $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->closeCursor();

        echo json_encode(['success' => true, 'message' => 'Product marked as editor selection']);
    } else {
        // Remove selection manually
        $stmt = $pdo->prepare("DELETE FROM product_editor_selection_tb WHERE product_id = :product_id");
        $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Product removed from editor selection']);
    }
} catch (PDOException $e) {
    echo json_encode([
        'error' => true,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>

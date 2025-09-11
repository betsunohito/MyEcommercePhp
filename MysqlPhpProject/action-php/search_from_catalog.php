<?php
header('Content-Type: application/json');
require '../db.php'; // your $pdo connection

$term = trim($_POST['term'] ?? '');
if (!$term) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("CALL search_products(?)");
    $stmt->execute([$term]);

    // First result set: products
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Move to next result set: types
    $stmt->nextRowset();
    $typesRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group types by product_id
    $typesByProduct = [];
    foreach ($typesRows as $type) {
        $typesByProduct[$type['product_id']][] = [
            'type_id' => $type['type_id'],
            'category_id' => $type['category_id'],
            'type_name' => $type['type_name']
        ];
    }

    // Attach types to each product
    foreach ($products as &$product) {
        $pid = $product['product_id'];
        $product['available_types'] = $typesByProduct[$pid] ?? [];
    }

    echo json_encode($products);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

<?php
require_once '../db.php';

$cat_id = intval($_GET['cat_id'] ?? 0);

$stmt = $pdo->prepare("CALL get_subcategories_by_category(:cat_id)");
$stmt->bindParam(':cat_id', $cat_id, PDO::PARAM_INT);
$stmt->execute();

// Fetch first result set: subcategories
$subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Move to the next result set: brands
$stmt->nextRowset();
$brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return both as JSON
echo json_encode([
    'subcategories' => $subcategories,
    'brands' => $brands
]);
?>

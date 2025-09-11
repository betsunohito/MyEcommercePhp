<?php
require_once __DIR__ . '/../db.php';

$categoryId = $_POST['category_main_id'] ?? null;
$brandName = trim($_POST['brand_name'] ?? '');

if (!$categoryId || !$brandName) {
    exit("Category and brand name are required.");
}

try {
    $stmt = $pdo->prepare("CALL add_brand(:brand_name, :category_id)");
    $stmt->execute([
        ':brand_name' => $brandName,
        ':category_id' => $categoryId
    ]);

    echo "Brand added successfully!";
} catch (PDOException $e) {
    // Extract only the error message (after the last colon if it's a SIGNAL)
    $parts = explode(':', $e->getMessage());
    echo trim(end($parts));
}
?>

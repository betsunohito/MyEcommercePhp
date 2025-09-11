<?php require_once '../db.php';

$stmt = $pdo->prepare("CALL get_all_categories()");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($categories); ?>

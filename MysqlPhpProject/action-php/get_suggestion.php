<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([]);
    exit;
}

$type = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : '';
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$parent_id = isset($_GET['parent_id']) && $_GET['parent_id'] !== ''
    ? (int) $_GET['parent_id']
    : null;

$validTypes = ['category', 'subcategory', 'tertiary'];

if ($q === '' || !in_array($type, $validTypes, true)) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("CALL get_category_suggestions(:type, :query, :parent_id)");
    $stmt->bindParam(':type', $type, PDO::PARAM_STR);
    $stmt->bindParam(':query', $q, PDO::PARAM_STR);
    if ($parent_id === null) {
        $stmt->bindValue(':parent_id', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(':parent_id', $parent_id, PDO::PARAM_INT);
    }
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($results);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

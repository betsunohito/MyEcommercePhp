<?php
header('Content-Type: application/json');
require_once '../../db.php'; // update path if needed

$search = $_GET['term'] ?? '';
$search = trim($search);

if (empty($search)) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("CALL search_autocomplete(:search)");
    $stmt->bindValue(':search', $search, PDO::PARAM_STR);
    $stmt->execute();

    $results = [];

    do {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = [
                'label' => $row['label'],
                'type' => $row['type'],
                'id'    => $row['value_id'],
                'gtin'  => $row['GTIN'] ?? null
            ];
        }
    } while ($stmt->nextRowset());

    echo json_encode($results);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

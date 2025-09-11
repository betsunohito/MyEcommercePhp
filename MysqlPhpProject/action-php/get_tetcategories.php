<?php
require_once '../db.php';

header('Content-Type: application/json'); // ensure JSON output

$sub_id = intval($_GET['sub_id'] ?? 0);

if ($sub_id > 0) {
    try {
        $stmt = $pdo->prepare("CALL get_tertiary_categories_by_subcategory(:sub_id)");
        $stmt->bindParam(':sub_id', $sub_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $tetcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($tetcategories);
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid subcategory ID"]);
}
?>

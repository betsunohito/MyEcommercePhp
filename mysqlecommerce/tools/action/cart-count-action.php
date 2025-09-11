<?php
include '../../db.php';

session_start();

header('Content-Type: application/json');

$p_user_id = $_SESSION['id'] ?? 0;
$response = ['count' => 0];

if ($p_user_id > 0) {
    try {
        $stmt = $pdo->prepare("CALL shoppingcart_items_count(:user_id)");
        $stmt->bindParam(':user_id', $p_user_id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result && isset($result['item_count'])) {
            $response['count'] = (int)$result['item_count'];
        }

        $stmt->closeCursor(); // Needed for multiple stored procedure calls
    } catch (PDOException $e) {
        $response['error'] = 'Database error';
    }
}

echo json_encode($response);
?>

<?php
include '../../db.php';
header('Content-Type: application/json');
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_SESSION['id'])) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in.']);
        exit;
    }

    $cart_id = (int) $_POST['cart_id'];
    $quantity = (int) $_POST['quantity'];

    try {
        $stmt = $pdo->prepare("CALL shoppingcart_q_update(:cart_id, :quantity, @new_quantity, @total_price)");
        $stmt->bindParam(':cart_id', $cart_id, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->closeCursor();

        $result = $pdo->query("SELECT @new_quantity AS new_quantity, @total_price AS total_price");
        $row = $result->fetch(PDO::FETCH_ASSOC);

        if (!$row || $row['new_quantity'] === null || $row['total_price'] === null) {
            echo json_encode(['success' => false, 'message' => 'Output missing']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'new_quantity' => (int) $row['new_quantity'],
            'total_price' => number_format((float) $row['total_price'], 2)
        ]);

    } catch (PDOException $e) {
        error_log("PDO error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server error']);
    }
}
?>

<?php
include '../../db.php'; // Make sure this defines $pdo
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}

$user_id = (int)$_SESSION['id'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];

    try {
        $stmt = $pdo->prepare("CALL favorite_toggle(:user_id, :product_id)");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(["success" => true]);
    } catch (PDOException $e) {
        error_log("PDO Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Database error"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}
?>

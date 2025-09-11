<?php
include '../../base-link.php';
include '../../db.php'; // Ensure this file defines $pdo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
ob_clean(); // Clear output buffer

if (!isset($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['id'];
    // Get card values from POST
    $card_number = $_POST['card_number'] ?? '';
    $card_name = $_POST['card_name'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    $expiry_month = $_POST['expiry_month'] ?? '';
    $expiry_year = $_POST['expiry_year'] ?? '';

    // Basic validation
    if (!$card_number || !$card_name || !$cvv || !$expiry_month || !$expiry_year) {
        echo json_encode(["status" => "error", "message" => "Missing card details"]);
        exit;
    }

    try {
        // Call the stored procedure
        $stmt = $pdo->prepare("CALL user_order_final(:user_id)");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        // Optional: clear cart or handle other post-order logic here
        $order_id = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode([
            "status" => "success",
            "message" => "Order placed successfully.",
            "base_path" => BASE_PATH,
            "order_id" => $order_id['order_id'] ?? 'N/A'
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Database error: " . $e->getMessage()
        ]);
    }
    exit;
}
?>
<?php
include __DIR__ . '/../../db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Ensure user ID is available in the session
if (!isset($_SESSION['id'])) {
    echo "Error: User ID not set";
    exit;
}

$user_id = $_SESSION['id'];

try {
    // Step 1: Call the stored procedure with the OUT parameter
    $stmt = $pdo->prepare("CALL shoppingcart_total(:user_id, @total_amount)");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $stmt->closeCursor(); // required to call next query

    // Step 2: Select the OUT parameter value
    $result = $pdo->query("SELECT @total_amount AS total_amount");
    $row = $result->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        echo json_encode([
            'success' => true,
            'total' => $row['total_amount']
        ]);
    } else {
        $formattedTotal = number_format($row['total_amount'], 2);
        echo $formattedTotal;
    }
} catch (PDOException $e) {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        echo json_encode([
            'success' => false,
            'error' => "Error retrieving cart total"
        ]);
    } else {
        echo "Error retrieving cart total";
    }
    // Optional: log the error
    error_log("Cart total error: " . $e->getMessage());
}
?>
<?php
// Include database connection and start session
include '../../db.php';
session_start();

header('Content-Type: application/json');

// Check if the user is logged in
if (isset($_SESSION['id'])) {
    $user_id = (int)$_SESSION['id'];

    try {
        // Prepare and execute the stored procedure
        $stmt = $pdo->prepare("CALL user_addresses_show(:user_id)");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch all addresses
        $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($addresses)) {
            echo json_encode(['status' => 'success', 'addresses' => $addresses]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No addresses found.']);
        }
    } catch (PDOException $e) {
        error_log("PDO error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database query failed.']);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'User is not logged in.']);
}
?>

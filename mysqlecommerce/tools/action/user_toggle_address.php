<?php
include '../../db.php'; // Make sure this defines $pdo (not $conn anymore)
session_start();

header('Content-Type: application/json');

// 1) Authentication check
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$userId = (int)$_SESSION['id'];
$data = [];

try {
    // 2) Execute the toggle-procedure using PDO
    $stmt = $pdo->prepare("CALL user_toggle_address(:user_id)");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    // 3) If the procedure SELECTs the new state, fetch all rows
    do {
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($result) {
            $data = array_merge($data, $result);
        }
    } while ($stmt->nextRowset());

    // 4) Output
    echo json_encode([
        'success' => true,
        'message' => 'Address toggled',
        'data'    => $data
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("PDO Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error',
        'error'   => 'Database operation failed'
    ]);
}

<?php
session_start();  // <---- Add this line at the top BEFORE any output
header('Content-Type: application/json'); // Always respond with JSON

require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $identity = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($identity) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Missing credentials']);
        exit;
    }

    try {
        // Prepare and call stored procedure
        $stmt = $pdo->prepare("CALL admin_login(:identity)");
        $stmt->bindParam(':identity', $identity);
        $stmt->execute();

        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor(); // Important when calling procedures

        if ($admin && password_verify($password, $admin['admin_password'])) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_username'] = $admin['admin_username'];
            $_SESSION['admin_company_name'] = $admin['admin_company_name'];

            echo json_encode([
                'success' => true,
                'admin_id' => $admin['admin_id'],
                'company' => $admin['admin_company_name'],
                'username' => $admin['admin_username']
            ]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        }

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Server error']);
        // Optionally log $e->getMessage() to a file
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
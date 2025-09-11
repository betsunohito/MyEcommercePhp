<?php
require_once __DIR__ . '/../db.php';  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get values from POST
    $company_name = $_POST['company_name'] ?? '';
    $username     = $_POST['username'] ?? '';
    $mail         = $_POST['email'] ?? '';
    $password     = $_POST['password'] ?? '';
    $phone        = $_POST['phone_number'] ?? '';

    // Basic validation
    if (empty($company_name) || empty($username) || empty($mail) || empty($password)) {
        echo "❌ Please fill in all required fields.";
        exit;
    }

    // Hash password securely
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Prepare and execute stored procedure
        $stmt = $pdo->prepare("CALL admin_register(?, ?, ?, ?, ?)");
        $stmt->execute([
            $company_name,
            $username,
            $mail,
            $hashed_password,
            $phone
        ]);

        echo "✅ Admin registered successfully!";
    } catch (PDOException $e) {
        echo "❌ Error: " . $e->getMessage();
    }
}
?>

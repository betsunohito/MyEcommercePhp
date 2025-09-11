<?php
include '../../db.php'; // Include PDO connection ($pdo)
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $errors = [];

    // Input validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Geçerli bir e-posta girin.";
    }
    if (strlen($password) < 6) {
        $errors['password'] = "Şifre en az 6 karakter olmalıdır.";
    }
    if (!isset($_POST['regulations'])) {
        $errors['regulations'] = "Kuralları kabul etmelisiniz.";
    }

    if (!empty($errors)) {
        $_SESSION['formData'] = [
            'email' => htmlspecialchars($email),
            'errors' => $errors
        ];
        $_SESSION['activeTab'] = 'signup';
        header("Location: ../../login.php");
        exit;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    try {
        // Call stored procedure
        $stmt = $pdo->prepare("CALL user_register(:email, :hashed_password, @user_id)");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':hashed_password', $hashed_password, PDO::PARAM_STR);
        $stmt->execute();
        $stmt->closeCursor();

        // Get @user_id output
        $result = $pdo->query("SELECT @user_id AS user_id");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $user_id = $row['user_id'] ?? null;

        if ($user_id) {
            $_SESSION["id"] = $user_id;
            $_SESSION["mail"] = $email;
            header("Location: ../../index.php");
            exit;
        } else {
            // Registration failed (e.g. duplicate email)
            $_SESSION['formData'] = [
                'email' => htmlspecialchars($email),
                'errors' => ['email' => "Bu e-posta zaten kullanılıyor."]
            ];
            $_SESSION['activeTab'] = 'signup';
            header("Location: ../../login.php");
            exit;
        }

    } catch (PDOException $e) {
        $_SESSION['formData'] = [
            'email' => htmlspecialchars($email),
            'errors' => ['email' => "Veritabanı hatası: " . $e->getMessage()]
        ];
        $_SESSION['activeTab'] = 'signup';
        header("Location: ../../login.php");
        exit;
    }
}
?>

<?php
include '../../db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $errors = [];

    if (empty($email) || empty($password)) {
        $errors['LoginFailError'] = "Email and password are required!";
    } else {
        try {
            // Call stored procedure
            $stmt = $pdo->prepare("CALL user_login(:email, :password, @user_id, @hashed_password, @mail)");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $password, PDO::PARAM_STR);
            $stmt->execute();
            $stmt->closeCursor();

            // Retrieve output variables
            $result = $pdo->query("SELECT @user_id AS user_id, @hashed_password AS hashed_password, @mail AS mail");
            $row = $result->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $user_id = $row['user_id'];
                $hashed_password = $row['hashed_password'];
                $user_mail = $row['mail'];

                if ($user_id && password_verify($password, $hashed_password)) {
                    $_SESSION["id"] = $user_id;
                    $_SESSION["mail"] = $user_mail;

                    header("Location: ../../index.php");
                    exit;
                } else {
                    $_SESSION['activeTab'] = 'login';
                    $errors['LoginFailError'] = "Incorrect email or password.";
                }
            } else {
                $errors['LoginFailError'] = "Incorrect email or password.";
            }
        } catch (PDOException $e) {
            $errors['LoginFailError'] = "Database error: " . $e->getMessage();
        }
    }

    $_SESSION['formData'] = [
        'email' => htmlspecialchars($email),
        'errors' => $errors
    ];

    header("Location: ../../login.php");
    exit;
}
?>

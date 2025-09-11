<?php
$host = "localhost";
$dbname = "mysqltestdb";
$user = "root";
$pass = "abc123";

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("PDO Connection failed: " . $e->getMessage());
    http_response_code(500);
    exit(json_encode(['success' => false, 'message' => 'DB connection failed']));
}

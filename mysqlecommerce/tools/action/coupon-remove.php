<?php
session_start();
require_once '../../db.php';

$user_id = $_SESSION['id'] ?? 0;

$stmt = $pdo->prepare("CALL coupon_active_clear(:user_id)");
$success = $stmt->execute(['user_id' => $user_id]);

echo json_encode([
    'success' => $success,
    'message' => $success ? '✅ Coupon removed.' : '❌ Failed to remove coupon.'
]);

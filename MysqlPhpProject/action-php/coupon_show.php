<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once '../db.php'; // $pdo

try {
    $stmt = $pdo->query("CALL coupon_show_active()");
    $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $coupons
    ]);

    // Clean up
    $stmt->closeCursor();

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

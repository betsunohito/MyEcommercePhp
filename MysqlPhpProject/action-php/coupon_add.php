<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once '../db.php';

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Decode JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Extract & validate required fields
$code = trim($input['code'] ?? '');
$amount = floatval($input['discount'] ?? 0);
$expiresIn = isset($input['expiresIn']) ? intval($input['expiresIn']) : null;

if ($code === '' || $amount <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid code or amount']);
    exit;
}

// Optional parameters
$category_id     = !empty($input['category_id'])     ? intval($input['category_id']) : null;
$subcategory_id  = !empty($input['subcategory_id'])  ? intval($input['subcategory_id']) : null;
$tertiary_id     = !empty($input['tertiary_id'])     ? intval($input['tertiary_id']) : null;
$product_id      = !empty($input['product_id'])      ? intval($input['product_id']) : null;
$user_id         = !empty($input['user_id'])         ? intval($input['user_id']) : null;

try {
    $stmt = $pdo->prepare("CALL coupon_add(
        :p_code,
        :p_amount,
        :p_days,
        :p_category,
        :p_subcategory,
        :p_tertiary,
        :p_product_id,
        :p_user_id
    )");

    $stmt->bindParam(':p_code',         $code);
    $stmt->bindParam(':p_amount',       $amount);
    $stmt->bindParam(':p_days',         $expiresIn, PDO::PARAM_INT);
    $stmt->bindParam(':p_category',     $category_id, PDO::PARAM_INT);
    $stmt->bindParam(':p_subcategory',  $subcategory_id, PDO::PARAM_INT);
    $stmt->bindParam(':p_tertiary',     $tertiary_id, PDO::PARAM_INT);
    $stmt->bindParam(':p_product_id',   $product_id, PDO::PARAM_INT);
    $stmt->bindParam(':p_user_id',      $user_id, PDO::PARAM_INT);

    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

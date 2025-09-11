<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit;
}

require_once '../db.php';

$admin_id = (int)$_SESSION['admin_id'];
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

// Basic shape check
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON body']);
    exit;
}

// Pull fields
$product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;

// product_type: accept product_type or type_id; force >= 0
if (isset($data['product_type']) && is_numeric($data['product_type'])) {
    $product_type = (int)$data['product_type'];
} elseif (isset($data['type_id']) && is_numeric($data['type_id'])) {
    $product_type = (int)$data['type_id'];
} else {
    $product_type = 0;
}
if ($product_type < 0) $product_type = 0;

// Normalize and whitelist type
$type = isset($data['type']) ? strtolower(trim((string)$data['type'])) : '';
$allowedTypes = ['price','quantity','discount','original'];
if (!in_array($type, $allowedTypes, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid type']);
    exit;
}

// Validate product_id
if ($product_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid product_id']);
    exit;
}

// Raw value (accept comma decimals like "21,50")
if (!array_key_exists('value', $data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing value']);
    exit;
}
$rawValue = is_string($data['value']) ? str_replace(',', '.', $data['value']) : $data['value'];

// Must be numeric for all types
if (!is_numeric($rawValue)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Value must be numeric']);
    exit;
}

// Normalize per type
if ($type === 'quantity') {
    $value = (int)$rawValue;
    if ($value < 0) $value = 0;
} else { // money types: price | discount | original
    $value = round((float)$rawValue, 2);
    if ($value < 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Amount must be ≥ 0']);
        exit;
    }
}

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare(
        "CALL product_update_admin(:admin_id, :product_id, :product_type, :type, :value)"
    );
    $stmt->bindValue(':admin_id',     $admin_id,     PDO::PARAM_INT);
    $stmt->bindValue(':product_id',   $product_id,   PDO::PARAM_INT);
    $stmt->bindValue(':product_type', $product_type, PDO::PARAM_INT);
    $stmt->bindValue(':type',         $type,         PDO::PARAM_STR);
    // bind as string → exact DECIMAL in MySQL (no float drift)
    $stmt->bindValue(':value',        (string)$value, PDO::PARAM_STR);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    while ($stmt->nextRowset()) { /* drain other resultsets if any */ }
    $stmt->closeCursor();

    echo json_encode([
        'success'      => true,
        'code'         => isset($result['code']) ? (int)$result['code'] : 0,
        'message'      => $result['response'] ?? 'OK',
        'product_id'   => isset($result['product_id']) ? (int)$result['product_id'] : $product_id,
        'product_type' => isset($result['product_type']) ? (int)$result['product_type'] : $product_type
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

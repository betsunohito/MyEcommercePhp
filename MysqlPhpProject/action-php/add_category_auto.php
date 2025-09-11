<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';

// 1. Read and parse JSON input
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON in request body'
    ]);
    exit;
}

// 2. Sanitize input fields
$in_category  = isset($data['category'])   ? trim($data['category'])   : '';
$in_subcat    = isset($data['subcategory'])? trim($data['subcategory']) : '';
$in_tertiary  = isset($data['tertiary'])   ? trim($data['tertiary'])   : '';

// 3. Require category input
if ($in_category === '') {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Category is required.'
    ]);
    exit;
}

try {
    // 4. Call stored procedure with OUT parameters
    $stmt = $pdo->prepare("CALL add_category_auto(:cat, :subcat, :ter, @out_cat_id, @out_sub_id, @out_ter_id, @out_msg)");
    $stmt->bindValue(':cat', $in_category, PDO::PARAM_STR);
    $stmt->bindValue(':subcat', $in_subcat, PDO::PARAM_STR);
    $stmt->bindValue(':ter', $in_tertiary, PDO::PARAM_STR);
    $stmt->execute();

    // 5. Fetch OUT results
    $row = $pdo->query("SELECT 
        @out_cat_id AS category_id,
        @out_sub_id AS subcategory_id,
        @out_ter_id AS tertiary_id,
        @out_msg AS message
    ")->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to retrieve output from stored procedure.'
        ]);
        exit;
    }

    // 6. Return final output
    echo json_encode([
        'success'        => true,
        'category_id'    => $row['category_id'] !== null ? (int)$row['category_id'] : null,
        'subcategory_id' => $row['subcategory_id'] !== null ? (int)$row['subcategory_id'] : null,
        'tertiary_id'    => $row['tertiary_id'] !== null ? (int)$row['tertiary_id'] : null,
        'message'        => $row['message'] ?? ''
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

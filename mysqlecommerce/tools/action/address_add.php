<?php
include '../../db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid method"]);
    exit;
}

$user_id = $_SESSION['id'];
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$neighborhood_id = $_POST['neighborhood_id'] ?? '';
$district_id = $_POST['district_id'] ?? '';
$province_id = $_POST['province_id'] ?? '';
$address_note = $_POST['address_note'] ?? '';
$phone_number = $_POST['phone_number'] ?? '';

$is_company = isset($_POST['is_company']) ? 1 : 0;
$tax_or_national_id = $is_company ? ($_POST['tax_or_national_id'] ?? null) : null;
$tax_office = $is_company ? ($_POST['tax_office'] ?? null) : null;
$company_name = $is_company ? ($_POST['company_name'] ?? null) : null;
$is_einvoice = $is_company ? (isset($_POST['is_einvoice']) ? 1 : 0) : null;

// Basit zorunlu alan kontrolü
if ($first_name === '' || $last_name === '' || $neighborhood_id === '' || $district_id === '' || $province_id === '') {
    echo json_encode(["status" => "error", "message" => "Required fields are missing"]);
    exit;
}

try {
    $stmt = $pdo->prepare("CALL address_add(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // DİKKAT: null olabilecekler bindValue + PARAM_NULL
    $stmt->bindValue(1, (int) $user_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $first_name, PDO::PARAM_STR);
    $stmt->bindValue(3, $last_name, PDO::PARAM_STR);
    $stmt->bindValue(4, (int) $neighborhood_id, PDO::PARAM_INT);
    $stmt->bindValue(5, (int) $district_id, PDO::PARAM_INT);
    $stmt->bindValue(6, (int) $province_id, PDO::PARAM_INT);
    $stmt->bindValue(7, $phone_number, PDO::PARAM_STR);
    $stmt->bindValue(8, $address_note, PDO::PARAM_STR);

    if ($tax_or_national_id === null) {
        $stmt->bindValue(9, null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(9, $tax_or_national_id, PDO::PARAM_STR);
    }
    if ($tax_office === null) {
        $stmt->bindValue(10, null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(10, $tax_office, PDO::PARAM_STR);
    }
    if ($company_name === null) {
        $stmt->bindValue(11, null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(11, $company_name, PDO::PARAM_STR);
    }
    if ($is_einvoice === null) {
        $stmt->bindValue(12, null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(12, (int) $is_einvoice, PDO::PARAM_INT);
    }

    $stmt->execute();

    // *** EN ÖNEMLİ KISIM: İlk result set'i oku ***
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Olası ek result set'leri temizle
    while ($stmt->nextRowset()) { /* drain */
    }
    $stmt->closeCursor();

    if (!$row) {
        echo json_encode(["status" => "error", "message" => "No result from procedure"]);
        exit;
    }

    // is_first string gelebilir -> sayıya çevir
    $address_id = isset($row['address_id']) ? (int) $row['address_id'] : 0;
    $is_first = isset($row['is_first']) ? (int) $row['is_first'] : 0;

    echo json_encode([
        "status" => $row['status'] ?? "success",
        "address_id" => $address_id,
        "is_first" => $is_first
    ]);
} catch (PDOException $e) {
    // Hata detayını logla; kullanıcıya genel mesaj ver
    // error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database error"]);
}

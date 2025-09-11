<?php
include '../../db.php'; // Make sure $pdo is initialized in this file
session_start();

header('Content-Type: application/json');
ob_clean();

if (!isset($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get and sanitize inputs
    $user_id = $_SESSION['id'];
    $user_address_id = $_POST['edit_address_id'];
    $first_name = $_POST['edit_first_name'];
    $last_name = $_POST['edit_last_name'];
    $neighborhood_id = $_POST['edit_neighborhood'];
    $district_id = $_POST['edit_district'];
    $province_id = $_POST['edit_province'];
    $address_note = $_POST['edit_address_note'];
    $phone_number = $_POST['edit_phone_number'];
    $is_company = isset($_POST['edit_is_company']) ? 1 : 0;

    $tax_or_national_id = $is_company ? ($_POST['edit_tax_or_national'] ?? null) : null;
    $tax_office = $is_company ? ($_POST['edit_tax_office'] ?? null) : null;
    $company_name = $is_company ? ($_POST['edit_company_name'] ?? null) : null;
    $is_einvoice = $is_company ? (isset($_POST['edit_einvoice']) ? 1 : 0) : null;

    // Required fields check
    if (
        empty($user_address_id) || empty($first_name) || empty($last_name) ||
        empty($neighborhood_id) || empty($district_id) || empty($province_id)
    ) {
        echo json_encode(["status" => "error", "message" => "Required fields are missing"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("CALL user_address_update(:user_id, :address_id, :first_name, :last_name, :neighborhood_id, :district_id, :province_id, :phone_number, :address_note, :tax_id, :tax_office, :company_name, :is_einvoice)");

        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':address_id', $user_address_id, PDO::PARAM_INT);
        $stmt->bindParam(':first_name', $first_name, PDO::PARAM_STR);
        $stmt->bindParam(':last_name', $last_name, PDO::PARAM_STR);
        $stmt->bindParam(':neighborhood_id', $neighborhood_id, PDO::PARAM_INT);
        $stmt->bindParam(':district_id', $district_id, PDO::PARAM_INT);
        $stmt->bindParam(':province_id', $province_id, PDO::PARAM_INT);
        $stmt->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);
        $stmt->bindParam(':address_note', $address_note, PDO::PARAM_STR);
        $stmt->bindParam(':tax_id', $tax_or_national_id, PDO::PARAM_STR);
        $stmt->bindParam(':tax_office', $tax_office, PDO::PARAM_STR);
        $stmt->bindParam(':company_name', $company_name, PDO::PARAM_STR);
        $stmt->bindParam(':is_einvoice', $is_einvoice, PDO::PARAM_INT);

        $stmt->execute();

        echo json_encode([
            "status" => "success",
            "message" => "Address updated successfully"
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Database error: " . $e->getMessage()
        ]);
    }
}
?>

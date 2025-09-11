<?php
include '../../db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate and sanitize province_id
    if (!isset($_POST['province_id']) || !is_numeric($_POST['province_id'])) {
        echo json_encode(['error' => 'Invalid province ID']);
        exit;
    }

    $province_id = (int)$_POST['province_id'];

    try {
        // Prepare the stored procedure call
        $stmt = $pdo->prepare("CALL address_district_fill(:province_id)");
        $stmt->bindParam(':province_id', $province_id, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch results
        $districts = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $districts[] = [
                'district_id' => sprintf('%02d', $row['district_id']),
                'district_name' => $row['district_name']
            ];
        }

        echo json_encode($districts);

    } catch (PDOException $e) {
        error_log("PDO error: " . $e->getMessage());
        echo json_encode(['error' => 'Database error occurred.']);
    }
}
?>

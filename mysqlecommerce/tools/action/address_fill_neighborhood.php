<?php
include '../../db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate and sanitize district_id
    if (!isset($_POST['district_id']) || !is_numeric($_POST['district_id'])) {
        echo json_encode(['error' => 'Invalid district ID']);
        exit;
    }

    $district_id = (int)$_POST['district_id'];

    try {
        // Prepare and execute the stored procedure
        $stmt = $pdo->prepare("CALL address_neighborhood_fill(:district_id)");
        $stmt->bindParam(':district_id', $district_id, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch and build the neighborhood list
        $neighborhoods = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $neighborhoods[] = [
                'neighborhood_id' => sprintf('%02d', $row['neighborhood_id']),
                'neighborhood_name' => $row['neighborhood_name']
            ];
        }

        echo json_encode($neighborhoods);

    } catch (PDOException $e) {
        error_log("PDO error: " . $e->getMessage());
        echo json_encode(['error' => 'Database error occurred.']);
    }
}
?>

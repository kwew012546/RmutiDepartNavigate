<?php
include '../../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete' && isset($_POST['service_id'])) {
        $service_id = $_POST['service_id'];

        $stmt_logs = $conn->prepare("DELETE FROM search_logs WHERE suggested_service_id = ?");
        $stmt_logs->bind_param("i", $service_id);
        $stmt_logs->execute();

        $stmt = $conn->prepare("DELETE FROM services WHERE service_id = ?");
        $stmt->bind_param("i", $service_id);
    
        if ($stmt->execute()) {
            echo "deleted";
        } else {
            echo "error: " . $stmt->error;
        }
        exit;
    }
    

    if ($action === 'update' && isset($_POST['service_id'])) {
        $service_id = $_POST['service_id'];
        $service_name = $_POST['service_name'];
        $description = $_POST['description'];
        $keywords = $_POST['keywords'];
        $floor = $_POST['floor'];
        $room = $_POST['room'];

        $stmt = $conn->prepare("
            UPDATE services 
            SET service_name = ?, description = ?, keywords = ?, floor = ?, room_number = ?
            WHERE service_id = ?
        ");
        $stmt->bind_param("sssisi", $service_name, $description, $keywords, $floor, $room, $service_id);

        if ($stmt->execute()) {
            echo "updated";
        } else {
            echo "error: $stmt->error";
        }
        exit;
    }

    if ($action === 'insert' && isset($_POST['service_name'], $_POST['description'], $_POST['keywords'], $_POST['floor'], $_POST['room'], $_POST['departments_id'])) {
        $department_id = $_POST['departments_id'];
        $service_names = $_POST['service_name'];
        $descriptions = $_POST['description'];
        $keywords_list = $_POST['keywords'];
        $floors = $_POST['floor'];
        $rooms = $_POST['room'];

        $stmt = $conn->prepare("INSERT INTO services (departments_id, service_name, description, keywords, floor, room_number) VALUES (?, ?, ?, ?, ?, ?)");

        for ($i = 0; $i < count($service_names); $i++) {
            $floor_val = is_numeric($floors[$i]) ? (int)$floors[$i] : 0;
            $room_val = trim($rooms[$i]);

            $stmt->bind_param(
                "isssis",
                $department_id,
                $service_names[$i],
                $descriptions[$i],
                $keywords_list[$i],
                 $floor_val,
                $room_val
            );
            if (!$stmt->execute()) {
                echo "error: $stmt->error";
                exit;
            }
        }
        echo "inserted";
        exit;
    }

    echo "invalid request";
}

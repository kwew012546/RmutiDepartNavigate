<?php
include '../../connect.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['image_file'])) {
        $departments_id = $_POST['departments_id'];
        $uploadDir = 'uploads/';

        foreach ($_FILES['image_file']['tmp_name'] as $index => $tmpPath) {
            if ($tmpPath != '') {
                $fileName = basename($_FILES['image_file']['name'][$index]);
                $targetPath = "$uploadDir$fileName";

                if (move_uploaded_file($tmpPath, $targetPath)) {
                    $stmt = $conn->prepare("INSERT INTO department_images (departments_id, image_name) VALUES (?, ?)");
                    $stmt->bind_param("is", $departments_id, $fileName);
                    $stmt->execute();
                }
            }
        }

        echo "success";
        exit;
    }
    if (isset($_POST['image_id']) && isset($_POST['departments_id'])) {
        $image_id = $_POST['image_id'];
        $departments_id = $_POST['departments_id'];
        $stmt = $conn->prepare("SELECT image_name FROM department_images WHERE image_id = ? AND departments_id = ?");
        $stmt->bind_param("ii", $image_id, $departments_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $image = $result->fetch_assoc();
    
        if (!$image) {
            echo "image not found";
            exit;
        }

        $stmtCheck = $conn->prepare("SELECT COUNT(*) as count FROM department_images WHERE image_name = ? AND image_id != ?");
        $stmtCheck->bind_param("si", $image['image_name'], $image_id);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        $row = $resultCheck->fetch_assoc();
        if ($row['count'] == 0) {
            $image_path = __DIR__ . "/uploads/" . $image['image_name'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
    
        $stmtDelete = $conn->prepare("DELETE FROM department_images WHERE image_id = ?");
        $stmtDelete->bind_param("i", $image_id);
        if ($stmtDelete->execute()) {
            echo "success";
        } else {
            echo "failed to delete from database";
        }
        exit;
    }

} else {
    echo "invalid request";
}

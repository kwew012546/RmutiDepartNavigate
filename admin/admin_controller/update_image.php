<?php
require_once __DIR__ . '/../../login/controller/check_auth.php';
include '../../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    // Action 1: Upload new image(s) for a department
    if (isset($_FILES['image_file']) && isset($_POST['departments_id'])) {
        $departments_id = (int)$_POST['departments_id'];

        if (is_array($_FILES['image_file']['tmp_name'])) {
            foreach ($_FILES['image_file']['tmp_name'] as $index => $tmpPath) {
                if (!empty($tmpPath) && is_uploaded_file($tmpPath)) {
                    $originalName = $_FILES['image_file']['name'][$index];
                    $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                    // 1. Verify Extension
                    if (!in_array($fileExtension, $allowedTypes, true)) {
                        continue;
                    }

                    // 2. Verify MIME Type
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $tmpPath);
                    finfo_close($finfo);

                    if (!in_array($mime, $allowedMimes, true)) {
                        continue;
                    }

                    // 3. Randomize file name to prevent collision and RCE
                    $safeFileName = uniqid('dept_img_', true) . '_' . bin2hex(random_bytes(4)) . '.' . $fileExtension;
                    $targetPath = $uploadDir . $safeFileName;

                    if (move_uploaded_file($tmpPath, $targetPath)) {
                        $stmt = $conn->prepare("INSERT INTO department_images (departments_id, image_name) VALUES (?, ?)");
                        $stmt->bind_param("is", $departments_id, $safeFileName);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            }
        }

        echo "success";
        exit;
    }

    // Action 2: Delete an image
    if (isset($_POST['image_id']) && isset($_POST['departments_id'])) {
        $image_id = (int)$_POST['image_id'];
        $departments_id = (int)$_POST['departments_id'];

        $stmt = $conn->prepare("SELECT image_name FROM department_images WHERE image_id = ? AND departments_id = ?");
        $stmt->bind_param("ii", $image_id, $departments_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $image = $result->fetch_assoc();
        $stmt->close();
    
        if (!$image) {
            echo "image not found";
            exit;
        }

        $image_name = $image['image_name'];

        // Check if any other department is referencing this image file
        $stmtCheck = $conn->prepare("SELECT COUNT(*) as count FROM department_images WHERE image_name = ? AND image_id != ?");
        $stmtCheck->bind_param("si", $image_name, $image_id);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        $row = $resultCheck->fetch_assoc();
        $stmtCheck->close();

        if ($row['count'] == 0) {
            $image_path = $uploadDir . basename($image_name);
            if (is_file($image_path)) {
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
        $stmtDelete->close();
        exit;
    }

} else {
    echo "invalid request";
}

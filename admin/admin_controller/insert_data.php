<?php
require_once __DIR__ . '/../../login/controller/check_auth.php';
include '../../connect.php';

$name_th = $_POST['agencyname_TH'] ?? '';
$name_en = $_POST['agencyname_EN'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
$workday = $_POST['workday'] ?? '';
$timestart = $_POST['timestart'] ?? '';
$timestop = $_POST['timestop'] ?? '';
$weekend_start = $_POST['weekend_timestart'] ?? '';
$weekend_stop = $_POST['weekend_timestop'] ?? '';
$weekday_business_hours = $weekend_business_hours = '';

switch ($workday) {
    case "Monday-Friday":
        $weekday_business_hours = "จันทร์-ศุกร์ เวลา $timestart - $timestop น.";
        break;
    case "Monday-Saturday":
        $weekday_business_hours = "จันทร์-เสาร์ เวลา $timestart - $timestop น.";
        $weekend_business_hours = "เสาร์ เวลา $weekend_start - $weekend_stop น.";
        break;
    case "Everyday":
        $weekday_business_hours = "จันทร์-ศุกร์ เวลา $timestart - $timestop น.";
        $weekend_business_hours = "เสาร์-อาทิตย์ เวลา $weekend_start - $weekend_stop น.";
        break;
}

if (!empty($_POST['existing_building'])) {
    [$building_number, $building, $lat, $lng] = explode('|', $_POST['existing_building']);
} else {
    $building = $_POST['building'] ?? '';
    $lat = $_POST['lat'] ?? 0;
    $lng = $_POST['lng'] ?? 0;
    if (!empty($_POST['buildingnumber'])) {
        $building_number = $_POST['buildingnumber'];
    } else {
        $result = $conn->query("SELECT MAX(building_number) AS max_num FROM building WHERE building_number >= 500");
        $row = $result->fetch_assoc();
        $building_number = ($row['max_num']) ? $row['max_num'] + 1 : 501;
    }
}

$targetDir = __DIR__ . '/uploads/';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}
$allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

$website = $_POST['website'] ?? '';
$subordinate_to = $_POST['subordinate_to'] ?? '';
$note = $_POST['note'] ?? '';

$check_duplicate = $conn->prepare("SELECT 1 FROM departments WHERE name_th = ? OR name_en = ?");
$check_duplicate->bind_param("ss", $name_th, $name_en);
$check_duplicate->execute();
$check_duplicate->store_result();

if ($check_duplicate->num_rows > 0) {
    http_response_code(409);
    echo "ชื่อหน่วยงานภาษาไทยหรือภาษาอังกฤษซ้ำกับข้อมูลที่มีอยู่แล้ว";
    exit;
}
$check_duplicate->close();

$conn->begin_transaction();

try {
    $check_stmt = $conn->prepare("SELECT 1 FROM building WHERE building_number = ?");
    $check_stmt->bind_param("i", $building_number);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows === 0) {
        $stmt_bldg = $conn->prepare("INSERT INTO building (building_number, building_name, lat, lng) VALUES (?, ?, ?, ?)");
        $stmt_bldg->bind_param("isdd", $building_number, $building, $lat, $lng);
        $stmt_bldg->execute();
        $stmt_bldg->close();
    }
    $check_stmt->close();

    // Check if new structured columns exist in departments table
    $hasStructuredHours = false;
    $colCheck = $conn->query("SHOW COLUMNS FROM departments LIKE 'workday'");
    if ($colCheck && $colCheck->num_rows > 0) {
        $hasStructuredHours = true;
    }

    if ($hasStructuredHours) {
        $stmt = $conn->prepare("INSERT INTO departments 
            (name_th, name_en, building, phone, email, weekday_business_hours, weekend_business_hours, website, subordinate_to, note, workday, weekday_open, weekday_close, weekend_open, weekend_close) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssissssssssssss", $name_th, $name_en, $building_number, $phone, $email, $weekday_business_hours, $weekend_business_hours, $website, $subordinate_to, $note, $workday, $timestart, $timestop, $weekend_start, $weekend_stop);
    } else {
        $stmt = $conn->prepare("INSERT INTO departments (name_th, name_en, building, phone, email, weekday_business_hours, weekend_business_hours, website, subordinate_to, note) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisssssss", $name_th, $name_en, $building_number, $phone, $email, $weekday_business_hours, $weekend_business_hours, $website, $subordinate_to, $note);
    }
    if (!$stmt->execute()) {
        throw new Exception("Insert department failed: " . $stmt->error);
    }
    $department_id = $stmt->insert_id;
    $stmt->close();

    // Handle Image Uploads
    if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
        $fileNames = array_filter($_FILES['images']['name']);
        if (!empty($fileNames)) {
            foreach ($fileNames as $key => $val) {
                $tmpPath = $_FILES['images']['tmp_name'][$key];
                if (!empty($tmpPath) && is_uploaded_file($tmpPath)) {
                    $fileType = strtolower(pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION));

                    // Verify extension
                    if (!in_array($fileType, $allowedTypes, true)) {
                        continue;
                    }

                    // Verify MIME
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $tmpPath);
                    finfo_close($finfo);

                    if (!in_array($mime, $allowedMimes, true)) {
                        continue;
                    }

                    // Unique filename to prevent collisions and RCE
                    $safeFileName = uniqid('dept_img_', true) . '_' . bin2hex(random_bytes(4)) . '.' . $fileType;
                    $targetFilePath = $targetDir . $safeFileName;

                    if (move_uploaded_file($tmpPath, $targetFilePath)) {
                        $stmt_img = $conn->prepare("INSERT INTO department_images (departments_id, image_name, uploaded_at) VALUES (?, ?, NOW())");
                        $stmt_img->bind_param("is", $department_id, $safeFileName);
                        $stmt_img->execute();
                        $stmt_img->close();
                    }
                }
            }
        }
    }

    // Handle Services
    if (isset($_POST['service']) && is_array($_POST['service'])) {
        $service_names = $_POST['service'];
        $descriptions = $_POST['service_description'] ?? [];
        $keywords = $_POST['keyword'] ?? [];
        $floor = $_POST['floor'] ?? [];
        $room = $_POST['room'] ?? [];

        for ($i = 0; $i < count($service_names); $i++) {
            if (trim($service_names[$i]) === '') continue;
            $s_name = $service_names[$i];
            $s_desc = $descriptions[$i] ?? '';
            $s_keyw = $keywords[$i] ?? '';
            $s_flor = is_numeric($floor[$i] ?? null) ? (int)$floor[$i] : 1;
            $s_room = $room[$i] ?? '';

            $stmt_service = $conn->prepare("INSERT INTO services (departments_id, service_name, description, keywords, floor, room_number) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_service->bind_param("isssis", $department_id, $s_name, $s_desc, $s_keyw, $s_flor, $s_room);
            $stmt_service->execute();
            $stmt_service->close();
        }
    }

    $conn->commit();
    echo "success";
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}
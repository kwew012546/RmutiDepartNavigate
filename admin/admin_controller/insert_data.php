<?php
include '../../connect.php';
$name_th = $_POST['agencyname_TH'];
$name_en = $_POST['agencyname_EN'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$workday = $_POST['workday'];
$timestart = $_POST['timestart'];
$timestop = $_POST['timestop'];
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
    $building = $_POST['building'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    if (!empty($_POST['buildingnumber'])) {
        $building_number = $_POST['buildingnumber'];
    } else {
        $result = $conn->query("SELECT MAX(building_number) AS max_num FROM building WHERE building_number >= 500");
        $row = $result->fetch_assoc();
        $building_number = ($row['max_num']) ? $row['max_num'] + 1 : 501;
    }
}

$targetDir = 'uploads/';
$allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
$statusMsg = $errorMsg = $insertValuesSQL = $errorUpload = $errorUploadType = '';
$errorUpload = !empty($errorUpload) ? 'Upload Error: ' . trim($errorUpload, ' | ') : '';
$errorUploadType = !empty($errorUploadType) ? 'File Type Error: ' . trim($errorUploadType, ' | ') : '';
$errorMsg = !empty($errorMsg) ? '<br/>' . $errorUpload . '<br/>' . $errorUploadType : '<br/>' . $errorUploadType;
$website = $_POST['website'];
$subordinate_to = $_POST['subordinate_to'];
$note = $_POST['note'];

$check_duplicate = $conn->prepare("SELECT 1 FROM departments WHERE name_th = ? OR name_en = ?");
$check_duplicate->bind_param("ss", $name_th, $name_en);
$check_duplicate->execute();
$check_duplicate->store_result();

if ($check_duplicate->num_rows > 0) {
    http_response_code(409);
    echo "ชื่อหน่วยงานภาษาไทยหรือภาษาอังกฤษซ้ำกับข้อมูลที่มีอยู่แล้ว";
    exit;
}

$check_stmt = $conn->prepare("SELECT 1 FROM building WHERE building_number = ?");
$check_stmt->bind_param("i", $building_number);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows === 0) {
    $stmt_bldg = $conn->prepare("INSERT INTO building (building_number, building_name, lat, lng) VALUES (?, ?, ?, ?)");
    $stmt_bldg->bind_param("isdd", $building_number, $building, $lat, $lng);
    $stmt_bldg->execute();
}

$stmt = $conn->prepare("INSERT INTO departments (name_th, name_en, building, phone, email, weekday_business_hours, weekend_business_hours, website, subordinate_to, note) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("ssisssssss", $name_th, $name_en, $building_number, $phone, $email, $weekday_business_hours, $weekend_business_hours, $website, $subordinate_to, $note);
if ($stmt->execute()) {
    echo "success";
} else {
    http_response_code(500);
    echo "Insert failed: $stmt->error";
}
$department_id = $stmt->insert_id;

if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
    $fileNames = array_filter($_FILES['images']['name']);
    if (!empty($fileNames)) {
        foreach ($fileNames as $key => $val) {
            $fileName = basename($_FILES['images']['name'][$key]);
            $targetFilePath = $targetDir . $fileName;
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

            if (in_array($fileType, $allowedTypes)) {
                if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $targetFilePath)) {
                    $stmt_img = $conn->prepare("INSERT INTO department_images (departments_id, image_name, uploaded_at) VALUES (?, ?, NOW())");
                    $stmt_img->bind_param("is", $department_id, $fileName);
                    $stmt_img->execute();
                } else {
                    $errorUpload .= $_FILES['images']['name'][$key] . ' | ';
                }
            } else {
                $errorUploadType .= $_FILES['images']['name'][$key] . ' | ';
            }
        }
    }
} else {
    echo "No file uploaded.";
}

$service_names = $_POST['service'];
$descriptions = $_POST['service_description'];
$keywords = $_POST['keyword'];
$floor = $_POST['floor'];
$room = $_POST['room'];

for ($i = 0; $i < count($service_names); $i++) {
    $stmt_service = $conn->prepare("INSERT INTO services (departments_id, service_name, description, keywords, floor, room_number) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_service->bind_param("isssis", $department_id, $service_names[$i], $descriptions[$i], $keywords[$i], $floor[$i], $room[$i]);
    $stmt_service->execute();
}
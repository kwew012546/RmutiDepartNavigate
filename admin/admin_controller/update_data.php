<?php
include '../../connect.php';

$action = $_POST['action'] ?? '';

if ($action === 'updateform') {
    $building_data = explode('|', $_POST['building']);
    $building_number = $building_data[0] ?? null;
    $workday = $_POST['updateworkday'] ?? 'Monday-Friday';
    $weekday_hours = "จันทร์-ศุกร์ เวลา " . $_POST['weekday_start'] . " - " . $_POST['weekday_stop'] . " น.";
    $weekend_hours = '';
    
    switch ($workday) {
        case 'Monday-Saturday':
            if (!empty($_POST['saturday_start']) && !empty($_POST['saturday_stop'])) {
                $weekend_hours = "เสาร์ เวลา " . $_POST['saturday_start'] . " - " . $_POST['saturday_stop'] . " น.";
            }
            break;
        case 'Everyday':
            if (!empty($_POST['weekend_start']) && !empty($_POST['weekend_stop'])) {
                $weekend_hours = "เสาร์-อาทิตย์ เวลา " . $_POST['weekend_start'] . " - " . $_POST['weekend_stop'] . " น.";
            }
            break;
    }
    
    $sql = "UPDATE departments SET 
        name_th=?, name_en=?, building=?,
        phone=?, email=?, weekday_business_hours=?, weekend_business_hours=?,
        website=?, subordinate_to=?, note=?
        WHERE department_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssisssssssi",
        $_POST['name_th'],
        $_POST['name_en'],
        $building_number,
        $_POST['phone'],
        $_POST['email'],
        $weekday_hours,
        $weekend_hours,
        $_POST['website'],
        $_POST['subordinate_to'],
        $_POST['note'],
        $_POST['id']
    );

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "เกิดข้อผิดพลาด: $stmt->error";
    }

} else if ($action === 'updatebuilding') {
    $lat = floatval($_POST['update_lat']);
    $lng = floatval($_POST['update_lng']);
    $sql = "UPDATE building SET building_number=?, building_name=?, lat=?, lng=? WHERE building_number=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "isddi",
        $_POST["building_number"],
        $_POST['building_name'],
        $lat,
        $lng,
        $_POST['old_building_number']
    );

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "เกิดข้อผิดพลาด: $stmt->error";
    }
} else {
    echo "ไม่พบ action ที่ถูกต้อง";
}
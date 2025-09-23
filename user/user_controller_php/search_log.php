<?php
header('Content-Type: application/json; charset=utf-8');
include '../../connect.php';

$user_input = $_POST['user_input'] ?? '';
$type = $_POST['type'] ?? '';
$id = $_POST['id'] ?? null;
$selected = $_POST['selected'];

$suggested_service_id = null;
$suggested_department_id = null;

if ($type === 'service') {
    $suggested_service_id = $id;
} elseif ($type === 'department') {
    $suggested_department_id = $id;
}

$stmt = $conn->prepare("
    INSERT INTO search_logs (user_input, suggested_service_id, suggested_department_id, user_selected)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param(
    "sssi",
    $user_input,
    $suggested_service_id,
    $suggested_department_id,
    $selected
);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

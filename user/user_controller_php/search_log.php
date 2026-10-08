<?php
header('Content-Type: application/json; charset=utf-8');
include '../../connect.php';

$user_input = trim($_POST['user_input'] ?? '');
$type = $_POST['type'] ?? '';
$id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : null;
$selected = isset($_POST['selected']) && is_numeric($_POST['selected']) ? (int)$_POST['selected'] : 1;

if ($user_input === '' || empty($id)) {
    echo json_encode(['status' => 'ignored']);
    exit;
}

$suggested_service_id = ($type === 'service') ? $id : null;
$suggested_department_id = ($type === 'department') ? $id : null;

$stmt = $conn->prepare("
    INSERT INTO search_logs (user_input, suggested_service_id, suggested_department_id, user_selected)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param(
    "siii",
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
$stmt->close();

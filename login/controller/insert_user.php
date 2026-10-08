<?php
session_start();
include '../../connect.php';

// Only allow logged in users, or allow creating initial user if the table is empty
$userCount = 0;
$checkCount = $conn->query("SELECT COUNT(*) AS count FROM users");
if ($checkCount) {
    $userCount = (int)($checkCount->fetch_assoc()['count'] ?? 0);
}

if ($userCount > 0 && !isset($_SESSION['usercode'])) {
    http_response_code(403);
    echo "Unauthorized: เข้าสู่ระบบก่อนสร้างผู้ใช้งานใหม่";
    exit;
}

$user_code = trim($_POST['user_code'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($user_code) || empty($password)) {
    http_response_code(400);
    echo "กรุณากรอกรหัสผู้ใช้งานและรหัสผ่าน";
    exit;
}

if (empty($username)) {
    $username = $user_code;
}

// Check existing usercode
$stmt_check = $conn->prepare("SELECT 1 FROM users WHERE usercode = ?");
$stmt_check->bind_param("s", $user_code);
$stmt_check->execute();
$stmt_check->store_result();
if ($stmt_check->num_rows > 0) {
    http_response_code(409);
    echo "รหัสผู้ใช้นี้มีในระบบแล้ว";
    exit;
}
$stmt_check->close();

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (usercode, username, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $user_code, $username, $hashedPassword);

if ($stmt->execute()) {
    echo "success";
} else {
    http_response_code(500);
    echo "เกิดข้อผิดพลาด: " . $stmt->error;
}
$stmt->close();

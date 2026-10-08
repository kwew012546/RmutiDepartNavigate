<?php
/**
 * Central Authentication Middleware
 * Checks if user session exists or validates remember_token cookie.
 * Returns 401 JSON for AJAX / API / Controller calls, or redirects to login.php for UI page requests.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usercode'])) {
    if (!empty($_COOKIE['remember_token'])) {
        require_once __DIR__ . '/../../connect.php';
        
        $token = $_COOKIE['remember_token'];
        $stmt = $conn->prepare("SELECT usercode, username FROM users WHERE remember_token = ?");
        if ($stmt) {
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($user = $result->fetch_assoc()) {
                $_SESSION['usercode'] = $user['usercode'];
                $_SESSION['username'] = $user['username'];
            }
            $stmt->close();
        }
    }
}

// If still not authenticated, block request
if (!isset($_SESSION['usercode'])) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
           || (strpos($requestUri, 'admin_controller') !== false)
           || (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST');

    if ($isAjax) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'error',
            'message' => 'Unauthorized: กรุณาเข้าสู่ระบบก่อนดำเนินการ'
        ]);
        exit();
    } else {
        header('Location: ../login/login.php');
        exit();
    }
}

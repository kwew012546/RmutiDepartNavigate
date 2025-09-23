<?php
    session_start();
    include '../../connect.php';

    $user_code = $_POST['user_code'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    $sql = "SELECT * FROM users WHERE usercode = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $user_code);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();    

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['usercode'] = $user['usercode'];
        $_SESSION['username'] = $user['username'];
    
        if ($remember) {
            $token = bin2hex(random_bytes(64));
            $updateToken = $conn->prepare("UPDATE users SET remember_token = ? WHERE usercode = ?");
            $updateToken->bind_param('ss', $token, $user_code);
            $updateToken->execute();
            setcookie('remember_token', $token, time() + (86400 * 30), "/", "", true, true);
        }
        echo "เข้าสู่ระบบสําเร็จ!";
        exit();
    
    } else {
        echo "รหัสผู้ใช้หรือรหัสผ่านไม่ถูกต้อง!";
    }
?>

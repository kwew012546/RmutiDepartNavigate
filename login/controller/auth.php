<?php
    session_start();
    include '../../connect.php';

    if (!isset($_SESSION['usercode'])) {
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];

            $sql = "SELECT * FROM users WHERE remember_token = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $token);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                $_SESSION['usercode'] = $user['usercode'];
                $_SESSION['username'] = $user['username'];
            } else {
                header('Location: login.php');
                exit();
            }
        } else {
            header('Location: login.php');
            exit();
        }
    }
?>

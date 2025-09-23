<?php
    session_start();
    include '../../connect.php';

    if (isset($_SESSION['usercode'])) {
        $sql = "UPDATE users SET remember_token = NULL WHERE usercode = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $_SESSION['usercode']);
        $stmt->execute();
    }

    session_unset();
    session_destroy();

    setcookie('remember_token', '', time() - 3600, "/", "", true, true); 

    header('Location: ../../index.php');
    exit();

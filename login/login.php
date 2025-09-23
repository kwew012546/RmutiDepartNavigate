<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="./login.js" type="module"></script>
    <title>Document</title>
</head>
<body>
    <form id = "loginform" method="POST">
        <div id="loginContent1" class = "content1">
            <h1>เข้าสู่ระบบ</h1>
                <div style = "display: flex; width: 100%; justify-content: center; align-items: center"> 
                    <i class="fa fa-user" style = "margin-right: 20px;"></i>
                    <input type="text" name="user_code" id="user_code1" placeholder="รหัสผู้ใช้งาน" required>
                </div>
                <div style = "display: flex; width: 100%; justify-content: center; align-items: center"> 
                    <i class="fa fa-lock" style = "margin-right: 20px;"></i>
                    <input type="password" name="password" id="password1" placeholder="รหัสผ่าน" required>
                    <button type="button" onclick="togglePassword('password1', 'toggleIcon1')"
                        style="position: absolute; right: 190px; background: none; border: none; cursor: pointer;">
                    <i class="fa fa-eye-slash" id="toggleIcon1"></i>
                </button>
                </div>
                <div id = "error" style = "color:red; display: flex; justify-content: flex-start; width:58%;"></div>
            <p><button type = "submit" class = "btn_login1">เข้าสู่ระบบ</button></p>
        </div>
    </form>
</body>
</html>
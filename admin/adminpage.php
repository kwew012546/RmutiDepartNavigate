<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" type="text/css" href="./style_admin_page.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href='https://fonts.googleapis.com/css?family=Sarabun' rel='stylesheet'>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB5iq8uZXQJoBRqi1VVRkXPx547kFFVb8s&v=weekly&libraries=maps,marker"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="./map_for_admin.js" type="module"></script>
</head>
<body style="background-color: #ffe2cb">
    <div class="header">
        <h1>หน่วยงานราชการมหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน</h1>
        <?php
            session_start();
            $username = htmlspecialchars($_SESSION['username']);
            echo "
                <div class='admin' onclick='toggleDropdown()' style='cursor:pointer;'>
                    $username
                    <div id='dropdown' class='dropdown' style='display:none; position:absolute; background:#fff; border:1px solid #ccc; padding:10px;'>
                        <a href='../index.php'>กลับไปหน้าหลัก</a>
                        <a href='../index.php'>คู่มือการใช้งาน</a>
                        <a href='./login/controller/log_out.php' onclick=\"return confirm('คุณต้องการออกจากระบบใช่หรือไม่?');\">ออกจากระบบ</a>
                    </div>
                </div>";
            ?>
    </div>
    <div class="tab">
        <button id="btnAdd" class="tablinks" onclick="openCity(event, 'add')">เพิ่มข้อมูล</button>
        <button id="btnUpdate" class="tablinks" onclick="openCity(event, 'update')">อัปเดตข้อมูล</button>
        <button id="btnDelete" class="tablinks" onclick="openCity(event, 'delete')">ลบข้อมูล</button>
      </div>
    <?php
        include '../connect.php';
        $building_options = [];
        $sql = "SELECT DISTINCT building_number, building_name AS building, lat, lng FROM building";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $building_options[] = $row;
            }
        }
    ?>
    <div id="add" class="tabcontent">
        <?php include 'admin_sub_content/add_page.php'; ?>
    </div>
    <div id="update" class="tabcontent">
        <?php include 'admin_sub_content/update_page.php'; ?>
    </div>
    <div id="delete" class="tabcontent">
        <?php include 'admin_sub_content/delete_page.php'; ?>
    </div>
</body>
</html>

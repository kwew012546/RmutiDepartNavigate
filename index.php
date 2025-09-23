<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" type="text/css" href="./user/style_user_page.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB5iq8uZXQJoBRqi1VVRkXPx547kFFVb8s&v=weekly&libraries=maps,marker,geometry">
    </script>
  <script async src="./user/user_controller_js/custom_label_overlay.js"></script>
  <script src="./user/map_for_user.js" type="module"></script>
</head>

<body>
  <div id="containerTop"><img src="images/rmuti.png" id="logo">
    <div id="title">หน่วยงานราชการมหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน <br> Government Agency of Rajamangala University of
      Technology Isan</div>
    <div id="loginRegister">
      <?php
      session_start();
      if (isset($_SESSION['usercode'])) {
        $username = htmlspecialchars($_SESSION['username']);
        echo "
              <div class = 'admin' onclick='toggleDropdown()' style='cursor:pointer;'>
                   $username
                      <div id='dropdown' class='dropdown' style='display:none; position:absolute; background:#fff; border:1px solid #ccc; padding:10px;'>
                          <a href='./admin/adminpage.php'>จัดการข้อมูล</a>
                          <a href='./login/controller/log_out.php' onclick=\"return confirm('คุณต้องการออกจากระบบใช่หรือไม่?');\">ออกจากระบบ</a>
                      </div>
              </div>";
      } else {
        echo '
                  <a href="./login/login.php" id="showLogin">เข้าสู่ระบบ</a>
              ';
      }
      ?>
    </div>
  </div>
  <div id="container">
    <div id="containerLeft">
      <div id="search">
      <div id="Back" style="display: none; margin-top: -10px; margin-bottom: -10px;">
      <button style="background: none; border: none; color: #FF7100; cursor: pointer;" onclick="goBack()"> < ย้อนกลับ</button>
      </div>
      <div id="agencyName" style="display: none;">
      </div>
        <h1 id="titleSearch">ค้นหาหน่วยงาน</h1>
        <input type="search" id="agency" placeholder="กรอกคำที่คุณต้องการ">
        <div id="search_result"></div>
        <div id="displayImage" style="display: none;">
        </div>
        <label class="switch">
          <input type="checkbox" id="toggleSwitch">
          <span class="slider">
            <span class="handle-text" id="handleText">เส้นทาง</span>
          </span>
        </label>
        <div id="displayTap">
          <div class="tab">
            <button id="top3ShortestRoutes" class="tablinks" onclick="openCity(event, 'BestPath')"><i
                class="fa fa-arrows-alt"></i></button>
            <button id="btnCar" class="tablinks" onclick="openCity(event, 'Car')"><i class="fa fa-car"></i></button>
            <button id="btnMotorcycle" class="tablinks" onclick="openCity(event, 'Motorcycle')"><i
                class="fa fa-motorcycle"></i></button>
            <button id="btnWalk" class="tablinks" onclick="openCity(event, 'Walk')"><i
                class="fa fa-walking"></i></button>
          </div>
          <div id="BestPath" class="tabcontent"></div>

          <div id="Car" class="tabcontent"></div>

          <div id="Motorcycle" class="tabcontent"></div>

          <div id="Walk" class="tabcontent"></div>

          <button id="getRouteBtn" style="float: right;"><i class="fa fa-search-location"
              style="font-size:16px; color: white"></i><span
              style="margin-left: 10px; font-size: 16px;">ค้นหาเส้นทาง</span>
          </button>
        </div>
        <div id="displayData" style="display: none;">
          <p>กรูณาเลือกหมุดปลายทาง</p>
        </div>
      </div>
      <button id="toggleSearch">
        <i id="toggleIcon" class="fa fa-search" style="font-size: 16px; color: white;"></i>
      </button>
    </div>
    <div style="width: 100%; flex-direction: row;">
      <div id="map"></div>
      <div style="margin-left: 10px; margin-top: 10px;">**หมายเหตุ ในกรณีที่หมุดของคุณ (<img
          src="https://cdn-icons-png.flaticon.com/128/3710/3710297.png" style="width: 30px; margin-bottom: -10px;">)
        ไม่ตรงกับตำแหน่งของคุณ คุณสามารถเลื่อนหมุดไปยังตำแหน่งที่ตุณต้องการและกดค้นหาเส้นทางอีกครั้งได้ <button
          id="resetLocationBtn">
          รีเซ็ตตำแหน่งของคุณใหม่</button></div>
      <div id="toast" style="
          visibility: hidden;
          min-width: 200px;
          background-color: #333;
          color: #fff;
          text-align: center;
          border-radius: 8px;
          padding: 12px;
          position: fixed;
          z-index: 9999;
          bottom: 30px;
          left: 50%;
          transform: translateX(-50%);
          font-size: 14px;
          opacity: 0;
          transition: opacity 0.5s ease, bottom 0.5s ease;
        ">
        คัดลอกแล้ว
      </div>
    </div>
  </div>
</body>

</html>
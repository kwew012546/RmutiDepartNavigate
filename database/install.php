<?php
/**
 * RMUTI Department Navigate - Automatic Database Installer
 * Run this script by visiting http://localhost/<project>/database/install.php
 * or via CLI: php database/install.php
 */

header('Content-Type: text/html; charset=utf-8');

// Load database configuration
$localConfigFile = __DIR__ . '/../config.local.php';
if (file_exists($localConfigFile)) {
    $dbConfig = require $localConfigFile;
} else {
    $dbConfig = [
        'host' => getenv('DB_HOST') ?: "localhost",
        'user' => getenv('DB_USER') ?: "root",
        'pass' => getenv('DB_PASS') ?: "",
        'name' => getenv('DB_NAME') ?: "deptnavigator_fet_st_db",
    ];
}

$host = $dbConfig['host'];
$user = $dbConfig['user'];
$pass = $dbConfig['pass'];
$name = $dbConfig['name'];

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMUTI Navigate - Database Installer</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; padding: 30px; }
        .card { max-width: 650px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        h1 { color: #FF7100; margin-top: 0; font-size: 24px; }
        .step { padding: 10px 14px; margin: 10px 0; border-radius: 8px; font-size: 15px; }
        .step.ok { background: #e8f5e9; color: #2e7d32; border-left: 4px solid #4caf50; }
        .step.err { background: #ffebee; color: #c62828; border-left: 4px solid #f44336; }
        .btn { display: inline-block; padding: 10px 20px; background: #FF7100; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 15px; }
        .btn:hover { background: #e06300; }
        code { background: #eee; padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>
<div class="card">
    <h1>🚀 ระบบติดตั้งฐานข้อมูลอัตโนมัติ (Database Installer)</h1>
    <p>ระบบนำทางหน่วยงาน มหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน</p>
    <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

<?php
// 1. Connect to MySQL server (without selecting DB first)
$mysqli = new mysqli($host, $user, $pass);
if ($mysqli->connect_error) {
    echo "<div class='step err'>❌ ไม่สามารถเชื่อมต่อ MySQL Server ($host): " . htmlspecialchars($mysqli->connect_error) . "<br><small>กรุณาตรวจสอบการตั้งค่าใน <code>config.local.php</code></small></div>";
    echo "</div></body></html>";
    exit;
}
echo "<div class='step ok'>✅ เชื่อมต่อ MySQL Server สำเร็จ ($host)</div>";

// 2. Create Database
$sqlCreateDb = "CREATE DATABASE IF NOT EXISTS `$name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!$mysqli->query($sqlCreateDb)) {
    echo "<div class='step err'>❌ ไม่สามารถสร้างฐานข้อมูล `$name`: " . htmlspecialchars($mysqli->error) . "</div>";
    echo "</div></body></html>";
    exit;
}
echo "<div class='step ok'>✅ ตรวจสอบและสร้างฐานข้อมูล <code>$name</code> (utf8mb4) สำเร็จ</div>";

$mysqli->select_db($name);
$mysqli->set_charset("utf8mb4");

// 3. Create Tables
$queries = [
    "Disable FK" => "SET FOREIGN_KEY_CHECKS = 0",
    "Drop search_logs" => "DROP TABLE IF EXISTS `search_logs`",
    "Drop department_images" => "DROP TABLE IF EXISTS `department_images`",
    "Drop services" => "DROP TABLE IF EXISTS `services`",
    "Drop departments" => "DROP TABLE IF EXISTS `departments`",
    "Drop building" => "DROP TABLE IF EXISTS `building`",
    "Drop users" => "DROP TABLE IF EXISTS `users`",
    "Enable FK" => "SET FOREIGN_KEY_CHECKS = 1",

    "Table: users" => "CREATE TABLE `users` (
        `usercode` VARCHAR(50) NOT NULL,
        `username` VARCHAR(100) NOT NULL,
        `password` VARCHAR(255) NOT NULL,
        `remember_token` VARCHAR(255) DEFAULT NULL,
        PRIMARY KEY (`usercode`),
        INDEX `idx_users_remember_token` (`remember_token`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "Table: building" => "CREATE TABLE `building` (
        `building_number` INT NOT NULL,
        `building_name` VARCHAR(255) NOT NULL,
        `lat` DECIMAL(10, 8) NOT NULL,
        `lng` DECIMAL(11, 8) NOT NULL,
        PRIMARY KEY (`building_number`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "Table: departments" => "CREATE TABLE `departments` (
        `department_id` INT AUTO_INCREMENT,
        `name_th` VARCHAR(255) NOT NULL,
        `name_en` VARCHAR(255) DEFAULT NULL,
        `building` INT DEFAULT NULL,
        `phone` VARCHAR(100) DEFAULT NULL,
        `email` VARCHAR(150) DEFAULT NULL,
        `weekday_business_hours` VARCHAR(255) DEFAULT NULL,
        `weekend_business_hours` VARCHAR(255) DEFAULT NULL,
        `website` TEXT DEFAULT NULL,
        `subordinate_to` VARCHAR(255) DEFAULT NULL,
        `note` TEXT DEFAULT NULL,
        PRIMARY KEY (`department_id`),
        INDEX `idx_departments_names` (`name_th`, `name_en`),
        INDEX `idx_departments_building` (`building`),
        CONSTRAINT `fk_departments_building`
            FOREIGN KEY (`building`) REFERENCES `building` (`building_number`)
            ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "Table: services" => "CREATE TABLE `services` (
        `service_id` INT AUTO_INCREMENT,
        `departments_id` INT NOT NULL,
        `service_name` VARCHAR(255) NOT NULL,
        `description` TEXT DEFAULT NULL,
        `keywords` TEXT DEFAULT NULL,
        `floor` INT DEFAULT 1,
        `room_number` VARCHAR(50) DEFAULT NULL,
        PRIMARY KEY (`service_id`),
        INDEX `idx_services_dept` (`departments_id`),
        INDEX `idx_services_name` (`service_name`),
        CONSTRAINT `fk_services_departments`
            FOREIGN KEY (`departments_id`) REFERENCES `departments` (`department_id`)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "Table: department_images" => "CREATE TABLE `department_images` (
        `image_id` INT AUTO_INCREMENT,
        `departments_id` INT NOT NULL,
        `image_name` VARCHAR(255) NOT NULL,
        `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`image_id`),
        INDEX `idx_images_dept` (`departments_id`),
        INDEX `idx_images_name` (`image_name`),
        CONSTRAINT `fk_images_departments`
            FOREIGN KEY (`departments_id`) REFERENCES `departments` (`department_id`)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "Table: search_logs" => "CREATE TABLE `search_logs` (
        `log_id` INT AUTO_INCREMENT,
        `user_input` VARCHAR(255) NOT NULL,
        `suggested_service_id` INT DEFAULT NULL,
        `suggested_department_id` INT DEFAULT NULL,
        `user_selected` TINYINT(1) DEFAULT 0,
        `searched_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`log_id`),
        INDEX `idx_logs_query_selected` (`user_input`, `user_selected`),
        INDEX `idx_logs_service` (`suggested_service_id`),
        INDEX `idx_logs_department` (`suggested_department_id`),
        CONSTRAINT `fk_logs_service`
            FOREIGN KEY (`suggested_service_id`) REFERENCES `services` (`service_id`)
            ON DELETE SET NULL,
        CONSTRAINT `fk_logs_department`
            FOREIGN KEY (`suggested_department_id`) REFERENCES `departments` (`department_id`)
            ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($queries as $label => $q) {
    if (!$mysqli->query($q)) {
        echo "<div class='step err'>❌ $label ล้มเหลว: " . htmlspecialchars($mysqli->error) . "</div>";
        echo "</div></body></html>";
        exit;
    }
}
echo "<div class='step ok'>✅ สร้างตารางและ Foreign Keys ครบทั้ง 6 ตารางสำเร็จ</div>";

// 4. Seed Initial Data
// Admin with native password_hash
$adminPass = password_hash("admin1234", PASSWORD_DEFAULT);
$stmtAdmin = $mysqli->prepare("INSERT INTO `users` (`usercode`, `username`, `password`) VALUES (?, ?, ?)");
$uCode = 'admin01';
$uName = 'Admin RMUTI';
$stmtAdmin->bind_param("sss", $uCode, $uName, $adminPass);
$stmtAdmin->execute();
$stmtAdmin->close();

// Buildings
$mysqli->query("INSERT INTO `building` (`building_number`, `building_name`, `lat`, `lng`) VALUES
    (1, 'อาคาร 1 (อาคารอำนวยการ / สำนักงานอธิการบดี)', 14.98747029, 102.11796446),
    (19, 'อาคาร 19 (สำนักวิทยบริการและเทคโนโลยีสารสนเทศ)', 14.98680000, 102.11920000),
    (35, 'อาคาร 35 (อาคารปฏิบัติการวิศวกรรมศาสตร์)', 14.98900000, 102.11750000),
    (36, 'อาคาร 36 (คณะวิศวกรรมศาสตร์และเทคโนโลยี)', 14.98825000, 102.11890000)");

// Departments
$mysqli->query("INSERT INTO `departments` (`department_id`, `name_th`, `name_en`, `building`, `phone`, `email`, `weekday_business_hours`, `website`, `subordinate_to`, `note`) VALUES
    (1, 'สำนักงานอธิการบดี', 'Office of the President', 1, '044-233000', 'president@rmuti.ac.th', 'จันทร์-ศุกร์ เวลา 08:30 - 16:30 น.', 'https://www.rmuti.ac.th', NULL, 'ศูนย์กลางบริหารงานมหาวิทยาลัย'),
    (2, 'สำนักส่งเสริมวิชาการและงานทะเบียน', 'Academic Promotion and Registration Office', 1, '044-233000 ต่อ 2200', 'academic@rmuti.ac.th', 'จันทร์-ศุกร์ เวลา 08:30 - 16:30 น.', 'https://academic.rmuti.ac.th', 'สำนักงานอธิการบดี', 'บริการงานทะเบียนและเอกสารทางการศึกษา'),
    (3, 'คณะวิศวกรรมศาสตร์และเทคโนโลยี', 'Faculty of Engineering and Technology', 36, '044-233000 ต่อ 3100', 'fet@rmuti.ac.th', 'จันทร์-ศุกร์ เวลา 08:30 - 16:30 น.', 'https://fet.rmuti.ac.th', NULL, 'เปิดสอนหลักสูตรวิศวกรรมศาสตร์'),
    (4, 'สำนักวิทยบริการและเทคโนโลยีสารสนเทศ', 'Library and Information Technology Office', 19, '044-233000 ต่อ 2800', 'arit@rmuti.ac.th', 'จันทร์-ศุกร์ เวลา 08:00 - 19:30 น.', 'https://arit.rmuti.ac.th', NULL, 'ห้องสมุดกลางและศูนย์คอมพิวเตอร์')");

// Services
$mysqli->query("INSERT INTO `services` (`service_id`, `departments_id`, `service_name`, `description`, `keywords`, `floor`, `room_number`) VALUES
    (1, 2, 'ขอใบรับรองผลการเรียน (Transcript)', 'บริการออกเอกสารรับรองผลการศึกษาสำหรับนักศึกษา', 'ทรานสคริปต์, ใบเกรด, ผลการเรียน, transcript, เอกสาร', 1, '101'),
    (2, 2, 'แจ้งสำเร็จการศึกษา (แจ้งจบ)', 'บริการยื่นคำร้องแจ้งสำเร็จการศึกษาและขออนุมัติปริญญาบัตร', 'แจ้งจบ, รับปริญญา, สำเร็จการศึกษา, จบการศึกษา, ยื่นจบ', 1, '102'),
    (3, 1, 'ชำระค่าธรรมเนียมการศึกษา (งานการเงิน)', 'จุดบริการรับชำระค่าลงทะเบียนเรียนและออกใบเสร็จรับเงิน', 'การเงิน, จ่ายค่าเทอม, ค่าธรรมเนียม, ใบเสร็จ, กองคลัง', 1, '105'),
    (4, 4, 'บริการยืม-คืนหนังสือและห้องค้นคว้า', 'บริการยืมคืนหนังสือวิชาการและห้องค้นคว้ากลุ่มย่อย', 'ยืมหนังสือ, ห้องสมุด, คืนหนังสือ, ห้องค้นคว้า, วิทยานิพนธ์', 2, '201'),
    (5, 3, 'สาขาวิชาวิศวกรรมคอมพิวเตอร์', 'สำนักงานสาขาวิชาวิศวกรรมคอมพิวเตอร์และปัญญาประดิษฐ์', 'คอมพิวเตอร์, วิศวะคอม, cpe, computer, โปรแกรมเมอร์, ai', 4, '402')");

echo "<div class='step ok'>✅ นำเข้าข้อมูลเริ่มต้น (Seed Data) และบัญชีผู้ดูแลระบบเสร็จสมบูรณ์</div>";
echo "<div style='margin-top: 20px; padding: 14px; background: #fff8e1; border-radius: 8px;'>";
echo "<strong>🔑 ข้อมูลเข้าสู่ระบบผู้ดูแลระบบ (Default Admin):</strong><br>";
echo "รหัสผู้ใช้: <code>admin01</code><br>";
echo "รหัสผ่าน: <code>admin1234</code>";
echo "</div>";

echo "<a href='../index.php' class='btn'>👉 ไปยังหน้าหลักของเว็บไซต์</a> ";
echo "<a href='../login/login.php' class='btn' style='background: #333;'>เข้าสู่ระบบจัดการ (Admin)</a>";
?>
</div>
</body>
</html>

-- ========================================================
-- RMUTI Department Navigate - Complete Database Setup Script
-- Character Set: utf8mb4 (Full Thai & International Unicode support)
-- ========================================================

CREATE DATABASE IF NOT EXISTS `deptnavigator_fet_st_db`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE `deptnavigator_fet_st_db`;

-- Drop existing tables in reverse foreign-key order
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `search_logs`;
DROP TABLE IF EXISTS `department_images`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `departments`;
DROP TABLE IF EXISTS `building`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- 1. Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE `users` (
    `usercode` VARCHAR(50) NOT NULL,
    `username` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `remember_token` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`usercode`),
    INDEX `idx_users_remember_token` (`remember_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Table structure for table `building`
-- --------------------------------------------------------
CREATE TABLE `building` (
    `building_number` INT NOT NULL,
    `building_name` VARCHAR(255) NOT NULL,
    `lat` DECIMAL(10, 8) NOT NULL,
    `lng` DECIMAL(11, 8) NOT NULL,
    PRIMARY KEY (`building_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Table structure for table `departments`
-- --------------------------------------------------------
CREATE TABLE `departments` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Table structure for table `services`
-- --------------------------------------------------------
CREATE TABLE `services` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Table structure for table `department_images`
-- --------------------------------------------------------
CREATE TABLE `department_images` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. Table structure for table `search_logs`
-- --------------------------------------------------------
CREATE TABLE `search_logs` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- SEED INITIAL DATA (ข้อมูลจำลองและข้อมูลเบื้องต้น มทร.อีสาน)
-- ========================================================

-- Initial Admin (usercode: admin01, username: Admin, password: admin1234)
INSERT INTO `users` (`usercode`, `username`, `password`, `remember_token`) VALUES
('admin01', 'Admin RMUTI', '$2y$10$wK1mJ.sT55pQ2jA6l5vD1.gQJ2qgWcEfZyG1Y7C1u8D6H3J5K7L9e', NULL);

-- Initial Buildings (อาคารใน มทร.อีสาน นครราชสีมา)
INSERT INTO `building` (`building_number`, `building_name`, `lat`, `lng`) VALUES
(1, 'อาคาร 1 (อาคารอำนวยการ / สำนักงานอธิการบดี)', 14.98747029, 102.11796446),
(19, 'อาคาร 19 (สำนักวิทยบริการและเทคโนโลยีสารสนเทศ)', 14.98680000, 102.11920000),
(35, 'อาคาร 35 (อาคารปฏิบัติการวิศวกรรมศาสตร์)', 14.98900000, 102.11750000),
(36, 'อาคาร 36 (คณะวิศวกรรมศาสตร์และเทคโนโลยี)', 14.98825000, 102.11890000);

-- Initial Departments
INSERT INTO `departments` (`department_id`, `name_th`, `name_en`, `building`, `phone`, `email`, `weekday_business_hours`, `weekend_business_hours`, `website`, `subordinate_to`, `note`) VALUES
(1, 'สำนักงานอธิการบดี', 'Office of the President', 1, '044-233000', 'president@rmuti.ac.th', 'จันทร์-ศุกร์ เวลา 08:30 - 16:30 น.', '', 'https://www.rmuti.ac.th', NULL, 'ศูนย์กลางบริหารงานมหาวิทยาลัย'),
(2, 'สำนักส่งเสริมวิชาการและงานทะเบียน', 'Academic Promotion and Registration Office', 1, '044-233000 ต่อ 2200', 'academic@rmuti.ac.th', 'จันทร์-ศุกร์ เวลา 08:30 - 16:30 น.', '', 'https://academic.rmuti.ac.th', 'สำนักงานอธิการบดี', 'บริการงานทะเบียนและเอกสารทางการศึกษา'),
(3, 'คณะวิศวกรรมศาสตร์และเทคโนโลยี', 'Faculty of Engineering and Technology', 36, '044-233000 ต่อ 3100', 'fet@rmuti.ac.th', 'จันทร์-ศุกร์ เวลา 08:30 - 16:30 น.', '', 'https://fet.rmuti.ac.th', NULL, 'เปิดสอนหลักสูตรวิศวกรรมศาสตร์ทุกสาขาวิชา'),
(4, 'สำนักวิทยบริการและเทคโนโลยีสารสนเทศ', 'Library and Information Technology Office', 19, '044-233000 ต่อ 2800', 'arit@rmuti.ac.th', 'จันทร์-ศุกร์ เวลา 08:00 - 19:30 น.', 'เสาร์ เวลา 08:30 - 16:30 น.', 'https://arit.rmuti.ac.th', NULL, 'ห้องสมุดกลางและศูนย์คอมพิวเตอร์');

-- Initial Services
INSERT INTO `services` (`service_id`, `departments_id`, `service_name`, `description`, `keywords`, `floor`, `room_number`) VALUES
(1, 2, 'ขอใบรับรองผลการเรียน (Transcript)', 'บริการออกเอกสารรับรองผลการศึกษาและทรานสคริปต์สำหรับนักศึกษา', 'ทรานสคริปต์, ใบเกรด, ผลการเรียน, transcript, เอกสาร, ขอใบรับรอง', 1, '101'),
(2, 2, 'แจ้งสำเร็จการศึกษา (แจ้งจบ)', 'บริการยื่นคำร้องแจ้งสำเร็จการศึกษาและขออนุมัติปริญญาบัตร', 'แจ้งจบ, รับปริญญา, สำเร็จการศึกษา, จบการศึกษา, ยื่นจบ', 1, '102'),
(3, 1, 'ชำระค่าธรรมเนียมการศึกษา (งานการเงิน)', 'จุดบริการรับชำระค่าลงทะเบียนเรียนและออกใบเสร็จรับเงิน', 'การเงิน, จ่ายค่าเทอม, ค่าธรรมเนียม, ใบเสร็จ, กองคลัง', 1, '105'),
(4, 4, 'บริการยืม-คืนหนังสือและห้องค้นคว้า', 'บริการยืมคืนหนังสือวิชาการ ตำราเรียน และห้องค้นคว้ากลุ่มย่อย', 'ยืมหนังสือ, ห้องสมุด, คืนหนังสือ, ห้องค้นคว้า, วิทยานิพนธ์, หนังสือ', 2, '201'),
(5, 3, 'สาขาวิชาวิศวกรรมคอมพิวเตอร์', 'สำนักงานสาขาวิชาวิศวกรรมคอมพิวเตอร์และปัญญาประดิษฐ์', 'คอมพิวเตอร์, วิศวะคอม, cpe, computer, โปรแกรมเมอร์, ai', 4, '402');

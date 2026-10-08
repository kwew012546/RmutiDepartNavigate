<div align="center">

<img src="images/rmuti.png" alt="RMUTI Logo" width="120"/>

# RMUTI Department Navigate
### ระบบแผนที่นำทางและสืบค้นข้อมูลหน่วยงาน มหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน

[![PHP Version](https://img.shields.io/badge/PHP-7.4%20|%208.x-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%20|%208.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Google Maps](https://img.shields.io/badge/Google%20Maps-API-4285F4?style=for-the-badge&logo=google-maps&logoColor=white)](https://developers.google.com/maps)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

<p align="center">
  ระบบเว็บแอปพลิเคชันแนะนำเส้นทางและค้นหาข้อมูลหน่วยงาน งานบริการ อาคาร และห้องปฏิบัติการ<br>
  ภายในมหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน (ศูนย์กลางนครราชสีมา) ด้วย Google Maps & Routes API<br>
  พร้อมระบบติดตั้งฐานข้อมูลอัตโนมัติ (Web Installer) และระบบค้นหาอัจฉริยะ (Fuzzy String Search)
</p>

</div>

---

## 📑 สารบัญ (Table of Contents)
- [📖 เกี่ยวกับโปรเจกต์ (About The Project)](#-เกี่ยวกับโปรเจกต์-about-the-project)
- [✨ ฟีเจอร์หลัก (Key Features)](#-ฟีเจอร์หลัก-key-features)
- [🛠️ เทคโนโลยีที่ใช้ (Tech Stack)](#️-เทคโนโลยีที่ใช้-tech-stack)
- [🗄️ โครงสร้างฐานข้อมูล (Database Schema)](#️-โครงสร้างฐานข้อมูล-database-schema)
- [📂 โครงสร้างโฟลเดอร์ (Project Structure)](#-โครงสร้างโฟลเดอร์-project-structure)
- [🚀 ขั้นตอนการติดตั้งและรันระบบ (Installation & Setup)](#-ขั้นตอนการติดตั้งและรันระบบ-installation--setup)
  - [1. ความต้องการของระบบ (Prerequisites)](#1-ความต้องการของระบบ-prerequisites)
  - [2. ดาวน์โหลดโปรเจกต์ (Clone)](#2-ดาวน์โหลดโปรเจกต์-clone-repository)
  - [3. ตั้งค่าฐานข้อมูล (Configuration)](#3-ตั้งค่าฐานข้อมูล-database-configuration)
  - [4. ติดตั้งฐานข้อมูล (Auto Installer / SQL Script)](#4-ติดตั้งฐานข้อมูล-automatic-installer--sql-import)
  - [5. ตั้งค่า Google Maps API Key](#5-การตั้งค่า-google-maps-api-key)
  - [6. เริ่มต้นใช้งาน](#6-เปิดใช้งานระบบ-run-application)
- [🔐 การตั้งค่าความปลอดภัยและ API Key (Configuration & Security)](#-การตั้งค่าความปลอดภัยและ-api-key-configuration--security)
- [👥 ผู้จัดทำ (Authors)](#-ผู้จัดทำ-authors)
- [📄 ใบอนุญาต (License)](#-ใบอนุญาต-license)

---

## 📖 เกี่ยวกับโปรเจกต์ (About The Project)

**RMUTI Department Navigate** เป็นระบบที่พัฒนาขึ้นเพื่อแก้ปัญหาความยากลำบากในการเดินทางและค้นหาสถานที่ภายใน **มหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน (ศูนย์กลางนครราชสีมา)** สำหรับนักศึกษา บุคลากร ผู้ปกครอง และบุคคลภายนอก 

ระบบช่วยให้ผู้ใช้งานสามารถ:
1. ค้นหาชื่อหน่วยงาน งานบริการ หรือคำสำคัญ (Keywords) ที่เกี่ยวข้องได้อย่างรวดเร็ว แม้พิมพ์คำผิดหรือสะกดไม่ครบ
2. ทราบตำแหน่งอาคาร ชั้น หมายเลขห้อง เวลาเปิด-ปิดทำการ และช่องทางติดต่อ
3. ดูภาพถ่ายอาคารและจุดสังเกตเพื่อความแม่นยำในการเดินทาง
4. เลือกจุดเริ่มต้นได้อย่างยืดหยุ่น (GPS ปัจจุบัน, เลือกอาคารเริ่มต้น, หรือลากหมุดบนแผนที่)
5. คำนวณและแสดงเส้นทางนำทางแบบเรียลไทม์ รองรับ 4 โหมดการเดินทาง (เส้นทางที่ดีที่สุด, รถยนต์, รถจักรยานยนต์, เดินเท้า)

---

## ✨ ฟีเจอร์หลัก (Key Features)

### 👤 ส่วนผู้ใช้งานทั่วไป (User Features)
- 🗺️ **Interactive Campus Map**: แผนที่มหาวิทยาลัยแบบโต้ตอบ รองรับการซูม เลื่อน และแสดง Custom Marker ของแต่ละอาคารอย่างชัดเจน
- 📍 **Flexible Starting Points (จุดเริ่มต้นยืดหยุ่น)**:
  - 📡 **Current GPS Location**: ตรวจจับพิกัดปัจจุบันอัตโนมัติ (พร้อมระบบ Fallback หากไม่สามารถดึง GPS ได้)
  - 🏢 **Building Selection Dropdown**: เลือกจุดเริ่มต้นจากรายชื่ออาคารภายในมหาวิทยาลัย
  - 📌 **Draggable Pin**: ลากหมุดเพื่อระบุตำแหน่งเริ่มต้นบนแผนที่ได้อิสระ พร้อมปุ่มรีเซ็ตตำแหน่ง
- 🔍 **Smart Fuzzy Search**: ระบบค้นหาอัจฉริยะที่รองรับทั้งภาษาไทยและอังกฤษ พร้อมระบบ **Fuzzy Matching** (คำนวณผ่าน Levenshtein Distance & String Similarity) ช่วยค้นหาเจอแม้สะกดผิดหรือพิมพ์ไม่ครบคำ
- 🧭 **Multi-Modal Route Navigation**: คำนวณเส้นทางผ่าน **Google Routes API (v2)** รองรับ 4 โหมด:
  - 🌟 **Best Path**: เส้นทางแนะนำที่ดีที่สุด
  - 🚗 **Car**: เส้นทางสำหรับรถยนต์
  - 🏍️ **Motorcycle**: เส้นทางสำหรับรถจักรยานยนต์
  - 🚶 **Walk**: เส้นทางสำหรับเดินเท้า
- 📋 **Comprehensive Department Profile**: แสดงข้อมูลหน่วยงานครบครัน:
  - ชื่อหน่วยงาน (ไทย/อังกฤษ), สังกัด
  - อาคาร, ชั้น, หมายเลขห้อง
  - ข้อมูลติดต่อ (เบอร์โทรศัพท์, อีเมล, ลิงก์เว็บไซต์)
  - เวลาทำการ (จันทร์-ศุกร์, เสาร์-อาทิตย์ พร้อมระบบวิเคราะห์เวลาเปิด-ปิด)
  - แกลเลอรีรูปภาพอาคารและหน่วยงานแบบสไลด์โชว์
- ⚡ **Client-Side Caching**: จัดเก็บพิกัดอาคารและหน่วยงานบน `sessionStorage` ลดภาระการโหลดข้อมูลซ้ำซ้อนจากเซิร์ฟเวอร์
- 📊 **Search Analytics Logging**: บันทึกสถิติคำค้นหาและการคลิกเลือกผลลัพธ์ของผู้ใช้ เพื่อนำมาเพิ่มน้ำหนักความแม่นยำในการค้นหาครั้งถัดไป

### 🛠️ ส่วนผู้ดูแลระบบ (Admin Features)
- 🔒 **Authentication & Middleware**:
  - ระบบตรวจสอบสิทธิ์ส่วนกลาง (`check_auth.php`) ป้องกันการเข้าถึง Controller และหน้าจัดการโดยไม่ได้รับอนุญาต
  - เข้ารหัสผ่านด้วย `password_hash()` และ `password_verify()`
  - ระบบจำการเข้าสู่ระบบอย่างปลอดภัย (Remember Token)
  - สคริปต์สร้างผู้ดูแลระบบเริ่มต้น (`insert_user.php`)
- ➕ **เพิ่มข้อมูลหน่วยงานและอาคาร (Add Department & Building)**:
  - ปักหมุดระบุพิกัด Latitude / Longitude บนแผนที่แบบ Interactive
  - เลือกระบุอาคารเดิม หรือสร้างอาคารใหม่
  - กำหนดบริการย่อย คำอธิบาย คำสำคัญ (Keywords) ชั้น และห้องได้หลายรายการ
  - อัปโหลดภาพถ่ายอาคารและหน่วยงานพร้อมระบบตรวจสอบนามสกุลไฟล์
- ✏️ **แก้ไขและอัปเดตข้อมูล (Update Department)**: แก้ไขข้อมูลหน่วยงาน ปรับปรุงบริการ และอัปเดตรูปภาพ
- 🗑️ **ลบข้อมูล (Delete Department)**: ระบบลบข้อมูลหน่วยงานพร้อมลบข้อมูลบริการ รูปภาพ และประวัติการค้นหาที่เชื่อมโยงกันแบบ Transaction ปลอดภัย

---

## 🛠️ เทคโนโลยีที่ใช้ (Tech Stack)

### Frontend
- **HTML5 / CSS3 / JavaScript (ES6+ Modules)**
- **Google Maps JavaScript API** (Maps, Marker, Geometry, Custom Label Overlays)
- **Google Routes API (Directions v2)**
- **jQuery 3.6.0**
- **Font Awesome 5** (Icons)

### Backend
- **PHP (Native 7.4+ / 8.x)** (Modular Controller Architecture, Prepared Statements ป้องกัน SQL Injection)
- **MySQL / MariaDB** (Default Charset: `utf8mb4_unicode_ci`)

### Algorithms & Optimizations
- **Fuzzy String Matching**: ใช้อัลกอริทึม `levenshtein()` และ `similar_text()` สำหรับสืบค้นคำใกล้เคียง
- **Polyline Decoding**: ถอดรหัสเส้นทาง Polyline พิกัดความละเอียดสูงจาก Google Routes API เพื่อวาดลงบนแผนที่
- **Database Indexing**: ดัชนีฐานข้อมูลเฉพาะทาง เพิ่มความเร็วในการค้นหาและดึงพิกัด

---

## 🗄️ โครงสร้างฐานข้อมูล (Database Schema)

ระบบใช้งานตารางฐานข้อมูลหลักดังนี้:

| ตาราง (Table) | หน้าที่และความสำคัญ |
|---|---|
| `departments` | เก็บข้อมูลหลักของหน่วยงาน (ชื่อไทย/อังกฤษ, สังกัด, เวลาทำการ, เบอร์โทร, อีเมล, เว็บไซต์, อาคาร) |
| `building` | เก็บข้อมูลอาคาร รหัสอาคาร ชื่ออาคาร และพิกัดละติจูด/ลองจิจูด (`lat`, `lng`) |
| `services` | เก็บข้อมูลงวด/บริการย่อยของหน่วยงาน, คำอธิบาย, คำสำคัญ (Keywords), ชั้น, และหมายเลขห้อง |
| `department_images` | เก็บชื่อไฟล์ภาพถ่ายของแต่ละหน่วยงาน (`image_name`) |
| `users` | เก็บข้อมูลผู้ดูแลระบบ รหัสผ่านแบบ Hash และ Remember Token |
| `search_logs` | บันทึก Log คำค้นหาของผู้ใช้และบริการ/หน่วยงานที่คลิกเลือก |

---

## 📂 โครงสร้างโฟลเดอร์ (Project Structure)

```plaintext
RmutiDepartNavigate/
├── admin/                           # ระบบจัดการสำหรับผู้ดูแลระบบ
│   ├── admin_controller/            # Backend API สำหรับ Insert, Update, Delete
│   │   ├── delete_data.php
│   │   ├── insert_data.php
│   │   ├── show_data.php
│   │   ├── update_data.php
│   │   ├── update_image.php
│   │   ├── update_service.php
│   │   └── uploads/                 # โฟลเดอร์เก็บรูปภาพหน่วยงานที่อัปโหลด
│   ├── admin_sub_content/           # UI ส่วนแท็บจัดการ (Add, Edit, Update, Delete)
│   ├── adminpage.php                # หน้าหลักของ Admin Dashboard (พร้อมตรวจสอบสิทธิ์)
│   ├── map_for_admin.js             # จัดการแผนที่และลากหมุดสำหรับ Admin
│   └── style_admin_page.css
├── database/                        # เครื่องมือและสคริปต์ฐานข้อมูล
│   ├── add_performance_indexes.sql # สคริปต์เพิ่ม Index เพิ่มความเร็ว Query
│   ├── install.php                  # หน้าเว็บติดตั้งฐานข้อมูลอัตโนมัติ (Web Installer)
│   ├── schema_and_seed.sql          # สคริปต์สร้างตารางพร้อมข้อมูลเริ่มต้น (UTF-8)
│   └── upgrade_schema.sql           # สคริปต์อัปเกรดฐานข้อมูลโครงสร้างเวลาทำการ
├── images/                          # รูปภาพส่วนกลาง (โลโก้, ไอคอน)
│   └── rmuti.png
├── login/                           # ระบบเข้าสู่ระบบสำหรับ Admin
│   ├── controller/                  # ตัวจัดการ Session, Login, Logout, Auth
│   │   ├── auth.php
│   │   ├── check_auth.php           # Middleware ตรวจสอบสิทธิ์ผู้ดูแลระบบ
│   │   ├── insert_user.php          # สคริปต์เพิ่มผู้ดูแลระบบ (Hash รหัสผ่าน)
│   │   ├── login_user.php
│   │   └── logout.php
│   ├── login.js
│   ├── login.php
│   └── style_login.css
├── user/                            # ส่วนติดต่อและตรรกะสำหรับผู้ใช้ทั่วไป
│   ├── user_controller_js/          # โมดูล JavaScript สำหรับฝั่ง Client
│   │   ├── custom_label_overlay.js  # แสดงป้ายชื่ออาคารบนแผนที่
│   │   ├── decode_and_draw_polyline.js # ถอดรหัสและวาดเส้นทางบนแผนที่
│   │   ├── get_routes.js            # เรียก Google Routes API คำนวณเส้นทาง
│   │   ├── search_fuzzy.js          # จัดการกล่องค้นหาแบบ Auto-complete & Fuzzy
│   │   └── show_custom_route_info.js
│   ├── user_controller_php/         # API Endpoint ดึงข้อมูลสำหรับแผนที่
│   │   ├── department_data.php      # รายละเอียดหน่วยงาน (ข้อมูล, รูปภาพ, เวลาทำการ)
│   │   ├── department_position.php  # พิกัดอาคารและหน่วยงาน
│   │   ├── fuzzy_matching.php       # ค้นหาและจับคู่ความคล้ายคลึงของข้อความ
│   │   └── search_log.php
│   ├── map_for_user.js              # สคริปต์หลักควบคุมแผนที่ฝั่ง User (Caching & Controls)
│   └── style_user_page.css
├── .gitignore                       # ละเว้นไฟล์ Config ส่วนบุคคลและไฟล์ขยะ
├── config.example.php               # ไฟล์แม่แบบการตั้งค่าฐานข้อมูลท้องถิ่น
├── connect.example.php              # ตัวอย่างไฟล์เชื่อมต่อฐานข้อมูล
├── connect.php                      # ตัวจัดการเชื่อมต่อฐานข้อมูล (รองรับ config.local.php & env)
├── index.php                        # หน้าแรกของแอปพลิเคชัน (User Navigation Page)
└── README.md                        # เอกสารแนะนำและคู่มือการใช้งานโปรเจกต์
```

---

## 🚀 ขั้นตอนการติดตั้งและรันระบบ (Installation & Setup)

### 1. ความต้องการของระบบ (Prerequisites)
- **Web Server**: Apache (แนะนำ [XAMPP](https://www.apachefriends.org/) หรือ [Laragon](https://laragon.org/))
- **PHP**: เวอร์ชัน 7.4 ขึ้นไป (แนะนำ PHP 8.0+)
- **MySQL / MariaDB**: 5.7+
- **Google Cloud Platform Account**: สำหรับเปิดใช้งาน Google Maps & Routes API Key

### 2. ดาวน์โหลดโปรเจกต์ (Clone Repository)
นำโปรเจกต์ไปวางไว้ในโฟลเดอร์ Web Root ของคุณ (เช่น `C:\xampp\htdocs\RmutiDepartNavigate` หรือ `C:\laragon\www\RmutiDepartNavigate`):
```bash
git clone https://github.com/kwew012546/RmutiDepartNavigate.git
```

### 3. ตั้งค่าฐานข้อมูล (Database Configuration)
คัดลอกไฟล์ `config.example.php` เป็น `config.local.php` (ไฟล์นี้จะไม่ถูกส่งขึ้น Git เพื่อความปลอดภัย):
```bash
cp config.example.php config.local.php
```
จากนั้นแก้ไขข้อมูลในไฟล์ `config.local.php` ให้ตรงกับฐานข้อมูลในเครื่องของคุณ:
```php
return [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'deptnavigator_fet_st_db',
];
```

### 4. ติดตั้งฐานข้อมูล (Automatic Installer / SQL Import)
สามารถเลือกติดตั้งได้ **2 วิธี**:

* **วิธีที่ 1 (แนะนำ - สะดวกที่สุดผ่าน Web Installer)**:
  เปิดเบราว์เซอร์แล้วเข้าไปที่:
  ```text
  http://localhost/RmutiDepartNavigate/database/install.php
  ```
  ระบบจะสร้างฐานข้อมูล ตาราง ดัชนี (Indexes) และข้อมูลเริ่มต้นให้อัตโนมัติในคลิกเดียว

* **วิธีที่ 2 (นำเข้าไฟล์ SQL ด้วยตนเอง)**:
  เปิด phpMyAdmin แล้วนำเข้า (Import) ไฟล์ต่อไปนี้ตามลำดับ:
  1. `database/schema_and_seed.sql` (สร้างฐานข้อมูลและข้อมูลเริ่มต้น)
  2. `database/add_performance_indexes.sql` (เพิ่มดัชนีประสิทธิภาพ)

### 5. การตั้งค่า Google Maps API Key
เปิดไฟล์ต่อไปนี้ แล้วแทนที่ `YOUR_GOOGLE_MAPS_API_KEY` ด้วย API Key ของคุณ:
- `index.php` (บรรทัดนำเข้า Google Maps Script)
- `admin/adminpage.php`
- `user/user_controller_js/get_routes.js`

> **Note**: บริการที่ต้องเปิดใน [Google Cloud Console](https://console.cloud.google.com/):
> - **Maps JavaScript API**
> - **Routes API** (หรือ Directions API)

### 6. เปิดใช้งานระบบ (Run Application)
เปิดเว็บเบราว์เซอร์แล้วเข้าสู่ URL:
- **หน้าผู้ใช้งานทั่วไป**: `http://localhost/RmutiDepartNavigate/index.php`
- **หน้าระบบผู้ดูแลระบบ**: `http://localhost/RmutiDepartNavigate/login/login.php`

---

## 🔐 การตั้งค่าความปลอดภัยและ API Key (Configuration & Security)

> [!WARNING]
> เพื่อความปลอดภัยของระบบและบัญชี Google Cloud ของคุณ:
> 1. **ห้าม Commit ไฟล์ `config.local.php`** ที่มีรหัสผ่านฐานข้อมูลจริงขึ้นสู่ GitHub โดยเด็ดขาด (ตั้งค่า `.gitignore` ไว้เรียบร้อยแล้ว)
> 2. ควรตั้งค่า **HTTP Referrer Restrictions** บน Google Cloud Console ให้รับคำขอเฉพาะโดเมนของคุณเท่านั้น
> 3. โค้ดในโปรเจกต์มีการใช้ **Prepared Statements** ในทุกจุดที่มีการรับค่าจากผู้ใช้ เพื่อป้องกัน SQL Injection

---

## 👥 ผู้จัดทำ (Authors)

- **นักศึกษาผู้พัฒนาโครงการ**: มหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน (นครราชสีมา)
- **GitHub**: [@kwew012546](https://github.com/kwew012546)

---

## 📄 ใบอนุญาต (License)

โปรเจกต์นี้เผยแพร่ภายใต้ใบอนุญาต [MIT License](LICENSE) สามารถนำไปศึกษา พัฒนาต่อยอด หรือประยุกต์ใช้งานได้ตามเงื่อนไขของสัญญาอนุญาต
<?php
header('Content-Type: application/json');
include '../../connect.php';

$name = $_GET['name'] ?? '';

$sql = "SELECT * FROM departments WHERE name_th = ? OR name_en = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $name, $name);
$stmt->execute();
$result = $stmt->get_result();

$response = [
    'name_html' => '',
    'detail_html' => '',
    'image_html' => ''
];

if ($row = $result->fetch_assoc()) {
    $id = $row['department_id'];

    $sqlserv = "SELECT * FROM services WHERE departments_id = ?";
    $stmtserv = $conn->prepare($sqlserv);
    $stmtserv->bind_param("i", $id);
    $stmtserv->execute();
    $resultserv = $stmtserv->get_result();

    $name_th = htmlspecialchars($row['name_th']);
    $name_en = htmlspecialchars($row['name_en']);

    $images = [];
    $image_stmt = $conn->prepare("SELECT * FROM department_images WHERE departments_id = ?");
    $image_stmt->bind_param("i", $row['department_id']);
    $image_stmt->execute();
    $image_result = $image_stmt->get_result();

    while ($img_row = $image_result->fetch_assoc()) {
        $images[] = $img_row;
    }

    $image_html = '';
    if (count($images) > 0) {
        $image_html .= '<div class="container">';
        
        foreach ($images as $index => $img) {
            $img_src = '/ProjectV5/admin/admin_controller/uploads/' . htmlspecialchars($img['image_name']);
            $num = $index + 1;
            $total = count($images);

            $image_html .= "
            <div class='mySlides'>
                <img src='{$img_src}' style='width:100%; height: 200px'>
            </div>";
        }

        if (count($images) > 1) {
            $image_html .= "
            <a class='prev' onclick='plusSlides(-1)'>&#10094;</a>
            <a class='next' onclick='plusSlides(1)'>&#10095;</a>

            <div class='row'>";

            foreach ($images as $index => $img) {
                $img_src = '/ProjectV5/admin/admin_controller/uploads/' . htmlspecialchars($img['image_name']);
                $slide = $index + 1;
                $image_html .= "
            <div class='column'>
                <img class='imgslide cursor' src='{$img_src}' style='width:100%; height: 50px;' onclick='currentSlide({$slide})'>
            </div>";
            }
            $image_html .= '</div>';
        }
        $image_html .= '</div>';
    }

    $response['image_html'] = $image_html;

    $phone = empty($row['phone']) ? '-' : htmlspecialchars($row['phone']);
    $email = empty($row['email']) ? '-' : htmlspecialchars($row['email']);
    $websites = [];
    if (!empty($row['website'])) {
        $websites = explode(',', $row['website']);
    }
    $response['name_html'] = "<h3>$name_th <br> $name_en</h3>";

    $weekday = htmlspecialchars($row['weekday_business_hours']);
    $weekend = htmlspecialchars($row['weekend_business_hours']);
    $dayHours = [];

    function parseBusinessHours($text)
    {
        $daysMap = [
            'จันทร์' => 'วันจันทร์',
            'อังคาร' => 'วันอังคาร',
            'พุธ' => 'วันพุธ',
            'พฤหัสบดี' => 'วันพฤหัสบดี',
            'ศุกร์' => 'วันศุกร์',
            'เสาร์' => 'วันเสาร์',
            'อาทิตย์' => 'วันอาทิตย์'
        ];

        $resultTime = [];

        if (preg_match('/(.*?)เวลา\s*([0-9:\s\-]+น\.)/', $text, $matches)) {
            $daysText = trim($matches[1]);
            $timeText = 'เวลา ' . trim($matches[2]);

            if (preg_match('/(\S+)\s*-\s*(\S+)/u', $daysText, $rangeMatch)) {
                $start = $rangeMatch[1];
                $end = $rangeMatch[2];
                $keys = array_keys($daysMap);
                $startIndex = array_search($start, $keys);
                $endIndex = array_search($end, $keys);

                if ($startIndex !== false && $endIndex !== false) {
                    for ($i = $startIndex; $i <= $endIndex; $i++) {
                        $dayFull = $daysMap[$keys[$i]];
                        $resultTime[$dayFull] = $timeText;
                    }
                }
            } else {
                foreach ($daysMap as $short => $full) {
                    if (mb_strpos($daysText, $short) !== false) {
                        $resultTime[$full] = $timeText;
                    }
                }
            }
        }

        return $resultTime;
    }

    $dayHours = array_merge(
        parseBusinessHours($weekday),
        parseBusinessHours($weekend)
    );

    $allDays = [
        'วันจันทร์',
        'วันอังคาร',
        'วันพุธ',
        'วันพฤหัสบดี',
        'วันศุกร์',
        'วันเสาร์',
        'วันอาทิตย์'
    ];

    $business_hours_html = '';
    foreach ($allDays as $day) {
        $time = $dayHours[$day] ?? 'ปิดทำการ';
        $business_hours_html .= "
        <div class='business-row'>
            <div class='day-name'>{$day}</div>
            <div class='day-time'>{$time}</div>
        </div>";
    }

    $response['detail_html'] .= "
        <div class='business-hours-container'>
            <button class='accordion'><i class='fa fa-clock' style='font-size:16px; margin-right: 12px;'></i>เวลาทำการ</button>
            <div class='panel' style='margin-top: 10px;'>$business_hours_html</div>
        </div>
    ";

    $services_html = '';
    while ($serv_row = $resultserv->fetch_assoc()) {
        $service_name = htmlspecialchars($serv_row['service_name']);
        $service_description = !empty($serv_row['description']) ? htmlspecialchars($serv_row['description']) : '-';
        $service_floor = htmlspecialchars($serv_row['floor']);
        $service_room = !empty($serv_row['room_number']) ? htmlspecialchars($serv_row['room_number']) : '-';
        $services_html .= "
            <button class='accordion-service' style='padding-left: 12px;'>- $service_name</button>
            <div class='panel-service'>คำอธิบาย: $service_description 
            <div style='color: #FF7100;'>ชั้น $service_floor ห้อง $service_room</div> </div>";
    }

    if (empty($services_html)) {
        $services_html = "<p>ไม่มีบริการที่เกี่ยวข้อง</p>";
    }

    $response['detail_html'] .= "
    <div class='business-hours-container'>
        <button class='accordion''><i class='fa fa-cogs' style='font-size:16px; margin-right: 9px;'></i>บริการ
        <i class='fa fa-chevron-down' style='font-size:16px; position: absolute; right: 24px; margin-top: 5px;'></i></button>
        <div class='panel'>$services_html</div>
    </div>
    ";

    $response['detail_html'] .= "<p><i class='fa fa-phone' style='font-size:16px; margin-right: 12px;'></i>$phone</p>";
    $response['detail_html'] .= "<p><i class='fa fa-envelope' style='font-size:16px; margin-right: 8px;'></i>
        <span onclick=\"copyText(this)\" style='cursor: pointer; font-size: 16px;'>$email</span></p>";

    if (count($websites) > 0) {
        $response['detail_html'] .= "<p>";
        foreach ($websites as $i => $site) {
            $site = trim($site);
            $display = htmlspecialchars($site);
            if (!preg_match('#^https?://#', $site)) {
                $site = "http://$site";
            }

            $response['detail_html'] .= "<i class='fa fa-globe' style='font-size:16px; margin-right: 8px;'></i>
                    <a href='" . htmlspecialchars($site, ENT_QUOTES) . "' target='_blank' 
                       style='margin-right: 10px; text-decoration: none; color: inherit;'>$display</a>";

            if ($i < count($websites) - 1) {
                $response['detail_html'] .= "<br><br>";
            }
        }
        $response['detail_html'] .= "</p>";
    } else {
        $response['detail_html'] .= "<p><i class='fa fa-globe' style='font-size:16px; margin-right: 8px;'></i>-</p>";
    }
    $note = htmlspecialchars($row['note']);
    if (empty($row['note'])) {
        $response['detail_html'] .= "";
    } else {
        $response['detail_html'] .= "<p><i class='fa fa-info-circle' style='font-size:16px; margin-right: 12px;'></i>$note</p>";
    }

} else {
    $response['detail_html'] = '<p>ไม่พบข้อมูลของหน่วยงาน</p>';
}

echo json_encode($response);
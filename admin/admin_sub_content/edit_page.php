<?php
include '../../connect.php';
$name = $_GET['name'] ?? '';

$sql = "SELECT * FROM departments WHERE name_th = ? OR name_en = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $name, $name);
$stmt->execute();
$result = $stmt->get_result();

$building_change = [];
$sql = "SELECT DISTINCT building_number, building_name AS building, lat, lng FROM building";
$building = $conn->query($sql);
if ($building && $building->num_rows > 0) {
    while ($row = $building->fetch_assoc()) {
        $building_change[] = $row;
    }
}

if ($result->num_rows == 0) {
    echo "ไม่พบข้อมูลหน่วยงาน";
    exit;
}

$data = $result->fetch_assoc();

function extract_times($text) {
    if (preg_match('/(\d{2}:\d{2})\s*-\s*(\d{2}:\d{2})/', $text, $matches)) {
        return [$matches[1], $matches[2]];
    }
    return [null, null];
}

$weekday_text = $data['weekday_business_hours'] ?? '';
$weekend_text = $data['weekend_business_hours'] ?? '';


[$weekday_start, $weekday_stop] = extract_times($weekday_text);
[$weekend_start, $weekend_stop] = extract_times($weekend_text);

$workday = 'Monday-Friday';
if (stripos($weekend_text, 'อาทิตย์') !== false) {
    $workday = 'Everyday';
} elseif (!empty($weekend_text)) {
    $workday = 'Monday-Saturday';

}

$services = [];
$service_stmt = $conn->prepare("SELECT * FROM services WHERE departments_id = ?");
$service_stmt->bind_param("i", $data['department_id']);
$service_stmt->execute();
$service_result = $service_stmt->get_result();

while ($row = $service_result->fetch_assoc()) {
    $services[] = $row;
}

$images = [];
$image_stmt = $conn->prepare("SELECT * FROM department_images WHERE departments_id = ?");
$image_stmt->bind_param("i", $data['department_id']);
$image_stmt->execute();
$image_result = $image_stmt->get_result();

while ($row = $image_result->fetch_assoc()) {
    $images[] = $row;
}
?>
<div style="display: flex; margin-bottom: 20px;">
    <button type="button" style="background: none; border: none; color: inherit; cursor: pointer;" onclick="goBack()">
        <i class="fa fa-chevron-left"></i> ย้อนกลับ</button>
    <h2 style="margin: auto;">แก้ไขข้อมูลหน่วยงาน: <?= htmlspecialchars($data['name_th']) ?></h2>
</div>
<form method="post" id="updateForm">
    <input type="hidden" name="action" value="updateform">
    <input type="hidden" name="id" id="id" value="<?= $data['department_id'] ?>">

    <label>ชื่อภาษาไทย:</label>
    <input type="text" name="name_th" id="name_th" value="<?= htmlspecialchars($data['name_th']) ?>"><br><br>

    <label>ชื่อภาษาอังกฤษ:</label>
    <input type="text" name="name_en" id="name_en" value="<?= htmlspecialchars($data['name_en']) ?>"><br><br>

    <label>อาคาร:</label>
    <select id="building" style="margin-left: 10px; padding: 5px;" name="building">
        <?php
        usort($building_change, fn($a, $b) => $a['building_number'] <=> $b['building_number']);

        $selected_building_number = $data['building'] ?? '';

        foreach ($building_change as $b):
            $value = $b['building_number'] . "|" . $b['building'] . "|" . $b['lat'] . "|" . $b['lng'];
            $selected = ($b['building_number'] == $selected_building_number) ? 'selected' : '';
            $label = $b['building_number'] > 500 ? $b['building'] : "อาคาร {$b['building_number']} {$b['building']}";
            ?>
            <option value="<?= htmlspecialchars($value) ?>" <?= $selected ?>>
                <?= htmlspecialchars($label) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>โทรศัพท์:</label>
    <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($data['phone']) ?>"><br><br>

    <label>อีเมล:</label>
    <input type="email" name="email" id="email" value="<?= htmlspecialchars($data['email']) ?>"><br><br>
    
    <div>
        <label>วันทำการ: </label>
        <label><input type="radio" name="updateworkday" value="Monday-Friday" <?= $workday == 'Monday-Friday' ? 'checked' : '' ?> 
        onclick="toggleTimeFieldsUpdate()"> จันทร์-ศุกร์</label>
        <label><input type="radio" name="updateworkday" value="Monday-Saturday" <?= $workday == 'Monday-Saturday' ? 'checked' : '' ?> 
        onclick="toggleTimeFieldsUpdate()"> จันทร์-เสาร์</label>
        <label><input type="radio" name="updateworkday" value="Everyday" <?= $workday == 'Everyday' ? 'checked' : '' ?> 
        onclick="toggleTimeFieldsUpdate()"> ทุกวัน</label>
        <br><br>

        <label>เวลาทำการ (จ-ศ): </label>
        <input type="time" id="updatetimestart" name="weekday_start" value="<?= htmlspecialchars($weekday_start) ?>">
        <label>ถึง</label>
        <input type="time" id="updatetimestop" name="weekday_stop" value="<?= htmlspecialchars($weekday_stop) ?>">

        <div id="extraTimeFieldsUpdate"      
        data-weekend-start="<?= htmlspecialchars($weekend_start ?? '') ?>" 
        data-weekend-stop="<?= htmlspecialchars($weekend_stop ?? '') ?>">
        </div>
    </div><br>

    <label>เว็บไซต์:</label>
    <input type="text" name="website" id="website" value="<?= htmlspecialchars($data['website']) ?>"><br><br>

    <label>สังกัด:</label>
    <input type="text" name="subordinate_to" id="subordinate_to"
        value="<?= htmlspecialchars($data['subordinate_to']) ?>"><br><br>

    <label>หมายเหตุ:</label>
    <input type="text" name="note" id="note" value="<?= htmlspecialchars($data['note']) ?>"><br><br>

    <button type="submit" id="submit"><i class="fa fa-check"></i></button>
</form>
<h3>บริการของหน่วยงาน</h3>
<?php if (count($services) > 0): ?>
    <?php foreach ($services as $service): ?>
        <form method="post" class="service-form" style="border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;">
            <input type="hidden" name="service_id" value="<?= $service['service_id'] ?>">

            <label>ชื่อบริการ:</label>
            <input type="text" name="service_name" value="<?= htmlspecialchars($service['service_name']) ?>"><br><br>

            <label>คำอธิบาย:</label>
            <input type="text" name="description" value="<?= htmlspecialchars($service['description']) ?>"
            style="width: 80%;"><br><br>

            <label>คีย์เวิร์ด:</label>
            <input type="text" name="keywords" value="<?= htmlspecialchars($service['keywords']) ?>"
                style="width: 80%;"><br><br>

            <label>ชั้น:</label>
            <input type="number" name="floor" value="<?= htmlspecialchars($service['floor']) ?>"
                style="text-align: center; width: 20px;">

            <label>ห้อง:</label>
            <input type="text" name="room" value="<?= htmlspecialchars($service['room_number']) ?>"
                style="text-align: center; width: 60px;"><br><br>

            <button type="submit" name="updateService" class="updateService">💾 บันทึก</button>
            <button type="submit" name="deleteService" class="deleteService">🗑️ ลบ</button>
        </form>
    <?php endforeach; ?>
    <button id="AddService" onclick="addNewService()">
        เพิ่มข้อมูลบริการ
    </button>
    <form method="post" id="newServiceForm" style="border: 1px solid #ccc; padding: 10px; margin-bottom: 15px; display: none;">
        <input type="hidden" name="departments_id" value="<?= $data['department_id'] ?>">   
    </form>
    <div id="new_services_container"></div>
<?php else: ?>
    <p>ไม่มีข้อมูลบริการในหน่วยงานนี้</p>
    <button id="AddService" onclick="addNewService()">
        เพิ่มข้อมูลบริการ
    </button>
    <form method="post" id="newServiceForm" style="border: 1px solid #ccc; padding: 10px; margin-bottom: 15px; display: none;">
        <input type="hidden" name="departments_id" value="<?= $data['department_id'] ?>">
    </form>
<?php endif; ?>
<h3>รูปภาพของหน่วยงาน</h3>
<?php if (count($images) > 0): ?>
    <div class="slideshow-container">
        <?php foreach ($images as $img): ?>
            <div class="mySlides">
                <img src="admin_controller/uploads/<?= htmlspecialchars($img['image_name']) ?>" width="400"
                    height="400"><br><br>
                <div style="display: flex; justify-content: center;">
                    <form id="uploadImage" enctype="multipart/form-data">
                        <input type="hidden" name="departments_id" value="<?= $data['department_id'] ?>">
                        <label id="upload_image" for="upload_image_<?= $data['department_id'] ?>">
                            <i class="fa fa-plus"></i>
                        </label>
                        <input type="file" name="image_file[]" id="upload_image_<?= $data['department_id'] ?>" style="display: none;"
                            multiple>
                    </form>
                    <form class="delete-image-form" data-image-id="<?= $img['image_id'] ?>">
                        <input type="hidden" name="image_id" value="<?= $img['image_id'] ?>">
                        <input type="hidden" name="departments_id" value="<?= $data['department_id'] ?>">
                        <button id="Btndelete" type="submit">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (count($images) > 1): ?>
        <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
        <a class="next" onclick="plusSlides(1)">&#10095;</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <p>ไม่มีข้อมูลรูปภาพในหน่วยงานนี้</p>
    <div style="margin: auto;">
        <form id="uploadImage" enctype="multipart/form-data">
            <input type="hidden" name="departments_id" value="<?= $data['department_id'] ?>">
            <label id="upload_image" for="upload_image_<?= $data['department_id'] ?>">
                <i class="fa fa-plus"></i>
            </label>
            <input type="file" name="image_file[]" id="upload_image_<?= $data['department_id'] ?>" style="display: none;" multiple>
        </form>
    </div>
<?php endif; ?>

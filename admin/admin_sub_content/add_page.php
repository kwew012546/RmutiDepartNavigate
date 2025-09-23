<div id="map"></div>
        <div style="margin: auto; padding: 10px; width: 80%;">
            <div>
                **ลากหมุด (<img src="https://cdn-icons-png.flaticon.com/128/9131/9131546.png" style="width: 10px;">) ไปยังตำแหน่งที่คุณต้องการ
            </div><br>
            <form id="agencyForm" method="post" enctype="multipart/form-data">
            <input type="hidden" name="lat" id="lat">
            <input type="hidden" name="lng" id="lng">
            <div>
                <label>เลือกอาคารที่มีอยู่แล้ว:</label>
                <select id="existing_building" name="existing_building" onchange="toggleBuildingFields()">
                    <option value="">-- ไม่เลือก (กรอกใหม่) --</option>
                    <?php
                        usort($building_options, function($a, $b) {
                            return $a['building_number'] <=> $b['building_number'];
                        });
                        foreach($building_options as $b): 
                            $value = $b['building_number'] . "|" . $b['building'] . "|" . $b['lat'] . "|" . $b['lng'];
                            $label = $b['building_number'] > 500 ? $b['building'] :$label = "อาคาร " . $b['building_number'] . " " . $b['building'];
                    ?>
                        <option value="<?= htmlspecialchars($value) ?>">
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div><br>
                <div>
                    <label>ชื่อหน่วยงาน (ภาษาไทย):</label>
                    <input type="text" id="agencyname_TH" name="agencyname_TH" required>
                    <span class="error-message">**</span>
                </div><br>
                <div>
                    <label>ชื่อหน่วยงาน (ภาษาอังกฤษ):</label>
                    <input type="text" id="agencyname_EN" name="agencyname_EN" required>
                    <span class="error-message">**</span>
                </div><br>
                <div>
                    <label>สังกัด (ถ้ามี):</label>
                    <input type="text" id="subordinate_to" name="subordinate_to">
                </div><br>
                <div id="services_container">
                <div class="service_entry">
                    <label>ข้อมูลการบริการ 1:</label>
                    <input type="text" name="service[]" id="service" data-index="1"><br><br>
                    <label>คำอธิบาย:</label>
                    <input type="text" name="service_description[]" id="service_description" data-index="1"><br><br>
                    <label>คำสำคัญ:</label>
                    <input type="text" name="keyword[]" id="keyword" data-index="1" required>
                    <span class="error-message">**</span><br><br>
                    <label>อยู่ชั้นที่:</label>
                    <input type="number" name="floor[]" id="floor" data-index="1" required>
                    <span class="error-message">**</span><br><br>
                    <label>หมายเลขห้อง:</label>
                    <input type="text" name="room[]" id="room" data-index="1" placeholder='ตัวอย่าง "18-315"'>
                    <span class="error-message">**</span><br><br>
                </div>
                </div>
                <button type="button" onclick="addService()">เพิ่มบริการ</button><br><br>
                <div id="manualBuildingFields">
                <label>
                <input type="checkbox" id="hasBuildingNumber" onchange="toggleBuildingNumber()" checked>
                มีหมายเลขอาคารหรือไม่
                </label><br><br>
                <div id="buildingNumberField">
                <label>หมายเลขอาคาร:</label>
                <input type="number" id="buildingnumber" name="buildingnumber">
                </div>
                <label>ชื่ออาคาร:</label>
                <input type="text" id="building" name="building" required>
                <span class="error-message">**</span>
                </div><br>
                <div>
                    <label>เบอร์โทรศัพท์ (ถ้ามีมากกว่า 1 เบอร์โทรศัพท์ ให้คั่นด้วย คอมม่า(,)):</label>
                    <input type="text" id="phone" name="phone" required>
                </div><br>
                <div>
                    <label>อีเมล:</label>
                    <input type="email" id="email" name="email">
                </div><br>
                <div><label>วันทำการ: </label>
                    <label><input type="radio" name="workday" value="Monday-Friday" checked onclick="toggleTimeFields()"> จันทร์-ศุกร์</label>
                    <label><input type="radio" name="workday" value="Monday-Saturday" onclick="toggleTimeFields()"> จันทร์-เสาร์</label>
                    <label><input type="radio" name="workday" value="Everyday" onclick="toggleTimeFields()"> ทุกวัน</label><br><br>
                    <label>เวลาทำการ: </label>
                    <input type="time" id="timestart" name="timestart" value="08:30">
                    <label>ถึง</label>
                    <input type="time" id="timestop" name="timestop" value="16:30">
                    <div id="extraTimeFields"></div>
                </div><br>
                <div>
                    <label>เว็บไซต์หรือเพจของหน่วยงาน:</label>
                    <input type="url" id="website" name="website">
                </div><br>
                <div><label>รูปภาพสถานที่:</label>
                    <input type="file" id="images" name="images[]" multiple>
                </div><br>
                <div>
                    <label>หมายเหตุ:</label>
                    <textarea id="note" name="note" rows="3" cols="50"></textarea>
                </div>
                <p style="color: red; font-size: 0.9em;">(**) คือช่องที่ต้องกรอกข้อมูล</p> 
                <button id="submit" type="submit">เพิ่มข้อมูล</button>
                <button id="clear">ล้างข้อมูล</button>
            </form>
        </div>
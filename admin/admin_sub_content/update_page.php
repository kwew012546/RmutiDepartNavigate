<div class="sidebar">
    <?php
    include '../admin/admin_controller/show_data.php';
    foreach ($main as $mainName => $subItems): ?>
        <button class="accordion">
            <?= $mainName ?>
            <div><i class="fa fa-chevron-down"></i></div>
        </button>
        <div class="panel">
            <ul style="list-style-type: none;">
                <?php
                $groupHeaders = ['สำนัก','สถาบัน','กอง','ศูนย์','หน่วยงานอื่น ๆ'];

                if (!in_array($mainName, $groupHeaders)) {
                    echo "<li onclick=\"loadData('$mainName')\" style='cursor: pointer; margin-bottom: 20px;'>
                            <i class='fa fa-home' style='margin-right: 10px; margin-left: -20px;'></i>
                            $mainName
                          </li>";
                }
                if (!empty($sub[$mainName])) {
                    foreach ($sub[$mainName] as $subName) {
                        echo "<li onclick=\"loadData('$subName')\" style='cursor: pointer; margin-bottom: 10px;'> 
                                <i class='fa fa-building' style='margin-right: 10px;'></i> 
                                $subName
                              </li>";
                    }
                }
                if (!empty($subItems)) {
                    foreach ($subItems as $subName) {
                        echo "<li onclick=\"loadData('$subName')\" style='cursor: pointer; margin-bottom: 10px;'>
                                <i class='fa fa-building' style='margin-right: 10px;'></i>
                                $subName
                              </li>";
                    }
                }
                ?>
            </ul>
        </div>
    <?php endforeach; ?>
</div>
<div id="content-container" style="padding: 20px; display: none;">
</div>
<div id="updateBuildingForm" style="display: block;">
    <h3 style="text-align: center;">แก้ไขข้อมูลอาคาร</h3>
    <select id="update_building" style="margin-left: 10px; padding: 5px;" name="update_building" onchange="Building()">
        <option value="">-- กรุณาเลือกอาคารที่ต้องการแก้ไข --</option>
        <?php
        usort($building_options, function ($a, $b) {
            return $a['building_number'] <=> $b['building_number'];
        });
        foreach ($building_options as $b):
            $value = $b['building_number'] . "|" . $b['building'] . "|" . $b['lat'] . "|" . $b['lng'];
            $label = $b['building_number'] > 500 ? $b['building'] : "อาคาร {$b['building_number']} {$b['building']}";
            ?>
            <option value="<?= htmlspecialchars($value) ?>">
                <?= htmlspecialchars($label) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <form method="post" id="updateBuilding" style="display: none;">
        <input type="hidden" name="action" value="updatebuilding">
        <div style="display: flex; justify-content: space-between; align-items: end;">
            <div id="buildingFields" style="padding-left: 10px;">
                <input type="hidden" id="old_building_number" name="old_building_number"
                    value="<?= htmlspecialchars($building_data['building_number'] ?? '') ?>">
                <p>เลขอาคาร: <input type="text" id="building_number_input" name="building_number"
                        value="<?= htmlspecialchars($building_data['building_number'] ?? '') ?>"></p>
                <p>ชื่ออาคาร: <input type="text" id="building_name_input" name="building_name"
                        value="<?= htmlspecialchars($building_data['building_name'] ?? '') ?>"></p>
                <div id="update_map" style="width: 100%; height: 300px; margin-bottom: 10px;"></div>
                <input type="hidden" name="update_lat" id="update_lat" value="<?= htmlspecialchars($building_data['lat'] ?? '') ?>">
                <input type="hidden" name="update_lng" id="update_lng" value="<?= htmlspecialchars($building_data['lng'] ?? '') ?>">
            </div>
            <button id="submit" type="submit" style="height: 50px; width: 50px; margin:0px 10px 10px;"><i
                    class="fa fa-check"></i></button>
        </div>
    </form>
</div>
<script>
    function loadData(name) {
        document.getElementById('updateBuildingForm').style.display = 'none';
        const container = document.getElementById('content-container');
        const sidebar = document.querySelector('.sidebar');
        container.style.display = 'block';
        sidebar.style.display = 'none';
        container.innerHTML = "<p>กำลังโหลดข้อมูล...</p>";

        fetch("admin_sub_content/edit_page.php?name=" + encodeURIComponent(name))
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
                const extraDiv = container.querySelector('#extraTimeFieldsUpdate');
                const weekendStart = extraDiv.dataset.weekendStart || '';
                const weekendStop  = extraDiv.dataset.weekendStop || '';

                window.initialWeekendStart = weekendStart;
                window.initialWeekendStop = weekendStop;

                function toggleTimeFieldsUpdate() {
                    const workday = container.querySelector('input[name="updateworkday"]:checked').value;
                    extraDiv.innerHTML = '';

                    if (workday === 'Monday-Saturday') {
                        extraDiv.innerHTML = `
                            <br>
                            <label>เวลาทำการ (ส): </label>
                            <input type="time" name="saturday_start" value="${weekendStart}">
                            <label>ถึง</label>
                            <input type="time" name="saturday_stop" value="${weekendStop}">
                        `;
                    } else if (workday === 'Everyday') {
                        extraDiv.innerHTML = `
                            <br>
                            <label>เวลาทำการ (ส-อา): </label>
                            <input type="time" name="weekend_start" value="${weekendStart}">
                            <label>ถึง</label>
                            <input type="time" name="weekend_stop" value="${weekendStop}">
                        `;
                    }
                };
                container.querySelectorAll('input[name="updateworkday"]').forEach(radio => {
                    radio.onclick = toggleTimeFieldsUpdate;
                });
                toggleTimeFieldsUpdate();
                slideIndex = 1;
                showSlides(slideIndex);
                bindAjaxFormSubmit('#updateForm', name);
                const inputFile = container.querySelector('input[type="file"][name="image_file[]"]');
                if (inputFile) {
                    inputFile.addEventListener('change', function () {
                        const form = container.querySelector('#uploadImage');
                        const formData = new FormData(form);
                        fetch('admin_controller/update_image.php', {
                            method: 'POST',
                            body: formData
                        }).then(response => response.text())
                            .then(result => {
                                alert('อัปโหลดเรียบร้อย');
                                loadData(name);
                            })
                            .catch(error => {
                                alert('เกิดข้อผิดพลาดในการอัปโหลด');
                                console.error(error);
                            });
                    });
                }
                const deleteImage = container.querySelectorAll('.delete-image-form');
                deleteImage.forEach(form => {
                    form.addEventListener('submit', function (e) {
                        e.preventDefault();
                        if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการลบรูปภาพนี้?')) return;

                        const imageId = this.dataset.imageId;
                        const formData = new FormData(this);

                        fetch('admin_controller/update_image.php', {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.text())
                            .then(result => {
                                if (result.trim() === 'success') {
                                    const slide = container.querySelector('#slide_' + imageId);
                                    if (slide) slide.remove();
                                    loadData(name);
                                } else {
                                    alert('ไม่สามารถลบภาพได้: ' + result);
                                }
                            })
                            .catch(error => {
                                alert('เกิดข้อผิดพลาดในการลบภาพ');
                                console.error(error);
                            });
                    });
                });
                document.querySelectorAll('.service-form').forEach(form => {
                    form.querySelector('.updateService').addEventListener('click', function (e) {
                        e.preventDefault();

                        const formData = new FormData(form);
                        formData.append('action', 'update');

                        fetch('admin_controller/update_service.php', {
                            method: 'POST',
                            body: formData
                        })
                            .then(res => res.text())
                            .then(result => {
                                if (result.trim() === 'updated') {
                                    alert('บันทึกข้อมูลเรียบร้อยแล้ว');
                                    loadData(name);
                                } else {
                                    alert('เกิดข้อผิดพลาด: ' + result);
                                }
                            });
                    });
                    form.querySelector('.deleteService').addEventListener('click', function (e) {
                        e.preventDefault();

                        if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการลบบริการนี้?')) return;

                        const formData = new FormData(form);
                        formData.append('action', 'delete');

                        fetch('admin_controller/update_service.php', {
                            method: 'POST',
                            body: formData
                        })
                            .then(res => res.text())
                            .then(result => {
                                if (result.trim() === 'deleted') {
                                    alert('ลบบริการแล้ว');
                                    form.remove();
                                    loadData(name);
                                } else {
                                    alert('ไม่สามารถลบได้: ' + result);
                                }
                            });
                    });
                });
                document.getElementById('newServiceForm').addEventListener('submit', function (e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    formData.append('action', 'insert');

                    fetch('admin_controller/update_service.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(res => res.text())
                        .then(result => {
                            if (result.trim() === 'inserted') {
                                alert('บันทึกข้อมูลบริการเรียบร้อยแล้ว');
                                loadData(name);
                            } else {
                                console.log('เกิดข้อผิดพลาด: ' + result);
                            }
                        });
                });
            })
            .catch(error => {
                container.innerHTML = "<p>เกิดข้อผิดพลาดในการโหลดข้อมูล</p>";
                console.error("Fetch error:", error);
            });
    }

    function goBack() {
        const container = document.getElementById('content-container');
        const sidebar = document.querySelector('.sidebar');
        sidebar.style.display = 'block';
        container.style.display = 'none';
        document.getElementById('updateBuildingForm').style.display = 'block';
    }

    function plusSlides(n) {
        showSlides(slideIndex += n);
    }

    function showSlides(n) {
        let x = document.getElementsByClassName("mySlides");
        if (x.length === 0) {
            console.log("ไม่พบรูปภาพใน .mySlides");
            return;
        }
        if (n > x.length) { slideIndex = 1 }
        if (n < 1) { slideIndex = x.length }
        for (let i = 0; i < x.length; i++) {
            x[i].style.display = "none";
        }
        x[slideIndex - 1].style.display = "block";
    }

    $(document).ready(function () {
        bindAjaxFormSubmit('#updateBuilding');
    });

    function bindAjaxFormSubmit(formSelector, name) {
        if ($(formSelector).length === 0) {
            console.warn(`ไม่พบฟอร์ม ${formSelector}`);
            return;
        }

        $(document).off('submit', formSelector).on('submit', formSelector, function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            $.ajax({
                url: 'admin_controller/update_data.php',
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response.trim() === "success") {
                        alert("บันทึกข้อมูลเรียบร้อยแล้ว");
                        if (formSelector === '#updateForm') {
                            loadData(name);
                        } else {
                            window.location.reload();
                        }
                    } else {
                        console.log("เกิดข้อผิดพลาด:", response);
                    }
                },
                error: function () {
                    alert('เกิดข้อผิดพลาดในการส่งข้อมูล');
                }
            });
        });
    }

    function Building() {
        const select = document.getElementById("update_building");
        document.getElementById("updateBuilding").style.display = "block";
        const selectedValue = select.value;

        if (selectedValue) {
            const [number, name, lat, lng] = selectedValue.split("|");
            document.getElementById("building_number_input").value = number;
            document.getElementById("building_name_input").value = name;
            document.getElementById("old_building_number").value = number;
            document.getElementById("update_lat").value = lat;
            document.getElementById("update_lng").value = lng;

            initUpdate_Map(parseFloat(lat), parseFloat(lng), name);
        } else {
            document.getElementById("updateBuilding").style.display = "none";
        }
    }
    let serviceCount2 = 1;
    function addNewService() {
        document.getElementById('newServiceForm').style.display = 'block';
        serviceCount2++;
        var containerService = document.getElementById('newServiceForm');

        const existingSubmitBtn = document.getElementById('saveServices');
        if (existingSubmitBtn) existingSubmitBtn.remove();

        var newService2 = document.createElement('div');
        newService2.classList.add('service2_entry');
        newService2.setAttribute('data-service2-id', serviceCount2);

        newService2.innerHTML = `
            <label>ชื่อบริการ:</label>
            <input type="text" name="service_name[]"><br><br>

            <label>คำอธิบาย:</label>
            <input type="text" name="description[]" style="width: 80%;"><br><br>

            <label>คีย์เวิร์ด:</label>
            <input type="text" name="keywords[]" style="width: 80%;"><br><br>

            <label>ชั้น:</label>
            <input type="number" name="floor[]" style="text-align: center; width: 20px;">

            <label>ห้อง:</label>
            <input type="text" name="room[]" style="text-align: center; width: 60px;"><br><br>

            <button type="button" onclick="removeNewService(this)" style="margin-bottom: 10px;">ลบข้อมูลบริการ</button>
        `;
        containerService.appendChild(newService2);
        const saveBtn = document.createElement('button');
        saveBtn.type = 'submit';
        saveBtn.id = 'saveServices';
        saveBtn.textContent = 'บันทึกบริการทั้งหมด';
        saveBtn.style = 'margin-top: 10px;';
        containerService.appendChild(saveBtn);
    }
    function removeNewService(button) {
        const serviceDiv = button.closest('.service2_entry');
        const addButton = document.getElementById('saveServices');
        serviceDiv.remove();
        const containerService = document.getElementById('newServiceForm');
        const remainingEntries = containerService.querySelectorAll('.service2_entry');
        if (remainingEntries.length === 0) {
            addButton.remove();
            document.getElementById('newServiceForm').style.display = 'none';
        }
    }
    let marker;

async function initUpdate_Map(lat = 14.98747028934542, lng = 102.11796446410003, name = "ไม่ระบุชื่ออาคาร") {
  const position = { lat, lng };
  var myStyles = [
    {
        featureType: "poi",
        elementType: "labels",
        stylers: [
            { visibility: "off" }
        ]
    }
];

var myOptions = {
    zoom: 17,
    center: position,
    mapTypeId: google.maps.MapTypeId.ROADMAP,
    styles: myStyles,
    clickableIcons: false
};

var map = new google.maps.Map(document.getElementById('update_map'), myOptions);

 marker = new google.maps.Marker({
    map: map,
    position: { lat: 14.98747028934542, lng: 102.11796446410003 },
    animation: google.maps.Animation.DROP,
    icon: {
      url: "../images/rmuti.png",
      scaledSize: new google.maps.Size(20, 40),
      anchor: new google.maps.Point(10, 30),
    },
  });

  buildingMarker = new google.maps.Marker({
    map: map,
    position: position,
    title: name,
    animation: google.maps.Animation.DROP,
    draggable: true,
    icon: {
      url: "https://cdn-icons-png.flaticon.com/128/9131/9131546.png",
      scaledSize: new google.maps.Size(32, 32),
    },
  });

  class CustomLabelOverlay extends google.maps.OverlayView {
    constructor(position, text, map, header) {
      super();
      this.position = position;
      this.text = text;
      this.div = null;
      this.header = header;
      this.setMap(map);
    }
  
    onAdd() {
      this.div = document.createElement("div");
      if (this.header) {
        this.div.style.cssText = `
        position: absolute;
        width: 40%;
        font-size: 16px;
        color: #FF7100;
        margin-left: -60px;
        margin-top: 40px;
        font-weight: bold;
        text-align: center;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        textAlign: center;
      `;
      } else {
        this.div.style.cssText = `
        position: absolute;
        width: 20%;
        margin-left: 65px;
        font-size: 14px;
        color: black;
      `;
      }
      this.div.innerText = this.text;
  
      const panes = this.getPanes();
      panes.overlayLayer.appendChild(this.div);
    }
  
    draw() {
      const projection = this.getProjection();
      const position = projection.fromLatLngToDivPixel(this.position);
      if (this.div) {
        this.div.style.left = `${position.x - 50}px`;
        this.div.style.top = `${position.y - 35}px`;
      }
    }
  
    onRemove() {
      if (this.div) {
        this.div.parentNode.removeChild(this.div);
        this.div = null;
      }
    }
  }

  google.maps.event.addListener(buildingMarker, 'dragend', function (event) {
    document.querySelector('#update_lat').value = event.latLng.lat();
    document.querySelector('#update_lng').value = event.latLng.lng();
  });
  new CustomLabelOverlay({ lat: 14.98747028934542, lng: 102.11796446410003 }, "มหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน (สุรนารายณ์)", map, true);
}
</script>
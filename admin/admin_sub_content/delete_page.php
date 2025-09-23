<?php
include '../admin/admin_controller/show_data.php';

$groupHeaders = ['สำนัก','สถาบัน','กอง','ศูนย์','หน่วยงานอื่น ๆ'];

foreach ($main as $mainName => $subItems): ?>
    <button class="accordion">
        <?= $mainName ?>
        <div><i class="fa fa-chevron-down"></i></div>
    </button>
    <div class="panel">
        <ul style="list-style-type: none;">
            <?php
            if (!in_array($mainName, $groupHeaders)) {
                echo "<li style='margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;'>
                        <span>
                            <i class='fa fa-home' style='margin-right: 10px;'></i>$mainName
                        </span>
                        <span onclick=\"deleteData('$mainName')\" style='cursor: pointer;'>
                            <i class='fa fa-trash' style='color: red;'></i>
                        </span>
                      </li>";
            }

            if (!empty($sub[$mainName])) {
                foreach ($sub[$mainName] as $subName) {
                    echo "<li style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;'>
                            <span>
                                <i class='fa fa-building' style='margin-right: 10px;'></i>$subName
                            </span>
                            <span onclick=\"deleteData('$subName')\" style='cursor: pointer;'>
                                <i class='fa fa-trash' style='color: red;'></i>
                            </span>
                          </li>";
                }
            }

            if (!empty($subItems)) {
                foreach ($subItems as $subName) {
                    echo "<li style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;'>
                            <span>
                                <i class='fa fa-building' style='margin-right: 10px;'></i>$subName
                            </span>
                            <span onclick=\"deleteData('$subName')\" style='cursor: pointer;'>
                                <i class='fa fa-trash' style='color: red;'></i>
                            </span>
                          </li>";
                }
            }
            ?>
        </ul>
    </div>
<?php endforeach; ?>

<div id="confirmDialog" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:9999;">
  <div style="background:white; padding:20px; border-radius:8px; width:320px; text-align:center;">
    <p>คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลของหน่วยงาน: <br><strong id="deptNameText" style="color:B22222;"></strong>
    <br><strong style="color:#8B0000;">หากดำเนินการลบ ข้อมูลทั้งหมดของหน่วยงานนี้ รวมถึงรายละเอียด บริการ การค้นหา และรูปภาพ จะถูกลบอย่างถาวร และไม่สามารถกู้คืนได้</strong></p>
    <button id="confirmYes" style="margin-right: 10px; background-color: red; color: white; padding: 5px 15px; cursor: pointer;">ลบ</button>
    <button id="confirmNo" style="padding: 5px 15px; cursor: pointer;">ยกเลิก</button>
  </div>
</div>
<script>
function deleteData(name) {
    const dialog = document.getElementById('confirmDialog');
    document.getElementById('deptNameText').textContent = name+' ?';
    dialog.style.display = 'flex';

    document.getElementById('confirmYes').onclick = function () {
        dialog.style.display = 'none';

        fetch('admin_controller/delete_data.php', {
            method: 'POST',
            headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'name=' + encodeURIComponent(name)
        }) .then(response => response.text())
        .then(result => {
            console.log(result);
            location.reload();
        })
        .catch(error => {
            alert("เกิดข้อผิดพลาด: " + error);
            console.error(error);
        });
    };

    document.getElementById('confirmNo').onclick = function () {
        dialog.style.display = 'none';
    };
}
</script>

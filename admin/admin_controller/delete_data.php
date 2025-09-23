<?php
    include '../../connect.php';

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['name'])) {
        $name = $_POST['name'];

        $stmt = $conn->prepare("SELECT department_id FROM departments WHERE name_th = ? OR name_en = ?");
        $stmt->bind_param("ss", $name, $name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $dept_id = $row['department_id'];

            $conn->begin_transaction();
            try {
                $services = [];
                $stmt_services = $conn->prepare("SELECT service_id FROM services WHERE departments_id = ?");
                $stmt_services->bind_param("i", $dept_id);
                $stmt_services->execute();
                $res_services = $stmt_services->get_result();
    
                while ($s = $res_services->fetch_assoc()) {
                    $services[] = $s['service_id'];
                }
    
                if (!empty($services)) {
                    $placeholders = implode(',', array_fill(0, count($services), '?'));
                    $types = str_repeat('i', count($services)) . 'i';
                    
                    $sql_service = "
                        DELETE FROM search_logs 
                        WHERE suggested_service_id IN ($placeholders)
                           OR suggested_department_id = ?
                    ";
                    $stmt_del_logs = $conn->prepare($sql_service);
                    $params = array_merge($services, [$dept_id]);
                    $stmt_del_logs->bind_param($types, ...$params);
                    $stmt_del_logs->execute();
                } else {
                    $stmt_del_logs = $conn->prepare("DELETE FROM search_logs WHERE suggested_department_id = ?");
                    $stmt_del_logs->bind_param("i", $dept_id);
                    $stmt_del_logs->execute();
                }
                
                $stmt_get_images = $conn->prepare("SELECT image_name FROM department_images WHERE departments_id = ?");
                $stmt_get_images->bind_param("i", $dept_id);
                $stmt_get_images->execute();
                $res_images = $stmt_get_images->get_result();
    
                $image_names = [];
                while ($img = $res_images->fetch_assoc()) {
                    $image_names[] = $img['image_name'];
                }

                $stmt_get_building = $conn->prepare("SELECT building FROM departments WHERE department_id = ?");
                $stmt_get_building->bind_param("i", $dept_id);
                $stmt_get_building->execute();
                $res_building = $stmt_get_building->get_result();
                $building_number = null;
                if ($row_building = $res_building->fetch_assoc()) {
                    $building_number = $row_building['building'];
                }

                $stmt_del_services = $conn->prepare("DELETE FROM services WHERE departments_id = ?");
                $stmt_del_services->bind_param("i", $dept_id);
                $stmt_del_services->execute();

                $stmt_del_images = $conn->prepare("DELETE FROM department_images WHERE departments_id = ?");
                $stmt_del_images->bind_param("i", $dept_id);
                $stmt_del_images->execute();
    
                $stmt_del_dept = $conn->prepare("DELETE FROM departments WHERE department_id = ?");
                $stmt_del_dept->bind_param("i", $dept_id);
                $stmt_del_dept->execute();

                if ($building_number !== null) {
                    $stmt_check_building = $conn->prepare("SELECT COUNT(*) as count FROM departments WHERE building = ?");
                    $stmt_check_building->bind_param("i", $building_number);
                    $stmt_check_building->execute();
                    $res_check_building = $stmt_check_building->get_result();
                    $count_building = $res_check_building->fetch_assoc()['count'];

                    if ($count_building == 0) {
                        $stmt_del_building = $conn->prepare("DELETE FROM building WHERE building_number = ?");
                        $stmt_del_building->bind_param("i", $building_number);
                        $stmt_del_building->execute();
                    }
                }

                $conn->commit();

                foreach ($image_names as $filename) {
                    $stmt_check = $conn->prepare("SELECT COUNT(*) as count FROM department_images WHERE image_name = ?");
                    $stmt_check->bind_param("s", $filename);
                    $stmt_check->execute();
                    $res_check = $stmt_check->get_result();
                    $count = $res_check->fetch_assoc()['count'];
    
                    if ($count == 0) {
                        $file_path = "uploads/$filename";

                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }
                }
                echo "ลบข้อมูลทั้งหมดของหน่วยงานเรียบร้อยแล้ว";
            } catch (Exception $e) {
                $conn->rollback();
                echo "เกิดข้อผิดพลาด: " . $e->getMessage();
            }
        } else {
            echo "ไม่พบหน่วยงานที่ต้องการลบ";
        }
    } else {
        echo "คำขอลบไม่ถูกต้อง";
    }
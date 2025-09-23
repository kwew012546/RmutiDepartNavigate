<?php
    include '../connect.php';

    $sql = "SELECT * FROM departments";
    $result = $conn->query($sql);

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $main = [];
    $sub = [];

    foreach ($data as $item) {
        if (strpos($item['name_th'], 'สำนัก') === 0) { 
            $main['สำนัก'][] = $item['name_th'];
        } elseif (strpos($item['name_th'], 'สถาบัน') === 0) {
            $main['สถาบัน'][] = $item['name_th']; 
        } elseif (strpos($item['name_th'], 'กอง') === 0) {
            $main['กอง'][] = $item['name_th']; 
        } elseif (strpos($item['name_th'], 'ศูนย์') === 0) {
            $main['ศูนย์'][] = $item['name_th']; 
        } elseif (strpos($item['name_th'], 'หน่วยงานอื่น ๆ') === 0) {
            $main['หน่วยงานอื่น ๆ'][] = $item['name_th']; 
        } elseif (empty($item['subordinate_to'])) {
            $main[$item['name_th']] = [];
        } else {
            $sub[$item['subordinate_to']][] = $item['name_th'];
        }
    }
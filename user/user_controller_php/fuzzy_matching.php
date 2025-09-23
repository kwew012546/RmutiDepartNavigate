<?php
header('Content-Type: application/json; charset=utf-8');
include '../../connect.php';

$search = $_GET['q'] ?? '';
$threshold = 12;

$buildings = [];
$sql_b = "SELECT building_number, building_name FROM building";
$result_b = $conn->query($sql_b);
while ($row = $result_b->fetch_assoc()) {
    $buildings[$row['building_number']] = $row['building_name'];
}

$departments = [];
$stmt_dep = $conn->prepare("SELECT department_id AS id, name_th, building FROM departments");
$stmt_dep->execute();
$result_dep = $stmt_dep->get_result();
while ($row = $result_dep->fetch_assoc()) {
    $departments[$row['name_th']] = $row;
}

$selected_logs = [
    'service_ids' => [],
    'department_ids' => []
];
$stmt_log = $conn->prepare("SELECT suggested_service_id, suggested_department_id FROM search_logs WHERE user_input = ? AND user_selected = 1");
$stmt_log->bind_param("s", $search);
$stmt_log->execute();
$result_log = $stmt_log->get_result();
while ($row = $result_log->fetch_assoc()) {
    if ($row['suggested_service_id']) {
        $selected_logs['service_ids'][] = $row['suggested_service_id'];
    }
    if ($row['suggested_department_id']) {
        $selected_logs['department_ids'][] = $row['suggested_department_id'];
    }
}

$matches = [];
$stmt_srv = $conn->prepare("SELECT service_id AS id, service_name, keywords, departments_id FROM services");
$stmt_srv->execute();
$result_srv = $stmt_srv->get_result();

while ($row = $result_srv->fetch_assoc()) {
    if (isset($departments[$row['service_name']]) || isset($departments[$row['keywords']])) {
        continue;
    }

    $input = mb_strtolower(trim($search));
    $keywords = mb_strtolower(trim($row['keywords']));
    similar_text($input, $keywords, $percent1);
    $levDistance = levenshtein($input, $keywords);
    $maxLen = max(mb_strlen($input, 'UTF-8'), mb_strlen($keywords, 'UTF-8'));
    $percent2 = ($maxLen > 0) ? (1 - $levDistance / $maxLen) * 100 : 0;
    $finalPercent = max($percent1, $percent2);

    if ($finalPercent >= $threshold) {
        $buildingName = '';
        $departmentName = '';
        foreach ($departments as $dep) {
            if ($dep['id'] == $row['departments_id']) {
                $departmentName = $dep['name_th'];
                $buildingNumber = $dep['building'];
                $buildingName = $buildings[$buildingNumber] ?? '';
                break;
            }
        }
        $isSelected = in_array($row['id'], $selected_logs['service_ids']);
        $matches[] = [
            'type' => 'service',
            'id' => $row['id'],
            'name' => $row['service_name'],
            'keywords' => $row['keywords'],
            'department_name' => $departmentName,
            'similarity' => round($finalPercent, 2),
            'building_name' => $buildingName,
            'selected' => $isSelected
        ];
    }
}

foreach ($departments as $dep) {
    $similarities = [];
    $input2 = mb_strtolower(trim($search));
    $department_name = mb_strtolower(trim($dep['name_th']));
    similar_text($input2, $department_name, $percent_th);
    $similarities[] = $percent_th;
    $levDistance2 = levenshtein($input2, $department_name);
    $maxLen2 = max(mb_strlen($input2, 'UTF-8'), mb_strlen($department_name, 'UTF-8'));
    $percent_lev = ($maxLen2 > 0) ? (1 - $levDistance2 / $maxLen2) * 100 : 0;
    $similarities[] = $percent_lev;
    $max_percent = max($similarities);

    if ($max_percent >= $threshold) {
        $buildingName = $buildings[$dep['building']] ?? '';
        $isSelected = in_array($dep['id'], $selected_logs['department_ids']);
        $matches[] = [
            'type' => 'department',
            'id' => $dep['departments_id'] ?? $dep['id'],
            'name' => $dep['name_th'],
            'similarity' => round($max_percent, 2),
            'building_name' => $buildingName,
            'selected' => $isSelected
        ];
    }
}

usort($matches, function($a, $b) {
    if (($a['selected'] ?? false) && !($b['selected'] ?? false)) return -1;
    if (!($a['selected'] ?? false) && ($b['selected'] ?? false)) return 1;
    return floatval($b['similarity']) <=> floatval($a['similarity']);
});

echo json_encode($matches, JSON_UNESCAPED_UNICODE);
exit;

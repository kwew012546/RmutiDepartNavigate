<?php
header('Content-Type: application/json; charset=utf-8');
include '../../connect.php';

$search = trim($_GET['q'] ?? '');
if ($search === '') {
    echo json_encode([]);
    exit;
}

$threshold = 25; // Adjusted threshold for multi-byte similarity scoring

/**
 * Multi-byte Levenshtein Distance for UTF-8 (Thai / English)
 */
function mb_levenshtein_distance($str1, $str2) {
    $chars1 = preg_split('//u', $str1, -1, PREG_SPLIT_NO_EMPTY);
    $chars2 = preg_split('//u', $str2, -1, PREG_SPLIT_NO_EMPTY);
    $len1 = count($chars1);
    $len2 = count($chars2);

    if ($len1 === 0) return $len2;
    if ($len2 === 0) return $len1;

    $v0 = range(0, $len2);
    $v1 = array_fill(0, $len2 + 1, 0);

    for ($i = 0; $i < $len1; $i++) {
        $v1[0] = $i + 1;
        for ($j = 0; $j < $len2; $j++) {
            $cost = ($chars1[$i] === $chars2[$j]) ? 0 : 1;
            $v1[$j + 1] = min(
                $v1[$j] + 1,
                $v0[$j + 1] + 1,
                $v0[$j] + $cost
            );
        }
        $v0 = $v1;
    }

    return $v0[$len2];
}

/**
 * Calculates similarity percentage (0-100) between query and target text.
 * Gives top scores to exact and substring matches, followed by typo/Levenshtein similarity.
 */
function calculate_thai_similarity($query, $target) {
    $q = mb_strtolower(trim($query), 'UTF-8');
    $t = mb_strtolower(trim($target), 'UTF-8');

    if ($q === '' || $t === '') {
        return 0.0;
    }

    // 1. Exact match
    if ($q === $t) {
        return 100.0;
    }

    // 2. Starts with query (prefix match)
    if (mb_strpos($t, $q, 0, 'UTF-8') === 0) {
        $coverage = mb_strlen($q, 'UTF-8') / mb_strlen($t, 'UTF-8');
        return round(85.0 + ($coverage * 15.0), 2);
    }

    // 3. Substring match (contains query)
    if (mb_strpos($t, $q, 0, 'UTF-8') !== false) {
        $coverage = mb_strlen($q, 'UTF-8') / mb_strlen($t, 'UTF-8');
        return round(70.0 + ($coverage * 25.0), 2);
    }

    // 4. Target is substring of query
    if (mb_strpos($q, $t, 0, 'UTF-8') !== false) {
        $coverage = mb_strlen($t, 'UTF-8') / mb_strlen($q, 'UTF-8');
        return round(60.0 + ($coverage * 20.0), 2);
    }

    // 5. Multi-byte Levenshtein calculation
    $lenQ = mb_strlen($q, 'UTF-8');
    $lenT = mb_strlen($t, 'UTF-8');

    // Cap string length for distance computation if very long
    if ($lenQ > 60) $q = mb_substr($q, 0, 60, 'UTF-8');
    if ($lenT > 60) $t = mb_substr($t, 0, 60, 'UTF-8');
    $lenQ = mb_strlen($q, 'UTF-8');
    $lenT = mb_strlen($t, 'UTF-8');

    $maxLen = max($lenQ, $lenT);
    if ($maxLen === 0) return 0.0;

    $dist = mb_levenshtein_distance($q, $t);
    $levPercent = max(0.0, (1.0 - ($dist / $maxLen)) * 100.0);

    // 6. Character overlap ratio
    $qChars = preg_split('//u', $q, -1, PREG_SPLIT_NO_EMPTY);
    $tChars = preg_split('//u', $t, -1, PREG_SPLIT_NO_EMPTY);
    $common = count(array_intersect($qChars, $tChars));
    $overlapPercent = ($common / $maxLen) * 100.0;

    return round(max($levPercent, $overlapPercent * 0.7), 2);
}

// 1. Fetch Buildings
$buildings = [];
$sql_b = "SELECT building_number, building_name FROM building";
$result_b = $conn->query($sql_b);
if ($result_b) {
    while ($row = $result_b->fetch_assoc()) {
        $buildings[$row['building_number']] = $row['building_name'];
    }
}

// 2. Fetch Departments (both Thai and English names)
$departments = [];
$stmt_dep = $conn->prepare("SELECT department_id AS id, name_th, name_en, building FROM departments");
$stmt_dep->execute();
$result_dep = $stmt_dep->get_result();
while ($row = $result_dep->fetch_assoc()) {
    $departments[$row['name_th']] = $row;
}
$stmt_dep->close();

// 3. Fetch Selected Logs
$selected_logs = [
    'service_ids' => [],
    'department_ids' => []
];
$stmt_log = $conn->prepare("SELECT suggested_service_id, suggested_department_id FROM search_logs WHERE user_input = ? AND user_selected = 1");
$stmt_log->bind_param("s", $search);
$stmt_log->execute();
$result_log = $stmt_log->get_result();
while ($row = $result_log->fetch_assoc()) {
    if (!empty($row['suggested_service_id'])) {
        $selected_logs['service_ids'][] = (int)$row['suggested_service_id'];
    }
    if (!empty($row['suggested_department_id'])) {
        $selected_logs['department_ids'][] = (int)$row['suggested_department_id'];
    }
}
$stmt_log->close();

$matches = [];

// 4. Match Services
$stmt_srv = $conn->prepare("SELECT service_id AS id, service_name, keywords, departments_id FROM services");
$stmt_srv->execute();
$result_srv = $stmt_srv->get_result();

while ($row = $result_srv->fetch_assoc()) {
    if (isset($departments[$row['service_name']]) || isset($departments[$row['keywords']])) {
        continue;
    }

    $serviceSim = calculate_thai_similarity($search, $row['service_name']);

    // Check individual keywords if keywords contain delimiters
    $bestKwSim = 0.0;
    if (!empty($row['keywords'])) {
        $kwList = preg_split('/[,;\s]+/u', $row['keywords'], -1, PREG_SPLIT_NO_EMPTY);
        foreach ($kwList as $kw) {
            $kwSim = calculate_thai_similarity($search, $kw);
            if ($kwSim > $bestKwSim) {
                $bestKwSim = $kwSim;
            }
        }
        // Also check keywords as a whole
        $fullKwSim = calculate_thai_similarity($search, $row['keywords']);
        if ($fullKwSim > $bestKwSim) {
            $bestKwSim = $fullKwSim;
        }
    }

    $finalPercent = max($serviceSim, $bestKwSim);

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
        $isSelected = in_array((int)$row['id'], $selected_logs['service_ids'], true);
        $matches[] = [
            'type' => 'service',
            'id' => (int)$row['id'],
            'name' => $row['service_name'],
            'keywords' => $row['keywords'],
            'department_name' => $departmentName,
            'similarity' => round($finalPercent, 2),
            'building_name' => $buildingName,
            'selected' => $isSelected
        ];
    }
}
$stmt_srv->close();

// 5. Match Departments (Thai and English)
foreach ($departments as $dep) {
    $simTh = calculate_thai_similarity($search, $dep['name_th']);
    $simEn = !empty($dep['name_en']) ? calculate_thai_similarity($search, $dep['name_en']) : 0.0;
    $max_percent = max($simTh, $simEn);

    if ($max_percent >= $threshold) {
        $buildingName = $buildings[$dep['building']] ?? '';
        $isSelected = in_array((int)$dep['id'], $selected_logs['department_ids'], true);
        $matches[] = [
            'type' => 'department',
            'id' => (int)$dep['id'],
            'name' => $dep['name_th'],
            'similarity' => round($max_percent, 2),
            'building_name' => $buildingName,
            'selected' => $isSelected
        ];
    }
}

// 6. Sort results: user selected first, then highest similarity
usort($matches, function($a, $b) {
    if (($a['selected'] ?? false) && !($b['selected'] ?? false)) return -1;
    if (!($a['selected'] ?? false) && ($b['selected'] ?? false)) return 1;
    return floatval($b['similarity']) <=> floatval($a['similarity']);
});

// Limit output to top 25 most relevant matches to keep payload small and fast
$topMatches = array_slice($matches, 0, 25);
echo json_encode($topMatches, JSON_UNESCAPED_UNICODE);
exit;

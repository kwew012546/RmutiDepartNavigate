<?php
header('Content-Type: application/json');
include '../../connect.php';
mysqli_set_charset($conn, "utf8");

$response = [];

$sql = "SELECT building_number, building_name, lat, lng FROM building";
$result = $conn->query($sql);
$locations = [];
while ($row = $result->fetch_assoc()) {
    $locations[] = $row;
}
$response['locations'] = $locations;

$sql2 = "
SELECT d.*, b.building_name
FROM departments d
LEFT JOIN building b ON d.building = b.building_number
";
$result2 = $conn->query($sql2);
$departments = [];
while ($row = $result2->fetch_assoc()) {
    $departments[] = $row;
}
$response['departments'] = $departments;

$conn->close();

echo json_encode($response);
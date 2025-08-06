<?php
include("con_db.php");


$sql4 = "SELECT DISTINCT LOG_IDUSER FROM LOG_USERS
-- AND LOG_FECHORA >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
$result4 = $conn->query($sql4);

$data = array();
if ($result4->num_rows > 0) {
    while ($row = $result4->fetch_assoc()) {
        $data[] = $row["LOG_IDUSER"];
    }
}

// Devolver los resultados como una lista en formato JSON
echo json_encode($data);
?>
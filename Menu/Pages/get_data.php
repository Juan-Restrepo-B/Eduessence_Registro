<?php
include("con_db.php");

$sql4 = "SELECT * FROM PAISES";
$result4 = $conn->query($sql4);

$data = array();
if ($result4->num_rows > 0) {
    while ($row = $result4->fetch_assoc()) {
        $data[] = $row["PAISNAME"];
    }
}

// Devolver los resultados como una lista en formato JSON
echo json_encode($data);
?>
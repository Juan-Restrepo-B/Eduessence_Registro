<?php
include("con_db.php");

if (isset($_GET['id'])) {
    $idPersona = $_GET['id'];

    $sql1 = "SELECT * FROM REGISTRO_PERSONA INNER JOIN PERSONAL ON REG_CORREO = PER_CORREO WHERE IDPERSONA = '$idPersona'";
    $result1 = mysqli_query($conn, $sql1);

    if ($result1) {
        $row = mysqli_fetch_assoc($result1);
        echo json_encode($row);
    }
}
?>

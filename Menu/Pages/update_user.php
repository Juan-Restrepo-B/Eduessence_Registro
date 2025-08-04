<?php
// Recibir los datos de la solicitud AJAX
$id = $_POST['id'];
$newUser = $_POST['user'];
$newPass = $_POST['pass'];
$newPermiso = $_POST['permiso'];

include("con_db.php");

$sql = "UPDATE USERS
SET `USER`='$newUser', PASS='$newPass', PERMISOS='$newPermiso'
WHERE IDUSER=$id";

if (mysqli_query($conn, $sql)) {
    header("Location: ./users.php");
}

http_response_code(200);
echo json_encode(array("message" => "Datos actualizados correctamente."));
?>
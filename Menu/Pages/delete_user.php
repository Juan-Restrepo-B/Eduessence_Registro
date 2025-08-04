<?php
// Recibir los datos de la solicitud AJAX
$id = $_POST['id'];

include("con_db.php");

$sql = "DELETE FROM registro.USERS
        WHERE IDUSER=$id";

if (mysqli_query($conn, $sql)) {
    header("Location: ./Users.php");
}

http_response_code(200);
echo json_encode(array("message" => "Datos actualizados correctamente."));
?>
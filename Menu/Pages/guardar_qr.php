<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userid = $_POST['userid'];

    $sponser = $_SESSION['idsponser'];
    
    date_default_timezone_set('America/Bogota');
    $fechareg = date('Y-m-d H:i:s');

    include("con_db.php");

    $sql_verificar = "SELECT * FROM LOGS_VISITAS WHERE LOGV_IDVISITANTE = '$userid'";
    $result_verificar = $conn->query($sql_verificar);
    if ($result_verificar->num_rows > 0) {
        $response = array('message' => 'La persona ya esta registrada.');
    } else {
        // Insertar el nuevo registro si el usuario no está registrado
        $sql_insertar = "INSERT INTO LOGS_VISITAS (LOGV_SPONNER, LOGV_IDVISITANTE, LOGV_FECHORA)
        VALUES ('$sponser', '$userid', '$fechareg')";
        if ($conn->query($sql_insertar) === TRUE) {
            $response = array('message' => 'Registro exitoso.');
        } else {
            $response = array('message' => 'Error al registrar.');
        }
    }
    echo json_encode($response);

} else {
    echo json_encode(array('message' => 'Acceso no válido.'));
}
?>
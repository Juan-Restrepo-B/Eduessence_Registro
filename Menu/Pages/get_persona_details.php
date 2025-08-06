<?php
include("con_db.php");

if (isset($_GET['id'])) {
    $idPersona = $_GET['id'];

    $sql1 = "SELECT rg.REG_QR, p.PER_NOMBRES, p.PER_APELLIDOS FROM REGISTRO_PERSONA rg
             INNER JOIN PERSONAL p ON rg.REG_CORREO = p.PER_CORREO
             WHERE p.IDPERSONA = '$idPersona'";

    $result1 = mysqli_query($conn, $sql1);

    if ($result1 && $row = mysqli_fetch_assoc($result1)) {
        // Codificar el campo REG_QR como base64 si no está vacío
        if (!empty($row['REG_QR'])) {
            $row['REG_QR'] = base64_encode($row['REG_QR']);
        } else {
            $row['REG_QR'] = null;
        }

        header('Content-Type: application/json');
        echo json_encode($row);
        exit;
    }
}

// Si hay un fallo, responde con error JSON
header('Content-Type: application/json');
echo json_encode(['error' => 'No se encontró el usuario']);
?>

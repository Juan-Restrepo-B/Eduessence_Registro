<?php
session_start();
function showButtons($buttonNumber)
{

    $user_permission = $_SESSION['user_permission'];

    if ($user_permission == 1) { // Administrador
        return true;
    } elseif ($user_permission == 2) { // Asistente
        return in_array($buttonNumber, [3, 4]);
    } elseif ($user_permission == 3) { // Organizador
        return in_array($buttonNumber, [1, 2]);
    } elseif ($user_permission == 4) { // Terceros
        return in_array($buttonNumber, [7, 8]);
    } elseif ($user_permission == 5) { // Apoyo
        return in_array($buttonNumber, [1]);
    }

    return false;
}

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../../index.php");
    exit();
}

// Verificar el tiempo de inactividad (10 minutos en este ejemplo)
$inactive_timeout = 10 * 60; // 10 minutes
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $inactive_timeout) {
    // Si ha pasado más de 10 minutos, destruir la sesión y redirigir al inicio de sesión
    session_unset();
    session_destroy();
    header("Location: ../../index.php");
    exit();
}

// Verificar si el usuario ya tiene una sesión activa en otro navegador
if (isset($_SESSION['single_session_id']) && $_SESSION['single_session_id'] !== session_id()) {
    header("Location: ../../Validaciones/usersin.php");
    exit();
}

// Si no hay single_session_id en la sesión, asignarlo
if (!isset($_SESSION['single_session_id'])) {
    $_SESSION['single_session_id'] = session_id();
}

// Actualizar el tiempo de la última actividad en cada carga de página
$_SESSION['last_activity'] = time();

// Resto del código HTML de la página
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="48x48" href="../Img/Logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pruebas Registro</title>
    <!-- Estilos CSS -->
    <link rel="stylesheet" type="text/css" href="../Css/Styles_From.css">
    <link rel="stylesheet" type="text/css" href="../Css/Styles_Parking.css">
</head>
<?php
include("con_db.php");

require('phpqrcode/qrlib.php');
?>

<body>
    <h1>Registro de Parqueadero</h1>

    <div class="from">

        <form action="fpdf/parking.php" method="post">
            <label for="placa">Placa del vehículo:</label>
            <input type="text" id="placa" name="placa" required>

            <label for="telefono">Teléfono de contacto:</label>
            <input type="tel" id="telefono" name="telefono" required>

            <label for="fecha_ingreso">Fecha y hora de ingreso:</label>
            <input type="datetime-local" id="fecha_ingreso" name="fecha_ingreso" required>

            <label for="fecha_ingreso">Fecha y hora de salida:</label>
            <input type="datetime-local" id="fecha_salida" name="fecha_salida" required>

            <label for="tipo_vehiculo">Tipo de vehículo:</label>
            <select id="tipo_vehiculo" name="tipo_vehiculo" required>
                <option value="automovil">Automóvil</option>
                <option value="moto">Moto</option>
                <option value="camioneta">Camioneta</option>
            </select>

            <label for="observaciones">Observaciones:</label>
            <textarea id="observaciones" name="observaciones" rows="4"></textarea>

            <button type="submit" id="register">Registrar</button>
        </form>
    </div>
</body>

</html>
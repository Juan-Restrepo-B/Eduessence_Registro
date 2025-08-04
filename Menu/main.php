<?php
session_start();
function showButtons($buttonNumber) {

    $user_permission = $_SESSION['user_permission'];

    if ($user_permission == 1) { // Administrador
        return true;
    } elseif ($user_permission == 2) { // Asistente
        return in_array($buttonNumber, [3, 4]);
    } elseif ($user_permission == 5) { // Apoyo
        return in_array($buttonNumber, [3, 4]);
    } elseif ($user_permission == 3) { // Organizador
        return in_array($buttonNumber, [1, 2]);
    } elseif ($user_permission == 4) { // Terceros
        return in_array($buttonNumber, [7, 8]);
    }

    return false;
}

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../index.php");
    exit();
}

// Verificar el tiempo de inactividad (10 minutos en este ejemplo)
$inactive_timeout = 10 * 60; // 10 minutes
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $inactive_timeout) {
    // Si ha pasado más de 10 minutos, destruir la sesión y redirigir al inicio de sesión
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
}

// Verificar si el usuario ya tiene una sesión activa en otro navegador
if (isset($_SESSION['single_session_id']) && $_SESSION['single_session_id'] !== session_id()) {
    header("Location: ../Validaciones/usersin.php");
    exit();
}

// Si no hay single_session_id en la sesión, asignarlo
if (!isset($_SESSION['single_session_id'])) {
    $_SESSION['single_session_id'] = session_id();
}

// Actualizar el tiempo de la última actividad en cada carga de página
$_SESSION['last_activity'] = time();
$sponser = $_SESSION['idsponser'];

// Resto del código HTML de la página
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="48x48" href="Img/Logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="Css/Styles_Index.css">
    <title>MENU REGISTRO</title>
</head>

<body>
    <div class="logs">
        <div class="logs-img"><img src="LogoS.png" alt=""></div>
        <div class="logs-imgs"><img src="LogoD.png" alt=""></div>
        <button class="btn btn-secundary btn-block" type="submit" name="logout" onclick="cerrarSesion()">CERRAR SESION</button>
    </div>
    <Section class="options">
        <div class="positioning">
            <?php if (showButtons(4)): ?>
                <div class="button4 button_wrapper">
                    <a href="Pages/personal.php" class="button">
                        ESCARAPELA PERSONAS
                    </a>
                </div>
            <?php endif; ?>
            
            <?php if (showButtons(3)): ?>
                <div class="button3 button_wrapper">
                    <a href="Pages/personnel_registration.php" class="button">
                        REGISTRO
                    </a>
                </div>
            <?php endif; ?>

            <?php if (showButtons(1)): ?>
                <div class="button1 button_wrapper">
                    <a href="Pages/qr_input_output.php" class="button">
                        ENTRADA - SALIDA
                    </a>
                </div>
            <?php endif; ?>

            <?php if (showButtons(2)): ?>
                <div class="button2 button_wrapper">
                    <a href="Pages/follow-up_rooms.php" class="button">
                        SEGUIMIENTO - CHARLAS
                    </a>
                </div>
            <?php endif; ?>

            <?php if (showButtons(7)): ?>
                <div class="button7 button_wrapper">
                    <a href="https://registro.juanprestrepob.com/index.php" class="button">
                        REGISTRO VISITA
                    </a>
                </div>
            <?php endif; ?>

            <?php if (showButtons(8)): ?>
                <div class="button8 button_wrapper">
                    <a href="Pages/reports_vista.php" class="button">
                        INFORMES VISITAS
                    </a>
                </div>
            <?php endif; ?>
            
            <?php if (showButtons(6)): ?>
                <div class="button6 button_wrapper">
                    <a href="Pages/reports.php" class="button">
                        INFORMES ASISTENCIA
                    </a>
                </div>
            <?php endif; ?>

            <?php if (showButtons(5)): ?>
                <div class="button5 button_wrapper">
                    <a href="Pages/parking.php" class="button">
                        PARQUEADERO
                    </a>
                </div>
            <?php endif; ?>

            <?php if (showButtons(9)): ?>
                <div class="button9 button_wrapper">
                    <a href="Pages/users.php" class="button">
                        USUARIOS
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </Section>
<script>
    function cerrarSesion() {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "main.php", true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Logout was successful, perform any additional actions here
                    console.log("Sesión cerrada correctamente.");
                    // For example, you can redirect the user to the login page after successful logout:
                    window.location.href = "../index.php";
                } else {
                    // Something went wrong with the logout process
                    console.error("Error al cerrar sesión.");
                }
            }
        };
        xhr.send();
    }
</script>
    <footer class="footer">
        <p>&copy;Todos los derechos reservados por &nbsp;
        <div class="class"> Juan Restrepo</div>
        </p>
    </footer>
</body>
</html>
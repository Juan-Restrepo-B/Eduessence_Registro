<?php
session_start();

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
include("con_db.php");

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Consulta para seleccionar registros que coincidan con el término de búsqueda
$sql = "SELECT * FROM LOGS_VISITAS lv 
        INNER JOIN PERSONAL p 
        ON lv.LOGV_IDVISITANTE = p.PER_DOCUMENTO
        INNER JOIN SPONSER s
        ON lv.LOGV_SPONNER = s.IDSPONSOR 
        WHERE PER_ESTADO NOT IN ('INACTIVO')
        AND LOGV_SPONNER = $sponser";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="48x48" href="../Img/Logo.png">
    <title>REPORTE VISITA</title>
    <!-- Estilos CSS -->
    <link rel="stylesheet" type="text/css" href="../Css/Styles_From.css">
    <link rel="stylesheet" type="text/css" href="../Css/Styles_Reports.css">
</head>

<body>
    <header>
        <div class="contenedor">
            <div class="add">
                <a href="fpdf/ReportVisita.php?LOGV_SPONNER=<?php echo $sponser; ?>" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" margin-left="0" width="24" height="24" viewBox="0 0 24 24"
                        style="fill: rgba(255, 255, 255, 1);" name="Imprimir">
                        <path
                            d="M19 7h-1V2H6v5H5c-1.654 0-3 1.346-3 3v7c0 1.103.897 2 2 2h2v3h12v-3h2c1.103 0 2-.897 2-2v-7c0-1.654-1.346-3-3-3zM8 4h8v3H8V4zm8 16H8v-4h8v4zm4-3h-2v-3H6v3H4v-7c0-.551.449-1 1-1h14c.552 0 1 .449 1 1v7z">
                        </path>
                        <path d="M14 10h4v2h-4z"></path>
                    </svg>
                </a>
            </div>
            <div class="search-bar">
                <form action="" method="get">
                    <input type="text" name="search" class="search" placeholder="Buscar por Usuario">
                    <button type="submit" class="btnsearch">Buscar</button>
                </form>
            </div>
            <nav>
                <ul>
                    <div class="content_menu">
                        <div class="list">
                            <!-- Enlace al índice (Home) -->
                            <li><a class='active' href="../main.php">SALIR</a></li>
                        </div>
                    </div>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <table>
            <tr>
                <td>#</td>
                <td>NOMBRE Y APELLIDOS</td>
                <td>CORREO</td>
                <td>PAIS</td>
                <td>FECHA Y HORA</td>
            </tr>
            <?php

            // Mostrar los registros de PERSONAL en la tabla
            while ($mostrar = mysqli_fetch_array($result)) {
                ?>
                <tr>
                    <td class="NroId">
                        <?php echo $mostrar['IDLOGVISITA'] ?>
                    </td>
                    <td>
                        <?php echo $mostrar['PER_NOMBRES'] ?>
                        <?php echo $mostrar['PER_APELLIDOS'] ?>
                    </td>
                    <td>
                        <?php echo $mostrar['PER_CORREO'] ?>
                    </td>
                    <td>
                        <?php echo $mostrar['PER_PAIS'] ?>
                    </td>
                    <td class='fechav'>
                        <?php echo $mostrar['LOGV_FECHORA'] ?>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>
    </main>
    <footer class="footer">
        <p>&copy;Todos los derechos reservados por &nbsp;
        <div class="class"> Juan Restrepo</div>
        </p>
    </footer>
</body>

</html>
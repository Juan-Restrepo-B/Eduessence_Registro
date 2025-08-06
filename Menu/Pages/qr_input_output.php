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

// Resto del código HTML de la página
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="48x48" href="../Img/Logo.png">
    <title>PUNTO CONTROL IN/OUT</title>
    <!-- Estilos CSS -->
    <link rel="stylesheet" type="text/css" href="../Css/Styles_From.css">
    <link rel="stylesheet" type="text/css" href="../Css/Styles_Qr_Input_Output.css">
</head>
<?php
include("con_db.php");

require('phpqrcode/qrlib.php');

// Procesar el formulario enviado por POST
if (isset($_POST['register'])) {
    $punto = $_POST["lista"];
    $fecha = $_POST["campo2"];
    $curso = $_POST["campo3"];


    $qrData1 = "https://www.eduessence.com?action=checking&control=$punto&cursoId=$curso";

    // Consulta para insertar un nuevo registro en la base de datos
    $sql = "INSERT INTO ENTRADA_SALIDA (INOUT_PUNTO, INOUT_DIRECCION, INOUT_URL_CONTROL, INOUT_FECHORA, INOUT_CURSO)
            VALUES('$punto', 'https://www.eduessence.com/', '$qrData1', '$fecha', '$curso')";

    if (mysqli_query($conn, $sql)) {

        // Generar el QR
        $qrData = "https://www.eduessence.com/?action=checking&control=$punto&cursoId=$curso";

        ob_start();
        QRcode::png($qrData, null, 'M', 10, 3);
        $qrBlob = ob_get_clean();

        $idPunto = mysqli_insert_id($conn);

        $sqlUpdate = "UPDATE ENTRADA_SALIDA SET INOUT_QR = ? WHERE IDPUNTO = ?";
        $stmt = mysqli_prepare($conn, $sqlUpdate);

        if ($stmt) {
            // b = blob (contenido binario), i = integer
            mysqli_stmt_bind_param($stmt, "bi", $null, $idPunto);

            // Especificar el contenido binario real
            mysqli_stmt_send_long_data($stmt, 0, $qrBlob);

            if (mysqli_stmt_execute($stmt)) {
                log("✅ QR actualizado correctamente en IDPUNTO: $idPunto");
            } else {
                log("❌ Error ejecutando el UPDATE: " . mysqli_stmt_error($stmt));
            }

            mysqli_stmt_close($stmt);
        } else {
            log("❌ Error preparando el UPDATE: " . mysqli_error($conn));
        }

        // Recargar la página
        header("Refresh: 0");
        exit;
    } else {
        echo "Error al insertar el registro: " . mysqli_error($conn);
    }
}

// Consulta para obtener todos los registros de ENTRADA_SALIDA
$result = mysqli_query($conn, "SELECT * FROM ENTRADA_SALIDA ORDER BY IDPUNTO DESC");

// Consulta para obtener los puntos de TIPO_DATE con IDTIPODATE en (3, 4)
$result1 = mysqli_query($conn, "SELECT PUNTO FROM TIPO_DATE WHERE IDTIPODATE IN (3, 4)");

$result3 = mysqli_query($conn, "SELECT * FROM CURSOS WHERE IDCURSO NOT IN (1, 2, 3)");

?>

<body>
    <header>
        <!-- Botón para mostrar el formulario emergente -->
        <div class="contenedor">
            <div class="add">
                <a href="#" id="mostrarFormulario">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        style="fill: rgba(255, 255, 255, 1);">
                        <!-- Icono de más -->
                        <path d="M13 7h-2v4H7v2h4v4h2v-4h4v-2h-4z"></path>
                        <!-- Círculo exterior -->
                        <path
                            d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z">
                        </path>
                    </svg>
                </a>
                <!-- Formulario emergente para crear un nuevo punto de ingreso y salida -->
                <div id="formularioEmergente" class="formularioEmergente" style="display: none;">
                    <div class="order">
                        <div class="formulario">
                            <h2>CREAR NUEVO PUNTO DE INGRESO Y SALIDA</h2>
                            <form action="" method="post" id="formulario">
                                <!-- Select para elegir el punto -->
                                <label for="lista">PUNTO:</label>
                                <select id="lista" name="lista">
                                    <?php
                                    while ($row = mysqli_fetch_array($result1)) {
                                        $mostrar = $row['PUNTO'];
                                        echo "<option value='$mostrar'>$mostrar</option>";
                                    }
                                    ?>
                                </select><br><br>
                                <!-- <input type="text" id="campo1" name="campo1" required=""> -->
                                <label for="campo2">FECHA Y HORA:</label>
                                <input type="datetime-local" id="campo2" name="campo2" required=""><br><br>
                                <label for="campo3">CURSO:</label>
                                <select id="campo3" name="campo3">
                                    <?php
                                    while ($row = mysqli_fetch_array($result3)) {
                                        $idCurso = $row['IDCURSO'];
                                        $idNombreCurso = $row['CURSONAME'];
                                        echo "<option value='$idCurso'>$idNombreCurso</option>";
                                    }
                                    ?>
                                </select><br><br>
                                <button class="btn btn-primary btn-block" type="submit" name="register">CREAR</button>
                                &nbsp;&nbsp;
                                <button class="btn btn-secundary btn-block" type="button" name="clsoe"
                                    onclick="ocultarFormulario()">CERRAR</button>
                            </form>
                            <!-- Script para mostrar y ocultar el formulario emergente -->
                            <script defer>
                                function mostrarFormulario() {
                                    var formularioEmergente = document.getElementById("formularioEmergente");
                                    formularioEmergente.style.display = "block";
                                }
                                function ocultarFormulario() {
                                    var formularioEmergente = document.getElementById("formularioEmergente");
                                    formularioEmergente.style.display = "none";
                                    window.location.reload();
                                }
                                var enlaceMostrarFormulario = document.getElementById("mostrarFormulario");
                                enlaceMostrarFormulario.addEventListener("click", mostrarFormulario);
                            </script>
                        </div>
                    </div>
                </div>
            </div>
            <div class="search-bar"></div>
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
                <td class="NroId">#</td>
                <td>PUNTO</td>
                <td>FECHA</td>
                <td>QR</td>
                <td class="titulof"><svg xmlns="http://www.w3.org/2000/svg" margin-left="0" width="24" height="24"
                        viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);">
                        <path
                            d="M19 7h-1V2H6v5H5c-1.654 0-3 1.346-3 3v7c0 1.103.897 2 2 2h2v3h12v-3h2c1.103 0 2-.897 2-2v-7c0-1.654-1.346-3-3-3zM8 4h8v3H8V4zm8 16H8v-4h8v4zm4-3h-2v-3H6v3H4v-7c0-.551.449-1 1-1h14c.552 0 1 .449 1 1v7z">
                        </path>
                        <path d="M14 10h4v2h-4z"></path>
                    </svg></td>
            </tr>
            <?php
            $result = mysqli_query($conn, "SELECT * FROM ENTRADA_SALIDA ORDER BY IDPUNTO DESC");

            // Mostrar los registros de ENTRADA_SALIDA en la tabla
            while ($mostrar = mysqli_fetch_array($result)) {
                ?>
                <tr>
                    <td class="NroId">
                        <?php echo $mostrar['IDPUNTO'] ?>
                    </td>
                    <td>
                        <?php echo $mostrar['INOUT_PUNTO'] ?>
                    </td>
                    <td class="fechav">
                        <?php echo $mostrar['INOUT_FECHORA'] ?>
                    </td>
                    <td class="imgQR">
                        <?php
                        $qrBinary = $mostrar['INOUT_QR'];
                        $qrBase64 = base64_encode($qrBinary);
                        echo "<img width='100' src='data:image/png;base64,{$qrBase64}'>";
                        ?>
                    </td>
                    <td class="btnfrom"><a href="fpdf/QRIN.php?IDPUNTO=<?php echo $mostrar['IDPUNTO']; ?>"
                            target="_blank">IMPRIMIR</a></td>
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
<script>
    window.addEventListener('beforeunload', function (event) {
        // Enviar una solicitud al servidor para cerrar la sesión cuando se cierre la pestaña
        navigator.sendBeacon('../../index.php');
    });
</script>

</html>
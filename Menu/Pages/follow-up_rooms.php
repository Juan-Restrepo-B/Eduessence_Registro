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
    <link rel="icon" type="image/png" sizes="48x48" href="../Img/Logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pruebas</title>
    <!-- Estilos CSS -->
    <link rel="stylesheet" type="text/css" href="../Css/Styles_From.css">
    <link rel="stylesheet" type="text/css" href="../Css/Styles_Follow-Up_Rooms.css">
</head>
<?php
include("con_db.php");

require('phpqrcode/qrlib.php');

// Procesar el formulario enviado por POST
if (isset($_POST['register'])) {
    $punto = $_POST["lista"];
    $direccion = $_POST["campo1"];
    $fecha = $_POST["campo2"];
    $descripcion = $_POST["descrip"];

    // Consulta para insertar un nuevo registro en la base de datos
    $sql2 = "INSERT INTO SEGUIMIENTO_SALAS (SEG_PUNTO, SEG_DIRECCION, SEG_URL_CONTROL, SEG_FECHORA, SEG_DESCRIPCION)
            VALUES('$punto', '$direccion', '$direccion/?action=checking&control=$punto', '$fecha', '$descripcion')";
    if (mysqli_query($conn, $sql2)) {
        // Registro insertado correctamente

        // Generate the QR code data
        $qrData = $direccion . "/?action=checking&control=" . $punto;

        // Generate a unique filename for the QR code image
        $qrFilename = uniqid('qr_', true) . '.png';

        // Path where the QR code image will be stored
        $dir = './qr_codes/seguimiento-salas/' . $qrFilename;

        //Variables
        $tamaño = 10;
        $nivelCorreccion = 'M';
        $margin = 3;
        $contenido = $qrData;

        // Generate and save the QR code as an image
        QRcode::png($contenido, $dir, $nivelCorreccion, $tamaño, $margin);

        // Save the QR code filename to the database
        $sqlUpdate = "UPDATE SEGUIMIENTO_SALAS SET SEG_QR = '$qrFilename' WHERE IDSEGSALAS = " . mysqli_insert_id($conn);
        mysqli_query($conn, $sqlUpdate);

        // Redirect the user to another page after successful form submission
        header("Location: follow-up_rooms.php");
    }
}

// Consulta para obtener todos los registros de ENTRADA_SALIDA
$result = mysqli_query($conn, "SELECT * FROM SEGUIMIENTO_SALAS ORDER BY IDSEGSALAS DESC");

// Consulta para obtener los puntos de TIPO_DATE con IDTIPODATE en (3, 4)
$result1 = mysqli_query($conn, "SELECT PUNTO FROM TIPO_DATE WHERE IDTIPODATE IN (1, 2)");

$result2 = mysqli_query($conn, "SELECT * FROM LINKS WHERE IDLINKS IN (1)");

?>

<body>
    <header>
        <div class="contenedor">
            <!-- Botón para mostrar el formulario emergente -->
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
                                <!-- Campo de texto para la dirección del servicio web -->
                                <label for="campo1">DIRECCION SERVICIO WEB:</label>
                                <select id="campo1" name="campo1">
                                    <?php
                                    while ($row = mysqli_fetch_array($result2)) {
                                        $mostrar = $row['LINKS'];
                                        echo "<option value='$mostrar'>$mostrar</option>";
                                    }
                                    ?>
                                </select><br><br>
                                <label for="descrip">DESCRIPCION:</label>
                                <input type="textarea" id="descrip" name="descrip" required=""><br><br>
                                <label for="campo2">FECHA:</label>
                                <input type="datetime-local" id="campo2" name="campo2" required=""><br><br>
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
                <td class="titulof NroId">#</td>
                <td class="titulof">PUNTO</td>
                <td class="titulof">DESCRIPCION</td>
                <td class="titulof">FECHA</td>
                <td class="titulof">QR</td>
                <td class="titulof"><svg xmlns="http://www.w3.org/2000/svg" margin-left="0" width="24" height="24"
                        viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);">
                        <path
                            d="M19 7h-1V2H6v5H5c-1.654 0-3 1.346-3 3v7c0 1.103.897 2 2 2h2v3h12v-3h2c1.103 0 2-.897 2-2v-7c0-1.654-1.346-3-3-3zM8 4h8v3H8V4zm8 16H8v-4h8v4zm4-3h-2v-3H6v3H4v-7c0-.551.449-1 1-1h14c.552 0 1 .449 1 1v7z">
                        </path>
                        <path d="M14 10h4v2h-4z"></path>
                    </svg></td>
            </tr>
            <?php
            $result = mysqli_query($conn, "SELECT * FROM SEGUIMIENTO_SALAS ORDER BY IDSEGSALAS DESC");

            // Mostrar los registros de ENTRADA_SALIDA en la tabla
            while ($mostrar = mysqli_fetch_array($result)) {
                ?>
                <tr>
                    <td class="NroId">
                        <?php echo $mostrar['IDSEGSALAS'] ?>
                    </td>
                    <td>
                        <?php echo $mostrar['SEG_PUNTO'] ?>
                    </td>
                    <td>
                        <?php echo $mostrar['SEG_DESCRIPCION'] ?>
                    </td>
                    <td class="fechav">
                        <?php echo $mostrar['SEG_FECHORA'] ?>
                    </td>
                    <td class="imgQR"><img width="100" src="./qr_codes/seguimiento-salas/<?php echo $mostrar['SEG_QR'] ?>"></td>
                    <td class="btnfrom"><a href="fpdf/QRSEG.php?IDSEGSALAS=<?php echo $mostrar['IDSEGSALAS']; ?>" target="_blank">IMPRIMIR</a></td>
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
<?php
session_start();
function showButtons($buttonNumber) {

    $user_permission = $_SESSION['user_permission'];

    if ($user_permission == 1) { // Administrador
        return true;
    } elseif ($user_permission == 2) { // Asistente
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="48x48" href="../Img/Logo.png">
    <title>ESPARAPELAS</title>
    <!-- Estilos CSS -->
    <link rel="stylesheet" type="text/css" href="../Css/Styles_From.css">
    <link rel="stylesheet" type="text/css" href="../Css/Styles_Personnel_Registration.css">
</head>

<body>
    <header>
        <div class="contenedor">
            <div class="add"></div>
            <div class="search-bar">
                <form action="" method="get">
                    <input type="text" name="search" class="search" placeholder="Buscar por ID">
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
                <td>ID</td>
                <td>USUARIO</td>
                <td>QR</td>
                <td class="titulof"><svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24"
                        style="fill: rgba(255, 255, 255, 1);">
                        <path
                            d="M12 5c-7.633 0-9.927 6.617-9.948 6.684L1.946 12l.105.316C2.073 12.383 4.367 19 12 19s9.927-6.617 9.948-6.684l.106-.316-.105-.316C21.927 11.617 19.633 5 12 5zm0 11c-2.206 0-4-1.794-4-4s1.794-4 4-4 4 1.794 4 4-1.794 4-4 4z">
                        </path>
                        <path d="M12 10c-1.084 0-2 .916-2 2s.916 2 2 2 2-.916 2-2-.916-2-2-2z"></path>
                    </svg></td>
            </tr>
            <?php
            include("con_db.php");

            $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

            // Consulta para seleccionar registros que coincidan con el término de búsqueda
            $sql = "SELECT DISTINCT * FROM REGISTRO_PERSONA
                    INNER JOIN PERSONAL
                    ON REG_CORREO = PER_CORREO WHERE
                    IDPERSONA LIKE '%$searchTerm%'
                    AND PER_ESTADO = 'ACTIVO'";

            $result = mysqli_query($conn, $sql);

            // Mostrar los registros de PERSONAL en la tabla
            while ($mostrar = mysqli_fetch_array($result)) {
                ?>
                <tr>
                    <td class="NroId">
                        <?php echo $mostrar['IDPERSONA'] ?>
                    </td>
                    <td>
                        <?php echo $mostrar['PER_CORREO'] ?>
                    </td>
                    <td class="imgQR"><img width="100" src="./qr_codes/registro/<?php echo $mostrar['REG_QR'] ?>"></td>
                    <td class="btnfrom"><a href="#" id="mostrarFormulario5"
                            data-idpersona="<?php echo $mostrar['IDPERSONA']; ?>">VER QR</a>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>
        <!-- Elimina el bucle while dentro del formulario emergente -->
        <div id="formularioEmergente5" class="formularioEmergente5" style="display: none;">
            <div class="order5">
                <div class="formulario5">
                    <h2 id="nombreApellido"></h2>
                    <div id="qrContainer"></div>
                    <button class="btn btn-secundary btn-block" type="submit" name="close"
                        onclick="ocultarFormulario5()">CERRAR</button>
                </div>
            </div>
        </div>

        <!-- Script para mostrar y ocultar el formulario emergente -->
        <script defer>
            function mostrarFormulario5(event) {
                var formularioEmergente = document.getElementById("formularioEmergente5");
                formularioEmergente.style.display = "block";

                // Obtener el IDPERSONA del atributo data del enlace
                var idPersona = event.target.getAttribute("data-idpersona");

                // Realizar una solicitud AJAX para obtener los detalles del registro
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "get_persona_details.php?id=" + idPersona, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        var nombre = response.PER_NOMBRES;
                        var apellido = response.PER_APELLIDOS;
                        var qr = response.REG_QR;

                        // Mostrar los detalles en el formulario emergente
                        var nombreApellidoElement = document.getElementById("nombreApellido");
                        nombreApellidoElement.textContent = nombre + " " + apellido;

                        var qrContainerElement = document.getElementById("qrContainer");
                        qrContainerElement.innerHTML = "<img src='./qr_codes/registro/" + qr + "'>";
                    }
                };
                xhr.send();
            }

            function ocultarFormulario5() {
                var formularioEmergente = document.getElementById("formularioEmergente5");
                formularioEmergente.style.display = "none";
            }

            var enlacesMostrarFormulario = document.querySelectorAll("#mostrarFormulario5");
            enlacesMostrarFormulario.forEach(function (enlace) {
                enlace.addEventListener("click", mostrarFormulario5);
            });
        </script>
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
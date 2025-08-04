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

// Resto del código HTML de la página
include("con_db.php");

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Consulta para seleccionar registros que coincidan con el término de búsqueda
$sql = "SELECT DISTINCT * FROM LOG_USERS WHERE
        LOG_IDUSER LIKE '%$searchTerm%'";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="48x48" href="../Img/Logo.png">
    <title>REPORTE ASISTENCIA</title>
    <!-- Estilos CSS -->
    <link rel="stylesheet" type="text/css" href="../Css/Styles_From.css">
    <link rel="stylesheet" type="text/css" href="../Css/Styles_Reports.css">
</head>

<body>
    <header>
        <div class="contenedor">
            <div class="add">
                <a href="#" id="mostrarFormulario4">
                    <svg xmlns="http://www.w3.org/2000/svg" margin-left="0" width="24" height="24" viewBox="0 0 24 24"
                        style="fill: rgba(255, 255, 255, 1);" name="Imprimir">
                        <path
                            d="M19 7h-1V2H6v5H5c-1.654 0-3 1.346-3 3v7c0 1.103.897 2 2 2h2v3h12v-3h2c1.103 0 2-.897 2-2v-7c0-1.654-1.346-3-3-3zM8 4h8v3H8V4zm8 16H8v-4h8v4zm4-3h-2v-3H6v3H4v-7c0-.551.449-1 1-1h14c.552 0 1 .449 1 1v7z">
                        </path>
                        <path d="M14 10h4v2h-4z"></path>
                    </svg>
                </a>
                <!-- Formulario emergente para crear un nuevo punto de ingreso y salida -->
                <div id="formularioEmergente4" class="formularioEmergente4" style="display: none;">
                    <div class="order4">
                        <div class="formulario4">
                            <h2>GENEREAR INFORME</h2>
                            <form action="fpdf/Report.php" method="post" id="formulario4" target="_blank">
                                <label for="iduser">USUARIO:</label>
                                <input type="text" id="inputData" name="inputData" required="" list="dataList">
                                <datalist id="dataList">
                                </datalist><br><br><br>
                                <button class="btn btn-primary btn-block" type="submit" name="register"
                                    onclick="addEventListener()">GENERAR</button>
                                &nbsp;&nbsp;
                                <button class="btn btn-secundary btn-block" type="button" name="clsoe"
                                    onclick="ocultarFormulario4()">CERRAR</button>
                            </form>
                            <!-- Script para mostrar y ocultar el formulario emergente -->
                            <script defer>
                                function mostrarFormulario4() {
                                    var formularioEmergente = document.getElementById("formularioEmergente4");
                                    formularioEmergente.style.display = "block";
                                }
                                function ocultarFormulario4() {
                                    var formularioEmergente = document.getElementById("formularioEmergente4");
                                    formularioEmergente.style.display = "none";
                                    window.location.reload();
                                }
                                var enlaceMostrarFormulario = document.getElementById("mostrarFormulario4");
                                enlaceMostrarFormulario.addEventListener("click", mostrarFormulario4);

                                // Función para obtener la lista de datos mediante AJAX
                                function fetchData() {
                                    fetch('get_data2.php')
                                        .then(response => response.json())
                                        .then(data => {
                                            const dataList = document.getElementById('dataList');
                                            dataList.innerHTML = ''; // Limpiar la lista de opciones existentes
                                            data.forEach(item => {
                                                const option = document.createElement('option');
                                                option.value = item;
                                                dataList.appendChild(option);
                                            });
                                        })
                                        .catch(error => console.error('Error al obtener la lista de datos:', error));
                                }

                                // Llamar a la función para cargar la lista de datos al cargar la página
                                fetchData();

                                // Obtener el campo de entrada y el formulario
                                const inputData = document.getElementById('inputData');
                                const form = document.getElementById('myForm');

                                // Variable para almacenar el valor original del campo de entrada
                                let originalValue = '';

                                // Evento de escucha para el foco en el campo de entrada
                                inputData.addEventListener('focus', function () {
                                    originalValue = inputData.value;
                                });

                                // Evento de escucha para el cambio del campo de entrada
                                inputData.addEventListener('input', function () {
                                    // Verificar si el valor del campo de entrada ha cambiado por copiado o escrito
                                    if (originalValue !== inputData.value) {
                                        updateDataList();
                                    }
                                });

                                // Evento de escucha para el evento de pegado en el campo de entrada
                                inputData.addEventListener('paste', function (event) {
                                    // Cancelar la acción de pegado
                                    event.preventDefault();
                                });

                                // Evento de escucha para el envío del formulario
                                form.addEventListener('submit', function (event) {
                                    // Verificar si el campo de entrada está vacío o si no es una opción válida de la lista
                                    const isValidOption = Array.from(dataList.options).some(option => option.value.toLowerCase() === inputData.value.toLowerCase());
                                    if (!inputData.value || !isValidOption) {
                                        event.preventDefault(); // Cancelar el envío del formulario
                                        alert('Por favor, selecciona una opción válida de la lista.');
                                    }
                                });

                                // Función para actualizar la lista de opciones
                                function updateDataList() {
                                    // Filtrar las opciones de la lista que coincidan con el valor del campo de entrada
                                    const options = Array.from(dataList.options);
                                    const filteredOptions = options.filter(option => option.value.toLowerCase().includes(inputData.value.toLowerCase()));

                                    // Limpiar la lista de opciones existentes
                                    dataList.innerHTML = '';

                                    // Agregar las opciones filtradas nuevamente a la lista
                                    filteredOptions.forEach(option => {
                                        dataList.appendChild(option);
                                    });
                                }
                            </script>
                        </div>
                    </div>
                </div>
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
                <td>USUARIO</td>
                <td>PUNTO</td>
                <td>FECHA Y HORA</td>
            </tr>
            <?php

            // Mostrar los registros de PERSONAL en la tabla
            while ($mostrar = mysqli_fetch_array($result)) {
                ?>
                <tr>
                    <td>
                        <?php echo $mostrar['IDLOG'] ?>
                    </td>
                    <td>
                        <?php echo $mostrar['LOG_IDUSER'] ?>
                    </td>
                    <td>
                        <?php echo $mostrar['LOG_PUNTO'] ?>
                    </td>
                    <td>
                        <?php echo $mostrar['LOG_FECHORA'] ?>
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
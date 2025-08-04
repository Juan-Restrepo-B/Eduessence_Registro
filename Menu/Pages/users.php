<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="48x48" href="../Img/Logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USUARIOS</title>
    <!-- Estilos CSS -->
    <link rel="stylesheet" type="text/css" href="../Css/Styles_From.css">
    <link rel="stylesheet" type="text/css" href="../Css/Styles_Users.css">
</head>
<?php
include("con_db.php");

require('phpqrcode/qrlib.php');

// Procesar el formulario enviado por POST
if (isset($_POST['register'])) {
    $user = $_POST["user"];
    $pass = $_POST["pass"];
    $permiso = $_POST["campo2"];
    $empresa = $_POST["campo3"];

    // Consulta para insertar un nuevo registro en la base de datos
    $sql5 = "INSERT INTO USERS (USER, PASS, USERS_PERMISOS, USERS_SPONSER)
    VALUES ('$user', '$pass', '$permiso', '$empresa')";
    if (mysqli_query($conn, $sql5)) {
        header("Location: users.php");
    }
}
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
$result = mysqli_query($conn, "SELECT DISTINCT * FROM PERMISOS p WHERE p.IDPERMISOS IN (2, 3, 4)");
$result2 = mysqli_query($conn, "SELECT DISTINCT * FROM SPONSER s ");
?>

<body>
    <header>
        <!-- Botón para mostrar el formulario emergente -->
        <div class="contenedor">
            <div class="add">
                <a href="#" id="mostrarFormulario">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        style="fill: rgba(255, 255, 255, 1)">
                        <path
                            d="M19 8h-2v3h-3v2h3v3h2v-3h3v-2h-3zM4 8a3.91 3.91 0 0 0 4 4 3.91 3.91 0 0 0 4-4 3.91 3.91 0 0 0-4-4 3.91 3.91 0 0 0-4 4zm6 0a1.91 1.91 0 0 1-2 2 1.91 1.91 0 0 1-2-2 1.91 1.91 0 0 1 2-2 1.91 1.91 0 0 1 2 2zM4 18a3 3 0 0 1 3-3h2a3 3 0 0 1 3 3v1h2v-1a5 5 0 0 0-5-5H7a5 5 0 0 0-5 5v1h2z">
                        </path>
                    </svg>
                </a>
                <!-- Formulario emergente para crear un nuevo punto de ingreso y salida -->
                <div id="formularioEmergente" class="formularioEmergente" style="display: none;">
                    <div class="order">
                        <div class="formulario">
                            <h2>CREAR NUEVO USUARIO</h2>
                            <form action="" method="post" id="formulario">
                                <!-- Select para elegir el punto -->
                                <label for="lista">Permiso:</label>
                                <select id="campo2" name="campo2">
                                    <?php
                                    while ($row = mysqli_fetch_array($result)) {
                                        $mostrar = $row['USER_PERMISO'];
                                        $mostrar1 = $row['IDPERMISOS'];
                                        echo "<option value='$mostrar1'>$mostrar</option>";
                                    }
                                    ?>
                                </select><br><br>
                                <label for="lista">Empresa:</label>
                                <select id="campo3" name="campo3">
                                    <?php
                                    while ($row1 = mysqli_fetch_array($result2)) {
                                        $mostrar4 = $row1['SPONSER_NAME'];
                                        $mostrar5 = $row1['IDSPONSOR'];
                                        echo "<option value='$mostrar5'>$mostrar4</option>";
                                    }
                                    ?>
                                </select><br><br>
                                <!-- Campo de texto para la dirección del servicio web -->
                                <label for="campo1">Ususario:</label>
                                <input id="campo1" type="text" name="user"><br><br>
                                <label for="campo1">Password:</label>
                                <input id="campo1" type="password" name="pass"><br><br>

                                <button class="btn btn-primary btn-block" type="submit" name="register">CREAR</button>
                                &nbsp;&nbsp;
                                <button class="btn btn-secundary btn-block" type="button" name="close"
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
                <td>USUARIO</td>
                <td>PERMISO</td>
                <td><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        style="fill: rgba(255, 255, 255, 1);">
                        <path
                            d="m7 17.013 4.413-.015 9.632-9.54c.378-.378.586-.88.586-1.414s-.208-1.036-.586-1.414l-1.586-1.586c-.756-.756-2.075-.752-2.825-.003L7 12.583v4.43zM18.045 4.458l1.589 1.583-1.597 1.582-1.586-1.585 1.594-1.58zM9 13.417l6.03-5.973 1.586 1.586-6.029 5.971L9 15.006v-1.589z">
                        </path>
                        <path
                            d="M5 21h14c1.103 0 2-.897 2-2v-8.668l-2 2V19H8.158c-.026 0-.053.01-.079.01-.033 0-.066-.009-.1-.01H5V5h6.847l2-2H5c-1.103 0-2 .897-2 2v14c0 1.103.897 2 2 2z">
                        </path>
                    </svg></td>
            </tr>
            <?php
            // Mostrar los registros de ENTRADA_SALIDA en la tabla
            $result1 = mysqli_query($conn, "SELECT DISTINCT * FROM USERS u INNER JOIN PERMISOS p 
            ON u.USERS_PERMISOS = p.IDPERMISOS WHERE p.IDPERMISOS IN (2, 3, 4)");
            while ($mostrar = mysqli_fetch_array($result1)) {
                ?>
                <tr>
                    <td class="NroId">
                        <?php echo $mostrar['IDUSER'] ?>
                    </td>
                    <td>
                        <?php echo $mostrar['USER'] ?>
                    </td>
                    <td>
                        <?php echo $mostrar['USER_PERMISO'] ?>
                    </td>
                    <td class="btnfrom">
                        <a href="#" id="mostrarFormulario5" data-iduser="<?php echo $mostrar['IDUSER']; ?>"
                            data-username="<?php echo $mostrar['USER']; ?>">EDITAR</a>
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
                    <h2 id="nombreApellido"></h2><br><br>
                    <label for="inputUser">Ususario:</label>
                    <input type="text" id="inputUser" placeholder="Nuevo usuario"><br><br>
                    <label for="inputPass">Password:</label>
                    <input type="password" id="inputPass" placeholder="Nueva contraseña"><br><br>
                    <label for="selectPermisos">Permiso:</label>
                    <select id="selectPermisos">
                        <?php
                        $result2 = mysqli_query($conn, "SELECT DISTINCT * FROM PERMISOS p WHERE p.IDPERMISOS IN (2, 3, 4)");
                        while ($row = mysqli_fetch_array($result2)) {
                            $mostrar = $row['USER_PERMISO'];
                            $mostrar1 = $row['IDUSERS'];
                            echo "<option value='$mostrar1'>$mostrar</option>";
                        }
                        ?>
                    </select><br><br>
                    <button class="btn btn-primary btn-block" type="submit" name="update"
                        onclick="guardarCambios()">GUARDAR</button>
                        &nbsp;&nbsp;
                    <button class="btn btn-secundary btn-block" type="button" name="close"
                        onclick="ocultarFormulario()">CERRAR</button>
                </div>
            </div>
        </div>
        <!-- Script para mostrar y ocultar el formulario emergente -->
        <script defer>

            function mostrarFormulario5(event) {
                var formularioEmergente = document.getElementById("formularioEmergente5");
                formularioEmergente.style.display = "block";

                // Obtener el IDPERSONA del atributo data del enlace
                currentIdPersona = event.target.getAttribute("data-iduser");

                // Obtener el iduser y el nombre de usuario del atributo data del enlace
                var idUser = event.target.getAttribute("data-iduser");
                var user = event.target.getAttribute("data-username");

                // Mostrar el nombre de usuario junto con el iduser en el elemento h2 con id "nombreApellido"
                var nombreApellidoElement = document.getElementById("nombreApellido");
                nombreApellidoElement.textContent = "Editar: " + user;
            }



            function ocultarFormulario5() {
                var formularioEmergente = document.getElementById("formularioEmergente5");
                formularioEmergente.style.display = "none";
            }

            var enlacesMostrarFormulario = document.querySelectorAll("#mostrarFormulario5");
            enlacesMostrarFormulario.forEach(function (enlace) {
                enlace.addEventListener("click", mostrarFormulario5);
            });

            function guardarCambios() {
                var newUser = document.getElementById("inputUser").value;
                var newPass = document.getElementById("inputPass").value;
                var newPermiso = document.getElementById("selectPermisos").value;

                // Realizar una solicitud AJAX para actualizar los datos en la tabla USERS
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "update_user.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                        // Actualización exitosa, puedes mostrar un mensaje de éxito o hacer lo que desees
                        console.log("Datos actualizados correctamente.");
                        // Aquí puedes agregar lógica adicional después de actualizar los datos, si es necesario.
                        // Por ejemplo, puedes ocultar el formulario emergente nuevamente:
                        ocultarFormulario5();
                    }
                };
                // Envía los datos de actualización junto con el idPersona actual
                xhr.send("id=" + currentIdPersona + "&user=" + newUser + "&pass=" + newPass + "&permiso=" + newPermiso);
            }
        </script>
    </main>
    <footer class="footer">
        <p>&copy;Todos los derechos reservados por &nbsp;
        <div class="class"> Juan Restrepo</div>
        </p>
    </footer>
</body>

</html>
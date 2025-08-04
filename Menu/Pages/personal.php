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
    <link rel="stylesheet" type="text/css" href="../Css/Styles_Personal.css">
</head>
<?php
include("con_db.php");

require('phpqrcode/qrlib.php');

// Procesar el formulario enviado por POST
if (isset($_POST['register'])) {
    $correo = $_POST["correo"];
    $name = $_POST["name"];
    $surnames = $_POST["surnames"];
    $doc = $_POST["doc"];
    $pais = $_POST["inputData"];
    $telef = $_POST["telef"];
    $cat = $_POST["campo1"];
    $cur = $_POST["campo3"];

    // Consulta para insertar un nuevo registro en la base de datos
    $sql = "INSERT INTO PERSONAL
    (PER_NOMBRES, PER_APELLIDOS, PER_CORREO, PER_DOCUMENTO, PER_TELEFONO, PER_PAIS, PER_CATEGORIA, PER_CURSO, PER_ESTADO)
    VALUES('$name', '$surnames', '$correo', '$doc', '$telef', '$pais', '$cat', '$cur', 'ACTIVO')";
    if (mysqli_query($conn, $sql)) {
        // Registro insertado correctamente

        // Generate the QR code data
        $qrData = 'http://registro.eduessence.com/?action=registro&control=VISITA&userid=' . $doc;

        // Generate a unique filename for the QR code image
        $qrFilename = uniqid('qr_', true) . '.png';

        // Path where the QR code image will be stored
        $dir = './qr_codes/personal/' . $qrFilename;

        //Variables
        $tamaño = 10;
        $nivelCorreccion = 'M';
        $margin = 3;
        $contenido = $qrData;

        // Generate and save the QR code as an image
        QRcode::png($contenido, $dir, $nivelCorreccion, $tamaño, $margin);

        // Save the QR code filename to the database
        $sqlUpdate = "UPDATE PERSONAL SET PER_QR = '$qrFilename' WHERE IDPERSONA = " . mysqli_insert_id($conn);
        mysqli_query($conn, $sqlUpdate);
    }
    // Consulta para insertar un nuevo registro en la base de datos
    $sql1 = "INSERT INTO REGISTRO_PERSONA
    (REG_CORREO, REG_URL_CONTROL)
    VALUES('$correo', 'http://registro.eduessence.com/?action=register&control=REGISTRO&userid=$correo')";
    if (mysqli_query($conn, $sql1)) {
        // Registro insertado correctamente

        // Generate the QR code data
        $qrData1 = "http://registro.eduessence.com/?action=register&control=REGISTRO&userid=$correo";

        // Generate a unique filename for the QR code image
        $qrFilename = uniqid('qr_', true) . '.png';

        // Path where the QR code image will be stored
        $dir1 = './qr_codes/registro/' . $qrFilename;

        //Variables
        $tamaño = 10;
        $nivelCorreccion = 'M';
        $margin = 3;
        $contenido1 = $qrData1;

        // Generate and save the QR code as an image
        QRcode::png($contenido1, $dir1, $nivelCorreccion, $tamaño, $margin);

        // Save the QR code filename to the database
        $sqlUpdate1 = "UPDATE REGISTRO_PERSONA SET REG_QR = '$qrFilename' WHERE IDREGPER = " . mysqli_insert_id($conn);
        mysqli_query($conn, $sqlUpdate1);

        $result4 = mysqli_query($conn, "SELECT * FROM CURSOS WHERE IDCURSO = $cur");
        $row = mysqli_fetch_assoc($result4);

        $cursName = $row['CURSONAME'];

        require 'Mail/vendor/autoload.php'; // Cargar la librería SendGrid


        if ($cursName === 'SUMMIT') {
            $from_email = new \SendGrid\Mail\From("info@eduessence.com", "EDUESSENCE");
            $to_email = new \SendGrid\Mail\To("$correo", "$name $surnames");
            $subject = new \SendGrid\Mail\Subject("REGISTRO EXITOSO AL $cursName EDUESSENCE 2023");
            $htmlContent = '<p style="font-size: 1.5rem;">
                        Buenas tardes, 
                        <br><br>
                        Para el comité científico del Summit Eduessence 2023 de Pie Diabético, Heridas Complejas  y sus Comorbilidades,
                        es un placer contar con ustedes dentro del grupo de asistentes a este gran evento de impacto latinoamericano, a 
                        desarrollarse en la ciudad de Barranquilla, Colombia el proximo 18 y 19 de Agosto.
                        <br><br>
                        Adjunto carta de confirmación de su inscripción, y programa final del SUMMIT que podra descargar en el siguiente codigo QR.
                        <br><br>
                        <div class="col-4 col-6 col-sm-4;"><a href=""><img src="cid:imagen_adjunta" alt="Imagen adjunta" style=" max-width: 150px; height: auto;"></a></div>
                        <br><br>
                        Nos vemos pronto en Barranquilla.
                        <br><br>
                        Cordial saludo,
                        <br>
                        COMITÉ CIENTIFICO
                        </p>';
            $content = new \SendGrid\Mail\HtmlContent($htmlContent);

            $mail = new \SendGrid\Mail\Mail($from_email, $to_email, $subject);
            $mail->addContent($content); // Agregar el contenido al correo

            // Adjuntar un archivo PDF
            $fileContent = file_get_contents('Mail/content/Logistica Inscritos Summit 2023.pdf'); // Cambia 'ruta_del_archivo.pdf' por la ruta real del archivo
            $attachment = new \SendGrid\Mail\Attachment();
            $attachment->setContent(base64_encode($fileContent));
            $attachment->setType('application/pdf');
            $attachment->setFilename('archivo_adjunto.pdf');
            $attachment->setDisposition('attachment');
            $mail->addAttachment($attachment);

            // Adjuntar la imagen como incrustada (CID)
            $imageContent = file_get_contents('Mail/content/QRSUMMIT.png'); // Cambia 'ruta_de_la_imagen.png' por la ruta real de la imagen
            $imageAttachment = new \SendGrid\Mail\Attachment();
            $imageAttachment->setContent(base64_encode($imageContent));
            $imageAttachment->setType('image/png');
            $imageAttachment->setFilename('imagen_adjunta.png');
            $imageAttachment->setDisposition('inline');
            $imageAttachment->setContentId('imagen_adjunta'); // Define un ID único para la imagen
            $mail->addAttachment($imageAttachment);

            $apiKey = ''; // Reemplaza con tu API Key de SendGrid
            $sendgrid = new \SendGrid($apiKey);

            try {
                $response = $sendgrid->send($mail);
                echo 'Envio Exitoso';
            } catch (Exception $e) {
                echo 'Ocurrió un error: ' . $e->getMessage() . "\n";
            }
        } else if ($cursName === 'TtFT') {
            $from_email = new \SendGrid\Mail\From("info@eduessence.com", "EDUESSENCE");
            $to_email = new \SendGrid\Mail\To("$correo", "$name $surnames");
            $subject = new \SendGrid\Mail\Subject("REGISTRO EXITOSO AL $cursName EDUESSENCE 2023");
            $htmlContent = '<p style="font-size: 1.5rem;">
                Buenas tardes, 
                <br><br>
                Para el comité científico del Summit Eduessence 2023 de Pie Diabético, Heridas 
                Complejas  y sus Comorbilidades, así como del Curso Train the Foot Trainer, 
                es un placer contar con usted. dentro del grupo de asistentes, de este gran 
                evento con impacto latinoamericano, a desarrollarse en la ciudad de 
                Barranquilla-Colombia del 16 al 19 de Agosto.
                <br><br>
                Adjunto carta de confirmación de su inscripción, y programa final del TtFT en el siguiente codigo QR.
                <br><br>
                <a href=""><img src="cid:imagen_adjunta" alt="Imagen adjunta" style="width: 150px;"></a>
                <br><br>
                Nos vemos pronto en Barranquilla.
                <br><br>
                Cordial saludo,
                <br>
                COMITÉ CIENTIFICO
                </p>';
            $content = new \SendGrid\Mail\HtmlContent($htmlContent);

            $mail = new \SendGrid\Mail\Mail($from_email, $to_email, $subject);
            $mail->addContent($content); // Agregar el contenido al correo

            // Adjuntar un archivo PDF
            $fileContent = file_get_contents('Mail/content/Logistica Inscritos Pre summit y Summit 2023.pdf'); // Cambia 'ruta_del_archivo.pdf' por la ruta real del archivo
            $attachment = new \SendGrid\Mail\Attachment();
            $attachment->setContent(base64_encode($fileContent));
            $attachment->setType('application/pdf');
            $attachment->setFilename('archivo_adjunto.pdf');
            $attachment->setDisposition('attachment');
            $mail->addAttachment($attachment);

            // Adjuntar la imagen como incrustada (CID)
            $imageContent = file_get_contents('Mail/content/QRTtFT.png'); // Cambia 'ruta_de_la_imagen.png' por la ruta real de la imagen
            $imageAttachment = new \SendGrid\Mail\Attachment();
            $imageAttachment->setContent(base64_encode($imageContent));
            $imageAttachment->setType('image/png');
            $imageAttachment->setFilename('imagen_adjunta.png');
            $imageAttachment->setDisposition('inline');
            $imageAttachment->setContentId('imagen_adjunta'); // Define un ID único para la imagen
            $mail->addAttachment($imageAttachment);

            $apiKey = ''; // Reemplaza con tu API Key de SendGrid
            $sendgrid = new \SendGrid($apiKey);

            try {
                $response = $sendgrid->send($mail);
                echo 'Envio Exitoso';
            } catch (Exception $e) {
                echo 'Ocurrió un error: ' . $e->getMessage() . "\n";
            }
        }
        // Redirect the user to another page after successful form submission
        header("Location: personal.php");
    }
}

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Consulta para seleccionar registros que coincidan con el término de búsqueda
$sql = "SELECT * FROM PERSONAL WHERE
        (PER_DOCUMENTO LIKE '%$searchTerm%' OR
        PER_NOMBRES LIKE '%$searchTerm%' OR
        PER_APELLIDOS LIKE '%$searchTerm%' OR
        PER_CORREO LIKE '%$searchTerm%')
        AND PER_ESTADO = 'ACTIVO'";

$result = mysqli_query($conn, $sql);
$result2 = mysqli_query($conn, "SELECT * FROM CATEGORIA");
$result3 = mysqli_query($conn, "SELECT * FROM CURSOS");
?>

<body>
    <header>
        <div class="contenedor">
            <!-- Botón para mostrar el formulario emergente -->
            <div class="add">
                <?php if (showButtons(1)): ?>
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
                                <h2>CREAR NUEVO REGISTRO</h2>
                                <form action="" method="post" id="formulario">
                                    <label for="doc">DOCUMENTO:</label>
                                    <input type="text" id="doc" name="doc" required=""
                                        oninput="validateDocumentInput(this)"><br><br>
                                    <label for="name">NOMBRES:</label>
                                    <input type="text" id="name" name="name" required=""
                                        oninput="validateLettersInput(this)">&nbsp;
                                    <label for="surnames">APELLIDOS:</label>
                                    <input type="text" id="surnames" name="surnames" required=""
                                        oninput="validateLettersInput(this)"><br><br>
                                    <label for="correo">CORREO:</label>
                                    <input type="text" id="correo" name="correo" required=""
                                        oninput="validateEmailInput(this)">&nbsp;
                                    <label for="telef">TELEFONO:</label>
                                    <input type="text" id="telef" name="telef" required=""
                                        oninput="validateNumberInput(this)"><br><br>
                                    <label for="pais">PAIS:</label>
                                    <input type="text" id="inputData" name="inputData" required="" list="dataList">
                                    <datalist id="dataList">
                                    </datalist>&nbsp;
                                    <label for="campo1">CATEGORIA:</label>
                                    <select id="campo1" name="campo1">
                                        <?php
                                        while ($row = mysqli_fetch_array($result2)) {
                                            $mostrar = $row['CATEGNAME'];
                                            $mostrar2 = $row['IDCATEG'];
                                            echo "<option value='$mostrar2'>$mostrar</option>";
                                        }
                                        ?>
                                    </select><br><br>
                                    <label for="campo3">CURSO:</label>
                                    <select id="campo3" name="campo3">
                                        <?php
                                        while ($row = mysqli_fetch_array($result3)) {
                                            $mostrar = $row['CURSONAME'];
                                            $mostrar1 = $row['IDCURSO'];
                                            echo "<option value='$mostrar1'>$mostrar</option>";
                                        }
                                        ?>
                                    </select><br><br>
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
                                        window.onload();
                                    }
                                    var enlaceMostrarFormulario = document.getElementById("mostrarFormulario");
                                    enlaceMostrarFormulario.addEventListener("click", mostrarFormulario);
                                    function validateDocumentInput(input) {
                                        input.value = input.value.replace(/[^A-Z\s\d]/g, ''); // Remueve todos los caracteres no numéricos

                                        // Limita la longitud del valor del campo a 15 dígitos
                                        if (input.value.length > 15) {
                                            input.value = input.value.slice(0, 15);
                                        }
                                    }
                                    function validateNumberInput(input) {
                                        input.value = input.value.replace(/\D/g, ''); // Remueve todos los caracteres no numéricos
                                        if (input.value.length > 11) {
                                            input.value = input.value.slice(0, 11);
                                        }
                                    }
                                    function validateLettersInput(input) {
                                        input.value = input.value.replace(/[^A-ZÁÉÍÓÚÑÜ\s]/g, ''); // Remueve todos los caracteres no alfabéticos

                                        // Limita la longitud del valor del campo a 15 dígitos
                                        if (input.value.length > 30) {
                                            input.value = input.value.slice(0, 30);
                                        }
                                    }
                                    function validateEmailInput(input) {
                                        const email = input.value;
                                        const atIndex = email.indexOf('@');
                                        const dotIndex = email.lastIndexOf('.');
                                        // Limita la longitud del valor del campo a 15 dígitos
                                        if (input.value.length > 50) {
                                            input.value = input.value.slice(0, 50);
                                        }
                                        if (atIndex === -1 || dotIndex === -1 || dotIndex < atIndex) {
                                            input.setCustomValidity('Ingresa una dirección de correo válida.');
                                        } else {
                                            input.setCustomValidity('');
                                        }
                                    }
                                    // Función para obtener la lista de datos mediante AJAX
                                    function fetchData() {
                                        fetch('get_data.php')
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
                    <div class="contenedor1">
                        <a href="#" id="mostrarFormulario1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                style="fill: rgba(255, 255, 255, 1);">
                                <path
                                    d="m21.706 5.292-2.999-2.999A.996.996 0 0 0 18 2H6a.996.996 0 0 0-.707.293L2.294 5.292A.994.994 0 0 0 2 6v13c0 1.103.897 2 2 2h16c1.103 0 2-.897 2-2V6a.994.994 0 0 0-.294-.708zM6.414 4h11.172l1 1H5.414l1-1zM14 14v3h-4v-3H7l5-5 5 5h-3z">
                                </path>
                            </svg>
                        </a>
                        <div id="formularioEmergente1" class="formularioEmergente1" style="display: none;">
                            <div class="order1">
                                <div class="formulario1">
                                    <h2>CARGUE DE ARCHIVO MASIVO</h2>
                                    <div class="col-md-5">
                                        <form action="subir.php" method="post" enctype="multipart/form-data">
                                            <div class="file-input text-center">
                                                <input type="file" name="dataCliente" id="file-input"
                                                    class="estilo-input-file" accept=".csv" required="" />
                                            </div> <br>
                                            <div class="text-center mt-5">
                                                <button type="submit" name="subir" class="btn-enviar"
                                                    value="Subir Archivo CSV">SUBIR ARCHIVO .CSV</button>&nbsp;&nbsp;
                                                <button class="btn-secun" type="button" name="close"
                                                    onclick="ocultarFormulario1()">CERRAR</button>
                                            </div>
                                        </form>
                                    </div>
                                    <!-- Script para mostrar y ocultar el formulario emergente -->
                                    <script defer>
                                        function mostrarFormulario1() {
                                            var formularioEmergente = document.getElementById("formularioEmergente1");
                                            formularioEmergente.style.display = "block";
                                        }
                                        function ocultarFormulario1() {
                                            var formularioEmergente = document.getElementById("formularioEmergente1");
                                            formularioEmergente.style.display = "none";
                                            window.onload();
                                        }
                                        var enlaceMostrarFormulario = document.getElementById("mostrarFormulario1");
                                        enlaceMostrarFormulario.addEventListener("click", mostrarFormulario1);
                                    </script>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="search-bar">
                <form action="" method="get">
                    <input type="text" name="search" class="search"
                        placeholder="Buscar por Documento, Nombre, Apellido o Correo">
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
                <td class="titulof NroId">ID</td>
                <td class="titulof">DOCUMENTO</td>
                <td class="titulof">NOMBRE Y APELLIDO</td>
                <td class="titulof">CORREO</td>
                <td class="titulof">TELEFONO</td>
                <td class="titulof">PAIS</td>
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

            // Mostrar los registros de PERSONAL en la tabla
            while ($mostrar = mysqli_fetch_array($result)) {
                ?>
                <tr>
                    <td>
                        <?php echo $mostrar['IDPERSONA'] ?>
                    </td>
                    <td>
                        <?php echo $mostrar['PER_DOCUMENTO'] ?>
                    </td>
                    <td>
                        <?php echo $mostrar['PER_NOMBRES'] ?>
                        <?php echo $mostrar['PER_APELLIDOS'] ?>
                    </td>
                    <td>
                        <?php echo $mostrar['PER_CORREO'] ?>
                    </td>
                    <td>
                        <?php echo $mostrar['PER_TELEFONO'] ?>
                    </td>
                    <td>
                        <?php echo $mostrar['PER_PAIS'] ?>
                    </td>
                    <td class="imgQR"><img width="100" src="./qr_codes/personal/<?php echo $mostrar['PER_QR'] ?>"></td>
                    <td class="btnfrom"><a
                            href="fpdf/Personal.php?IDPERSONA=<?php echo $mostrar['IDPERSONA'] ?>&CATEGORIA=<?php echo $mostrar['PER_CATEGORIA'] ?>"
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
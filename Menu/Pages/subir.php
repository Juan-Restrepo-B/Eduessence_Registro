<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>CARGUE</title>
    <link rel="stylesheet" href="">
</head>

<body>

    <?php
    header('Content-Type: text/html; charset=UTF-8');

    include("con_db.php");

    require('./phpqrcode/qrlib.php');

    $archivotmp = $_FILES['dataCliente']['tmp_name'];
    $lineas = file($archivotmp);
    $cantidad_registros = count($lineas);
    $cantidad_regist_agregados = ($cantidad_registros - 1);

    foreach ($lineas as $i => $linea) {
        if ($i == 0) {
            // Salta la primera línea de encabezados
            continue;
        }

        if ($i != 0) {

            $datos = explode(";", $linea);
            $doc1 = trim($datos[0]);
            $name1 = isset($datos[1]) ? trim($datos[1]) : '';
            $surnames1 = isset($datos[2]) ? trim($datos[2]) : '';
            $correo1 = isset($datos[3]) ? trim($datos[3]) : '';
            $telef = isset($datos[4]) ? trim($datos[4]) : '';
            $pais = isset($datos[5]) ? trim($datos[5]) : '';
            $catName = isset($datos[6]) ? trim($datos[6]) : '';
            $cursName = isset($datos[7]) ? trim($datos[7]) : '';

            $result4 = mysqli_query($conn, "SELECT * FROM CURSOS WHERE CURSONAME = '$cursName'");
            $rowCurso = mysqli_fetch_assoc($result4);
            $cur = $rowCurso['IDCURSO'];

            $result5 = mysqli_query($conn, "SELECT * FROM CATEGORIA WHERE CATEGNAME = '$catName'");
            $rowCategoria = mysqli_fetch_assoc($result5);
            $cat = $rowCategoria['IDCATEG'];


            $sql2 = "INSERT INTO PERSONAL
            (PER_NOMBRES, PER_APELLIDOS, PER_CORREO, PER_DOCUMENTO, PER_TELEFONO, PER_PAIS, PER_CATEGORIA, PER_CURSO, PER_ESTADO)
            VALUES('$name1', '$surnames1', '$correo1', '$doc1', '$telef', '$pais', '$cat', '$cur', 'ACTIVO')";

            if (mysqli_query($conn, $sql2)) {
                // Registro insertado correctamente
    
                // Genera el código QR para el registro
                $qrData2 = 'http://registro.eduessence.com/?action=registro&control=VISITA&userid=' . $doc1;
                $qrFilename = uniqid('qr_', true) . '.png';
                $dir2 = './qr_codes/personal/' . $qrFilename;
                $tamaño = 10;
                $nivelCorreccion = 'M';
                $margin = 3;
                QRcode::png($qrData2, $dir2, $nivelCorreccion, $tamaño, $margin);

                // Guarda el nombre del archivo QR en la base de datos
                $sqlUpdate2 = "UPDATE PERSONAL SET PER_QR = '$qrFilename' WHERE IDPERSONA = " . mysqli_insert_id($conn);
                mysqli_query($conn, $sqlUpdate2);

            }
            // Consulta para insertar un nuevo registro en la base de datos
            $sql3 = "INSERT INTO REGISTRO_PERSONA (REG_CORREO, REG_URL_CONTROL)
            VALUES('$correo1', 'http://registro.eduessence.com/?action=register&control=REGISTRO&userid=$correo1')";
            if (mysqli_query($conn, $sql3)) {

                // Genera el código QR para el registro en la tabla REGISTRO_PERSONA
                $qrData3 = "http://registro.eduessence.com/?action=register&control=REGISTRO&userid=" . $correo1;
                $qrFilename = uniqid('qr_', true) . '.png';
                $dir3 = './qr_codes/registro/' . $qrFilename;
                QRcode::png($qrData3, $dir3, $nivelCorreccion, $tamaño, $margin);

                // Guarda el nombre del archivo QR en la tabla REGISTRO_PERSONA
                $sqlUpdate3 = "UPDATE REGISTRO_PERSONA SET REG_QR = '$qrFilename' WHERE IDREGPER = " . mysqli_insert_id($conn);
                mysqli_query($conn, $sqlUpdate3);

                require('Mail/vendor/autoload.php');

                if ($cursName === 'SUMMIT') {
                    $from_email = new \SendGrid\Mail\From("info@eduessence.com", "EDUESSENCE");
                    $to_email = new \SendGrid\Mail\To("$correo1", "$name1 $surnames1");
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
                    $to_email = new \SendGrid\Mail\To("$correo1", "$name1 $surnames1");
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
        echo '<center><p style="text-align:center; color:#333;">Total de Registros: ' . $cantidad_regist_agregados . '</p></center>';
        echo "<center><a href='./Personal.php'>Atras</a></center>";
    }
    ?>

</body>

</html>
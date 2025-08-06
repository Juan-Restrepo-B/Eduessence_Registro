<?php

if (!empty($_POST["inputData"])) {
   require('fpdf.php');

   $userid = $_POST["inputData"];
   class PDF extends FPDF
   {

      // Cabecera de página
      function Header()
      {
         if (!empty($_POST["inputData"])) {
            include 'conexion_bd.php'; //llamamos a la conexion BD
            $userid = $_POST["inputData"];


            $consulta_info = $conn->query("SELECT * FROM EMPRESA"); //traemos datos de la empresa desde BD
            $dato_info = $consulta_info->fetch_object();
            $this->Image('Logo.jpeg', 160, 5, 45); //logo de la empresa,moverDerecha,moverAbajo,tamañoIMG
            $this->Image('LogoD.jpg', 15, 5, 30); //logo de la empresa,moverDerecha,moverAbajo,tamañoIMG
            $this->SetFont('Arial', 'B', 19); //tipo fuente, negrita(B-I-U-BIU), tamañoTexto
            $this->Cell(37); // Movernos a la derecha
            $this->SetTextColor(0, 0, 0); //color
            //creamos una celda o fila
            $this->Cell(110, 1, utf8_decode($dato_info->NAME_EMPRESA), 0, 1, 'C', 0);
            // AnchoCelda,AltoCelda,titulo,borde(1-0),saltoLinea(1-0),posicion(L-C-R),ColorFondo(1-0)
            //$this->Ln(0.5); // Salto de línea
            $this->SetTextColor(103); //color

            /* UBICACION */
            $this->Cell(62); // mover a la derecha
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(96, 10, utf8_decode("Ubicación: " . $dato_info->UBICACION), 0, 0, '', 0);
            $this->Ln(15);

            /* TELEFONO 
            $this->Cell(70); // mover a la derecha
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(59, 10, utf8_decode("Teléfono: " . $dato_info->TELEFONO), 0, 0, '', 0);
            $this->Ln(5);*/

            /* COREEO 
            $this->Cell(70); // mover a la derecha
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(85, 10, utf8_decode("Correo: " . $dato_info->CORREO), 0, 0, '', 0);
            $this->Ln(5);*/

            /* SUCURSAL 
            $this->Cell(70); // mover a la derecha
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(85, 10, utf8_decode("Sucursal: " . $dato_info->SUCURSAL), 0, 0, '', 0);
            $this->Ln(10);*/
            $consulta_reporte_alquiler1 = $conn->query("SELECT
    IDPERSONA,
    PER_NOMBRES,
    PER_APELLIDOS,
    PER_CORREO,
    PER_DOCUMENTO,
    PER_QR,
    PER_TELEFONO,
    PER_PAIS,
    LOG_PUNTO,
    LOG_FECHORA,
    TIMESTAMPDIFF(
        SECOND,
        (
            SELECT MAX(LOG_FECHORA)
            FROM LOG_USERS
            WHERE LOG_FECHORA < lu.LOG_FECHORA
            AND LOG_IDUSER = lu.LOG_IDUSER
        ),
        LOG_FECHORA
    ) AS TIEMPO_REGISTROS
FROM
    LOG_USERS lu
INNER JOIN
    PERSONAL p ON lu.LOG_IDUSER = p.PER_CORREO
WHERE
    lu.LOG_IDUSER='$userid'
    AND PER_ESTADO = 'ACTIVO'
    AND TIME(LOG_FECHORA) BETWEEN '06:55:00' AND '18:00:00'");

            $datos_reporte1 = $consulta_reporte_alquiler1->fetch_object();

            /* TITULO DE LA TABLA */
            //color
            $this->SetTextColor(255, 0, 0);
            $this->Cell(45); // mover a la derecha
            $this->SetFont('Arial', 'B', 15);
            $this->Cell(100, 10, utf8_decode("REPORTE DE ASISTENCIA "), 0, 1, 'C', 0);
            $this->SetTextColor(0, 0, 0);
            $this->SetFont('Arial', 'B', 11);
            $this->Cell(45); // mover a la derecha
            $this->Cell(100, 5, utf8_decode("USUARIO: " . $datos_reporte1->PER_CORREO), 0, 1, 'C', 0);
            $this->Ln(2);

            /* CAMPOS DE LA TABLA */
            //color
            $this->SetFillColor(0, 0, 0); //colorFondo
            $this->SetTextColor(255, 255, 255); //colorTexto
            $this->SetDrawColor(163, 163, 163); //colorBorde
            $this->SetFont('Arial', 'B', 11);
            $this->Cell(8, 10, utf8_decode('N°'), 1, 0, 'C', 1);
            $this->Cell(28, 10, utf8_decode('DOCUMENTO'), 1, 0, 'C', 1);
            $this->Cell(85, 10, utf8_decode('NOMBRE Y APELLIDO'), 1, 0, 'C', 1);
            $this->Cell(25, 10, utf8_decode('PUNTO'), 1, 0, 'C', 1);
            $this->Cell(45, 10, utf8_decode('FECHA Y HORA'), 1, 1, 'C', 1);

         }
      }

      // Pie de página
      function Footer()
      {
         $this->SetY(-15); // Posición: a 1,5 cm del final
         $this->SetFont('Arial', 'I', 8); //tipo fuente, negrita(B-I-U-BIU), tamañoTexto
         $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C'); //pie de pagina(numero de pagina)

         $this->SetY(-15); // Posición: a 1,5 cm del final
         $this->SetFont('Arial', 'I', 8); //tipo fuente, cursiva, tamañoTexto
         $hoy = date('d/m/Y');
         $this->Cell(355, 10, utf8_decode($hoy), 0, 0, 'C'); // pie de pagina(fecha de pagina)
      }
   }
   include 'conexion_bd.php';

    $pdf = new PDF();
    $pdf->AddPage();
    $pdf->AliasNbPages();

    $i = 0;
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetDrawColor(163, 163, 163);

    $consulta_reporte_alquiler = $conn->query("SELECT
        IDPERSONA,
        PER_NOMBRES,
        PER_APELLIDOS,
        PER_CORREO,
        PER_DOCUMENTO,
        PER_QR,
        PER_TELEFONO,
        PER_PAIS,
        LOG_PUNTO,
        LOG_FECHORA,
        TIMESTAMPDIFF(
            SECOND,
            (
                SELECT MAX(LOG_FECHORA)
                FROM LOG_USERS
                WHERE LOG_FECHORA < lu.LOG_FECHORA
                AND LOG_IDUSER = lu.LOG_IDUSER
            ),
            LOG_FECHORA
        ) AS TIEMPO_REGISTROS
    FROM
        LOG_USERS lu
    INNER JOIN
        PERSONAL p ON lu.LOG_IDUSER = p.PER_CORREO
    WHERE
        lu.LOG_IDUSER='$userid'
        AND PER_ESTADO = 'ACTIVO'
        AND TIME(LOG_FECHORA) BETWEEN '06:55:00' AND '18:00:00'
    ORDER BY LOG_FECHORA");

    $total_horas_seconds = 0; // Inicializamos el contador de segundos
    $prev_date = null; // Variable para almacenar la fecha del registro anterior
    $ingreso_time = null; // Variable para almacenar la hora de ingreso
    $salida_time = null; // Variable para almacenar la hora de salida

    while ($datos_reporte = $consulta_reporte_alquiler->fetch_object()) {
        $log_punto = strtoupper($datos_reporte->LOG_PUNTO);
        if ($log_punto === 'INGRESO' && $ingreso_time === null) {
            $ingreso_time = strtotime($datos_reporte->LOG_FECHORA);
        } elseif ($log_punto === 'SALIDA' && $ingreso_time !== null) {
            $salida_time = strtotime($datos_reporte->LOG_FECHORA);
            $total_horas_seconds += $salida_time - $ingreso_time;
            $ingreso_time = null;
            $salida_time = null;
        }

        $i = $i + 1;
        /* TABLA */
        $pdf->Cell(8, 10, utf8_decode($i), 1, 0, 'C', 0);
        $pdf->Cell(28, 10, utf8_decode($datos_reporte->PER_DOCUMENTO), 1, 0, 'C', 0);
        $pdf->Cell(85, 10, utf8_decode($datos_reporte->PER_NOMBRES . " " . $datos_reporte->PER_APELLIDOS), 1, 0, 'C', 0);
        $pdf->Cell(25, 10, utf8_decode($datos_reporte->LOG_PUNTO), 1, 0, 'C', 0);
        $pdf->Cell(45, 10, utf8_decode($datos_reporte->LOG_FECHORA), 1, 1, 'C', 0);
    }

    $total_horas = sprintf(
        "%02d:%02d:%02d",
        floor($total_horas_seconds / 3600),
        floor(($total_horas_seconds % 3600) / 60),
        $total_horas_seconds % 60
    );

    $pdf->SetTextColor(255, 255, 255);
    $pdf->Ln(1);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(50, 10, utf8_decode('TOTAL HORAS'), 1, 0, 'C', 1);

    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(30, 10, utf8_decode($total_horas), 0, 1, 'C', 0);

    $pdf->Output('Report_' . $userid . '.pdf', 'I');
}
<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require('fpdf.php');

    // Obtén los datos del formulario
    $placa = $_POST['placa'];
    $telefono = $_POST['telefono'];
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $fecha_salida = $_POST['fecha_salida'];
    $tipo_vehiculo = $_POST['tipo_vehiculo'];
    $observaciones = $_POST['observaciones'];

    class PDF extends FPDF
    {
        // Cabecera de página
        function Header()
        {
            global $placa, $telefono, $fecha_ingreso, $fecha_salida, $tipo_vehiculo, $observaciones;

            $this->Image('Logo.png', 88, 10, 10); //logo de la empresa,moverDerecha,moverAbajo,tamañoIMG
            $this->Image('LogoD.jpg', 3, 10, 7); //logo de la empresa,moverDerecha,moverAbajo,tamañoIMG

            $this->SetY($this->GetY() -5);
            $this->SetX($this->GetX() -5);
            $this->SetTextColor(0, 0, 235); //color   
            $this->SetFont('Arial', 'B', 15); // Tipo de fuente, estilo (B = bold), tamaño
            $this->Cell(0, 0, 'PAGO PARQUEADERO', 0, 1, 'C'); // Texto centrado
            $this->Ln(4); // Salto de línea

            $this->SetTextColor(0, 0, 0); //color   
            $this->SetFont('Arial', '', 10);
            $this->Cell(0, 5, 'PLACA: ' . $placa . ' | ' . 'TELEFONO: ' . $telefono, 0, 1, 'C');
            $this->Cell(0, 5, 'FECHA DE INGRESO ' . ' | ' . 'FECHA DE SALIDA ', 0, 1, 'C');
            $this->Cell(0, 5, $fecha_ingreso . ' | ' . $fecha_salida, 0, 1, 'C');

            $this->Ln(10); // Salto de línea
        }

        // Pie de página
        function Footer()
        {
        }
    }

    $pdf = new PDF();
    $pdf->AliasNbPages(); // Necesario para contar el número total de páginas
    $pdf->AddPage('L', array(27, 100)); /* aqui entran dos parametros (horientazion,tamaño)V->portrait H->landscape tamaño (A3.A4.A5.letter.legal) */

    $pdf->Output('Pago_' . $placa . '.pdf', 'D'); // Nombre de descarga, visor (I = visualizar - D = descargar)
}
?>

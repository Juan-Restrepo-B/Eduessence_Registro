<?php
require('fpdf.php');

class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        if (isset($_GET['IDSEGSALAS'])) {
            $idPunto = $_GET['IDSEGSALAS'];

            include 'conexion_bd.php';

            $consulta_info = $conn->query("SELECT * FROM SEGUIMIENTO_SALAS WHERE IDSEGSALAS = $idPunto");
            $dato_info = $consulta_info->fetch_object();

            // Generar archivo temporal desde el blob SEG_QR
            $tempQR = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
            file_put_contents($tempQR, $dato_info->SEG_QR);

            // Imágenes y estilos
            $this->Image($tempQR, 4, 45, 200); // QR desde BLOB
            $this->SetFont('Arial', 'B', 19);
            $this->Cell(45);

            $this->SetTextColor(0, 0, 235);
            $this->Cell(18);
            $this->SetFont('Arial', 'B', 55);
            $this->Cell(60, 35, utf8_decode($dato_info->SEG_PUNTO), 0, 1, 'C', 0);
            $this->SetFont('Arial', 'B', 20);
            $this->Cell( 190, 3, utf8_decode($dato_info->SEG_DESCRIPCION), 0, 1, 'C', 0);
            $this->Ln(7);
            $this->Image('UNIVERSIDAD-NORTE.jpeg', 75,  252, 50);
            $this->Image('Logo.jpeg', 20, 240, 40); // Logo superior
            $this->Image('SIPEHA.png', 140, 240, 50);

            // Eliminar archivo temporal después de usarlo
            unlink($tempQR);
        }
    }

    // Pie de página (opcional, puedes descomentar si lo necesitas)
    /*
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
    */
}

$pdf = new PDF();
$pdf->AddPage(); // orientación y tamaño (por defecto: portrait y A4)
$pdf->AliasNbPages();
$pdf->Output('Report.pdf', 'I'); // 'I' = visualizar, 'D' = forzar descarga
?>
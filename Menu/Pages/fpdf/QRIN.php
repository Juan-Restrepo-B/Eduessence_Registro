<?php
require('fpdf.php');

class PDF extends FPDF
{
   private $qrTempFile;

   // Cabecera de página
   function Header()
   {
      if (isset($_GET['IDPUNTO'])) {
         $idPunto = $_GET['IDPUNTO'];

         include 'conexion_bd.php';

         $consulta_info = $conn->query("SELECT * FROM ENTRADA_SALIDA WHERE IDPUNTO = $idPunto");
         $dato_info = $consulta_info->fetch_object();

         // Crear archivo temporal para el QR
         if (!empty($dato_info->INOUT_QR)) {
            $this->qrTempFile = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
            file_put_contents($this->qrTempFile, $dato_info->INOUT_QR);

            // Insertar la imagen QR temporal
            $this->Image($this->qrTempFile, 4, 45, 200);
         }

         // Logos fijos
         $this->Image('Logo.jpeg', 10, 10, 40);
         $this->SetFont('Arial', 'B', 19);
         $this->Cell(45);

         // Título
         $this->SetTextColor(0, 0, 235);
         $this->Cell(18);
         $this->SetFont('Arial', 'B', 60);
         $this->Cell(100, 35, utf8_decode($dato_info->INOUT_PUNTO), 0, 1, 'C', 0);
         $this->Ln(7);

         $this->Image('UNIVERSIDAD-NORTE.jpeg', 15, 252, 80);
         $this->Image('SIPEHA.png', 140, 240, 50);
      }
   }

   // Pie de página
   function Footer()
   {
      // Puedes implementar si lo necesitas
   }

   // Limpiar archivo temporal si existe
   function __destruct()
   {
      if (!empty($this->qrTempFile) && file_exists($this->qrTempFile)) {
         unlink($this->qrTempFile);
      }
   }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->AliasNbPages();
$pdf->Output('Report.pdf', 'I');

<?php
require('fpdf.php');

class PDF extends FPDF
{
   function Header()
   {
      if (isset($_GET['IDPERSONA'], $_GET['CATEGORIA'])) {
         $idper = $_GET['IDPERSONA'];
         $idcat = $_GET['CATEGORIA'];

         include 'conexion_bd.php';

         $this->Ln(2); // Salto de línea

         $consulta_info = $conn->query("SELECT * FROM PERSONAL WHERE IDPERSONA = $idper");
         $dato_info = $consulta_info->fetch_object();

         $result5 = mysqli_query($conn, "SELECT * FROM CATEGORIA WHERE IDCATEG = '$idcat'");
         $rowCategoria = mysqli_fetch_assoc($result5);

         $consulta_info2 = $conn->query("SELECT * FROM CATEGORIA WHERE IDCATEG = $idcat");
         $dato_info2 = $consulta_info2->fetch_object();

         // Crear archivo temporal con el contenido del QR (BLOB)
         $tempQR = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
         file_put_contents($tempQR, $dato_info->PER_QR);

         // Mostrar el QR desde el archivo temporal
         $this->Image($tempQR, 1, 7, 15);

         // Eliminar el archivo temporal
         unlink($tempQR);

         // Datos del personal
         $this->SetY($this->GetY() - 7);
         $this->SetX($this->GetX() - 7);
         $this->SetFont('Arial', 'B', 10);
         $this->SetTextColor(0, 0, 235);
         $this->Cell(0, 0, utf8_decode($dato_info2->CATEGNAME), 0, 0, 'L', 0);
         $this->Ln(5);

         $this->SetTextColor(0, 0, 0);

         $this->Cell(5);
         $this->SetFont('Arial', 'B', 8);
         $this->Cell(0, 0, utf8_decode($dato_info->PER_NOMBRES), 0, 0, 'L', 0);
         $this->Ln(4);

         $this->Cell(5);
         $this->SetFont('Arial', 'B', 8);
         $this->Cell(0, 0, utf8_decode($dato_info->PER_APELLIDOS), 0, 0, 'L', 0);
         $this->Ln(4);

         $this->Cell(5);
         $this->SetFont('Arial', 'B', 8);
         $this->Cell(0, 0, utf8_decode($dato_info->PER_PAIS), 0, 0, 'L', 0);
         $this->Ln(4);

         $this->Cell(1);
         $this->SetX($this->GetX() - 5);
         $this->SetFont('Arial', 'B', 7);
         $this->Cell(0, 2, utf8_decode($dato_info->IDPERSONA), 0, 0, 'L', 0);
      }
   }

   function Footer()
   {
      // Puedes implementar pie de página si es necesario
   }
}

include 'conexion_bd.php';
$idper = $_GET['IDPERSONA'];

$consulta_info = $conn->query("SELECT * FROM PERSONAL WHERE IDPERSONA = $idper");
$dato_info = $consulta_info->fetch_object();

$pdf = new PDF();
$pdf->AddPage('L', array(27, 100));
$pdf->AliasNbPages();
$pdf->Output('Escarapela_' . $dato_info->IDPERSONA . '.pdf', 'I');
?>
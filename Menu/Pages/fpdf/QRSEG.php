<?php


require('fpdf.php');


class PDF extends FPDF
{
   
   // Cabecera de página
   function Header()
   {
      if (isset($_GET['IDSEGSALAS'])) {
         $idPunto = $_GET['IDSEGSALAS'];

      include 'conexion_bd.php'; //llamamos a la conexion BD
      
      
      $consulta_info = $conn->query("SELECT * FROM SEGUIMIENTO_SALAS WHERE IDSEGSALAS = $idPunto"); //traemos datos de la empresa desde BD WHERE IDPUNTO='$idpun'
      $dato_info = $consulta_info->fetch_object();
      $this->Image('../qr_codes/seguimiento-salas/' . $dato_info->SEG_QR, 4, 45, 200); //logo de la empresa,moverDerecha,moverAbajo,tamañoIMG
      $this->Image('Logo.png', 10, 10, 50); //logo de la empresa,moverDerecha,moverAbajo,tamañoIMG
      $this->SetFont('Arial', 'B', 19); //tipo fuente, negrita(B-I-U-BIU), tamañoTexto
      $this->Cell(45); // Movernos a la derecha



      /* TITULO DE LA TABLA */
      //color
      $this->SetTextColor(0, 0, 235);
      $this->Cell(18); // mover a la derecha
      $this->SetFont('Arial', 'B', 55);
      $this->Cell(100, 35, utf8_decode($dato_info->SEG_PUNTO), 0, 1, 'C', 0);
      $this->Ln(7);
      $this->Image('LogoA.png', 5, 232, 90); //logo de la empresa,moverDerecha,moverAbajo,tamañoIMG
      $this->Image('LogoS.png', 140, 240, 50); //logo de la empresa,moverDerecha,moverAbajo,tamañoIMG

      
   }}
   // Pie de página
   function Footer()
   {
      //$this->SetY(-15); // Posición: a 1,5 cm del final
      //$this->SetFont('Arial', 'I', 8); //tipo fuente, negrita(B-I-U-BIU), tamañoTexto
      //$this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C'); //pie de pagina(numero de pagina)

      //$this->SetY(-15); // Posición: a 1,5 cm del final
      //$this->SetFont('Arial', 'I', 8); //tipo fuente, cursiva, tamañoTexto
      //$hoy = date('d/m/Y');
      //$this->Cell(355, 10, utf8_decode($hoy), 0, 0, 'C'); // pie de pagina(fecha de pagina)
   }

}

$pdf = new PDF();
$pdf->AddPage(); /* aqui entran dos parametros (horientazion,tamaño)V->portrait H->landscape tamaño (A3.A4.A5.letter.legal) */
$pdf->AliasNbPages(); //muestra la pagina / y total de paginas

$pdf->Output('Report.pdf', 'I'); //nombreDescarga, Visor(I->visualizar - D->descargar)

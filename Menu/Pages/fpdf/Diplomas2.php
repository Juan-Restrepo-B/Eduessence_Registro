<?php

require('./fpdf.php');

class PDF extends FPDF
{
   function Header()
   {
      $this->Image('DIPLOMAS2.png', 0, 0, 297); //logo de la empresa,moverDerecha,moverAbajo,tamañoIMG
      $this->Image('qr.png', 10.5, 178, 28); //logo de la empresa,moverDerecha,moverAbajo,tamañoIMG
      $this->SetFont('Arial', 'B', 29); //tipo fuente, negrita(B-I-U-BIU), tamañoTexto
      $this->Ln(72); // Salto de línea
      $this->Cell(83); // Movernos a la derecha
      $this->SetTextColor(0, 0, 0); //color
      //creamos una celda o fila
      $this->Cell(100, 0, utf8_decode('ALEJANDRO GUILLERMO DEL GORDO '), 0, 1, 'C', 0); // AnchoCelda,AltoCelda,titulo,borde(1-0),saltoLinea(1-0),posicion(L-C-R),ColorFondo(1-0)
      $this->Ln(3); // Salto de línea
      $this->SetTextColor(103); //color

      /* ID */
      $this->Cell(115);  // mover a la derecha
      $this->SetFont('Arial', 'B', 15);
      $this->Cell(85, 10, utf8_decode("ID: 1000161351"), 0, 0, '', 0);
      $this->Ln(10);

   }

   // Pie de página
   function Footer(){}
}

$pdf = new PDF();
$pdf->AddPage("landscape"); /* aqui entran dos para parametros (horientazion,tamaño)V->portrait H->landscape tamaño (A3.A4.A5.letter.legal) */
$pdf->AliasNbPages(); //muestra la pagina / y total de paginas

$pdf->Output('Prueba7-2.pdf', 'I');//nombreDescarga, Visor(I->visualizar - D->descargar)

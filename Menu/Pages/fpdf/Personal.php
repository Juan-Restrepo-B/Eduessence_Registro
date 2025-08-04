<?php

   require('fpdf.php');

   class PDF extends FPDF
   {
      
      // Cabecera de página
      function Header()
      {
         
         if (isset($_GET['IDPERSONA'], $_GET['CATEGORIA'])) {
            $idper = $_GET['IDPERSONA'];
            $idcat = $_GET['CATEGORIA'];
         include 'conexion_bd.php'; //llamamos a la conexion BD
         $this->Ln(2); // Salto de línea

         $consulta_info = $conn->query("SELECT * FROM PERSONAL WHERE IDPERSONA = $idper"); //traemos datos de la empresa desde BD
         $dato_info = $consulta_info->fetch_object();

         $result5 = mysqli_query($conn, "SELECT * FROM CATEGORIA WHERE IDCATEG = '$idcat'");
         $rowCategoria = mysqli_fetch_assoc($result5);
         $cat = $rowCategoria['CATEGNAME'];

         $consulta_info2 = $conn->query("SELECT * FROM CATEGORIA WHERE IDCATEG = $idcat"); //traemos datos de la empresa desde BD
         $dato_info2 = $consulta_info2->fetch_object();
         $this->Image('../qr_codes/personal/' . $dato_info->PER_QR, 1, 7, 15); //logo de la empresa,moverDerecha,moverAbajo,tamañoIMG

         //creamos una celda o fila
         $this->SetY($this->GetY() -7);
         $this->SetX($this->GetX() -7);
         $this->SetFont('Arial', 'B', 10); //tipo fuente, negrita(B-I-U-BIU), tamañoTexto
         $this->SetTextColor(0, 0, 235); //color
         $this->Cell(0, 0, utf8_decode($dato_info2->CATEGNAME), 0, 0, 'L', 0); // AnchoCelda,AltoCelda,titulo,borde(1-0),saltoLinea(1-0),posicion(L-C-R),ColorFondo(1-0)
         $this->Ln(5); // Salto de línea

         /* Nombre */
         $this->SetTextColor(0, 0, 0); //color
         $this->Cell(5); // mover a la derecha
         $this->SetFont('Arial', 'B', 8);
         $this->Cell(0, 0, utf8_decode($dato_info->PER_NOMBRES), 0, 0, 'L', 0);
         $this->Ln(4);

         /* Apellido */
         $this->Cell(5); // mover a la derecha
         $this->SetFont('Arial', 'B', 8);
         $this->Cell(0, 0, utf8_decode($dato_info->PER_APELLIDOS), 0, 0, 'L', 0);
         $this->Ln(4);

         /* Pais */
         $this->Cell(5); // mover a la derecha
         $this->SetFont('Arial', 'B', 8);
         $this->Cell(0, 0, utf8_decode($dato_info->PER_PAIS), 0, 0, 'L', 0);
         $this->Ln(4);

         /*ID*/
         $this->Cell(1); // mover a la derecha
         $this->SetX($this->GetX() -5);
         $this->SetFont('Arial', 'B', 7);
         $this->Cell(0, 2, utf8_decode($dato_info->IDPERSONA), 0, 0, 'L', 0);
         
      }}

      // Pie de página
      function Footer()
      {
         
      }
   }

   include 'conexion_bd.php'; //llamamos a la conexion BD
   $idper = $_GET['IDPERSONA'];
 
   $consulta_info = $conn->query("SELECT * FROM PERSONAL WHERE IDPERSONA = $idper"); //traemos datos de la empresa desde BD
   $dato_info = $consulta_info->fetch_object();
   
   $pdf = new PDF();
   $pdf->AddPage('L', array(27, 100)); /* aqui entran dos parametros (horientazion,tamaño)V->portrait H->landscape tamaño (A3.A4.A5.letter.legal) */
   $pdf->AliasNbPages(); //muestra la pagina / y total de paginas

   $pdf->Output('Escarapela_'.$dato_info->IDPERSONA.'.pdf', 'I'); //nombreDescarga, Visor(I->visualizar - D->descargar)

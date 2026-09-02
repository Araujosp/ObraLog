<?php

require_once "fpdf19/fpdf.php";


$pdf = new FPDF();
$pdf->AddPage();

//Imagem logo
$pdf->Image("img/logoObraLog2.png", 160, 10, 35); //X, Y, largura

//titulo
$pdf->SetFont("Arial", "B", 18);
$pdf->Cell(0, 10, "RELATORIO DE ENTREGAS", 0, 1, "C"); //largura, altura, texto, borda, quebra_de_linha, alinhamento
            //0 usa a largura inteira         esse 1 quebra linha  C = center
//subtitulo
$pdf->setFont("Arial", "", 11);
$pdf->Cell(0, 8, "Controle de Materiais de Entrega", 0, 1, "C");

//espaçamento
$pdf->Ln(10);

//conteudo
$pdf->setFont("Arial", "", 12);





$arquivo = "relatorio-gerado.pdf";
$pdf->Output($arquivo, "I");

?>

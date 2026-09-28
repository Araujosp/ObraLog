<?php
/* =====================================================================
   ObraLog — relatorio.php
   Gera o PDF de UMA entrega (?id=). So pode gerar quem participa dela:
   a loja que criou ou o motorista que aceitou.
   ===================================================================== */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/conexao.php';

exigirLogin();
$usuario = usuarioLogado();

$idEntrega = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT  en.*,
             COALESCE(l.nome_fantasia, ul.nome) AS nome_loja,
             l.cnpj                              AS cnpj_loja,
             l.telefone                          AS telefone_loja,
             c.nome                              AS nome_cliente,
             c.cpf                                AS cpf_cliente,
             c.telefone                          AS telefone_cliente,
             c.endereco                          AS endereco_cliente,
             um.nome                             AS nome_motorista,
             m.cnh                               AS cnh_motorista,
             m.telefone                          AS telefone_motorista,
             v.placa                             AS placa_veiculo,
             v.modelo                            AS modelo_veiculo
     FROM    entrega en
     JOIN    loja      l  ON l.id_usuario = en.id_loja
     JOIN    usuario   ul ON ul.id_usuario = en.id_loja
     JOIN    cliente   c  ON c.id_cliente = en.id_cliente
     LEFT JOIN motorista m  ON m.id_usuario = en.id_motorista
     LEFT JOIN usuario   um ON um.id_usuario = en.id_motorista
     LEFT JOIN veiculo   v  ON v.id_veiculo = m.id_veiculo
     WHERE   en.id_entrega = ?"
);
$stmt->execute([$idEntrega]);
$entrega = $stmt->fetch();

if (!$entrega) {
    http_response_code(404);
    exit('Entrega não encontrada.');
}

if ($entrega['id_motorista'] === null) {
    exit('Esta entrega ainda não foi aceita por um entregador — o PDF só é gerado depois do aceite.');
}

// so a loja dona ou o motorista que aceitou podem gerar o PDF
$ehALojaDona     = $usuario['tipo_usuario'] === 'loja'      && (int) $entrega['id_loja']      === (int) $usuario['id_usuario'];
$ehOMotoristaCerto = $usuario['tipo_usuario'] === 'motorista' && (int) $entrega['id_motorista'] === (int) $usuario['id_usuario'];

if (!$ehALojaDona && !$ehOMotoristaCerto) {
    http_response_code(403);
    exit('Você não tem permissão para ver o PDF desta entrega.');
}

require_once __DIR__ . '/fpdf19/fpdf.php';

$dataSolicitacao = date('d/m/Y H:i', strtotime($entrega['data_solicitacao']));
$dataAceite       = $entrega['data_entrega'] ? date('d/m/Y H:i', strtotime($entrega['data_entrega'])) : '-';

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetMargins(15, 12, 15);

// ---------- cabecalho ----------
$pdf->Image('img/logoObraLog2.png', 160, 10, 35); // X, Y, largura

$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 10, 'RELATORIO DE ENTREGA', 0, 1, 'C');

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, 'Entrega #' . $entrega['id_entrega'], 0, 1, 'C');

$pdf->Ln(6);
$pdf->SetDrawColor(230, 230, 230);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(6);

// ---------- helper para desenhar uma secao "rotulo: valor" ----------
function linhaCampo(FPDF $pdf, string $rotulo, string $valor): void
{
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(45, 7, $rotulo, 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(0, 7, $valor, 0, 'L');
}

function tituloSecao(FPDF $pdf, string $texto): void
{
    $pdf->Ln(3);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(255, 107, 0); // laranja da marca
    $pdf->Cell(0, 8, $texto, 0, 1);
    $pdf->SetTextColor(0, 0, 0);
}

// ---------- loja ----------
tituloSecao($pdf, 'LOJA');
linhaCampo($pdf, 'Nome:', $entrega['nome_loja']);
linhaCampo($pdf, 'CNPJ:', formatarCnpj($entrega['cnpj_loja']));
linhaCampo($pdf, 'Telefone:', formatarTelefone($entrega['telefone_loja']));

// ---------- cliente ----------
tituloSecao($pdf, 'CLIENTE (DESTINATARIO)');
linhaCampo($pdf, 'Nome:', $entrega['nome_cliente']);
linhaCampo($pdf, 'CPF:', formatarCpf($entrega['cpf_cliente']));
linhaCampo($pdf, 'Telefone:', formatarTelefone($entrega['telefone_cliente']));
linhaCampo($pdf, 'Endereco:', $entrega['endereco_cliente']);

// ---------- rota ----------
tituloSecao($pdf, 'ROTA');
linhaCampo($pdf, 'Origem:', $entrega['endereco_origem']);
linhaCampo($pdf, 'Destino:', $entrega['endereco_entrega']);
linhaCampo($pdf, 'Solicitada em:', $dataSolicitacao);
linhaCampo($pdf, 'Aceita em:', $dataAceite);

// ---------- carga ----------
tituloSecao($pdf, 'MATERIAIS TRANSPORTADOS');
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 7, $entrega['materiais']);

// ---------- motorista ----------
tituloSecao($pdf, 'ENTREGADOR RESPONSAVEL');
linhaCampo($pdf, 'Nome:', $entrega['nome_motorista']);
linhaCampo($pdf, 'CNH:', $entrega['cnh_motorista']);
linhaCampo($pdf, 'Telefone:', formatarTelefone($entrega['telefone_motorista']));
if ($entrega['placa_veiculo']) {
    linhaCampo($pdf, 'Veiculo:', $entrega['modelo_veiculo'] . ' - Placa ' . $entrega['placa_veiculo']);
}

// ---------- assinatura ----------
$pdf->Ln(18);
$yAssinatura = $pdf->GetY();
$pdf->Line(20, $yAssinatura, 95, $yAssinatura);
$pdf->Line(115, $yAssinatura, 190, $yAssinatura);

$pdf->SetFont('Arial', '', 9);
$pdf->SetXY(20, $yAssinatura + 2);
$pdf->Cell(75, 5, 'Assinatura do cliente', 0, 0, 'C');
$pdf->SetXY(115, $yAssinatura + 2);
$pdf->Cell(75, 5, 'Assinatura do entregador', 0, 0, 'C');

$pdf->Output('entrega-' . $entrega['id_entrega'] . '.pdf', 'I');

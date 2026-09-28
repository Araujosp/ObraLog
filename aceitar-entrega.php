<?php
/* =====================================================================
   ObraLog — aceitar-entrega.php
   Vincula a entrega ao motorista logado. O WHERE id_motorista IS NULL
   garante que, se dois motoristas clicarem "aceitar" ao mesmo tempo,
   so o primeiro UPDATE realmente vale — o segundo simplesmente nao
   afeta nenhuma linha (rowCount = 0).
   ===================================================================== */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/conexao.php';

$usuario = exigirTipo('motorista');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: entregas.php');
    exit;
}

$idEntrega = (int) ($_POST['id_entrega'] ?? 0);

$stmt = $pdo->prepare(
    'UPDATE entrega
        SET id_motorista = ?, data_entrega = NOW()
      WHERE id_entrega = ? AND id_motorista IS NULL'
);
$stmt->execute([$usuario['id_usuario'], $idEntrega]);

if ($stmt->rowCount() === 0) {
    $_SESSION['flash'] = ['mensagem' => 'Essa entrega já não está mais disponível.'];
    header('Location: entregas.php');
    exit;
}

// aceite confirmado: manda direto para o PDF da entrega
header('Location: relatorio.php?id=' . $idEntrega);
exit;

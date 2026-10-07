<?php
/* =====================================================================
   ObraLog — finalizar-entrega.php
   Marca como finalizada uma entrega que o motorista logado aceitou.
   O WHERE confere o dono (id_motorista) e que ainda nao foi finalizada,
   entao um motorista nunca consegue finalizar a entrega de outro.
   ===================================================================== */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/conexao.php';

$usuario = exigirTipo('motorista');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: minhas-entregas.php');
    exit;
}

$idEntrega = (int) ($_POST['id_entrega'] ?? 0);

$stmt = $pdo->prepare(
    'UPDATE entrega
        SET data_finalizacao = NOW()
      WHERE id_entrega = ? AND id_motorista = ? AND data_finalizacao IS NULL'
);
$stmt->execute([$idEntrega, $usuario['id_usuario']]);

if ($stmt->rowCount() === 0) {
    $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Não foi possível finalizar essa entrega.'];
} else {
    $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => 'Entrega finalizada com sucesso.'];
}

header('Location: minhas-entregas.php');
exit;

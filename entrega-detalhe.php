<?php
/* =====================================================================
   ObraLog — entrega-detalhe.php
   Mostra os dados completos de UMA entrega para o motorista conferir
   antes de aceitar.
   ===================================================================== */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/conexao.php';

$usuario = exigirTipo('motorista');

$idEntrega = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT  en.*,
             COALESCE(l.nome_fantasia, ul.nome) AS nome_loja,
             l.telefone                          AS telefone_loja,
             c.nome                              AS nome_cliente,
             c.cpf                                AS cpf_cliente,
             c.telefone                          AS telefone_cliente
     FROM    entrega en
     JOIN    loja    l  ON l.id_usuario = en.id_loja
     JOIN    usuario ul ON ul.id_usuario = en.id_loja
     JOIN    cliente c  ON c.id_cliente = en.id_cliente
     WHERE   en.id_entrega = ?"
);
$stmt->execute([$idEntrega]);
$entrega = $stmt->fetch();

if (!$entrega) {
    http_response_code(404);
    exit('Entrega não encontrada.');
}

// so pode ver o detalhe se a entrega estiver disponivel, ou se ja foi
// aceita por ESTE motorista — nao deixa um motorista bisbilhotar a
// carga que outro ja aceitou
$ehDoMotorista = (int) $entrega['id_motorista'] === (int) $usuario['id_usuario'];
if ($entrega['id_motorista'] !== null && !$ehDoMotorista) {
    http_response_code(403);
    exit('Esta entrega já foi aceita por outro entregador.');
}

function dataBr(?string $data): string
{
    return $data ? date('d/m/Y \à\s H:i', strtotime($data)) : '-';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrega #<?= (int) $entrega['id_entrega'] ?> · ObraLog</title>
    <link rel="icon" href="img/logoObraLog.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="topo">
    <div class="logo-topo">
        <img src="img/logoObraLog.png" alt="ObraLog">
        <span>Obra<b>Log</b></span>
    </div>
    <div class="usuario-atual">
        <div class="avatar"><?= e(iniciais($usuario['nome'])) ?></div>
        <div class="dados">
            <strong><?= e($usuario['nome']) ?></strong>
            <small>Entregador</small>
        </div>
        <a href="logout.php" class="botao-sair">Sair</a>
    </div>
</header>

<main class="conteudo conteudo-largo">

    <div class="cabecalho-pagina">
        <div>
            <h1>Entrega <span>#<?= (int) $entrega['id_entrega'] ?></span></h1>
            <p>Confira os dados antes de aceitar a carga.</p>
        </div>
        <a href="entregas.php" class="acao">&larr; Voltar para a lista</a>
    </div>

    <div class="grade-cartoes">
        <div class="cartao-info">
            <h3>Loja</h3>
            <p><?= e($entrega['nome_loja']) ?></p>
        </div>
        <div class="cartao-info">
            <h3>Data da solicitação</h3>
            <p><?= dataBr($entrega['data_solicitacao']) ?></p>
        </div>
        <div class="cartao-info">
            <h3>Endereço de origem</h3>
            <p><?= e($entrega['endereco_origem']) ?></p>
        </div>
        <div class="cartao-info">
            <h3>Endereço de destino</h3>
            <p><?= e($entrega['endereco_entrega']) ?></p>
        </div>
        <div class="cartao-info">
            <h3>Cliente</h3>
            <p><?= e($entrega['nome_cliente']) ?></p>
        </div>
        <div class="cartao-info">
            <h3>CPF do cliente</h3>
            <p><?= e(formatarCpf($entrega['cpf_cliente'])) ?></p>
        </div>
        <div class="cartao-info">
            <h3>Telefone do cliente</h3>
            <p><?= e($entrega['telefone_cliente']) ?></p>
        </div>
        <div class="cartao-info" style="grid-column: 1 / -1">
            <h3>Materiais</h3>
            <p style="font-weight:400"><?= nl2br(e($entrega['materiais'])) ?></p>
        </div>
    </div>

    <?php if (!$ehDoMotorista): ?>
        <form method="post" action="aceitar-entrega.php" style="margin-top:22px; max-width:300px">
            <input type="hidden" name="id_entrega" value="<?= (int) $entrega['id_entrega'] ?>">
            <button type="submit" class="botao botao-principal">Aceitar esta carga</button>
        </form>
    <?php else: ?>
        <div class="aviso sucesso visivel" style="margin-top:22px">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
            </svg>
            <span>
                Você já aceitou essa entrega em <?= dataBr($entrega['data_entrega']) ?>.
                <a href="relatorio.php?id=<?= (int) $entrega['id_entrega'] ?>" class="link">Ver PDF novamente</a>
            </span>
        </div>
    <?php endif; ?>

</main>
</body>
</html>

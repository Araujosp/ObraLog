<?php
/* =====================================================================
   ObraLog — minhas-entregas.php
   Visao do MOTORISTA: entregas que ELE aceitou e ainda nao finalizou
   (id_motorista = logado e data_finalizacao IS NULL).
   ===================================================================== */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/conexao.php';

$usuario = exigirTipo('motorista');

// mensagem deixada por aceitar-entrega.php / finalizar-entrega.php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$stmt = $pdo->prepare(
    "SELECT  en.id_entrega,
             en.data_entrega,
             en.endereco_entrega,
             COALESCE(l.nome_fantasia, u.nome) AS nome_loja
     FROM    entrega en
     JOIN    loja    l ON l.id_usuario = en.id_loja
     JOIN    usuario u ON u.id_usuario = en.id_loja
     WHERE   en.id_motorista = ?
       AND   en.data_finalizacao IS NULL
     ORDER BY en.data_entrega DESC"
);
$stmt->execute([$usuario['id_usuario']]);
$entregas = $stmt->fetchAll();

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
    <title>Minhas Entregas · ObraLog</title>
    <link rel="icon" href="img/logoObraLog.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- =================== BARRA SUPERIOR =================== -->
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

<!-- =================== CONTEÚDO =================== -->
<main class="conteudo conteudo-largo">

    <?php $paginaAtual = 'minhas'; require __DIR__ . '/includes/menu-motorista.php'; ?>

    <div class="cabecalho-pagina">
        <div>
            <h1>Minhas <span>Entregas</span></h1>
            <p><?= count($entregas) ?> entrega(s) em andamento</p>
        </div>
    </div>

    <?php if ($flash): ?>
        <?php $ehSucesso = ($flash['tipo'] ?? 'erro') === 'sucesso'; ?>
        <div class="aviso <?= $ehSucesso ? 'sucesso' : 'erro' ?> visivel" role="alert" style="margin-bottom:20px">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <?php if ($ehSucesso): ?>
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
                <?php else: ?>
                    <circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>
                <?php endif; ?>
            </svg>
            <span><?= e($flash['mensagem']) ?></span>
        </div>
    <?php endif; ?>

    <div class="quadro-tabela">
        <table class="tabela">
            <thead>
                <tr>
                    <th>Entrega</th>
                    <th>Loja</th>
                    <th>Aceita em</th>
                    <th>Destino</th>
                    <th>Status</th>
                    <th class="alinha-direita">Ações</th>
                </tr>
            </thead>

            <tbody>
            <?php if (!$entregas): ?>
                <tr>
                    <td colspan="6">
                        <div class="sem-registros">
                            <strong>Nenhuma entrega em andamento</strong>
                            <span>Aceite uma carga em Entregas disponíveis e ela aparece aqui.</span>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($entregas as $en): ?>
                <tr>
                    <td data-rotulo="Entrega">
                        <span class="codigo">#<?= (int) $en['id_entrega'] ?></span>
                    </td>

                    <td data-rotulo="Loja">
                        <span class="destaque"><?= e($en['nome_loja']) ?></span>
                    </td>

                    <td data-rotulo="Aceita em">
                        <?= dataBr($en['data_entrega']) ?>
                    </td>

                    <td data-rotulo="Destino">
                        <?= e($en['endereco_entrega']) ?>
                    </td>

                    <td data-rotulo="Status">
                        <span class="status-entrega">Em andamento</span>
                    </td>

                    <td data-rotulo="Ações" class="alinha-direita">
                        <div class="acoes">
                            <a class="acao" href="entrega-detalhe.php?id=<?= (int) $en['id_entrega'] ?>">Detalhes</a>
                            <a class="acao" href="relatorio.php?id=<?= (int) $en['id_entrega'] ?>" target="_blank" rel="noopener">PDF</a>

                            <form method="post" action="finalizar-entrega.php" onsubmit="return confirm('Tem certeza que deseja finalizar esta entrega?')">
                                <input type="hidden" name="id_entrega" value="<?= (int) $en['id_entrega'] ?>">
                                <button type="submit" class="acao finalizar">Finalizar entrega</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>
</body>
</html>

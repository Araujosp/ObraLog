<?php
/* =====================================================================
   ObraLog — entregas.php
   Visao do MOTORISTA: lista as entregas ainda sem motorista
   (id_motorista IS NULL = disponivel).
   ===================================================================== */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/conexao.php';

$usuario = exigirTipo('motorista');

$busca = trim($_GET['q'] ?? '');

// mensagem deixada pelo aceitar-entrega.php (ex.: carga ja foi aceita por outro)
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$sql = "SELECT  en.id_entrega,
                en.data_solicitacao,
                en.endereco_entrega,
                COALESCE(l.nome_fantasia, u.nome) AS nome_loja
        FROM    entrega en
        JOIN    loja    l ON l.id_usuario = en.id_loja
        JOIN    usuario u ON u.id_usuario = en.id_loja
        WHERE   en.id_motorista IS NULL";

$parametros = [];

if ($busca !== '') {
    $sql .= " AND (COALESCE(l.nome_fantasia, u.nome) LIKE ? OR en.id_entrega = ?)";
    $parametros[] = '%' . $busca . '%';
    $parametros[] = is_numeric($busca) ? (int) $busca : 0;
}

$sql .= ' ORDER BY en.data_solicitacao DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($parametros);
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
    <title>Entregas disponíveis · ObraLog</title>
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

    <div class="cabecalho-pagina">
        <div>
            <h1>Entregas <span>disponíveis</span></h1>
            <p><?= count($entregas) ?> carga(s) aguardando entregador</p>
        </div>

        <form class="busca" method="get">
            <div class="entrada">
                <span class="icone-campo">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                    </svg>
                </span>
                <input type="search" name="q" placeholder="Buscar por loja ou nº da entrega"
                       value="<?= e($busca) ?>">
            </div>
        </form>
    </div>

    <?php if ($flash): ?>
        <div class="aviso erro visivel" role="alert" style="margin-bottom:20px">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>
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
                    <th>Data do pedido</th>
                    <th>Destino</th>
                    <th class="alinha-direita">Ações</th>
                </tr>
            </thead>

            <tbody>
            <?php if (!$entregas): ?>
                <tr>
                    <td colspan="5">
                        <div class="sem-registros">
                            <strong>Nenhuma entrega disponível agora</strong>
                            <span>Assim que uma loja publicar uma carga, ela aparece aqui.</span>
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

                    <td data-rotulo="Data do pedido">
                        <?= dataBr($en['data_solicitacao']) ?>
                    </td>

                    <td data-rotulo="Destino">
                        <?= e($en['endereco_entrega']) ?>
                    </td>

                    <td data-rotulo="Ações" class="alinha-direita">
                        <div class="acoes">
                            <a class="acao" href="entrega-detalhe.php?id=<?= (int) $en['id_entrega'] ?>">Detalhes</a>

                            <form method="post" action="aceitar-entrega.php">
                                <input type="hidden" name="id_entrega" value="<?= (int) $en['id_entrega'] ?>">
                                <button type="submit" class="acao aceitar">Aceitar carga</button>
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

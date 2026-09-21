<?php

require_once __DIR__ . '/includes/sessao.php';
/* =====================================================================
   ObraLog — Entregas disponíveis (visão do transportador)
   ---------------------------------------------------------------------
   ESTE É UM MODELO. A conexão com o banco fica por sua conta.
   Abaixo está o trecho comentado mostrando exatamente onde plugar.
   ===================================================================== */

// require_once 'conexao.php';   // sua conexão PDO
//
// $sql = "SELECT  e.id,
//                 l.nome_fantasia            AS loja,
//                 e.criada_em                AS data,
//                 SUM(m.quantidade * m.preco_unitario) AS valor_total
//         FROM    entregas e
//         JOIN    lojas     l ON l.id = e.loja_id
//         JOIN    materiais m ON m.entrega_id = e.id
//         WHERE   e.status = 'disponivel'
//         GROUP BY e.id, l.nome_fantasia, e.criada_em
//         ORDER BY e.criada_em DESC";
//
// $entregas = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

/* --- dados de exemplo: apague quando ligar no banco ------------------ */
$entregas = [
    ['id' => 1042, 'loja' => 'Depósito São Jorge',      'data' => '2026-09-21 08:15:00', 'valor_total' => 18450.00],
    ['id' => 1041, 'loja' => 'Casa do Construtor ABC',  'data' => '2026-09-20 17:40:00', 'valor_total' => 7320.50],
    ['id' => 1039, 'loja' => 'Ferragens Ipiranga',      'data' => '2026-09-20 11:02:00', 'valor_total' => 2980.00],
    ['id' => 1036, 'loja' => 'Materiais Vila Guiomar',  'data' => '2026-09-19 14:27:00', 'valor_total' => 43110.90],
    ['id' => 1034, 'loja' => 'Depósito São Jorge',      'data' => '2026-09-19 09:10:00', 'valor_total' => 1260.00],
    ['id' => 1031, 'loja' => 'Cimento & Cia',           'data' => '2026-09-18 16:55:00', 'valor_total' => 26740.00],
];
/* -------------------------------------------------------------------- */

function moeda($v)  { return 'R$ ' . number_format((float)$v, 2, ',', '.'); }
function data_br($d) { return date('d/m/Y \à\s H:i', strtotime($d)); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entregas disponíveis · ObraLog</title>
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
        <div class="avatar">JM</div>
        <div class="dados">
            <strong>João Martins</strong>
            <small>Transportador · Placa QNZ-4B12</small>
        </div>
        <a href="minhas-entregas.php" class="botao-sair">Minhas entregas</a>
    </div>
</header>

<!-- =================== CONTEÚDO =================== -->
<main class="conteudo conteudo-largo">

    <div class="cabecalho-pagina">
        <div>
            <h1>Entregas <span>disponíveis</span></h1>
            <p><?= count($entregas) ?> carga(s) aguardando transportador</p>
        </div>

        <!-- filtro opcional: mande por GET e trate no SQL -->
        <form class="busca" method="get">
            <div class="entrada">
                <span class="icone-campo">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                    </svg>
                </span>
                <input type="search" name="q" placeholder="Buscar por loja ou nº da entrega"
                       value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            </div>
        </form>
    </div>

    <div class="quadro-tabela">
        <table class="tabela">
            <thead>
                <tr>
                    <th>Entrega</th>
                    <th>Loja</th>
                    <th>Data do pedido</th>
                    <th class="alinha-direita">Valor dos materiais</th>
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
                <?php foreach ($entregas as $e): ?>
                <tr>
                    <td data-rotulo="Entrega">
                        <span class="codigo">#<?= htmlspecialchars($e['id']) ?></span>
                    </td>

                    <td data-rotulo="Loja">
                        <span class="destaque"><?= htmlspecialchars($e['loja']) ?></span>
                    </td>

                    <td data-rotulo="Data do pedido">
                        <?= data_br($e['data']) ?>
                    </td>

                    <td data-rotulo="Valor dos materiais" class="alinha-direita">
                        <span class="valor"><?= moeda($e['valor_total']) ?></span>
                    </td>

                    <td data-rotulo="Ações" class="alinha-direita">
                        <div class="acoes">
                            <a class="acao" href="entrega-detalhe.php?id=<?= (int)$e['id'] ?>">Detalhes</a>

                            <!-- POST para evitar aceite por link; trate o CSRF no seu backend -->
                            <form method="post" action="aceitar-entrega.php">
                                <input type="hidden" name="entrega_id" value="<?= (int)$e['id'] ?>">
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
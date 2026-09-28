<?php
/* =====================================================================
   ObraLog — cadastrar-entrega.php
   Visao da LOJA: cria uma nova entrega (origem, destino, cliente + CPF
   e os materiais). A entrega nasce com id_motorista NULL, ou seja,
   ja fica "disponivel" para os entregadores em entregas.php.
   ===================================================================== */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/conexao.php';

$usuario = exigirTipo('loja');

// endereco cadastrado da loja, so para sugerir como origem
$stmt = $pdo->prepare('SELECT rua, numero, complemento, cidade, estado FROM loja WHERE id_usuario = ?');
$stmt->execute([$usuario['id_usuario']]);
$loja = $stmt->fetch();
$enderecoLoja = trim(sprintf(
    '%s, %s%s - %s/%s',
    $loja['rua'], $loja['numero'],
    $loja['complemento'] ? ' (' . $loja['complemento'] . ')' : '',
    $loja['cidade'], $loja['estado']
));

/* ===================================================================
   PROCESSA O POST
   =================================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clienteNome     = trim($_POST['cliente_nome'] ?? '');
    $clienteCpf      = somenteDigitos($_POST['cliente_cpf'] ?? '');
    $clienteTelefone = somenteDigitos($_POST['cliente_telefone'] ?? '');
    $clienteEndereco = trim($_POST['cliente_endereco'] ?? '');
    $enderecoOrigem  = trim($_POST['endereco_origem'] ?? '');
    $enderecoEntrega = trim($_POST['endereco_entrega'] ?? '');
    $materiais       = trim($_POST['materiais'] ?? '');

    $erros = [];

    if (mb_strlen($clienteNome) < 3)         { $erros['cliente_nome'] = 'Informe o nome do cliente.'; }
    if (!validarCpf($clienteCpf))            { $erros['cliente_cpf'] = 'CPF inválido.'; }
    if (strlen($clienteTelefone) < 10)       { $erros['cliente_telefone'] = 'Informe o telefone com DDD.'; }
    if ($clienteEndereco === '')             { $erros['cliente_endereco'] = 'Informe o endereço do cliente.'; }
    if ($enderecoOrigem === '')              { $erros['endereco_origem'] = 'Informe o endereço de origem.'; }
    if ($enderecoEntrega === '')             { $erros['endereco_entrega'] = 'Informe o endereço de destino.'; }
    if (mb_strlen($materiais) < 3)           { $erros['materiais'] = 'Descreva os materiais da carga.'; }

    if ($erros) {
        responderFormulario(false, 'Confira os campos destacados.', $erros, '', 'cadastrar-entrega.php');
    }

    try {
        $pdo->beginTransaction();

        // cliente: se o CPF ja existe, reaproveita o cadastro (e atualiza
        // os dados) em vez de tentar duplicar — cpf e UNIQUE no banco
        $stmt = $pdo->prepare('SELECT id_cliente FROM cliente WHERE cpf = ?');
        $stmt->execute([$clienteCpf]);
        $idCliente = $stmt->fetchColumn();

        if ($idCliente) {
            $stmt = $pdo->prepare('UPDATE cliente SET nome = ?, telefone = ?, endereco = ? WHERE id_cliente = ?');
            $stmt->execute([$clienteNome, $clienteTelefone, $clienteEndereco, $idCliente]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO cliente (nome, cpf, telefone, endereco) VALUES (?, ?, ?, ?)');
            $stmt->execute([$clienteNome, $clienteCpf, $clienteTelefone, $clienteEndereco]);
            $idCliente = (int) $pdo->lastInsertId();
        }

        $stmt = $pdo->prepare(
            'INSERT INTO entrega (id_loja, id_cliente, endereco_origem, endereco_entrega, materiais)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$usuario['id_usuario'], $idCliente, $enderecoOrigem, $enderecoEntrega, $materiais]);
        $idEntrega = (int) $pdo->lastInsertId();

        $pdo->commit();
    } catch (PDOException $erroBanco) {
        $pdo->rollBack();
        responderFormulario(false, 'Não foi possível salvar a entrega. Tente novamente.', [], '', 'cadastrar-entrega.php');
    }

    responderFormulario(true, 'Entrega #' . $idEntrega . ' criada e disponível para os entregadores.', [], 'cadastrar-entrega.php?criada=' . $idEntrega);
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$criada = isset($_GET['criada']) ? (int) $_GET['criada'] : null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nova entrega · ObraLog</title>
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
            <small>Loja</small>
        </div>
        <a href="logout.php" class="botao-sair">Sair</a>
    </div>
</header>

<main class="conteudo">

    <div class="cabecalho-pagina">
        <div>
            <h1>Nova <span>entrega</span></h1>
            <p>Preencha os dados e a carga já fica disponível para os entregadores.</p>
        </div>
    </div>

    <?php if ($criada): ?>
        <div class="aviso sucesso visivel" style="margin-bottom:20px">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
            </svg>
            <span>Entrega #<?= $criada ?> criada com sucesso e já está disponível para os entregadores.</span>
        </div>
    <?php endif; ?>

    <div class="aviso erro <?= $flash ? 'visivel' : '' ?>" id="aviso" role="alert">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>
        </svg>
        <span id="aviso-texto"><?= $flash ? e($flash['mensagem']) : '' ?></span>
    </div>

    <div class="cartao-largo">
        <h2>Dados da entrega</h2>
        <p class="subtitulo">Todos os campos são obrigatórios.</p>

        <form class="formulario" id="form-entrega" method="post" action="cadastrar-entrega.php" novalidate>

            <!-- ---------- endereços ---------- -->
            <div class="secao-form">
                <div class="titulo-secao">Rota</div>

                <div class="campo" data-campo="endereco_origem">
                    <label for="endereco_origem">Endereço de origem <span class="obrigatorio">*</span></label>
                    <div class="entrada">
                        <input type="text" id="endereco_origem" name="endereco_origem" style="padding-left:16px"
                               value="<?= e($flash['dados']['endereco_origem'] ?? $enderecoLoja) ?>">
                    </div>
                    <span class="mensagem-erro"></span>
                </div>

                <div class="campo" data-campo="endereco_entrega" style="margin-top:18px">
                    <label for="endereco_entrega">Endereço de destino <span class="obrigatorio">*</span></label>
                    <div class="entrada">
                        <input type="text" id="endereco_entrega" name="endereco_entrega" placeholder="Rua, número, bairro, cidade/UF"
                               style="padding-left:16px" value="<?= e($flash['dados']['endereco_entrega'] ?? '') ?>">
                    </div>
                    <span class="mensagem-erro"></span>
                </div>
            </div>

            <!-- ---------- cliente ---------- -->
            <div class="secao-form">
                <div class="titulo-secao">Dados do cliente</div>

                <div class="linha">
                    <div class="campo" data-campo="cliente_nome">
                        <label for="cliente_nome">Nome do cliente <span class="obrigatorio">*</span></label>
                        <div class="entrada">
                            <input type="text" id="cliente_nome" name="cliente_nome" style="padding-left:16px"
                                   value="<?= e($flash['dados']['cliente_nome'] ?? '') ?>">
                        </div>
                        <span class="mensagem-erro"></span>
                    </div>
                    <div class="campo" data-campo="cliente_cpf">
                        <label for="cliente_cpf">CPF <span class="obrigatorio">*</span></label>
                        <div class="entrada">
                            <input type="text" id="cliente_cpf" name="cliente_cpf" placeholder="000.000.000-00"
                                   inputmode="numeric" maxlength="14" style="padding-left:16px"
                                   value="<?= e($flash['dados']['cliente_cpf'] ?? '') ?>">
                        </div>
                        <span class="mensagem-erro"></span>
                    </div>
                </div>

                <div class="linha" style="margin-top:18px">
                    <div class="campo" data-campo="cliente_telefone">
                        <label for="cliente_telefone">Telefone <span class="obrigatorio">*</span></label>
                        <div class="entrada">
                            <input type="tel" id="cliente_telefone" name="cliente_telefone" placeholder="(00) 00000-0000"
                                   inputmode="numeric" maxlength="15" style="padding-left:16px"
                                   value="<?= e($flash['dados']['cliente_telefone'] ?? '') ?>">
                        </div>
                        <span class="mensagem-erro"></span>
                    </div>
                    <div class="campo" data-campo="cliente_endereco">
                        <label for="cliente_endereco">Endereço do cliente <span class="obrigatorio">*</span></label>
                        <div class="entrada">
                            <input type="text" id="cliente_endereco" name="cliente_endereco" style="padding-left:16px"
                                   value="<?= e($flash['dados']['cliente_endereco'] ?? '') ?>">
                        </div>
                        <span class="mensagem-erro"></span>
                    </div>
                </div>
            </div>

            <!-- ---------- carga ---------- -->
            <div class="secao-form">
                <div class="titulo-secao">Carga</div>

                <div class="campo" data-campo="materiais">
                    <label for="materiais">Materiais transportados <span class="obrigatorio">*</span></label>
                    <div class="entrada">
                        <textarea id="materiais" name="materiais"
                                  placeholder="Ex.: 50 sacos de cimento CP-II, 200 tijolos baianos..."><?= e($flash['dados']['materiais'] ?? '') ?></textarea>
                    </div>
                    <span class="mensagem-erro"></span>
                </div>
            </div>

            <button type="submit" class="botao botao-principal" id="botao-salvar" style="margin-top:6px">
                <span class="girando"></span>
                <span class="rotulo">Publicar entrega</span>
            </button>
        </form>
    </div>

</main>

<script src="script.js"></script>
<script>
    /* mascaras de CPF/telefone, so para esta pagina (nao mexe no script.js
       principal, que ja cuida de login/cadastro) */
    (function () {
        function digitos(v) { return v.replace(/\D/g, ''); }
        function mascarar(input, formatar, max) {
            if (!input) return;
            input.addEventListener('input', function () {
                input.value = formatar(digitos(input.value).slice(0, max));
            });
        }
        mascarar(document.getElementById('cliente_cpf'), function (v) {
            return v
                .replace(/^(\d{3})(\d)/, '$1.$2')
                .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
                .replace(/\.(\d{3})(\d)/, '.$1-$2');
        }, 11);
        mascarar(document.getElementById('cliente_telefone'), function (v) {
            if (v.length <= 10) { return v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3'); }
            return v.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
        }, 11);
    })();
</script>
</body>
</html>

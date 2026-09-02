<?php
// retorno do formulario quando o navegador esta sem JavaScript
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ObraLog</title>
    <meta name="theme-color" content="#0a0a0d">
    <link rel="icon" href="img/logoObraLog.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="pagina-auth">

    <!-- ============ painel da marca ============ -->
    <aside class="painel-marca">
        <div class="logo-topo">
            <img src="img/logoObraLog.png" alt="ObraLog">
            <span>Obra<b>Log</b></span>
        </div>

        <div class="marca-texto">
            <h1>Da loja ao canteiro,<br><em>sem perder a carga de vista.</em></h1>
            <p>
                Plataforma de logistica para materiais de construcao: lojas solicitam,
                motoristas entregam e todo mundo acompanha o percurso no mesmo lugar.
            </p>

            <ul class="lista-recursos">
                <li>
                    <span class="icone">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10 17h4V5H2v12h3"/><path d="M20 17h2v-3.34a4 4 0 0 0-1.17-2.83L19 9h-5v8h1"/>
                            <circle cx="7.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>
                        </svg>
                    </span>
                    Entregas registradas do pedido a assinatura
                </li>
                <li>
                    <span class="icone">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>
                        </svg>
                    </span>
                    Endereco de origem e destino em cada rota
                </li>
                <li>
                    <span class="icone">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </span>
                    Cadastro separado para lojas e motoristas
                </li>
            </ul>
        </div>

        <p class="rodape-marca">&copy; <?= date('Y') ?> ObraLog &middot; Todos os direitos reservados</p>
    </aside>

    <!-- ============ painel do formulario ============ -->
    <main class="painel-form">
        <section class="cartao">

            <div class="logo-mobile">
                <img src="img/logoObraLog.png" alt="ObraLog">
                <span>Obra<b>Log</b></span>
            </div>

            <header class="cartao-topo">
                <span class="selo">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    Acesso restrito
                </span>
                <h2>Entrar na conta</h2>
                <p>Use o e-mail cadastrado para acessar o painel.</p>
            </header>

            <div class="aviso erro <?= $flash ? 'visivel' : '' ?>" id="aviso" role="alert">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>
                </svg>
                <span id="aviso-texto"><?= $flash ? e($flash['mensagem']) : '' ?></span>
            </div>

            <form class="formulario" id="form-login" action="api/entrar.php" method="post" novalidate>

                <div class="campo" data-campo="email">
                    <label for="email">E-mail</label>
                    <div class="entrada">
                        <span class="icone-campo">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>
                            </svg>
                        </span>
                        <input type="email" id="email" name="email" placeholder="voce@empresa.com.br"
                               autocomplete="email" required
                               value="<?= e($flash['dados']['email'] ?? '') ?>">
                    </div>
                    <span class="mensagem-erro"></span>
                </div>

                <div class="campo" data-campo="senha">
                    <label for="senha">Senha</label>
                    <div class="entrada">
                        <span class="icone-campo">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </span>
                        <input type="password" id="senha" name="senha" placeholder="Sua senha"
                               autocomplete="current-password" required>
                        <button type="button" class="ver-senha" data-alvo="senha" aria-label="Mostrar senha">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <span class="mensagem-erro"></span>
                </div>

                <div class="linha-opcoes">
                    <label class="checkbox">
                        <input type="checkbox" name="lembrar" value="1">
                        <span class="marca">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m20 6-11 11-5-5"/>
                            </svg>
                        </span>
                        Lembrar de mim
                    </label>
                    <a href="#" class="link" id="link-recuperar">Esqueci minha senha</a>
                </div>

                <button type="submit" class="botao botao-principal" id="botao-entrar">
                    <span class="girando"></span>
                    <span class="rotulo">Entrar</span>
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                    </svg>
                </button>
            </form>

            <p class="rodape-cartao">
                Ainda nao tem conta? <a href="cadastro.php" class="link">Criar cadastro</a>
            </p>
        </section>
    </main>
</div>

<script src="script.js"></script>
</body>
</html>

<?php
require_once __DIR__ . '/includes/auth.php';

if (estaLogado()) {
    header('Location: dashboard.php');
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$antigo = static function (string $campo) use ($flash): string {
    return e($flash['dados'][$campo] ?? '');
};

$estados = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar conta | ObraLog</title>
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
            <h1>Comece a rodar<br><em>em poucos minutos.</em></h1>
            <p>
                Crie sua conta como loja para solicitar entregas de material,
                ou como motorista para receber e realizar as rotas.
            </p>

            <ul class="lista-recursos">
                <li>
                    <span class="icone">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9h18"/><path d="m5 9 1.5-5h11L19 9"/><path d="M5 9v11h14V9"/><path d="M9 20v-6h6v6"/>
                        </svg>
                    </span>
                    Loja: CNPJ, endereco completo e telefones
                </li>
                <li>
                    <span class="icone">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="8" cy="12" r="2.5"/><path d="M14 10h5"/><path d="M14 14h3"/>
                        </svg>
                    </span>
                    Motorista: CNH e veiculos vinculados
                </li>
                <li>
                    <span class="icone">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>
                        </svg>
                    </span>
                    Senha protegida com hash no banco de dados
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
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/>
                    </svg>
                    Novo cadastro
                </span>
                <h2>Criar sua conta</h2>
                <p>Leva menos de um minuto e ja libera o painel.</p>
            </header>

            <div class="indicador-etapas" id="indicador">
                <span class="bolinha ativa" data-bolinha="1">1</span>
                <span class="traco" data-traco="1"><i></i></span>
                <span class="bolinha" data-bolinha="2">2</span>
            </div>

            <div class="aviso erro <?= $flash ? 'visivel' : '' ?>" id="aviso" role="alert">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>
                </svg>
                <span id="aviso-texto"><?= $flash ? e($flash['mensagem']) : '' ?></span>
            </div>

            <form class="formulario" id="form-cadastro" action="api/cadastrar.php" method="post" novalidate>

                <!-- ---------- ETAPA 1: tipo + dados de acesso ---------- -->
                <div class="etapa ativa" data-etapa="1">

                    <div class="campo" data-campo="tipo_usuario">
                        <label>Tipo de conta <span class="obrigatorio">*</span></label>
                        <div class="seletor-tipo">
                            <label class="opcao-tipo">
                                <input type="radio" name="tipo_usuario" value="loja" required
                                    <?= ($antigo('tipo_usuario') ?: 'loja') === 'loja' ? 'checked' : '' ?>>
                                <span class="conteudo">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 9h18"/><path d="m5 9 1.5-5h11L19 9"/><path d="M5 9v11h14V9"/><path d="M9 20v-6h6v6"/>
                                    </svg>
                                    <strong>Loja</strong>
                                    <small>Solicito entregas</small>
                                </span>
                            </label>
                            <label class="opcao-tipo">
                                <input type="radio" name="tipo_usuario" value="motorista"
                                    <?= $antigo('tipo_usuario') === 'motorista' ? 'checked' : '' ?>>
                                <span class="conteudo">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M10 17h4V5H2v12h3"/><path d="M20 17h2v-3.34a4 4 0 0 0-1.17-2.83L19 9h-5v8h1"/>
                                        <circle cx="7.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>
                                    </svg>
                                    <strong>Motorista</strong>
                                    <small>Realizo entregas</small>
                                </span>
                            </label>
                        </div>
                        <span class="mensagem-erro"></span>
                    </div>

                    <div class="campo" data-campo="nome">
                        <label for="nome">Nome completo / Razao social <span class="obrigatorio">*</span></label>
                        <div class="entrada">
                            <span class="icone-campo">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                </svg>
                            </span>
                            <input type="text" id="nome" name="nome" placeholder="Como devemos te chamar"
                                   autocomplete="name" required value="<?= $antigo('nome') ?>">
                        </div>
                        <span class="mensagem-erro"></span>
                    </div>

                    <div class="campo" data-campo="email">
                        <label for="email">E-mail <span class="obrigatorio">*</span></label>
                        <div class="entrada">
                            <span class="icone-campo">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>
                                </svg>
                            </span>
                            <input type="email" id="email" name="email" placeholder="voce@empresa.com.br"
                                   autocomplete="email" required value="<?= $antigo('email') ?>">
                        </div>
                        <span class="mensagem-erro"></span>
                    </div>

                    <div class="campo" data-campo="senha">
                        <label for="senha">Senha <span class="obrigatorio">*</span></label>
                        <div class="entrada">
                            <span class="icone-campo">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </span>
                            <input type="password" id="senha" name="senha" placeholder="Minimo de 6 caracteres"
                                   autocomplete="new-password" required>
                            <button type="button" class="ver-senha" data-alvo="senha" aria-label="Mostrar senha">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        <div class="forca-senha" id="forca" data-nivel="0">
                            <div class="forca-barras"><span></span><span></span><span></span><span></span></div>
                            <small id="forca-texto">Use letras, numeros e simbolos</small>
                        </div>
                        <span class="mensagem-erro"></span>
                    </div>

                    <div class="campo" data-campo="confirmar_senha">
                        <label for="confirmar_senha">Confirmar senha <span class="obrigatorio">*</span></label>
                        <div class="entrada">
                            <span class="icone-campo">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>
                                </svg>
                            </span>
                            <input type="password" id="confirmar_senha" name="confirmar_senha"
                                   placeholder="Repita a senha" autocomplete="new-password" required>
                            <button type="button" class="ver-senha" data-alvo="confirmar_senha" aria-label="Mostrar senha">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        <span class="mensagem-erro"></span>
                    </div>

                    <button type="button" class="botao botao-principal" id="botao-avancar">
                        <span class="rotulo">Continuar</span>
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                        </svg>
                    </button>
                </div>

                <!-- ---------- ETAPA 2: dados da especializacao ---------- -->
                <div class="etapa" data-etapa="2">

                    <div class="campo" data-campo="telefone">
                        <label for="telefone">Telefone <span class="obrigatorio">*</span></label>
                        <div class="entrada">
                            <span class="icone-campo">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2 4.2 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.4-1.1a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.7.7A2 2 0 0 1 22 16.9z"/>
                                </svg>
                            </span>
                            <input type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000"
                                   inputmode="numeric" maxlength="15" required value="<?= $antigo('telefone') ?>">
                        </div>
                        <span class="mensagem-erro"></span>
                    </div>

                    <!-- ......... campos exclusivos da LOJA ......... -->
                    <div id="bloco-loja">
                        <div class="linha">
                            <div class="campo" data-campo="cnpj">
                                <label for="cnpj">CNPJ <span class="obrigatorio">*</span></label>
                                <div class="entrada">
                                    <input type="text" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00"
                                           inputmode="numeric" maxlength="18" style="padding-left:16px"
                                           value="<?= $antigo('cnpj') ?>">
                                </div>
                                <span class="mensagem-erro"></span>
                            </div>
                            <div class="campo" data-campo="nome_fantasia">
                                <label for="nome_fantasia">Nome fantasia</label>
                                <div class="entrada">
                                    <input type="text" id="nome_fantasia" name="nome_fantasia" placeholder="Opcional"
                                           style="padding-left:16px" value="<?= $antigo('nome_fantasia') ?>">
                                </div>
                                <span class="mensagem-erro"></span>
                            </div>
                        </div>

                        <div class="linha" style="margin-top:18px">
                            <div class="campo" data-campo="cep">
                                <label for="cep">CEP <span class="obrigatorio">*</span></label>
                                <div class="entrada">
                                    <input type="text" id="cep" name="cep" placeholder="00000-000"
                                           inputmode="numeric" maxlength="9" style="padding-left:16px"
                                           value="<?= $antigo('cep') ?>">
                                </div>
                                <span class="mensagem-erro"></span>
                            </div>
                            <div class="campo" data-campo="numero">
                                <label for="numero">Numero <span class="obrigatorio">*</span></label>
                                <div class="entrada">
                                    <input type="text" id="numero" name="numero" placeholder="123"
                                           style="padding-left:16px" value="<?= $antigo('numero') ?>">
                                </div>
                                <span class="mensagem-erro"></span>
                            </div>
                        </div>

                        <div class="campo" data-campo="rua" style="margin-top:18px">
                            <label for="rua">Rua <span class="obrigatorio">*</span></label>
                            <div class="entrada">
                                <input type="text" id="rua" name="rua" placeholder="Av. das Industrias"
                                       style="padding-left:16px" value="<?= $antigo('rua') ?>">
                            </div>
                            <span class="mensagem-erro"></span>
                        </div>

                        <div class="campo" data-campo="complemento" style="margin-top:18px">
                            <label for="complemento">Complemento</label>
                            <div class="entrada">
                                <input type="text" id="complemento" name="complemento" placeholder="Galpao, sala, bloco (opcional)"
                                       style="padding-left:16px" value="<?= $antigo('complemento') ?>">
                            </div>
                            <span class="mensagem-erro"></span>
                        </div>

                        <div class="linha-3" style="margin-top:18px">
                            <div class="campo" data-campo="cidade">
                                <label for="cidade">Cidade <span class="obrigatorio">*</span></label>
                                <div class="entrada">
                                    <input type="text" id="cidade" name="cidade" placeholder="Sua cidade"
                                           style="padding-left:16px" value="<?= $antigo('cidade') ?>">
                                </div>
                                <span class="mensagem-erro"></span>
                            </div>
                            <div class="campo" data-campo="estado">
                                <label for="estado">UF <span class="obrigatorio">*</span></label>
                                <div class="entrada">
                                    <select id="estado" name="estado">
                                        <option value="">--</option>
                                        <?php foreach ($estados as $uf): ?>
                                            <option value="<?= $uf ?>" <?= $antigo('estado') === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="seta">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m6 9 6 6 6-6"/>
                                        </svg>
                                    </span>
                                </div>
                                <span class="mensagem-erro"></span>
                            </div>
                        </div>
                    </div>

                    <!-- ......... campos exclusivos do MOTORISTA ......... -->
                    <div id="bloco-motorista">
                        <div class="campo" data-campo="cnh">
                            <label for="cnh">Numero da CNH <span class="obrigatorio">*</span></label>
                            <div class="entrada">
                                <span class="icone-campo">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="8" cy="12" r="2.5"/><path d="M14 10h5"/><path d="M14 14h3"/>
                                    </svg>
                                </span>
                                <input type="text" id="cnh" name="cnh" placeholder="11 digitos"
                                       inputmode="numeric" maxlength="11" value="<?= $antigo('cnh') ?>">
                            </div>
                            <span class="mensagem-erro"></span>
                        </div>
                    </div>

                    <label class="checkbox" style="margin-top:4px">
                        <input type="checkbox" id="termos" required>
                        <span class="marca">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m20 6-11 11-5-5"/>
                            </svg>
                        </span>
                        Concordo com os termos de uso da plataforma
                    </label>

                    <div class="acoes-etapa">
                        <button type="button" class="botao botao-secundario" id="botao-voltar">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
                            </svg>
                            Voltar
                        </button>
                        <button type="submit" class="botao botao-principal" id="botao-cadastrar">
                            <span class="girando"></span>
                            <span class="rotulo">Criar conta</span>
                        </button>
                    </div>
                </div>
            </form>

            <p class="rodape-cartao">
                Ja tem cadastro? <a href="login.php" class="link">Fazer login</a>
            </p>
        </section>
    </main>
</div>

<script src="script.js"></script>
</body>
</html>

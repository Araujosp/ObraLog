/* =====================================================================
   ObraLog - JavaScript das telas de login e cadastro (sem bibliotecas)
   ===================================================================== */
(function () {
    'use strict';

    /* ---------- atalhos ---------- */
    var $  = function (sel, ctx) { return (ctx || document).querySelector(sel); };
    var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); };

    var aviso      = $('#aviso');
    var avisoTexto = $('#aviso-texto');

    /* =================================================================
       AVISOS E ERROS DE CAMPO
       ================================================================= */
    function mostrarAviso(texto, tipo) {
        if (!aviso) { return; }
        aviso.classList.remove('erro', 'sucesso');
        aviso.classList.add(tipo || 'erro', 'visivel');
        avisoTexto.textContent = texto;
    }

    function esconderAviso() {
        if (aviso) { aviso.classList.remove('visivel'); }
    }

    function marcarErro(nomeCampo, mensagem) {
        var campo = $('[data-campo="' + nomeCampo + '"]');
        if (!campo) { return; }
        campo.classList.add('com-erro');
        var alvo = $('.mensagem-erro', campo);
        if (alvo) { alvo.textContent = mensagem || ''; }
    }

    function limparErro(nomeCampo) {
        var campo = $('[data-campo="' + nomeCampo + '"]');
        if (!campo) { return; }
        campo.classList.remove('com-erro');
        var alvo = $('.mensagem-erro', campo);
        if (alvo) { alvo.textContent = ''; }
    }

    function limparTodosOsErros() {
        $$('.campo.com-erro').forEach(function (campo) {
            campo.classList.remove('com-erro');
            var alvo = $('.mensagem-erro', campo);
            if (alvo) { alvo.textContent = ''; }
        });
    }

    // ao digitar, o erro do campo some
    $$('.campo').forEach(function (campo) {
        campo.addEventListener('input', function () {
            campo.classList.remove('com-erro');
            var alvo = $('.mensagem-erro', campo);
            if (alvo) { alvo.textContent = ''; }
        });
    });

    /* =================================================================
       MOSTRAR / OCULTAR SENHA
       ================================================================= */
    var olhoAberto  = '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/>';
    var olhoFechado = '<path d="M9.9 4.2A10.9 10.9 0 0 1 12 4c6.4 0 10 7 10 7a18 18 0 0 1-3.2 4.2"/>' +
                      '<path d="M6.6 6.6A18 18 0 0 0 2 11s3.6 7 10 7a10.9 10.9 0 0 0 4.5-.9"/>' +
                      '<path d="M14.1 14.1a3 3 0 1 1-4.2-4.2"/><path d="m2 2 20 20"/>';

    $$('.ver-senha').forEach(function (botao) {
        botao.addEventListener('click', function () {
            var input = document.getElementById(botao.dataset.alvo);
            if (!input) { return; }
            var oculto = input.type === 'password';
            input.type = oculto ? 'text' : 'password';
            $('svg', botao).innerHTML = oculto ? olhoFechado : olhoAberto;
            botao.setAttribute('aria-label', oculto ? 'Ocultar senha' : 'Mostrar senha');
            input.focus();
        });
    });

    /* =================================================================
       MASCARAS
       ================================================================= */
    function digitos(valor) { return valor.replace(/\D/g, ''); }

    function aplicarMascara(input, formatar, maxDigitos) {
        if (!input) { return; }
        input.addEventListener('input', function () {
            var numeros = digitos(input.value).slice(0, maxDigitos);
            input.value = formatar(numeros);
        });
    }

    aplicarMascara($('#telefone'), function (v) {
        if (v.length <= 2)  { return v.replace(/(\d{0,2})/, '($1'); }
        if (v.length <= 6)  { return v.replace(/(\d{2})(\d{0,4})/, '($1) $2'); }
        if (v.length <= 10) { return v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3'); }
        return v.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
    }, 11);

    aplicarMascara($('#cnpj'), function (v) {
        return v
            .replace(/^(\d{2})(\d)/, '$1.$2')
            .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
            .replace(/\.(\d{3})(\d)/, '.$1/$2')
            .replace(/(\d{4})(\d)/, '$1-$2');
    }, 14);

    aplicarMascara($('#cep'), function (v) {
        return v.replace(/^(\d{5})(\d)/, '$1-$2');
    }, 8);

    aplicarMascara($('#cnh'), function (v) { return v; }, 11);

    /* =================================================================
       VALIDACOES
       ================================================================= */
    function emailValido(valor) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(valor.trim());
    }

    function cnpjValido(valor) {
        var cnpj = digitos(valor);
        if (cnpj.length !== 14 || /^(\d)\1{13}$/.test(cnpj)) { return false; }
        for (var t = 12; t < 14; t++) {
            var soma = 0;
            var peso = t - 7;
            for (var i = 0; i < t; i++) {
                soma += Number(cnpj[i]) * peso;
                peso = (peso - 1) < 2 ? 9 : peso - 1;
            }
            var dv = (soma % 11) < 2 ? 0 : 11 - (soma % 11);
            if (Number(cnpj[t]) !== dv) { return false; }
        }
        return true;
    }

    /* =================================================================
       ENVIO VIA FETCH (o formulario continua funcionando sem JS)
       ================================================================= */
    function enviarFormulario(form, botao) {
        botao.disabled = true;
        botao.classList.add('carregando');
        esconderAviso();

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (resposta) {
                return resposta.json().catch(function () {
                    throw new Error('O servidor devolveu uma resposta invalida.');
                });
            })
            .then(function (dados) {
                if (dados.ok) {
                    mostrarAviso(dados.mensagem, 'sucesso');
                    window.location.href = dados.redirect || 'dashboard.php';
                    return;
                }

                botao.disabled = false;
                botao.classList.remove('carregando');
                mostrarAviso(dados.mensagem || 'Nao foi possivel continuar.', 'erro');

                var erros = dados.erros || {};
                var primeiro = null;
                Object.keys(erros).forEach(function (campo) {
                    marcarErro(campo, erros[campo]);
                    if (!primeiro) { primeiro = campo; }
                });

                // volta para a etapa que contem o primeiro erro
                if (primeiro) {
                    var caixa = $('[data-campo="' + primeiro + '"]');
                    var etapa = caixa && caixa.closest('.etapa');
                    if (etapa && !etapa.classList.contains('ativa')) {
                        irParaEtapa(Number(etapa.dataset.etapa));
                    }
                    var entrada = caixa && caixa.querySelector('input, select');
                    if (entrada) { entrada.focus(); }
                }
            })
            .catch(function (erro) {
                botao.disabled = false;
                botao.classList.remove('carregando');
                mostrarAviso(erro.message || 'Falha de comunicacao com o servidor.', 'erro');
            });
    }

    /* =================================================================
       TELA DE LOGIN
       ================================================================= */
    var formLogin = $('#form-login');

    if (formLogin) {
        formLogin.addEventListener('submit', function (evento) {
            evento.preventDefault();
            limparTodosOsErros();

            var email = $('#email').value.trim();
            var senha = $('#senha').value;
            var ok = true;

            if (!email)                  { marcarErro('email', 'Informe o seu e-mail.'); ok = false; }
            else if (!emailValido(email)) { marcarErro('email', 'E-mail em formato invalido.'); ok = false; }
            if (!senha)                  { marcarErro('senha', 'Informe a sua senha.'); ok = false; }

            if (!ok) {
                mostrarAviso('Confira os campos destacados.', 'erro');
                return;
            }

            enviarFormulario(formLogin, $('#botao-entrar'));
        });

        var linkRecuperar = $('#link-recuperar');
        if (linkRecuperar) {
            linkRecuperar.addEventListener('click', function (evento) {
                evento.preventDefault();
                mostrarAviso('A recuperacao de senha ainda nao esta disponivel. Fale com o administrador.', 'erro');
            });
        }
    }

    /* =================================================================
       TELA DE CADASTRO
       ================================================================= */
    var formCadastro = $('#form-cadastro');

    function irParaEtapa(numero) {
        $$('.etapa').forEach(function (etapa) {
            etapa.classList.toggle('ativa', Number(etapa.dataset.etapa) === numero);
        });
        $$('.indicador-etapas .bolinha').forEach(function (bolinha) {
            var n = Number(bolinha.dataset.bolinha);
            bolinha.classList.toggle('ativa', n === numero);
            bolinha.classList.toggle('concluida', n < numero);
        });
        var traco = $('.indicador-etapas .traco');
        if (traco) { traco.classList.toggle('preenchido', numero > 1); }
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    if (formCadastro) {
        var blocoLoja      = $('#bloco-loja');
        var blocoMotorista = $('#bloco-motorista');

        /* ---------- alterna os campos conforme o tipo de conta ---------- */
        function tipoSelecionado() {
            var marcado = $('input[name="tipo_usuario"]:checked');
            return marcado ? marcado.value : '';
        }

        function alternarBlocos() {
            var ehLoja = tipoSelecionado() === 'loja';

            blocoLoja.style.display      = ehLoja ? 'block' : 'none';
            blocoMotorista.style.display = ehLoja ? 'none'  : 'block';

            // campos escondidos ficam desabilitados: nao vao para o servidor
            $$('input, select', blocoLoja).forEach(function (el) { el.disabled = !ehLoja; });
            $$('input, select', blocoMotorista).forEach(function (el) { el.disabled = ehLoja; });
        }

        $$('input[name="tipo_usuario"]').forEach(function (radio) {
            radio.addEventListener('change', alternarBlocos);
        });
        alternarBlocos();

        /* ---------- forca da senha ---------- */
        var campoSenha = $('#senha');
        var forca      = $('#forca');
        var forcaTexto = $('#forca-texto');
        var rotulos    = ['Use letras, numeros e simbolos', 'Senha fraca', 'Senha razoavel', 'Senha boa', 'Senha forte'];

        campoSenha.addEventListener('input', function () {
            var valor = campoSenha.value;
            var nivel = 0;
            if (valor.length >= 6)                       { nivel++; }
            if (valor.length >= 10)                      { nivel++; }
            if (/[A-Z]/.test(valor) && /[a-z]/.test(valor)) { nivel++; }
            if (/\d/.test(valor) && /[^A-Za-z0-9]/.test(valor)) { nivel++; }
            if (!valor) { nivel = 0; }

            forca.dataset.nivel = String(nivel);
            forcaTexto.textContent = rotulos[nivel];
        });

        /* ---------- etapa 1 -> etapa 2 ---------- */
        function validarEtapa1() {
            limparTodosOsErros();
            var ok = true;

            if (!tipoSelecionado()) {
                marcarErro('tipo_usuario', 'Escolha o tipo de conta.');
                ok = false;
            }

            var nome = $('#nome').value.trim();
            if (nome.length < 3) { marcarErro('nome', 'Informe o nome completo.'); ok = false; }

            var email = $('#email').value.trim();
            if (!emailValido(email)) { marcarErro('email', 'Informe um e-mail valido.'); ok = false; }

            var senha = $('#senha').value;
            if (senha.length < 6) { marcarErro('senha', 'A senha precisa ter no minimo 6 caracteres.'); ok = false; }

            if ($('#confirmar_senha').value !== senha) {
                marcarErro('confirmar_senha', 'As senhas nao conferem.');
                ok = false;
            }

            return ok;
        }

        $('#botao-avancar').addEventListener('click', function () {
            if (!validarEtapa1()) {
                mostrarAviso('Confira os campos destacados.', 'erro');
                return;
            }
            esconderAviso();
            irParaEtapa(2);
        });

        $('#botao-voltar').addEventListener('click', function () {
            esconderAviso();
            irParaEtapa(1);
        });

        /* ---------- etapa 2 + envio ---------- */
        function validarEtapa2() {
            limparTodosOsErros();
            var ok = true;

            var telefone = digitos($('#telefone').value);
            if (telefone.length < 10) {
                marcarErro('telefone', 'Informe o telefone com DDD.');
                ok = false;
            }

            if (tipoSelecionado() === 'loja') {
                if (!cnpjValido($('#cnpj').value)) { marcarErro('cnpj', 'CNPJ invalido.'); ok = false; }
                if (digitos($('#cep').value).length !== 8) { marcarErro('cep', 'CEP invalido.'); ok = false; }
                if (!$('#numero').value.trim()) { marcarErro('numero', 'Informe o numero.'); ok = false; }
                if (!$('#rua').value.trim())    { marcarErro('rua', 'Informe a rua.'); ok = false; }
                if (!$('#cidade').value.trim()) { marcarErro('cidade', 'Informe a cidade.'); ok = false; }
                if (!$('#estado').value)        { marcarErro('estado', 'Selecione a UF.'); ok = false; }
            } else {
                if (digitos($('#cnh').value).length !== 11) {
                    marcarErro('cnh', 'A CNH deve ter 11 digitos.');
                    ok = false;
                }
            }

            if (!$('#termos').checked) {
                mostrarAviso('E preciso aceitar os termos de uso.', 'erro');
                ok = false;
            }

            return ok;
        }

        formCadastro.addEventListener('submit', function (evento) {
            evento.preventDefault();

            if (!validarEtapa1()) {
                mostrarAviso('Confira os campos destacados.', 'erro');
                irParaEtapa(1);
                return;
            }
            if (!validarEtapa2()) {
                if (!aviso.classList.contains('visivel')) {
                    mostrarAviso('Confira os campos destacados.', 'erro');
                }
                return;
            }

            enviarFormulario(formCadastro, $('#botao-cadastrar'));
        });
    }
})();

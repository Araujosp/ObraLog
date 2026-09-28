<?php
/**
 * ObraLog - funcoes de sessao e autenticacao.
 * As funcoes que usam PDO recebem $pdo por parametro; quem chama
 * precisa ter dado require em includes/conexao.php antes.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/** Retorna true se existe usuario logado na sessao. */
function estaLogado(): bool
{
    return isset($_SESSION['usuario']['id_usuario']);
}

/** Dados do usuario logado (ou null). */
function usuarioLogado(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

/** Bloqueia paginas internas: manda para o login se nao estiver logado. */
function exigirLogin(): void
{
    if (!estaLogado()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Bloqueia a pagina para um tipo especifico ('loja' ou 'motorista').
 * Quem esta logado com o tipo errado nao recebe erro: e mandado para
 * a propria area dele, porque so redirecionar apos o login nao basta
 * (a pessoa pode digitar a URL da area errada direto).
 */
function exigirTipo(string $tipo): array
{
    exigirLogin();
    $usuario = usuarioLogado();

    if ($usuario['tipo_usuario'] !== $tipo) {
        $destino = $usuario['tipo_usuario'] === 'loja' ? 'cadastrar-entrega.php' : 'entregas.php';
        header('Location: ' . $destino);
        exit;
    }

    return $usuario;
}

/** Iniciais do nome para mostrar no avatar do topo (ex.: "João Martins" -> "JM"). */
function iniciais(string $nome): string
{
    $partes = preg_split('/\s+/', trim($nome));
    $letras = mb_substr($partes[0] ?? '', 0, 1);
    if (count($partes) > 1) {
        $letras .= mb_substr(end($partes), 0, 1);
    }
    return mb_strtoupper($letras);
}

/** Grava o usuario na sessao apos login/cadastro. */
function iniciarSessao(array $usuario): void
{
    session_regenerate_id(true);
    $_SESSION['usuario'] = [
        'id_usuario'   => (int) $usuario['id_usuario'],
        'nome'         => $usuario['nome'],
        'email'        => $usuario['email'],
        'tipo_usuario' => $usuario['tipo_usuario'],
    ];
}

/** Encerra a sessao. */
function encerrarSessao(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** Busca um usuario pelo e-mail usando prepared statement. */
function buscarUsuarioPorEmail(PDO $pdo, string $email): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM usuario WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    return $usuario ?: null;
}

/** Conta registros de uma coluna (checagem de duplicidade). */
function existeRegistro(PDO $pdo, string $tabela, string $coluna, string $valor): bool
{
    $stmt = $pdo->prepare("SELECT 1 FROM `$tabela` WHERE `$coluna` = ? LIMIT 1");
    $stmt->execute([$valor]);
    return (bool) $stmt->fetchColumn();
}

/** Deixa so os digitos de uma string (CPF/CNPJ/CEP/telefone). */
function somenteDigitos(?string $valor): string
{
    return preg_replace('/\D/', '', (string) $valor);
}

/** Escapa saida em HTML. */
function e(?string $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/** Devolve uma resposta JSON e encerra o script. */
function responderJson(array $dados, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Valida o CNPJ pelos digitos verificadores. */
function validarCnpj(string $cnpj): bool
{
    $cnpj = somenteDigitos($cnpj);
    if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) {
        return false;
    }
    for ($t = 12; $t < 14; $t++) {
        $soma = 0;
        $peso = $t - 7;
        for ($i = 0; $i < $t; $i++) {
            $soma += $cnpj[$i] * $peso;
            $peso = ($peso - 1) < 2 ? 9 : $peso - 1;
        }
        $digito = ($soma % 11) < 2 ? 0 : 11 - ($soma % 11);
        if ((int) $cnpj[$t] !== $digito) {
            return false;
        }
    }
    return true;
}

/** Valida o CPF pelos digitos verificadores. */
function validarCpf(string $cpf): bool
{
    $cpf = somenteDigitos($cpf);
    if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }
    for ($t = 9; $t < 11; $t++) {
        $soma = 0;
        for ($i = 0; $i < $t; $i++) {
            $soma += (int) $cpf[$i] * (($t + 1) - $i);
        }
        $digito = ($soma * 10 % 11) % 10;
        if ((int) $cpf[$t] !== $digito) {
            return false;
        }
    }
    return true;
}

/**
 * Responde ao formulario.
 * - Chamada via fetch (JS): devolve JSON.
 * - Sem JavaScript: guarda o retorno na sessao e volta para a pagina de origem.
 *
 * $destino e $voltarPara sao caminhos relativos A PAGINA QUE ESTA SENDO
 * ABERTA NO NAVEGADOR (nao ao arquivo PHP atual). Ex.: chamando a partir
 * de api/entrar.php, use '../entregas.php'; chamando a partir de
 * cadastro.php (que fica na raiz e posta para si mesmo), use so
 * 'entregas.php'.
 */
function responderFormulario(bool $ok, string $mensagem, array $erros = [], string $destino = '', string $voltarPara = 'login.php'): void
{
    $ehAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

    if ($ehAjax) {
        responderJson([
            'ok'        => $ok,
            'mensagem'  => $mensagem,
            'erros'     => (object) $erros,
            'redirect'  => $destino,
        ], $ok ? 200 : 422);
    }

    if ($ok && $destino !== '') {
        header('Location: ' . $destino);
        exit;
    }

    $_SESSION['flash'] = ['mensagem' => $mensagem, 'erros' => $erros, 'dados' => $_POST];
    header('Location: ' . $voltarPara);
    exit;
}

/** Formata telefone para exibicao: (11) 98877-6655 */
function formatarTelefone(?string $valor): string
{
    $d = somenteDigitos($valor);
    if (strlen($d) === 11) {
        return sprintf('(%s) %s-%s', substr($d, 0, 2), substr($d, 2, 5), substr($d, 7));
    }
    if (strlen($d) === 10) {
        return sprintf('(%s) %s-%s', substr($d, 0, 2), substr($d, 2, 4), substr($d, 6));
    }
    return (string) $valor;
}

/** Formata CNPJ para exibicao: 11.222.333/0001-81 */
function formatarCnpj(?string $valor): string
{
    $d = somenteDigitos($valor);
    if (strlen($d) !== 14) {
        return (string) $valor;
    }
    return sprintf('%s.%s.%s/%s-%s', substr($d, 0, 2), substr($d, 2, 3), substr($d, 5, 3), substr($d, 8, 4), substr($d, 12));
}

/** Formata CPF para exibicao: 529.982.247-25 */
function formatarCpf(?string $valor): string
{
    $d = somenteDigitos($valor);
    if (strlen($d) !== 11) {
        return (string) $valor;
    }
    return sprintf('%s.%s.%s-%s', substr($d, 0, 3), substr($d, 3, 3), substr($d, 6, 3), substr($d, 9));
}

/** Formata CEP para exibicao: 13560-000 */
function formatarCep(?string $valor): string
{
    $d = somenteDigitos($valor);
    return strlen($d) === 8 ? substr($d, 0, 5) . '-' . substr($d, 5) : (string) $valor;
}

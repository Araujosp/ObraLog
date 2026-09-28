<?php
/* =====================================================================
   ObraLog — api/entrar.php
   Confere e-mail/senha no banco e abre a sessao. O destino depois do
   login depende do tipo_usuario: motorista -> entregas.php
                                   loja      -> cadastrar-entrega.php
   ===================================================================== */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/conexao.php';

// mapa de destino por tipo de conta — reaproveitado tambem no login.php
const DESTINO_POR_TIPO = [
    'loja'      => 'cadastrar-entrega.php',
    'motorista' => 'entregas.php',
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if ($email === '' || $senha === '') {
    responderFormulario(false, 'Preencha e-mail e senha.', [], '', '../login.php');
}

$usuario = buscarUsuarioPorEmail($pdo, $email);

// mesma mensagem para "nao existe" e "senha errada" — nao entregamos
// pista de qual dos dois esta incorreto
if (!$usuario || !password_verify($senha, $usuario['senha'])) {
    responderFormulario(false, 'E-mail ou senha incorretos.', ['email' => 'Confira e-mail e senha.'], '', '../login.php');
}

iniciarSessao($usuario);

// "Lembrar de mim": estende o cookie de sessao para 30 dias
if (!empty($_POST['lembrar'])) {
    $parametros = session_get_cookie_params();
    setcookie(session_name(), session_id(), [
        'expires'  => time() + 60 * 60 * 24 * 30,
        'path'     => $parametros['path'],
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

$destino = DESTINO_POR_TIPO[$usuario['tipo_usuario']] ?? '../login.php';
responderFormulario(true, 'Login realizado com sucesso.', [], '../' . $destino);

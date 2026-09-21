<?php
/* =====================================================================
   ObraLog — api/entrar.php  (MODELO)
   Valida o login e manda o usuario para a tela certa:
     tipo_usuario = 'motorista' -> entregas.php            (ve e aceita cargas)
     tipo_usuario = 'loja'      -> cadastrar-entrega.php   (publica cargas)
   A conexao com o banco fica por sua conta (bloco comentado abaixo).
   ===================================================================== */

declare(strict_types=1);
require_once __DIR__ . '/../includes/sessao.php';

/* destinos por tipo — se criar outro perfil, e so acrescentar aqui */
const DESTINOS = [
    'motorista' => 'entregas.php',
    'loja'      => 'cadastrar-entrega.php',
];

/* ---------------------------------------------------------------- */
function voltarComErro(string $msg, array $dados = []): void
{
    $_SESSION['flash'] = ['mensagem' => $msg, 'dados' => $dados];
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if ($email === '' || $senha === '') {
    voltarComErro('Preencha e-mail e senha.', ['email' => $email]);
}

/* ============ AQUI ENTRA O SEU BANCO ==============================
require_once __DIR__ . '/../conexao.php';   // devolve $pdo

$stmt = $pdo->prepare(
    "SELECT id_usuario, nome, email, senha, tipo_usuario
       FROM usuario
      WHERE email = :email
      LIMIT 1"
);
$stmt->execute([':email' => $email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario || !password_verify($senha, $usuario['senha'])) {
    voltarComErro('E-mail ou senha incorretos.', ['email' => $email]);
}

// dados complementares da especializacao (loja OU motorista)
if ($usuario['tipo_usuario'] === 'loja') {
    $perfil = $pdo->prepare("SELECT nome_fantasia, cnpj FROM loja WHERE id_usuario = ?");
} else {
    $perfil = $pdo->prepare("SELECT cnh, telefone FROM motorista WHERE id_usuario = ?");
}
$perfil->execute([$usuario['id_usuario']]);
$_SESSION['perfil'] = $perfil->fetch(PDO::FETCH_ASSOC) ?: [];
=================================================================== */

/* --- exemplo para a tela funcionar sem banco: apague depois ------- */
$usuario = [
    'id_usuario'   => 1,
    'nome'         => 'João Martins',
    'email'        => $email,
    'tipo_usuario' => 'motorista',   // troque para 'loja' e veja o outro destino
];
/* ------------------------------------------------------------------ */

if (!isset(DESTINOS[$usuario['tipo_usuario']])) {
    voltarComErro('Sua conta esta sem perfil definido. Fale com o suporte.');
}

/* login aceito */
session_regenerate_id(true);          // evita fixacao de sessao
unset($_SESSION['flash']);

$_SESSION['usuario'] = [
    'id'    => $usuario['id_usuario'],
    'nome'  => $usuario['nome'],
    'email' => $usuario['email'],
    'tipo'  => $usuario['tipo_usuario'],
];

/* "Lembrar de mim": estende a sessao para 30 dias */
if (!empty($_POST['lembrar'])) {
    $p = session_get_cookie_params();
    setcookie(session_name(), session_id(), [
        'expires'  => time() + 60 * 60 * 24 * 30,
        'path'     => $p['path'],
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

header('Location: ../' . DESTINOS[$usuario['tipo_usuario']]);
exit;
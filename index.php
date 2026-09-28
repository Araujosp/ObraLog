<?php
/* Ponto de entrada do site: so decide para onde mandar a pessoa. */
require_once __DIR__ . '/includes/auth.php';

if ($usuario = usuarioLogado()) {
    header('Location: ' . ($usuario['tipo_usuario'] === 'loja' ? 'cadastrar-entrega.php' : 'entregas.php'));
} else {
    header('Location: login.php');
}
exit;

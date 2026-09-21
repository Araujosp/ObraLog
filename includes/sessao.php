<?php
/* =====================================================================
   ObraLog — sessao.php
   Inclua no TOPO de toda pagina interna, antes de qualquer HTML.

     require_once 'sessao.php';
     exigirTipo('motorista');   // em entregas.php
     exigirTipo('loja');        // em cadastrar-entrega.php

   Sem isso, um motorista consegue abrir a tela da loja so digitando a
   URL — o redirecionamento do login sozinho nao protege nada.
   ===================================================================== */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function usuarioLogado(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function exigirLogin(): array
{
    $u = usuarioLogado();
    if (!$u) {
        $_SESSION['flash'] = ['mensagem' => 'Faca login para continuar.'];
        header('Location: login.php');
        exit;
    }
    return $u;
}

/** Garante que o usuario logado e do tipo esperado. */
function exigirTipo(string $tipo): array
{
    $u = exigirLogin();
    if ($u['tipo'] !== $tipo) {
        // manda cada um para a sua propria area em vez de dar 403 seco
        $destino = $u['tipo'] === 'loja' ? 'cadastrar-entrega.php' : 'entregas.php';
        header('Location: ' . $destino);
        exit;
    }
    return $u;
}

/** Escape para imprimir em HTML. */
function e(?string $texto): string
{
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

/** Iniciais para o avatar do topo. */
function iniciais(string $nome): string
{
    $partes = preg_split('/\s+/', trim($nome));
    $ini = mb_substr($partes[0] ?? '', 0, 1);
    if (count($partes) > 1) {
        $ini .= mb_substr(end($partes), 0, 1);
    }
    return mb_strtoupper($ini);
}
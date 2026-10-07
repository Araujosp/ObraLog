<?php
/* ObraLog — includes/menu-motorista.php
   Atalho entre as duas areas do motorista. Quem inclui define
   $paginaAtual = 'disponiveis' ou 'minhas' antes do require. */
$paginaAtual = $paginaAtual ?? 'disponiveis';
?>
<nav class="menu-motorista" aria-label="Área do entregador">
    <a href="entregas.php" class="<?= $paginaAtual === 'disponiveis' ? 'ativo' : '' ?>">Entregas disponíveis</a>
    <a href="minhas-entregas.php" class="<?= $paginaAtual === 'minhas' ? 'ativo' : '' ?>">Minhas Entregas</a>
</nav>

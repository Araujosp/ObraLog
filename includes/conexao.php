<?php
/* =====================================================================
   ObraLog — includes/conexao.php
   Abre a conexao PDO com o MySQL e devolve em $pdo.
   Ajuste as 4 constantes abaixo para o seu ambiente (ex.: XAMPP).
   ===================================================================== */

const DB_HOST = 'localhost';
const DB_NAME = 'obralog';
const DB_USER = 'root';
const DB_SENHA = '';           // no XAMPP padrao, a senha do root e vazia

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_SENHA,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $erro) {
    // Erro de conexao encerra a pagina com uma mensagem simples.
    // (Em producao isso nao apareceria assim, mas o projeto e de estudo.)
    http_response_code(500);
    exit('Nao foi possivel conectar ao banco de dados: ' . $erro->getMessage());
}

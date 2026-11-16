<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Usuario.php';
require_once __DIR__ . '/classes/Post.php';
require_once __DIR__ . '/classes/Configuracao.php';

function dbConfig(): array
{
    return [
        'driver' => getenv('DB_DRIVER') ?: 'mysql',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'sci',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
        'sqlite_path' => __DIR__ . '/database/sci.sqlite',
    ];
}

function usuarioLogado(): bool
{
    return !empty($_SESSION['usuario_id']);
}

function senhaPadraoAdmin(): string
{
    return 'Sci@2026!Admin';
}

function usuarioAtual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit();
}

$dadosCorpo = json_decode(file_get_contents('php://input'), true);
if (!is_array($dadosCorpo)) {
    $dadosCorpo = $_POST;
}

$usuario = trim((string) ($dadosCorpo['usuario'] ?? $dadosCorpo['user'] ?? ''));
$senha = (string) ($dadosCorpo['senha'] ?? $dadosCorpo['pass'] ?? '');

try {
    $database = (new Database())->conectar();
    $modelUsuario = new Usuario($database);
    $usuarioAutenticado = $modelUsuario->autenticar($usuario, $senha);

    if (!$usuarioAutenticado) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Usuário ou senha inválidos.',
        ]);
        exit();
    }

    $_SESSION['usuario_id'] = (int) $usuarioAutenticado['id'];
    $_SESSION['usuario_nome'] = $usuarioAutenticado['nome'];
    $_SESSION['usuario'] = $usuarioAutenticado['usuario'];

    echo json_encode([
        'success' => true,
        'message' => 'Login realizado com sucesso.',
        'redirect' => 'admin/home.php',
    ]);
} catch (Throwable $erro) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno no login.',
    ]);
}

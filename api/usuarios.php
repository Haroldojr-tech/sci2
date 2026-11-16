<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !usuarioLogado()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Você precisa estar logado para acessar.']);
    exit();
}

try {
    $database = (new Database())->conectar();
    $usuarioModel = new Usuario($database);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        echo json_encode($usuarioModel->listar());
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $dados = json_decode(file_get_contents('php://input'), true);
        if (!is_array($dados)) {
            $dados = $_POST;
        }

        $novoUsuario = $usuarioModel->cadastrar($dados);

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Usuário cadastrado com sucesso.',
            'usuario' => $novoUsuario,
        ]);
        exit();
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método HTTP não suportado.']);
} catch (Throwable $erro) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $erro->getMessage(),
    ]);
}

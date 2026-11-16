<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $database = (new Database())->conectar();
    $postModel = new Post($database);
    $configModel = new Configuracao($database);

    $metodo = $_SERVER['REQUEST_METHOD'];

    if (in_array($metodo, ['POST', 'PUT', 'DELETE'], true) && !usuarioLogado()) {
        http_response_code(401);
        echo json_encode(['error' => 'Autenticação necessária.']);
        exit();
    }

    if (isset($_GET['config'])) {
        if ($metodo === 'GET') {
            echo json_encode($configModel->obter());
            exit();
        }

        if ($metodo === 'POST') {
            $dados = json_decode(file_get_contents('php://input'), true);
            $dados = is_array($dados) ? $dados : [];
            echo json_encode($configModel->salvar($dados));
            exit();
        }
    }

    if ($metodo === 'GET') {
        echo json_encode($postModel->listar());
        exit();
    }

    if ($metodo === 'POST') {
        $dados = $_POST;

        if (!empty($_FILES['image']['name'])) {
            $arquivo = $_FILES['image'];
            $diretorio = __DIR__ . '/../uploads/posts';

            if (!is_dir($diretorio)) {
                mkdir($diretorio, 0777, true);
            }

            $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

            if (!in_array($extensao, $permitidas, true)) {
                throw new InvalidArgumentException('Extensão de imagem inválida.');
            }

            $nomeArquivo = uniqid('post_', true) . '.' . $extensao;
            $caminhoCompleto = $diretorio . '/' . $nomeArquivo;

            if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
                throw new RuntimeException('Não foi possível salvar a imagem.');
            }

            $dados['postImage'] = '/sci/uploads/posts/' . $nomeArquivo;
        }

        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;

        if ($id) {
            $resultado = $postModel->atualizar($id, $dados);
            echo json_encode($resultado ?: ['error' => 'Post não encontrado.']);
            exit();
        }

        $resultado = $postModel->criar($dados);
        echo json_encode($resultado);
        exit();
    }

    if ($metodo === 'PUT') {
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID obrigatório.']);
            exit();
        }

        $dadosJson = json_decode(file_get_contents('php://input'), true);
        $dadosJson = is_array($dadosJson) ? $dadosJson : [];
        $resultado = $postModel->atualizar((int) $_GET['id'], $dadosJson);

        echo json_encode($resultado ?: ['error' => 'Post não encontrado.']);
        exit();
    }

    if ($metodo === 'DELETE') {
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID obrigatório.']);
            exit();
        }

        $id = (int) $_GET['id'];
        $resultado = $postModel->excluir($id);
        echo json_encode(['success' => $resultado]);
        exit();
    }

    http_response_code(405);
    echo json_encode(['error' => 'Método HTTP não suportado.']);
} catch (Throwable $erro) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erro interno do servidor.',
    ]);
}

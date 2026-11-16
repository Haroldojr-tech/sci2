<?php

/**
 * Reprensa a entidade usuário do sistema.
 * Aqui ficam as regras de autenticação e validação da senha.
 */
class Usuario
{
    private PDO $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    public function autenticar(string $usuario, string $senha): ?array
    {
        $usuario = trim($usuario);
        $senha = trim($senha);

        if ($usuario === '' || $senha === '') {
            return null;
        }

        $sql = 'SELECT * FROM usuarios WHERE usuario = :usuario LIMIT 1';
        $stmt = $this->database->prepare($sql);
        $stmt->execute([':usuario' => $usuario]);
        $registro = $stmt->fetch();

        if (!$registro) {
            return null;
        }

        if (!password_verify($senha, $registro['senha'])) {
            return null;
        }

        unset($registro['senha']);
        return $registro;
    }

    public function listar(): array
    {
        $sql = 'SELECT id, nome, usuario, email, tipo, criado_em FROM usuarios ORDER BY id ASC';
        $stmt = $this->database->query($sql);
        $usuarios = $stmt->fetchAll();

        foreach ($usuarios as &$usuario) {
            unset($usuario['senha']);
        }

        return $usuarios;
    }

    public function cadastrar(array $dados): array
    {
        $nome = trim((string) ($dados['nome'] ?? ''));
        $usuario = trim((string) ($dados['usuario'] ?? ''));
        $email = trim((string) ($dados['email'] ?? ''));
        $senha = (string) ($dados['senha'] ?? '');
        $tipo = trim((string) ($dados['tipo'] ?? 'visualizador'));

        if ($nome === '' || $usuario === '' || $email === '' || $senha === '') {
            throw new InvalidArgumentException('Preencha nome, usuário, e-mail e senha.');
        }

        if (strlen($usuario) < 3) {
            throw new InvalidArgumentException('O usuário deve ter pelo menos 3 caracteres.');
        }

        if (strlen($senha) < 6) {
            throw new InvalidArgumentException('A senha deve ter pelo menos 6 caracteres.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('E-mail inválido.');
        }

        $tiposPermitidos = ['admin', 'comunicador', 'editor', 'visualizador'];
        if (!in_array($tipo, $tiposPermitidos, true)) {
            throw new InvalidArgumentException('Tipo de usuário inválido.');
        }

        $stmt = $this->database->prepare('SELECT id FROM usuarios WHERE usuario = :usuario OR email = :email LIMIT 1');
        $stmt->execute([':usuario' => $usuario, ':email' => $email]);

        if ($stmt->fetch()) {
            throw new InvalidArgumentException('Já existe um usuário com esse login ou e-mail.');
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $insert = $this->database->prepare(
            'INSERT INTO usuarios (nome, usuario, senha, email, tipo) VALUES (:nome, :usuario, :senha, :email, :tipo)'
        );
        $insert->execute([
            ':nome' => $nome,
            ':usuario' => $usuario,
            ':senha' => $senhaHash,
            ':email' => $email,
            ':tipo' => $tipo,
        ]);

        $id = (int) $this->database->lastInsertId();

        return [
            'id' => $id,
            'nome' => $nome,
            'usuario' => $usuario,
            'email' => $email,
            'tipo' => $tipo,
            'criado_em' => date('Y-m-d H:i:s'),
        ];
    }

    public function criarPadraoSeNecessario(): void
    {
        $stmt = $this->database->query('SELECT COUNT(*) AS total FROM usuarios');
        $total = (int) $stmt->fetchColumn();

        if ($total > 0) {
            return;
        }

        $senha = password_hash(senhaPadraoAdmin(), PASSWORD_DEFAULT);
        $this->database->prepare(
            'INSERT INTO usuarios (nome, usuario, senha, email) VALUES (:nome, :usuario, :senha, :email)'
        )->execute([
            ':nome' => 'Administrador',
            ':usuario' => 'admin',
            ':senha' => $senha,
            ':email' => 'admin@sci.local',
        ]);
    }
}

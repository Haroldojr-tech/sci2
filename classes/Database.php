<?php

/**
 * Classe responsável pela criação da conexão com o banco de dados.
 * Este projeto foi migrado do armazenamento em JSON para um banco SQL.
 */
class Database
{
    private array $config;
    private ?PDO $connection = null;

    public function __construct(array $config = [])
    {
        $this->config = $config ?: dbConfig();
    }

    public function conectar(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        $driver = strtolower($this->config['driver'] ?? 'mysql');
        $driversDisponiveis = PDO::getAvailableDrivers();

        try {
            if ($driver === 'sqlite') {
                if (!in_array('sqlite', $driversDisponiveis, true)) {
                    throw new RuntimeException('O driver PDO SQLite não está habilitado no ambiente atual.');
                }

                return $this->conectarSQLite();
            }

            if ($driver === 'mysql' && !in_array('mysql', $driversDisponiveis, true)) {
                return $this->conectarSQLite();
            }

            return $this->conectarMysql();
        } catch (Throwable $erro) {
            return $this->conectarSQLite();
        }
    }

    private function conectarSQLite(): PDO
    {
        $sqlitePath = $this->config['sqlite_path'] ?? __DIR__ . '/../database/sci.sqlite';
        $this->connection = new PDO('sqlite:' . $sqlitePath);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->inicializarSqlite();
        return $this->connection;
    }

    private function conectarMysql(): PDO
    {
        $dsnBase = sprintf(
            'mysql:host=%s;port=%s;charset=%s',
            $this->config['host'],
            $this->config['port'],
            $this->config['charset'] ?? 'utf8mb4'
        );

        try {
            $baseConnection = new PDO(
                $dsnBase,
                $this->config['user'],
                $this->config['pass'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            $baseConnection->exec('CREATE DATABASE IF NOT EXISTS `' . $this->config['name'] . '`');
        } catch (Throwable $erro) {
            return $this->conectarSQLite();
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $this->config['host'],
            $this->config['port'],
            $this->config['name'],
            $this->config['charset'] ?? 'utf8mb4'
        );

        $this->connection = new PDO(
            $dsn,
            $this->config['user'],
            $this->config['pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
            ]
        );

        $this->inicializarMysql();

        return $this->connection;
    }

    private function inicializarMysql(): void
    {
        $this->connection->exec(
            "CREATE TABLE IF NOT EXISTS usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(150) NOT NULL,
                usuario VARCHAR(80) NOT NULL UNIQUE,
                senha VARCHAR(255) NOT NULL,
                email VARCHAR(150) NOT NULL,
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;"
        );

        $this->connection->exec(
            "CREATE TABLE IF NOT EXISTS posts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                titulo VARCHAR(255) NOT NULL,
                data_postagem DATE NOT NULL,
                link VARCHAR(255) NOT NULL,
                icone VARCHAR(80) NOT NULL DEFAULT 'logo',
                imagem VARCHAR(255) DEFAULT NULL,
                clicks INT NOT NULL DEFAULT 0,
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;"
        );

        $this->connection->exec(
            "CREATE TABLE IF NOT EXISTS configuracoes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                chave VARCHAR(100) NOT NULL UNIQUE,
                valor TEXT DEFAULT NULL,
                atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;"
        );

        $stmt = $this->connection->prepare('SELECT id FROM usuarios WHERE usuario = :usuario LIMIT 1');
        $stmt->execute([':usuario' => 'admin']);
        if (!$stmt->fetch()) {
            $senhaPadrao = password_hash(senhaPadraoAdmin(), PASSWORD_DEFAULT);
            $this->connection->prepare(
                'INSERT INTO usuarios (nome, usuario, senha, email) VALUES (:nome, :usuario, :senha, :email)'
            )->execute([
                ':nome' => 'Administrador',
                ':usuario' => 'admin',
                ':senha' => $senhaPadrao,
                ':email' => 'admin@sci.local',
            ]);
        }

        $stmtPosts = $this->connection->prepare('SELECT id FROM posts LIMIT 1');
        $stmtPosts->execute();
        if (!$stmtPosts->fetch()) {
            $this->connection->exec(
                "INSERT INTO posts (titulo, data_postagem, link, icone, imagem, clicks) VALUES
                ('UNIVERSE OF UI', '2024-01-10', 'https://www.ifto.edu.br/', 'logo', '', 12),
                ('DESIGN SYSTEM', '2024-02-15', 'https://www.ifto.edu.br/', 'clock', '', 24),
                ('CREATIVE TOOLS', '2024-03-22', 'https://www.ifto.edu.br/', 'calendar', '', 31),
                ('FAST DEVELOPMENT', '2024-04-18', 'https://www.ifto.edu.br/', 'secretary', '', 18);"
            );
        }

        $stmtCfg = $this->connection->prepare('SELECT id FROM configuracoes WHERE chave = :chave LIMIT 1');
        foreach (['startDate', 'endDate'] as $chave) {
            $stmtCfg->execute([':chave' => $chave]);
            if (!$stmtCfg->fetch()) {
                $this->connection->prepare('INSERT INTO configuracoes (chave, valor) VALUES (:chave, :valor)')->execute([
                    ':chave' => $chave,
                    ':valor' => '',
                ]);
            }
        }
    }

    private function inicializarSqlite(): void
    {
        $this->connection->exec(
            "
            CREATE TABLE IF NOT EXISTS usuarios (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome VARCHAR(150) NOT NULL,
                usuario VARCHAR(80) NOT NULL UNIQUE,
                senha VARCHAR(255) NOT NULL,
                email VARCHAR(150) NOT NULL,
                criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS posts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                titulo VARCHAR(255) NOT NULL,
                data_postagem DATE NOT NULL,
                link VARCHAR(255) NOT NULL,
                icone VARCHAR(80) NOT NULL DEFAULT 'logo',
                imagem VARCHAR(255) DEFAULT NULL,
                clicks INTEGER NOT NULL DEFAULT 0,
                criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS configuracoes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                chave VARCHAR(100) NOT NULL UNIQUE,
                valor TEXT DEFAULT NULL,
                atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            "
        );

        $totalUsuarios = (int) $this->connection->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
        if ($totalUsuarios === 0) {
            $senhaPadrao = password_hash(senhaPadraoAdmin(), PASSWORD_DEFAULT);
            $this->connection->prepare(
                'INSERT INTO usuarios (nome, usuario, senha, email) VALUES (:nome, :usuario, :senha, :email)'
            )->execute([
                ':nome' => 'Administrador',
                ':usuario' => 'admin',
                ':senha' => $senhaPadrao,
                ':email' => 'admin@sci.local',
            ]);
        }

        $totalPosts = (int) $this->connection->query('SELECT COUNT(*) FROM posts')->fetchColumn();
        if ($totalPosts === 0) {
            $this->connection->exec(
                "INSERT INTO posts (titulo, data_postagem, link, icone, imagem, clicks) VALUES
                ('UNIVERSE OF UI', '2024-01-10', 'https://www.ifto.edu.br/', 'logo', '', 12),
                ('DESIGN SYSTEM', '2024-02-15', 'https://www.ifto.edu.br/', 'clock', '', 24),
                ('CREATIVE TOOLS', '2024-03-22', 'https://www.ifto.edu.br/', 'calendar', '', 31);"
            );
        }

        $configuracoes = [
            'startDate' => '',
            'endDate' => '',
        ];

        foreach ($configuracoes as $chave => $valor) {
            $existe = $this->connection->prepare('SELECT id FROM configuracoes WHERE chave = :chave');
            $existe->execute([':chave' => $chave]);
            if (!$existe->fetch()) {
                $this->connection->prepare('INSERT INTO configuracoes (chave, valor) VALUES (:chave, :valor)')->execute([
                    ':chave' => $chave,
                    ':valor' => $valor,
                ]);
            }
        }
    }
}

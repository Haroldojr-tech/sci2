<?php

/**
 * Classe responsável pelo CRUD dos posts.
 * A estrutura foi traduzida para banco de dados SQL, removendo o uso dos arquivos JSON.
 */
class Post
{
    private PDO $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    public function listar(): array
    {
        $sql = 'SELECT id, titulo, data_postagem, link, icone, imagem, clicks FROM posts ORDER BY id DESC';
        $stmt = $this->database->query($sql);
        $posts = $stmt->fetchAll();

        return array_map(function ($post) {
            return $this->normalizar($post);
        }, $posts);
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = 'SELECT id, titulo, data_postagem, link, icone, imagem, clicks FROM posts WHERE id = :id LIMIT 1';
        $stmt = $this->database->prepare($sql);
        $stmt->execute([':id' => $id]);
        $post = $stmt->fetch();

        if (!$post) {
            return null;
        }

        return $this->normalizar($post);
    }

    public function criar(array $dados): array
    {
        $titulo = trim((string) ($dados['title'] ?? ''));
        if ($titulo === '') {
            throw new InvalidArgumentException('O título do post é obrigatório.');
        }

        $dataPostagem = $dados['postDate'] ?? date('Y-m-d');
        $link = trim((string) ($dados['postLink'] ?? ''));
        $icone = trim((string) ($dados['icon'] ?? 'logo')) ?: 'logo';
        $imagem = trim((string) ($dados['postImage'] ?? ''));

        $sql = 'INSERT INTO posts (titulo, data_postagem, link, icone, imagem, clicks) VALUES (:titulo, :data_postagem, :link, :icone, :imagem, :clicks)';
        $stmt = $this->database->prepare($sql);
        $stmt->execute([
            ':titulo' => $titulo,
            ':data_postagem' => $dataPostagem,
            ':link' => $link,
            ':icone' => $icone,
            ':imagem' => $imagem,
            ':clicks' => 0,
        ]);

        $id = (int) $this->database->lastInsertId();
        return $this->buscarPorId($id) ?? [];
    }

    public function atualizar(int $id, array $dados): ?array
    {
        $postAtual = $this->buscarPorId($id);

        if (!$postAtual) {
            return null;
        }

        $titulo = trim((string) ($dados['title'] ?? $postAtual['title']));
        $dataPostagem = $dados['postDate'] ?? $postAtual['postDate'];
        $link = trim((string) ($dados['postLink'] ?? $postAtual['postLink']));
        $icone = trim((string) ($dados['icon'] ?? $postAtual['icon'])) ?: 'logo';
        $imagem = trim((string) ($dados['postImage'] ?? $postAtual['postImage']));
        $clicks = isset($dados['clicks']) ? (int) $dados['clicks'] : (int) $postAtual['clicks'];

        $sql = 'UPDATE posts SET titulo = :titulo, data_postagem = :data_postagem, link = :link, icone = :icone, imagem = :imagem, clicks = :clicks WHERE id = :id';
        $stmt = $this->database->prepare($sql);
        $stmt->execute([
            ':titulo' => $titulo,
            ':data_postagem' => $dataPostagem,
            ':link' => $link,
            ':icone' => $icone,
            ':imagem' => $imagem,
            ':clicks' => $clicks,
            ':id' => $id,
        ]);

        return $this->buscarPorId($id);
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->database->prepare('DELETE FROM posts WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function incrementarClique(int $id): ?array
    {
        $postAtual = $this->buscarPorId($id);

        if (!$postAtual) {
            return null;
        }

        $novoTotal = (int) $postAtual['clicks'] + 1;
        $stmt = $this->database->prepare('UPDATE posts SET clicks = :clicks WHERE id = :id');
        $stmt->execute([
            ':clicks' => $novoTotal,
            ':id' => $id,
        ]);

        return $this->buscarPorId($id);
    }

    private function normalizar(array $post): array
    {
        return [
            'id' => (int) $post['id'],
            'title' => (string) ($post['titulo'] ?? ''),
            'postDate' => (string) ($post['data_postagem'] ?? ''),
            'postLink' => (string) ($post['link'] ?? ''),
            'icon' => (string) ($post['icone'] ?? 'logo'),
            'postImage' => (string) ($post['imagem'] ?? ''),
            'clicks' => (int) ($post['clicks'] ?? 0),
        ];
    }
}

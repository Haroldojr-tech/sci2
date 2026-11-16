<?php

/**
 * Classe de configuração do site.
 * Armazena os filtros de data usados na exibição dos posts.
 */
class Configuracao
{
    private PDO $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    public function obter(): array
    {
        $sql = 'SELECT chave, valor FROM configuracoes';
        $stmt = $this->database->query($sql);
        $dados = $stmt->fetchAll();
        $resultado = [
            'startDate' => '',
            'endDate' => '',
        ];

        foreach ($dados as $item) {
            if (isset($resultado[$item['chave']])) {
                $resultado[$item['chave']] = $item['valor'];
            }
        }

        return $resultado;
    }

    public function salvar(array $dados): array
    {
        foreach (['startDate', 'endDate'] as $chave) {
            $valor = (string) ($dados[$chave] ?? '');
            $stmt = $this->database->prepare('SELECT id FROM configuracoes WHERE chave = :chave LIMIT 1');
            $stmt->execute([':chave' => $chave]);

            if ($stmt->fetch()) {
                $this->database->prepare('UPDATE configuracoes SET valor = :valor, atualizado_em = CURRENT_TIMESTAMP WHERE chave = :chave')->execute([
                    ':valor' => $valor,
                    ':chave' => $chave,
                ]);
            } else {
                $this->database->prepare('INSERT INTO configuracoes (chave, valor) VALUES (:chave, :valor)')->execute([
                    ':chave' => $chave,
                    ':valor' => $valor,
                ]);
            }
        }

        return $this->obter();
    }
}

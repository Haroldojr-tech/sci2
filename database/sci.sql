CREATE DATABASE IF NOT EXISTS sci DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sci;

DROP TABLE IF EXISTS configuracoes;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    usuario VARCHAR(80) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL,
    tipo ENUM('admin', 'comunicador', 'editor', 'visualizador') NOT NULL DEFAULT 'visualizador',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    data_postagem DATE NOT NULL,
    link VARCHAR(255) NOT NULL,
    icone VARCHAR(80) NOT NULL DEFAULT 'logo',
    imagem VARCHAR(255) DEFAULT NULL,
    clicks INT NOT NULL DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT DEFAULT NULL,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO usuarios (nome, usuario, senha, email, tipo) VALUES
('Administrador', 'admin', '$2y$10$1ZASEC2IhYGLL3yfFVuuJ.kRv0Roinr3den3WmaS2kmewY/D6MK7m', 'admin@sci.local', 'admin'),
('Comunicador', 'comunicador', '$2y$10$1ZASEC2IhYGLL3yfFVuuJ.kRv0Roinr3den3WmaS2kmewY/D6MK7m', 'comunicador@sci.local', 'comunicador'),
('Editor', 'editor', '$2y$10$1ZASEC2IhYGLL3yfFVuuJ.kRv0Roinr3den3WmaS2kmewY/D6MK7m', 'editor@sci.local', 'editor');

INSERT INTO posts (titulo, data_postagem, link, icone, imagem, clicks) VALUES
('UNIVERSE OF UI', '2024-01-10', 'https://www.ifto.edu.br/', 'logo', '', 12),
('DESIGN SYSTEM', '2024-02-15', 'https://www.ifto.edu.br/', 'clock', '', 24),
('CREATIVE TOOLS', '2024-03-22', 'https://www.ifto.edu.br/', 'calendar', '', 31),
('FAST DEVELOPMENT', '2024-04-18', 'https://www.ifto.edu.br/', 'secretary', '', 18);

INSERT INTO configuracoes (chave, valor) VALUES
('startDate', ''),
('endDate', '');

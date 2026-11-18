-- Kanbanbord database schema
-- Tabellen: boards, columns, cards
-- Charset utf8mb4 voor volledige emoji/unicode-ondersteuning, InnoDB voor foreign keys

CREATE TABLE boards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE columns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    board_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    position INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_columns_board
        FOREIGN KEY (board_id) REFERENCES boards(id)
        ON DELETE CASCADE,
    INDEX idx_columns_board_position (board_id, position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    column_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    position INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_cards_column
        FOREIGN KEY (column_id) REFERENCES columns(id)
        ON DELETE CASCADE,
    INDEX idx_cards_column_position (column_id, position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO boards (id, name, slug, created_at, updated_at)
VALUES (1, 'Mijn werk', 'main', NOW(), NOW());

INSERT INTO columns (board_id, name, position, created_at, updated_at) VALUES
    (1, 'Te doen', 0, NOW(), NOW()),
    (1, 'Bezig', 1, NOW(), NOW()),
    (1, 'Afgerond', 2, NOW(), NOW());

INSERT INTO cards (column_id, title, description, position, created_at, updated_at) VALUES
    (
        (SELECT id FROM columns WHERE board_id = 1 AND name = 'Te doen'),
        'Nieuwe portfolio-content',
        'Projectbeschrijvingen voor de hoofdpagina bijwerken.',
        0,
        NOW(),
        NOW()
    ),
    (
        (SELECT id FROM columns WHERE board_id = 1 AND name = 'Bezig'),
        'Contactformulier testen',
        'Controleer of de verzending in productie werkt.',
        0,
        NOW(),
        NOW()
    ),
    (
        (SELECT id FROM columns WHERE board_id = 1 AND name = 'Afgerond'),
        'Adminomgeving opleveren',
        'Beheerscherm en kanbanbord zijn beschikbaar.',
        0,
        NOW(),
        NOW()
    );
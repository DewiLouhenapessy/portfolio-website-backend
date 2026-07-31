-- Kanbanbord database schema
-- Tabellen: boards, columns, cards
-- Charset utf8mb4 voor volledige emoji/unicode-ondersteuning, InnoDB voor foreign keys

CREATE TABLE boards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
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
    INDEX idx_columns_board_id (board_id)
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
    INDEX idx_cards_column_id (column_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optioneel: meteen een board met standaard kolommen aanmaken
-- (verwijder of pas aan naar wens)

INSERT INTO boards (id, name, created_at, updated_at)
VALUES (1, 'Mijn werk', NOW(), NOW());

INSERT INTO columns (board_id, name, position, created_at, updated_at) VALUES
    (1, 'Idee',    0, NOW(), NOW()),
    (1, 'Te doen', 1, NOW(), NOW()),
    (1, 'Bezig',   2, NOW(), NOW()),
    (1, 'Klaar',   3, NOW(), NOW());
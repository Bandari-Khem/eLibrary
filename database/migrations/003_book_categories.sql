-- eLibrary v1 — Migration 003
-- Purpose: replace books.category_id (one category) with book_categories (many-to-many).
-- Run this against an existing database only after making a database backup.

SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS book_categories (
    book_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (book_id, category_id),
    INDEX idx_book_categories_category (category_id),
    CONSTRAINT fk_book_categories_book
        FOREIGN KEY (book_id) REFERENCES books(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_book_categories_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Preserve every existing book-to-category assignment before removing the legacy column.
INSERT IGNORE INTO book_categories (book_id, category_id)
SELECT id, category_id
FROM books
WHERE category_id IS NOT NULL;

-- Remove the old one-to-one relationship after the data has been copied.
ALTER TABLE books DROP FOREIGN KEY fk_books_category;
ALTER TABLE books DROP INDEX idx_books_category;
ALTER TABLE books DROP COLUMN category_id;

SET FOREIGN_KEY_CHECKS = 1;

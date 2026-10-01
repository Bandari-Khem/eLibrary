-- ============================================================
-- E-LIBRARY SYSTEM
-- Complete Database Schema
-- Version: 1.0
-- Purpose: BCA project + portfolio foundation
-- ============================================================

SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- Drop tables in dependency-safe order
-- ------------------------------------------------------------
DROP TABLE IF EXISTS review_reports;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS review_ratings;
DROP TABLE IF EXISTS downloads;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS bookmarks;
DROP TABLE IF EXISTS reading_history;
DROP TABLE IF EXISTS reading_progress;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS book_categories;
DROP TABLE IF EXISTS book_authors;
DROP TABLE IF EXISTS book_files;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS authors;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS faq;
DROP TABLE IF EXISTS library_settings;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 1. USERS
-- ============================================================
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,

    role ENUM('user', 'librarian', 'admin')
        NOT NULL DEFAULT 'user',

    status ENUM('active', 'inactive', 'suspended')
        NOT NULL DEFAULT 'active',

    avatar VARCHAR(255) NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_users_role (role),
    INDEX idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. CATEGORIES
-- Supports parent/child category hierarchy
-- ============================================================
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    parent_id INT UNSIGNED NULL,
    description TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_category_name_parent (name, parent_id),
    INDEX idx_categories_parent (parent_id),

    CONSTRAINT fk_categories_parent
        FOREIGN KEY (parent_id)
        REFERENCES categories(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. AUTHORS
-- ============================================================
CREATE TABLE authors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    bio TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_authors_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. BOOKS
-- ============================================================
CREATE TABLE books (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    title VARCHAR(255) NOT NULL,
    slug VARCHAR(280) NOT NULL UNIQUE,
    description TEXT NULL,

    publisher VARCHAR(150) NULL,
    publication_year YEAR NULL,
    isbn VARCHAR(30) NULL,
    language VARCHAR(50) NOT NULL DEFAULT 'English',

    cover_image VARCHAR(255) NULL,

    status ENUM('draft', 'published', 'archived')
        NOT NULL DEFAULT 'published',

    added_by INT UNSIGNED NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_books_status (status),
    INDEX idx_books_title (title),
    INDEX idx_books_added_by (added_by),

    CONSTRAINT fk_books_added_by
        FOREIGN KEY (added_by)
        REFERENCES users(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. BOOK FILES
-- Physical electronic files belonging to a book
-- ============================================================
CREATE TABLE book_files (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    book_id INT UNSIGNED NOT NULL,

    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,

    file_type ENUM('pdf', 'epub', 'mobi', 'other')
        NOT NULL DEFAULT 'pdf',

    file_size BIGINT UNSIGNED NOT NULL,
    file_hash CHAR(64) NULL,

    is_main BOOLEAN NOT NULL DEFAULT TRUE,

    uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_book_files_book (book_id),
    INDEX idx_book_files_type (file_type),

    CONSTRAINT fk_book_files_book
        FOREIGN KEY (book_id)
        REFERENCES books(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. BOOK AUTHORS
-- Many-to-many relationship between books and authors
-- ============================================================
CREATE TABLE book_authors (
    book_id INT UNSIGNED NOT NULL,
    author_id INT UNSIGNED NOT NULL,

    PRIMARY KEY (book_id, author_id),

    CONSTRAINT fk_book_authors_book
        FOREIGN KEY (book_id)
        REFERENCES books(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_book_authors_author
        FOREIGN KEY (author_id)
        REFERENCES authors(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. BOOK CATEGORIES
-- Many-to-many relationship between books and categories
-- A book may belong to multiple academic or general categories.
-- ============================================================
CREATE TABLE book_categories (
    book_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,

    PRIMARY KEY (book_id, category_id),
    INDEX idx_book_categories_category (category_id),

    CONSTRAINT fk_book_categories_book
        FOREIGN KEY (book_id)
        REFERENCES books(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_book_categories_category
        FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. READING PROGRESS
-- One progress record per user/book
-- ============================================================
CREATE TABLE reading_progress (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL,
    book_id INT UNSIGNED NOT NULL,

    current_page INT UNSIGNED NOT NULL DEFAULT 1,
    total_pages INT UNSIGNED NULL,

    last_read_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_progress_user_book (user_id, book_id),
    INDEX idx_progress_book (book_id),
    INDEX idx_progress_last_read (last_read_at),

    CONSTRAINT fk_progress_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_progress_book
        FOREIGN KEY (book_id)
        REFERENCES books(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. READING HISTORY
-- ============================================================
CREATE TABLE reading_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL,
    book_id INT UNSIGNED NOT NULL,

    action ENUM('read', 'download')
        NOT NULL DEFAULT 'read',

    read_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_history_user (user_id),
    INDEX idx_history_book (book_id),
    INDEX idx_history_date (read_at),

    CONSTRAINT fk_history_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_history_book
        FOREIGN KEY (book_id)
        REFERENCES books(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. BOOKMARKS
-- ============================================================
CREATE TABLE bookmarks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL,
    book_id INT UNSIGNED NOT NULL,

    page_number INT UNSIGNED NOT NULL,
    note VARCHAR(500) NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_bookmark_user_book_page
        (user_id, book_id, page_number),

    INDEX idx_bookmarks_user (user_id),
    INDEX idx_bookmarks_book (book_id),

    CONSTRAINT fk_bookmarks_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_bookmarks_book
        FOREIGN KEY (book_id)
        REFERENCES books(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10. FAVORITES
-- ============================================================
CREATE TABLE favorites (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL,
    book_id INT UNSIGNED NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_favorite_user_book (user_id, book_id),

    INDEX idx_favorites_user (user_id),
    INDEX idx_favorites_book (book_id),

    CONSTRAINT fk_favorites_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_favorites_book
        FOREIGN KEY (book_id)
        REFERENCES books(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 11. DOWNLOADS
-- ============================================================
CREATE TABLE downloads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL,
    book_id INT UNSIGNED NOT NULL,
    file_id INT UNSIGNED NOT NULL,

    downloaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_downloads_user (user_id),
    INDEX idx_downloads_book (book_id),
    INDEX idx_downloads_file (file_id),
    INDEX idx_downloads_date (downloaded_at),

    CONSTRAINT fk_downloads_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_downloads_book
        FOREIGN KEY (book_id)
        REFERENCES books(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_downloads_file
        FOREIGN KEY (file_id)
        REFERENCES book_files(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 12. REVIEW RATINGS
-- Meaning of each star rating
-- ============================================================
CREATE TABLE review_ratings (
    rating TINYINT UNSIGNED PRIMARY KEY,
    label VARCHAR(50) NOT NULL,
    meaning VARCHAR(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO review_ratings (rating, label, meaning) VALUES
(1, 'Very Poor', 'The book did not meet expectations.'),
(2, 'Poor', 'The book had several weaknesses.'),
(3, 'Average', 'The book was satisfactory overall.'),
(4, 'Good', 'The book was useful and enjoyable.'),
(5, 'Excellent', 'The book was highly useful and strongly recommended.');

-- ============================================================
-- 13. REVIEWS
-- ============================================================
CREATE TABLE reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL,
    book_id INT UNSIGNED NOT NULL,

    rating TINYINT UNSIGNED NOT NULL,

    review_reason VARCHAR(150) NULL,
    review_text TEXT NULL,

    status ENUM('pending', 'approved', 'hidden')
        NOT NULL DEFAULT 'pending',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_review_user_book (user_id, book_id),

    INDEX idx_reviews_book (book_id),
    INDEX idx_reviews_user (user_id),
    INDEX idx_reviews_status (status),
    INDEX idx_reviews_rating (rating),

    CONSTRAINT chk_review_rating
        CHECK (rating BETWEEN 1 AND 5),

    CONSTRAINT fk_reviews_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_reviews_book
        FOREIGN KEY (book_id)
        REFERENCES books(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_reviews_rating
        FOREIGN KEY (rating)
        REFERENCES review_ratings(rating)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 14. REVIEW REPORTS
-- ============================================================
CREATE TABLE review_reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    review_id BIGINT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,

    reason VARCHAR(255) NOT NULL,

    status ENUM('pending', 'reviewed', 'dismissed')
        NOT NULL DEFAULT 'pending',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_review_report_user (review_id, user_id),

    INDEX idx_review_reports_status (status),
    INDEX idx_review_reports_review (review_id),

    CONSTRAINT fk_review_reports_review
        FOREIGN KEY (review_id)
        REFERENCES reviews(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_review_reports_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 15. ACTIVITY LOGS
-- ============================================================
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NULL,

    action VARCHAR(100) NOT NULL,

    target_type VARCHAR(50) NULL,
    target_id BIGINT UNSIGNED NULL,

    details TEXT NULL,

    ip_address VARCHAR(45) NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_activity_user (user_id),
    INDEX idx_activity_action (action),
    INDEX idx_activity_target (target_type, target_id),
    INDEX idx_activity_date (created_at),

    CONSTRAINT fk_activity_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 16. NOTIFICATIONS
-- ============================================================
CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NULL,

    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,

    is_read BOOLEAN NOT NULL DEFAULT FALSE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_notifications_user (user_id),
    INDEX idx_notifications_read (is_read),
    INDEX idx_notifications_date (created_at),

    CONSTRAINT fk_notifications_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 17. LIBRARY SETTINGS
-- ============================================================
CREATE TABLE library_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Basic settings
INSERT INTO library_settings (setting_key, setting_value) VALUES
('library_name', 'E-Library'),
('maintenance_mode', '0'),
('max_upload_mb', '20'),
('contact_email', ''),
('allowed_file_types', 'pdf');

-- ============================================================
-- 18. CONTACT MESSAGES
-- ============================================================
CREATE TABLE contact_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,

    status ENUM('unread', 'read', 'replied')
        NOT NULL DEFAULT 'unread',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_contact_status (status),
    INDEX idx_contact_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 19. FAQ
-- ============================================================
CREATE TABLE faq (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    question VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL,

    display_order INT NOT NULL DEFAULT 0,
    status BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_faq_order (display_order),
    INDEX idx_faq_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- OPTIONAL STARTER FAQ DATA
-- ============================================================
INSERT INTO faq (question, answer, display_order, status) VALUES
('What is E-Library?', 'E-Library is a web-based platform for browsing, reading and managing digital books.', 1, TRUE),
('Do I need an account to browse books?', 'No. Public users can browse the catalogue and view book information without logging in.', 2, TRUE),
('Do I need an account to read or download books?', 'Yes. Reading, downloading and personal library features require an authenticated account.', 3, TRUE),
('Can I leave a review for a book?', 'Yes. Logged-in users can rate a book from 1 to 5 stars and submit a review.', 4, TRUE);

-- ============================================================
-- END OF E-LIBRARY DATABASE SCHEMA
-- ============================================================

-- C-15 profile support
-- Apply database/migrations/004_profile_and_lifecycle.sql to existing databases.

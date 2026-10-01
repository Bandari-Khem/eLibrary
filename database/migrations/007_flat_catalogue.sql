-- eLibrary v1 — Remove optional pages and flatten the book catalogue.
-- Run after migrations 005 and 006, after making a database backup.

-- Remove the old self-reference first so deleting a grouping entry does not
-- cascade-delete the subject categories beneath it.
ALTER TABLE categories
    DROP FOREIGN KEY fk_categories_parent,
    DROP INDEX uq_category_name_parent,
    DROP INDEX idx_categories_parent;

-- Remove legacy grouping entries while preserving subject categories and books.
DELETE bc
FROM book_categories bc
JOIN categories c ON c.id = bc.category_id
WHERE c.name IN ('Academic', 'BCA')
   OR c.name REGEXP '^[0-9]+(st|nd|rd|th)SEM$';

DELETE FROM categories
WHERE name IN ('Academic', 'BCA')
   OR name REGEXP '^[0-9]+(st|nd|rd|th)SEM$';

-- Keep the existing subjects as a flat catalogue list.
UPDATE categories SET parent_id = NULL;
ALTER TABLE categories
    DROP COLUMN parent_id,
    ADD INDEX idx_categories_name (name);

-- These features are outside the agreed student-project scope.
DROP TABLE IF EXISTS faq;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS library_settings;
DROP TABLE IF EXISTS review_ratings;

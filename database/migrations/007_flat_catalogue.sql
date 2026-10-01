-- eLibrary v1 — Remove optional pages and flatten the book catalogue.
-- Run after migrations 005 and 006, after making a database backup.

-- Remove the old self-reference first so deleting a grouping entry does not
-- cascade-delete the subject categories beneath it.
ALTER TABLE categories
    DROP FOREIGN KEY fk_categories_parent,
    DROP INDEX uq_category_name_parent,
    DROP INDEX idx_categories_parent;

-- Keep the existing subjects as a flat catalogue list.
UPDATE categories SET parent_id = NULL;
ALTER TABLE categories
    DROP COLUMN parent_id,
    ADD INDEX idx_categories_name (name);

-- Remove the old grouping rows only after removing the self-reference.
DELETE bc
FROM book_categories bc
JOIN categories c ON c.id = bc.category_id
WHERE c.name IN ('Academic', 'BCA')
   OR c.name REGEXP '^[0-9]+(st|nd|rd|th)SEM$';

DELETE FROM categories
WHERE name IN ('Academic', 'BCA')
   OR name REGEXP '^[0-9]+(st|nd|rd|th)SEM$';

-- These features are outside the agreed student-project scope.
DROP TABLE IF EXISTS faq;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS library_settings;

-- Older installations linked ratings to the now-unneeded label lookup table.
SET @drop_rating_fk = (
    SELECT IF(COUNT(*) > 0,
        'ALTER TABLE reviews DROP FOREIGN KEY fk_reviews_rating',
        'SELECT 1')
    FROM information_schema.REFERENTIAL_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'reviews'
      AND CONSTRAINT_NAME = 'fk_reviews_rating'
);
PREPARE drop_rating_fk_stmt FROM @drop_rating_fk;
EXECUTE drop_rating_fk_stmt;
DEALLOCATE PREPARE drop_rating_fk_stmt;

DROP TABLE IF EXISTS review_ratings;

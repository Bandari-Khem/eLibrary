-- eLibrary: simplify the learner feature set for the BCA fourth-semester project.
-- Back up the database before applying: this intentionally removes data held by
-- retired bookmark, notification, review-report and duplicate download tables. Reviews are
-- retained as a simple learner feature.

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS review_reports;
DROP TABLE IF EXISTS bookmarks;
DROP TABLE IF EXISTS downloads;
DROP TABLE IF EXISTS notifications;
SET FOREIGN_KEY_CHECKS = 1;

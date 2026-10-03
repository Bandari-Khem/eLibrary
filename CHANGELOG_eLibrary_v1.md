# eLibrary v1 — Scope and Change Record

## Academic fit

The project targets TU BCA 2024 batch, Fourth Semester, Project I. Its implementation uses a small PHP and MariaDB web application to demonstrate scripting, database operations, CRUD, system analysis/design and testing concepts. Feature selection is based on the agreed student-project scope rather than production-library requirements.

## Current agreed features

- Public home page, About page, category catalogue, database-backed category selection, and book details.
- Mobile-first presentation and responsive navigation.
- Three roles: learner (`user` in the database), librarian, and administrator (`admin` in the database).
- Learner authentication, profile, favourites, PDF reading, reading progress/history, and book reviews/ratings.
- Librarian book add/edit/delete, author management, category management, and individual PDF upload.
- Administrator user/role management and basic activity monitoring.
- Home discovery areas for trending reads and highly rated books, plus recent reader review messages.
- Twelve core tables: `users`, `categories`, `authors`, `books`, `book_files`, `book_authors`, `book_categories`, `reading_progress`, `reading_history`, `favorites`, `reviews`, and `activity_logs`.

## Scope decisions

- Catalogue subjects are presented as a flat list (for example, C Programming and Scripting Language), not as semester navigation.
- The currently implemented reading/upload flow supports PDF. EPUB and DOCX should not be claimed as implemented.
- FAQ, bookmarks, notification features, advanced recommendations, advanced analytics, bulk upload, and physical-library functions are outside the agreed implementation scope.
- The existing About page contains Services and references; the footer links to the Services section.
- The live demo database has incomplete catalogue relationships; see `TESTING_eLibrary_v1.md` before interpreting catalogue test results.

## 2026-10-02 reliability updates

- Fixed the librarian profile page's invalid include path and added editable name/email fields with duplicate-address checks.
- Added learner email editing with uniqueness and syntax validation; updated both profile screens and the registration form to explain that mailbox ownership is not verified.
- Improved cover-upload feedback for invalid image files and detected when a selected cover does not reach the server. The cover form now records file-selection intent so this case is visible rather than silently treated as an optional empty cover.
- Restricted favourite actions to learners in both the book page and the server handler; librarians/admins receive a clear explanation and cannot alter favourite records.
- Updated Trending Reads and Loved by Learners to fetch up to four books; small screens show up to two, wider laptop/desktop screens show up to four, and available cards are horizontally centered.
- Kept existing profile icons; passwords remain managed through Account Settings.

## Recent implementation history

- `673899a` Preserve flat catalogue subjects.
- `14da986` Focus category book listings.
- `3ad55be` Fix librarian book index query.
- `b1c6bae` Add librarian book deletion.
- `ecd2ce5` Reshape catalogue flow for mobile-first browsing.

## Verification status

Syntax, route, role, learner workflow, librarian CRUD/file, database integrity, and responsive/source checks are recorded in `TESTING_eLibrary_v1.md`. The team has also confirmed that the visual viewport, multi-page PDF/progress, administrator, and exact-search follow-up checks passed. Their detailed execution evidence was not supplied. These are project-level functional checks, not a claim of exhaustive production, load, or security testing.

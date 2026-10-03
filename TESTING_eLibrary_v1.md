# eLibrary v1 — Test Report

## Project context

Testing is scoped to the TU BCA 2024 batch Project I and fourth-semester web/database coursework: a small database-backed catalogue with role-based pages, CRUD operations, and learner activity. This is functional project testing, not production load or security certification.

## Test environment

| Component | Environment |
|---|---|
| Application | Current workspace copy of eLibrary |
| PHP | 8.2.12 |
| Database | MariaDB 10.4.32; isolated database `elib_bca_test_20261002` |
| HTTP server | PHP development server on `127.0.0.1:8098` |
| Test data | Synthetic learner, librarian, administrator, books, categories, reviews and reading records |

The test server used a session directory inside the workspace because the configured XAMPP temporary session directory was inaccessible to the test process. The live `dlibrary` database was not used for write tests.

## Completed checks

| ID | Check | Result |
|---|---|---|
| ST-01 | Syntax-check every PHP source file | PASS — 49 PHP files; no syntax errors. |
| ST-02 | Public page smoke checks: home, catalogue, About, login, registration, Privacy, Terms, book detail, category list/view and category search route | PASS — all returned HTTP 200. |
| ST-03 | Anonymous protection for learner, librarian and administrator pages | PASS — protected pages redirected to login. |
| AUTH-01 | Learner, librarian and administrator login and their role dashboards/profile | PASS — each test account reached its permitted page. |
| AUTH-02 | Learner opening librarian pages; librarian opening administrator dashboard | PASS — both received HTTP 403. |
| CAT-01 | Database-backed category catalogue and search route | PASS — routes rendered with seeded categories and linked books. |
| LEARN-01 | Learner favourite add and remove | PASS — favourite row was created and removed. |
| LEARN-02 | Librarian and administrator attempting the favourite POST directly | PASS — both were denied; no favourite row was added. |
| LEARN-03 | Review submission/update | PASS — the unique learner/book review was updated without creating a duplicate. |
| LEARN-04 | Reading progress API | PASS — valid progress saved; a page beyond total pages returned HTTP 422. |
| LEARN-05 | Reader page and protected PDF download | PASS — reader route rendered; PDF download returned HTTP 200 and added a download-history record. |
| LIB-01 | Librarian profile display and update | PASS — name and email were saved and the updated profile was shown. |
| LIB-02 | Book create with PDF and cover, edit, then delete | PASS — multipart upload stored the cover and PDF; edit saved; delete removed the book and its uploaded files. |
| DB-01 | Schema table count | PASS — `database/schema.sql` creates 12 tables. |
| DB-02 | Test database relationships | PASS — no orphaned book/category, book/author, or book/file rows; each published fixture book had its required category and author. |
| UI-01 | Discovery section query and responsive CSS rules | PASS by source inspection — each landing section requests up to four books; mobile/tablet rules show two centered cards, and the laptop breakpoint reveals up to four. |
| UI-02 | Visual viewport check at phone, tablet and desktop widths, including navigation and overflow | PASS — the team confirmed all three viewport checks passed and the layout/navigation/cards were usable. Exact viewport measurements and screenshots were not provided. |
| BOOK-01 | Render valid multi-page PDF through PDF.js and verify saved progress from page navigation | PASS — the team confirmed PDF.js rendered the multi-page PDF and saved progress followed reader navigation. Fixture details/page counts were not provided. |
| ADMIN-01 | Administrator user/role management and activity-monitoring interactions | PASS — the team confirmed the administrator actions and activity view behaved as expected. Exact action-by-action notes were not provided. |
| CAT-02 | Confirm search result contents for title, author and category combinations | PASS — the team confirmed search results matched the selected criteria. Exact query strings and returned fixture rows were not provided. |
| ST-04 | Whitespace/diff check | PASS — no whitespace errors reported. |

## Follow-up checks completed

The team subsequently confirmed UI-02, BOOK-01, ADMIN-01, and CAT-02 passed. This status update records the team's confirmation; detailed measurements, screenshots, fixture metadata, query strings, and action-by-action traces were not supplied for independent reproduction.

## Notes

All write tests ran against the isolated test database and synthetic accounts. The live database and user uploads were left unchanged. The first test-server launch could not persist sessions because of local filesystem permissions; using an isolated workspace session directory resolved that harness issue. It was not an application defect.

The email field validates address syntax; it does not prove that a mailbox exists or belongs to the user. A syntactically valid but mistyped domain therefore cannot be identified without mailbox verification.

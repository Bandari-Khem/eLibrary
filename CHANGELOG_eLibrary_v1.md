# eLibrary v1 — Change Completion Record

## Completed baseline changes

- C-01 — Many-to-many book categories via `book_categories`.
- C-02 — Homepage discovery: Editors' Favorites, Recently Added, Top Trending.
- C-03 — BCA → Semester → Subject navigation.
- C-04 — Searchable category selector in the public catalogue.
- C-05 — Responsive mobile book grid with two cards per row at narrow widths.
- C-06 — Navigation closes on outside click and Escape.
- C-07 — Optional book cover upload/replacement with server-side image validation.
- C-08 — Author editing for name and biography.
- C-09 — Stricter email syntax validation and Forgot Password entry point.
- C-10 — Profile member-since information and controlled avatar selection; role hidden.
- C-11 — Searchable/filterable administrative activity logs and broader action logging.
- C-12 — Archive-first book lifecycle with restore and admin-only permanent deletion of archived books, including physical file cleanup.
- C-13 — Structured About page with design references and up to six real approved reviews.
- C-14 — Clear Favourite and page-level Bookmark state feedback.
- C-15 — Secure logged-in password change from Account Settings.

## Runtime boundary

Source-level implementation and PHP syntax validation are complete. A configured MySQL/MariaDB runtime is required before browser/database test cases can be marked PASS.

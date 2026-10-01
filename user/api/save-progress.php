<?php require_once __DIR__ . '/../../includes/auth.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Method not allowed'], 405);
$raw = json_decode(file_get_contents('php://input'), true) ?? [];
verify_csrf($raw['csrf_token'] ?? '');
$uid = (int)current_user()['id'];
$book = (int)($raw['book_id'] ?? 0);
$page = (int)($raw['current_page'] ?? 0);
$total = isset($raw['total_pages']) ? (int)$raw['total_pages'] : null;
if ($page < 1 || ($total !== null && ($total < 1 || $page > $total))) {
    json_response(['error' => 'Invalid page'], 422);
}
$s = db()->prepare("SELECT id FROM books WHERE id=? AND status='published'");
$s->execute([$book]);
if (!$s->fetch()) json_response(['error' => 'Book not found'], 404);
$s = db()->prepare('INSERT INTO reading_progress(user_id,book_id,current_page,total_pages,last_read_at) 
    VALUES(?,?,?,?,CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE current_page=VALUES(current_page),total_pages=VALUES(total_pages),last_read_at=CURRENT_TIMESTAMP');
$s->execute([$uid, $book, $page, $total]);
$h = db()->prepare('INSERT INTO reading_history(user_id,book_id,action) VALUES(?,?,\'read\')');
$h->execute([$uid, $book]);
json_response(['ok' => true]);

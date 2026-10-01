<?php

declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}
function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}
function get_flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}
function old(string $key, string $default = ''): string
{
    return e($_POST[$key] ?? $default);
}
function client_ip(): string
{
    return substr($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 0, 45);
}
function log_activity(?int $userId, string $action, ?string $targetType = null, ?int $targetId = null, ?string $details = null): void
{
    try {
        $s = db()->prepare('INSERT INTO activity_logs (user_id,action,target_type,target_id,details,ip_address) VALUES (?,?,?,?,?,?)');
        $s->execute([$userId, $action, $targetType, $targetId, $details, client_ip()]);
    } catch (Throwable $e) {
    }
}
function setting(string $key, mixed $default = null): mixed
{
    static $cache = [];
    if (array_key_exists($key, $cache)) return $cache[$key];
    try {
        $s = db()->prepare('SELECT setting_value FROM library_settings WHERE setting_key=?');
        $s->execute([$key]);
        $v = $s->fetchColumn();
        return $cache[$key] = ($v === false ? $default : $v);
    } catch (Throwable $e) {
        return $default;
    }
}
function is_maintenance(): bool
{
    return setting('maintenance_mode', '0') === '1';
}
function slugify(string $text): string
{
    $text = trim($text);
    $text = preg_replace('/[^\pL\pN]+/u', '-', $text) ?? '';
    $text = trim($text, '-');
    $text = function_exists('mb_strtolower') ? mb_strtolower($text) : strtolower($text);
    return $text ?: 'book';
}
function unique_slug(string $title, ?int $ignoreId = null): string
{
    $base = slugify($title);
    $slug = $base;
    $i = 2;
    while (true) {
        $sql = 'SELECT id FROM books WHERE slug=?';
        $p = [$slug];
        if ($ignoreId) {
            $sql .= ' AND id<>?';
            $p[] = $ignoreId;
        }
        $s = db()->prepare($sql);
        $s->execute($p);
        if (!$s->fetch()) return $slug;
        $slug = $base . '-' . $i++;
    }
}
function paginate(int $page, int $perPage, int $total): array
{
    $pages = max(1, (int)ceil($total / $perPage));
    $page = max(1, min($page, $pages));
    return [$page, $pages, ($page - 1) * $perPage];
}
function allowed_file_types(): array
{
    // The reader in this student project supports PDF only.
    return ['pdf'];
}
function file_type_from_ext(string $ext): string
{
    return strtolower($ext) === 'pdf' ? 'pdf' : 'other';
}
function format_bytes(int $bytes): string
{
    $u = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    $n = $bytes;
    while ($n >= 1024 && $i < count($u) - 1) {
        $n /= 1024;
        $i++;
    }
    return number_format($n, $i ? 1 : 0) . ' ' . $u[$i];
}
function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method Not Allowed');
    }
}
function require_admin_or_librarian(): void
{
    require_login();
    if (!in_array(current_user()['role'] ?? '', ['admin', 'librarian'], true)) {
        redirect('403.php');
    }
}
function require_admin(): void
{
    require_login();
    if ((current_user()['role'] ?? '') !== 'admin') {
        redirect('403.php');
    }
}
function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function cover_upload_types(): array
{
    return ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
}
function save_cover_upload(?array $file): ?string
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK)
        throw new RuntimeException('Cover upload failed.');

    if (($file['size'] ?? 0) > 5 * 1024 * 1024)
        throw new RuntimeException('Cover image must be 5 MB or smaller.');
    $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
    $types = cover_upload_types();
    if (!isset($types[$ext]))
        throw new RuntimeException('Cover must be JPG, PNG or WebP.');
    $info = @getimagesize($file['tmp_name']);
    if (!$info || !isset($info['mime']) || $info['mime'] !== $types[$ext])
        throw new RuntimeException('Uploaded cover is not a valid image.');
    if (!is_dir(UPLOAD_COVER_DIR) && !mkdir(UPLOAD_COVER_DIR, 0755, true) && !is_dir(UPLOAD_COVER_DIR))
        throw new RuntimeException('Cover directory unavailable.');
    $stored = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest = UPLOAD_COVER_DIR . DIRECTORY_SEPARATOR . $stored;
    if (!move_uploaded_file($file['tmp_name'], $dest))
        throw new RuntimeException('Could not save cover image.');
    return UPLOAD_COVER_WEB_PATH . '/' . $stored;
}
function delete_stored_file(?string $relative): void
{
    if (!$relative) return;
    $relative = ltrim(str_replace(['\\', '..'], ['/', ''], $relative), '/');
    $root = rtrim(str_replace('\\', '/', APP_ROOT), '/') . '/';
    $path = $root . $relative;
    if (is_file($path)) @unlink($path);
}

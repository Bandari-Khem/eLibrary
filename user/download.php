<?php require_once __DIR__ . '/../includes/auth.php';
require_login();
$fid = (int)($_GET['file'] ?? 0);
$inline = isset($_GET['inline']);
$s = db()->prepare("SELECT bf.*,b.title,b.status FROM book_files bf JOIN books b ON b.id=bf.book_id WHERE bf.id=? AND b.status='published'");
$s->execute([$fid]);
$f = $s->fetch();
if (!$f) {
    http_response_code(404);
    exit('File not found');
}
$path = APP_ROOT . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $f['file_path']);
$real = realpath($path);
$uploadRoot = realpath(UPLOAD_DIR);
if (!$real || !$uploadRoot || strpos($real, $uploadRoot . DIRECTORY_SEPARATOR) !== 0 || !is_file($real)) {
    http_response_code(404);
    exit('File unavailable');
}
$mime = $f['file_type'] === 'pdf' ? 'application/pdf' : ($f['file_type'] === 'epub' ? 'application/epub+zip' : 'application/octet-stream');
$size = filesize($real);
$start = 0;
$end = $size - 1;
if (isset($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $m)) {
    if ($m[1] === '' && $m[2] !== '') {
        $length = (int)$m[2];
        $start = max(0, $size - $length);
    } else {
        $start = (int)$m[1];
        if ($m[2] !== '') $end = min($end, (int)$m[2]);
    }
    if ($start > $end || $start >= $size) {
        header('Content-Range: bytes */' . $size);
        http_response_code(416);
        exit;
    }
    http_response_code(206);
    header("Content-Range: bytes $start-$end/$size");
} else {
    http_response_code(200);
}
$length = $end - $start + 1;
header('Content-Type: ' . $mime);
header('Content-Length: ' . $length);
header('Accept-Ranges: bytes');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
$name = preg_replace('/[^A-Za-z0-9._ -]/', '_', basename($f['file_name']));
header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $name . '"');
if (!$inline) {
    $s = db()->prepare('INSERT INTO downloads(user_id,book_id,file_id) VALUES(?,?,?)');
    $s->execute([(int)current_user()['id'], (int)$f['book_id'], (int)$f['id']]);
    $s = db()->prepare('INSERT INTO reading_history(user_id,book_id,action) VALUES(?,?,\'download\')');
    $s->execute([(int)current_user()['id'], (int)$f['book_id']]);
    log_activity((int)current_user()['id'], 'download', 'book', (int)$f['book_id']);
}
while (ob_get_level()) ob_end_clean();
$fp = fopen($real, 'rb');
fseek($fp, $start);
$remaining = $length;
while ($remaining > 0 && !feof($fp)) {
    $buf = fread($fp, min(1024 * 1024, $remaining));
    echo $buf;
    $remaining -= strlen($buf);
    flush();
}
fclose($fp);
exit;

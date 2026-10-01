<?php $pageTitle = 'Settings';
require __DIR__ . '/../../includes/admin-layout.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $allowed = ['library_name', 'maintenance_mode', 'max_upload_mb', 'contact_email', 'allowed_file_types'];
    foreach ($allowed as $key) {
        $val = trim((string)($_POST[$key] ?? ''));
        if ($key === 'maintenance_mode') $val = isset($_POST[$key]) ? '1' : '0';
        if ($key === 'max_upload_mb') $val = (string)max(1, min(200, (int)$val));
        if ($key === 'contact_email' && $val !== '' && !filter_var($val, FILTER_VALIDATE_EMAIL)) continue;
        if ($key === 'allowed_file_types') {
            $parts = array_intersect(['pdf', 'epub', 'mobi'], array_map('strtolower', array_map('trim', explode(',', $val))));
            $val = implode(',', $parts ?: ['pdf']);
        }
        $s = db()->prepare('INSERT INTO library_settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
        $s->execute([$key, $val]);
    }
    log_activity((int)current_user()['id'], 'update_settings');
    flash('success', 'Settings saved.');
    redirect('admin/settings/index.php');
}
$keys = ['library_name', 'maintenance_mode', 'max_upload_mb', 'contact_email', 'allowed_file_types']; ?>
<section class="section">
    <div class="form-card">
        <h1>Library Settings</h1>
        <form method="post"><?= csrf_field() ?><div class="field"><label>Library name</label><input name="library_name"
                    value="<?= e((string)setting('library_name', 'E-Library')) ?>"></div>
            <div class="field">
                <label>Contact email</label>
                <input type="email" name="contact_email" value="<?= e((string)setting('contact_email', '')) ?>">
            </div>
            <div class="field">
                <label>Max upload size (MB)</label>
                <input type="number" min="1" max="200" name="max_upload_mb"
                    value="<?= e((string)setting('max_upload_mb', 20)) ?>">
            </div>
            <div class="field">
                <label>Allowed file types</label>
                <input name="allowed_file_types" value="<?= e((string)setting('allowed_file_types', 'pdf')) ?>"><small
                    class="muted">Comma-separated:
                    pdf, epub, mobi</small>
            </div>
            <div class="field">
                <label>
                    <input type="checkbox" name="maintenance_mode"
                        <?= setting('maintenance_mode', '0') === '1' ? 'checked' : '' ?>> Maintenance mode</label>
            </div>
            <button class="btn">Save settings</button>
        </form>
    </div>
</section><?php require __DIR__ . '/../../includes/panel-footer.php'; ?>
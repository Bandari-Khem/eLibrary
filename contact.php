<?php $pageTitle = 'Contact';
require __DIR__ . '/includes/header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/includes/csrf.php';
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$subject || !$message) {
        flash('error', 'Please provide a valid name, email, subject and message.');
    } else {
        $s = db()->prepare('INSERT INTO contact_messages(name,email,subject,message) VALUES(?,?,?,?)');
        $s->execute([$name, $email, $subject, $message]);
        log_activity(current_user()['id'] ?? null, 'contact_message');
        flash('success', 'Your message has been received.');
        redirect('contact.php');
    }
} ?>
<section class="section">
    <div class="form-card">
        <h1>Contact Us</h1>
        <p class="muted">Send a message to the library team.</p>
        <form method="post" class="form-grid"><?= csrf_field() ?><div class="field"><label>Name</label><input
                    name="name" required maxlength="100" value="<?= old('name', current_user()['full_name'] ?? '') ?>">
            </div>
            <div class="field"><label>Email</label><input type="email" name="email" required maxlength="150"
                    value="<?= old('email', current_user()['email'] ?? '') ?>"></div>
            <div class="field full"><label>Subject</label><input name="subject" required maxlength="200"></div>
            <div class="field full"><label>Message</label><textarea name="message" required maxlength="5000"></textarea>
            </div>
            <div><button class="btn">Send message</button></div>
        </form>
    </div>
</section><?php require __DIR__ . '/includes/footer.php'; ?>
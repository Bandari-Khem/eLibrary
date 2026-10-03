<?php require_once __DIR__ . '/includes/auth.php';
if (current_user()) log_activity((int)current_user()['id'], 'logout', 'user', (int)current_user()['id']);
logout_user();
header('Location: ' . url('login.php'));
exit;

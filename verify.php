<?php
require_once __DIR__ . '/config/db.php';

if (!empty($_SESSION['user_id'])) { header('Location: /index.php'); exit; }

$token = trim($_GET['token'] ?? '');
$error = '';

if ($token === '') {
    $error = 'Invalid verification link.';
} else {
    $stmt = $conn->prepare(
        'SELECT id, email_verified FROM users WHERE verification_token = ? LIMIT 1'
    );
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $error = 'This verification link is invalid or has already been used.';
    } elseif ($user['email_verified']) {
        header('Location: /login.php?already_verified=1');
        exit;
    } else {
        $stmt = $conn->prepare(
            'UPDATE users SET email_verified = 1, verification_token = NULL WHERE id = ?'
        );
        $stmt->bind_param('i', $user['id']);
        $stmt->execute();
        $stmt->close();
        header('Location: /login.php?verified=1');
        exit;
    }
}

$page_title = 'Verify Email — FilmVault';
require_once __DIR__ . '/includes/header.php';
?>
    <div class="form-page">
        <div class="form-card">
            <h1>Email Verification</h1>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <p class="form-footer"><a href="/register.php">Back to Register</a></p>
        </div>
    </div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

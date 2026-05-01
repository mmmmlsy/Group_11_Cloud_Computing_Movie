<?php
require_once __DIR__ . '/config/db.php';

if (!empty($_SESSION['user_id'])) { header('Location: /index.php'); exit; }

$token  = trim($_GET['token'] ?? '');
$errors = [];
$done   = false;

if ($token === '') { header('Location: /login.php'); exit; }

// Validate token on every load
$stmt = $conn->prepare(
    'SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1'
);
$stmt->bind_param('s', $token);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    $page_title = 'Reset Password — FilmVault';
    require_once __DIR__ . '/includes/header.php';
    ?>
        <div class="form-page">
            <div class="form-card">
                <h1>Reset Password</h1>
                <div class="alert alert-error">
                    This reset link is invalid or has expired.
                    <a href="/forgot_password.php" style="color:var(--text)">Request a new one.</a>
                </div>
            </div>
        </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password']         ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';

    if (strlen($password) < 8)       $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm)       $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare(
            'UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?'
        );
        $stmt->bind_param('si', $hash, $user['id']);
        $stmt->execute();
        $stmt->close();
        header('Location: /login.php?password_reset=1');
        exit;
    }
}

$page_title = 'Reset Password — FilmVault';
require_once __DIR__ . '/includes/header.php';
?>
    <div class="form-page">
        <div class="form-card">
            <h1>Set New Password</h1>

            <?php foreach ($errors as $e): ?>
                <div class="alert alert-error"><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>

            <form method="post" action="/reset_password.php?token=<?= htmlspecialchars($token) ?>">
                <div class="form-group">
                    <label for="password">New Password <small style="color:var(--muted)">(min. 8 characters)</small></label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="password_confirm">Confirm New Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" required minlength="8">
                </div>
                <button type="submit" class="btn" style="width:100%">Update Password</button>
            </form>
        </div>
    </div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

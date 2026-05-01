<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/mailer.php';

if (!empty($_SESSION['user_id'])) { header('Location: /index.php'); exit; }

$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare(
            'SELECT id, display_name FROM users WHERE email = ? AND email_verified = 1 LIMIT 1'
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $conn->prepare(
                'UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?'
            );
            $stmt->bind_param('ssi', $token, $expires, $user['id']);
            $stmt->execute();
            $stmt->close();

            $app_url  = rtrim($env['APP_URL'] ?? '', '/');
            $link     = $app_url . '/reset_password.php?token=' . $token;
            $html     = email_template(
                'Reset your password',
                '<p>Hi ' . htmlspecialchars($user['display_name']) . ',</p>
                 <p>We received a request to reset your FilmVault password.<br>
                 This link expires in <strong>1 hour</strong>.</p>',
                $link,
                'Reset Password'
            );

            send_email($env, $email, 'Reset your FilmVault password', $html);
        }
    }

    $sent = true;
}

$page_title = 'Forgot Password — FilmVault';
require_once __DIR__ . '/includes/header.php';
?>
    <div class="form-page">
        <div class="form-card">
            <h1>Forgot Password</h1>

            <?php if ($sent): ?>
                <div class="alert alert-success">
                    If an account with that email exists, a reset link has been sent. Check your inbox.
                </div>
                <p class="form-footer"><a href="/login.php">Back to Login</a></p>
            <?php else: ?>
                <p style="color:var(--muted);font-size:0.9rem;margin-bottom:1.25rem">
                    Enter your registered email and we'll send you a reset link.
                </p>
                <form method="post" action="/forgot_password.php">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <button type="submit" class="btn" style="width:100%">Send Reset Link</button>
                </form>
                <p class="form-footer"><a href="/login.php">Back to Login</a></p>
            <?php endif; ?>
        </div>
    </div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

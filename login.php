<?php
require_once __DIR__ . '/config/db.php';

if (!empty($_SESSION['user_id'])) { header('Location: /index.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare(
        'SELECT id, password_hash, display_name, email_verified FROM users WHERE email = ? LIMIT 1'
    );
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password_hash'])) {
        if (!$user['email_verified']) {
            $error = 'Please verify your email address before logging in. Check your inbox.';
        } else {
            $_SESSION['user_id']      = $user['id'];
            $_SESSION['display_name'] = $user['display_name'];
            header('Location: /index.php');
            exit;
        }
    } else {
        $error = 'Invalid email or password.';
    }
}

$page_title = 'Login — FilmVault';
require_once __DIR__ . '/includes/header.php';
?>

    <div class="form-page">
        <?php if (isset($_GET['verified'])): ?>
            <div class="alert alert-success">Email verified — you can now log in.</div>
        <?php elseif (isset($_GET['already_verified'])): ?>
            <div class="alert alert-success">Your email is already verified. Go ahead and log in.</div>
        <?php elseif (isset($_GET['password_reset'])): ?>
            <div class="alert alert-success">Password updated — please log in with your new password.</div>
        <?php endif; ?>

        <div class="form-card">
            <h1>Log In</h1>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post" action="/login.php">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn" style="width:100%">Log In</button>
            </form>

            <p class="form-footer"><a href="/forgot_password.php">Forgot password?</a></p>
            <p class="form-footer">No account? <a href="/register.php">Register here</a></p>
        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

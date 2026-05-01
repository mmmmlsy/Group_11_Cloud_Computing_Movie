<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/mailer.php';

if (!empty($_SESSION['user_id'])) { header('Location: /index.php'); exit; }

$errors = [];
$sent   = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email        = trim($_POST['email'] ?? '');
    $password     = $_POST['password'] ?? '';
    $display_name = trim($_POST['display_name'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 8)                       $errors[] = 'Password must be at least 8 characters.';
    if ($display_name === '')                         $errors[] = 'Display name is required.';

    if (empty($errors)) {
        $hash  = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));

        $stmt = $conn->prepare(
            'INSERT INTO users (email, password_hash, display_name, verification_token) VALUES (?, ?, ?, ?)'
        );
        $stmt->bind_param('ssss', $email, $hash, $display_name, $token);

        try {
            $stmt->execute();
            $stmt->close();

            $app_url = rtrim($env['APP_URL'] ?? '', '/');
            $link    = $app_url . '/verify.php?token=' . $token;
            $html    = email_template(
                'Verify your email address',
                '<p>Hi ' . htmlspecialchars($display_name) . ',</p>
                 <p>Thanks for joining FilmVault! Click the button below to verify your email address.<br>
                 If you did not create this account, you can safely ignore this email.</p>',
                $link,
                'Verify Email'
            );

            send_email($env, $email, 'Verify your FilmVault email', $html);
            $sent = true;
        } catch (\mysqli_sql_exception $e) {
            $stmt->close();
            $errors[] = 'That email address is already registered.';
        }
    }
}

$page_title = 'Register — FilmVault';
require_once __DIR__ . '/includes/header.php';
?>

    <div class="form-page">
        <div class="form-card">
            <h1>Create Account</h1>

            <?php if ($sent): ?>
                <div class="alert alert-success">
                    Account created! Check your inbox for a verification link before logging in.
                </div>
                <p class="form-footer"><a href="/login.php">Back to Login</a></p>
            <?php else: ?>
                <?php foreach ($errors as $e): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>

                <form method="post" action="/register.php">
                    <div class="form-group">
                        <label for="display_name">Display Name</label>
                        <input type="text" id="display_name" name="display_name" required maxlength="80"
                               value="<?= htmlspecialchars($_POST['display_name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="password">Password <small style="color:var(--muted)">(min. 8 characters)</small></label>
                        <input type="password" id="password" name="password" required minlength="8">
                    </div>
                    <button type="submit" class="btn" style="width:100%">Create Account</button>
                </form>

                <p class="form-footer">Already have an account? <a href="/login.php">Log in</a></p>
            <?php endif; ?>
        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

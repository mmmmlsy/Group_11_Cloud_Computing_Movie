<?php
require_once __DIR__ . '/config/db.php';

$auth_error  = '';
$add_error   = '';
$add_success = false;

// Admin login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_password'])) {
    if ($admin_password !== '' && $_POST['admin_password'] === $admin_password) {
        $_SESSION['is_admin'] = true;
    } else {
        $auth_error = 'Incorrect admin password.';
    }
}

// Admin sign-out
if (isset($_GET['signout'])) {
    unset($_SESSION['is_admin']);
    header('Location: /admin.php');
    exit;
}

// Add film
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SESSION['is_admin']) && isset($_POST['add_film'])) {
    $title           = trim($_POST['title'] ?? '');
    $genre_id        = (int)($_POST['genre_id'] ?? 0);
    $year            = (int)($_POST['year'] ?? 0);
    $director        = trim($_POST['director'] ?? '');
    $synopsis        = trim($_POST['synopsis'] ?? '');
    $poster_filename = trim($_POST['poster_filename'] ?? '');

    if ($title && $genre_id && $year >= 1888 && $year <= 2099 && $director && $poster_filename) {
        $stmt = $conn->prepare(
            'INSERT INTO movies (title, genre_id, year, director, synopsis, poster_filename)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('siisss', $title, $genre_id, $year, $director, $synopsis, $poster_filename);
        $stmt->execute();
        $stmt->close();
        $add_success = true;
    } else {
        $add_error = 'All fields except synopsis are required, and year must be between 1888–2099.';
    }
}

$genres = $conn->query('SELECT id, name FROM genres ORDER BY name')->fetch_all(MYSQLI_ASSOC);
$movies = [];
if (!empty($_SESSION['is_admin'])) {
    $movies = $conn->query(
        'SELECT m.id, m.title, g.name AS genre, m.year
         FROM movies m
         JOIN genres g ON m.genre_id = g.id
         ORDER BY m.title'
    )->fetch_all(MYSQLI_ASSOC);
}

$page_title = 'Admin — FilmVault';
require_once __DIR__ . '/includes/header.php';
?>

    <div class="page-header">
        <h1>Admin Panel</h1>
    </div>

    <?php if (empty($_SESSION['is_admin'])): ?>

        <div class="form-page">
            <div class="form-card">
                <h1>Admin Login</h1>
                <?php if ($auth_error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($auth_error) ?></div>
                <?php endif; ?>
                <form method="post" action="/admin.php">
                    <div class="form-group">
                        <label for="admin_password">Admin Password</label>
                        <input type="password" id="admin_password" name="admin_password" required>
                    </div>
                    <button type="submit" class="btn" style="width:100%">Login</button>
                </form>
            </div>
        </div>

    <?php else: ?>

        <div style="display:flex;justify-content:flex-end;margin-bottom:1.5rem">
            <a href="/admin.php?signout=1" class="btn-ghost">Sign out of admin</a>
        </div>

        <?php if ($add_success): ?>
            <div class="alert alert-success">Film added successfully.</div>
        <?php endif; ?>
        <?php if ($add_error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($add_error) ?></div>
        <?php endif; ?>

        <div class="admin-grid">
            <div class="admin-card">
                <h2>Add New Film</h2>
                <form method="post" action="/admin.php">
                    <input type="hidden" name="add_film" value="1">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" required maxlength="200">
                    </div>
                    <div class="form-group">
                        <label>Genre</label>
                        <select name="genre_id" required>
                            <option value="">Select genre</option>
                            <?php foreach ($genres as $g): ?>
                                <option value="<?= (int)$g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Year</label>
                        <input type="number" name="year" required min="1888" max="2099">
                    </div>
                    <div class="form-group">
                        <label>Director</label>
                        <input type="text" name="director" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label>Synopsis</label>
                        <textarea name="synopsis" maxlength="2000"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Poster Filename <small style="color:var(--muted)">(e.g. my_movie.jpg — upload to S3 separately)</small></label>
                        <input type="text" name="poster_filename" required maxlength="100">
                    </div>
                    <button type="submit" class="btn">Add Film</button>
                </form>
            </div>

            <div class="admin-card">
                <h2>Current Films (<?= count($movies) ?>)</h2>
                <table class="film-table">
                    <thead>
                        <tr><th>Title</th><th>Genre</th><th>Year</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movies as $m): ?>
                            <tr>
                                <td>
                                    <a href="/movie.php?id=<?= (int)$m['id'] ?>"
                                       style="color:var(--text);text-decoration:none">
                                        <?= htmlspecialchars($m['title']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($m['genre']) ?></td>
                                <td><?= (int)$m['year'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

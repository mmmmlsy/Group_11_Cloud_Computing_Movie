<?php
require_once __DIR__ . '/config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: /index.php'); exit; }

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SESSION['user_id'])) {
    $body = trim($_POST['body'] ?? '');
    if ($body !== '') {
        $stmt = $conn->prepare('INSERT INTO comments (movie_id, user_id, body) VALUES (?, ?, ?)');
        $stmt->bind_param('iis', $id, $_SESSION['user_id'], $body);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: /movie.php?id=' . $id . '#tab-comments');
    exit;
}

// Fetch movie
$stmt = $conn->prepare(
    'SELECT m.id, m.title, m.year, m.director, m.synopsis, m.poster_filename, g.name AS genre
     FROM movies m
     JOIN genres g ON m.genre_id = g.id
     WHERE m.id = ? LIMIT 1'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$movie = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$movie) { header('Location: /index.php'); exit; }

// Fetch comments
$stmt = $conn->prepare(
    'SELECT c.body, c.created_at, u.display_name
     FROM comments c
     JOIN users u ON c.user_id = u.id
     WHERE c.movie_id = ?
     ORDER BY c.created_at ASC'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = htmlspecialchars($movie['title']) . ' — FilmVault';
require_once __DIR__ . '/includes/header.php';
?>

    <a href="javascript:history.back()" class="back-link">&#8592; Back</a>

    <div class="detail-layout">
        <div class="detail-poster">
            <?php if ($cloudfront_url): ?>
                <img src="<?= htmlspecialchars($cloudfront_url . '/' . $movie['poster_filename']) ?>"
                     alt="<?= htmlspecialchars($movie['title']) ?> poster"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                <div class="poster-placeholder" style="display:none">🎬</div>
            <?php else: ?>
                <div class="poster-placeholder">🎬</div>
            <?php endif; ?>
        </div>

        <div class="detail-info">
            <h1><?= htmlspecialchars($movie['title']) ?></h1>

            <div class="detail-meta">
                <span class="meta-pill"><strong>Genre</strong>&nbsp; <?= htmlspecialchars($movie['genre']) ?></span>
                <span class="meta-pill"><strong>Year</strong>&nbsp; <?= (int)$movie['year'] ?></span>
                <span class="meta-pill"><strong>Director</strong>&nbsp; <?= htmlspecialchars($movie['director']) ?></span>
            </div>

            <div class="tabs" role="tablist">
                <button class="tab-btn active" data-tab="tab-synopsis" role="tab">Synopsis</button>
                <button class="tab-btn" data-tab="tab-comments" role="tab">
                    Comments (<?= count($comments) ?>)
                </button>
            </div>

            <div id="tab-synopsis" class="tab-panel active">
                <?php if (!empty($movie['synopsis'])): ?>
                    <p><?= htmlspecialchars($movie['synopsis']) ?></p>
                <?php else: ?>
                    <p style="color:var(--muted)">No synopsis available.</p>
                <?php endif; ?>
            </div>

            <div id="tab-comments" class="tab-panel">
                <?php if (!empty($comments)): ?>
                    <div class="comments-list">
                        <?php foreach ($comments as $c): ?>
                            <div class="comment">
                                <div class="comment-header">
                                    <span class="comment-author"><?= htmlspecialchars($c['display_name']) ?></span>
                                    <span class="comment-date"><?= date('d M Y', strtotime($c['created_at'])) ?></span>
                                </div>
                                <div class="comment-body"><?= htmlspecialchars($c['body']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color:var(--muted);margin-bottom:1.5rem">No comments yet. Be the first!</p>
                <?php endif; ?>

                <?php if (!empty($_SESSION['user_id'])): ?>
                    <form method="post" action="/movie.php?id=<?= (int)$movie['id'] ?>" class="comment-form">
                        <textarea name="body" placeholder="Write a comment&hellip;" required maxlength="1000"></textarea>
                        <button type="submit" class="btn">Post Comment</button>
                    </form>
                <?php else: ?>
                    <div class="login-prompt">
                        <a href="/login.php">Log in</a> or <a href="/register.php">register</a> to leave a comment.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).classList.add('active');
    });
});
if (window.location.hash === '#tab-comments') {
    document.querySelector('[data-tab="tab-comments"]').click();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

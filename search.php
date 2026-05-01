<?php
require_once __DIR__ . '/config/db.php';

$q       = trim($_GET['q'] ?? '');
$movies  = [];
$searched = false;

if ($q !== '') {
    $searched   = true;
    $like_param = '%' . $q . '%';
    $stmt = $conn->prepare(
        'SELECT m.id, m.title, m.year, m.poster_filename, g.name AS genre
         FROM movies m
         JOIN genres g ON m.genre_id = g.id
         WHERE m.title LIKE ? OR m.director LIKE ?
         ORDER BY m.title'
    );
    $stmt->bind_param('ss', $like_param, $like_param);
    $stmt->execute();
    $movies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$page_title = $q ? 'Search: ' . htmlspecialchars($q) . ' — FilmVault' : 'Search — FilmVault';
require_once __DIR__ . '/includes/header.php';
?>

    <div class="page-header">
        <h1>Search</h1>
        <?php if ($searched): ?>
            <p class="search-results-header">
                <?php if (!empty($movies)): ?>
                    <?= count($movies) ?> result<?= count($movies) !== 1 ? 's' : '' ?> for
                    <strong><?= htmlspecialchars($q) ?></strong>
                <?php else: ?>
                    No results for <strong><?= htmlspecialchars($q) ?></strong>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>

    <?php if (!empty($movies)): ?>
        <div class="movie-grid">
            <?php foreach ($movies as $movie): ?>
                <a href="/movie.php?id=<?= (int)$movie['id'] ?>" class="movie-card">
                    <div class="poster-wrap">
                        <?php if ($cloudfront_url): ?>
                            <img src="<?= htmlspecialchars($cloudfront_url . '/' . $movie['poster_filename']) ?>"
                                 alt="<?= htmlspecialchars($movie['title']) ?> poster"
                                 loading="lazy"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                            <div class="poster-placeholder" style="display:none">🎬</div>
                        <?php else: ?>
                            <div class="poster-placeholder">🎬</div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="card-title"><?= htmlspecialchars($movie['title']) ?></div>
                        <div class="card-meta">
                            <span class="badge"><?= htmlspecialchars($movie['genre']) ?></span>
                            <?= (int)$movie['year'] ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php elseif ($searched): ?>
        <div class="empty-state">
            <div class="icon">🔍</div>
            <h2>No results found</h2>
            <p>Try a different title or director name.</p>
        </div>
    <?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

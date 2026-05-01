<?php
require_once __DIR__ . '/config/db.php';

$genres      = $conn->query('SELECT id, name FROM genres ORDER BY name')->fetch_all(MYSQLI_ASSOC);
$selected_id = isset($_GET['genre_id']) ? (int)$_GET['genre_id'] : 0;
$sel_name    = '';
$movies      = [];

if ($selected_id > 0) {
    $stmt = $conn->prepare(
        'SELECT m.id, m.title, m.year, m.poster_filename, g.name AS genre
         FROM movies m
         JOIN genres g ON m.genre_id = g.id
         WHERE m.genre_id = ?
         ORDER BY m.title'
    );
    $stmt->bind_param('i', $selected_id);
    $stmt->execute();
    $movies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($genres as $g) {
        if ((int)$g['id'] === $selected_id) { $sel_name = $g['name']; break; }
    }
}

$page_title = 'Filter by Genre — FilmVault';
require_once __DIR__ . '/includes/header.php';
?>

    <div class="page-header">
        <h1>Filter by Genre</h1>
        <?php if ($sel_name): ?>
            <p>Showing <strong><?= htmlspecialchars($sel_name) ?></strong></p>
        <?php else: ?>
            <p>Select a genre to browse the catalogue.</p>
        <?php endif; ?>
    </div>

    <form method="get" action="/filter.php" class="filter-bar">
        <label for="genre-select">Genre:</label>
        <select id="genre-select" name="genre_id">
            <option value="">— Choose a genre —</option>
            <?php foreach ($genres as $g): ?>
                <option value="<?= (int)$g['id'] ?>"
                    <?= ((int)$g['id'] === $selected_id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($g['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn">Filter</button>
        <?php if ($selected_id): ?>
            <a href="/filter.php" class="btn-ghost">Clear</a>
        <?php endif; ?>
    </form>

    <?php if ($selected_id > 0 && empty($movies)): ?>
        <div class="empty-state">
            <div class="icon">🔍</div>
            <h2>No films found</h2>
            <p>The catalogue has no films in the <strong><?= htmlspecialchars($sel_name) ?></strong> genre.</p>
        </div>
    <?php elseif (!empty($movies)): ?>
        <div class="movie-grid">
            <?php foreach ($movies as $movie): ?>
                <a href="/movie.php?id=<?= (int)$movie['id'] ?>" class="movie-card">
                    <div class="poster-wrap">
                        <?php $psrc = poster_src($movie['poster_filename'], $cloudfront_url); ?>
                        <?php if ($psrc): ?>
                            <img src="<?= htmlspecialchars($psrc) ?>"
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
    <?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

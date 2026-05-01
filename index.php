<?php
require_once __DIR__ . '/config/db.php';

$genres = $conn->query('SELECT id, name FROM genres ORDER BY name')->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare(
    'SELECT m.id, m.title, m.year, m.poster_filename, g.name AS genre
     FROM movies m
     JOIN genres g ON m.genre_id = g.id
     ORDER BY m.title'
);
$stmt->execute();
$movies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = 'FilmVault — Movie Catalogue';
require_once __DIR__ . '/includes/header.php';
?>

    <div class="page-header">
        <h1>Movie Catalogue</h1>
        <p><?= count($movies) ?> films in the vault</p>
    </div>

    <div class="filter-bar">
        <label for="genre-select">Jump to genre:</label>
        <select id="genre-select" onchange="if(this.value) window.location='/filter.php?genre_id='+this.value">
            <option value="">All genres</option>
            <?php foreach ($genres as $g): ?>
                <option value="<?= (int)$g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if (empty($movies)): ?>
        <div class="empty-state">
            <div class="icon">🎬</div>
            <h2>No movies yet</h2>
            <p>The catalogue is currently empty.</p>
        </div>
    <?php else: ?>
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

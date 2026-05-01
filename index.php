<?php
require_once __DIR__ . '/config/db.php';

$genres_result = $conn->query('SELECT id, name FROM genres ORDER BY name');
$genres = [];
while ($row = $genres_result->fetch_assoc()) {
    $genres[] = $row;
}

$movies_stmt = $conn->prepare(
    'SELECT m.id, m.title, m.year, m.poster_filename, g.name AS genre
     FROM movies m
     JOIN genres g ON m.genre_id = g.id
     ORDER BY m.title'
);
$movies_stmt->execute();
$movies_result = $movies_stmt->get_result();
$movies = [];
while ($row = $movies_result->fetch_assoc()) {
    $movies[] = $row;
}
$movies_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FilmVault — Movie Catalogue</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header>
    <a href="index.php" class="logo">Film<span>Vault</span></a>
    <nav>
        <a href="index.php" class="active">Browse</a>
        <a href="filter.php">Filter by Genre</a>
    </nav>
</header>

<main>
    <div class="page-header">
        <h1>Movie Catalogue</h1>
        <p><?= count($movies) ?> films in the vault</p>
    </div>

    <div class="filter-bar">
        <label for="genre-select">Jump to genre:</label>
        <select id="genre-select" onchange="if(this.value) window.location='filter.php?genre_id='+this.value">
            <option value="">All genres</option>
            <?php foreach ($genres as $genre): ?>
                <option value="<?= (int)$genre['id'] ?>">
                    <?= htmlspecialchars($genre['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if (empty($movies)): ?>
        <div class="empty-state">
            <div class="icon">🎬</div>
            <h2>No movies yet</h2>
            <p>The catalogue is currently empty. Check back soon.</p>
        </div>
    <?php else: ?>
        <div class="movie-grid">
            <?php foreach ($movies as $movie): ?>
                <a href="movie.php?id=<?= (int)$movie['id'] ?>" class="movie-card">
                    <div class="poster-wrap">
                        <?php if ($cloudfront_url): ?>
                            <img
                                src="<?= htmlspecialchars($cloudfront_url . '/' . $movie['poster_filename']) ?>"
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
</main>

<footer>
    <p>FilmVault &mdash; Group 11 &mdash; CMP6210 Cloud Computing &mdash; Kaplan 2025</p>
</footer>

</body>
</html>

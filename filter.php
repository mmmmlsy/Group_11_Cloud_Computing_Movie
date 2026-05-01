<?php
require_once __DIR__ . '/config/db.php';

$genres_result = $conn->query('SELECT id, name FROM genres ORDER BY name');
$genres = [];
while ($row = $genres_result->fetch_assoc()) {
    $genres[] = $row;
}

$selected_genre_id = isset($_GET['genre_id']) ? (int)$_GET['genre_id'] : 0;
$selected_genre_name = '';
$movies = [];

if ($selected_genre_id > 0) {
    $stmt = $conn->prepare(
        'SELECT m.id, m.title, m.year, m.poster_filename, g.name AS genre
         FROM movies m
         JOIN genres g ON m.genre_id = g.id
         WHERE m.genre_id = ?
         ORDER BY m.title'
    );
    $stmt->bind_param('i', $selected_genre_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $movies[] = $row;
    }
    $stmt->close();

    foreach ($genres as $g) {
        if ((int)$g['id'] === $selected_genre_id) {
            $selected_genre_name = $g['name'];
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filter by Genre — FilmVault</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header>
    <a href="index.php" class="logo">Film<span>Vault</span></a>
    <nav>
        <a href="index.php">Browse</a>
        <a href="filter.php" class="active">Filter by Genre</a>
    </nav>
</header>

<main>
    <div class="page-header">
        <h1>Filter by Genre</h1>
        <?php if ($selected_genre_name): ?>
            <p>Showing results for <strong><?= htmlspecialchars($selected_genre_name) ?></strong></p>
        <?php else: ?>
            <p>Select a genre to browse the catalogue.</p>
        <?php endif; ?>
    </div>

    <form method="get" action="filter.php" class="filter-bar">
        <label for="genre-select">Genre:</label>
        <select id="genre-select" name="genre_id">
            <option value="">— Choose a genre —</option>
            <?php foreach ($genres as $genre): ?>
                <option value="<?= (int)$genre['id'] ?>"
                    <?= ((int)$genre['id'] === $selected_genre_id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($genre['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn">Filter</button>
        <?php if ($selected_genre_id): ?>
            <a href="filter.php" class="btn-ghost">Clear</a>
        <?php endif; ?>
    </form>

    <?php if ($selected_genre_id > 0 && empty($movies)): ?>
        <div class="empty-state">
            <div class="icon">🔍</div>
            <h2>No films found</h2>
            <p>The catalogue has no films in the <strong><?= htmlspecialchars($selected_genre_name) ?></strong> genre yet.</p>
        </div>

    <?php elseif (!empty($movies)): ?>
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

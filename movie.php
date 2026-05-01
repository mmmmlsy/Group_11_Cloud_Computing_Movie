<?php
require_once __DIR__ . '/config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare(
    'SELECT m.id, m.title, m.year, m.director, m.synopsis, m.poster_filename, g.name AS genre
     FROM movies m
     JOIN genres g ON m.genre_id = g.id
     WHERE m.id = ?
     LIMIT 1'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();
$stmt->close();

if (!$movie) {
    header('Location: index.php');
    exit;
}

$page_title = htmlspecialchars($movie['title']) . ' — FilmVault';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header>
    <a href="index.php" class="logo">Film<span>Vault</span></a>
    <nav>
        <a href="index.php">Browse</a>
        <a href="filter.php">Filter by Genre</a>
    </nav>
</header>

<main>
    <a href="javascript:history.back()" class="back-link">&#8592; Back</a>

    <div class="detail-layout">
        <div class="detail-poster">
            <?php if ($cloudfront_url): ?>
                <img
                    src="<?= htmlspecialchars($cloudfront_url . '/' . $movie['poster_filename']) ?>"
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

            <?php if (!empty($movie['synopsis'])): ?>
                <div class="detail-section">
                    <h2>Synopsis</h2>
                    <p><?= htmlspecialchars($movie['synopsis']) ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<footer>
    <p>FilmVault &mdash; Group 11 &mdash; CMP6210 Cloud Computing &mdash; Kaplan 2025</p>
</footer>

</body>
</html>

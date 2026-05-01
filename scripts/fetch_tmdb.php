<?php
// scripts/fetch_tmdb.php
// Fetches 20 popular movies from TMDB and re-seeds the filmvault database.
// Run from EC2: php scripts/fetch_tmdb.php
// Requires TMDB_API_KEY in .env

require_once __DIR__ . '/../config/db.php';

$api_key  = $env['TMDB_API_KEY'] ?? '';
$img_base = 'https://image.tmdb.org/t/p/w500';

if (!$api_key) {
    die("Error: TMDB_API_KEY not set in .env\n");
}

function tmdb_get(string $url): ?array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_USERAGENT      => 'FilmVault/1.0',
    ]);
    $body = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);
    if ($err || !$body) return null;
    return json_decode($body, true) ?: null;
}

// TMDB genre ID → our genre label
$genre_map = [
    28    => 'Action',
    12    => 'Adventure',
    16    => 'Animation',
    35    => 'Comedy',
    80    => 'Crime',
    99    => 'Documentary',
    18    => 'Drama',
    10751 => 'Family',
    14    => 'Fantasy',
    27    => 'Horror',
    10749 => 'Romance',
    878   => 'Sci-Fi',
    53    => 'Thriller',
    10752 => 'War',
];

echo "Fetching popular movies from TMDB...\n\n";

$data = tmdb_get("https://api.themoviedb.org/3/movie/popular?api_key={$api_key}&language=en-US&page=1");

if (!$data || empty($data['results'])) {
    die("Error: Could not fetch movies. Check your TMDB_API_KEY in .env\n");
}

// Clear existing movies and genres (comments cascade-deleted with movies)
$conn->query('SET FOREIGN_KEY_CHECKS = 0');
$conn->query('TRUNCATE TABLE movies');
$conn->query('TRUNCATE TABLE genres');
$conn->query('SET FOREIGN_KEY_CHECKS = 1');
echo "Cleared existing movies and genres.\n\n";

$genre_cache = [];

function get_or_create_genre(mysqli $conn, string $name, array &$cache): int {
    if (isset($cache[$name])) return $cache[$name];
    $stmt = $conn->prepare('INSERT IGNORE INTO genres (name) VALUES (?)');
    $stmt->bind_param('s', $name);
    $stmt->execute();
    if ($conn->insert_id) {
        $cache[$name] = (int)$conn->insert_id;
        $stmt->close();
        return $cache[$name];
    }
    $stmt->close();
    $s2 = $conn->prepare('SELECT id FROM genres WHERE name = ?');
    $s2->bind_param('s', $name);
    $s2->execute();
    $cache[$name] = (int)$s2->get_result()->fetch_row()[0];
    $s2->close();
    return $cache[$name];
}

$inserted = 0;

foreach ($data['results'] as $movie) {
    if ($inserted >= 20) break;

    $title    = trim($movie['title'] ?? '');
    $synopsis = trim($movie['overview'] ?? '');
    $year     = (int)substr($movie['release_date'] ?? '2000-01-01', 0, 4);
    $poster   = !empty($movie['poster_path']) ? $img_base . $movie['poster_path'] : '';

    if (!$title || !$poster || !$year) continue;

    // Use first recognisable genre
    $genre_name = 'Other';
    foreach ($movie['genre_ids'] as $gid) {
        if (isset($genre_map[$gid])) { $genre_name = $genre_map[$gid]; break; }
    }

    // Fetch director from credits endpoint
    $director = 'Unknown';
    $credits  = tmdb_get("https://api.themoviedb.org/3/movie/{$movie['id']}/credits?api_key={$api_key}");
    if ($credits && !empty($credits['crew'])) {
        foreach ($credits['crew'] as $person) {
            if ($person['job'] === 'Director') { $director = $person['name']; break; }
        }
    }

    $genre_id = get_or_create_genre($conn, $genre_name, $genre_cache);

    $stmt = $conn->prepare(
        'INSERT INTO movies (title, genre_id, year, director, synopsis, poster_filename)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('siisss', $title, $genre_id, $year, $director, $synopsis, $poster);
    $stmt->execute();
    $stmt->close();

    echo sprintf("  [%2d] %-45s (%d)  %s  [%s]\n",
        $inserted + 1, $title, $year, $director, $genre_name);

    $inserted++;
}

echo "\nDone — {$inserted} movies added to filmvault.\n";

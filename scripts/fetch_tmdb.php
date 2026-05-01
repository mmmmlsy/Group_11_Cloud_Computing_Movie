<?php
// scripts/fetch_tmdb.php — runs locally on your Mac, NOT on EC2
//
// What it does:
//   1. Fetches 20 popular movies from TMDB
//   2. Downloads poster JPGs to posters/
//   3. Generates sql/tmdb_seed.sql
//
// After running:
//   - Upload the posters/ folder contents to S3
//   - Run sql/tmdb_seed.sql on EC2 against RDS

$env_path = __DIR__ . '/../.env';
if (!file_exists($env_path)) die("Error: .env not found. Copy .env.example to .env first.\n");
$env = parse_ini_file($env_path);

$api_key  = $env['TMDB_API_KEY'] ?? '';
$img_base = 'https://image.tmdb.org/t/p/w500';

if (!$api_key) die("Error: TMDB_API_KEY not set in .env\n");

$posters_dir = __DIR__ . '/../posters';
if (!is_dir($posters_dir)) mkdir($posters_dir, 0755, true);

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

function download_image(string $url, string $dest): bool {
    $ch = curl_init($url);
    $fp = fopen($dest, 'wb');
    curl_setopt_array($ch, [
        CURLOPT_FILE      => $fp,
        CURLOPT_TIMEOUT   => 30,
        CURLOPT_USERAGENT => 'FilmVault/1.0',
    ]);
    $ok = curl_exec($ch) && !curl_errno($ch);
    curl_close($ch);
    fclose($fp);
    return $ok;
}

function sql_escape(string $val): string {
    return str_replace(["\\", "'"], ["\\\\", "''"], $val);
}

$genre_map = [
    28    => 'Action',
    12    => 'Adventure',
    16    => 'Animation',
    35    => 'Comedy',
    80    => 'Crime',
    99    => 'Documentary',
    18    => 'Drama',
    14    => 'Fantasy',
    27    => 'Horror',
    10749 => 'Romance',
    878   => 'Sci-Fi',
    53    => 'Thriller',
    10752 => 'War',
];

$target      = 50;
$genres_used = [];
$genre_seq   = 1;
$movie_rows  = [];
$page        = 1;

echo "Fetching {$target} movies from TMDB (multiple pages)...\n\n";

while (count($movie_rows) < $target && $page <= 3) {
    $data = tmdb_get("https://api.themoviedb.org/3/movie/popular?api_key={$api_key}&language=en-US&page={$page}");
    if (!$data || empty($data['results'])) break;

    foreach ($data['results'] as $movie) {
        if (count($movie_rows) >= $target) break;

        $title    = trim($movie['title'] ?? '');
        $synopsis = trim($movie['overview'] ?? '');
        $year     = (int)substr($movie['release_date'] ?? '2000-01-01', 0, 4);
        $poster   = $movie['poster_path'] ?? '';

        if (!$title || !$poster || !$year) continue;

        // Map first recognisable genre
        $genre_name = 'Drama';
        foreach ($movie['genre_ids'] as $gid) {
            if (isset($genre_map[$gid])) { $genre_name = $genre_map[$gid]; break; }
        }

        if (!isset($genres_used[$genre_name])) {
            $genres_used[$genre_name] = $genre_seq++;
        }

        // Fetch director
        $director = 'Unknown';
        $credits  = tmdb_get("https://api.themoviedb.org/3/movie/{$movie['id']}/credits?api_key={$api_key}");
        if ($credits && !empty($credits['crew'])) {
            foreach ($credits['crew'] as $person) {
                if ($person['job'] === 'Director') { $director = $person['name']; break; }
            }
        }

        // Download poster
        $filename = $movie['id'] . '.jpg';
        $dest     = $posters_dir . '/' . $filename;
        $ok       = download_image($img_base . $poster, $dest);

        $movie_rows[] = [
            'title'           => $title,
            'genre_id'        => $genres_used[$genre_name],
            'year'            => $year,
            'director'        => $director,
            'synopsis'        => $synopsis,
            'poster_filename' => $filename,
        ];

        echo sprintf("  [%2d] %-40s (%d)  %-22s  [%s]  poster:%s\n",
            count($movie_rows), $title, $year, $director, $genre_name,
            $ok ? 'ok' : 'FAILED');
    }

    $page++;
}

// Generate sql/tmdb_seed.sql
$sql  = "-- FilmVault TMDB seed — generated " . date('Y-m-d H:i:s') . "\n";
$sql .= "-- Run on EC2: mysql -h <RDS_ENDPOINT> -P 3306 -u admin -p filmvault < sql/tmdb_seed.sql\n\n";
$sql .= "USE filmvault;\n\n";
$sql .= "SET FOREIGN_KEY_CHECKS = 0;\n";
$sql .= "TRUNCATE TABLE movies;\n";
$sql .= "TRUNCATE TABLE genres;\n";
$sql .= "SET FOREIGN_KEY_CHECKS = 1;\n\n";

foreach ($genres_used as $name => $id) {
    $sql .= "INSERT INTO genres (id, name) VALUES ({$id}, '" . sql_escape($name) . "');\n";
}

$sql .= "\n";

foreach ($movie_rows as $m) {
    $sql .= sprintf(
        "INSERT INTO movies (title, genre_id, year, director, synopsis, poster_filename) VALUES ('%s', %d, %d, '%s', '%s', '%s');\n",
        sql_escape($m['title']),
        $m['genre_id'],
        $m['year'],
        sql_escape($m['director']),
        sql_escape($m['synopsis']),
        $m['poster_filename']
    );
}

$sql_path = __DIR__ . '/../sql/tmdb_seed.sql';
file_put_contents($sql_path, $sql);

echo "\n--- Done ---\n";
echo count($movie_rows) . " movies fetched.\n";
echo "Posters downloaded to:  posters/\n";
echo "SQL file generated at:  sql/tmdb_seed.sql\n\n";
echo "Next steps:\n";
echo "  1. Upload posters/ contents to your S3 bucket\n";
echo "  2. git add sql/tmdb_seed.sql && git push\n";
echo "  3. On EC2: git pull && mysql ... < sql/tmdb_seed.sql\n";

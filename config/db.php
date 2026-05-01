<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$env_path = __DIR__ . '/../.env';

if (!file_exists($env_path)) {
    die('Configuration error: .env file not found. Copy .env.example to .env and fill in your values.');
}

$env = parse_ini_file($env_path);

$db_host   = $env['DB_HOST'];
$db_socket = ($db_host === 'localhost') ? ($env['DB_SOCKET'] ?? null) : null;

$conn = new mysqli(
    $db_host,
    $env['DB_USER'],
    $env['DB_PASS'],
    $env['DB_NAME'],
    (int)($env['DB_PORT'] ?? 3306),
    $db_socket
);

if ($conn->connect_error) {
    die('Database connection failed. Please contact the site administrator.');
}

$conn->set_charset('utf8mb4');

$cloudfront_url = rtrim($env['CLOUDFRONT_URL'] ?? '', '/');
$admin_password = $env['ADMIN_PASSWORD'] ?? '';

function poster_src(string $filename, string $cloudfront_url): string {
    if (str_starts_with($filename, 'http')) return $filename;
    if ($cloudfront_url !== '') return $cloudfront_url . '/' . $filename;
    return '';
}

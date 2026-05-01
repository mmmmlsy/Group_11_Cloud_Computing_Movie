<?php
$env_path = __DIR__ . '/../.env';

if (!file_exists($env_path)) {
    die('Configuration error: .env file not found. Copy .env.example to .env and fill in your values.');
}

$env = parse_ini_file($env_path);

$conn = new mysqli(
    $env['DB_HOST'],
    $env['DB_USER'],
    $env['DB_PASS'],
    $env['DB_NAME'],
    (int)($env['DB_PORT'] ?? 3306)
);

if ($conn->connect_error) {
    die('Database connection failed. Please contact the site administrator.');
}

$conn->set_charset('utf8mb4');

$cloudfront_url = rtrim($env['CLOUDFRONT_URL'] ?? '', '/');

<?php
require_once __DIR__ . '/config/db.php';
$email = 'magnuslim1995@gmail.com';
$stmt = $conn->prepare('SELECT id, display_name, email_verified FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
echo '<pre>';
var_dump($user);
echo '</pre>';

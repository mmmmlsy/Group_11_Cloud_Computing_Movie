<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/mailer.php';
$result = send_email($env, 'magnuslim1995@gmail.com', 'FilmVault Test', '<p>Test email from FilmVault</p>');
echo $result ? 'SENT' : 'FAILED';

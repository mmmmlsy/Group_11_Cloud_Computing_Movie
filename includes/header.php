<?php if (!isset($page_title)) $page_title = 'FilmVault'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<header>
    <a href="/index.php" class="logo">Film<span>Vault</span></a>

    <form method="get" action="/search.php" class="header-search" role="search">
        <input type="search" name="q" placeholder="Search films&hellip;"
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
               aria-label="Search films">
        <button type="submit" aria-label="Search">&#128269;</button>
    </form>

    <nav>
        <?php $cur = basename($_SERVER['PHP_SELF']); ?>
        <a href="/index.php"  <?= $cur === 'index.php'  ? 'class="active"' : '' ?>>Browse</a>
        <a href="/filter.php" <?= $cur === 'filter.php' ? 'class="active"' : '' ?>>Genre</a>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <span class="nav-user"><?= htmlspecialchars($_SESSION['display_name']) ?></span>
            <a href="/logout.php">Logout</a>
        <?php else: ?>
            <a href="/login.php"    <?= $cur === 'login.php'    ? 'class="active"' : '' ?>>Login</a>
            <a href="/register.php" <?= $cur === 'register.php' ? 'class="active"' : '' ?>>Register</a>
        <?php endif; ?>
    </nav>
</header>

<main>

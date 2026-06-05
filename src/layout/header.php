<?php
require_once __DIR__ . '/../auth.php';

$me = current_user();
$title = isset($page_title) ? $page_title . ' · FillMovie' : 'FillMovie';
$current = basename($_SERVER['SCRIPT_NAME'] ?? '');

/** Print class="active" when $file is the current page. */
function nav_active(string $file, string $current): string
{
    return $file === $current ? ' class="active"' : '';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?></title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <header class="topbar">
    <a class="brand" href="<?= $me ? 'feed.php' : 'index.php' ?>">Fill<span>Movie</span></a>
    <nav class="nav">
      <?php if ($me): ?>
        <a href="feed.php"<?= nav_active('feed.php', $current) ?>>Feed</a>
        <a href="movies.php"<?= nav_active('movies.php', $current) ?>>Movies</a>
        <a href="reviews.php"<?= nav_active('reviews.php', $current) ?>>Reviews</a>
        <a href="watchlist.php"<?= nav_active('watchlist.php', $current) ?>>Watchlist</a>
        <a href="friends.php"<?= nav_active('friends.php', $current) ?>>Friends</a>
        <a href="messages.php"<?= nav_active('messages.php', $current) ?>>Messages</a>
        <a href="profile.php?id=<?= (int) $me['id'] ?>">Profile</a>
        <a href="settings.php"<?= nav_active('settings.php', $current) ?>>Settings</a>
        <a href="logout.php">Sign out</a>
      <?php else: ?>
        <a href="login.php"<?= nav_active('login.php', $current) ?>>Sign in</a>
        <a href="register.php"<?= nav_active('register.php', $current) ?>>Register</a>
      <?php endif; ?>
    </nav>
  </header>
  <main class="container">
    <?php foreach (take_flashes() as $f): ?>
      <div class="flash flash-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
    <?php endforeach; ?>

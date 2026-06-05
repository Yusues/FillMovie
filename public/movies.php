<?php
require __DIR__ . '/../src/bootstrap.php';
require_login();

$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'add_watchlist') {
        $movie = trim($_POST['movie_title'] ?? '');
        if ($movie !== '') {
            $stmt = db()->prepare('INSERT INTO watchlist (user_id, movie_title, poster_url, status) VALUES (?, ?, ?, ?)');
            $stmt->execute([$me['id'], mb_substr($movie, 0, 150), ($_POST['poster'] ?? '') ?: null, 'to_watch']);
            flash('Added to your watchlist.', 'success');
        }
    }
    redirect('movies.php' . (($_POST['q'] ?? '') !== '' ? '?q=' . urlencode($_POST['q']) : ''));
}

$q = trim($_GET['q'] ?? '');
$results = $q !== '' ? omdb_search($q) : [];

$page_title = 'Movies';
require __DIR__ . '/../src/layout/header.php';
?>
<h1>Find a movie</h1>

<?php if (!omdb_enabled()): ?>
  <div class="flash flash-info">
    Movie search is off because no OMDb API key is configured. Add <code>OMDB_API_KEY</code>
    to your <code>.env</code> to enable it. You can still add titles by hand on the
    <a href="watchlist.php">Watchlist</a> and <a href="reviews.php">Reviews</a> pages.
  </div>
<?php endif; ?>

<div class="card">
  <form method="get" action="movies.php" class="inline-form">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search by title…" <?= omdb_enabled() ? '' : 'disabled' ?>>
    <button type="submit" class="btn btn-primary" <?= omdb_enabled() ? '' : 'disabled' ?>>Search</button>
  </form>
</div>

<?php if ($q !== '' && !$results): ?>
  <p class="muted">No movies found for "<?= e($q) ?>".</p>
<?php endif; ?>

<div class="movie-grid">
  <?php foreach ($results as $movie): ?>
    <div class="card movie-card">
      <?php if ($movie['poster']): ?>
        <img src="<?= e($movie['poster']) ?>" alt="<?= e($movie['title']) ?> poster" loading="lazy">
      <?php else: ?>
        <div class="poster-placeholder">No poster</div>
      <?php endif; ?>
      <h3><?= e($movie['title']) ?></h3>
      <p class="muted"><?= e($movie['year']) ?></p>
      <div class="movie-actions">
        <form method="post" action="movies.php">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="add_watchlist">
          <input type="hidden" name="movie_title" value="<?= e($movie['title']) ?>">
          <input type="hidden" name="poster" value="<?= e($movie['poster'] ?? '') ?>">
          <input type="hidden" name="q" value="<?= e($q) ?>">
          <button type="submit" class="btn btn-small btn-primary">+ Watchlist</button>
        </form>
        <a class="btn btn-small" href="reviews.php?movie=<?= urlencode($movie['title']) ?>">Review</a>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php require __DIR__ . '/../src/layout/footer.php'; ?>

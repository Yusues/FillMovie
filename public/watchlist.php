<?php
require __DIR__ . '/../src/bootstrap.php';
require_login();

$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $movie  = trim($_POST['movie_title'] ?? '');
        $status = ($_POST['status'] ?? 'to_watch') === 'watched' ? 'watched' : 'to_watch';
        if ($movie !== '') {
            $stmt = db()->prepare('INSERT INTO watchlist (user_id, movie_title, status) VALUES (?, ?, ?)');
            $stmt->execute([$me['id'], mb_substr($movie, 0, 150), $status]);
            flash('Added to your watchlist.', 'success');
        }
    } elseif ($action === 'move') {
        $itemId = (int) ($_POST['id'] ?? 0);
        $status = ($_POST['status'] ?? 'to_watch') === 'watched' ? 'watched' : 'to_watch';
        $stmt = db()->prepare('UPDATE watchlist SET status = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$status, $itemId, $me['id']]);
    } elseif ($action === 'delete') {
        $itemId = (int) ($_POST['id'] ?? 0);
        $stmt = db()->prepare('DELETE FROM watchlist WHERE id = ? AND user_id = ?');
        $stmt->execute([$itemId, $me['id']]);
    }
    redirect('watchlist.php');
}

$prefillMovie = trim($_GET['movie'] ?? '');

$stmt = db()->prepare('SELECT id, movie_title, status, created_at FROM watchlist WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$me['id']]);
$items = $stmt->fetchAll();

$toWatch = array_filter($items, fn($i) => $i['status'] === 'to_watch');
$watched = array_filter($items, fn($i) => $i['status'] === 'watched');

$page_title = 'Watchlist';
require __DIR__ . '/../src/layout/header.php';

/** Render one watchlist column. */
function render_items(array $items, string $emptyText): void
{
    if (!$items) {
        echo '<p class="muted">' . e($emptyText) . '</p>';
        return;
    }
    foreach ($items as $item) {
        $toStatus = $item['status'] === 'watched' ? 'to_watch' : 'watched';
        $toLabel  = $item['status'] === 'watched' ? 'Move to "to watch"' : 'Mark watched';
        ?>
        <div class="card list-item">
          <span><?= e($item['movie_title']) ?></span>
          <span class="list-actions">
            <form method="post" action="watchlist.php">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="move">
              <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
              <input type="hidden" name="status" value="<?= $toStatus ?>">
              <button type="submit" class="btn btn-small"><?= e($toLabel) ?></button>
            </form>
            <form method="post" action="watchlist.php" onsubmit="return confirm('Remove this title?');">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
              <button type="submit" class="btn btn-small btn-danger">Remove</button>
            </form>
          </span>
        </div>
        <?php
    }
}
?>
<h1>Your watchlist</h1>

<div class="card">
  <form method="post" action="watchlist.php" class="inline-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <input type="text" name="movie_title" value="<?= e($prefillMovie) ?>" placeholder="Movie title" maxlength="150" required>
    <select name="status">
      <option value="to_watch">To watch</option>
      <option value="watched">Watched</option>
    </select>
    <button type="submit" class="btn btn-primary">Add</button>
  </form>
</div>

<div class="columns">
  <section>
    <h2>To watch</h2>
    <?php render_items($toWatch, 'Nothing queued up.'); ?>
  </section>
  <section>
    <h2>Watched</h2>
    <?php render_items($watched, 'Nothing marked as watched yet.'); ?>
  </section>
</div>
<?php require __DIR__ . '/../src/layout/footer.php'; ?>

<?php
require __DIR__ . '/../src/bootstrap.php';
require_login();

$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $target = (int) ($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($target > 0 && $target !== (int) $me['id']) {
        if ($action === 'follow') {
            $stmt = db()->prepare('INSERT IGNORE INTO friendships (follower_id, following_id) VALUES (?, ?)');
            $stmt->execute([$me['id'], $target]);
        } elseif ($action === 'unfollow') {
            $stmt = db()->prepare('DELETE FROM friendships WHERE follower_id = ? AND following_id = ?');
            $stmt->execute([$me['id'], $target]);
        }
    }
    redirect('friends.php' . (isset($_POST['q']) && $_POST['q'] !== '' ? '?q=' . urlencode($_POST['q']) : ''));
}

// Who I already follow.
$stmt = db()->prepare('SELECT following_id FROM friendships WHERE follower_id = ?');
$stmt->execute([$me['id']]);
$followingIds = array_column($stmt->fetchAll(), 'following_id');
$followingIds = array_map('intval', $followingIds);

$q = trim($_GET['q'] ?? '');
$results = [];
if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = db()->prepare(
        'SELECT id, first_name, last_name, bio FROM users
          WHERE id <> ? AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)
          ORDER BY first_name LIMIT 25'
    );
    $stmt->execute([$me['id'], $like, $like, $like]);
    $results = $stmt->fetchAll();
}

$stmt = db()->prepare(
    'SELECT u.id, u.first_name, u.last_name, u.bio
       FROM friendships f JOIN users u ON u.id = f.following_id
      WHERE f.follower_id = ? ORDER BY u.first_name'
);
$stmt->execute([$me['id']]);
$following = $stmt->fetchAll();

$page_title = 'Friends';
require __DIR__ . '/../src/layout/header.php';

/** A follow/unfollow button for a given user. */
function follow_button(array $user, bool $isFollowing, string $q): void
{
    ?>
    <form method="post" action="friends.php">
      <?= csrf_field() ?>
      <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">
      <input type="hidden" name="q" value="<?= e($q) ?>">
      <input type="hidden" name="action" value="<?= $isFollowing ? 'unfollow' : 'follow' ?>">
      <button type="submit" class="btn btn-small <?= $isFollowing ? '' : 'btn-primary' ?>">
        <?= $isFollowing ? 'Unfollow' : 'Follow' ?>
      </button>
    </form>
    <?php
}
?>
<h1>Find people</h1>

<div class="card">
  <form method="get" action="friends.php" class="inline-form">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search by name or email">
    <button type="submit" class="btn btn-primary">Search</button>
  </form>
</div>

<?php if ($q !== ''): ?>
  <h2>Results</h2>
  <?php if (!$results): ?><p class="muted">No one matched "<?= e($q) ?>".</p><?php endif; ?>
  <?php foreach ($results as $user): ?>
    <div class="card list-item">
      <span>
        <a class="author" href="profile.php?id=<?= (int) $user['id'] ?>"><?= e($user['first_name'] . ' ' . $user['last_name']) ?></a>
        <small class="muted"><?= e($user['bio']) ?></small>
      </span>
      <?php follow_button($user, in_array((int) $user['id'], $followingIds, true), $q); ?>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<h2>People you follow</h2>
<?php if (!$following): ?><p class="muted">You're not following anyone yet.</p><?php endif; ?>
<?php foreach ($following as $user): ?>
  <div class="card list-item">
    <span>
      <a class="author" href="profile.php?id=<?= (int) $user['id'] ?>"><?= e($user['first_name'] . ' ' . $user['last_name']) ?></a>
      <small class="muted"><?= e($user['bio']) ?></small>
    </span>
    <?php follow_button($user, true, $q); ?>
  </div>
<?php endforeach; ?>
<?php require __DIR__ . '/../src/layout/footer.php'; ?>

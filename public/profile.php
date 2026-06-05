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
    redirect('profile.php?id=' . $target);
}

$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    flash('That user does not exist.', 'error');
    redirect('feed.php');
}

$isSelf = (int) $user['id'] === (int) $me['id'];

$stmt = db()->prepare('SELECT 1 FROM friendships WHERE follower_id = ? AND following_id = ?');
$stmt->execute([$me['id'], $user['id']]);
$following = (bool) $stmt->fetch();

$stmt = db()->prepare('SELECT body, created_at FROM posts WHERE user_id = ? ORDER BY created_at DESC LIMIT 20');
$stmt->execute([$user['id']]);
$posts = $stmt->fetchAll();

$stmt = db()->prepare('SELECT movie_title, title, body, rating, created_at FROM reviews WHERE user_id = ? ORDER BY created_at DESC LIMIT 10');
$stmt->execute([$user['id']]);
$reviews = $stmt->fetchAll();

$stmt = db()->prepare('SELECT movie_title, status FROM watchlist WHERE user_id = ? ORDER BY created_at DESC LIMIT 30');
$stmt->execute([$user['id']]);
$watchlist = $stmt->fetchAll();

$page_title = full_name($user);
require __DIR__ . '/../src/layout/header.php';
?>
<section class="card profile-head">
  <div>
    <h1><?= e(full_name($user)) ?></h1>
    <p class="muted"><?= e($user['bio']) ?: '<span class="muted">No bio yet.</span>' ?></p>
    <p class="muted">Joined <?= e(format_date($user['created_at'])) ?></p>
  </div>
  <?php if (!$isSelf): ?>
    <form method="post" action="profile.php">
      <?= csrf_field() ?>
      <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">
      <input type="hidden" name="action" value="<?= $following ? 'unfollow' : 'follow' ?>">
      <button type="submit" class="btn <?= $following ? '' : 'btn-primary' ?>">
        <?= $following ? 'Following' : 'Follow' ?>
      </button>
    </form>
  <?php else: ?>
    <a class="btn" href="settings.php">Edit profile</a>
  <?php endif; ?>
</section>

<div class="columns">
  <section>
    <h2>Posts</h2>
    <?php if (!$posts): ?><p class="muted">No posts yet.</p><?php endif; ?>
    <?php foreach ($posts as $post): ?>
      <article class="card post">
        <time class="muted"><?= e(format_date($post['created_at'])) ?></time>
        <p class="post-body"><?= nl2br(e($post['body'])) ?></p>
      </article>
    <?php endforeach; ?>
  </section>

  <section>
    <h2>Reviews</h2>
    <?php if (!$reviews): ?><p class="muted">No reviews yet.</p><?php endif; ?>
    <?php foreach ($reviews as $review): ?>
      <article class="card review">
        <h3><?= e($review['movie_title']) ?>
          <?php if ($review['rating'] !== null): ?>
            <span class="rating"><?= (int) $review['rating'] ?>/10</span>
          <?php endif; ?>
        </h3>
        <?php if ($review['title'] !== ''): ?><p class="review-title"><?= e($review['title']) ?></p><?php endif; ?>
        <p class="post-body"><?= nl2br(e($review['body'])) ?></p>
      </article>
    <?php endforeach; ?>

    <h2>Watchlist</h2>
    <?php if (!$watchlist): ?><p class="muted">Nothing on the watchlist.</p><?php endif; ?>
    <ul class="taglist">
      <?php foreach ($watchlist as $item): ?>
        <li class="tag tag-<?= e($item['status']) ?>"><?= e($item['movie_title']) ?></li>
      <?php endforeach; ?>
    </ul>
  </section>
</div>
<?php require __DIR__ . '/../src/layout/footer.php'; ?>

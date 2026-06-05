<?php
require __DIR__ . '/../src/bootstrap.php';
require_login();

$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $body = trim($_POST['body'] ?? '');
    if ($body === '') {
        flash('Your post was empty.', 'error');
    } else {
        $stmt = db()->prepare('INSERT INTO posts (user_id, body) VALUES (?, ?)');
        $stmt->execute([$me['id'], mb_substr($body, 0, 500)]);
        flash('Posted!', 'success');
    }
    redirect('feed.php');
}

// Posts from the current user and everyone they follow.
$stmt = db()->prepare(
    'SELECT p.id, p.body, p.created_at, u.id AS author_id, u.first_name, u.last_name
       FROM posts p
       JOIN users u ON u.id = p.user_id
      WHERE p.user_id = :me
         OR p.user_id IN (SELECT following_id FROM friendships WHERE follower_id = :follower)
   ORDER BY p.created_at DESC
      LIMIT 50'
);
$stmt->execute(['me' => $me['id'], 'follower' => $me['id']]);
$posts = $stmt->fetchAll();

$page_title = 'Feed';
require __DIR__ . '/../src/layout/header.php';
?>
<h1>Your feed</h1>

<div class="card">
  <form method="post" action="feed.php" class="composer">
    <?= csrf_field() ?>
    <textarea name="body" rows="3" maxlength="500" placeholder="Share something about a movie…" required></textarea>
    <button type="submit" class="btn btn-primary">Post</button>
  </form>
</div>

<?php if (!$posts): ?>
  <p class="muted">Nothing here yet. Follow some people on the <a href="friends.php">Friends</a> page, or write the first post.</p>
<?php endif; ?>

<?php foreach ($posts as $post): ?>
  <article class="card post">
    <div class="post-head">
      <a class="author" href="profile.php?id=<?= (int) $post['author_id'] ?>">
        <?= e($post['first_name'] . ' ' . $post['last_name']) ?>
      </a>
      <time class="muted"><?= e(format_date($post['created_at'])) ?></time>
    </div>
    <p class="post-body"><?= nl2br(e($post['body'])) ?></p>
  </article>
<?php endforeach; ?>
<?php require __DIR__ . '/../src/layout/footer.php'; ?>

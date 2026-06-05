<?php
require __DIR__ . '/../src/bootstrap.php';
require_login();

$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $movie  = trim($_POST['movie_title'] ?? '');
    $title  = trim($_POST['title'] ?? '');
    $body   = trim($_POST['body'] ?? '');
    $rating = $_POST['rating'] ?? '';

    $rating = ($rating === '' ? null : (int) $rating);
    if ($rating !== null && ($rating < 1 || $rating > 10)) {
        $rating = null;
    }

    if ($movie === '' || $body === '') {
        flash('A movie title and some text are required.', 'error');
    } else {
        $stmt = db()->prepare(
            'INSERT INTO reviews (user_id, movie_title, title, body, rating) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$me['id'], $movie, $title, $body, $rating]);
        flash('Review published.', 'success');
        redirect('reviews.php');
    }
}

$prefillMovie = trim($_GET['movie'] ?? '');

$reviews = db()->query(
    'SELECT r.movie_title, r.title, r.body, r.rating, r.created_at,
            u.id AS author_id, u.first_name, u.last_name
       FROM reviews r
       JOIN users u ON u.id = r.user_id
   ORDER BY r.created_at DESC
      LIMIT 30'
)->fetchAll();

$page_title = 'Reviews';
require __DIR__ . '/../src/layout/header.php';
?>
<h1>Reviews</h1>

<div class="card">
  <h2>Write a review</h2>
  <form method="post" action="reviews.php">
    <?= csrf_field() ?>
    <label for="movie_title">Movie</label>
    <input type="text" id="movie_title" name="movie_title" value="<?= e($prefillMovie) ?>" maxlength="150" required>

    <div class="form-row">
      <div style="flex:3">
        <label for="title">Headline (optional)</label>
        <input type="text" id="title" name="title" maxlength="150">
      </div>
      <div style="flex:1">
        <label for="rating">Rating /10</label>
        <input type="number" id="rating" name="rating" min="1" max="10">
      </div>
    </div>

    <label for="body">Your review</label>
    <textarea id="body" name="body" rows="4" required></textarea>

    <button type="submit" class="btn btn-primary">Publish</button>
  </form>
</div>

<?php foreach ($reviews as $review): ?>
  <article class="card review">
    <div class="post-head">
      <a class="author" href="profile.php?id=<?= (int) $review['author_id'] ?>">
        <?= e($review['first_name'] . ' ' . $review['last_name']) ?>
      </a>
      <time class="muted"><?= e(format_date($review['created_at'])) ?></time>
    </div>
    <h3><?= e($review['movie_title']) ?>
      <?php if ($review['rating'] !== null): ?>
        <span class="rating"><?= (int) $review['rating'] ?>/10</span>
      <?php endif; ?>
    </h3>
    <?php if ($review['title'] !== ''): ?><p class="review-title"><?= e($review['title']) ?></p><?php endif; ?>
    <p class="post-body"><?= nl2br(e($review['body'])) ?></p>
  </article>
<?php endforeach; ?>
<?php require __DIR__ . '/../src/layout/footer.php'; ?>

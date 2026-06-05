<?php
require __DIR__ . '/../src/bootstrap.php';

if (current_user()) {
    redirect('feed.php');
}

$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if (attempt_login($email, $password)) {
        flash('Welcome back!', 'success');
        redirect('feed.php');
    }
    flash('Wrong email or password.', 'error');
}

$page_title = 'Sign in';
require __DIR__ . '/../src/layout/header.php';
?>
<div class="card auth-card">
  <h1>Sign in</h1>
  <form method="post" action="login.php">
    <?= csrf_field() ?>
    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= e($email) ?>" required autofocus>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" required>

    <button type="submit" class="btn btn-primary">Sign in</button>
  </form>
  <p class="muted">No account yet? <a href="register.php">Create one</a>.</p>
</div>
<?php require __DIR__ . '/../src/layout/footer.php'; ?>

<?php
require __DIR__ . '/../src/bootstrap.php';

if (current_user()) {
    redirect('feed.php');
}

$input = ['first_name' => '', 'last_name' => '', 'email' => '', 'bio' => '', 'birth_date' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $input = array_merge($input, $_POST);
    $errors = register_user($_POST);
    if (!$errors) {
        flash('Account created. Welcome to FillMovie!', 'success');
        redirect('feed.php');
    }
}

$page_title = 'Register';
require __DIR__ . '/../src/layout/header.php';
?>
<div class="card auth-card">
  <h1>Create your account</h1>

  <?php foreach ($errors as $error): ?>
    <div class="flash flash-error"><?= e($error) ?></div>
  <?php endforeach; ?>

  <form method="post" action="register.php">
    <?= csrf_field() ?>
    <div class="form-row">
      <div>
        <label for="first_name">First name</label>
        <input type="text" id="first_name" name="first_name" value="<?= e($input['first_name']) ?>" required>
      </div>
      <div>
        <label for="last_name">Last name</label>
        <input type="text" id="last_name" name="last_name" value="<?= e($input['last_name']) ?>" required>
      </div>
    </div>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= e($input['email']) ?>" required>

    <label for="bio">Short bio</label>
    <input type="text" id="bio" name="bio" value="<?= e($input['bio']) ?>" maxlength="255" placeholder="What do you like to watch?">

    <label for="birth_date">Date of birth</label>
    <input type="date" id="birth_date" name="birth_date" value="<?= e($input['birth_date']) ?>">

    <div class="form-row">
      <div>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="8">
      </div>
      <div>
        <label for="confirm">Confirm password</label>
        <input type="password" id="confirm" name="confirm" required minlength="8">
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Create account</button>
  </form>
  <p class="muted">Already have an account? <a href="login.php">Sign in</a>.</p>
</div>
<?php require __DIR__ . '/../src/layout/footer.php'; ?>

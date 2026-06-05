<?php
require __DIR__ . '/../src/bootstrap.php';
require_login();

$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $form = $_POST['form'] ?? '';

    if ($form === 'profile') {
        $first = trim($_POST['first_name'] ?? '');
        $last  = trim($_POST['last_name'] ?? '');
        $bio   = trim($_POST['bio'] ?? '');
        $birth = trim($_POST['birth_date'] ?? '');

        if ($first === '' || $last === '') {
            flash('Name cannot be empty.', 'error');
        } else {
            $stmt = db()->prepare(
                'UPDATE users SET first_name = ?, last_name = ?, bio = ?, birth_date = ? WHERE id = ?'
            );
            $stmt->execute([$first, $last, $bio, $birth !== '' ? $birth : null, $me['id']]);
            flash('Profile updated.', 'success');
        }
    } elseif ($form === 'password') {
        $current = (string) ($_POST['current'] ?? '');
        $new     = (string) ($_POST['new'] ?? '');
        $confirm = (string) ($_POST['confirm'] ?? '');

        if (!password_verify($current, $me['password_hash'])) {
            flash('Your current password is incorrect.', 'error');
        } elseif (strlen($new) < 8) {
            flash('New password must be at least 8 characters.', 'error');
        } elseif ($new !== $confirm) {
            flash('The new passwords do not match.', 'error');
        } else {
            $stmt = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $stmt->execute([password_hash($new, PASSWORD_DEFAULT), $me['id']]);
            flash('Password changed.', 'success');
        }
    }
    redirect('settings.php');
}

$page_title = 'Settings';
require __DIR__ . '/../src/layout/header.php';
?>
<h1>Settings</h1>

<div class="card">
  <h2>Profile</h2>
  <form method="post" action="settings.php">
    <?= csrf_field() ?>
    <input type="hidden" name="form" value="profile">
    <div class="form-row">
      <div>
        <label for="first_name">First name</label>
        <input type="text" id="first_name" name="first_name" value="<?= e($me['first_name']) ?>" required>
      </div>
      <div>
        <label for="last_name">Last name</label>
        <input type="text" id="last_name" name="last_name" value="<?= e($me['last_name']) ?>" required>
      </div>
    </div>
    <label for="bio">Bio</label>
    <input type="text" id="bio" name="bio" value="<?= e($me['bio']) ?>" maxlength="255">
    <label for="birth_date">Date of birth</label>
    <input type="date" id="birth_date" name="birth_date" value="<?= e($me['birth_date']) ?>">
    <button type="submit" class="btn btn-primary">Save profile</button>
  </form>
</div>

<div class="card">
  <h2>Change password</h2>
  <form method="post" action="settings.php">
    <?= csrf_field() ?>
    <input type="hidden" name="form" value="password">
    <label for="current">Current password</label>
    <input type="password" id="current" name="current" required>
    <div class="form-row">
      <div>
        <label for="new">New password</label>
        <input type="password" id="new" name="new" required minlength="8">
      </div>
      <div>
        <label for="confirm">Confirm new password</label>
        <input type="password" id="confirm" name="confirm" required minlength="8">
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Change password</button>
  </form>
</div>
<?php require __DIR__ . '/../src/layout/footer.php'; ?>

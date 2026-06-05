<?php
require __DIR__ . '/../src/bootstrap.php';
require_login();

$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $receiver = (int) ($_POST['receiver_id'] ?? 0);
    $subject  = trim($_POST['subject'] ?? '');
    $body     = trim($_POST['body'] ?? '');

    if ($receiver === (int) $me['id']) {
        flash('You cannot message yourself.', 'error');
    } elseif ($body === '') {
        flash('Message body is empty.', 'error');
    } else {
        $stmt = db()->prepare('SELECT 1 FROM users WHERE id = ?');
        $stmt->execute([$receiver]);
        if (!$stmt->fetch()) {
            flash('That recipient does not exist.', 'error');
        } else {
            $stmt = db()->prepare(
                'INSERT INTO messages (sender_id, receiver_id, subject, body) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$me['id'], $receiver, mb_substr($subject, 0, 150), mb_substr($body, 0, 1000)]);
            flash('Message sent.', 'success');
        }
    }
    redirect('messages.php');
}

// Mark everything in the inbox as read on visit.
$stmt = db()->prepare('UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND is_read = 0');
$stmt->execute([$me['id']]);

$stmt = db()->prepare(
    'SELECT m.subject, m.body, m.created_at, u.id AS other_id, u.first_name, u.last_name
       FROM messages m JOIN users u ON u.id = m.sender_id
      WHERE m.receiver_id = ? ORDER BY m.created_at DESC LIMIT 50'
);
$stmt->execute([$me['id']]);
$inbox = $stmt->fetchAll();

$stmt = db()->prepare(
    'SELECT m.subject, m.body, m.created_at, u.id AS other_id, u.first_name, u.last_name
       FROM messages m JOIN users u ON u.id = m.receiver_id
      WHERE m.sender_id = ? ORDER BY m.created_at DESC LIMIT 50'
);
$stmt->execute([$me['id']]);
$sent = $stmt->fetchAll();

$people = db()->prepare('SELECT id, first_name, last_name FROM users WHERE id <> ? ORDER BY first_name');
$people->execute([$me['id']]);
$people = $people->fetchAll();

$page_title = 'Messages';
require __DIR__ . '/../src/layout/header.php';

/** Render a list of messages with the other party's name. */
function render_messages(array $messages, string $direction, string $emptyText): void
{
    if (!$messages) {
        echo '<p class="muted">' . e($emptyText) . '</p>';
        return;
    }
    foreach ($messages as $m) {
        ?>
        <article class="card message">
          <div class="post-head">
            <span><?= $direction === 'in' ? 'From' : 'To' ?>
              <a class="author" href="profile.php?id=<?= (int) $m['other_id'] ?>"><?= e($m['first_name'] . ' ' . $m['last_name']) ?></a>
            </span>
            <time class="muted"><?= e(format_date($m['created_at'])) ?></time>
          </div>
          <?php if ($m['subject'] !== ''): ?><p class="review-title"><?= e($m['subject']) ?></p><?php endif; ?>
          <p class="post-body"><?= nl2br(e($m['body'])) ?></p>
        </article>
        <?php
    }
}
?>
<h1>Messages</h1>

<div class="card">
  <h2>New message</h2>
  <form method="post" action="messages.php">
    <?= csrf_field() ?>
    <label for="receiver_id">To</label>
    <select id="receiver_id" name="receiver_id" required>
      <option value="">Choose a person…</option>
      <?php foreach ($people as $person): ?>
        <option value="<?= (int) $person['id'] ?>"><?= e($person['first_name'] . ' ' . $person['last_name']) ?></option>
      <?php endforeach; ?>
    </select>
    <label for="subject">Subject</label>
    <input type="text" id="subject" name="subject" maxlength="150">
    <label for="body">Message</label>
    <textarea id="body" name="body" rows="3" maxlength="1000" required></textarea>
    <button type="submit" class="btn btn-primary">Send</button>
  </form>
</div>

<div class="columns">
  <section>
    <h2>Inbox</h2>
    <?php render_messages($inbox, 'in', 'No messages yet.'); ?>
  </section>
  <section>
    <h2>Sent</h2>
    <?php render_messages($sent, 'out', 'You have not sent anything.'); ?>
  </section>
</div>
<?php require __DIR__ . '/../src/layout/footer.php'; ?>

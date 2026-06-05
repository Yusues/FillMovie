<?php
require __DIR__ . '/../src/bootstrap.php';

redirect(current_user() ? 'feed.php' : 'login.php');

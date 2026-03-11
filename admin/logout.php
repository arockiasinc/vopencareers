<?php
declare(strict_types=1);

require __DIR__ . '/auth.php';

logoutAdmin();
redirectTo('login.php?logged_out=1');

<?php

declare(strict_types=1);

require __DIR__ . '/auth.php';

logoutUser();
header('Location: index.php?logged_out=1');
exit;

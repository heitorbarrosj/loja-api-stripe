<?php

declare(strict_types=1);

require __DIR__ . '/auth.php';

$user = currentUser();
$orderId = (int) ($_GET['order_id'] ?? 0);

if ($user !== null && $orderId > 0) {
    $status = 'canceled';
    $statement = db()->prepare('UPDATE orders SET status = ? WHERE id = ? AND user_id = ?');
    $statement->bind_param('sii', $status, $orderId, $user['id']);
    $statement->execute();
    $statement->close();
}

header('Location: index.php?canceled=1');
exit;

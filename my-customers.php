<?php

declare(strict_types=1);

require __DIR__ . '/auth.php';

$user = requireLogin();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim((string) ($_POST['address'] ?? ''));

    $statement = db()->prepare('UPDATE users SET address = ? WHERE id = ?');
    $statement->bind_param('si', $address, $user['id']);
    $statement->execute();
    $statement->close();

    $message = 'Address updated successfully.';
    $user = currentUser();
}

$statement = db()->prepare(
    'SELECT id, product_name, currency, amount, shipping_address, status, stripe_session_id, stripe_payment_status, created_at
     FROM orders
     WHERE user_id = ?
     ORDER BY created_at DESC, id DESC'
);
$statement->bind_param('i', $user['id']);
$statement->execute();
$orders = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
$statement->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Customers</title>
    <style>
        body {
            margin: 0;
            background: #f7f1e4;
            font-family: Arial, sans-serif;
            color: #1f1a12;
            padding: 24px;
        }

        .wrap {
            width: min(1080px, 100%);
            margin: 0 auto;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 22px;
        }

        a {
            color: #0f766e;
        }

        .card {
            background: #fffdfa;
            border: 1px solid #e5dac6;
            border-radius: 22px;
            padding: 24px;
            margin-bottom: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            text-align: left;
            padding: 14px 10px;
            border-bottom: 1px solid #eee3d3;
            vertical-align: top;
        }

        .badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            background: #ecfdf5;
            color: #065f46;
        }

        .badge.pending {
            background: #fff7ed;
            color: #9a3412;
        }

        .badge.canceled {
            background: #fff1f2;
            color: #9f1239;
        }

        textarea, button {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            border: 1px solid #d8cbb7;
            font: inherit;
        }

        button {
            background: #0f766e;
            color: #fff;
            border: 0;
            cursor: pointer;
            margin-top: 12px;
        }

        .success {
            background: #ecfdf5;
            border: 1px solid #6ee7b7;
            color: #065f46;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 14px;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="nav">
            <div>
                <h1>My Customers</h1>
                <p>Orders for <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div>
                <a href="index.php">Back to products</a> |
                <a href="logout.php">Logout</a>
            </div>
        </div>

        <div class="card">
            <h2>Customer Address</h2>
            <?php if ($message !== ''): ?>
                <div class="success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <form method="post">
                <textarea name="address" rows="4" placeholder="Type your address here"><?= htmlspecialchars((string) ($user['address'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                <button type="submit">Save address</button>
            </form>
        </div>

        <div class="card">
            <?php if ($orders === []): ?>
                <p>No orders yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Address</th>
                            <th>Stripe</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <?php $statusClass = in_array($order['status'], ['pending', 'canceled'], true) ? $order['status'] : ''; ?>
                            <tr>
                                <td>#<?= (int) $order['id'] ?></td>
                                <td><?= htmlspecialchars($order['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>R$ <?= htmlspecialchars(number_format(((int) $order['amount']) / 100, 2, ',', '.'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td><?= nl2br(htmlspecialchars((string) ($order['shipping_address'] ?? '-'), ENT_QUOTES, 'UTF-8')) ?></td>
                                <td>
                                    <?= htmlspecialchars($order['stripe_payment_status'] ?: '-', ENT_QUOTES, 'UTF-8') ?><br>
                                    <small><?= htmlspecialchars($order['stripe_session_id'] ?: '-', ENT_QUOTES, 'UTF-8') ?></small>
                                </td>
                                <td><?= htmlspecialchars($order['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

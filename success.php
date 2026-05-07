<?php

declare(strict_types=1);

require __DIR__ . '/auth.php';

$user = requireLogin();
$sessionId = trim((string) ($_GET['session_id'] ?? ''));
$orderId = (int) ($_GET['order_id'] ?? 0);
$message = 'Payment approved.';

try {
    if ($sessionId !== '' && $orderId > 0) {
        $session = getStripeCheckoutSession($sessionId);
        $paymentStatus = (string) ($session['payment_status'] ?? 'unpaid');
        $status = $paymentStatus === 'paid' ? 'paid' : $paymentStatus;

        $statement = db()->prepare(
            'UPDATE orders SET status = ?, stripe_session_id = ?, stripe_payment_status = ? WHERE id = ? AND user_id = ?'
        );
        $statement->bind_param('sssii', $status, $sessionId, $paymentStatus, $orderId, $user['id']);
        $statement->execute();
        $statement->close();
    }
} catch (Throwable $exception) {
    $message = 'Payment finished, but the local order status could not be updated yet.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Arial, sans-serif;
            background: #ecfdf5;
            color: #064e3b;
            padding: 24px;
        }

        .card {
            max-width: 680px;
            background: #ffffff;
            border: 1px solid #a7f3d0;
            border-radius: 18px;
            padding: 28px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Payment approved</h1>
        <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
        <?php if ($sessionId !== ''): ?>
            <p>Session ID: <code><?= htmlspecialchars($sessionId, ENT_QUOTES, 'UTF-8') ?></code></p>
        <?php endif; ?>
        <p><a href="my-customers.php">Open My Customers</a></p>
    </div>
</body>
</html>

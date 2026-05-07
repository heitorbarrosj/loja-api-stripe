<?php

declare(strict_types=1);

require __DIR__ . '/auth.php';

$user = requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed.';
    exit;
}

try {
    $productId = trim((string) ($_POST['product_id'] ?? ''));
    $product = findProduct($productId);

    if ($product === null) {
        throw new RuntimeException('Invalid product selected.');
    }

    $baseUrl = rtrim(env('APP_URL'), '/');
    $status = 'pending';
    $currency = 'brl';
    $shippingAddress = trim((string) ($user['address'] ?? ''));

    $statement = db()->prepare(
        'INSERT INTO orders (user_id, product_code, product_name, currency, amount, shipping_address, status) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $statement->bind_param(
        'isssiss',
        $user['id'],
        $product['id'],
        $product['name'],
        $currency,
        $product['unit_amount'],
        $shippingAddress,
        $status
    );
    $statement->execute();
    $orderId = db()->insert_id;
    $statement->close();

    $session = createStripeCheckoutSession([
        'mode' => 'payment',
        'customer_email' => $user['email'],
        'success_url' => $baseUrl . '/success.php?session_id={CHECKOUT_SESSION_ID}&order_id=' . $orderId,
        'cancel_url' => $baseUrl . '/cancel.php?order_id=' . $orderId,
        'metadata[order_id]' => (string) $orderId,
        'metadata[user_id]' => (string) $user['id'],
        'line_items[0][price_data][currency]' => 'brl',
        'line_items[0][price_data][product_data][name]' => $product['name'],
        'line_items[0][price_data][product_data][description]' => $product['description'],
        'line_items[0][price_data][unit_amount]' => $product['unit_amount'],
        'line_items[0][quantity]' => 1,
    ]);

    $statement = db()->prepare('UPDATE orders SET stripe_session_id = ? WHERE id = ?');
    $statement->bind_param('si', $session['id'], $orderId);
    $statement->execute();
    $statement->close();

    header('Location: ' . $session['url']);
    exit;
} catch (Throwable $exception) {
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Stripe Error</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #fff7ed;
                color: #7c2d12;
                padding: 32px;
            }

            .box {
                max-width: 720px;
                margin: 0 auto;
                background: #fffbeb;
                border: 1px solid #fdba74;
                border-radius: 16px;
                padding: 24px;
            }
        </style>
    </head>
    <body>
        <div class="box">
            <h1>Stripe integration error</h1>
            <p><?= htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') ?></p>
            <p>Check your <code>.env</code> values, database connection, and Stripe secret key.</p>
            <p><a href="index.php">Back to store</a></p>
        </div>
    </body>
    </html>
    <?php
}

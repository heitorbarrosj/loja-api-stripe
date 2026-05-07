<?php

declare(strict_types=1);

require __DIR__ . '/auth.php';

if (currentUser() !== null) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    try {
        $statement = db()->prepare('SELECT id, password_hash FROM users WHERE email = ? LIMIT 1');
        $statement->bind_param('s', $email);
        $statement->execute();
        $user = $statement->get_result()->fetch_assoc();
        $statement->close();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new RuntimeException('Invalid email or password.');
        }

        loginUser((int) $user['id']);
        header('Location: index.php');
        exit;
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #f8f4ec;
            font-family: Arial, sans-serif;
            padding: 24px;
        }

        .card {
            width: min(100%, 420px);
            background: #fff;
            border: 1px solid #eadfcd;
            border-radius: 20px;
            padding: 28px;
        }

        input, button {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            border: 1px solid #d8cbb7;
            margin-bottom: 14px;
        }

        button {
            background: #0f766e;
            color: #fff;
            border: 0;
            cursor: pointer;
        }

        .error {
            background: #fff1f2;
            border: 1px solid #fda4af;
            color: #9f1239;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 14px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Login</h1>
        <?php if ($error !== ''): ?>
            <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p><a href="register.php">Create account</a></p>
        <p><a href="index.php">Back to store</a></p>
    </div>
</body>
</html>

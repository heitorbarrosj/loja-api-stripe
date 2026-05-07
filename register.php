<?php

declare(strict_types=1);

require __DIR__ . '/auth.php';

if (currentUser() !== null) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $address = trim((string) ($_POST['address'] ?? ''));

    try {
        if ($name === '' || $email === '' || $password === '') {
            throw new RuntimeException('Fill in all fields.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Invalid email.');
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $statement = db()->prepare('INSERT INTO users (name, email, password_hash, address) VALUES (?, ?, ?, ?)');
        $statement->bind_param('ssss', $name, $email, $passwordHash, $address);
        $statement->execute();
        $statement->close();

        header('Location: index.php?registered=1');
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
    <title>Create account</title>
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

        input, textarea, button {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            border: 1px solid #d8cbb7;
            margin-bottom: 14px;
            font: inherit;
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
        <h1>Create account</h1>
        <?php if ($error !== ''): ?>
            <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="name" placeholder="Full name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <textarea name="address" placeholder="Address" rows="4"></textarea>
            <button type="submit">Register</button>
        </form>
        <p><a href="login.php">I already have an account</a></p>
        <p><a href="index.php">Back to store</a></p>
    </div>
</body>
</html>

<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function currentUser(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $statement = db()->prepare('SELECT id, name, email, address, created_at FROM users WHERE id = ? LIMIT 1');
    $statement->bind_param('i', $_SESSION['user_id']);
    $statement->execute();
    $result = $statement->get_result()->fetch_assoc();
    $statement->close();

    return $result ?: null;
}

function requireLogin(): array
{
    $user = currentUser();

    if ($user === null) {
        header('Location: login.php');
        exit;
    }

    return $user;
}

function loginUser(int $userId): void
{
    $_SESSION['user_id'] = $userId;
}

function logoutUser(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

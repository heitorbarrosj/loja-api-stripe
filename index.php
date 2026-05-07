<?php

declare(strict_types=1);

require __DIR__ . '/auth.php';

$user = currentUser();
$search = trim((string) ($_GET['q'] ?? ''));
$sort = (string) ($_GET['sort'] ?? 'default');
$products = getProducts();

if ($search !== '') {
    $products = array_values(array_filter(
        $products,
        static fn(array $product): bool => stripos($product['name'], $search) !== false
    ));
}

if ($sort === 'price_asc') {
    usort($products, static fn(array $a, array $b): int => $a['unit_amount'] <=> $b['unit_amount']);
} elseif ($sort === 'price_desc') {
    usort($products, static fn(array $a, array $b): int => $b['unit_amount'] <=> $a['unit_amount']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(env('APP_NAME', 'Stripe PHP Demo'), ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        :root {
            --bg: #f7f1e4;
            --card: #fffdfa;
            --text: #1f1a12;
            --muted: #6a5c49;
            --line: #e5dac6;
            --brand: #0f766e;
            --brand-dark: #115e59;
            --danger: #9f1239;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(15, 118, 110, 0.16), transparent 28%),
                linear-gradient(180deg, #faf6ef 0%, var(--bg) 100%);
            min-height: 100vh;
        }

        .wrap {
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
            padding: 24px 0 56px;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 28px;
            background: rgba(255, 253, 250, 0.9);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 14px 18px;
        }

        .brand {
            font-size: 1.2rem;
            font-weight: bold;
        }

        .tabs, .user-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        a.button, button.button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 18px;
            border-radius: 999px;
            border: 0;
            background: var(--brand);
            color: #fff;
            text-decoration: none;
            cursor: pointer;
        }

        a.button.secondary {
            background: #efe6d7;
            color: var(--text);
        }

        .hero {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 24px;
            margin-bottom: 28px;
        }

        .hero-card, .panel {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 24px;
            padding: 28px;
            box-shadow: 0 18px 42px rgba(31, 26, 18, 0.08);
        }

        .eyebrow {
            color: var(--brand);
            letter-spacing: 0.14em;
            text-transform: uppercase;
            font-size: 0.8rem;
            margin-bottom: 12px;
        }

        h1, h2 {
            margin-top: 0;
        }

        p {
            color: var(--muted);
            line-height: 1.6;
        }

        .products {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
        }

        .filters {
            display: grid;
            grid-template-columns: 1.4fr 0.8fr auto;
            gap: 12px;
            margin-bottom: 24px;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 16px;
        }

        .product {
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 22px;
            background: linear-gradient(180deg, #fffdf8 0%, #fffaf1 100%);
        }

        .product h3 {
            margin-top: 0;
            margin-bottom: 8px;
        }

        .price {
            font-size: 1.8rem;
            margin: 18px 0;
            font-weight: bold;
        }

        .product form {
            margin: 0;
        }

        input[type="search"], select {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: #fff;
            font: inherit;
        }

        .product button {
            width: 100%;
        }

        .notice {
            padding: 14px 16px;
            border-radius: 14px;
            margin-bottom: 20px;
        }

        .notice.success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .notice.error {
            background: #fff1f2;
            color: var(--danger);
            border: 1px solid #fda4af;
        }

        ul {
            padding-left: 18px;
        }

        @media (max-width: 860px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .filters {
                grid-template-columns: 1fr;
            }

            .nav {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <header class="nav">
            <div class="brand"><?= htmlspecialchars(env('APP_NAME', 'Stripe PHP Demo'), ENT_QUOTES, 'UTF-8') ?></div>
            <nav class="tabs">
                <a class="button secondary" href="index.php">Products</a>
                <?php if ($user !== null): ?>
                    <a class="button secondary" href="my-customers.php">My Customers</a>
                <?php endif; ?>
            </nav>
            <div class="user-actions">
                <?php if ($user !== null): ?>
                    <span>Signed in as <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <a class="button secondary" href="logout.php">Logout</a>
                <?php else: ?>
                    <a class="button secondary" href="login.php">Login</a>
                    <a class="button" href="register.php">Create account</a>
                <?php endif; ?>
            </div>
        </header>

        <?php if (isset($_GET['registered'])): ?>
            <div class="notice success">Account created. You can log in now.</div>
        <?php endif; ?>

        <?php if (isset($_GET['logged_out'])): ?>
            <div class="notice success">You have been logged out.</div>
        <?php endif; ?>

        <?php if (isset($_GET['canceled'])): ?>
            <div class="notice error">Payment canceled. Your order is still visible in the My Customers tab.</div>
        <?php endif; ?>

        <section class="hero">
            <div class="hero-card">
                <div class="eyebrow">BRL Store</div>
                <h1>Sell with Stripe in Brazilian real.</h1>
                <p>
                    Explore the catalog below. The customer signs in, selects a product,
                    pays with Stripe Checkout, and then sees the order in the <strong>My Customers</strong> tab.
                </p>
                <ul>
                    <li>50 products with prices between R$ 10,00 and R$ 100,00.</li>
                    <li>Search field and sorting by lower or higher price.</li>
                    <li>Address saved in the customer account and editable later.</li>
                </ul>
            </div>
            <aside class="panel">
                <h2>Setup</h2>
                <p>1. Import <code>database.sql</code> into MySQL.</p>
                <p>2. Edit <code>.env</code> with your Stripe and MySQL credentials.</p>
                <p>3. Open <code>http://localhost/ApiStripe/index.php</code>.</p>
            </aside>
        </section>

        <form class="filters" method="get">
            <input type="search" name="q" placeholder="Search by product name" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
            <select name="sort">
                <option value="default"<?= $sort === 'default' ? ' selected' : '' ?>>Default order</option>
                <option value="price_asc"<?= $sort === 'price_asc' ? ' selected' : '' ?>>Lowest price first</option>
                <option value="price_desc"<?= $sort === 'price_desc' ? ' selected' : '' ?>>Highest price first</option>
            </select>
            <button class="button" type="submit">Filter</button>
        </form>

        <section class="products">
            <?php if ($products === []): ?>
                <article class="product">
                    <h3>No products found</h3>
                    <p>Try another search term or remove the price filter.</p>
                </article>
            <?php endif; ?>

            <?php foreach ($products as $product): ?>
                <article class="product">
                    <h3><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="price">R$ <?= htmlspecialchars($product['display_price'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php if ($user !== null): ?>
                        <form action="checkout.php" method="post">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') ?>">
                            <button class="button" type="submit">Buy now</button>
                        </form>
                    <?php else: ?>
                        <a class="button" href="login.php">Login to buy</a>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    </div>
</body>
</html>

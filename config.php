<?php

declare(strict_types=1);

function loadEnv(string $path): array
{
    if (!file_exists($path)) {
        throw new RuntimeException('.env file not found at ' . $path);
    }

    $values = [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $key = trim($key);
        $value = trim($value);

        if ($key === '') {
            continue;
        }

        $values[$key] = trim($value, "\"'");
    }

    return $values;
}

function env(string $key, ?string $default = null): string
{
    static $config = null;

    if ($config === null) {
        $config = loadEnv(__DIR__ . DIRECTORY_SEPARATOR . '.env');
    }

    if (array_key_exists($key, $config)) {
        return $config[$key];
    }

    if ($default !== null) {
        return $default;
    }

    throw new RuntimeException("Missing environment variable: {$key}");
}

function createStripeCheckoutSession(array $payload): array
{
    return stripeRequest('POST', '/v1/checkout/sessions', $payload);
}

function getStripeCheckoutSession(string $sessionId): array
{
    return stripeRequest('GET', '/v1/checkout/sessions/' . rawurlencode($sessionId));
}

function stripeRequest(string $method, string $endpoint, array $payload = []): array
{
    $secretKey = env('STRIPE_SECRET_KEY');
    $url = 'https://api.stripe.com' . $endpoint;

    if ($method === 'GET' && $payload !== []) {
        $url .= '?' . http_build_query($payload);
    }

    $curl = curl_init($url);
    $options = [
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $secretKey,
            'Content-Type: application/x-www-form-urlencoded',
        ],
        CURLOPT_RETURNTRANSFER => true,
    ];

    if ($method === 'POST') {
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = http_build_query($payload);
    }

    curl_setopt_array($curl, $options);

    $response = curl_exec($curl);

    if ($response === false) {
        $error = curl_error($curl);
        curl_close($curl);
        throw new RuntimeException('cURL error: ' . $error);
    }

    $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    $data = json_decode($response, true);

    if ($statusCode >= 400) {
        $message = $data['error']['message'] ?? 'Stripe request failed.';
        throw new RuntimeException($message);
    }

    if (!is_array($data)) {
        throw new RuntimeException('Invalid Stripe response.');
    }

    return $data;
}

function getProducts(): array
{
    $prices = [
        78, 24, 91, 35, 62, 49, 88, 17, 54, 73,
        28, 96, 44, 67, 12, 83, 39, 58, 100, 21,
        64, 31, 75, 46, 19, 85, 52, 69, 27, 94,
        41, 60, 14, 81, 33, 57, 98, 26, 71, 43,
        16, 87, 50, 65, 23, 92, 37, 55, 79, 10,
    ];

    $products = [];

    foreach ($prices as $index => $price) {
        $productNumber = $index + 1;
        $products[] = [
            'id' => 'product_' . $productNumber,
            'name' => 'Produto' . $productNumber,
            'description' => 'Descricao do Produto' . $productNumber . ' para a vitrine do ecommerce.',
            'unit_amount' => $price * 100,
            'display_price' => number_format((float) $price, 2, ',', '.'),
        ];
    }

    return $products;
}

function findProduct(string $productId): ?array
{
    foreach (getProducts() as $product) {
        if ($product['id'] === $productId) {
            return $product;
        }
    }

    return null;
}

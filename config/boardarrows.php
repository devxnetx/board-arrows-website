<?php

$revolut = env('REVOLUT_URL');

$coin = function (string $name, string $symbol, $configured, $example = null): ?array {
    $address = filled($configured) ? $configured : $example;
    if (! filled($address)) {
        return null;
    }

    return [
        'name' => $name,
        'symbol' => $symbol,
        'address' => $address,
        'example' => ! filled($configured),
    ];
};

$coins = array_values(array_filter([
    $coin('Bitcoin', 'BTC', env('DONATE_BTC'), 'bc1qexampledonotsend000000000000000000000'),
    $coin('Ethereum', 'ETH', env('DONATE_ETH')),
    $coin('Litecoin', 'LTC', env('DONATE_LTC')),
    $coin('Solana', 'SOL', env('DONATE_SOL'), 'ExampleSoLanaDoNotSend11111111111111111111'),
]));

return [
    'chrome_url' => env('CHROME_STORE_URL') ?: null,
    'firefox_url' => env('FIREFOX_STORE_URL') ?: null,
    'inbox_token' => env('BUG_INBOX_TOKEN') ?: '',
    'admin_password' => env('ADMIN_PASSWORD') ?: '',
    'revolut_url' => is_string($revolut) && str_starts_with($revolut, 'https://') ? $revolut : null,
    'coins' => $coins,
];

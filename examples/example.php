<?php

/**
 * Auto API PHP Client — Complete usage example.
 *
 * Replace 'your-api-key' with your actual API key from https://auto-api.com
 *
 * Run: php example.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use AutoApi\Client;
use AutoApi\Exception\AuthException;
use AutoApi\Exception\ApiException;

$client = new Client('your-api-key');
$source = 'encar';

// --- Get available filters ---

$filters = $client->getFilters($source);

echo "Available brands:\n";
foreach (array_keys($filters['mark']) as $brand) {
    echo "  - {$brand}\n";
}

echo "\nTransmission types: " . implode(', ', $filters['transmission_type']) . "\n";
echo "Body types: " . implode(', ', $filters['body_type']) . "\n";

// --- Search offers with filters ---

$offers = $client->getOffers($source, [
    'page' => 1,
    'brand' => 'Hyundai',
    'year_from' => 2020,
    'price_to' => 50000,
]);

echo "\n--- Offers (page {$offers['meta']['page']}) ---\n";
foreach ($offers['result'] as $item) {
    $d = $item['data'];
    echo "{$d['mark']} {$d['model']} {$d['year']} — \${$d['price']} ({$d['km_age']} km)\n";
}

// Pagination
if (isset($offers['meta']['next_page'])) {
    $nextPage = $client->getOffers($source, [
        'page' => $offers['meta']['next_page'],
        'brand' => 'Hyundai',
        'year_from' => 2020,
    ]);
    echo "Next page has " . count($nextPage['result']) . " offers\n";
}

// --- Get single offer ---

$innerId = $offers['result'][0]['inner_id'] ?? '40427050';
$offer = $client->getOffer($source, $innerId);

echo "\n--- Single offer ---\n";
echo "URL: {$offer['data']['url']}\n";
echo "Seller: {$offer['data']['seller_type']}\n";
echo "Images: " . count($offer['data']['images']) . "\n";

// --- Track changes ---

$changeId = $client->getChangeId($source, '2025-01-15');
echo "\n--- Changes from 2025-01-15 (change_id: {$changeId}) ---\n";

$changes = $client->getChanges($source, $changeId);
foreach ($changes['result'] as $change) {
    echo "[{$change['change_type']}] {$change['inner_id']}\n";
}

// Fetch next batch
if (isset($changes['meta']['next_change_id'])) {
    $moreChanges = $client->getChanges($source, $changes['meta']['next_change_id']);
    echo "Next batch: " . count($moreChanges['result']) . " changes\n";
}

// --- Get offer by URL ---

$info = $client->getOfferByUrl('https://www.encar.com/dc/dc_cardetailview.do?carid=40427050');
echo "\n--- Offer by URL ---\n";
echo "{$info['mark']} {$info['model']} {$info['year']} — \${$info['price']}\n";

// --- Error handling ---

try {
    $badClient = new Client('invalid-key');
    $badClient->getOffers('encar', ['page' => 1]);
} catch (AuthException $e) {
    echo "\nAuth error: {$e->getMessage()} (HTTP {$e->getStatusCode()})\n";
} catch (ApiException $e) {
    echo "\nAPI error: {$e->getMessage()} (HTTP {$e->getStatusCode()})\n";
}

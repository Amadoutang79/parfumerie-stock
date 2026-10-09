<?php

use App\Models\Product;

test('that true is true', function () {
    expect(true)->toBeTrue();
});

test('products without an image use an offline avatar', function () {
    $imageUrl = (new Product(['name' => 'Eros']))->image_url;

    expect($imageUrl)->toStartWith('data:image/svg+xml;base64,');
    expect(base64_decode(substr($imageUrl, strlen('data:image/svg+xml;base64,')), true))
        ->toContain('<svg', 'ER');
});

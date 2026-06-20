<?php

use LeafyTech\Core\Header;

test('it can get header values dynamically', function () {
    $header = new Header([
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer token',
    ]);

    expect($header->{'Content-Type'})->toBe('application/json')
        ->and($header->Authorization)->toBe('Bearer token')
        ->and($header->NonExistent)->toBeNull();
});

<?php

use LeafyTech\Core\Helpers\Env;

test('it retrieves env variables and defaults', function () {
    $_ENV['TEST_KEY'] = 'test_value';

    expect(Env::get('TEST_KEY'))->toBe('test_value')
        ->and(Env::get('NON_EXISTENT_KEY', 'default'))->toBe('default')
        ->and(Env::get('NON_EXISTENT_KEY'))->toBeNull();

    unset($_ENV['TEST_KEY']);
});

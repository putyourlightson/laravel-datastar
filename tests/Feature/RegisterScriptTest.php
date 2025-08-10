<?php
/**
 * Tests the register script class.
 */

use Putyourlightson\Datastar\Http\Middleware\RegisterScript;

test('Test that the correct version is registered', function() {
    $filePath = realpath(__DIR__ . '/../../public/datastar/' . RegisterScript::VERSION . '/datastar.js');

    expect(file_exists($filePath))
        ->toBeTrue();
});

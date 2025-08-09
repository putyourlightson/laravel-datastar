<?php

use Putyourlightson\Datastar\Helpers\Action;

test('Test creating an action', function(string $method) {
    $value = Action::getAction($method, 'test', []);
    expect($value)
        ->toStartWith("@$method(")
        ->toContain('test');

    if ($method === 'get') {
        expect($value)
            ->not->toContain('X-CSRF-TOKEN');
    } else {
        expect($value)
            ->toContain('X-CSRF-TOKEN');
    }
})->with([
    'get',
    'post',
    'put',
    'patch',
    'delete',
]);

test('Test creating an action containing an array of primitive params', function() {
    $value = Action::getAction('get','route', ['x' => 1, 'y' => 'string', 'z' => true]);
    expect($value)
        ->toContain('1', 'string', 'true');
});

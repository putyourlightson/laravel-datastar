<?php

use Putyourlightson\Datastar\Helpers\Datastar;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

test('Test creating an action', function(string $method) {
    $helper = new Datastar();
    $value = $helper->$method('view');
    expect($value)
        ->toStartWith("@$method(")
        ->toContain('view');

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
    $helper = new Datastar();
    $value = $helper->get('route', ['x' => 1, 'y' => 'string', 'z' => true]);
    expect($value)
        ->toContain('1', 'string', 'true');
});

test('Test that creating an action containing a reserved params name throws an exception', function() {
    $helper = new Datastar();
    $signalsVariableName = config('datastar.signalsVariableName');
    $helper->get('route', [$signalsVariableName => 1]);
})->throws(BadRequestHttpException::class);

test('Test that creating an action containing an object param throws an exception', function() {
    $helper = new Datastar();
    $helper->get('route', ['object' => new stdClass()]);
})->throws(BadRequestHttpException::class);

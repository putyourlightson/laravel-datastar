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

test('Test creating an action containing an array of primitive variables', function() {
    $helper = new Datastar();
    $value = $helper->get('template', ['x' => 1, 'y' => 'string', 'z' => true]);
    expect($value)
        ->toContain('1', 'string', 'true');
});

test('Test that creating an action containing a reserved variable name throws an exception', function() {
    $helper = new Datastar();
    $signalsVariableName = config('datastar.signalsVariableName');
    $helper->get('template', [$signalsVariableName => 1]);
})->throws(BadRequestHttpException::class);

test('Test that creating an action containing an object variable throws an exception', function() {
    $helper = new Datastar();
    $helper->get('template', ['object' => new stdClass()]);
})->throws(BadRequestHttpException::class);

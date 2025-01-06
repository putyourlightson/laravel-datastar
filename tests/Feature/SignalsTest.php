<?php

use Putyourlightson\Datastar\Models\Signals;
use Putyourlightson\Datastar\Services\Sse;

beforeEach(function() {
    $sse = Mockery::mock(Sse::class);
    app()->instance(Sse::class, $sse);
});

test('Test getting a signal value', function() {
    $signals = new Signals(['a' => 1]);
    expect($signals->get('a'))
        ->toBe(1);
});

test('Test getting a signal value using a magic call', function() {
    $signals = new Signals(['a' => 1]);
    expect($signals->a)
        ->toBe(1);
});

test('Test getting a nested signal value', function() {
    $signals = new Signals(['a' => ['b' => ['c' => 1]]]);
    expect($signals->get('a.b.c'))
        ->toBe(1);
});

test('Test getting a missing signal value', function() {
    $signals = new Signals(['a' => 1]);
    expect($signals->get('x'))
        ->toBeNull();
});

test('Test getting a missing signal value using a magic call', function() {
    $signals = new Signals(['a' => 1]);
    expect($signals->x)
        ->toBeNull();
});

test('Test adding a signal', function() {
    app(Sse::class)->shouldReceive('mergeSignals');
    $signals = new Signals([]);
    $signals->set('a', 1);
    expect($signals->get('a'))
        ->toBe(1);
});

test('Test adding a signal using a magic call', function() {
    app(Sse::class)->shouldReceive('mergeSignals');
    $signals = new Signals([]);
    $signals->a(1);
    expect($signals->get('a'))
        ->toBe(1);
});

test('Test modifying an existing signal', function() {
    app(Sse::class)->shouldReceive('mergeSignals');
    $signals = new Signals(['a' => 1]);
    $signals->set('a', 2);
    expect($signals->get('a'))
        ->toBe(2);
});

test('Test modifying an existing signal using a magic call', function() {
    app(Sse::class)->shouldReceive('mergeSignals');
    $signals = new Signals(['a' => 1]);
    $signals->a(2);
    expect($signals->get('a'))
        ->toBe(2);
});

test('Test adding a nested signal', function() {
    app(Sse::class)->shouldReceive('mergeSignals');
    $signals = new Signals([]);
    $signals->set('a.b.c', 1);
    expect($signals->get('a.b.c'))
        ->toBe(1);
});

test('Test modifying an existing nested signal', function() {
    app(Sse::class)->shouldReceive('mergeSignals');
    $signals = new Signals(['a' => ['b' => ['c' => 1]]]);
    $signals->set('a.b.c', 2);
    expect($signals->get('a.b.c'))
        ->toBe(2);
});

test('Test removing a signal value', function() {
    app(Sse::class)->shouldReceive('removeSignals');
    $signals = new Signals(['a' => 1]);
    $signals->remove('a');
    expect($signals->getValues())
        ->toBe([]);
});

test('Test removing a nested signal value', function() {
    app(Sse::class)->shouldReceive('removeSignals');
    $signals = new Signals(['a' => ['b' => ['c' => 1]]]);
    $signals->remove('a.b.c');
    expect($signals->getValues())
        ->toBe(['a' => ['b' => []]]);
});

<?php

use Illuminate\Validation\ValidationException;
use Putyourlightson\Datastar\Models\Config;

test('Test that creating a config containing a reserved param name throws an exception', function() {
    $signalsVariableName = config('datastar.signalsVariableName');
    $config = new Config(['route' => 'test', 'params' => [$signalsVariableName => 1]]);
    $config->validate();
})->throws(ValidationException::class);

test('Test that creating a config containing an object param throws an exception', function() {
    $config = new Config(['route' => 'test', 'params' => ['object' => new stdClass()]]);
    $config->validate();
})->throws(ValidationException::class);

<?php

/**
 * Tests the SSE service.
 */

use Putyourlightson\Datastar\Services\SseService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

test('Test that calling an SSE method when another one is in process throws an exception', function() {
    app(SseService::class)->setSseInProcess('patchElements');
    app(SseService::class)->patchSignals([]);
})->throws(BadRequestHttpException::class);

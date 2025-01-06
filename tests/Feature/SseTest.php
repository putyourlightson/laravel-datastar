<?php

/**
 * Tests the SSE service.
 */

use Putyourlightson\Datastar\Services\Sse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

test('Test that calling an SSE method when another one is in process throws an exception', function() {
    app(Sse::class)->setSseInProcess('mergeFragments');
    app(Sse::class)->mergeSignals([]);
})->throws(BadRequestHttpException::class);

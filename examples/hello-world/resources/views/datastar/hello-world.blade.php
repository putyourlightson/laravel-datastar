@php
    /** @var \Putyourlightson\Datastar\Models\Signals $signals */
    $delay = $signals['delay'] ?? 100;
    $message = 'Hello, world!';
@endphp

@for ($i = 0; $i < strlen($message); $i++)
    @patchelements
        <div id="message">
            {{ substr($message, 0, $i + 1) }}
        </div>
    @endpatchelements
    @php
        // Sleep for the provided delay in milliseconds.
        usleep($delay * 1000);
    @endphp
@endfor

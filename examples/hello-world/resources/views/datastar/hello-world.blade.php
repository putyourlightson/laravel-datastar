@php
    /** @var \Putyourlightson\Datastar\Models\Signals $signals */
    $delay = $signals->get('delay', 0);
    $message = 'Hello, world!';
@endphp

@for ($i = 0; $i < strlen($message); $i++)
    @mergefragments
        <div id="message">
            {{ substr($message, 0, $i + 1) }}
        </div>
    @endmergefragments
    @php
        // Sleep for the provided delay in milliseconds.
        usleep($delay * 1000);
    @endphp
@endfor

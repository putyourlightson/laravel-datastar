<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

return [
    /**
     * Whether to register the Datastar script on the front-end.
     */
    'registerScript' => true,

    /**
     * Whether the session should be closed when the event stream begins.
     * This is useful to allow other requests to be processed while the event stream is being sent.
     */
    'shouldCloseSession' => false,

    /**
     * The name of the signals variable that will be injected into Datastar templates.
     */
    'signalsVariableName' => 'signals',

    /**
     * The event options to override the Datastar defaults. Null values will be ignored.
     */
    'defaultEventOptions' => [
        'retryDuration' => 1000,
    ],

    /**
     * The element options to override the Datastar defaults. Null values will be ignored.
     */
    'defaultElementOptions' => [
        'settleDuration' => null,
        'useViewTransition' => null,
    ],

    /**
     * The signal options to override the Datastar defaults. Null values will be ignored.
     */
    'defaultSignalOptions' => [
        'onlyIfMissing' => null,
    ],

    /**
     * The execute script options to override the Datastar defaults. Null values will be ignored.
     */
    'defaultExecuteScriptOptions' => [
        'autoRemove' => null,
        'attributes' => null,
    ],
];

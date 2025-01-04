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
     * The name of the signals variable that will be injected into Datastar templates.
     */
    'signalsVariableName' => 'signals',

    /**
     * The event options to override the Datastar defaults. Null values will be ignored.
     */
    'defaultEventOptions' => [
        'retryDuration' => 123,
    ],

    /**
     * The fragment options to override the Datastar defaults. Null values will be ignored.
     */
    'defaultFragmentOptions' => [
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

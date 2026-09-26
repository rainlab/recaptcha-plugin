<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled State Override
    |--------------------------------------------------------------------------
    |
    | Overrides the "Enabled" backend setting when set to a non-null value.
    | Set RECAPTCHA_ENABLED=false in a local environment to bypass rendering
    | and verification entirely. When null, the backend setting decides.
    |
    */

    'enabled' => env('RECAPTCHA_ENABLED', null),

];

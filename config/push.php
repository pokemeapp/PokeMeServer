<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Apple Push Notifications
    |--------------------------------------------------------------------------
    |
    | Set the path to the certificate.pem file. A password can be provided if
    | the certificate is secured.
    |
    */

    'apns' => [
        'environment' => env('PUSH_APNS_ENVIRONMENT', 'production'),
        'root' => env('PUSH_APNS_ROOT', __DIR__ . '/push/certificates.pem'),
        'certificate' => env('PUSH_APNS_CERTIFICATE'),
        'password' => env('PUSH_APNS_PASSWORD')
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Cloud Messaging
    |--------------------------------------------------------------------------
    |
    | Set the GCM API key
    |
    */

    'gcm' => [
        'key' => env('PUSH_GCM_KEY')
    ],

    /*
    |--------------------------------------------------------------------------
    | Tasks settings
    |--------------------------------------------------------------------------
    |
    | chunk : the size of the chunk batch loop
    |
    */

    'chunk' => 100,

    /*
    |--------------------------------------------------------------------------
    | Return message payload send to service
    |--------------------------------------------------------------------------
    |
    | Set to true to return the payload sent to the underlying service.
    | Only available for APNs
    |
    */

    'payloads' => filter_var(env('PUSH_RETURN_PAYLOADS', false), FILTER_VALIDATE_BOOLEAN),

];
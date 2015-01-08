<?php

/**
 * Sample Tutum configuration
 *
 * Copy this file into your application's config directory
 * and modify it to match your configuration
 */
return [
    // Username for key based authentication
    'username' => 'someuser',

    // API key for key based authentication
    'apikey' => 'XXXXXXXXXXXXXXXX',

    // Redis server options
    'redis' => [
        // Name of the Tutum Redis service
        'service' => 'redis',

        // Redis server password
        'password' => 'XXXXXXXXXXXXXXXX'
    ]
];

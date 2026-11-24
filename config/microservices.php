<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mode integrasi modul
    |--------------------------------------------------------------------------
    |
    | "local"  : modul berbagi database (kondisi monolit saat ini).
    | "http"   : modul saling memanggil internal API (menuju pemisahan service).
    |
    */
    'mode' => env('MICROSERVICE_MODE', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Token antar-service
    |--------------------------------------------------------------------------
    |
    | Token yang wajib dikirim pada header X-Service-Token / Bearer untuk
    | mengakses endpoint /api/internal/*. Saat rotasi, isi token lama pada
    | previous beserta expired_atnya, lalu pasang token baru di "token".
    |
    */
    'token' => env('MICROSERVICE_TOKEN'),
    'token_expires_at' => env('MICROSERVICE_TOKEN_EXPIRES_AT'),
    'token_previous' => env('MICROSERVICE_TOKEN_PREVIOUS'),
    'token_previous_expires_at' => env('MICROSERVICE_TOKEN_PREVIOUS_EXPIRES_AT'),

    /*
    |--------------------------------------------------------------------------
    | Batas permintaan internal API
    |--------------------------------------------------------------------------
    |
    | Pembatasan diterapkan per identitas service (hash token), bukan per IP,
    | agar satu service yang bermasalah tidak memenuhi kuota service lain.
    |
    */
    'rate_limit' => [
        'per_minute' => (int) env('MICROSERVICE_RATE_LIMIT_PER_MINUTE', 120),
    ],

    /*
    |--------------------------------------------------------------------------
    | Transport outbox
    |--------------------------------------------------------------------------
    |
    | "log"  : event dicatat ke log (satu proses).
    | "http" : event dikirim ke webhook/broker.
    |
    */
    'event_transport' => env('MICROSERVICE_EVENT_TRANSPORT', 'log'),

    'event_webhook' => env('MICROSERVICE_EVENT_WEBHOOK'),

    'timeout' => (int) env('MICROSERVICE_HTTP_TIMEOUT', 5),

    /*
    |--------------------------------------------------------------------------
    | Reliability outbox
    |--------------------------------------------------------------------------
    |
    | Circuit breaker menghentikan relay sementara saat tujuan gagal terus-
    | menerus. Event yang melewati max_attempts masuk dead-letter dan bisa
    | dikembalikan ke antrean lewat domain-events:replay-dead-letter.
    |
    */
    'circuit_breaker' => [
        'failure_threshold' => (int) env('MICROSERVICE_CIRCUIT_FAILURE_THRESHOLD', 5),
        'cooldown_seconds' => (int) env('MICROSERVICE_CIRCUIT_COOLDOWN_SECONDS', 60),
    ],

    'outbox' => [
        'max_attempts' => (int) env('MICROSERVICE_OUTBOX_MAX_ATTEMPTS', 5),
        'backoff_seconds' => (int) env('MICROSERVICE_OUTBOX_BACKOFF_SECONDS', 30),
    ],

    'services' => [
        'orgmate' => [
            'url' => env('ORGMATE_SERVICE_URL'),
        ],
        'eprogress' => [
            'url' => env('EPROGRESS_SERVICE_URL'),
        ],
        'sentra' => [
            'url' => env('SENTRA_SERVICE_URL'),
        ],
    ],
];

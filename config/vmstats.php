<?php

return [
    'wiki_base_url' => env('WIKI_BASE_URL', 'https://wiki.example.com/index.php/'),

    /*
    |--------------------------------------------------------------------------
    | API authentication feature flag
    |--------------------------------------------------------------------------
    |
    | When true, the mutating API routes (POST /api/vms, POST /api/vms/delete,
    | POST /api/servers/delete, POST /api/server/notes, POST /api/guest/notes)
    | require a valid Sanctum bearer token. When false, they are accessible
    | without authentication.
    |
    | This is a runtime flag — read on every request via the ApiAuthIfEnabled
    | middleware, so flipping the env var takes effect without rebuilding the
    | route cache.
    |
    | GET /api/servers is always protected regardless of this flag.
    |
    */
    'api_auth_required' => env('VMSTATS_API_AUTH_REQUIRED', false),
];

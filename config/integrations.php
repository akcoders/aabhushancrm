<?php

return [
    'interakt' => [
        'api_key' => env('INTERAKT_API_KEY'),
        'base_url' => env('INTERAKT_BASE_URL', 'https://api.interakt.ai/v1/public'),
        'webhook_secret' => env('INTERAKT_WEBHOOK_SECRET'),
    ],
    'meta' => [
        'access_token' => env('META_ACCESS_TOKEN'),
        'ad_account_id' => env('META_AD_ACCOUNT_ID'),
        'page_id' => env('META_PAGE_ID'),
        'instagram_account_id' => env('META_INSTAGRAM_ACCOUNT_ID'),
        'webhook_verify_token' => env('META_WEBHOOK_VERIFY_TOKEN'),
        'graph_version' => env('META_GRAPH_VERSION', 'v23.0'),
    ],
    'jitsi' => [
        'domain' => env('JITSI_DOMAIN', 'meet.jit.si'),
        'jaas_app_id' => env('JAAS_APP_ID'),
        'jaas_key_id' => env('JAAS_KEY_ID'),
        'jaas_private_key_base64' => env('JAAS_PRIVATE_KEY_BASE64'),
        'jaas_private_key_path' => env('JAAS_PRIVATE_KEY_PATH'),
    ],
];

<?php

return [
    'portal' => [
        'maintenance_mode' => false,
    ],
    'user' => [
        'accept_use_terms' => false,
        'language' => 'de',
        'show_subscriptions_to_home_page' => false,
    ],
    'opencast' => [
        'url' => 'localhost:8080',
        'username' => 'admin',
        'password' => 'opencast',
        'archive_path' => '/archive/mh_default',
        'default_workflow' => 'fast',
        'upload_workflow_id' => 'fast',
        'theme_id_top_right' => '500',
        'theme_id_top_left' => '501',
        'theme_id_bottom_left' => '502',
        'theme_id_bottom_right' => '503',
    ],
    'streaming' => [
        'engine_url' => 'localhost:1935',
        'api_url' => 'localhost:8087',
        'username' => 'digest_user',
        'password' => 'digest_password',
        'content_path' => '/content/videoportal',
        'secure_token' => 'awsTides12tvv10',
        'token_prefix' => 'tides',
    ],
    'elasticSearch' => [
        'url' => 'localhost',
        'port' => 9200,
        'username' => 'elastic',
        'password' => 'changeme',
        'prefix' => 'tides_',
    ],
];
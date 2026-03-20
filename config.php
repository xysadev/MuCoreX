<?php
return [
    'app' => [
        'base_url' => (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') 
            . $_SERVER['HTTP_HOST'] . '/WebEngine/MuCoreX/'
    ],
    'db' => [
        'env'         => 'dev', # prod / dev
        'host'        => 'localhost',
        'dbname'      => 'MuOnlineBase',
        'user'        => 'sa',
        'pass'        => '493603',
        'force_https' => false
    ]
];
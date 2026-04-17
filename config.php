<?php
return [
    'app' => [
        'base_url' => (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') 
            . $_SERVER['HTTP_HOST'] . '/MuCoreX/'
    ],
    'db' => [
        'env'         => 'prod',
        'host'        => 'localhost',
        'dbname'      => 'MuOnline',
        'user'        => 'sa',
        'pass'        => 'tu_pass',
        'force_https' => false
    ]
];
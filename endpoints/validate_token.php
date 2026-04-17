<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Core.php';

$config = require __DIR__ . '/../config.php';

$core = new Core(new Database($config));

header('Content-Type: application/json; charset=utf-8');

/* AUTH */
$auth = $core->auth(true);

/* RESPONSE */
$core->json([
    'status' => 'ok',
    'user_id' => $auth['user_id'],
    'last_action' => $auth['last_action_at']
]);
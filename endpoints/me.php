<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Core.php';

$config = require __DIR__ . '/../config.php';

$core = new Core(new Database($config));

header('Content-Type: application/json; charset=utf-8');

/* AUTH */
$auth = $core->auth(true);

/* USER */
$user = $core->queryOne("
    SELECT memb___id, mail_addr
    FROM MEMB_INFO
    WHERE memb_guid = :uid
", [
    'uid' => $auth['user_id']
]);

if (!$user) {
    $core->json([
        'status' => 'error',
        'message' => 'User not found'
    ], 404);
}

/* RESPONSE */
$core->json([
    'status' => 'ok',
    'user' => [
        'username' => $user['memb___id'],
        'email' => $user['mail_addr']
    ]
]);
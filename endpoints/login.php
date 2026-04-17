<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Core.php';

$config = require __DIR__ . '/../config.php';

$core = new Core(
    new Database($config)
);

header('Content-Type: application/json; charset=utf-8');

/* INPUT */
$user = trim($_POST['username'] ?? '');
$pass = trim($_POST['password'] ?? '');

if (!$user || !$pass) {
    $core->json([
        'status' => 'error',
        'message' => 'Missing credentials'
    ], 400);
}

/* AUTH CHECK */
$row = $core->queryOne("
    SELECT memb___id, memb_guid
    FROM MEMB_INFO
    WHERE memb___id = :user
      AND memb__pwd = :pass
", [
    'user' => $user,
    'pass' => $pass
]);

if (!$row) {
    $core->json([
        'status' => 'error',
        'message' => 'Invalid credentials'
    ], 401);
}

$uid = $row['memb_guid'];
$token = bin2hex(random_bytes(32));

/* UPSERT SESSION */
$updated = $core->execute("
    UPDATE users_tokens
    SET api_token = :token,
        expires_at = DATEADD(HOUR, 2, GETDATE()),
        last_action_at = GETDATE()
    WHERE user_id = :uid
", [
    'uid' => $uid,
    'token' => $token
]);

if ($updated === 0) {
    $core->execute("
        INSERT INTO users_tokens (user_id, api_token, expires_at, last_action_at)
        VALUES (:uid, :token, DATEADD(HOUR, 2, GETDATE()), GETDATE())
    ", [
        'uid' => $uid,
        'token' => $token
    ]);
}

/* RESPONSE */
$core->json([
    'status' => 'ok',
    'token' => $token,
    'user' => $row['memb___id']
]);
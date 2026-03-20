<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Core.php';
require_once __DIR__ . '/../core/Logger.php';

$config = require __DIR__ . '/../config.php';

$logger = new Logger();
$db = new Database($config);
$core = new Core($db, $logger, $config['db']['force_https'] ?? false);

$user = trim($_POST['username'] ?? '');
$pass = trim($_POST['password'] ?? '');

if (!$user || !$pass) {
    $core->json(['status'=>'error','message'=>'Missing credentials']);
}

$row = $core->querySingle("
    SELECT memb___id, memb_guid
    FROM MEMB_INFO
    WHERE memb___id = :user AND memb__pwd = :pass
", [
    'user' => $user,
    'pass' => $pass
]);

if (!$row) {
    $logger->log("Login FAIL: $user");
    $core->json(['status'=>'error','message'=>'Invalid credentials']);
}

$token = bin2hex(random_bytes(32));
$uid = (int)$row['memb_guid'];

$updated = $db->execute("
    UPDATE users_tokens
    SET api_token = :token,
        expires_at = DATEADD(HOUR, 2, GETDATE())
    WHERE user_id = :uid
", [
    'uid'   => $uid,
    'token' => $token
]);

if (!$updated) {
    $db->execute("
        INSERT INTO users_tokens (user_id, api_token, expires_at)
        VALUES (:uid, :token, DATEADD(HOUR, 2, GETDATE()))
    ", [
        'uid'   => $uid,
        'token' => $token
    ]);
}

$logger->log("Login OK: {$row['memb___id']} (UID: $uid, TOKEN: $token)");

$core->json([
    'status'   => 'ok',
    'token'    => $token,
    'user'     => $row['memb___id']
]);
?>
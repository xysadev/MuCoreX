<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Core.php';
require_once __DIR__ . '/../core/Logger.php';

$config = require __DIR__ . '/../config.php';

$logger = new Logger();
$db = new Database($config);
$core = new Core($db, $logger, $config['db']['force_https'] ?? false);

// Obtener token desde Bearer
$token = $core->getBearerToken();

// Validar token y obtener user_id
$auth = $core->validateToken();
$uid = $auth['user_id'];

// Obtener info del usuario
$row = $core->querySingle("
    SELECT memb___id, mail_addr
    FROM MEMB_INFO
    WHERE memb_guid = :uid
", ['uid' => $uid]);

if (!$row) {
    $core->json(['status'=>'error','message'=>'User not found']);
}

// Respuesta final
$core->json([
    'status' => 'ok',
    'user' => $row
]);
<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Core.php';
require_once __DIR__ . '/../core/Logger.php';

$config = require __DIR__ . '/../config.php';

$db = new Database($config);
$logger = new Logger();
$core = new Core($db, $logger);

$core->validateToken();

$core->json([
    'status' => 'ok'
]);
?>
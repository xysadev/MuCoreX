<?php
require_once __DIR__.'/core/Database.php';

$config = require __DIR__.'/config.php';
try {

	$db = new Database($config); // pasar todo $config, no solo $config['db']
    echo "Conexión OK ✅";
} catch (Exception $e) {
    echo "Error de conexión: ".$e->getMessage();


}
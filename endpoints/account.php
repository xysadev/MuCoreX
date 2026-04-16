<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Core.php';

$config = require __DIR__ . '/../config.php';

$core = new Core(
    new Database($config)
);

header('Content-Type: application/json; charset=utf-8');

/* AUTH */
$uid = $core->userId(); // usa cache del Core (NO requireAuth directo)

/* ACCOUNT */
$user = $core->queryOne("
    SELECT memb___id, mail_addr, memb_guid
    FROM MEMB_INFO
    WHERE memb_guid = :uid
", [
    'uid' => $uid
]);

if (!$user) {
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'message' => 'USER_NOT_FOUND'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* CHARACTERS */
$accChars = $core->queryOne("
    SELECT GameID1, GameID2, GameID3, GameID4, GameID5
    FROM AccountCharacter
    WHERE Id = :acc
", [
    'acc' => $user['memb___id']
]);

$names = array_values(array_filter([
    $accChars['GameID1'] ?? null,
    $accChars['GameID2'] ?? null,
    $accChars['GameID3'] ?? null,
    $accChars['GameID4'] ?? null,
    $accChars['GameID5'] ?? null
]));

/* STATS */
$stats = [
    'characters' => count($names),
    'last_action' => $core->auth()['last_action_at'] ?? null
];

/* MAPS */
$classMap = [
    0 => 'Dark Wizard',
    1 => 'Soul Master',
    16 => 'Dark Knight',
    17 => 'Blade Knight',
    32 => 'Elf',
    33 => 'Muse Elf',
    48 => 'Magic Gladiator',
    64 => 'Dark Lord',
    80 => 'Summoner',
    96 => 'Rage Fighter'
];

$mapMap = [
    0 => 'Lorencia',
    1 => 'Dungeon',
    2 => 'Devias',
    3 => 'Noria',
    4 => 'Lost Tower',
    5 => 'Exile',
    6 => 'Arena'
];

$pkMap = [
    0 => ['Hero', 'pk-hero'],
    3 => ['Commoner', 'pk-normal'],
    4 => ['PK', 'pk-warning'],
    5 => ['Killer', 'pk-danger']
];

/* CHAR QUERY */
$characters = [];

if (!empty($names)) {

    $in = [];
    $params = [];

    foreach ($names as $i => $name) {
        $key = ":n$i";
        $in[] = $key;
        $params[$key] = $name;
    }

    $rows = $core->query("
        SELECT Name, Class, cLevel, MapNumber, PkLevel
        FROM Character
        WHERE Name IN (" . implode(',', $in) . ")
    ", $params);

    foreach ($rows as $c) {

        $pk = $pkMap[$c['PkLevel']] ?? ['Unknown', 'pk-dark'];

        $characters[] = [
            'name' => $c['Name'],
            'class' => $classMap[$c['Class']] ?? 'Unknown',
            'level' => (int)$c['cLevel'],
            'resets' => 0,
            'map' => $mapMap[$c['MapNumber']] ?? 'Unknown',
            'pk' => $pk[0],
            'pk_class' => $pk[1],
            'avatar' => '#',
            'progress' => min(100, ($c['cLevel'] / 400) * 100)
        ];
    }
}

/* RESPONSE */
echo json_encode([
    'status' => 'success',
    'data' => [
        'account' => [
            'username' => $user['memb___id'],
            'email' => $user['mail_addr'],
            'guid' => $user['memb_guid']
        ],
        'stats' => $stats,
        'characters' => $characters
    ]
], JSON_UNESCAPED_UNICODE);

exit;
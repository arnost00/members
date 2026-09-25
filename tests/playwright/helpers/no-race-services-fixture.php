<?php
// Dev/test-only data owned by the disabled race services suite.
chdir(__DIR__.'/../../../www');
define('__HIDE_TEST__', true);
require 'connect.inc.php';
db_Connect();
if (!preg_match('/dev|test/i', $g_dbname)) throw new RuntimeException('A development/test database is required');
$input = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
$id = (int)$input['id'];
if (!in_array($id, [24000, 24001, 24002, 24003, 24004], true)) throw new RuntimeException('Invalid fixture ID');
$name = 'PW no race services '.$id;
$race = $db_conn->query('SELECT * FROM '.TBL_RACE.' WHERE id='.$id)->fetch_assoc();
if ($race && $race['nazev'] !== $name) throw new RuntimeException('Fixture ID belongs to another race');
$createdName = $name.' created';
$member = $db_conn->query("SELECT id_users FROM ".TBL_ACCOUNT." WHERE login='tnov_5'")->fetch_assoc()['id_users'];
if (in_array($input['action'], ['reset', 'cleanup'], true)) {
    $owned = $db_conn->query("SELECT id FROM ".TBL_RACE." WHERE id=$id OR nazev='$createdName'");
    while ($row = $owned->fetch_assoc()) {
        $ownedId = (int)$row['id'];
        $db_conn->query('DELETE FROM '.TBL_ZAVXUS.' WHERE id_zavod='.$ownedId);
        $db_conn->query('DELETE FROM '.TBL_RACE.' WHERE id='.$ownedId);
    }
}
if ($input['action'] === 'reset') {
    $race = $db_conn->query('SELECT * FROM '.TBL_RACE.' ORDER BY id LIMIT 1')->fetch_assoc();
    $type = (int)($input['type'] ?? 0);
    $mode = (int)($input['mode'] ?? 1);
    $race = array_merge($race, ['id'=>$id, 'nazev'=>$name, 'ext_id'=>null,
        'datum'=>strtotime('+14 days midnight'), 'datum2'=>$type ? strtotime('+16 days midnight') : 0,
        'vicedenni'=>$type, 'etap'=>$type ? 3 : 1, 'kategorie'=>'H21;H35',
        'prihlasky'=>1, 'prihlasky1'=>time()+86400, 'prihlasky2'=>0, 'prihlasky3'=>0, 'prihlasky4'=>0, 'prihlasky5'=>0,
        'transport'=>$mode, 'ubytovani'=>$mode === 2 ? 2 : 1,
        'transport_do'=>time()+172800, 'ubytovani_do'=>time()+259200,
        'cancelled'=>0, 'prihlasenych'=>0, 'entry_start'=>null]);
    $fields = implode(',', array_map(fn($field)=>'`'.$field.'`', array_keys($race)));
    $values = implode(',', array_map(fn($value)=>$value===null ? 'NULL' : "'".$db_conn->real_escape_string((string)$value)."'", $race));
    $db_conn->query('INSERT INTO '.TBL_RACE.' ('.$fields.') VALUES ('.$values.')');
}
$race = $db_conn->query('SELECT * FROM '.TBL_RACE.' WHERE id='.$id)->fetch_assoc();
$created = $db_conn->query("SELECT * FROM ".TBL_RACE." WHERE nazev='$createdName'")->fetch_assoc();
$entry = $db_conn->query('SELECT * FROM '.TBL_ZAVXUS.' WHERE id_zavod='.$id.' AND id_user='.(int)$member)->fetch_assoc();
echo json_encode(['race'=>$race, 'created'=>$created, 'entry'=>$entry, 'member'=>(int)$member, 'createdName'=>$createdName]);

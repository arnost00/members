<?php
// Dev/test-only fixture. It owns only the two named deadline test races.
chdir(__DIR__.'/../../../www');
define('__HIDE_TEST__', true);
require 'connect.inc.php';
db_Connect();
if (!preg_match('/dev|test/i', $g_dbname)) throw new RuntimeException('A development/test database is required');
$input = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
$id = (int)$input['id'];
if (!in_array($id, [6200, 6201], true)) throw new RuntimeException('Invalid fixture ID');
$name = 'PW exact deadlines '.$id;
$race = $db_conn->query('SELECT * FROM '.TBL_RACE.' WHERE id='.$id)->fetch_assoc();
if ($race && $race['nazev'] !== $name) throw new RuntimeException('Fixture ID belongs to another race');
$member = $db_conn->query("SELECT id_users FROM ".TBL_ACCOUNT." WHERE login='tnov_5'")->fetch_assoc()['id_users'];
if ($input['action'] === 'created') {
    $created = $db_conn->query("SELECT * FROM ".TBL_RACE." WHERE nazev='".$name." created'")->fetch_assoc();
    if ($created) {
        $db_conn->query('DELETE FROM '.TBL_ZAVXUS.' WHERE id_zavod='.(int)$created['id']);
        $db_conn->query('DELETE FROM '.TBL_RACE.' WHERE id='.(int)$created['id']);
    }
    echo json_encode(['race'=>$created]); exit;
}
if ($input['action'] === 'reset') {
    $db_conn->query('DELETE FROM '.TBL_ZAVXUS.' WHERE id_zavod='.$id);
    $db_conn->query('DELETE FROM '.TBL_RACE.' WHERE id='.$id);
    $race = $db_conn->query('SELECT * FROM '.TBL_RACE.' ORDER BY id LIMIT 1')->fetch_assoc();
    $race = array_merge($race, ['id'=>$id, 'nazev'=>$name, 'ext_id'=>null, 'datum'=>strtotime('+14 days midnight'), 'datum2'=>0,
        'vicedenni'=>0, 'etap'=>1, 'kategorie'=>'H21;H35', 'prihlasky'=>1, 'prihlasky1'=>time()+3600,
        'prihlasky2'=>0, 'prihlasky3'=>0, 'prihlasky4'=>0, 'prihlasky5'=>0,
        'transport'=>3, 'ubytovani'=>1, 'transport_do'=>null, 'ubytovani_do'=>null,
        'cancelled'=>0, 'prihlasenych'=>0, 'entry_start'=>null]);
    $fields = implode(',', array_map(fn($x)=>'`'.$x.'`', array_keys($race)));
    $values = implode(',', array_map(fn($x)=>$x===null?'NULL':"'".$db_conn->real_escape_string((string)$x)."'", $race));
    $db_conn->query('INSERT INTO '.TBL_RACE.' ('.$fields.') VALUES ('.$values.')');
} elseif ($input['action'] === 'patch') {
    foreach ($input['fields'] as $field=>$value) {
        if (!in_array($field, ['prihlasky1','prihlasky2','prihlasky','transport_do','ubytovani_do','transport','ubytovani','ext_id','vedouci'],true)) throw new RuntimeException('Invalid fixture field');
        $sqlValue = $value===null ? 'NULL' : "'".$db_conn->real_escape_string((string)$value)."'";
        $db_conn->query('UPDATE '.TBL_RACE.' SET `'.$field.'`='.$sqlValue.' WHERE id='.$id);
    }
} elseif ($input['action'] === 'cleanup') {
    $db_conn->query("DELETE FROM ".TBL_RACE." WHERE nazev='".$name." created'");
    $db_conn->query('DELETE FROM '.TBL_ZAVXUS.' WHERE id_zavod='.$id);
    $db_conn->query('DELETE FROM '.TBL_RACE.' WHERE id='.$id);
}
$race = $db_conn->query('SELECT * FROM '.TBL_RACE.' WHERE id='.$id)->fetch_assoc();
$entry = $db_conn->query('SELECT * FROM '.TBL_ZAVXUS.' WHERE id_zavod='.$id.' AND id_user='.(int)$member)->fetch_assoc();
echo json_encode(['race'=>$race, 'entry'=>$entry, 'member'=>(int)$member]);

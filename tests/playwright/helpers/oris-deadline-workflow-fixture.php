<?php
// Dev/test-only fixture for an ORIS-linked entry after registration closes.
chdir(__DIR__.'/../../../www');
define('__HIDE_TEST__', true);
require 'connect.inc.php';
db_Connect();

if (!preg_match('/dev|test/i', $g_dbname)) throw new RuntimeException('A development/test database is required');

$input = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
$id = 24011;
$name = 'PW ORIS deadline workflow 24011';
$action = $input['action'] ?? 'read';

function orisDeadlineWorkflowUserId(string $login): int
{
    global $db_conn;
    $login = $db_conn->real_escape_string($login);
    $row = $db_conn->query("SELECT id_users FROM ".TBL_ACCOUNT." WHERE login='$login'")->fetch_assoc();
    if (!$row) throw new RuntimeException("Missing test account: $login");
    return (int)$row['id_users'];
}

function orisDeadlineWorkflowCleanup(int $id, string $name): void
{
    global $db_conn;
    $escapedName = $db_conn->real_escape_string($name);
    $ids = [$id];
    $result = $db_conn->query("SELECT id FROM ".TBL_RACE." WHERE nazev='$escapedName'");
    while ($row = $result->fetch_assoc()) $ids[] = (int)$row['id'];
    $idList = implode(',', array_values(array_unique($ids)));
    $db_conn->query('DELETE FROM '.TBL_ZAVXUS.' WHERE id_zavod IN ('.$idList.')');
    $db_conn->query("DELETE FROM ".TBL_RACE." WHERE id IN ($idList) OR nazev='$escapedName'");
}

$owned = $db_conn->query('SELECT nazev FROM '.TBL_RACE.' WHERE id='.$id)->fetch_assoc();
if ($owned && $owned['nazev'] !== $name) throw new RuntimeException('Reserved race ID 24011 belongs to another race');

if ($action === 'cleanup') {
    orisDeadlineWorkflowCleanup($id, $name);
} elseif ($action === 'reset') {
    orisDeadlineWorkflowCleanup($id, $name);
    $template = $db_conn->query('SELECT * FROM '.TBL_RACE.' ORDER BY id LIMIT 1')->fetch_assoc();
    if (!$template) throw new RuntimeException('Missing race template');
    $race = array_merge($template, [
        'id' => $id, 'nazev' => $name, 'ext_id' => null, 'datum' => strtotime('+14 days midnight'), 'datum2' => 0,
        'vicedenni' => 0, 'etap' => 1, 'kategorie' => 'H21;H35', 'prihlasky' => 1, 'prihlasky1' => time() + 3600,
        'prihlasky2' => 0, 'prihlasky3' => 0, 'prihlasky4' => 0, 'prihlasky5' => 0,
        'transport' => 3, 'ubytovani' => 1, 'transport_do' => null, 'ubytovani_do' => null,
        'cancelled' => 0, 'prihlasenych' => 0, 'entry_start' => null,
    ]);
    $fields = implode(',', array_map(fn($field) => '`'.$field.'`', array_keys($race)));
    $values = implode(',', array_map(fn($value) => $value === null ? 'NULL' : "'".$db_conn->real_escape_string((string)$value)."'", $race));
    if (!$db_conn->query('INSERT INTO '.TBL_RACE.' ('.$fields.') VALUES ('.$values.')')) {
        throw new RuntimeException('Could not create the ORIS deadline workflow race');
    }
} elseif ($action === 'patch') {
    foreach (($input['fields'] ?? []) as $field => $value) {
        if (!in_array($field, ['ext_id', 'prihlasky1', 'transport_do'], true)) {
            throw new RuntimeException('Invalid fixture field: '.$field);
        }
        $sqlValue = $value === null ? 'NULL' : "'".$db_conn->real_escape_string((string)$value)."'";
        if (!$db_conn->query('UPDATE '.TBL_RACE.' SET `'.$field.'`='.$sqlValue.' WHERE id='.$id)) {
            throw new RuntimeException('Could not patch '.$field);
        }
    }
} elseif ($action === 'patch_entry') {
    $member = orisDeadlineWorkflowUserId('tnov_5');
    if (!$db_conn->query("UPDATE ".TBL_ZAVXUS." SET sync_status='SYNCED' WHERE id_zavod=$id AND id_user=$member")) {
        throw new RuntimeException('Could not patch entry sync status');
    }
}

$member = orisDeadlineWorkflowUserId('tnov_5');
$race = $db_conn->query('SELECT * FROM '.TBL_RACE.' WHERE id='.$id)->fetch_assoc();
$entry = $db_conn->query('SELECT * FROM '.TBL_ZAVXUS.' WHERE id_zavod='.$id.' AND id_user='.$member)->fetch_assoc();
echo json_encode(['id' => $id, 'name' => $name, 'race' => $race, 'entry' => $entry, 'member' => $member]);

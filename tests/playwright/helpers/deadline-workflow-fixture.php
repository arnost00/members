<?php
// Dev/test-only fixture for the complete local race deadline workflow.
chdir(__DIR__.'/../../../www');
define('__HIDE_TEST__', true);
require 'connect.inc.php';
db_Connect();

if (!preg_match('/dev|test/i', $g_dbname)) throw new RuntimeException('A development/test database is required');

$input = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
$id = 24010;
$name = 'PW deadline workflow 24010';
$action = $input['action'] ?? 'read';

function workflowAccountId(string $login): int
{
    global $db_conn;
    $login = $db_conn->real_escape_string($login);
    $row = $db_conn->query("SELECT id_users FROM ".TBL_ACCOUNT." WHERE login='$login'")->fetch_assoc();
    if (!$row) throw new RuntimeException("Missing test account: $login");
    return (int)$row['id_users'];
}

function workflowCleanup(int $id, string $name): void
{
    global $db_conn;
    $escapedName = $db_conn->real_escape_string($name);
    $ids = [$id];
    $result = $db_conn->query("SELECT id FROM ".TBL_RACE." WHERE nazev='$escapedName'");
    while ($row = $result->fetch_assoc()) $ids[] = (int)$row['id'];
    $ids = array_values(array_unique($ids));
    $idList = implode(',', $ids);
    $db_conn->query('DELETE FROM '.TBL_ZAVXUS.' WHERE id_zavod IN ('.$idList.')');
    $db_conn->query("DELETE FROM ".TBL_RACE." WHERE id IN ($idList) OR nazev='$escapedName'");
}

$owned = $db_conn->query('SELECT nazev FROM '.TBL_RACE.' WHERE id='.$id)->fetch_assoc();
if ($owned && $owned['nazev'] !== $name) throw new RuntimeException('Reserved race ID 24010 belongs to another race');

if ($action === 'cleanup') {
    workflowCleanup($id, $name);
} elseif ($action === 'prepare') {
    workflowCleanup($id, $name);
} elseif ($action === 'claim_created') {
    $escapedName = $db_conn->real_escape_string($name);
    $created = $db_conn->query("SELECT id FROM ".TBL_RACE." WHERE nazev='$escapedName' ORDER BY id DESC LIMIT 1")->fetch_assoc();
    if (!$created) throw new RuntimeException('The registrar-created workflow race was not found');
    $createdId = (int)$created['id'];
    if ($createdId !== $id) {
        $db_conn->query('DELETE FROM '.TBL_ZAVXUS.' WHERE id_zavod='.$id);
        $db_conn->query('DELETE FROM '.TBL_RACE.' WHERE id='.$id);
        if (!$db_conn->query('UPDATE '.TBL_RACE.' SET id='.$id.' WHERE id='.$createdId)) {
            throw new RuntimeException('Could not assign the reserved workflow race ID');
        }
    }
} elseif ($action === 'patch') {
    $allowed = ['prihlasky', 'prihlasky1', 'prihlasky2', 'prihlasky3', 'prihlasky4', 'prihlasky5',
        'transport_do', 'ubytovani_do', 'transport', 'ubytovani', 'ext_id'];
    foreach (($input['fields'] ?? []) as $field => $value) {
        if (!in_array($field, $allowed, true)) throw new RuntimeException('Invalid fixture field: '.$field);
        $sqlValue = $value === null ? 'NULL' : "'".$db_conn->real_escape_string((string)$value)."'";
        if (!$db_conn->query('UPDATE '.TBL_RACE.' SET `'.$field.'`='.$sqlValue.' WHERE id='.$id)) {
            throw new RuntimeException('Could not patch '.$field);
        }
    }
} elseif ($action === 'patch_entry') {
    $member = workflowAccountId('tnov_5');
    $allowed = ['sync_status'];
    foreach (($input['fields'] ?? []) as $field => $value) {
        if (!in_array($field, $allowed, true)) throw new RuntimeException('Invalid entry fixture field: '.$field);
        $sqlValue = $value === null ? 'NULL' : "'".$db_conn->real_escape_string((string)$value)."'";
        if (!$db_conn->query('UPDATE '.TBL_ZAVXUS.' SET `'.$field.'`='.$sqlValue.' WHERE id_zavod='.$id.' AND id_user='.$member)) {
            throw new RuntimeException('Could not patch entry '.$field);
        }
    }
}

$member = workflowAccountId('tnov_5');
$secondMember = workflowAccountId('tnov_5_2');
$smallManager = workflowAccountId('tnov_4');
$secondMemberRow = $db_conn->query('SELECT chief_id FROM '.TBL_USER.' WHERE id='.$secondMember)->fetch_assoc();
if (!$secondMemberRow || (int)$secondMemberRow['chief_id'] !== $smallManager) {
    throw new RuntimeException('The second workflow member must be assigned to the seeded small manager');
}
$race = $db_conn->query('SELECT * FROM '.TBL_RACE.' WHERE id='.$id)->fetch_assoc();
$entry = $db_conn->query('SELECT * FROM '.TBL_ZAVXUS.' WHERE id_zavod='.$id.' AND id_user='.$member)->fetch_assoc();
echo json_encode(['id' => $id, 'name' => $name, 'race' => $race, 'entry' => $entry,
    'member' => $member, 'secondMember' => $secondMember]);

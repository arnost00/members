<?php
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST, EXTR_SKIP);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
require_once ("./common.inc.php");
require_once ("./common_race.inc.php");
require_once ("./url.inc.php");
require_once ("./ctable.inc.php");
require_once ("./connectors.php");
require_once ("./lib/oris_sync.inc.php");

if (!IsLoggedRegistrator())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

function mapRaceTypeToDbEnum($typeValue, array $raceTypes): string
{
	foreach ($raceTypes as $raceType) {
		if ((string)$typeValue === (string)$raceType['id'] || (string)$typeValue === (string)$raceType['enum']) {
			return (string)$raceType['enum'];
		}
	}

	return (string)$typeValue;
}

function createRaceUpdateMap(RaceDTO $raceInfo, array $raceRow, array $raceTypes): array
{
	$datum = (int)$raceInfo->datum;
	$vicedenni = ($raceInfo->vicedenni == 1) ? 1 : 0;
	$datum2 = $vicedenni ? (int)$raceInfo->datum2 : 0;
	$etap = $vicedenni ? max((int)$raceInfo->etap, 1) : 1;
	// adjust the time according manual entry ( 00:00:00 )
	$prihlasky1 = !empty($raceInfo->prihlasky) ? ((int)$raceInfo->prihlasky - (int)$raceInfo->prihlasky % 86400 - 86400) : 0;
	$prihlasky2 = !empty($raceInfo->prihlasky1) ? ((int)$raceInfo->prihlasky1 - (int)$raceInfo->prihlasky1 % 86400 - 86400) : 0;
	$prihlasky3 = !empty($raceInfo->prihlasky2) ? ((int)$raceInfo->prihlasky2 - (int)$raceInfo->prihlasky2 % 86400 - 86400) : 0;
	$prihlasky4 = (int)$raceRow['prihlasky4'];
	$prihlasky5 = (int)$raceRow['prihlasky5'];

	$prihlasky = 0;
	if ($prihlasky1 != 0) $prihlasky++;
	if ($prihlasky2 != 0) $prihlasky++;
	if ($prihlasky3 != 0) $prihlasky++;
	if ($prihlasky4 != 0) $prihlasky++;
	if ($prihlasky5 != 0) $prihlasky++;

	$modify_flag = ($prihlasky != $raceRow['prihlasky']
		|| $prihlasky1 != $raceRow['prihlasky1']
		|| $prihlasky2 != $raceRow['prihlasky2']
		|| $prihlasky3 != $raceRow['prihlasky3']
		|| $prihlasky4 != $raceRow['prihlasky4']
		|| $prihlasky5 != $raceRow['prihlasky5']) ? $GLOBALS['g_modify_flag'][0]['id'] : 0;

	if ($datum != $raceRow['datum'] || $datum2 != $raceRow['datum2'])
		$modify_flag += $GLOBALS['g_modify_flag'][2]['id'];

	$modify_flag = gen_modify_flag_v2b($raceRow['modify_flag'], $modify_flag);

	$odkaz = (string)$raceInfo->odkaz;
	if ($odkaz != '')
		$odkaz = cononize_url($odkaz, 1);
	$typ = mapRaceTypeToDbEnum($raceInfo->typ, $raceTypes);

	$update = [];
	$update['ext_id'] = (string)$raceInfo->ext_id;
	$update['datum'] = $datum;
	$update['datum2'] = $datum2;
	$update['nazev'] = (string)$raceInfo->nazev;
	$update['misto'] = (string)$raceInfo->misto;
	$update['typ0'] = 'Z';
	$update['typ'] = $typ;
	$update['zebricek'] = (int)$raceInfo->zebricek2;
	$update['ranking'] = (string)$raceInfo->ranking;
	$update['odkaz'] = $odkaz;
	$update['prihlasky'] = $prihlasky;
	$update['prihlasky1'] = $prihlasky1;
	$update['prihlasky2'] = $prihlasky2;
	$update['prihlasky3'] = $prihlasky3;
	$update['prihlasky4'] = $prihlasky4;
	$update['prihlasky5'] = $prihlasky5;
	$update['etap'] = $etap;
	$update['oddil'] = (string)$raceInfo->oddil;
	$update['kategorie'] = (string)$raceInfo->kategorie;
	$update['vicedenni'] = $vicedenni;
	$update['cancelled'] = ($raceInfo->cancelled === null) ? (int)$raceRow['cancelled'] : (int)$raceInfo->cancelled;
	$update['oris_entry_start'] = $raceInfo->oris_entry_start;
	$update['modify_flag'] = $modify_flag;

	return $update;
}

function getChangedUpdates(array $raceRow, array $update): array
{

	$changed = [];

	foreach ($update as $column => $value) {
		if ((string)$raceRow[$column] !== (string)$value) {
			$changed[$column] = $value;
		}
	}

	return $changed;
}

function getChangedLabels(array $changedUpdates): array
{
	$labels = [
		'ext_id' => 'ORIS ID',
		'datum' => 'datum',
		'datum2' => 'datum do',
		'nazev' => 'název',
		'misto' => 'místo',
		'typ0' => 'typ akce',
		'typ' => 'sport',
		'zebricek' => 'žebříček',
		'ranking' => 'ranking',
		'odkaz' => 'odkaz',
		'prihlasky' => 'počet termínů přihlášek',
		'prihlasky1' => '1. termín přihlášek',
		'prihlasky2' => '2. termín přihlášek',
		'prihlasky3' => '3. termín přihlášek',
		'prihlasky4' => '4. termín přihlášek',
		'prihlasky5' => '5. termín přihlášek',
		'etap' => 'počet etap',
		'oddil' => 'pořádající oddíl',
		'kategorie' => 'kategorie',
		'vicedenni' => 'vícedenní',
		'cancelled' => 'zrušení',
		'oris_entry_start' => 'zahájení přihlášek ORIS',
		'modify_flag' => 'příznak změny'
	];
	$changedLabels = [];

	foreach (array_keys($changedUpdates) as $column) {
		$changedLabels[] = $labels[$column] ?? $column;
	}

	return $changedLabels;
}

function fetchRaceRow(int $raceId)
{
	$sql = "SELECT id, ext_id, datum, datum2, nazev, misto, typ0, typ, zebricek, ranking, odkaz, prihlasky, prihlasky1, prihlasky2, prihlasky3, prihlasky4, prihlasky5, etap, oddil, kategorie, vicedenni, cancelled, oris_entry_start, modify_flag"
		." FROM ".TBL_RACE." WHERE id=? LIMIT 1";
	$stmt = db_prepare($sql);
	if ($stmt === false)
		return false;

	$rows = db_select($stmt, 'i', [$raceId]);
	return !empty($rows) ? $rows[0] : false;
}

function executeRaceUpdate(int $raceId, array $changedUpdates): bool
{
	if (empty($changedUpdates))
		return true;

	$assignments = [];
	$types = '';
	$params = [];

	foreach ($changedUpdates as $column => $value) {
		$assignments[] = $column.'=?';
		$types .= is_int($value) ? 'i' : 's';
		$params[] = $value;
	}

	$types .= 'i';
	$params[] = $raceId;

	$sql = "UPDATE ".TBL_RACE." SET ".implode(', ', $assignments)." WHERE id=?";
	$stmt = db_prepare($sql);
	if ($stmt === false)
		return false;

	$result = db_exec($stmt, $types, $params);
	return ($result !== false);
}

$connector = ConnectorFactory::create();

if ($connector === null )
{
	echo('Chyba v nastavení, nenalezen žádny connector, kontaktuje administrátora.<br>');
	exit;
}

db_Connect();

$race_ids = [];
if (isset($_POST['race_ids']) && is_array($_POST['race_ids'])) {
	foreach ($_POST['race_ids'] as $race_id) {
		$race_id = (int)$race_id;
		if ($race_id > 0) {
			$race_ids[$race_id] = $race_id;
		}
	}
}

require_once ("./header.inc.php");

DrawPageTitle('Aktualizace závodů ze systému '.$connector->getSystemName());

?>
<TABLE width="100%" cellpadding="0" cellspacing="0" border="0">
<TR>
<TD width="2%"></TD>
<TD width="90%" ALIGN=left>
<CENTER>
<?php

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'ORIS ID',ALIGN_CENTER,0);
$data_tbl->set_header_col($col++,'Datum',ALIGN_CENTER,0);
$data_tbl->set_header_col($col++,'Název',ALIGN_LEFT);
$data_tbl->set_header_col_with_help($col++,'Poř.',ALIGN_CENTER,"Pořadatel");
$data_tbl->set_header_col($col++,'Stav',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Poznámka',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$updated_count = 0;
$unchanged_count = 0;
$error_count = 0;

if (empty($race_ids)) {
	$error_count++;
	echo $data_tbl->get_new_row_arr([ '-', '-', '-', '-', 'Chyba', 'Nebyl vybrán žádný závod.', ''])."\n";
}
else {
	foreach ($race_ids as $race_id) {
		$raceRow = fetchRaceRow($race_id);

		if ($raceRow === false) {
			$error_count++;
			echo $data_tbl->get_new_row_arr([ '-', '-', '-', '-', 'Chyba', 'Závod nebyl v databázi nalezen.', ''])."\n";
			continue;
		}

		if (empty($raceRow['ext_id'])) {
			$error_count++;
			echo $data_tbl->get_new_row_arr([ '-', Date2String($raceRow['datum']), $raceRow['nazev'], $raceRow['oddil'], 'Chyba', 'Závod nemá uložené ORIS ID.'])."\n";
			continue;
		}

		$raceInfo = $connector->getRaceInfo($raceRow['ext_id']);
		if ($raceInfo === null) {
			$error_count++;
			echo $data_tbl->get_new_row_arr ([$raceRow['ext_id'], Date2String($raceRow['datum']), $raceRow['nazev'], $raceRow['oddil'], 'Chyba', 'Nepodařilo se načíst data z ORISu.'])."\n";
			continue;
		}

		$update = createRaceUpdateMap($raceInfo, $raceRow, $g_racetype);
		$changedUpdates = getChangedUpdates($raceRow, $update);

		if (empty($changedUpdates)) {
			$unchanged_count++;
			echo $data_tbl->get_new_row_arr([ $raceRow['ext_id'], Date2String($raceRow['datum']), $raceRow['nazev'], $raceRow['oddil'], 'Beze změny', 'Data už odpovídají ORISu.',
			  '<A HREF="javascript:open_win(\'./race_edit.php?id='.$raceRow['id'].'&refresh_parent=0\',\'\')">Edit</A>'])."\n";
			continue;
		}

		if (!executeRaceUpdate((int)$raceRow['id'], $changedUpdates)) {
			$error_count++;
			echo $data_tbl->get_new_row_arr([$raceRow['ext_id'], Date2String($raceRow['datum']), $raceRow['nazev'], $raceRow['oddil'], 'Chyba', 'Nepodařilo se uložit změny do databáze.',
			  '<A HREF="javascript:open_win(\'./race_edit.php?id='.$raceRow['id'].'&refresh_parent=0\',\'\')">Edit</A>'])."\n";

			continue;
		}

		$syncNote = '';
		if (array_key_exists('oris_entry_start', $changedUpdates)) {
			$newEntryStart = $changedUpdates['oris_entry_start'];
			$entryStartOpen = empty($newEntryStart) || strtotime($newEntryStart) <= time();
			if ($entryStartOpen) {
				global $g_oris_club_key;
				if (!empty($g_oris_club_key)) {
					$service = new OrisIntegrationService($g_oris_club_key);
					$pendingQuery = query_db("SELECT * FROM `" . TBL_ZAVXUS . "` WHERE `id_zavod` = " . (int)$race_id . " AND `sync_status` = 'PENDING_CREATE'");
					$syncedCount = 0;
					$failedCount = 0;
					while ($pendingRow = mysqli_fetch_assoc($pendingQuery)) {
						$res = processEntry($pendingRow, 'create', $service);
						if ($res === true) {
							$syncedCount++;
						} elseif ($res !== null) {
							$failedCount++;
						}
					}
					if ($syncedCount > 0 || $failedCount > 0) {
						$syncNote = '; sync přihlášek: ' . $syncedCount . ' OK' . ($failedCount > 0 ? ', ' . $failedCount . ' chyb' : '');
					}
				}
			}
		}

		$updated_count++;
		echo $data_tbl->get_new_row_arr([ $raceRow['ext_id'], Date2String($raceRow['datum']), $raceRow['nazev'], $raceRow['oddil'], 'Aktualizováno', implode(', ', getChangedLabels($changedUpdates)) . $syncNote,
			  '<A HREF="javascript:open_win(\'./race_edit.php?id='.$raceRow['id'].'&refresh_parent=0\',\'\')">Edit</A>'])."\n";
	}
}

echo $data_tbl->get_footer()."\n";

?>
<BR>
Aktualizováno: <?php echo $updated_count; ?><BR>
Beze změny: <?php echo $unchanged_count; ?><BR>
Chyba: <?php echo $error_count; ?><BR>
<BR><hr><BR>
<A HREF="./race_imports_update.php">Zpět na výběr závodů</A><BR>
<BR><hr><BR>
</CENTER>
</TD>
<TD width="2%"></TD>
</TR>
<TR><TD COLSPAN=4 ALIGN=CENTER>
<!-- Footer Begin -->
<?require_once ("footer.inc.php");?>
<!-- Footer End -->
</TD></TR>
</TABLE>

<?
HTML_Footer();
?>

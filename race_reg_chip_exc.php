<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST, EXTR_SKIP);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
require_once ("./lib/oris_sync.inc.php");
require_once ("./common.inc.php");
require_once ("./common_race.inc.php");

if (!IsLoggedRegistrator())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

$id_zav = (IsSet($id_zav) && is_numeric($id_zav)) ? (int)$id_zav : 0;

db_Connect();

@$vysledek_z=query_db("SELECT ext_id FROM ".TBL_RACE." WHERE id=$id_zav");
$zaznam_z = mysqli_fetch_array($vysledek_z);
$has_ext_id = !empty($zaznam_z['ext_id']);
$sync_queue = [];

$query = 'SELECT z.id as z_id, z.id_user, z.sync_status, z.si_chip as z_chip, u.si_chip FROM '.TBL_ZAVXUS.' as z, '.TBL_USER.' as u WHERE z.id_user = u.id AND z.id_zavod='.$id_zav.' AND u.hidden = 0';

@$vysledek=query_db($query);

if (mysqli_num_rows($vysledek) > 0)
{
	while ($zaznam=mysqli_fetch_array($vysledek))
	{
		$user=$zaznam['id_user'];
		if (IsSet($chip[$user]))
		{
			$si_chip = (int)$chip[$user];
			$old_chip = (int)($zaznam['z_chip'] ? $zaznam['z_chip'] : $zaznam['si_chip']);
			if ($si_chip != $old_chip)
			{
				$sync_status_update = "";
				if ($has_ext_id && $zaznam['sync_status'] !== 'PENDING_CREATE') {
					$sync_status_update = ", `sync_status`='PENDING_UPDATE'";
				}
				$result=query_db('UPDATE '.TBL_ZAVXUS.' SET `si_chip`= '.$si_chip.$sync_status_update.' WHERE `id_zavod` = '.$id_zav.' AND `id_user` = '.$user)
					or die("Chyba při provádění dotazu do databáze.");
				if ($result == FALSE)
					die ("Nepodařilo se změnit přihlášku člena.");
				
				if ($has_ext_id) {
					$action = ($zaznam['sync_status'] === 'PENDING_CREATE') ? 'create' : 'update';
					$sync_queue[] = ['id' => $zaznam['z_id'], 'action' => $action];
				}
			}
		}
	}
}

$sync_errors = [];
if ($has_ext_id && count($sync_queue) > 0) {
	global $g_oris_club_key;
	if (!empty($g_oris_club_key)) {
		$service = new OrisIntegrationService($g_oris_club_key);
		foreach ($sync_queue as $sq) {
			$rowQuery = query_db("SELECT * FROM `" . TBL_ZAVXUS . "` WHERE `id` = " . (int)$sq['id']);
			if ($rowQuery && $syncRow = mysqli_fetch_assoc($rowQuery)) {
				$syncRes = processEntry($syncRow, $sq['action'], $service);
				if ($syncRes !== true && $syncRes !== 'queued') {
					$sync_errors[] = "Záznam " . $sq['id'] . ": " . getOrisSyncError($sq['id']);
				}
			}
		}
	}
}
?>
<SCRIPT LANGUAGE="JavaScript">
<!--
<?php
if (!empty($sync_errors)) {
	echo "alert('Chyba při synchronizaci s ORIS:\\n" . addslashes(implode("\\n", $sync_errors)) . "');\n";
}
?>
	window.opener.focus();
	window.close();
//-->
</SCRIPT>

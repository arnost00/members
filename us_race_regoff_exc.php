<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
$id_zav = $_REQUEST['id_zav'] ?? null;
$id_us = $_REQUEST['id_us'] ?? null;

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
require_once ("./lib/oris_sync.inc.php");

if (!IsLogged())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

db_Connect();

$id_zav = (IsSet($id_zav) && is_numeric($id_zav)) ? (int)$id_zav : 0;
$id_us = (IsSet($id_us) && is_numeric($id_us)) ? (int)$id_us : 0;

@$vysledek2=query_db("SELECT * FROM ".TBL_USER." where id=$id_us");
$entry_lock = false;
if ($zaznam2=mysqli_fetch_array($vysledek2))
{
	$entry_lock = ($zaznam2['entry_locked'] != 0);
}

if (!$entry_lock)
{
	$vysledek_z=query_db("SELECT ext_id FROM ".TBL_RACE." WHERE id='$id_zav'");
	$zaznam_z = mysqli_fetch_array($vysledek_z);
	$has_ext_id = !empty($zaznam_z['ext_id']);

	$vysledek_zx=query_db("SELECT id, sync_status FROM ".TBL_ZAVXUS." WHERE id_zavod='$id_zav' AND id_user='$id_us'");
	$zaznam_zx = mysqli_fetch_array($vysledek_zx);

	$sync_error_msg = null;
	$sync_warn_msg = null;
	if ($zaznam_zx) {
		$zx_id = $zaznam_zx['id'];
		$sync_status = $zaznam_zx['sync_status'];

		if ($has_ext_id && $sync_status !== 'PENDING_CREATE') {
			$vysledek = query_db("UPDATE ".TBL_ZAVXUS." SET sync_status='PENDING_DELETE' WHERE id = '$zx_id'");
			if ($vysledek !== false) {
				global $g_oris_club_key;
				if (!empty($g_oris_club_key)) {
					$service = new OrisIntegrationService($g_oris_club_key);
					$rowQuery = query_db("SELECT * FROM `" . TBL_ZAVXUS . "` WHERE `id` = '$zx_id'");
					if ($rowQuery && $syncRow = mysqli_fetch_assoc($rowQuery)) {
						$syncRes = processEntry($syncRow, 'delete', $service);
						if ($syncRes === true || $syncRes === 'queued' || $syncRes === 'not_open') {
							query_db("UPDATE ".TBL_RACE." SET prihlasenych = GREATEST(0, prihlasenych - 1) WHERE id = '$id_zav'");
							if ($syncRes === 'queued') {
								$sync_warn_msg = 'Přihláška odstraněna. Zrušení v ORIS se nezdařilo (síťová chyba) — zkuste to prosím znovu.';
							} elseif ($syncRes === 'not_open') {
								$sync_warn_msg = 'Přihláška odstraněna. Zrušení v ORIS proběhne, až se otevře přihlašovací termín.';
							}
						} else {
							$sync_error_msg = getOrisSyncError($zx_id);
						}
					}
				}
			}
		} else {
			@$vysledek=query_db("DELETE FROM ".TBL_ZAVXUS." WHERE id = '$zx_id'");
			if ($vysledek !== false && mysqli_affected_rows($db_conn) > 0) {
				query_db("UPDATE ".TBL_RACE." SET prihlasenych = GREATEST(0, prihlasenych - 1) WHERE id = '$id_zav'");
			}
		}
	}
}
?>

<SCRIPT LANGUAGE="JavaScript">
<!--
<?php if (!empty($sync_error_msg)) { ?>
	alert("Chyba při synchronizaci s ORIS:\n<?php echo addslashes($sync_error_msg); ?>");
<?php } elseif (!empty($sync_warn_msg)) { ?>
	alert("<?php echo addslashes($sync_warn_msg); ?>");
<?php } ?>
	window.opener.location.reload();

	window.opener.focus();
	window.close();
//-->
</SCRIPT>

<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
$id_zav = $_REQUEST['id_zav'] ?? null;
$id_us = $_REQUEST['id_us'] ?? null;
$id_z = $_REQUEST['id_z'] ?? null;
$kat = $_REQUEST['kat'] ?? null;
$pozn = $_REQUEST['pozn'] ?? null;
$pozn2 = $_REQUEST['pozn2'] ?? null;
$sedadel = $_REQUEST['sedadel'] ?? null;
$transport = $_REQUEST['transport'] ?? null;
$ubytovani = $_REQUEST['ubytovani'] ?? null;
$novy = $_REQUEST['novy'] ?? null;

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
require_once ("./common.inc.php");
require_once ("./common_race.inc.php");
require_once ("./lib/oris_sync.inc.php");

if (!IsLogged())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
$id_zav = (IsSet($id_zav) && is_numeric($id_zav)) ? (int)$id_zav: 0;
$id_us = (IsSet($id_us) && is_numeric($id_us)) ? (int)$id_us: 0;
$id_z = (IsSet($id_z) && is_numeric($id_z)) ? (int)$id_z: 0;
$kat = (IsSet($kat)) ? $kat : '';

if ($kat != '')
{
	db_Connect();

	@$vysledek2=query_db("SELECT * FROM ".TBL_USER." where id=$id_us");
	$entry_lock = false;
	if ($zaznam2=mysqli_fetch_array($vysledek2))
	{
		$entry_lock = ($zaznam2['entry_locked'] != 0);
	}

	if (!$entry_lock)
	{
		$kat=correct_sql_string($kat);
		$pozn=correct_sql_string($pozn);
		$pozn2=correct_sql_string($pozn2);

		@$vysledek_z=query_db("SELECT datum, vicedenni, prihlasky, prihlasky1, prihlasky2, prihlasky3, prihlasky4, prihlasky5, transport, ext_id FROM ".TBL_RACE." WHERE id=$id_zav");
		$zaznam_z = mysqli_fetch_array($vysledek_z);

		$termin = raceterms::GetCurr4RegTerm($zaznam_z);

		if ($termin != 0) // not process if invalid termin number
		{
			if ( $zaznam_z["transport"] == 3 ) {
				// shared transport
				if ( !isset($sedadel) || $sedadel=='' || $sedadel=='null') {
					// no seats no trasport
					$sedadel = 'null';
					$transport = 0;
				} else {
					// if seats set, transport automatically
					$sedadel = intval($sedadel);
					$transport = 1;
				}	
			} else {
				$transport = !isset($transport)? 0: 1;
				$sedadel = 'null';
			}
			$ubytovani = !isset($ubytovani)? 'null': 1;
			$novy  = !isset($novy)? 0: (int)$novy;

			$has_ext_id = !empty($zaznam_z['ext_id']);
			$inserted_or_updated_id = 0;
			$sync_action = '';
			$is_new_insert = false;
			$previous_state = null;

			if ($novy)
			{
				$vysledek=query_db("SELECT * FROM ".TBL_ZAVXUS." WHERE id_zavod='$id_zav' and id_user='$id_us'");
				if ($vysledek != FALSE && ($zaznam = mysqli_fetch_array($vysledek)) != FALSE )
				{	// latest new == update
					$previous_state = $zaznam;
					$sync_status_update = ($has_ext_id && $zaznam['sync_status'] !== 'PENDING_CREATE') ? ", sync_status='PENDING_UPDATE'" : "";
					$sync_action = ($has_ext_id && $zaznam['sync_status'] === 'PENDING_CREATE') ? 'create' : 'update';
					query_db("UPDATE ".TBL_ZAVXUS." SET kat='$kat', pozn='$pozn', pozn_in='$pozn2', termin='$termin', transport=$transport, sedadel=$sedadel, ubytovani=$ubytovani".$sync_status_update." WHERE id='".$zaznam['id']."'");
					$inserted_or_updated_id = $zaznam['id'];
				}
				else
				{	// really new
					$is_new_insert = true;
					$sync_status = $has_ext_id ? 'PENDING_CREATE' : 'LOCAL_ONLY';
					$sync_action = $has_ext_id ? 'create' : '';
					$vysledek = query_db("INSERT INTO ".TBL_ZAVXUS." (id_user, id_zavod, kat, pozn, pozn_in, termin, transport, sedadel, ubytovani, sync_status) VALUES ('$id_us','$id_zav','$kat','$pozn','$pozn2','$termin',$transport, $sedadel, $ubytovani, '$sync_status')");	
					if ($vysledek !== false && mysqli_affected_rows($db_conn) > 0) {
						$inserted_or_updated_id = mysqli_insert_id($db_conn);
						query_db("UPDATE ".TBL_RACE." SET prihlasenych = prihlasenych + 1 WHERE id = '$id_zav'");
					}
				}
			}
			else
			{	// update
				$vysledek=query_db("SELECT * FROM ".TBL_ZAVXUS." WHERE id='".$id_z."'");
				if ($vysledek != FALSE && ($zaznam = mysqli_fetch_array($vysledek)) != FALSE )
				{
					$previous_state = $zaznam;
					$sync_status_update = ($has_ext_id && $zaznam['sync_status'] !== 'PENDING_CREATE') ? ", sync_status='PENDING_UPDATE'" : "";
					$sync_action = ($has_ext_id && $zaznam['sync_status'] === 'PENDING_CREATE') ? 'create' : 'update';
					query_db("UPDATE ".TBL_ZAVXUS." SET kat='$kat', pozn='$pozn', pozn_in='$pozn2', transport=$transport, sedadel=$sedadel, ubytovani=$ubytovani".$sync_status_update." WHERE id='".$id_z."'");
					$inserted_or_updated_id = $id_z;
				}
			}

			$sync_error_msg = null;
			$sync_warn_msg = null;
			if ($has_ext_id && $inserted_or_updated_id > 0 && $sync_action !== '') {
				global $g_oris_club_key;
				if (!empty($g_oris_club_key)) {
					$service = new OrisIntegrationService($g_oris_club_key);
					$rowQuery = query_db("SELECT * FROM `" . TBL_ZAVXUS . "` WHERE `id` = " . (int)$inserted_or_updated_id);
					if ($rowQuery && $syncRow = mysqli_fetch_assoc($rowQuery)) {
						$syncRes = processEntry($syncRow, $sync_action, $service);
						if ($syncRes === 'not_open') {
							$sync_warn_msg = 'Přihláška uložena. Přihlašování na ORIS ještě nezačalo — odešlete ji znovu, až se termín přihlášek otevře.';
						} elseif ($syncRes === 'queued') {
							$sync_warn_msg = 'Přihláška uložena. Synchronizace s ORIS se nezdařila (síťová chyba) — zkuste to prosím znovu.';
						} elseif ($syncRes !== true && $syncRes !== null) {
							$sync_error_msg = getOrisSyncError($inserted_or_updated_id);
							// Rollback changes
							if ($is_new_insert) {
								query_db("DELETE FROM ".TBL_ZAVXUS." WHERE id = '$inserted_or_updated_id'");
								query_db("UPDATE ".TBL_RACE." SET prihlasenych = prihlasenych - 1 WHERE id = '$id_zav'");
							} else if ($previous_state) {
								$prev_kat = correct_sql_string($previous_state['kat']);
								$prev_pozn = correct_sql_string($previous_state['pozn']);
								$prev_pozn_in = correct_sql_string($previous_state['pozn_in']);
								$prev_termin = (int)$previous_state['termin'];
								$prev_transport = (int)$previous_state['transport'];
								$prev_sedadel = (!isset($previous_state['sedadel']) || $previous_state['sedadel'] === null) ? 'null' : (int)$previous_state['sedadel'];
								$prev_ubytovani = (!isset($previous_state['ubytovani']) || $previous_state['ubytovani'] === null) ? 'null' : (int)$previous_state['ubytovani'];
								$prev_sync_status = correct_sql_string($previous_state['sync_status']);

								query_db("UPDATE ".TBL_ZAVXUS." SET kat='$prev_kat', pozn='$prev_pozn', pozn_in='$prev_pozn_in', termin='$prev_termin', transport=$prev_transport, sedadel=$prev_sedadel, ubytovani=$prev_ubytovani, sync_status='$prev_sync_status' WHERE id='$inserted_or_updated_id'");
							}
						}
					}
				}
			}
		}
	}
}
?>
<?php
$return_url = $g_baseadr.'us_race_regon.php?id_zav='.$id_zav.'&id_us='.$id_us;
if (!empty($sync_error_msg)) { ?>
	<div style="color: red; font-weight: bold; padding: 20px; border: 1px solid red; margin: 20px; font-family: sans-serif;">
		Chyba při synchronizaci s ORIS:<br><br>
		<?php echo htmlspecialchars($sync_error_msg); ?>
		<br><br>
		<a href="<?php echo htmlspecialchars($return_url); ?>">Zpět na přehled</a>
	</div>
	<SCRIPT LANGUAGE="JavaScript">window.opener.location.reload();</SCRIPT>
<?php } elseif (!empty($sync_warn_msg)) { ?>
	<div style="color: #7a4f00; background: #fff3cd; padding: 20px; border: 1px solid #ffc107; margin: 20px; font-family: sans-serif;">
		<?php echo htmlspecialchars($sync_warn_msg); ?>
		<br><br>
		<a href="<?php echo htmlspecialchars($return_url); ?>">Zpět na přehled</a>
	</div>
	<SCRIPT LANGUAGE="JavaScript">window.opener.location.reload();</SCRIPT>
<?php } else {
	header("Location: $return_url");
	exit;
} ?>

<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
$gr_id = $_REQUEST['gr_id'] ?? null;
$id = $_REQUEST['id'] ?? null;
$show_ed = $_REQUEST['show_ed'] ?? null;
$user_id = $_REQUEST['user_id'] ?? null;
$kateg = $_REQUEST['kateg'] ?? null;
$pozn = $_REQUEST['pozn'] ?? null;
$pozn2 = $_REQUEST['pozn2'] ?? null;
$new_termin = $_REQUEST['new_termin'] ?? null;
$transport = $_REQUEST['transport'] ?? null;
$sedadel = $_REQUEST['sedadel'] ?? null;
$ubytovani = $_REQUEST['ubytovani'] ?? null;

//TBD: podpora entry_locked

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");

if (!IsLoggedRegistrator() && !IsLoggedManager()&& !IsLoggedSmallManager())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
require_once ("./common.inc.php");
require_once ("./common_race.inc.php");
require_once ("./lib/oris_sync.inc.php");

$gr_id = (IsSet($gr_id) && is_numeric($gr_id)) ? (int)$gr_id : 0;
$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;
$show_ed = (IsSet($show_ed) && is_numeric($show_ed)) ? (int)$show_ed : 0;	// only for resend
$user_id = (IsSet($user_id) && is_numeric($user_id)) ? (int)$user_id : 0;
$kateg = (IsSet($kateg)) ? $kateg : '';
$pozn = (IsSet($pozn)) ? $pozn : '';
$pozn2 =(IsSet($pozn2)) ? $pozn2 : '';
$new_termin = (IsSet($new_termin) && is_numeric($new_termin)) ? (int)$new_termin : 0;
$transport = (IsSet($transport)) ? 1 : 0;
$sedadel = (IsSet($sedadel)) ? $sedadel : 'null';
$ubytovani = (IsSet($ubytovani)) ? 1 : 0;

db_Connect();

$vysledek=query_db('SELECT * FROM '.TBL_ZAVXUS.' WHERE id_zavod='.$id.' and id_user='.$user_id);
if ($vysledek != FALSE && mysqli_num_rows ($vysledek) == 1)
	$zaznam=mysqli_fetch_array($vysledek);
else
	$zaznam=false;

@$vysledek_z=query_db('SELECT * FROM '.TBL_RACE.' WHERE id='.$id);
$zaznam_z = mysqli_fetch_array($vysledek_z);

$termin = raceterms::GetCurr4RegTerm($zaznam_z);

$is_registrator_on = IsCalledByRegistrator($gr_id);
$is_termin_show_on = $is_registrator_on && ($zaznam_z['prihlasky'] > 1);
$is_spol_dopr_on = ($zaznam_z["transport"]==1);
$is_sdil_dopr_on = ($zaznam_z["transport"]==3);
$is_spol_ubyt_on = ($zaznam_z["ubytovani"]==1);

if($is_termin_show_on && $new_termin != 0)
	$termin = $new_termin;

if ($zaznam_z['prihlasky'] <= 1 && $is_registrator_on && $termin == 0)
	$termin = 1;

if ( $is_spol_dopr_on) {
	// common transport, no seats
	$sedadel = 'null';
} else if ( $is_sdil_dopr_on) {
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
	// no transport
	$transport = 0;
	$sedadel = 'null';
}

$ubytovani = ($is_spol_ubyt_on) ? $ubytovani : 0;

if($termin != 0)
{
	$sync_action = '';
	$inserted_or_updated_id = 0;
	$has_ext_id = !empty($zaznam_z['ext_id']);
	$is_new_insert = false;
	$previous_state = null;

	if ($zaznam != false)
	{
		$previous_state = $zaznam;
		if ($kateg == '')
		{	// del
			$is_pending_create = ($zaznam['sync_status'] === 'PENDING_CREATE');
			
			if ($has_ext_id && !$is_pending_create) {
				$result=query_db("UPDATE ".TBL_ZAVXUS." SET sync_status='PENDING_DELETE' WHERE id_zavod = '$id' AND id_user = '$user_id'")
					or die("Chyba při provádění dotazu do databáze.");
				$sync_action = 'delete';
				$inserted_or_updated_id = $zaznam['id'];
			} else {
				$result=query_db("DELETE FROM ".TBL_ZAVXUS." WHERE id_zavod = '$id' AND id_user = '$user_id'")
					or die("Chyba při provádění dotazu do databáze.");
				if ($result !== false && mysqli_affected_rows($db_conn) > 0) {
					query_db("UPDATE ".TBL_RACE." SET prihlasenych = GREATEST(0, prihlasenych - 1) WHERE id = '$id'");
				}
			}
			if ($result == FALSE)
				die ("Nepodařilo se změnit přihlášku člena.");
		}
		else
		{	// update
//			echo "UPD";
			$kateg=correct_sql_string($kateg);
			$pozn=correct_sql_string($pozn);
			$pozn2=correct_sql_string($pozn2);
			$termin=correct_sql_string($termin);
			
			$sync_status_update = "";
			if (!empty($zaznam_z['ext_id']) && $zaznam['sync_status'] !== 'PENDING_CREATE') {
				$sync_status_update = ", sync_status='PENDING_UPDATE'";
			}
			
			$result=query_db("UPDATE ".TBL_ZAVXUS." SET kat='$kateg', pozn='$pozn', pozn_in='$pozn2', termin='$termin', transport = '$transport', sedadel = ".$sedadel.", ubytovani = '$ubytovani'".$sync_status_update." WHERE id_zavod = '$id' AND id_user = '$user_id'")
				or die("Chyba při provádění dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodařilo se změnit přihlášku člena.");
			
			$sync_action = ($has_ext_id && $zaznam['sync_status'] === 'PENDING_CREATE') ? 'create' : 'update';
			$inserted_or_updated_id = $zaznam['id'];
		}
	}
	else
	{
		if ($kateg != '')
		{	// new
//			echo "NEW";
			$is_new_insert = true;
			$kateg=correct_sql_string($kateg);
			$pozn=correct_sql_string($pozn);
			$pozn2=correct_sql_string($pozn2);
			$termin=correct_sql_string($termin);
			
			$sync_status = !empty($zaznam_z['ext_id']) ? 'PENDING_CREATE' : 'LOCAL_ONLY';

			$result=query_db("INSERT INTO ".TBL_ZAVXUS." (id_user, id_zavod, kat, pozn, pozn_in,termin,transport,sedadel,ubytovani,sync_status) VALUES ('$user_id','$id','$kateg', '$pozn', '$pozn2','$termin','$transport',".$sedadel.",'$ubytovani','$sync_status')")
				or die("Chyba při provádění dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodařilo se změnit přihlášku člena.");
			if ($result !== false && mysqli_affected_rows($db_conn) > 0) {
				$inserted_or_updated_id = mysqli_insert_id($db_conn);
				query_db("UPDATE ".TBL_RACE." SET prihlasenych = prihlasenych + 1 WHERE id = '$id'");
				if ($has_ext_id) $sync_action = 'create';
			}
		}
	}

	if ($has_ext_id && $inserted_or_updated_id > 0 && $sync_action !== '') {
		global $g_oris_club_key;
		if (!empty($g_oris_club_key)) {
			$service = new OrisIntegrationService($g_oris_club_key);
			$rowQuery = query_db("SELECT * FROM `" . TBL_ZAVXUS . "` WHERE `id` = " . (int)$inserted_or_updated_id);
			if ($rowQuery && $syncRow = mysqli_fetch_assoc($rowQuery)) {
				if ($sync_action === 'delete') {
					$syncRes = processEntry($syncRow, 'delete', $service);
					if ($syncRes === true || $syncRes === 'queued' || $syncRes === 'not_open') {
						query_db("UPDATE ".TBL_RACE." SET prihlasenych = GREATEST(0, prihlasenych - 1) WHERE id = '$id'");
						if ($syncRes === 'queued') {
							$sync_warn = 'Přihláška odstraněna. Zrušení v ORIS se nezdařilo (síťová chyba) — zkuste to prosím znovu.';
						} elseif ($syncRes === 'not_open') {
							$sync_warn = 'Přihláška odstraněna. Zrušení v ORIS proběhne, až se otevře přihlašovací termín.';
						}
					} else {
						$sync_error = getOrisSyncError($inserted_or_updated_id);
						// Rollback delete to original status if delete failed
						if ($previous_state) {
							$prev_sync_status = correct_sql_string($previous_state['sync_status']);
							query_db("UPDATE ".TBL_ZAVXUS." SET sync_status='$prev_sync_status' WHERE id='$inserted_or_updated_id'");
						}
					}
				} else {
					$syncRes = processEntry($syncRow, $sync_action, $service);
					if ($syncRes === 'not_open') {
						$sync_warn = 'Přihláška uložena. Přihlašování na ORIS ještě nezačalo — odešlete ji znovu, až se termín přihlášek otevře.';
					} elseif ($syncRes === 'queued') {
						$sync_warn = 'Přihláška uložena. Synchronizace s ORIS se nezdařila (síťová chyba) — zkuste to prosím znovu.';
					} elseif ($syncRes !== true && $syncRes !== null) {
						$sync_error = getOrisSyncError($inserted_or_updated_id);
						// Rollback changes
						if ($is_new_insert) {
							query_db("DELETE FROM ".TBL_ZAVXUS." WHERE id = '$inserted_or_updated_id'");
							query_db("UPDATE ".TBL_RACE." SET prihlasenych = GREATEST(0, prihlasenych - 1) WHERE id = '$id'");
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

if (!empty($sync_error)) {
	$return_url = ($gr_id != 0) ? $g_baseadr.'race_regs_1.php?gr_id='.$gr_id.'&id='.$id.'&show_ed='.$show_ed : $g_baseadr.'race_regs_1.php?id='.$id.'&show_ed='.$show_ed;
?>
	<div style="color: red; font-weight: bold; padding: 20px; border: 1px solid red; margin: 20px; font-family: sans-serif;">
		Chyba při synchronizaci s ORIS:<br><br>
		<?php echo htmlspecialchars($sync_error); ?>
		<br><br>
		<a href="<?php echo htmlspecialchars($return_url); ?>">Zpět na přehled</a>
	</div>
<?php
	exit;
}

if (!empty($sync_warn)) {
	$return_url = ($gr_id != 0) ? $g_baseadr.'race_regs_1.php?gr_id='.$gr_id.'&id='.$id.'&show_ed='.$show_ed : $g_baseadr.'race_regs_1.php?id='.$id.'&show_ed='.$show_ed;
?>
	<div style="color: #7a4f00; background: #fff3cd; padding: 20px; border: 1px solid #ffc107; margin: 20px; font-family: sans-serif;">
		<?php echo htmlspecialchars($sync_warn); ?>
		<br><br>
		<a href="<?php echo htmlspecialchars($return_url); ?>">Zpět na přehled</a>
	</div>
<?php
	exit;
}

if ($gr_id != 0)
	header('location: '.$g_baseadr.'race_regs_1.php?gr_id='.$gr_id.'&id='.$id.'&show_ed='.$show_ed);
else
	header('location: '.$g_baseadr.'race_regs_1.php?id='.$id.'&show_ed='.$show_ed);
exit;
?>

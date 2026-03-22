<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST, EXTR_SKIP);

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

db_Connect();

$sub_query = (IsLoggedRegistrator() || IsLoggedManager()) ? '' : ' AND '.TBL_USER.'.chief_id = '.$usr->user_id.' OR '.TBL_USER.'.id = '.$usr->user_id;

$query = 'SELECT '.TBL_USER.'.id, kat, termin, sync_status FROM '.TBL_USER.' LEFT JOIN '.TBL_ZAVXUS.' ON '.TBL_USER.'.id = '.TBL_ZAVXUS.'.id_user AND '.TBL_ZAVXUS.'.id_zavod='.$id.' WHERE '.TBL_USER.'.hidden = 0'.$sub_query;

@$vysledek=query_db($query);

@$vysledek_z=query_db("SELECT * FROM ".TBL_RACE." WHERE id=$id");
$zaznam_z = mysqli_fetch_array($vysledek_z);

$is_registrator_on = IsCalledByRegistrator($gr_id);
$is_termin_edit_on = $is_registrator_on && ($zaznam_z['prihlasky'] > 1);
$is_spol_dopr_on = ($zaznam_z["transport"]==1);
$is_sdil_dopr_on = ($zaznam_z["transport"]==3);
$is_spol_ubyt_on = ($zaznam_z["ubytovani"]==1);

$termin = raceterms::GetCurr4RegTerm($zaznam_z);
$has_ext_id = !empty($zaznam_z['ext_id']);
$sync_queue = [];

while ($zaznamZ=mysqli_fetch_array($vysledek))
{
	$user=$zaznamZ["id"];
	if (IsSet($kateg[$user]))
	{
		$kat = correct_sql_string($kateg[$user]);
		$poz = correct_sql_string($pozn[$user]);
		$poz2 = correct_sql_string($pozn2[$user]);
		$cterm = $termin;
		if ($is_spol_dopr_on) {
			$trans = (IsSet($transport[$user])) ? 1 : 'NULL';
			$sedl = 'NULL';
		} else if ($is_sdil_dopr_on) {
			if (IsSet($sedadel[$user])&&is_numeric($sedadel[$user])){
				$trans = 1;
				$sedl = intval($sedadel[$user]);
			} else {
				$trans = 'NULL';
				$sedl = 'NULL';
			}
		} else {
			$trans = 'NULL';
			$sedl = 'NULL';
		}
		$ubyt = ($is_spol_ubyt_on && IsSet($ubytovani[$user])) ? 1 : 'NULL';
		if($is_registrator_on)
		{
			if($is_termin_edit_on && $term[$user] != 0)
				$cterm = (int)$term[$user];
		}
		if ($cterm == 0)
			$cterm = 1;
		
		if ($zaznamZ['kat'] != NULL)
		{	// jiz prihlasen
			// We need the internal ID of the row from TBL_ZAVXUS, but it's not selected. 
			// We'll have to get it, or select it initially. Let's select it initially. Wait, the query is JOINed, but id from ZAVXUS isn't selected!
			// Actually, let's fetch the real ID since we need it.
			$q_zavxus = query_db("SELECT * FROM ".TBL_ZAVXUS." WHERE id_zavod='$id' AND id_user='$user'");
			$row_zx = mysqli_fetch_assoc($q_zavxus);
			$zx_id = $row_zx['id'] ?? 0;

			if ($kat == "")
			{	// del
				$is_pending_create = ($row_zx && $row_zx['sync_status'] === 'PENDING_CREATE');

				if ($has_ext_id && !$is_pending_create) {
					$result=query_db("UPDATE ".TBL_ZAVXUS." SET sync_status='PENDING_DELETE' WHERE id_zavod = '$id' AND id_user = '$user'")
						or die("Chyba při provádění dotazu do databáze.");
					$sync_queue[] = ['id' => $zx_id, 'action' => 'delete', 'previous_state' => $row_zx];
				} else {
					$result=query_db("DELETE FROM ".TBL_ZAVXUS." WHERE id_zavod = '$id' AND id_user = '$user'")
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
				$kat=correct_sql_string($kat);
				$poz=correct_sql_string($poz);
				$poz2=correct_sql_string($poz2);
				$cterm=correct_sql_string($cterm);
			
				$sync_status_update = "";
				if ($has_ext_id && $row_zx && $row_zx['sync_status'] !== 'PENDING_CREATE') {
					$sync_status_update = ", sync_status='PENDING_UPDATE'";
				}

				$result=query_db("UPDATE ".TBL_ZAVXUS." SET kat='$kat', pozn='$poz', pozn_in='$poz2', termin='$cterm', transport=$trans, sedadel=$sedl, ubytovani=$ubyt".$sync_status_update." WHERE id_zavod = '$id' AND id_user = '$user'")
					or die("Chyba při provádění dotazu do databáze.");
				if ($result == FALSE)
					die ("Nepodařilo se změnit přihlášku člena.");

				if ($has_ext_id && $zx_id) {
					$action = ($row_zx['sync_status'] === 'PENDING_CREATE') ? 'create' : 'update';
					$sync_queue[] = ['id' => $zx_id, 'action' => $action, 'previous_state' => $row_zx];
				}
			}
		}
		else
		{
			if ($kat != "")
			{	// new
				$kat=correct_sql_string($kat);
				$poz=correct_sql_string($poz);
				$poz2=correct_sql_string($poz2);
				$cterm=correct_sql_string($cterm);
			
				$sync_status = $has_ext_id ? 'PENDING_CREATE' : 'LOCAL_ONLY';

				$result=query_db("INSERT INTO ".TBL_ZAVXUS." (id_user, id_zavod, kat, pozn, pozn_in, termin, transport, sedadel,ubytovani, sync_status) VALUES ('$user','$id','$kat','$poz','$poz2','$cterm',$trans,$sedl,$ubyt,'$sync_status')")
					or die("Chyba při provádění dotazu do databáze.");
				if ($result == FALSE)
					die ("Nepodařilo se změnit přihlášku člena.");
				if ($result !== false && mysqli_affected_rows($db_conn) > 0) {
					$inserted_id = mysqli_insert_id($db_conn);
					query_db("UPDATE ".TBL_RACE." SET prihlasenych = prihlasenych + 1 WHERE id = '$id'");
					if ($has_ext_id) {
						$sync_queue[] = ['id' => $inserted_id, 'action' => 'create', 'is_new_insert' => true];
					}
				}
			}
		}
	}
}

$sync_errors = [];
if ($has_ext_id && count($sync_queue) > 0) {
	global $g_oris_club_key, $g_shortcut;
	if (!empty($g_oris_club_key)) {
		$service = new OrisIntegrationService($g_oris_club_key);
		foreach ($sync_queue as $sq) {
			$rowQuery = query_db("SELECT z.*, u.reg FROM `" . TBL_ZAVXUS . "` z LEFT JOIN `" . TBL_USER . "` u ON z.id_user = u.id WHERE z.`id` = " . (int)$sq['id']);
			if ($rowQuery && $syncRow = mysqli_fetch_assoc($rowQuery)) {
				$regNum = $syncRow['reg'] ?? $sq['id'];
				if (!empty($regNum) && !preg_match('/^[A-Z]{3}/', $regNum) && $regNum != $sq['id']) {
					$regNum = $g_shortcut . str_pad($regNum, 4, '0', STR_PAD_LEFT);
				}
				$displayName = "Reg.č. " . $regNum;

				if ($sq['action'] === 'delete') {
					$syncRes = processEntry($syncRow, 'delete', $service);
					if ($syncRes === true || $syncRes === 'queued') {
						query_db("UPDATE ".TBL_RACE." SET prihlasenych = GREATEST(0, prihlasenych - 1) WHERE id = '$id'");
					} else {
						$sync_errors[] = $displayName . ": " . getOrisSyncError($sq['id']);
						if (isset($sq['previous_state'])) {
							$prev_sync_status = correct_sql_string($sq['previous_state']['sync_status']);
							query_db("UPDATE ".TBL_ZAVXUS." SET sync_status='$prev_sync_status' WHERE id='{$sq['id']}'");
						}
					}
				} else {
					$syncRes = processEntry($syncRow, $sq['action'], $service);
					if ($syncRes !== true && $syncRes !== 'queued') {
						$sync_errors[] = $displayName . ": " . getOrisSyncError($sq['id']);
						if (!empty($sq['is_new_insert'])) {
							query_db("DELETE FROM ".TBL_ZAVXUS." WHERE id = '{$sq['id']}'");
							query_db("UPDATE ".TBL_RACE." SET prihlasenych = GREATEST(0, prihlasenych - 1) WHERE id = '$id'");
						} else if (isset($sq['previous_state'])) {
							$prev_kat = correct_sql_string($sq['previous_state']['kat']);
							$prev_pozn = correct_sql_string($sq['previous_state']['pozn']);
							$prev_pozn_in = correct_sql_string($sq['previous_state']['pozn_in']);
							$prev_termin = (int)$sq['previous_state']['termin'];
							$prev_transport = (int)$sq['previous_state']['transport'];
							$prev_sedadel = (!isset($sq['previous_state']['sedadel']) || $sq['previous_state']['sedadel'] === null) ? 'null' : (int)$sq['previous_state']['sedadel'];
							$prev_ubytovani = (!isset($sq['previous_state']['ubytovani']) || $sq['previous_state']['ubytovani'] === null) ? 'null' : (int)$sq['previous_state']['ubytovani'];
							$prev_sync_status = correct_sql_string($sq['previous_state']['sync_status']);
							
							query_db("UPDATE ".TBL_ZAVXUS." SET kat='$prev_kat', pozn='$prev_pozn', pozn_in='$prev_pozn_in', termin='$prev_termin', transport=$prev_transport, sedadel=$prev_sedadel, ubytovani=$prev_ubytovani, sync_status='$prev_sync_status' WHERE id='{$sq['id']}'");
						}
					}
				}
			}
		}
	}
}

?>
<?php 
if (!empty($sync_errors)) { 
	$return_url = ($gr_id != 0) ? $g_baseadr.'race_regs_all.php?gr_id='.$gr_id.'&id='.$id : $g_baseadr.'race_regs_all.php?id='.$id;
?>
	<div style="color: red; font-weight: bold; padding: 20px; border: 1px solid red; margin: 20px; font-family: sans-serif;">
		Chyby při synchronizaci s ORIS:<br><br>
		<?php echo implode('<br>', array_map('htmlspecialchars', $sync_errors)); ?>
		<br><br>
		<a href="<?php echo htmlspecialchars($return_url); ?>">Zpět na přehled</a>
	</div>
<?php } else { 
	$return_url = ($gr_id != 0) ? $g_baseadr.'race_regs_all.php?gr_id='.$gr_id.'&id='.$id : $g_baseadr.'race_regs_all.php?id='.$id;
	header("Location: $return_url");
	exit;
} ?>

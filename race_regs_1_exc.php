<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

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
	if ( !isset($sedadel) || $sedadel=='' ) {
		// no seats no trasport
		$sedadel = 'null';
		$transport = 'null';
	} else {
		// if seats set, transport automatically
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
	if ($zaznam != false)
	{
		if ($kateg == '')
		{	// del
//			echo "DEL";
			$result=query_db("DELETE FROM ".TBL_ZAVXUS." WHERE id_zavod = '$id' AND id_user = '$user_id'")
				or die("Chyba při provádění dotazu do databáze.");
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
			
			$result=query_db("UPDATE ".TBL_ZAVXUS." SET kat='$kateg', pozn='$pozn', pozn_in='$pozn2', termin='$termin', transport = '$transport', sedadel = ".$sedadel.", ubytovani = '$ubytovani' WHERE id_zavod = '$id' AND id_user = '$user_id'")
				or die("Chyba při provádění dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodařilo se změnit přihlášku člena.");
		}
	}
	else
	{
		if ($kateg != '')
		{	// new
//			echo "NEW";
			$kateg=correct_sql_string($kateg);
			$pozn=correct_sql_string($pozn);
			$pozn2=correct_sql_string($pozn2);
			$termin=correct_sql_string($termin);

			$result=query_db("INSERT INTO ".TBL_ZAVXUS." (id_user, id_zavod, kat, pozn, pozn_in,termin,transport,sedadel,ubytovani) VALUES ('$user_id','$id','$kateg', '$pozn', '$pozn2','$termin','$transport',".$sedadel.",'$ubytovani')")
				or die("Chyba při provádění dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodařilo se změnit přihlášku člena.");
		}
	}
}
//echo " -".$kateg." u clena ".$user_id." a s pozn.: '".$pozn."'<BR>";
if ($gr_id != 0)
	header('location: '.$g_baseadr.'race_regs_1.php?gr_id='.$gr_id.'&id='.$id.'&show_ed='.$show_ed);
else
	header('location: '.$g_baseadr.'race_regs_1.php?id='.$id.'&show_ed='.$show_ed);
exit;
?>

<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require ("./connect.inc.php");
require ("./sess.inc.php");

if (!IsLoggedRegistrator() && !IsLoggedManager()&& !IsLoggedSmallManager())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
require ("./common.inc.php");
require ("./common_race.inc.php");

$gr_id = (IsSet($gr_id) && is_numeric($gr_id)) ? (int)$gr_id : 0;
$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;
$user_id = (IsSet($user_id) && is_numeric($user_id)) ? (int)$user_id : 0;
$kateg = (IsSet($kateg)) ? $kateg : '';
$pozn = (IsSet($pozn)) ? $pozn : '';
$pozn2 =(IsSet($pozn2)) ? $pozn2 : '';
$new_termin = (IsSet($new_termin) && is_numeric($new_termin)) ? (int)$new_termin : 0;

db_Connect();

$vysledek=MySQL_Query('SELECT * FROM '.TBL_ZAVXUS.' WHERE id_zavod='.$id.' and id_user='.$user_id);
if ($vysledek != FALSE && mysql_num_rows ($vysledek) == 1)
	$zaznam=MySQL_Fetch_Array($vysledek);
else
	$zaznam=false;

@$vysledek_z=MySQL_Query('SELECT * FROM '.TBL_RACE.' WHERE id='.$id);
$zaznam_z = MySQL_Fetch_Array($vysledek_z);

$termin = raceterms::GetCurr4RegTerm($zaznam_z);

$is_registrator_on = IsCalledByRegistrator($gr_id);
$is_termin_show_on = $is_registrator_on && ($zaznam_z['prihlasky'] > 1);

if($is_termin_show_on && $new_termin != 0)
	$termin = $new_termin;

if($termin != 0)
{
	if ($zaznam != false)
	{
		if ($kateg == '')
		{	// del
//			echo "DEL";
			$result=MySQL_Query("DELETE FROM ".TBL_ZAVXUS." WHERE id_zavod = '$id' AND id_user = '$user_id'")
				or die("Chyba pøi provádìní dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodaøilo se zmìnit pøihlášku èlena.");
		}
		else
		{	// update
//			echo "UPD";
			$result=MySQL_Query("UPDATE ".TBL_ZAVXUS." SET kat='$kateg', pozn='$pozn', pozn_in='$pozn2', termin='$termin' WHERE id_zavod = '$id' AND id_user = '$user_id'")
				or die("Chyba pøi provádìní dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodaøilo se zmìnit pøihlášku èlena.");
		}
	}
	else
	{
		if ($kateg != '')
		{	// new
//			echo "NEW";
			$result=MySQL_Query("INSERT INTO ".TBL_ZAVXUS." (id_user, id_zavod, kat, pozn, pozn_in,termin) VALUES ('$user_id','$id','$kateg', '$pozn', '$pozn2','$termin')")
				or die("Chyba pøi provádìní dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodaøilo se zmìnit pøihlášku èlena.");
		}
	}
}
//echo " -".$kateg." u clena ".$user_id." a s pozn.: '".$pozn."'<BR>";
if ($is_termin_show_on)
	header("location: ".$g_baseadr."race_regs_1.php?gr_id=".$gr_id."&id=".$id);
else
	header("location: ".$g_baseadr."race_regs_1.php?id=".$id);
exit;
?>

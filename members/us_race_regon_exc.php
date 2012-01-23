<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
@extract($_REQUEST);

require ("./connect.inc.php");
require ("./sess.inc.php");
include ("./common.inc.php");
include ("./common_race.inc.php");

if (!IsLogged())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

if ($kat != '')
{
	db_Connect();
	@$vysledek_z=MySQL_Query("SELECT datum, vicedenni, prihlasky, prihlasky1, prihlasky2, prihlasky3, prihlasky4, prihlasky5 FROM ".TBL_RACE." WHERE id=$id_zav");
	$zaznam_z = MySQL_Fetch_Array($vysledek_z);

	$termin = GetActiveRaceRegTerm($zaznam_z);
	if($termin == 0)
		$termin = 1;

	if ($novy)
	{
		$vysledek=MySQL_Query("SELECT * FROM ".TBL_ZAVXUS." WHERE id_zavod='$id_zav' and id_user='$id_us'");
		if ($vysledek != FALSE && ($zaznam = MySQL_Fetch_Array($vysledek)) != FALSE )
		{	// latest new == update
			MySQL_Query("UPDATE ".TBL_ZAVXUS." SET kat='$kat', pozn='$pozn', pozn_in='$pozn2', termin='$termin' WHERE id_zavod='$id_zav' and id_user='$id_us'");
		}
		else
		{	// really new
			MySQL_Query("INSERT INTO ".TBL_ZAVXUS." (id_user, id_zavod, kat, pozn, pozn_in,termin) VALUES ('$id_us','$id_zav','$kat','$pozn','$pozn2','$termin')");	
		}
	}
	else
	{	// update
		MySQL_Query("UPDATE ".TBL_ZAVXUS." SET kat='$kat', pozn='$pozn', pozn_in='$pozn2' WHERE id='$id_z'");
	}

}
?>

<SCRIPT LANGUAGE="JavaScript">
<!--
	window.opener.focus();
	window.close();
//-->
</SCRIPT>
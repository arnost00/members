<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
@extract($_REQUEST);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
require_once ("./common.inc.php");
require_once ("./common_race.inc.php");

if (!IsLogged())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
$id_zav = (IsSet($id_zav) && is_numeric($id_zav)) ? (int)$id_zav: 0;
$id_us = (IsSet($id_us) && is_numeric($id_us)) ? (int)$id_us: 0;
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

		@$vysledek_z=query_db("SELECT datum, vicedenni, prihlasky, prihlasky1, prihlasky2, prihlasky3, prihlasky4, prihlasky5 FROM ".TBL_RACE." WHERE id=$id_zav");
		$zaznam_z = mysqli_fetch_array($vysledek_z);

		$termin = raceterms::GetCurr4RegTerm($zaznam_z);

		if ($termin != 0) // not process if invalid termin number
		{
			$transport = !isset($transport)? 'null': 1;
			$ubytovani = !isset($ubytovani)? 'null': 1;
			$novy  = !isset($novy)? 0: (int)$novy;

			if ($novy)
			{
				$vysledek=query_db("SELECT * FROM ".TBL_ZAVXUS." WHERE id_zavod='$id_zav' and id_user='$id_us'");
				if ($vysledek != FALSE && ($zaznam = mysqli_fetch_array($vysledek)) != FALSE )
				{	// latest new == update
					query_db("UPDATE ".TBL_ZAVXUS." SET kat='$kat', pozn='$pozn', pozn_in='$pozn2', termin='$termin', transport=$transport, ubytovani=$ubytovani WHERE id='".$zaznam['id']."'");
				}
				else
				{	// really new
					query_db("INSERT INTO ".TBL_ZAVXUS." (id_user, id_zavod, kat, pozn, pozn_in, termin, transport, ubytovani) VALUES ('$id_us','$id_zav','$kat','$pozn','$pozn2','$termin',$transport, $ubytovani)");	
				}
			}
			else
			{	// update
				query_db("UPDATE ".TBL_ZAVXUS." SET kat='$kat', pozn='$pozn', pozn_in='$pozn2', transport=$transport, ubytovani=$ubytovani WHERE id='".$id_z."'");
			}
		}
	}
}
?>
<SCRIPT LANGUAGE="JavaScript">
	window.opener.location.reload();

	window.opener.focus();
	window.close();
</SCRIPT>
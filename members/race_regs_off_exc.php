<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
require ("./connect.inc.php");
require ("./sess.inc.php");

if (!IsLoggedRegistrator())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

require ("./common.inc.php");
require ("./common_race.inc.php");

$gr_id = (IsSet($gr_id) && is_numeric($gr_id)) ? $gr_id : 0;
db_Connect();

@$vysledek=MySQL_Query("SELECT id_user FROM ".TBL_ZAVXUS." WHERE $id=id_zavod");

while ($zaznam=MySQL_Fetch_Array($vysledek))
{
	$zaz_db[]=$zaznam["id_user"];
}

@$vysledekZ=MySQL_Query("SELECT id,hidden FROM ".TBL_USER);

@$vysledek_z=MySQL_Query("SELECT * FROM ".TBL_RACE." WHERE id=$id");
$zaznam_z = MySQL_Fetch_Array($vysledek_z);

while ($zaznamZ=MySQL_Fetch_Array($vysledekZ))
{
	if ($zaznamZ["hidden"] == 0)
	{
		$user=$zaznamZ["id"];
		if (IsSet($kateg[$user]))
		{
			$kat = $kateg[$user];
			$termin = $term[$user];
			$poz = $pozn[$user];
			$poz2 = $pozn2[$user];
			if (IsSet($zaz_db) && count($zaz_db) > 0 && in_array($user,$zaz_db))
			{
				if ($kat == '')
				{	// del
	//				echo "DEL";
					$result=MySQL_Query("DELETE FROM ".TBL_ZAVXUS." WHERE id_zavod = '$id' AND id_user = '$user'")
						or die("Chyba pøi provádìní dotazu do databáze.");
					if ($result == FALSE)
						die ("Nepodaøilo se zmìnit pøihlášku èlena.");
				}
				else
				{	// update
	//				echo "UPD";
					$result=MySQL_Query("UPDATE ".TBL_ZAVXUS." SET kat='$kat', pozn='$poz', pozn_in='$poz2', termin='$termin' WHERE id_zavod = '$id' AND id_user = '$user'")
						or die("Chyba pøi provádìní dotazu do databáze.");
					if ($result == FALSE)
						die ("Nepodaøilo se zmìnit pøihlášku èlena.");
				}
			}
		}
	}
}
?>
<SCRIPT LANGUAGE="JavaScript">
<!--
	window.opener.focus();
	window.close();
//-->
</SCRIPT>

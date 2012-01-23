<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?

exit; // <-- temporary disabled

@extract($_REQUEST);
require ("./connect.inc.php");
require ("./sess.inc.php");

if (!IsLoggedRegistrator())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
db_Connect();

@$vysledek=MySQL_Query("SELECT id_zavod FROM ".TBL_ZAVXUS." WHERE $id=id_user");

while ($zaznam=MySQL_Fetch_Array($vysledek))
{
	$zaz_db[]=$zaznam["id_zavod"];
}

@$vysledekZ=MySQL_Query("SELECT id FROM ".TBL_RACE);
while ($zaznamZ=MySQL_Fetch_Array($vysledekZ))
{
	$zav=$zaznamZ["id"];
	$kat = $zavod[$zav];
	if (IsSet($zaz_db) && count($zaz_db) > 0 && in_array($zav,$zaz_db))
	{
		if ($kat == "")
		{	// del
//			echo "DEL";
			$result=MySQL_Query("DELETE FROM ".TBL_ZAVXUS." WHERE id_zavod = '$zav' AND id_user = '$id'")
				or die("Chyba pøi provádìní dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodaøilo se zmìnit pøihlášku èlena.");
		}
		else
		{	// update
//			echo "UPD";
			$result=MySQL_Query("UPDATE ".TBL_ZAVXUS." SET kat='$kat' WHERE id_zavod = '$zav' AND id_user = '$id'")
				or die("Chyba pøi provádìní dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodaøilo se zmìnit pøihlášku èlena.");
		}
	}
	else
	{
		if ($kat != "")
		{	// new
//			echo "NEW";
			$result=MySQL_Query("INSERT INTO ".TBL_ZAVXUS." (id_user, id_zavod, kat) VALUES ('$id','$zav','$kat')")
				or die("Chyba pøi provádìní dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodaøilo se zmìnit pøihlášku èlena.");
		}
	}
//	echo " -".$kat." v zavode ".$zav."<BR>";
}

header("location: ".$g_baseadr."rg_user.php?id=".$id);
?>

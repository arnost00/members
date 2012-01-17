<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php /* adminova stranka -oprava ceskych(tridicich) jmen v db.uzivatelu */
require ("./connect.inc.php");
require ("./sess.inc.php");

if (IsLoggedAdmin())
{
		echo 'Selected language for sorting : ';
		switch ($g_czech_sort)
		{
			case 1 :	// cp1250 -> iso?
				echo 'Czech (cs-iso-8859-2)';
				break;
			case 2 :	//	 without changes
				echo 'Czech (cs-win1250)';
				break;
			default :
				echo 'Undefined';
		}
		echo "<BR>\n";
	db_Connect();
	// --> filling of czech sort helping column
	include "./common.inc.php";

	@$vysledek=MySQL_Query("SELECT prijmeni,jmeno,id FROM ".TBL_USER)
		or die("Chyba pøi provádìní dotazu do databáze.");
	$i = 0;
	$cnt = mysql_num_rows($vysledek);
	while ($zaznam=MySQL_Fetch_Array($vysledek))
	{
		$name2 = $zaznam["prijmeni"]." ".$zaznam["jmeno"];
		switch ($g_czech_sort)
		{
			case 1 :	// cp1250 -> iso?
				$name2 = cp2iso($name2);
				break;
			case 2 :	//	 without changes
				break;
		}
		$id = $zaznam["id"];
		$result=MySQL_Query("UPDATE ".TBL_USER." SET sort_name='$name2' WHERE id='$id'")
		or die("Chyba pøi provádìní dotazu do databáze.");
		if ($result != FALSE)
			$i++;
	}
	echo "<HTML><BODY>Výsledek opravy jmen v db :".$i."/".$cnt."<BR><A href=\"".$g_baseadr."index.php?id=300&subid=1\">Návrat na stránky</A></BODY></HTML>";
}
else
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
?>
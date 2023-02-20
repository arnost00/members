<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php /* adminova stranka - oprava tabulky usxzav */

@extract($_REQUEST);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");

if (IsLoggedAdmin())
{
	db_Connect();

	require_once ('./common.inc.php');
	require_once ('./version.inc.php');
	
	$races = Array();
	$users = Array();

	$vysledekR=query_db("SELECT id FROM ".TBL_RACE);
	while ($zaznamR=mysqli_fetch_array($vysledekR))
	{
		$races[] = $zaznamR['id'];
	}

	$vysledekU=query_db("SELECT id FROM ".TBL_USER);
	if ($vysledekU)
	{
		while ($zaznamU=mysqli_fetch_array($vysledekU))
		{
			$users[] = $zaznamU['id'];
		}
	}

	$i = 0;
	$j = 0;
	$vysledek=query_db("SELECT id,id_zavod,id_user FROM ".TBL_ZAVXUS);
	$cnt = mysqli_num_rows($vysledek);
	while ($zaznam=mysqli_fetch_array($vysledek))
	{
		if(in_array($zaznam['id_zavod'],$races) && in_array($zaznam['id_user'],$users))
		{
			$i++;
		}
		else
		{
			$result=query_db("DELETE FROM ".TBL_ZAVXUS." WHERE id='".$zaznam['id']."'")
			or die("Chyba při provádění dotazu do databáze.");
			if ($result != FALSE)
				$j++;
		}
	}
	HTML_Header($g_www_title);
	echo "<b>Výsledek opravy tabluky registaci v db :</b><BR>V pořádku / Celkem : ".$i."/".$cnt."<br>Smazáno / Ke smazání : ".$j."/".($cnt-$i)."<br><A href=\"".$g_baseadr."index.php?id=300&subid=1\">Návrat na stránky</A>";
	HTML_Footer();
}
else
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
?>
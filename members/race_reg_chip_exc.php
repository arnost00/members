<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
require ("./connect.inc.php");
require ("./sess.inc.php");

if (!IsLoggedRegistrator())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

$id_zav = (IsSet($id_zav) && is_numeric($id_zav)) ? (int)$id_zav : 0;

db_Connect();

$query = 'SELECT z.id_user FROM '.TBL_ZAVXUS.' as z, '.TBL_USER.' as u WHERE z.id_user = u.id AND z.id_zavod='.$id_zav.' AND u.si_chip = 0 AND u.hidden = 0';

@$vysledek=MySQL_Query($query);

if (mysql_num_rows($vysledek) > 0)
{
	while ($zaznam=MySQL_Fetch_Array($vysledek))
	{
		$user=$zaznam['id_user'];
		if (IsSet($chip[$user]))
		{
			$si_chip = (int)$chip[$user];
			$result=MySQL_Query('UPDATE '.TBL_ZAVXUS.' SET `si_chip`= '.$si_chip.' WHERE `id_zavod` = '.$id_zav.' AND `id_user` = '.$user)
				or die("Chyba pøi provádìní dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodaøilo se zmìnit pøihlášku èlena.");
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

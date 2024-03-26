<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");

if (!IsLoggedRegistrator())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

$id_zav = (IsSet($id_zav) && is_numeric($id_zav)) ? (int)$id_zav : 0;

db_Connect();

$query = 'SELECT z.id_user, u.si_chip FROM '.TBL_ZAVXUS.' as z, '.TBL_USER.' as u WHERE z.id_user = u.id AND z.id_zavod='.$id_zav.' AND u.hidden = 0';

@$vysledek=query_db($query);

if (mysqli_num_rows($vysledek) > 0)
{
	while ($zaznam=mysqli_fetch_array($vysledek))
	{
		$user=$zaznam['id_user'];
		if (IsSet($chip[$user]))
		{
			$si_chip = (int)$chip[$user];
			if ($si_chip != $zaznam['si_chip'])
			{
				$result=query_db('UPDATE '.TBL_ZAVXUS.' SET `si_chip`= '.$si_chip.' WHERE `id_zavod` = '.$id_zav.' AND `id_user` = '.$user)
					or die("Chyba při provádění dotazu do databáze.");
				if ($result == FALSE)
					die ("Nepodařilo se změnit přihlášku člena.");
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

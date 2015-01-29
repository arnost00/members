<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require ("./connect.inc.php");
require ("./sess.inc.php");
include ("./common.inc.php");

include "./race_kateg_list.inc.php";

if (!IsLoggedRegistrator())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

db_Connect();

$kat = '';
foreach ($kategorie_vypis as $kat_key => $kat_value)
{
	foreach ($zebricek_vypis as $zeb_key => $zeb_value)
	{
		if (IsSet($H[$kat_value][$zeb_value]) && $H[$kat_value][$zeb_value])
		{
			$kat=$kat.'H'.$kat_key.$zeb_key.';';
		}
		if (IsSet($D[$kat_value][$zeb_value]) && $D[$kat_value][$zeb_value])
		{
			$kat=$kat.'D'.$kat_key.$zeb_key.';';
		}
	}
	if (IsSet($H[$kat_value]['X']) && $H[$kat_value]['X'])
	{
		$kat=$kat.'H'.$kat_key.';';
	}
	if (IsSet($D[$kat_value]['X']) && $D[$kat_value]['X'])
	{
		$kat=$kat.'D'.$kat_key.';';
	}

}

$kategorie = $kat.$kat_n;
$kategorie=correct_sql_string($kategorie);

$result=MySQL_Query('UPDATE '.TBL_RACE." SET `kategorie`='$kategorie' WHERE `id`='$id'")
	or die('Chyba při provádění dotazu do databáze.');
if ($result == FALSE)
	die ('Nepodařilo se změnit údaje o závodě.');

?>
<SCRIPT LANGUAGE="JavaScript">
<!--
	window.opener.location.reload();

	window.opener.focus();
	window.close();
//-->
</SCRIPT>

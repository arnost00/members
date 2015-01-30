<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require_once('./connect.inc.php');
require_once('./sess.inc.php');
require_once('./const_strings.inc.php');
require_once('./modify_log.inc.php');

if (IsLoggedSmallAdmin() || IsLoggedFinance())
{
	db_Connect();
	require_once './common_user.inc.php';

	$gr_id = (isset($gr_id) && is_numeric($gr_id)) ? (int)$gr_id : 0;
	$id = (isset($id) && is_numeric($id)) ? (int)$id : 0;

	$vysl2=MySQL_Query('SELECT * FROM '.TBL_USER.' WHERE `id`=\''.$id."'");
	$zaznam2=MySQL_Fetch_Array($vysl2);
	if ($zaznam2 != FALSE)
	{
		$lock = (bool)($zaznam2['entry_locked']);
		$lock = !$lock;
		$result=MySQL_Query('UPDATE '.TBL_USER.' SET entry_locked=\''.$lock.'\' WHERE `id`=\''.$id."'")
			or die('Chyba při provádění dotazu do databáze.');
		if ($result == FALSE)
			die ('Nepodařilo se zamčít/odemčít přihlášky člena.');
		SaveItemToModifyLog_Edit(TBL_USER,$zaznam2['jmeno'].' '.$zaznam2['prijmeni'].' ['.$zaznam2['reg'].'] - entry lock ('.(int)$lock.')');
	}
	header('location: '.$g_baseadr.'index.php?id='.$gr_id.'&subid=1');
}
else
{
	header('location: '.$g_baseadr.'error.php?code=21');
	exit;
}
?>
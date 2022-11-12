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

	$vysl=query_db('SELECT id, entry_locked, jmeno, prijmeni, reg FROM '.TBL_USER.' WHERE `id`=\''.$id."'");
	$zaznam=mysqli_fetch_array($vysl);
	if ($zaznam["id"] != null)
	{
		$lock = (bool)$zaznam['entry_locked']?0:1;
		$result=query_db('UPDATE '.TBL_USER.' SET entry_locked=\''.$lock.'\' WHERE `id`=\''.$id."'")
			or die('Chyba při provádění dotazu do databáze.');
		if ($result == null)
			die ('Nepodařilo se zamčít/odemčít přihlášky člena.');
		SaveItemToModifyLog_Edit(TBL_USER,$zaznam['jmeno'].' '.$zaznam['prijmeni'].' ['.$zaznam['reg'].'] - entry lock ('.(int)$lock.')');
	}
	header('location: '.$g_baseadr.'index.php?id='.$gr_id.'&subid=1');
}
else
{
	header('location: '.$g_baseadr.'error.php?code=21');
	exit;
}
?>
<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require_once('./connect.inc.php');
require_once('./sess.inc.php');
require_once('./const_strings.inc.php');
require_once('./modify_log.inc.php');

if (IsLoggedSmallAdmin())
{
	db_Connect();
	require_once './common_user.inc.php';

	$id = (isset($id) && is_numeric($id)) ? (int)$id : 0;

	if ($id)
	{
		$hidden_result = false;
		$vysl=query_db('SELECT u.id, u.hidden, a.id aid FROM '.TBL_USER.' u left join '.TBL_ACCOUNT.' a on a.id_users = u.id WHERE u.id=\''.$id."'");
		$zaznam=mysqli_fetch_array($vysl);
		if ($zaznam["id"] != null)
		{
			$hidden = (bool)$zaznam['hidden']?0:1;
			$result=query_db('UPDATE '.TBL_USER.' SET hidden=\''.$hidden.'\' WHERE `id`=\''.$id."'")
				or die('Chyba při provádění dotazu do databáze.');
			if ($result == FALSE)
				die ('Nepodařilo se skrýt/zpřístupnit člena.');
			$hidden_result = true;
			SaveItemToModifyLog_Edit(TBL_USER,'user.id = '.$id.' - hide ('.(int)$hidden.')');
		}

		if (($zaznam["aid"] != null) && $hidden_result)
		{
			$lock = $hidden;
			$result=query_db('UPDATE '.TBL_ACCOUNT.' SET locked=\''.$lock.'\' WHERE `id`=\''.$zaznam["aid"]."'")
				or die('Chyba při provádění dotazu do databáze.');
			if ($result == null)
				die ('Nepodařilo se zamčít/odemčít účet člena.');
			SaveItemToModifyLog_Edit(TBL_ACCOUNT,'acc.id = '.$zaznam["aid"].' - lock ('.(int)$lock.')');
		}
	}
	header('location: '.$g_baseadr.'index.php?id='._SMALL_ADMIN_GROUP_ID_.'&subid=1');
}
else
{
	header('location: '.$g_baseadr.'error.php?code=21');
	exit;
}
?>
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
		$vysl=mysqli_query($db_conn, 'SELECT * FROM '.TBL_USER.' WHERE `id`=\''.$id."'");
		$zaznam=mysqli_fetch_array($vysl);
		if ($zaznam != FALSE)
		{
			$hidden = (bool)($zaznam['hidden']);
			$hidden = !$hidden;
			$result=mysqli_query($db_conn, 'UPDATE '.TBL_USER.' SET hidden=\''.$hidden.'\' WHERE `id`=\''.$id."'")
				or die('Chyba při provádění dotazu do databáze.');
			if ($result == FALSE)
				die ('Nepodařilo se skrýt/zpřístupnit člena.');
			$hidden_result = true;
			SaveItemToModifyLog_Edit(TBL_USER,'user.id = '.$id.' - hide ('.(int)$hidden.')');
		}

		$id2 = GetUserAccountId_Users($id);
		if ($id2 && $hidden_result)
		{
			$vysl2=mysqli_query($db_conn, 'SELECT * FROM '.TBL_ACCOUNT.' WHERE `id`=\''.$id2."'");
			$zaznam2=mysqli_fetch_array($vysl2);
			if ($zaznam2 != FALSE)
			{
				$lock = $hidden;
				$result=mysqli_query($db_conn, 'UPDATE '.TBL_ACCOUNT.' SET locked=\''.$lock.'\' WHERE `id`=\''.$id2."'")
					or die('Chyba při provádění dotazu do databáze.');
				if ($result == FALSE)
					die ('Nepodařilo se zamčít/odemčít účet člena.');
				SaveItemToModifyLog_Edit(TBL_ACCOUNT,'acc.id = '.$id2.' - lock ('.(int)$lock.')');
			}
		}
	}
	header('location: '.$g_baseadr.'index.php?id='._SMALL_ADMIN_GROUP_ID_.'&subid=3');
}
else
{
	header('location: '.$g_baseadr.'error.php?code=21');
	exit;
}
?>
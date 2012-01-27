<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require('./connect.inc.php');
require('./sess.inc.php');
require('./const_strings.inc.php');
require('./modify_log.inc.php');

if (IsLoggedSmallAdmin())
{
	db_Connect();
	include './common_user.inc.php';

	$id = (isset($id) && is_numeric($id)) ? (int)$id : 0;

	if ($id)
	{
		$vysl=MySQL_Query('SELECT * FROM '.TBL_USER.' WHERE `id`=\''.$id."'");
		$zaznam=MySQL_Fetch_Array($vysl);
		if ($zaznam != FALSE)
		{
			$hidden = (bool)($zaznam['hidden']);
			$hidden = !$hidden;
			$result=MySQL_Query('UPDATE '.TBL_USER.' SET hidden=\''.$hidden.'\' WHERE `id`=\''.$id."'")
				or die('Chyba pøi provádìní dotazu do databáze.');
			if ($result == FALSE)
				die ('Nepodaøilo se skrýt/zpøístupnit èlena.');
			SaveItemToModifyLog_Edit(TBL_USER,'user.id = '.$id.' - hide ('.(int)$hidden.')');
		}

		$id2 = GetUserAccountId_Users($id);
		if ($id2)
		{
			$vysl2=MySQL_Query('SELECT * FROM '.TBL_ACCOUNT.' WHERE `id`=\''.$id2."'");
			$zaznam2=MySQL_Fetch_Array($vysl2);
			if ($zaznam2 != FALSE)
			{
				$lock = $hidden;
				$result=MySQL_Query('UPDATE '.TBL_ACCOUNT.' SET locked=\''.$lock.'\' WHERE `id`=\''.$id2."'")
					or die('Chyba pøi provádìní dotazu do databáze.');
				if ($result == FALSE)
					die ('Nepodaøilo se zamèít/odemèít úèet èlena.');
				SaveItemToModifyLog_Edit(TBL_ACCOUNT,'acc.id = '.$id2.' - lock ('.(int)$lock.')');
			}
		}
	}
	header('location: '.$g_baseadr.'index.php?id='._SMALL_ADMIN_GROUP_ID_.'&subid=2');
}
else
{
	header('location: '.$g_baseadr.'error.php?code=21');
	exit;
}
?>
<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
require('./connect.inc.php');
require('./sess.inc.php');
require('./const_strings.inc.php');
require('./modify_log.inc.php');

if (IsLoggedSmallAdmin())
{
	db_Connect();
	include './common_user.inc.php';

	if (!IsSet ($id)) $id = 0;

	$id2 = GetUserAccountId_Users($id);
	if ($id2)
	{
		$vysl2=MySQL_Query('SELECT * FROM '.TBL_ACCOUNT.' WHERE `id`=\''.$id2."'");
		$zaznam2=MySQL_Fetch_Array($vysl2);
		if ($zaznam2 != FALSE)
		{
			$lock = (bool)($zaznam2['locked']);
			$lock = !$lock;
			$result=MySQL_Query('UPDATE '.TBL_ACCOUNT.' SET locked=\''.$lock.'\' WHERE `id`=\''.$id2."'")
				or die('Chyba p�i prov�d�n� dotazu do datab�ze.');
			if ($result == FALSE)
				die ('Nepoda�ilo se zam��t/odem��t ��et �lena.');
			SaveItemToModifyLog_Edit(TBL_ACCOUNT,'acc.id = '.$id2.' - lock ('.(int)$lock.')');
		}
	}
	header('location: '.$g_baseadr.'index.php?id=700&subid=1');
}
else
{
	header('location: '.$g_baseadr.'error.php?code=21');
	exit;
}
?>
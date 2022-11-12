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

	$vysl=query_db('SELECT id, locked FROM '.TBL_ACCOUNT.' WHERE `id_users`=\''.$id."'");
	$zaznam=mysqli_fetch_array($vysl);
	if ($zaznam["id"] != null)
	{
		$lock = (bool)$zaznam['locked']?0:1;
		$result=query_db('UPDATE '.TBL_ACCOUNT.' SET locked=\''.$lock.'\' WHERE `id`=\''.$zaznam["id"]."'")
			or die('Chyba při provádění dotazu do databáze.');
		if ($result == null)
			die ('Nepodařilo se zamčít/odemčít účet člena.');
		//pri zamceni rovnou promazat opravneni a mailinfo
		//promazava i pri odemceni, coz nevadi, protoze je stejne promazano, ale neni to hezka
		$result_clear_rights=query_db("update ".TBL_ACCOUNT." set policy_news=0, policy_regs=0, policy_mng=0, policy_adm=0, policy_fin=0 where id_users = ".$id);
		$result_delete_mailinfo=query_db("delete from ".TBL_MAILINFO." where id_user = ".$id);
		if ($result_clear_rights == null or $result_delete_mailinfo == null)
			die ('Nepodařilo se promazat práva nebo mailinfo člena.');
		SaveItemToModifyLog_Edit(TBL_ACCOUNT,'acc.id = '.$zaznam["id"].' - lock ('.(int)$lock.')');
	}
	header('location: '.$g_baseadr.'index.php?id='._SMALL_ADMIN_GROUP_ID_.'&subid=1');
}
else
{
	header('location: '.$g_baseadr.'error.php?code=21');
	exit;
}
?>
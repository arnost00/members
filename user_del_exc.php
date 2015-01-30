<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php /* adminova stranka - provedeni smazani clena */
@extract($_REQUEST);

require_once ('./connect.inc.php');
require_once ('./sess.inc.php');
require_once ('./modify_log.inc.php');

if (IsLoggedSmallAdmin())
{
	$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

	if ($id == $usr->user_id)
	{
		header("location: ".$g_baseadr."error.php?code=202");
		exit;
	}

	db_Connect();
	require_once "./common_user.inc.php";

	@$vysledek=MySQL_Query("DELETE FROM ".TBL_USER." WHERE id = '$id'");
	$id2 = GetUserAccountId_Users($id);
	SaveItemToModifyLog_Delete(TBL_USER,'id = '.$id);
	if ($id2)
	{	// has account
		@$vysledek=MySQL_Query("DELETE FROM ".TBL_ACCOUNT." WHERE id = '$id2'");
		@$vysledek=MySQL_Query("DELETE FROM ".TBL_USXUS." WHERE id_accounts = '$id2'");
		SaveItemToModifyLog_Delete(TBL_ACCOUNT,'user.id = '.$id.' acc.id = '.$id2);
	}
	header("location: ".$g_baseadr."index.php?id=700&subid=1");
}
else if (IsLoggedManager())
{
	$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;
	
	if ($id == $usr->user_id)
	{
		header("location: ".$g_baseadr."error.php?code=202");
		exit;
	}
	db_Connect();
	require_once "./common_user.inc.php";

	@$vysledek=MySQL_Query("DELETE FROM ".TBL_USER." WHERE id = '$id'");
	$id2 = GetUserAccountId_Users($id);
	SaveItemToModifyLog_Delete(TBL_USER,'id = '.$id);
	if ($id2)
	{	// has account
		@$vysledek=MySQL_Query("DELETE FROM ".TBL_ACCOUNT." WHERE id = '$id2'");
		@$vysledek=MySQL_Query("DELETE FROM ".TBL_USXUS." WHERE id_accounts = '$id2'");
		SaveItemToModifyLog_Delete(TBL_ACCOUNT,'user.id = '.$id.' acc.id = '.$id2);
	}
	header("location: ".$g_baseadr."index.php?id=500&subid=1");
}
else
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
?>
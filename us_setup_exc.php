<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php /* clenova stranka - provedeni zmeny informaci a nastaveni */
@extract($_REQUEST);

require('./connect.inc.php');
require('./sess.inc.php');
require('./const_strings.inc.php');
require('./modify_log.inc.php');

if (IsLogged())
{
	db_Connect();
	include "./common_user.inc.php";

	$result=0;
	switch ($type)
	{
	case 1: // podpis & login
		if ($podpis=="" || $login=="")
			$result=CS_EMPTY_ITEM;
		else if (strlen($login) < 4)
			$result=CS_MIN_LEN_LOGIN;
		else if (!CheckIfLoginIsValid($login,$id))
			$result=CS_LOGIN_EXIST;
		else
		{
			MySQL_Query("UPDATE ".TBL_ACCOUNT." SET podpis='$podpis', login='$login' WHERE id='$id'");
			$result=CS_LOGIN_UPDATED;
			SaveItemToModifyLog_Edit(TBL_ACCOUNT,'acc.id = '.$id.' login = "'.$login.'" ['.$podpis.']');
		}
		break;
	case 2: // heslo
		if ($oldheslo=="" || $heslo=="" || $heslo2=="")
			$result=CS_EMPTY_ITEM;
		else
		{
			$hheslo = md5($heslo);
			$oldhheslo = md5($oldheslo);
			$vysledek=MySQL_Query("SELECT heslo FROM ".TBL_ACCOUNT." WHERE id = '$id' LIMIT 1");
			$curr_usr=MySQL_Fetch_Array($vysledek);
			if ($oldhheslo != $curr_usr["heslo"])
				$result=CS_BAD_CUR_PASS;
			else if ($heslo == $oldheslo)
				$result=CS_NODIFF_PASS;
			else if (strlen($heslo) < 4)
				$result=CS_MIN_LEN_PASS;
			else if ($heslo != $heslo2)
				$result=CS_DIFF_NEWPASS;
			else
			{
				MySQL_Query("UPDATE ".TBL_ACCOUNT." SET heslo='$hheslo' WHERE id='$id'");
				$result=CS_PASS_UPDATED;
				SaveItemToModifyLog_Edit(TBL_ACCOUNT,'acc.id = '.$id.' - pass');
			}
		}
		break;
	default:
		$result=CS_UNKNOWN_ERROR;
	}
	header("location: ".$g_baseadr."index.php?id=200&subid=1&result=".$result);
}
else
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
?>
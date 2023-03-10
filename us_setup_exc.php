<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php /* clenova stranka - provedeni zmeny informaci a nastaveni */
@extract($_REQUEST);

require_once('./connect.inc.php');
require_once('./sess.inc.php');
require_once('./const_strings.inc.php');
require_once('./modify_log.inc.php');

if (IsLogged())
{
	$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;
	$type = (IsSet($type) && is_numeric($type)) ? (int)$type : 0;
	
	db_Connect();
	require_once "./common_user.inc.php";

	$result=0;
	switch ($type)
	{
	case 1: // podpis & login
		$login=correct_sql_string($login);
		$podpis=correct_sql_string($podpis);
		$vysledek=query_db("SELECT heslo FROM ".TBL_ACCOUNT." WHERE id = '$id' LIMIT 1");
		$curr_usr=mysqli_fetch_array($vysledek);

		if (!password_verify(md5($hesloo), $curr_usr['heslo']))
			$result=CS_BAD_CUR_PASS;
		else if ($podpis=="" || $login=="")
			$result=CS_EMPTY_ITEM;
		else if (strlen($login) < 4)
			$result=CS_MIN_LEN_LOGIN;
		else if (!CheckIfLoginIsValid($login,$id))
			$result=CS_LOGIN_EXIST;
		else
		{
			query_db("UPDATE ".TBL_ACCOUNT." SET podpis='$podpis', login='$login' WHERE id='$id'");
			$result=CS_LOGIN_UPDATED;
			SaveItemToModifyLog_Edit(TBL_ACCOUNT,'acc.id = '.$id.' login = "'.$login.'" ['.$podpis.']');
		}
		break;
	case 2: // heslo
		if ($oldheslo=="" || $heslo=="" || $heslo2=="")
			$result=CS_EMPTY_ITEM;
		else
		{
			$vysledek=query_db("SELECT heslo FROM ".TBL_ACCOUNT." WHERE id = '$id' LIMIT 1");
			$curr_usr=mysqli_fetch_array($vysledek);
			if (!password_verify(md5($oldheslo), $curr_usr['heslo']))
				$result=CS_BAD_CUR_PASS;
			else if ($heslo == $oldheslo)
				$result=CS_NODIFF_PASS;
			else if (strlen($heslo) < 4)
				$result=CS_MIN_LEN_PASS;
			else if ($heslo != $heslo2)
				$result=CS_DIFF_NEWPASS;
			else
			{
				$hheslo = password_hash(md5($heslo), PASSWORD_DEFAULT);
				query_db("UPDATE ".TBL_ACCOUNT." SET heslo='$hheslo' WHERE id='$id'");
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
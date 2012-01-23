<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require('./connect.inc.php');
require('./sess.inc.php');
require('./const_strings.inc.php');
require('./modify_log.inc.php');

if (IsLoggedAdmin())
{
	db_Connect();
	include "./common_user.inc.php";

	$result=0;

	if (!IsSet ($news)) $news = 0;
	if (!IsSet ($regs)) $regs = 0;
	if (!IsSet ($mng)) $mng = 0;
	if (!IsSet ($mng2)) $mng2 = 0;
	if (!IsSet ($adm)) $adm = 0;
	if (!IsSet ($lock)) $lock = 0;

	if ($mng2 == 1) 
		$mng = _MNG_BIG_INT_VALUE_;
	else if ($mng == 1)
		$mng = _MNG_SMALL_INT_VALUE_;

	switch ($type)
	{
	case 1: // update
		$id2 = GetUserAccountId_Users($id);
		if ($login=="" || $podpis=="")
			$result=CS_EMPTY_ITEM;
		else if (!CheckIfLoginIsValid($login,$id2))
			$result=CS_LOGIN_EXIST;
		else
		{
			$vysledek=MySQL_Query("UPDATE ".TBL_ACCOUNT." SET login='$login', podpis='$podpis', policy_news='$news', policy_regs='$regs', policy_mng='$mng', policy_adm='$adm' WHERE id='$id2'")
				or die("Chyba pøi provádìní dotazu do databáze.");
			if ($vysledek == FALSE)
				die ("Nepodaøilo se upravit úèet èlenu.");
			else
				$result = CS_ACC_UPDATED;
			SaveItemToModifyLog_Edit(TBL_ACCOUNT,'acc.id = '.$id2.' login = "'.$login.'" ['.$podpis.']');
		}
		break;
	case 2: // new
		if ($login=="" || $podpis=="" || $nheslo=="" || $nheslo2=="")
			$result=CS_EMPTY_ITEM;
		else if (!CheckIfLoginIsValid($login,0))
			$result=CS_LOGIN_EXIST;
		else if (strlen($nheslo) < 4)
			$result=CS_MIN_LEN_PASS;
		else if ($nheslo != $nheslo2)
			$result=CS_DIFF_NEWPASS;
		else
		{
			$hheslo = md5($nheslo);
			$id2 = 9; // min. value
			// find max idx in table "usxus" -->
			{
				@$vysledek=MySQL_Query("SELECT id_accounts FROM ".TBL_USXUS);
				while ($zaznam=MySQL_Fetch_Array($vysledek))
				{
					if ($zaznam["id_accounts"] > $id2)
						$id2 = $zaznam["id_accounts"];
				}
				$id2++;	// = maximum + 1
			}
			// <--
			$vysledek=MySQL_Query("INSERT INTO ".TBL_ACCOUNT." (id,login,heslo,policy_news,policy_regs,policy_mng,policy_adm,podpis) VALUES ('$id2','$login','$hheslo','$news','$regs','$mng','$adm','$podpis')")
				or die("Chyba pøi provádìní dotazu do databáze.");
			if ($vysledek == FALSE)
				die ("Nepodaøilo se založit úèet èlenu.");
			else
			{
				$vysledek=MySQL_Query("INSERT INTO ".TBL_USXUS." (id_accounts,id_users) VALUES ('$id2','$id')")
					or die("Chyba pøi provádìní dotazu do databáze.");
				$result = CS_ACC_CREATED;
			}
			SaveItemToModifyLog_Add(TBL_ACCOUNT,'acc.id = '.$id2.' login = "'.$login.'" ['.$podpis.']');
		}
		break;
	case 3: // password
		if ($nheslo=="" || $nheslo2=="")
			$result=CS_EMPTY_ITEM;
		else if (strlen($nheslo) < 4)
			$result=CS_MIN_LEN_PASS;
		else if ($nheslo != $nheslo2)
			$result=CS_DIFF_NEWPASS;
		else
		{
			$id2 = GetUserAccountId_Users($id);
			$hheslo = md5($nheslo);
			$vysledek=MySQL_Query("UPDATE ".TBL_ACCOUNT." SET heslo='$hheslo' WHERE id='$id2'")
				or die("Chyba pøi provádìní dotazu do databáze.");
			if ($vysledek == FALSE)
				die ("Nepodaøilo se upravit heslo èlena.");
			else
				$result = CS_USER_PASS_UPDATED;
			SaveItemToModifyLog_Edit(TBL_ACCOUNT,'acc.id = '.$id2.' - pass');
		}
		break;
	case 4: // lock
		$id2 = GetUserAccountId_Users($id);
		$lock = !$lock;
		$result=MySQL_Query("UPDATE ".TBL_ACCOUNT." SET locked='$lock' WHERE id='$id2'")
			or die("Chyba pøi provádìní dotazu do databáze.");
		if ($result == FALSE)
			die ("Nepodaøilo se zamèít/odemèít úèet èlena.");
		else
			$result = CS_USER_LOCK_ACC;
		SaveItemToModifyLog_Edit(TBL_ACCOUNT,'acc.id = '.$id2.' - lock');
		break;
	default:
		$result=CS_UNKNOWN_ERROR;
	}
	header("location: ".$g_baseadr."index.php?id=300&subid=3&result=".$result);
}
else if (IsLoggedManager())
{
	db_Connect();
	include "./common_user.inc.php";

	$result="";

	if (!IsSet ($news)) $news = 0;
	if (!IsSet ($mng))
		$mng2 = 0;
	else
		$mng2 = _MNG_SMALL_INT_VALUE_;
	switch ($type)
	{
	case 1: // update
		$id2 = GetUserAccountId_Users($id);
		if ($login=="" || $podpis=="")
			$result=CS_EMPTY_ITEM;
		else if (!CheckIfLoginIsValid($login,$id2))
			$result=CS_LOGIN_EXIST;
		else
		{
			$result=MySQL_Query("UPDATE ".TBL_ACCOUNT." SET login='$login', podpis='$podpis', policy_news='$news', policy_mng='$mng2' WHERE id='$id2'")
				or die("Chyba pøi provádìní dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodaøilo se upravit úèet èlenu.");
			else
				$result = CS_ACC_UPDATED;
			SaveItemToModifyLog_Edit(TBL_ACCOUNT,'acc.id = '.$id2.' login = "'.$login.'" ['.$podpis.']');
		}
		break;
	case 2: // new
		if ($login=="" || $podpis=="" || $nheslo=="" || $nheslo2=="")
			$result=CS_EMPTY_ITEM;
		else if (!CheckIfLoginIsValid($login,0))
			$result=CS_LOGIN_EXIST;
		else if (strlen($nheslo) < 4)
			$result=CS_MIN_LEN_PASS;
		else if ($nheslo != $nheslo2)
			$result=CS_DIFF_NEWPASS;
		else
		{
			$hheslo = md5($nheslo);
			$id2 = 9; // min. value
			// find max idx in table "usxus" -->
			{
				@$vysledek=MySQL_Query("SELECT * FROM ".TBL_USXUS);
				while ($zaznam=MySQL_Fetch_Array($vysledek))
				{
					if ($zaznam["id_accounts"] > $id2)
						$id2 = $zaznam["id_accounts"];
				}
				$id2++;	// = maximum + 1
			}
			// <--
			$result=MySQL_Query("INSERT INTO ".TBL_ACCOUNT." (id,login,heslo,policy_news,policy_regs,policy_mng,podpis) VALUES ('$id2','$login','$hheslo','$news',0,'$mng2','$podpis')")
				or die("Chyba pøi provádìní dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodaøilo se založit úèet èlenu.");
			else
			{
				$result=MySQL_Query("INSERT INTO ".TBL_USXUS." (id_accounts,id_users) VALUES ('$id2','$id')")
					or die("Chyba pøi provádìní dotazu do databáze.");
				$result = CS_ACC_CREATED;
			}
			SaveItemToModifyLog_Add(TBL_ACCOUNT,'acc.id = '.$id2.' login = "'.$login.'" ['.$podpis.']');
		}
		break;
	case 3: // password
		if ($nheslo=="" || $nheslo2=="")
			$result=CS_EMPTY_ITEM;
		else if (strlen($nheslo) < 4)
			$result=CS_MIN_LEN_PASS;
		else if ($nheslo != $nheslo2)
			$result=CS_DIFF_NEWPASS;
		else
		{
			$id2 = GetUserAccountId_Users($id);
			$hheslo = md5($nheslo);
			$result=MySQL_Query("UPDATE ".TBL_ACCOUNT." SET heslo='$hheslo' WHERE id='$id2'")
				or die("Chyba pøi provádìní dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodaøilo se upravit heslo èlena.");
			else
				$result = CS_USER_PASS_UPDATED;
			SaveItemToModifyLog_Edit(TBL_ACCOUNT,'acc.id = '.$id2.' - pass');
		}
		break;
	default:
		$result=CS_UNKNOWN_ERROR;
	}
	header("location: ".$g_baseadr."index.php?id=500&subid=1&result=".$result);
}
else if (IsLoggedSmallManager())
{
	db_Connect();
	include "./common_user.inc.php";

	$result="";

	switch ($type)
	{
	case 3: // password
		if ($nheslo=="" || $nheslo2=="")
			$result=CS_EMPTY_ITEM;
		else if (strlen($nheslo) < 4)
			$result=CS_MIN_LEN_PASS;
		else if ($nheslo != $nheslo2)
			$result=CS_DIFF_NEWPASS;
		else
		{
			$id2 = GetUserAccountId_Users($id);
			$hheslo = md5($nheslo);
			$result=MySQL_Query("UPDATE ".TBL_ACCOUNT." SET heslo='$hheslo' WHERE id='$id2'")
				or die("Chyba pøi provádìní dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodaøilo se upravit heslo èlena.");
			else
				$result = CS_USER_PASS_UPDATED;
			SaveItemToModifyLog_Edit(TBL_ACCOUNT,'acc.id = '.$id2.' - pass');
		}
		break;
	default:
		$result=CS_UNKNOWN_ERROR;
	}
	header("location: ".$g_baseadr."index.php?id=600&subid=1&result=".$result);
}
else
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
?>
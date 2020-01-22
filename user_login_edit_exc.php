<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require_once('./connect.inc.php');
require_once('./sess.inc.php');
require_once('./const_strings.inc.php');
require_once('./modify_log.inc.php');

function GenerateInfoEmail($type,$id,$login,$heslo,$email)
{
	global $g_baseadr, $g_emailadr, $g_shortcut, $g_fullname;
	define ('DIV_LINE','-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-');
	define ('EMAIL_ENDL',"\n");

	$name = '';
	@$vysledek=query_db("SELECT jmeno,prijmeni FROM ".TBL_USER." WHERE id = '$id' LIMIT 1");
	if ($zaznam=mysqli_fetch_array($vysledek))
	{
		$name = $zaznam['jmeno'].' '.$zaznam['prijmeni'];
	}
	
	if ($login == '')
	{
		$id_acc=GetUserAccountId_Users($id);
		@$vysledek=query_db("SELECT login FROM ".TBL_ACCOUNT." WHERE id = '$id_acc' LIMIT 1");
		if ($zaznam=mysqli_fetch_array($vysledek))
		{
			$login = $zaznam['login'];
		}
	}
	
	$full_msg = 'Dobrý den, '.EMAIL_ENDL.EMAIL_ENDL;
	if ($type == 1) // nove heslo
		$full_msg .= 'Bylo vám vygenerováno nové heslo pro váš účet v přihláškovém systému oddílu '.$g_shortcut.'.'.EMAIL_ENDL.EMAIL_ENDL;
	else if ($type == 2) // novy ucet
		$full_msg .= 'Byl vám vytvořen nový účet v přihláškovém systému oddílu '.$g_shortcut.'.'.EMAIL_ENDL.EMAIL_ENDL;
	$full_msg .= 'Adresa: '.$g_baseadr.EMAIL_ENDL;
	if ($name != '')
		$full_msg .= 'Jméno : '.$name.EMAIL_ENDL;
	if ($login != '')
		$full_msg .= 'Login : '.$login.EMAIL_ENDL;
	$full_msg .= 'Heslo : '.$heslo.EMAIL_ENDL.EMAIL_ENDL;
	$full_msg .= 'V případě problémů s přihlášením, směřujte případné dotazy na email '.$g_emailadr.'.'.EMAIL_ENDL.EMAIL_ENDL;
	$full_msg .= 'Vygenerované heslo si lze po přihlášení v systému změnit.'.EMAIL_ENDL.EMAIL_ENDL;
	$full_msg .= DIV_LINE.EMAIL_ENDL;
	$full_msg .= 'Na tento email prosím neodpovídejte, byl vytvořen přihláškovým systémem oddílu '.$g_shortcut.'.'.EMAIL_ENDL;
	
	$subject = 'přihláškový systém - informace o účtu';
/*
	// DEV - output
	$fp = fopen(dirname(__FILE__) .'/logs/dbg_login_mail_'.md5(date('d.m.Y - H:i:s')).'.txt', 'a');
	fputs($fp, 'To: '.$email."\r\n");
	fputs($fp, 'From: '.$g_emailadr."\r\n");
	fputs($fp, 'Subject: '.$subject."\r\n");
	fputs($fp, DIV_LINE."\r\n");
	fputs($fp, $full_msg."\r\n");
	fclose($fp);
*/
	require_once ('version.inc.php');
	require_once('common.inc.php');
	SendEmail($g_fullname, $g_emailadr,'',$email,$full_msg,$subject);
}

if (IsLoggedSmallAdmin())
{
	$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;
	$type = (IsSet($type) && is_numeric($type)) ? (int)$type : 0;
	$action_type = (IsSet($action_type) && is_numeric($action_type)) ? (int)$action_type : 1;

	db_Connect();
	require_once "./common_user.inc.php";

	$result=0;

	if (!IsSet ($news)) $news = 0;
	if (!IsSet ($regs)) $regs = 0;
	if (!IsSet ($mng)) $mng = 0;
	if (!IsSet ($mng2)) $mng2 = 0;
	if (!IsSet ($adm)) $adm = 0;
	if (!IsSet ($fin)) $fin = 0;

	if ($mng2 == 1) 
		$mng = _MNG_BIG_INT_VALUE_;
	else if ($mng == 1)
		$mng = _MNG_SMALL_INT_VALUE_;

	switch ($type)
	{
	case 1: // update
		$login=correct_sql_string($login);
		$podpis=correct_sql_string($podpis);
		$news=correct_sql_string($news);
		$regs=correct_sql_string($regs);
		$mng=correct_sql_string($mng);
		$fin=correct_sql_string($fin);
		$adm=correct_sql_string($adm);
 
		$id2 = GetUserAccountId_Users($id);
		if ($login=="" || $podpis=="")
			$result=CS_EMPTY_ITEM;
		else if (!CheckIfLoginIsValid($login,$id2))
			$result=CS_LOGIN_EXIST;
		else
		{
			$vysledek=query_db("UPDATE ".TBL_ACCOUNT." SET login='$login', podpis='$podpis', policy_news='$news', policy_regs='$regs', policy_mng='$mng', policy_adm='$adm', policy_fin='$fin' WHERE id='$id2'")
				or die("Chyba při provádění dotazu do databáze.");
			if ($vysledek == FALSE)
				die ("Nepodařilo se upravit účet členu.");
			else
				$result = CS_ACC_UPDATED;
			SaveItemToModifyLog_Edit(TBL_ACCOUNT,'acc.id = '.$id2.' login = "'.$login.'" ['.$podpis.']');
		}
		break;
	case 2: // new
		$login=correct_sql_string($login);
		$podpis=correct_sql_string($podpis);
		$news=correct_sql_string($news);
		$regs=correct_sql_string($regs);
		$mng=correct_sql_string($mng);
		$fin=correct_sql_string($fin);
		$adm=correct_sql_string($adm);

		if ($action_type == 2)
		{
			require_once ('generators.inc.php');
			$login=correct_sql_string($login_g);
			$podpis=correct_sql_string($podpis_g);
			$nheslo2 = $nheslo = GeneratePassword(8);
		}

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
				@$vysledek=query_db("SELECT id_accounts FROM ".TBL_USXUS);
				while ($zaznam=mysqli_fetch_array($vysledek))
				{
					if ($zaznam["id_accounts"] > $id2)
						$id2 = $zaznam["id_accounts"];
				}
				$id2++;	// = maximum + 1
			}
			// <--
			$vysledek=query_db("INSERT INTO ".TBL_ACCOUNT." (id,login,heslo,policy_news,policy_regs,policy_mng,policy_adm,policy_fin,podpis) VALUES ('$id2','$login','$hheslo','$news','$regs','$mng','$adm','$fin','$podpis')")
				or die("Chyba při provádění dotazu do databáze.");
			if ($vysledek == FALSE)
				die ("Nepodařilo se založit účet členu.");
			else
			{
				$vysledek=query_db("INSERT INTO ".TBL_USXUS." (id_accounts,id_users) VALUES ('$id2','$id')")
					or die("Chyba při provádění dotazu do databáze.");
				$result = CS_ACC_CREATED;
			}
			SaveItemToModifyLog_Add(TBL_ACCOUNT,'acc.id = '.$id2.' login = "'.$login.'" ['.$podpis.']');
			if ($action_type == 2 && $email != '')
			{	// send email
				GenerateInfoEmail(2,$id,$login,$nheslo,$email);
			}
		}
		break;
	case 3: // password
		if ($action_type == 2)
		{
			require_once ('generators.inc.php');
			$nheslo2 = $nheslo = GeneratePassword(8);
		}

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
			$vysledek=query_db("UPDATE ".TBL_ACCOUNT." SET heslo='$hheslo' WHERE id='$id2'")
				or die("Chyba při provádění dotazu do databáze.");
			if ($vysledek == FALSE)
				die ("Nepodařilo se upravit heslo člena.");
			else
				$result = CS_USER_PASS_UPDATED;
			SaveItemToModifyLog_Edit(TBL_ACCOUNT,'acc.id = '.$id2.' - pass');
			if ($action_type == 2 && $email != '')
			{	// send email
				GenerateInfoEmail(1,$id,'',$nheslo,$email);
			}
		}
		break;
	default:
		$result=CS_UNKNOWN_ERROR;
	}
	header("location: ".$g_baseadr."index.php?id=700&subid=1&result=".$result);
}
else if (IsLoggedManager())
{
	$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;
	$type = (IsSet($type) && is_numeric($type)) ? (int)$type : 0;
	$action_type = (IsSet($action_type) && is_numeric($action_type)) ? (int)$action_type : 1;
	
	db_Connect();
	require_once "./common_user.inc.php";

	$result="";

	if (!IsSet ($news)) $news = 0;
	if (!IsSet ($mng))
		$mng2 = 0;
	else
		$mng2 = _MNG_SMALL_INT_VALUE_;
	switch ($type)
	{
	case 1: // update
		$login=correct_sql_string($login);
		$podpis=correct_sql_string($podpis);
		$news=correct_sql_string($news);
		$mng2=correct_sql_string($mng2);

 		$id2 = GetUserAccountId_Users($id);
		if ($login=="" || $podpis=="")
			$result=CS_EMPTY_ITEM;
		else if (!CheckIfLoginIsValid($login,$id2))
			$result=CS_LOGIN_EXIST;
		else
		{
			$result=query_db("UPDATE ".TBL_ACCOUNT." SET login='$login', podpis='$podpis', policy_news='$news', policy_mng='$mng2' WHERE id='$id2'")
				or die("Chyba při provádění dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodařilo se upravit účet členu.");
			else
				$result = CS_ACC_UPDATED;
			SaveItemToModifyLog_Edit(TBL_ACCOUNT,'acc.id = '.$id2.' login = "'.$login.'" ['.$podpis.']');
		}
		break;
	case 2: // new
		$login=correct_sql_string($login);
		$podpis=correct_sql_string($podpis);
		$news=correct_sql_string($news);
		$mng2=correct_sql_string($mng2);

		if ($action_type == 2)
		{
			require_once ('generators.inc.php');
			$login=correct_sql_string($login_g);
			$podpis=correct_sql_string($podpis_g);
			$nheslo2 = $nheslo = GeneratePassword(8);
		}

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
				@$vysledek=query_db("SELECT * FROM ".TBL_USXUS);
				while ($zaznam=mysqli_fetch_array($vysledek))
				{
					if ($zaznam["id_accounts"] > $id2)
						$id2 = $zaznam["id_accounts"];
				}
				$id2++;	// = maximum + 1
			}
			// <--
			$result=query_db("INSERT INTO ".TBL_ACCOUNT." (id,login,heslo,policy_news,policy_regs,policy_mng,podpis) VALUES ('$id2','$login','$hheslo','$news',0,'$mng2','$podpis')")
				or die("Chyba při provádění dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodařilo se založit účet členu.");
			else
			{
				$result=query_db("INSERT INTO ".TBL_USXUS." (id_accounts,id_users) VALUES ('$id2','$id')")
					or die("Chyba při provádění dotazu do databáze.");
				$result = CS_ACC_CREATED;
			}
			SaveItemToModifyLog_Add(TBL_ACCOUNT,'acc.id = '.$id2.' login = "'.$login.'" ['.$podpis.']');
			if ($action_type == 2 && $email != '')
			{	// send email
				GenerateInfoEmail(2,$id,$login,$nheslo,$email);
			}
		}
		break;
	case 3: // password
		if ($action_type == 2)
		{
			require_once ('generators.inc.php');
			$nheslo2 = $nheslo = GeneratePassword(8);
		}

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
			$result=query_db("UPDATE ".TBL_ACCOUNT." SET heslo='$hheslo' WHERE id='$id2'")
				or die("Chyba při provádění dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodařilo se upravit heslo člena.");
			else
				$result = CS_USER_PASS_UPDATED;
			SaveItemToModifyLog_Edit(TBL_ACCOUNT,'acc.id = '.$id2.' - pass');
			if ($action_type == 2 && $email != '')
			{	// send email
				GenerateInfoEmail(1,$id,'',$nheslo,$email);
			}
		}
		break;
	default:
		$result=CS_UNKNOWN_ERROR;
	}
	header("location: ".$g_baseadr."index.php?id=500&subid=1&result=".$result);
}
else if (IsLoggedSmallManager())
{
	$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;
	$type = (IsSet($type) && is_numeric($type)) ? (int)$type : 0;
	
	db_Connect();
	require_once "./common_user.inc.php";

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
			$result=query_db("UPDATE ".TBL_ACCOUNT." SET heslo='$hheslo' WHERE id='$id2'")
				or die("Chyba při provádění dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodařilo se upravit heslo člena.");
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
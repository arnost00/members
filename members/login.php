<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
require ("./connect.inc.php");
require ("./sess.inc.php");
require ("./common.inc.php");
if (!IsLogged())
{
	$login = (isset($_POST[_VAR_USER_LOGIN])) ? mysql_real_escape_string($_POST[_VAR_USER_LOGIN]) : '';
	$password = (isset($_POST[_VAR_USER_PASS])) ? $_POST[_VAR_USER_PASS] : '';
	if($login == '')
	{
		header('location: '.$g_baseadr);
		exit;
	}
	db_Connect();
	@$vysledek=MySQL_Query('SELECT * FROM '.TBL_ACCOUNT.' WHERE `login` = \''.$login.'\' LIMIT 1');
	if (!$vysledek)
	{
		header("location: ".$g_baseadr."error.php?code=12");
		exit;
	}
	$zaznam=MySQL_Fetch_Array($vysledek);
	if (!$zaznam)
	{
		if ($g_log_loginfailed)
		{	// log.
			$ipa =getenv ('REMOTE_ADDR');
			$cd = getdate();
			$scd = $cd['mday'].'.'.$cd['mon'].'.'.$cd['year'].' - '.$cd['hours'].':'.$cd['minutes'].'.'.$cd['seconds'];
			$msg = 'username | '.$scd.' | user : '.$login.' | IP : '.$ipa.' ('.gethostbyaddr($ipa).")\r\n";
			LogToFile(dirname(__FILE__) . '/logs/.bad_login.txt',$msg);
		}
		header("location: ".$g_baseadr."error.php?code=101");
		exit;
	}
	if (md5($password) != $zaznam['heslo'])
	{
		if ($g_log_loginfailed)
		{	// log.
			$ipa =getenv ('REMOTE_ADDR');
			$cd = getdate();
			$scd = $cd['mday'].'.'.$cd['mon'].'.'.$cd['year'].' - '.$cd['hours'].':'.$cd['minutes'].'.'.$cd['seconds'];
			$msg = 'password | '.$scd.' | user : '.$login.' | IP : '.$ipa.' ('.gethostbyaddr($ipa).")\r\n";
			LogToFile(dirname(__FILE__) . '/logs/.bad_login.txt',$msg);
		}
		header("location:".$g_baseadr."error.php?code=102");
		exit;
	}
	if ($zaznam["locked"])
	{
		header("location: ".$g_baseadr."error.php?code=103");
		exit;
	}

	// set information for login user
	$usr->logged=1;
	$usr->account_id=$zaznam["id"];
	$usr->policy_news=$zaznam["policy_news"];
	$usr->policy_reg=$zaznam["policy_regs"];
	$usr->policy_mng=$zaznam["policy_mng"];
	$usr->policy_sadmin=$zaznam["policy_adm"];
	$usr->policy_admin=($usr->account_id == $g_www_admin_id);
	$usr->policy_fin=$zaznam["policy_fin"];
	if ($usr->policy_admin)
	{	// admin has all rights
		$usr->policy_news = 1;
		$usr->policy_reg = 1;
		$usr->policy_mng = _MNG_BIG_INT_VALUE_;
		$usr->policy_sadmin = 1;
		$usr->policy_fin = 1;
	}
	$usr->cross_id = 0;	// preset value
	$usr->user_id = 0; // preset value
	@$vysledek2=MySQL_Query("SELECT * FROM ".TBL_USXUS." WHERE id_accounts = '$usr->account_id' LIMIT 1");
	if ($vysledek2)
	{
		$zaznam2=MySQL_Fetch_Array($vysledek2);
		if ($zaznam2)
		{
			$usr->cross_id=$zaznam2["id"];
			$usr->user_id=$zaznam2["id_users"];
		}
	}
	$_SESSION['usr'] = $usr;
	//--> set last visited
//	$currdate=getdate();
//	$sqldate= $currdate['year']."-".$currdate['mon']."-".$currdate['mday'];
	$sqldate= GetCurrentDate();
	$id=$zaznam["id"];
	MySQL_Query("UPDATE ".TBL_ACCOUNT." SET last_visit='$sqldate' WHERE id='$id'")
		or die("Chyba pøi provádìní dotazu do databáze.");
	//<--
	include ("log_browser.php");
}
if (!IsLogged())
	header("location: ".$g_baseadr);
else
{
	if (IsLoggedAdmin())
		header("location: ".$g_baseadr."index.php?id=300&subid=1");
	else
		header("location: ".$g_baseadr."index.php?id=4");
}
?>
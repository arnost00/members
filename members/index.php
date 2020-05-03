<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

$id = (IsSet($_GET['id']) && is_numeric($_GET['id'])) ? (int)$_GET['id'] : 0;
$subid = (IsSet($_GET['subid']) && is_numeric($_GET['subid'])) ? (int)$_GET['subid'] : 0;

require_once ("./timestamp.inc.php");
_set_global_RT_Start();
require_once('./cfg/_uc.php');
require_once("./cfg/_colors.php");
require_once("./cfg/_globals.php");
require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
require_once ("./common.inc.php");
require_once ("./ctable.inc.php");
define('IS_INDEX',true);
require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
require_once ("./version.inc.php");
db_Connect();

if (defined('GC_NOTHING_VISIBLE_WO_LOGIN') && !IsLogged())
{
DrawPageTitle('Přihlášení do přihláškového systému');
?>
<FORM METHOD=POST ACTION="./login.php">
<TABLE border="0" cellpadding="0" cellspacing="2">
<TR><TD class="login">Jméno&nbsp;</TD><TD><INPUT TYPE="text" NAME="<? echo(_VAR_USER_LOGIN);?>" SIZE=10 class="login"></TD></TR>
<TR><TD class="login">Heslo&nbsp;</TD><TD><INPUT TYPE="password" NAME="<? echo(_VAR_USER_PASS);?>" SIZE=10 class="login"></TD></TR>
<TR><TD colspan="2" height="4"></TD></TR>
<TR><TD></TD><TD><INPUT TYPE="submit" VALUE="Přihlásit"></TD></TR>
</TABLE>
</FORM>
<?
	HTML_Footer();
	exit;
}

?>
<TABLE width="100%" cellpadding="0" cellspacing="0" border="0">
<? 
if (IsLoggedAdmin())
{
	echo '<!-- sess.name : '.session_name().' -->'."\n";
	echo '<!-- sess.pars : ';
	print_r (session_get_cookie_params());
	echo ' -->'."\n";
}
?>
<TR>
<TD rowspan=2 width="180" bgcolor="<? echo $g_colors['body_bgcolor']; ?>" valign=top align=left>
<!-- navigace -->
<?require_once "./nav.inc.php"?>
<!-- navigace -->
<CENTER><span class="VersionText"><? echo GetCodeVersion(); ?></span></CENTER><BR>
</TD>
<TD rowspan=2 width="2%"></TD>
<TD width="90%" ALIGN="left" valign="top">
<?
if (!$g_is_release)
{	// pri debug zobrazit
	echo("<!-- beta-dev -->\n");
	echo('<div id="is_debug_info">');
	echo('!! neveřejný debug.build !!');
	echo('</div>');
	echo "<!-- beta-dev -->\n";
}

if($g_is_system_running || IsLoggedAdmin())
{
	switch ($id)
	{
		case 0:  // novinky
			require_once "./news.inc.php";
			break;
		case 1: //adresar
			require_once "./directory.inc.php";
			break;
		case 2: //terminovka
			if (!IsLogged())
				require_once './racelist.inc.php';
			else
				require_once "./news.inc.php";
			break;
		case 3: //prihlasky clena
			if (IsLogged())
				require_once './users_races.inc.php';
			else
				require_once "./news.inc.php";
			break;
		case 4: //aktualitky
			if (IsLogged() && !IsLoggedAdmin())
			{
				define('SHOW_USER',true);
				require_once "./us_news.inc.php";
			}
			else if (!IsLogged() || IsLoggedAdmin())
			{
				define('SHOW_USER',false);
				require_once "./us_news.inc.php";
			}
			else
				require_once "./news.inc.php";
			break;
		case 21:  // ...
//			require_once "./news.inc.php";
			break;
		case _USER_GROUP_ID_: //clenske podmenu
			if (IsLoggedUser())
			{
				switch($subid)
				{
					case 1:
						require_once "./us_setup.inc.php";
						break;
					case 2:
						require_once "./us_races.inc.php";
						break;
					case 3:
						require_once "./us_user_edit.inc.php";
						break;
					case 4:
						if ($g_enable_mailinfo)
							require_once "./us_mailinfo.inc.php";
						else
							require_once "./news.inc.php";
						break;
					case 10: //finance
						if ($g_enable_finances)
							require_once "./us_finance.inc.php";
						else
							require_once "./news.inc.php";
						break;
					default:
						require_once "./news.inc.php";
				}
			}
			else
				require_once "./news.inc.php";
			break;
		case _ADMIN_GROUP_ID_: //administratorske podmenu
			if ( IsLoggedAdmin() )
			{
				switch($subid)
				{
					case 1:
						require_once "./ad_main.inc.php";
						break;
					case 2:
						require_once "./rg_ad_races.inc.php";
						break;
					case 4:
						require_once "./ad_accview.inc.php";
						break;
					case 5:
						require_once "./rg_ad_races_edit.inc.php";
						break;
					case 6:
						require_once "./ad_modify_log.inc.php";
						break;
					case 7:
						require_once "./ad_fin_history.inc.php";
						break;
					case 8:
						require_once "./ad_mailinfo.inc.php";
						break;
					case 3:
					default:
						require_once "./news.inc.php";
				}
			}
			else
				require_once "./news.inc.php";
			break;
		case _SMALL_ADMIN_GROUP_ID_: // small-administratorske podmenu
			if ( IsLoggedSmallAdmin() )
			{
				switch($subid)
				{
					case 1:
						require_once "./ads_directory.inc.php";
						break;
					default:
						require_once "./news.inc.php";
				}
			}
			else
				require_once "./news.inc.php";
			break;
		case _REGISTRATOR_GROUP_ID_: // prihlasovaci clen - podmenu
			if ( IsLoggedRegistrator() )
			{
				switch($subid)
				{
					case 1:
						require_once "./rg_ad_races.inc.php";
						break;
					case 4:
						require_once "./rg_ad_races_edit.inc.php";
						break;
					default:
						require_once "./news.inc.php";
				}
			}
			else
				require_once "./news.inc.php";
			break;
		case _MANAGER_GROUP_ID_: // trener clen - podmenu
			if ( IsLoggedManager() )
			{
				switch($subid)
				{
					case 1:
						require_once "./mn_directory.inc.php";
						break;
					case 2:
						require_once "./mn_races.inc.php";
						break;
					case 3:
						require_once "./mn_groups.inc.php";
						break;
					case 4:
						require_once "./mn_smn_list.inc.php";
						break;
					case 10: //finance
						if ($g_enable_finances)
							require_once "./mn_finance.inc.php";
						else
							require_once "./news.inc.php";
						break;
					default:
						require_once "./news.inc.php";
				}
			}
			else
				require_once "./news.inc.php";
			break;
		case _SMALL_MANAGER_GROUP_ID_: // maly trener clen - podmenu
			if ( IsLoggedSmallManager() )
			{
				switch($subid)
				{
					case 1:
						require_once "./mns_directory.inc.php";
						break;
					case 2:
						require_once "./mns_races.inc.php";
						break;
					case 10: // finance
						if ($g_enable_finances)
							require_once "./mns_finance.inc.php";
						else
							require_once "./news.inc.php";
						break;
					default:
						require_once "./news.inc.php";
				}
			}
			else
				require_once "./news.inc.php";
			break;
		case _FINANCE_GROUP_ID_: // financnik - podmenu
			if ( IsLoggedFinance() && $g_enable_finances)
			{
				switch($subid)
				{
					case 1:
						require_once "fin_directory.inc.php";
						break;
					case 2:
						require_once "fin_races.inc.php";
						break;
					case 3:
						if ($g_enable_finances_claim) 
							require_once "fin_claims.inc.php";
						else
							require_once "./news.inc.php";
						break;
					case 4:
						require_once "fin_types.inc.php";
						break;
					default:
						require_once "./news.inc.php";
				}
			}
			else
				require_once "./news.inc.php";
			break;
		case 99: // ve vyvoji
			require_once "./develop.inc.php";
			break;
		default:
			require_once "./news.inc.php";
			break;
	}
}
else
{
	require_once './uc.inc.php';
}
?>
</TD>
<TD rowspan=2 width="2%"></TD>
</TR>
<TR><TD ALIGN=CENTER VALIGN=bottom height="15">
<hr>
</TD></TR>
<TR><TD COLSPAN=4 ALIGN=CENTER>
<!-- Footer Begin -->
<?require_once "./footer.inc.php"?>
<!-- Footer End -->
</TD></TR>
</TABLE>

<? 	if (!$g_is_release)
{	// pri debug zobrazit
echo '<HR>';
echo '<B>Debug Informations ::</B>'."<BR>\n";
echo '<U>Current User</U>'."<BR>\n";
echo 'Func: Admin : '.IsLoggedAdmin().', Reg : '.IsLoggedRegistrator().', Mng : '.IsLoggedManager().', SmallMng : '.IsLoggedSmallManager().', Editor : '.IsLoggedEditor().', User : '.IsLogged()."<BR>\n";
echo 'Vars: Admin : '.$usr->policy_admin.', Reg : '.$usr->policy_reg.', Mng : '.$usr->policy_mng.', Editor : '.$usr->policy_news.', User : '.$usr->logged."<BR>\n";
echo 'Logged : '.$usr->logged.', UserID : '.$usr->user_id.', AccountID : '.$usr->account_id.', CrossID : '.$usr->cross_id."<BR>\n";
echo '<U>System & Browser</U>'."<BR>\n";
echo 'PHP Session ID = '._CURR_SESS_ID_."<BR>\n";
echo 'WWW Browser = ['.(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '')."]<BR>\n";
echo 'URL opened = ['.$_SERVER['PHP_SELF']."]<BR>\n";
echo 'Referer URL  = ['.(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '')."]<BR>\n";
echo 'header = ["Last-Modified: '. gmdate("D, d M Y H:i:s") .' GMT"]'."<BR>\n";
echo '<HR>';
} ?>

<?
_set_global_RT_End();
if (!$g_is_release || IsLoggedAdmin())
{
	echo '<p align="right"><span class ="MiniHelpText">';
	_print_global_RT_difference_TS();
	echo "</span><BR>\n";
}

HTML_Footer();
?>
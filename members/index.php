<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

$id = (IsSet($_GET['id']) && is_numeric($_GET['id'])) ? (int)$_GET['id'] : 0;
$subid = (IsSet($_GET['subid']) && is_numeric($_GET['subid'])) ? (int)$_GET['subid'] : 0;

require ("./timestamp.inc.php");
_set_global_RT_Start();
require('./cfg/_uc.php');
require("./cfg/_colors.php");
require("./cfg/_globals.php");
require ("./connect.inc.php");
if (!$g_is_release)
	include ('debuglib.phps');
require ("./sess.inc.php");
require ("./common.inc.php");
require ("./ctable.inc.php");
define('IS_INDEX',true);
include ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
include ("./version.inc.php");
db_Connect();
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
<?include "./nav.inc.php"?>
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
	echo('!! neveøejný debug.build !!');
	echo('</div>');
	echo "<!-- beta-dev -->\n";
}

if($g_is_system_running || IsLoggedAdmin())
{
	switch ($id)
	{
		case 0:  // novinky
			include "./news.inc.php";
			break;
		case 1: //adresar
			include "./directory.inc.php";
			break;
		case 2: //terminovka
			if (!IsLogged())
				include './racelist.inc.php';
			else
				include "./news.inc.php";
			break;
		case 3: //prihlasky clena
			if (IsLogged())
				include './users_races.inc.php';
			else
				include "./news.inc.php";
			break;
		case 4: //aktualitky
			if (IsLogged() && !IsLoggedAdmin())
			{
				define('SHOW_USER',true);
				include "./us_news.inc.php";
			}
			else if (!IsLogged() || IsLoggedAdmin())
			{
				define('SHOW_USER',false);
				include "./us_news.inc.php";
			}
			else
				include "./news.inc.php";
			break;
		case 21:  // ...
//			include "./news.inc.php";
			break;
		case _USER_GROUP_ID_: //clenske podmenu
			if (IsLoggedUser())
			{
				switch($subid)
				{
					case 1:
						include "./us_setup.inc.php";
						break;
					case 2:
						include "./us_races.inc.php";
						break;
					case 3:
						include "./us_user_edit.inc.php";
						break;
					case 4:
						if ($g_enable_mailinfo)
							include "./us_mailinfo.inc.php";
						else
							include "./news.inc.php";
						break;
					case 10: //finance
						include "./us_finance.inc.php";
						break;
					default:
						include "./news.inc.php";
				}
			}
			else
				include "./news.inc.php";
			break;
		case _ADMIN_GROUP_ID_: //administratorske podmenu
			if ( IsLoggedAdmin() )
			{
				switch($subid)
				{
					case 1:
						include "./ad_main.inc.php";
						break;
					case 2:
						include "./rg_ad_races.inc.php";
						break;
					case 3:
						include "./ad_directory.inc.php";
						break;
					case 4:
						include "./ad_accview.inc.php";
						break;
					case 5:
						include "./rg_ad_races_edit.inc.php";
						break;
					case 6:
						include "./ad_modify_log.inc.php";
						break;
					default:
						include "./news.inc.php";
				}
			}
			else
				include "./news.inc.php";
			break;
		case _SMALL_ADMIN_GROUP_ID_: // small-administratorske podmenu
			if ( IsLoggedSmallAdmin() )
			{
				switch($subid)
				{
					case 1:
						include "./ads_lock.inc.php";
						break;
					case 2:
						include "./ads_hidden.inc.php";
						break;
					default:
						include "./news.inc.php";
				}
			}
			else
				include "./news.inc.php";
			break;
		case _REGISTRATOR_GROUP_ID_: // prihlasovaci clen - podmenu
			if ( IsLoggedRegistrator() )
			{
				switch($subid)
				{
					case 1:
						include "./rg_ad_races.inc.php";
						break;
					case 4:
						include "./rg_ad_races_edit.inc.php";
						break;
					default:
						include "./news.inc.php";
				}
			}
			else
				include "./news.inc.php";
			break;
		case _MANAGER_GROUP_ID_: // trener clen - podmenu
			if ( IsLoggedManager() )
			{
				switch($subid)
				{
					case 1:
						include "./mn_directory.inc.php";
						break;
					case 2:
						include "./mn_races.inc.php";
						break;
					case 3:
						include "./mn_groups.inc.php";
						break;
					case 4:
						include "./mn_smn_list.inc.php";
						break;
					case 10: //finance
						include "./mn_finance.inc.php";
						break;
					default:
						include "./news.inc.php";
				}
			}
			else
				include "./news.inc.php";
			break;
		case _SMALL_MANAGER_GROUP_ID_: // maly trener clen - podmenu
			if ( IsLoggedSmallManager() )
			{
				switch($subid)
				{
					case 1:
						include "./mns_directory.inc.php";
						break;
					case 2:
						include "./mns_races.inc.php";
						break;
					case 10: // finance
						include "./mns_finance.inc.php";
						break;
					default:
						include "./news.inc.php";
				}
			}
			else
				include "./news.inc.php";
			break;
		case 99: // ve vyvoji
			include "./develop.inc.php";
			break;
		default:
			include "./news.inc.php";
			break;
	}
}
else
{
	include './uc.inc.php';
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
<?include "./footer.inc.php"?>
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
if (!$g_is_release)
	show_vars(false,true,0);
?>
</BODY>
</HTML>
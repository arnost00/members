<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require_once("./cfg/_cfg.php");
require_once ("./sess.inc.php");

require_once "./header.inc.php"; // header obsahuje uvod html a konci <BODY>
DrawPageTitle('Chybový stav :');
?>
<TABLE width="80%" cellpadding="0" cellspacing="0" border="0">
<TR><TD width="20px" ROWSPAN="4">&nbsp;</TD><TD ALIGN=CENTER>
<H4>
<?
if (IsSet($code) && $code != 0)
{
	$errors_list = array ( // preddefinovany seznam chybovych hlasek
		11 => 'Nepodařilo se navázat spojení s databází.',
		12 => 'Chyba při komunikaci s databází.',
		21 => 'Do této oblasti nemáte přístupová práva, kontaktujte správce stránek.',
		31 => 'Nemáte přístupová práva pro psaní a mazání novinek, kontaktujte správce stránek.',
		32 => 'Je potřeba zadat nějaké údaje pro vytvoření novinky.',
		42 => 'Je potřeba zadat nějaké údaje pro vytvoření závodu.',
		52 => 'Je potřeba zadat nějaké údaje pro změnu upozorňování na email.',
		62 => 'Je potřeba zadat nějaké údaje pro vytvoření typu oddílového příspěvku.',
		72 => 'Je potřeba zadat nějaké údaje pro vytvoření předdefinovaných kategorií.',
		101 => 'Neexistující uživatel. Zadejte správné uživatelské jméno.',
		102 => 'Špatně zadané heslo! Zkuste zadat heslo znovu.',
		103 => 'Učet je zablokován! Pokud nevíte důvod, kontaktujte správce stránek.',
		201 => 'Nebyl nalezen požadovaný záznam.',
		202 => 'Nelze smazat sebe sama.',
		9999 => 'Neznámá chyba.'
	);
	$text = $errors_list[$code];
	if (strlen($text) == 0)
		$text = $errors_list[9999];
	echo $text;
	//--> log file
	{
		$ipa = getenv ('REMOTE_ADDR');
		$www = getenv ('HTTP_USER_AGENT');
		$hr = (IsSet($HTTP_REFERER)) ? $HTTP_REFERER : '?';
		$str = $ipa."\terr:".$code."\t".$www."\t".gethostbyaddr($ipa)."\tREF[".$hr."]";
		LogToFile(dirname(__FILE__) . '/logs/.errors.txt',$str);
	}
	//<-- log file
}
else
	header("location: ".$g_baseadr);
?>
</H4>
<?
 $reff = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
 if ($reff != '')
 {
	echo('<BR><A href="'.$reff.'">Zpět</A>');
 }
?>
<BR><A href="<? echo $g_baseadr?>">Zpět na úvodní stránku</A><BR><BR><BR>
<hr><BR>
</TD></TR>
<TR><TD ALIGN=CENTER>
<?require_once "./footer.inc.php"?>
</TD></TR>
</TABLE>

<? 	if (!$g_is_release)
{	// pri debug zobrazit
echo '<HR>';
echo '<B>Debug Informations ::</B>'."<BR>\n";
echo '<U>Current User</U>'."<BR>\n";
echo 'Func: Admin : '.IsLoggedAdmin().', Reg : '.IsLoggedRegistrator().', Mng : '.IsLoggedManager().', SmallMng : '.IsLoggedSmallManager().', Editor : '.IsLoggedEditor().', User : '.IsLogged()."<BR>\n";
echo 'Vars: Admin : '.$usr->policy_admin.', Reg : '.$usr->policy_reg.', Mng : '.$usr->policy_mng.', Editor : '.$usr->policy_news.', User : '.$usr->logged."<BR>\n";
echo 'Logged : '.$usr->logged.', UserID : '.$usr->user_id.', AccountID : '.$usr->account_id."<BR>\n";
echo '<U>System & Browser</U>'."<BR>\n";
echo 'PHP Session ID = '._CURR_SESS_ID_."<BR>\n";
echo 'WWW Browser = ['.(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '')."]<BR>\n";
echo 'URL opened = ['.$_SERVER['PHP_SELF']."]<BR>\n";
echo 'Referer URL  = ['.(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '')."]<BR>\n";
echo 'header = ["Last-Modified: '. gmdate("D, d M Y H:i:s") .' GMT"]'."<BR>\n";
echo '<HR>';
} 

HTML_Footer();
?>

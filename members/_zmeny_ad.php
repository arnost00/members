<?php 
// Date in the past 
header("Expires: " . gmdate("D, d M Y H:i:s") . " GMT"); 

// always modified 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 

// HTTP/1.1 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false); 

// HTTP/1.0 
header("Pragma: no-cache"); 
?>
<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?

require_once ('timestamp.inc.php');
_set_global_RT_Start();
require_once('cfg/_globals.php');
require_once ('connect.inc.php');
require_once ('./version.inc.php');
require_once ('common.inc.php');

HTML_Header($g_www_title,'view.css');
db_Connect();

?>
<center>
<hr>
<h1>Seznam závodů se změnami</h1>
<hr><br>
<?
require_once ('common_race.inc.php');
require_once ('url.inc.php');

$curr_date = GetCurrentDate();

$query="SELECT id,datum,typ,datum2,nazev,vicedenni,odkaz,oddil,misto,modify_flag,cancelled FROM ".TBL_RACE.' WHERE datum >= '.$curr_date.' ORDER BY datum, datum2, id';

@$vysledek=query_db($query);

if (mysqli_num_rows($vysledek) > 0)
{
	echo('<table>'."\n");
	echo('<tr>');
	echo('<th>Datum</th>');
	echo('<th>Název</th>');
	echo('<th>Poř.</th>');
	echo('<th>W</th>');
	echo('<th>Změny</th>');
	echo('</tr>'."\n");
		while ($zaznam=mysqli_fetch_array($vysledek))
	{
		$termin_class = 'center';
		$termin2_class = 'center';
		echo('<tr>');
		if($zaznam['vicedenni'])
			$datum=Date2StringFT($zaznam['datum'],$zaznam['datum2']);
		else
			$datum=Date2String($zaznam['datum']);

		$nazev = GetFormatedTextDel($zaznam['nazev'], $zaznam['cancelled']);
		$oddil = $zaznam['oddil'];
		$odkaz = GetRaceLinkHTML($zaznam['odkaz']);
		echo('<td class="center">'.$datum.'</td>');
		echo('<td>'.$nazev.'</td>');
		echo('<td class="center">'.$oddil.'</td>');
		echo('<td class="center">'.$odkaz.'</td>');
		echo('<td class="center">'.GetModifyFlagDesc($zaznam['modify_flag']).'</td>');
		echo('</tr>'."\n");
	}
	echo('</table>'."\n");
}
else
{
	echo('Není žádný závod.');
}
?>
<br>
<hr>
<?
_set_global_RT_End();
echo('<p id="footer_time">');
_print_global_RT_difference_TS();
echo("</p><br>\n");
?>
</center>
<?
HTML_Footer();
?>
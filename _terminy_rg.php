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
require_once ('common_rg_race.inc.php');

HTML_Header($g_www_title,'view.css');

db_Connect();

?>
<center>
<hr>
<h1>Přehled přihlášek pro přihlašovatele</h1>
<hr><br>
<?
require_once ('common_race.inc.php');
require_once ('url.inc.php');

$curr_date = GetCurrentDate();
//$curr_date = mktime (0,0,0,6,1,2010);

$d1 = $curr_date;

$query="SELECT id,datum,typ,datum2,prihlasky,prihlasky1,prihlasky2,prihlasky3,prihlasky4,prihlasky5,nazev,vicedenni,odkaz,vedouci, oddil,send,misto,cancelled,typ0 FROM ".TBL_RACE.' WHERE datum >= '.$d1.' || datum2 >= '.$d1.' ORDER BY datum, datum2, id';

@$vysledek=mysqli_query($db_conn, $query);

if (mysqli_num_rows($vysledek) > 0)
{
	echo('<table>'."\n");
	echo('<tr>');
	echo('<th rowspan=2>Datum</th>');
	echo('<th rowspan=2>Název</th>');
	echo('<th rowspan=2>Poř.</th>');
	echo('<th rowspan=2>T</th>');
	echo('<th rowspan=2>S</th>');
	echo('<th rowspan=2>W</th>');
	echo('<th colspan=2>Termín přihlášek</th>');
	echo('<th rowspan=2>OP</th>');
	echo('<th rowspan=2>Pomocný seznam termínů</th>');
	echo('</tr>'."\n");
	echo('<tr>');
	echo('<th>byl</th>');
	echo('<th>bude</th>');
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

		//----------------------------
		if($zaznam['prihlasky'] > 0 && $zaznam['prihlasky1'] != 0)
		{
			$termin = _Reg2Str(_GetOldReg($zaznam,$curr_date));
			$termin2 = _Reg2Str(_GetNewReg($zaznam,$curr_date));
			$termin_class = _GetOldRegClass($zaznam,$curr_date);
			$termin2_class = _GetNewRegClass($zaznam,$curr_date);
		}
		else
		{
			$termin_class = 'center_gray';
			$termin2_class = 'center_gray';
			$termin = '?';
			$termin2 = '?';
		}
		//----------------------------

		if($zaznam['send'] > 0)
		{
			if($zaznam['prihlasky'] > 1)
				$send = $zaznam['send'].'.t.';
			else
				$send = 'Ano';
		}
		else
			$send = 'Ne';
		$nazev = GetFormatedTextDel($zaznam['nazev'], $zaznam['cancelled']);
		$oddil = $zaznam['oddil'];
		$odkaz = GetRaceLinkHTML($zaznam['odkaz']);
		echo('<td class="center">'.$datum.'</td>');
		echo('<td>'.$nazev.'</td>');
		echo('<td class="center">'.$oddil.'</td>');
		echo('<td class="center">'.GetRaceType0($zaznam['typ0']).'</td>');
		echo('<td class="center">'.GetRaceTypeImg($zaznam['typ']).'</td>');
		echo('<td class="center">'.$odkaz.'</td>');
		echo('<td class="'.$termin_class.'">'.$termin.'</td>');
		echo('<td class="'.$termin2_class.'">'.$termin2.'</td>');
		echo('<td class="center">'.$send.'</td>');
		echo('<td class="termlist">'.Term_list($zaznam).'</td>');
		echo('</tr>'."\n");
	}
	echo('</table>'."\n");
}
else
{
	echo('V nejbližších '.GC_SHOW_REG_DAYS.' dnech není žádná přihláška na závod.');
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
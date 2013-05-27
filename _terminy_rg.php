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

require ('timestamp.inc.php');
_set_global_RT_Start();
require('cfg/_globals.php');
require ('connect.inc.php');
require ('common.inc.php');
require ('common_rg_race.inc.php')

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1250">
	<meta http-equiv="Content-language" content="cs">
</head>
<style type="text/css">

body,html {
	font-family : Verdana, sans-serif;
	font-size : 12px;
	color: #000;
	background-color : #fff;
}
table {
	font-family : Verdana, sans-serif;
	font-size : 12px;
	color: #000;
	background-color : #fff;
	border-collapse: collapse;
}

table tr:hover {
	background: #eff;
}

table td {
	border:1px solid #999;
	padding: 1pt 4pt;
	margin: 0px;
/*	background-color cannot be set, blocks tr:hover */
}

table td.center {
	text-align: center;
}

table td.center_gray {
	text-align: center;
	color : #999;
}

table td.center_alert2 {
	text-align: center;
	background-color : #fcc;
}

table td.center_alert7 {
	text-align: center;
	background-color : #ffc;
}

table td.center_alert21 {
	text-align: center;
	background-color : #cfc;
}

table th {
	background-color : #eee;
	border:1px solid #999;
	padding: 1pt 4pt;
	margin: 0px;
}

table td.termlist {
	color: #999;
}

hr {
	width : 100%;
	height : 1px;
	border: 0;
	border-top:1px dotted #ccc;
}

#footer_time {
	font-family : Verdana, sans-serif;
	font-size: 9px;
	color: #ccc;
	text-align: right;
}

</style>
<body>
<?
db_Connect();

?>
<center>
<hr>
<h1>Přehled přihlášek pro přihlašovatele</h1>
<hr><br>
<?
include ('common_race.inc.php');
include ('url.inc.php');

$curr_date = GetCurrentDate();
//$curr_date = mktime (0,0,0,6,1,2010);

$d1 = $curr_date;

$query="SELECT id,datum,typ,datum2,prihlasky,prihlasky1,prihlasky2,prihlasky3,prihlasky4,prihlasky5,nazev,vicedenni,odkaz,vedouci, oddil,send,misto FROM ".TBL_RACE.' WHERE datum >= '.$d1.' || datum2 >= '.$d1.' ORDER BY datum, datum2, id';

@$vysledek=MySQL_Query($query);

if (mysql_num_rows($vysledek) > 0)
{
	echo('<table>'."\n");
	echo('<tr>');
	echo('<th rowspan=2>Datum</th>');
	echo('<th rowspan=2>Název</th>');
	echo('<th rowspan=2>Poř.</th>');
	echo('<th rowspan=2>W</th>');
	echo('<th colspan=2>Termín přihlášek</th>');
	echo('<th rowspan=2>OP</th>');
	echo('<th rowspan=2>Pomocný seznam termínů</th>');
	echo('</tr>'."\n");
	echo('<tr>');
	echo('<th>byl</th>');
	echo('<th>bude</th>');
	echo('</tr>'."\n");
		while ($zaznam=MySQL_Fetch_Array($vysledek))
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
		$nazev = $zaznam['nazev'];
		$oddil = $zaznam['oddil'];
		$odkaz = GetRaceLinkHTML($zaznam['odkaz']);
		echo('<td class="center">'.$datum.'</td>');
		echo('<td>'.$nazev.'</td>');
		echo('<td class="center">'.$oddil.'</td>');
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
</body>
</html>

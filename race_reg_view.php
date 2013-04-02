<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
@extract($_REQUEST);

require("./cfg/_colors.php");
require ("./connect.inc.php");
require ("./sess.inc.php");

require ("./ctable.inc.php");
include ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
include ("./common.inc.php");
include ("./common_race.inc.php");
include ("./common_user.inc.php");
include ('./url.inc.php');

$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;
$us = (int)((IsSet($us) && is_numeric($us)) ? (($us > 0) ? 1 : 0) : 0);
$gr_id = (IsSet($gr_id) && is_numeric($gr_id)) ? (int)$gr_id : 0;
$select = (int)((IsSet($select) && is_numeric($select)) ? (($select > 0) ? 1 : 0) : 0);

DrawPageTitle('Seznam závodníkù pøihlášených na závod');

db_Connect();

$query = 'SELECT u.*, z.kat, z.pozn, z.pozn_in, z.termin, z.si_chip as t_si_chip, z.id_user FROM '.TBL_ZAVXUS.' as z, '.TBL_USER.' as u WHERE z.id_user = u.id AND z.id_zavod='.$id.' ORDER BY z.termin ASC, z.id ASC';

@$vysledek=MySQL_Query($query);

@$vysledek_z=MySQL_Query('SELECT * FROM '.TBL_RACE." WHERE `id`='$id' LIMIT 1");
$zaznam_z = MySQL_Fetch_Array($vysledek_z);


DrawPageSubTitle('Vybraný závod');

RaceInfoTable($zaznam_z,'',$gr_id != _REGISTRATOR_GROUP_ID_,false,true);
?>
<TABLE class= "Zav" cellpadding="0" cellspacing="2" border="0">
<BR>
<BUTTON onclick="javascript:close_popup();">Zavøi</BUTTON>
<BR><BR><hr><BR>
<?
DrawPageSubTitle('Pøihlášení závodníci');

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Poø.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Pøíjmení',ALIGN_LEFT);
if ($us == 0)
{
	$data_tbl->set_header_col_with_help($col++,'Reg.è.',ALIGN_CENTER,"Registraèní èíslo");
	$data_tbl->set_header_col($col++,'SI èip',ALIGN_RIGHT);
}
$data_tbl->set_header_col($col++,'Kategorie',ALIGN_CENTER);
if($zaznam_z['prihlasky'] > 1)
	$data_tbl->set_header_col($col++,'Termín',ALIGN_CENTER);
if (IsLogged())
{
	$data_tbl->set_header_col($col++,'Pozn.',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Pozn.(i)',ALIGN_LEFT);
}
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$i=0;
while ($zaznam=MySQL_Fetch_Array($vysledek))
{
	if(($select == 0 || $zaznam['chief_id'] == $usr->user_id || $zaznam['id_user'] == $usr->user_id) && $zaznam['hidden'] == 0)
	{
		$i++;

		$row = array();
		$row[] = $i.'<!-- '.$zaznam['id'].' -->';
		$row[] = $zaznam['jmeno'];
		$row[] = $zaznam['prijmeni'];
		if ($us == 0)
		{
			$row[] = $g_shortcut.RegNumToStr($zaznam['reg']);
			if ($zaznam['si_chip'] == 0)
				$row[] = (($zaznam['t_si_chip'] != 0) ? '<span class="TemporaryChip">'.SINumToStr($zaznam['t_si_chip']).'</span>' : '');
			else
				$row[] = (($zaznam['t_si_chip'] != 0) ? '<span class="TemporaryChip">'.SINumToStr($zaznam['t_si_chip']).'</span>' : SINumToStr($zaznam['si_chip']));
		}
		$row[] = '<B>'.$zaznam['kat'].'</B>';
		if($zaznam_z['prihlasky'] > 1)
			$row[] = $zaznam['termin'];
		if(IsLogged())
		{
			$row[] = $zaznam['pozn'];
			$row[] = $zaznam['pozn_in'];
		}
		echo $data_tbl->get_new_row_arr($row)."\n";
	}
}
echo $data_tbl->get_footer()."\n";
?>

<BR>

</body>
</html>
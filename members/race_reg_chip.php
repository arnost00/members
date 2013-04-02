<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
@extract($_REQUEST);

require("./cfg/_colors.php");
require ("./connect.inc.php");
require ("./sess.inc.php");

if (!IsLoggedRegistrator())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
require ("./ctable.inc.php");
include ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
include ("./common.inc.php");
include ("./common_race.inc.php");
include ("./common_user.inc.php");
include ('./url.inc.php');

DrawPageTitle('Pøiøazení SI èipù pro závod');

db_Connect();

$id_zav = (isset($id_zav) && is_numeric($id_zav)) ? (int)$id_zav : 0;

//$query = 'SELECT u.*, z.kat, z.pozn, z.pozn_in, z.si_chip as t_si_chip FROM '.TBL_ZAVXUS.' as z, '.TBL_USER.' as u WHERE z.id_user = u.id AND z.id_zavod='.$id_zav.' AND u.si_chip = 0 AND u.hidden = 0 ORDER BY z.id ASC';
$query = 'SELECT u.*, z.kat, z.pozn, z.pozn_in, z.si_chip as t_si_chip FROM '.TBL_ZAVXUS.' as z, '.TBL_USER.' as u WHERE z.id_user = u.id AND z.id_zavod='.$id_zav.' AND u.hidden = 0 ORDER BY z.id ASC';

@$vysledek=MySQL_Query($query);

@$vysledek_z=MySQL_Query("SELECT * FROM ".TBL_RACE." WHERE id=$id_zav LIMIT 1");
$zaznam_z = MySQL_Fetch_Array($vysledek_z);

DrawPageSubTitle('Vybraný závod');

RaceInfoTable($zaznam_z,'',false,false,true);
?>

<BR><BR><hr><BR>
<?
DrawPageSubTitle('Pøihlášení závodníci bez trvalých SI èipù');

if (mysql_num_rows($vysledek) > 0)
{
?>
<FORM METHOD="POST" ACTION="race_reg_chip_exc.php?id_zav=<? echo($id_zav); ?>">

<?
	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Poø.',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Pøíjmení',ALIGN_LEFT);
	$data_tbl->set_header_col_with_help($col++,'Reg.',ALIGN_CENTER,"Registraèní èíslo");
	$data_tbl->set_header_col($col++,'SI èip',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Kategorie',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Poznámka',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Poznámka (interní)',ALIGN_CENTER);

	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";
	echo $data_tbl->get_header_row()."\n";

	$i=0;
	while ($zaznam=MySQL_Fetch_Array($vysledek))
	{
		$i++;

		$row = array();
		$row[] = $i.'<!-- '.$zaznam['id'].' -->';
		$row[] = $zaznam['jmeno'];
		$row[] = $zaznam['prijmeni'];
		$row[] = $g_shortcut.RegNumToStr($zaznam['reg']);
		if ($zaznam['si_chip'] != 0)
		{
			$si = ($zaznam['t_si_chip'] != 0) ? $zaznam['t_si_chip'] : $zaznam['si_chip'];
			$row[] = '<input type="text" name="chip['.$zaznam['id'].']" SIZE=9 MAXLENGTH=9 value="'.$si.'"> ('.$zaznam['si_chip'].')';
		}
		else
			$row[] = '<input type="text" name="chip['.$zaznam['id'].']" SIZE=9 MAXLENGTH=9 value="'.$zaznam['t_si_chip'].'">';
		
		$row[] = '<B>'.$zaznam['kat'].'</B>';
		$row[] = $zaznam['pozn'];
		$row[] = $zaznam['pozn_in'];

		echo $data_tbl->get_new_row_arr($row)."\n";
	}
	echo $data_tbl->get_footer()."\n";
?>
<br>
<INPUT TYPE="submit" value='Zapsat èipy pro závod'>
</FORM>
<?
}
else
{
	echo('Nejsou pøihlášení žádní závodníci.<br>');
}
?>
<BR>
<BUTTON onclick="javascript:close_popup();">Zavøi</BUTTON>

</body>
</html>

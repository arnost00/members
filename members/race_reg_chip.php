<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
@extract($_REQUEST);

require_once("./cfg/_colors.php");
require_once ("./connect.inc.php");
require_once ("./sess.inc.php");

if (!IsLoggedRegistrator())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
require_once ("./ctable.inc.php");
require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
require_once ("./common.inc.php");
require_once ("./common_race.inc.php");
require_once ("./common_user.inc.php");
require_once ('./url.inc.php');

DrawPageTitle('Přiřazení SI čipů pro závod');

db_Connect();

$id_zav = (isset($id_zav) && is_numeric($id_zav)) ? (int)$id_zav : 0;

//$query = 'SELECT u.*, z.kat, z.pozn, z.pozn_in, z.si_chip as t_si_chip FROM '.TBL_ZAVXUS.' as z, '.TBL_USER.' as u WHERE z.id_user = u.id AND z.id_zavod='.$id_zav.' AND u.si_chip = 0 AND u.hidden = 0 ORDER BY z.id ASC';
$query = 'SELECT u.*, z.kat, z.pozn, z.pozn_in, z.si_chip as t_si_chip FROM '.TBL_ZAVXUS.' as z, '.TBL_USER.' as u WHERE z.id_user = u.id AND z.id_zavod='.$id_zav.' AND u.hidden = 0 ORDER BY z.id ASC';

@$vysledek=query_db($query);

@$vysledek_z=query_db("SELECT * FROM ".TBL_RACE." WHERE id=$id_zav LIMIT 1");
$zaznam_z = mysqli_fetch_array($vysledek_z);

DrawPageSubTitle('Vybraný závod');

RaceInfoTable($zaznam_z,'',false,false,true);
?>

<BR><BR><hr><BR>
<?
DrawPageSubTitle('Přihlášení závodníci bez trvalých SI čipů');

if (mysqli_num_rows($vysledek) > 0)
{
?>
<FORM METHOD="POST" ACTION="race_reg_chip_exc.php?id_zav=<? echo($id_zav); ?>">

<?
	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Poř.',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Příjmení',ALIGN_LEFT);
	$data_tbl->set_header_col_with_help($col++,'Reg.',ALIGN_CENTER,"Registrační číslo");
	$data_tbl->set_header_col($col++,'SI čip',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Kategorie',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Poznámka',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Poznámka (interní)',ALIGN_CENTER);

	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";
	echo $data_tbl->get_header_row()."\n";

	$i=0;
	while ($zaznam=mysqli_fetch_array($vysledek))
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
<INPUT TYPE="submit" value='Zapsat čipy pro závod'>
</FORM>
<?
}
else
{
	echo('Nejsou přihlášení žádní závodníci.<br>');
}
?>
<BR>
<BUTTON onclick="javascript:close_popup();">Zavři</BUTTON>

<?
HTML_Footer();
?>
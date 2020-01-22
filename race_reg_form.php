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

DrawPageTitle('Export přihlášky - kontrola');

db_Connect();

$id_zav = (isset($id_zav) && is_numeric($id_zav)) ? (int)$id_zav : 0;
//------------------------------
$regsend = (isset($regsend) && is_numeric($regsend)) ? (int)$regsend : -1;
if($regsend >= 0 && $regsend <= 5)
{
	$regsendnow = (isset($regsendnow) && is_numeric($regsendnow)) ? (int)$regsendnow : 0;
	if($regsendnow > 0)
	{	// save new regsend...
		$result=query_db("UPDATE ".TBL_RACE." SET `send`='$regsend' WHERE `id`='$id_zav'")
				or die('Chyba při provádění dotazu do databáze.');
		if ($result == FALSE)
			die ('Nepodařilo se změnit údaje o závodě.');
	}
}
else	// kontrola rozsahu
	$regsend = -1;
//------------------------------

@$vysledek_z=query_db("SELECT * FROM ".TBL_RACE." WHERE id=$id_zav LIMIT 1");
$zaznam_z = mysqli_fetch_array($vysledek_z);

$regsend = $zaznam_z['send'];

$kat_arr = array();
function prepare_kats()
{
	global $kat_arr;
	global $zaznam_z;
	$kat_arr = explode(';',$zaznam_z['kategorie']);
}

prepare_kats();

function check_kat($kat)
{
	global $kat_arr;
	$result = in_array($kat,$kat_arr);
	
	return $result;
}

?>
<SCRIPT LANGUAGE="JavaScript">
//<!--

function submit_form(termin)
{
	document.form_exp_reg.termin.value=termin;
	document.form_exp_reg.creg.value=0;
	document.form_exp_reg.submit();
	return true;
}

function submit_form_reg(reg_type)
{
	document.form_exp_reg.termin.value=0;
	document.form_exp_reg.creg.value=reg_type;
	document.form_exp_reg.submit();
	return true;
}

//-->
</SCRIPT>
<?
DrawPageSubTitle('Vybraný závod');

RaceInfoTable($zaznam_z,'',false,false,true);
?>

<BR>
<FORM METHOD="GET" ACTION="race_reg_form_exc.php" name="form_exp_reg" target="_blank">
<input type="hidden" name="id_zav" value="<? echo($id_zav); ?>">
Způsob výpisu:<input type="radio" name="ff" value="0" id="radio_ff0" checked="checked"><label for="radio_ff0">Export přihlášky</label>&nbsp;&nbsp;
<input type="radio" name="ff" value="1" id="radio_ff1"><label for="radio_ff1">Náhled na přihlášku</label>
<input type="hidden" name="termin" value="0">
<input type="hidden" name="creg" value="0">
<br><br>
<? if($zaznam_z['prihlasky'] > 1)
{ ?>
<BUTTON onclick="submit_form(0);  return false;">Proveď - všechny termíny</BUTTON>
<br>
<?
	for($ii=1; $ii<=$zaznam_z['prihlasky']; $ii++)
	{
		echo"<BUTTON onclick=\"submit_form(".$ii."); return false;\">Proveď - ".$ii.". termín</BUTTON>&nbsp;";
	}
?>
<br>
<? } else { ?>
<BUTTON onclick="submit_form(0); return false;">Proveď akci</BUTTON>
<? } ?>
<BUTTON onclick="submit_form_reg(1); return false;">Výpis pro centrální registraci (ObHana)</BUTTON>
<?
	if ($g_enable_oris_support)
	{
?>
<BUTTON onclick="submit_form_reg(2); return false;">Výpis pro centrální registraci (ORIS)</BUTTON>
<?
	}
?>
</FORM>
<BUTTON onclick="javascript:close_popup();">Zavři</BUTTON>
<?//------------------------------?>
<br><br>
<FORM METHOD="POST" ACTION="race_reg_form.php?id_zav=<? echo($id_zav); ?>">
<input type="hidden" name="regsendnow" value="1">
Stav odeslání přihlášky&nbsp;&nbsp;<select name="regsend" size="1">
	<option value="0"<? if($regsend ==0) echo(' selected="selected"'); ?>>není odeslána</option>
<?
	if($zaznam_z['prihlasky'] > 1)
	{
		for($ii=1; $ii<=$zaznam_z['prihlasky']; $ii++)
		{
			echo'<option value="'.$ii.'"'.(($regsend == $ii) ? ' selected="selected" ' : '').'>je odeslána pro '.$ii.'. termín</option>';
		}
	}
	else
	{
?>
	<option value="1"<? if($regsend ==1) echo(' selected="selected"'); ?>>je odeslána</option>
<?
	}
?>
</select>
<INPUT TYPE="submit" value='Nastav stav odeslání přihlášky'>
</FORM>
<?//------------------------------?>
<BR><BR><hr><BR>

<?
DrawPageSubTitle('Přihlášení závodníci');

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Poř.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Příjmení',ALIGN_LEFT);
$data_tbl->set_header_col_with_help($col++,'Reg.',ALIGN_CENTER,"Registrační číslo");
$data_tbl->set_header_col($col++,'SI čip',ALIGN_RIGHT);
$data_tbl->set_header_col($col++,'Kategorie',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Kontrola',ALIGN_CENTER);
if($zaznam_z['prihlasky'] > 1)
{
	$data_tbl->set_header_col($col++,'Termín',ALIGN_CENTER);
}
$data_tbl->set_header_col($col++,'Poznámka',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Poznámka (interní)',ALIGN_CENTER);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$query = 'SELECT u.jmeno, u.prijmeni, u.reg, u.si_chip, z.kat, z.pozn, z.pozn_in, z.termin, z.si_chip as t_si_chip FROM '.TBL_ZAVXUS.' as z, '.TBL_USER.' as u WHERE z.id_user = u.id AND z.id_zavod='.$id_zav.' AND u.hidden = 0 ORDER BY z.termin ASC, z.id ASC';

@$vysledek=query_db($query);

$i=0;
$err_cnt = 0;
$old_term = 1;

while ($zaznam=mysqli_fetch_array($vysledek))
{
	$i++;

	$row = array();
	$row[] = $i;
	$row[] = $zaznam['jmeno'];
	$row[] = $zaznam['prijmeni'];
	$row[] = $g_shortcut.RegNumToStr($zaznam['reg']);
	if ($zaznam['si_chip'] == 0)
		$row[] = (($zaznam['t_si_chip'] != 0) ? '<span class="TemporaryChip">'.SINumToStr($zaznam['t_si_chip']).'</span>' : '');
	else
		$row[] = (($zaznam['t_si_chip'] != 0) ? '<span class="TemporaryChip">'.SINumToStr($zaznam['t_si_chip']).'</span>' : SINumToStr($zaznam['si_chip']));
	$row[] = '<B>'.$zaznam['kat'].'</B>';
	if (check_kat($zaznam['kat']))
		$kres = '<span class="TextCheckOk">OK';
	else
	{
		$kres = '<span class="TextCheckBad">Chyba*';
		$err_cnt++;
	}
	$kres .= '</span>';
	$row[] = $kres;
	if($zaznam_z['prihlasky'] > 1)
		$row[] = $zaznam['termin'];
	$row[] = $zaznam['pozn'];
	$row[] = $zaznam['pozn_in'];

	if($zaznam_z['prihlasky'] > 1 && $old_term != $zaznam['termin'])
	{
		$old_term = $zaznam['termin'];
		echo $data_tbl->get_break_row()."\n";
	}

	echo $data_tbl->get_new_row_arr($row)."\n";
}
echo $data_tbl->get_footer()."\n";

if($err_cnt > 0)
{
?>
<span class="TextCheckBad">* Kategorie v přihlášce závodníka není definovaná jako platná kategorie pro tento závod.</span><br>
<?
	echo('Celkový počet chyb v přihlášce je '.$err_cnt.'.<br>');
	// spusteni opravy vysledku ...
}

if(strlen($zaznam_z['poznamka']) > 0)
{
?>
<p><b>Doplňující informace o závodě (interní)</b> :<br>
<?
	echo('&nbsp;&nbsp;&nbsp;'.$zaznam_z['poznamka'].'</p>');
}

?>
<BR>

<?
HTML_Footer();
?>
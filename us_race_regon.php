<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
@extract($_REQUEST);

require('./cfg/_colors.php');
require('./connect.inc.php');
require('./sess.inc.php');

if (!IsLogged())
{
	header('location: '.$g_baseadr.'error.php?code=21');
	exit;
}
require('./ctable.inc.php');
include('./header.inc.php'); // header obsahuje uvod html a konci <BODY>
include('./common.inc.php');
include('./common_race.inc.php');
include('./url.inc.php');

db_Connect();


$id_zav = (IsSet($id_zav) && is_numeric($id_zav)) ? (int)$id_zav : 0;
$id_us = (IsSet($id_us) && is_numeric($id_us)) ? (int)$id_us : 0;

DrawPageTitle('Pøihláška na závod', false);

@$vysledek=MySQL_Query("SELECT * FROM ".TBL_ZAVXUS." WHERE id_zavod=$id_zav ORDER BY id");

@$vysledek_z=MySQL_Query("SELECT * FROM ".TBL_RACE." WHERE id=$id_zav");
$zaznam_z = MySQL_Fetch_Array($vysledek_z);

@$vysledek_rg=MySQL_Query("SELECT * FROM ".TBL_ZAVXUS." WHERE id_zavod=$id_zav and id_user=$id_us");
$zaznam_rg=MySQL_Fetch_Array($vysledek_rg);

$new = ($zaznam_rg && $zaznam_rg['kat'] != '') ? 0 : 1;

?>

<SCRIPT LANGUAGE="JavaScript">
<!--
function zmen_kat(kateg)
{
	document.form1.kat.value=kateg;
}

function check_reg(vstup)
{
	if (vstup.kat.value == "")
	{
		alert("Musíš zadat kategorii pro pøihlášení do závodu.");
		return false;
	}
	else
		return true;
}

function submit_off()
{
	if (confirm('Opravdu se chcete odhlásit?'))
	{
		window.location = 'us_race_regoff_exc.php?id_zav=<? echo($id_zav) ?>&id_us=<? echo($id_us) ?>';
	}
	return false;
}

//-->
</SCRIPT>

<?
DrawPageSubTitle('Vybraný závod');

if (!$new)
{
	$add_r[0] ='Kategorie';
	$add_r[1] ='<B>'.$zaznam_rg['kat'].'</B>';
	RaceInfoTable($zaznam_z,$add_r,true);
}
else
	RaceInfoTable($zaznam_z,'',true);
?>
<BR>
<BUTTON onclick="javascript:close_popup();">Zpìt</BUTTON>
<BR><BR>
<hr><BR>

<FORM METHOD=POST ACTION="us_race_regon_exc.php" name="form1" onsubmit="return check_reg(this);">

<? DrawPageSubTitle('Výbìr kategorie'); ?>

Do které kategorie chcete pøihlásit:&nbsp;
<?
if ($g_user_race_reg_type == GURRT_COMBOBOX)
{

	echo '<SELECT name="kat" size=1>';

	echo '<option value=""';
	if ($zaznam_rg['kat'] == '')
		echo ' selected ';
	echo '>- - -</option>';

	$kategorie=explode(';',$zaznam_z['kategorie']);
	for ($i=0; $i<count($kategorie)-1; $i++)
	{
		echo '<option value="'.$kategorie[$i].'"';
		if (!$new && $kategorie[$i] == $zaznam_rg['kat'])
			echo ' selected ';
		echo '>'.$kategorie[$i].'</option>';
	}
	echo '</SELECT>';

}
else
{
	echo'<br>';
	$kategorie=explode(';',$zaznam_z['kategorie']);
	for ($i=0; $i<count($kategorie)-1; $i++)
	{
		echo "<button onclick=\"javascript:zmen_kat('".$kategorie[$i]."');return false;\">".$kategorie[$i]."</button>";
	}

	echo'<BR><BR>Vybraná kategorie:&nbsp;<INPUT TYPE="text" NAME="kat" size=4 value="'.$zaznam_rg['kat'].'"><BR>';
}
?>

<BR><BR>
Poznámka&nbsp;<INPUT TYPE="text" name="pozn" size="50" maxlength="250" value="<?echo $zaznam_rg['pozn']?>">&nbsp;(do&nbsp;pøihlášky)
<BR><BR>
Poznámka&nbsp;<INPUT TYPE="text" name="pozn2" size="50" maxlength="250" value="<?echo $zaznam_rg['pozn_in']?>">&nbsp;(interní)
<BR><BR>

<INPUT TYPE="hidden" name="id_us" value="<?echo $id_us?>">
<INPUT TYPE="hidden" name="id_zav" value="<?echo $id_zav?>">
<INPUT TYPE="hidden" name="novy" value="<?echo $new?>">
<INPUT TYPE="hidden" name="id_z" value="<?echo $zaznam_rg['id']?>">

<?
if ($new)
{
?>
<INPUT TYPE="submit" value="Pøihlásit na závod">
<?
}
else
{
?>
<INPUT TYPE="submit" value="Zmìnit kategorii">
&nbsp;&nbsp;&nbsp;&nbsp;<BUTTON onclick="return submit_off();">Odhlásit za závodu</BUTTON>
<?
}
?>
</FORM>
<?
if(strlen($zaznam_z['poznamka']) > 0)
{
?>
<p><b>Doplòující informace o závodì (interní)</b> :<br>
<?
	echo('&nbsp;&nbsp;&nbsp;'.$zaznam_z['poznamka'].'</p>');
}
?>

<BR><hr><BR>
<?
DrawPageSubTitle('Již pøihlášení závodníci');

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Poø.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Pøíjmení',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Kategorie',ALIGN_CENTER);
if($zaznam_z['prihlasky'] > 1)
	$data_tbl->set_header_col($col++,'Termín',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Pozn.',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Pozn.(i)',ALIGN_LEFT);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$i=0;
while ($zaznam=MySQL_Fetch_Array($vysledek))
{
	@$vysledek1=MySQL_Query("SELECT * FROM ".TBL_USER." WHERE id=$zaznam[id_user] LIMIT 1");
	$zaznam1=MySQL_Fetch_Array($vysledek1);
	$i++;

	$row = array();
	$row[] = $i.'<!-- '.$zaznam['id'].' -->';
	$row[] = $zaznam1['jmeno'];
	$row[] = $zaznam1['prijmeni'];
	$row[] = '<B>'.$zaznam['kat'].'</B>';
	if($zaznam_z['prihlasky'] > 1)
		$row[] = $zaznam['termin'];
	$row[] = $zaznam['pozn'];
	$row[] = $zaznam['pozn_in'];
	echo $data_tbl->get_new_row_arr($row)."\n";
}
echo $data_tbl->get_footer()."\n";
?>

<BR>

</body>
</html>
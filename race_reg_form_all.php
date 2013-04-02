<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
@extract($_REQUEST);

require("./cfg/_colors.php");
require("./cfg/_globals.php");
require ("./connect.inc.php");
require ("./sess.inc.php");
	include ('debuglib.phps');

if (!IsLogged())
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

DrawPageTitle('Vytvoøení a export pøihlášky');

db_Connect();

$gen = (IsSet($gen)&& is_numeric($gen)) ? (($gen >= 0 && $gen <= 1) ? (int)$gen : 0) : 0;
$rt = (IsSet($rt)&& is_numeric($rt)) ? (($rt >= 0 && $rt <= 2) ? (int)$rt : 0) : 0;

if (!isset($kateg) || !is_array($kateg))
	$kateg = array();
if (!isset($pozn) || !is_array($pozn))
	$pozn = array();
if (!isset($chip) || !is_array($chip))
	$chip = array();
	
if ($gen == 1)
{
DrawPageSubTitle('Vygenerovaná pøihláška');

$rows = 0;
$entry = '';

include ('exports.inc.php');

$query = 'SELECT * FROM '.TBL_USER.' WHERE '.TBL_USER.'.hidden = 0 ORDER BY reg';
@$vysledek=MySQL_Query($query);

$entry = new CSOB_Export_Entry($g_shortcut);

while ($zaznam=MySQL_Fetch_Array($vysledek))
{
	// function get_entry_line_CSOB($prijmeni, $jmeno, $reg, $lic, $kat, $si, $pozn)
	$u=$zaznam['id'];
	if ($kateg[$u] != '')
	{
		$rows++;
		$licence = GetLicence($zaznam['lic'],$zaznam['lic_mtbo'],$zaznam['lic_lob'],$rt);
		$entry->add_line($zaznam['prijmeni'], $zaznam['jmeno'], $zaznam['reg'],$licence,$kateg[$u],$chip[$u],$pozn[$u]);
	}
	
}

if ($rows < 5) $rows = 5;
?>
<TEXTAREA name="generated_entry" cols="80" rows="<? echo($rows); ?>" wrap=virtual>
<? echo($entry->generate());?>
</TEXTAREA>
<BR><BR><hr>
<?
}
?>

<?
DrawPageSubTitle('Seznam èlenù');
?>
<FORM METHOD="POST" ACTION="race_reg_form_all.php" name="form_reg_all">
<?

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Reg.è.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Pøíjmení',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Vìk',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'SI èip',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Kategorie',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Poznámka',ALIGN_CENTER);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$query = 'SELECT * FROM '.TBL_USER.' WHERE '.TBL_USER.'.hidden = 0 ORDER BY reg';
@$vysledek=MySQL_Query($query);

$i=0;
while ($zaznam=MySQL_Fetch_Array($vysledek))
{
	$row = array();
	$row[] = RegNumToStr($zaznam['reg']);
	$row[] = $zaznam['prijmeni'];
	$row[] = $zaznam['jmeno'];
	$age = CountManAge($zaznam['datum']);
	$row[] = ($age != -1) ? (($age < GC_SHOW_AGE_LIMIT)? $age :'') : '?';
	$u=$zaznam['id'];
	if (!isset($kateg[$u]))
		$kateg[$u] = '';
	if (!isset($pozn[$u]))
		$pozn[$u] = '';
	$row[] = '<input type="text" name="chip['.$u.']" SIZE=9 MAXLENGTH=9 value="'.$zaznam['si_chip'].'" onfocus="javascript:select_row('.$u.');">';
	$row[] = '<INPUT TYPE="text" NAME="kateg['.$u.']" SIZE=5 value="'.$kateg[$u].'" onfocus="javascript:select_row('.$u.');">';
	$row[] = '<INPUT TYPE="text" NAME="pozn['.$u.']" size="25" maxlength="250" value="'.$pozn[$u].'" onfocus="javascript:select_row('.$u.');">';
	
	if ($zaznam['id'] == $usr->user_id) 
		$data_tbl->set_next_row_highlighted();
	echo $data_tbl->get_new_row_arr($row)."\n";
}
echo $data_tbl->get_footer()."\n";
?>
<BR>
<input type="hidden" name="gen" value="1">
Typ závodu:<input type="radio" name="rt" value="0" id="radio_rt0"<? echo(($rt == 0) ? ' checked="checked"':''); ?>><label for="radio_rt0">OB</label>&nbsp;&nbsp;
<input type="radio" name="rt" value="1" id="radio_rt1"<? echo(($rt == 1) ? ' checked="checked"':''); ?>><label for="radio_rt1">LOB</label>&nbsp;&nbsp;
<input type="radio" name="rt" value="2" id="radio_rt2"<? echo(($rt == 2) ? ' checked="checked"':''); ?>><label for="radio_rt2">MTBO</label><i> ... pro správné licence</i>

<br><br>
<INPUT TYPE="submit" VALUE="Proveï akci">
</FORM>
<hr><BR>
</body>
</html>

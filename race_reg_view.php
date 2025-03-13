<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
@extract($_REQUEST);

require_once("./cfg/_colors.php");
require_once ("./connect.inc.php");
require_once ("./sess.inc.php");

require_once ("./ctable.inc.php");
require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
require_once ("./common.inc.php");
require_once ("./common_race.inc.php");
require_once ("./common_user.inc.php");
require_once ('./url.inc.php');

$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;
$us = (int)((IsSet($us) && is_numeric($us)) ? (($us > 0) ? 1 : 0) : 0);
$gr_id = (IsSet($gr_id) && is_numeric($gr_id)) ? (int)$gr_id : 0;
$select = (int)((IsSet($select) && is_numeric($select)) ? (($select > 0) ? 1 : 0) : 0);

DrawPageTitle('Seznam závodníků přihlášených na závod');

db_Connect();

$query = 'SELECT u.*, z.kat, z.pozn, z.pozn_in, z.termin, z.si_chip as t_si_chip, z.id_user, z.transport transport, z.sedadel, z.ubytovani ubytovani FROM '.TBL_ZAVXUS.' as z, '.TBL_USER.' as u WHERE z.id_user = u.id AND z.id_zavod='.$id.' ORDER BY z.termin ASC, z.id ASC';

@$vysledek=query_db($query);

@$vysledek_z=query_db('SELECT * FROM '.TBL_RACE." WHERE `id`='$id' LIMIT 1");
$zaznam_z = mysqli_fetch_array($vysledek_z);


DrawPageSubTitle('Vybraný závod');

RaceInfoTable($zaznam_z,'',$gr_id != _REGISTRATOR_GROUP_ID_,false,true);
?>
<TABLE class= "Zav" cellpadding="0" cellspacing="2" border="0">
<BR>
<BUTTON onclick="javascript:close_popup();">Zavři</BUTTON>
<BR><BR><hr><BR>
<?
DrawPageSubTitle('Přihlášení závodníci');

$is_spol_dopr_on = ($zaznam_z["transport"]==1) && $g_enable_race_transport;
$is_sdil_dopr_on = ($zaznam_z["transport"]==3) && $g_enable_race_transport;
$is_spol_ubyt_on = ($zaznam_z["ubytovani"]==1) && $g_enable_race_accommodation;

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Poř.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Příjmení',ALIGN_LEFT);
if ($us == 0)
{
	$data_tbl->set_header_col_with_help($col++,'Reg.č.',ALIGN_CENTER,"Registrační číslo");
	$data_tbl->set_header_col($col++,'SI čip',ALIGN_RIGHT);
}
// Create category header with a clickable link
$kat_header = '<span style="cursor:pointer; text-decoration:underline;" onclick="toggleCategoriesAndScroll()" title="Zobrazí počet účastníků v jednotlivých kategoriích">Kategorie</span>';
$data_tbl->set_header_col($col++,$kat_header,ALIGN_CENTER);
if($is_spol_dopr_on||$is_sdil_dopr_on)
	$data_tbl->set_header_col_with_help($col++,'SD',ALIGN_CENTER,($is_spol_dopr_on?'Společná':'Sdílená').' doprava');
if($is_sdil_dopr_on)
	$data_tbl->set_header_col_with_help($col++,'&#x1F697;',ALIGN_CENTER,'Nabízených sedadel');
if($is_spol_ubyt_on)
	$data_tbl->set_header_col_with_help($col++,'SU',ALIGN_CENTER,'Společné ubytování');
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
$trans=0;
$sedadel=0;
$ubyt=0;
$category_counts = [];
while ($zaznam=mysqli_fetch_array($vysledek))
{
	if(($select == 0 || $zaznam['chief_id'] == $usr->user_id || $zaznam['id_user'] == $usr->user_id) && $zaznam['hidden'] == 0)
	{
		$i++;

		// Count category occurrences
		$kat = $zaznam['kat'];
		if (!isset($category_counts[$kat])) {
			$category_counts[$kat] = 0;
		}
		$category_counts[$kat]++;

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
		if($is_spol_dopr_on||$is_sdil_dopr_on)
		{
			if ($zaznam["transport"])
			{
				$row[] = '<B>&#x2714;</B>';
				$trans++;
			}
			else
				$row[] = '';
		}
		if($is_sdil_dopr_on)
			$row[] = GetSharedTransportValue($zaznam["transport"], $zaznam["sedadel"], $sedadel );
		if($is_spol_ubyt_on)
		{
			if ($zaznam["ubytovani"])
			{
				$row[] = '<B>&#x2714;</B>';
				$ubyt++;
			}
			else
				$row[] = '';
		}
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
if ($select == 0)
{	// SD pouze pro vypis vsech prihlasek
echo $is_spol_dopr_on||$is_sdil_dopr_on ? "<BR>Počet přihlášených na dopravu: $trans" : "";
$warning_text = $sedadel < 0 ? ' <font color="red">(málo volných míst)</font>' : '';
echo $is_sdil_dopr_on ? "<BR>Počet volných sdílených míst: $sedadel".$warning_text : "";
echo $is_spol_ubyt_on ? "<BR>Počet přihlášených na ubytování: $ubyt" : "";
// Add collapsible section for category counts with table formatting
echo '<br><br><div id="category_details" style="display:none;">';
echo '<table cellspacing="2">';
echo '<tr><th>Kategorie</th>';

foreach ($category_counts as $category => $count) {
    echo "<td>$category</td>";
}
echo '</tr><tr><th>Počet</th>';

foreach ($category_counts as $category => $count) {
    echo "<td style='text-align:center;'>$count</td>";
}

echo "</tr></table></div>";

// JavaScript to expand list and scroll
echo '<script>
function toggleCategoriesAndScroll() {
    var details = document.getElementById("category_details");
    details.style.display = "block";
    
    // Scroll to the category list
    details.scrollIntoView({ behavior: "smooth", block: "start" });
}
</script>';
?>

<BR>

<?
HTML_Footer();
?>

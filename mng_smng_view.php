<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
@extract($_REQUEST);

require_once("./cfg/_colors.php");
require_once ("./connect.inc.php");
require_once ("./sess.inc.php");

if (!IsLoggedManager())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
require_once ("./ctable.inc.php");
require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
require_once ("./common.inc.php");
require_once ("./common_user.inc.php");

DrawPageTitle('Zobrazení členů přidělených malému trenéru');

DrawPageSubTitle('Trenér');

$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

db_Connect();

$query = "SELECT jmeno,prijmeni,reg FROM ".TBL_USER." WHERE id = $id LIMIT 1";
@$vysledek0=query_db($query);
@$zaznam0=mysqli_fetch_array($vysledek0);

$data_tbl = new html_table_nfo;
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_new_row('Jméno',$zaznam0['jmeno'].' '.$zaznam0['prijmeni']);
echo $data_tbl->get_new_row('Registrační číslo',$g_shortcut.RegNumToStr($zaznam0['reg']));
echo $data_tbl->get_footer()."\n";

DrawPageSubTitle('Přiřazení členové');

$i=1;
$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Poř.č.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Příjmení',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$data_tbl->set_header_col_with_help($col++,'Reg.č.',ALIGN_CENTER,"Registrační číslo");
$data_tbl->set_header_col_with_help($col++,'L.OB',ALIGN_CENTER,"Licence pro OB");
$data_tbl->set_header_col_with_help($col++,'L.MTBO',ALIGN_CENTER,"Licence pro MTBO");
$data_tbl->set_header_col_with_help($col++,'L.LOB',ALIGN_CENTER,"Licence pro LOB");

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$query = 'SELECT id,prijmeni,jmeno,reg,hidden,lic,lic_mtbo,lic_lob FROM '.TBL_USER.' WHERE chief_id = '.$id.' ORDER BY sort_name ASC ';
@$vysledek=query_db($query);

while ($zaznam=mysqli_fetch_array($vysledek))
{
	if (!$zaznam['hidden'])
	{
		$row = array();
		$row[] = $i++;
		$row[] = $zaznam['prijmeni'];
		$row[] = $zaznam['jmeno'];
		$row[] = RegNumToStr($zaznam['reg']);
		$row[] = ($zaznam['lic'] != 'C' || $zaznam['lic'] != '-') ? '<B>'.$zaznam['lic'].'</B>' : $zaznam['lic'];
		$row[] = ($zaznam['lic_mtbo'] != 'C' || $zaznam['lic_mtbo'] != '-') ? '<B>'.$zaznam['lic_mtbo'].'</B>' : $zaznam['lic_mtbo'];
		$row[] = ($zaznam['lic_lob'] != 'C' || $zaznam['lic_lob'] != '-') ? '<B>'.$zaznam['lic_lob'].'</B>' : $zaznam['lic_lob'];
		echo $data_tbl->get_new_row_arr($row)."\n";
	}
}
echo $data_tbl->get_footer()."\n";

?>
<BR>
<BUTTON onclick="javascript:close_popup();">Zpět</BUTTON>
</TD></TR></TABLE>
<?
HTML_Footer();
?>
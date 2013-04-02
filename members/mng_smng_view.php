<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
@extract($_REQUEST);

require("./cfg/_colors.php");
require ("./connect.inc.php");
require ("./sess.inc.php");

if (!IsLoggedManager())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
require ("./ctable.inc.php");
include ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
include ("./common.inc.php");
include ("./common_user.inc.php");

DrawPageTitle('Zobrazení èlenù pøidìlených malému trenéru');

DrawPageSubTitle('Trenér');

$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

db_Connect();

@$vysledek0=MySQL_Query("SELECT jmeno,prijmeni,reg FROM ".TBL_USER." WHERE id = $id LIMIT 1");
@$zaznam0=MySQL_Fetch_Array($vysledek0);

$data_tbl = new html_table_nfo;
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_new_row('Jméno',$zaznam0['jmeno'].' '.$zaznam0['prijmeni']);
echo $data_tbl->get_new_row('Registraèní èíslo',$g_shortcut.RegNumToStr($zaznam0['reg']));
echo $data_tbl->get_footer()."\n";

DrawPageSubTitle('Pøiøazení èlenové');

$i=1;
$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Poø.è.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Pøíjmení',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$data_tbl->set_header_col_with_help($col++,'Reg.è.',ALIGN_CENTER,"Registraèní èíslo");
$data_tbl->set_header_col_with_help($col++,'L.OB',ALIGN_CENTER,"Licence pro OB");
$data_tbl->set_header_col_with_help($col++,'L.MTBO',ALIGN_CENTER,"Licence pro MTBO");
$data_tbl->set_header_col_with_help($col++,'L.LOB',ALIGN_CENTER,"Licence pro LOB");

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

@$vysledek=MySQL_Query('SELECT id,prijmeni,jmeno,reg,hidden,lic,lic_mtbo,lic_lob FROM '.TBL_USER.' WHERE chief_id = '.$id.' ORDER BY sort_name ASC ');

while ($zaznam=MySQL_Fetch_Array($vysledek))
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
<BUTTON onclick="javascript:close_popup();">Zpìt</BUTTON>
</TD></TR></TABLE>

</body>
</html>
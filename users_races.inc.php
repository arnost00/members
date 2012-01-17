<?php /* adminova stranka - editace clenu oddilu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Pøihlášky èlenù oddílu', false);
?>
<CENTER>
<script language="javascript">
<!-- 

	javascript:set_default_size(700,600);
//-->
</script>

<?
include "./common_user.inc.php";

@$vysledek=MySQL_Query("SELECT id,prijmeni,jmeno,reg,si_chip,hidden FROM ".TBL_USER." ORDER BY sort_name ASC");

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Poø.è.',ALIGN_CENTER,40);
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT,150);
$data_tbl->set_header_col($col++,'Reg.è.',ALIGN_CENTER,50);
$data_tbl->set_header_col($col++,'SI èip',ALIGN_CENTER,50);
$data_tbl->set_header_col($col++,'Pøihlášky',ALIGN_CENTER,80);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$i=1;
while ($zaznam=MySQL_Fetch_Array($vysledek))
{
	if ($zaznam["hidden"] == 0)
	{
		$row = array();
		$row[] = $i++;
		$row[] = $zaznam['prijmeni'].' '.$zaznam['jmeno'];
		$row[] = RegNumToStr($zaznam['reg']);
		$row[] = ($zaznam['si_chip'] != 0) ? SINumToStr($zaznam['si_chip']) : '';
		$row[] = "<A HREF=\"javascript:open_win('./user_view.php?id=".$zaznam['id']."','')\">Zobrazit</A>";
		echo $data_tbl->get_new_row_arr($row)."\n";
	}
}

echo $data_tbl->get_footer()."\n";

?>
<BR>
</CENTER>
<?php /* trenerova stranka - prirazeni clenu trenerum */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Pøiøazení èlenù malým trenérùm');
?>
<CENTER>

<script language="javascript">
<!-- 
	/*	"status=yes,width=600,height=460"	*/

	javascript:set_default_size(600,520);
//-->
</script>

<?
include "./common_user.inc.php";

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Poø.è.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Pøíjmení',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$data_tbl->set_header_col_with_help($col++,'Reg.è.',ALIGN_CENTER,"Registraèní èíslo");
$data_tbl->set_header_col($col++,'Trenér',ALIGN_CENTER);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

// u.id, u.prijmeni, u.jmeno, u.reg, u.hidden, u.lic, u2.jmeno as ch_jmeno, u2.prijmeni as ch_prijmeni
// u.*

$query='SELECT u.*, u2.jmeno as ch_jmeno, u2.prijmeni as ch_prijmeni, u2.hidden as ch_hidden FROM '.TBL_USER.' as u LEFT JOIN '.TBL_USER.' as u2 ON u.chief_id = u2.id ORDER BY sort_name ASC';
@$vysledek=MySQL_Query($query);

$i=1;
while ($zaznam=MySQL_Fetch_Array($vysledek))
{
	if (!$zaznam['hidden'])
	{
		$row = array();
		$row[] = $i++;
		$row[] = $zaznam['prijmeni'];
		$row[] = $zaznam['jmeno'];
		$row[] = RegNumToStr($zaznam['reg']);
		if ($zaznam['ch_hidden'] || $zaznam['ch_jmeno'] == NULL || $zaznam['ch_prijmeni'] == NULL)
			$row[] = "<A HREF=\"javascript:open_win('./mng_edit.php?id=".$zaznam['id']."','')\">Edit</A>";
		else
		{
			$row[] = $zaznam['ch_jmeno'].' '.$zaznam['ch_prijmeni'].'&nbsp;/&nbsp;'. "<A HREF=\"javascript:open_win('./mng_edit.php?id=".$zaznam['id']."','')\">Edit</A>";
		}
		echo $data_tbl->get_new_row_arr($row)."\n";
	}

}

echo $data_tbl->get_footer()."\n";

?>
<BR>
</CENTER>
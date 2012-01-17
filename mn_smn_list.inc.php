<?php /* trenerova stranka - editace clenu oddilu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Pøehled malých trenérù', false);
?>
<CENTER>

<script language="JavaScript">
<!--
	/*	"status=yes,width=600,height=460"	*/

	javascript:set_default_size(600,600);
//-->
</script>

<?
include "./common_user.inc.php";

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Poø.è.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Pøíjmení',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Reg.è.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Poèet',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

@$vysl=MySQL_Query('SELECT u.id, u.prijmeni, u.jmeno, u.reg, u.hidden FROM '.TBL_ACCOUNT.', '.TBL_USER.' as u, '.TBL_USXUS.' WHERE '.TBL_ACCOUNT.'.id = '.TBL_USXUS.'.id_accounts AND '.TBL_USXUS.'.id_users = u.id AND '.TBL_ACCOUNT.'.policy_mng = '._MNG_SMALL_INT_VALUE_);

$i=1;
while ($zazn=MySQL_Fetch_Array($vysl))
{
	if(!$zazn['hidden'])
	{
		$row = array();
		$row[] = $i++;
		$row[] = $zazn['prijmeni'];
		$row[] = $zazn['jmeno'];
		$row[] = RegNumToStr($zazn['reg']);
		$vysl3 = MySQL_Query('SELECT COUNT(*) AS `mbr_cnt` FROM '.TBL_USER.' WHERE chief_id = '.$zazn['id']);
		$zazn3 = MySQL_Fetch_Array($vysl3);
		$num = ($zazn3 != FALSE) ? $zazn3['mbr_cnt'] : 0;
		$row[] = $num;
		$row[] = ($num > 0) ? "<A HREF=\"javascript:open_win('./mng_smng_view.php?id=".$zazn['id']."','')\">Zobraz</A>" : '';
		echo $data_tbl->get_new_row_arr($row)."\n";
	}
}

echo $data_tbl->get_footer()."\n";

?>
<BR>
</CENTER>
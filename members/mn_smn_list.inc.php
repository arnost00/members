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

//vytazeni malych treneru a poctu jejich sverencu
//ifnull je tam kvuli malym trenerum, kteri nemaji zadne sverence
//posledni subselect vytahne id_accounts pouze malych treneru
$select = "select u.id, u.jmeno, u.prijmeni, u.reg, ifnull(ucn.cnt,0) `mbr_cnt`, u.hidden from ".TBL_USER." u left join
(select count(1) cnt, chief_id cid from ".TBL_USER." group by chief_id) as ucn on u.id = ucn.cid
where u.id in
(select u.id from ".TBL_ACCOUNT." a inner join ".TBL_USXUS." uu on a.id = uu.id_accounts inner join ".TBL_USER." u on uu.id_users = u.id
where a.policy_mng = "._MNG_SMALL_INT_VALUE_.") order by u.sort_name";

@$vysl=MySQL_Query($select);
$i=1;
while ($zazn=MySQL_Fetch_Array($vysl))
{
	if ($zazn != FALSE && !$zazn['hidden'])
	{
		$row = array();
		$row[] = $i++;
		$row[] = $zazn['prijmeni'];
		$row[] = $zazn['jmeno'];
		$row[] = RegNumToStr($zazn['reg']);
		$num = $zazn['mbr_cnt'];
		$row[] = $num;
		$row[] = ($num > 0) ? "<A HREF=\"javascript:open_win('./mng_smng_view.php?id=".$zazn['id']."','')\">Zobraz</A>" : '';
		echo $data_tbl->get_new_row_arr($row)."\n";
	}
}

echo $data_tbl->get_footer()."\n";

?>
<BR>
</CENTER>
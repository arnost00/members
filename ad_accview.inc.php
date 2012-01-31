<?php /* adminova stranka - editace clenu oddilu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Náhled na úèty - Administrace',false);
?>
<CENTER>
<?
// specialni vypis user z accounts -->

@$list_v=MySQL_Query("SELECT id,login,podpis,policy_news,policy_regs,policy_mng,policy_fin,locked,last_visit FROM ".TBL_ACCOUNT." ORDER BY id");

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'id',ALIGN_CENTER,20);
$data_tbl->set_header_col($col++,'login',ALIGN_LEFT,100);
$data_tbl->set_header_col($col++,'news',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'reg',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'mng',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'fin',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'podpis',ALIGN_LEFT,100);
$data_tbl->set_header_col($col++,'locked',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'last visit',ALIGN_CENTER,60);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

while ($list_z=MySQL_Fetch_Array($list_v))
{
	echo $data_tbl->get_new_row($list_z["id"], $list_z["login"], (($list_z["policy_news"]) ? "A" : "-"), (($list_z["policy_regs"]) ? "A" : "-"), (($list_z["policy_mng"]) ? (( $list_z["policy_mng"] == _MNG_BIG_INT_VALUE_) ? "A" : "m") : "-"), (($list_z["policy_fin"]) ? "A" : "-"), $list_z["podpis"], (($list_z["locked"]) ? "A" : "-"), Date2String($list_z["last_visit"]))."\n";
}
echo $data_tbl->get_footer()."\n";
?>
<BR>
</CENTER>
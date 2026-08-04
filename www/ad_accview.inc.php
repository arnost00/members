<?php /* adminova stranka - editace clenu oddilu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Náhled na účty - Administrace');
?>
<CENTER>
<?
// specialni vypis user z accounts -->

$query = "SELECT a.id,login,podpis,policy_news,policy_regs,policy_mng,policy_fin,locked,last_visit, policy_adm, m.id mid, m.active_news, m.active_tf, m.active_ch, m.active_rg, m.active_fin FROM ".TBL_ACCOUNT." a left join ".TBL_MAILINFO." m on m.id_user = a.id_users ORDER BY id";
@$list_v = query_db($query);

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'id',ALIGN_CENTER,20);
$data_tbl->set_header_col($col++,'login',ALIGN_LEFT,100);
$data_tbl->set_header_col($col++,'policies',ALIGN_CENTER,100);
$data_tbl->set_header_col($col++,'signature',ALIGN_LEFT,100);
$data_tbl->set_header_col($col++,'locked',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'last visit',ALIGN_CENTER,60);
if ($g_enable_mailinfo)
{
	$data_tbl->set_header_col_with_help($col++,'ei',ALIGN_CENTER,'Email info',40);
	$data_tbl->set_header_col($col++,'ei - settings',ALIGN_CENTER,120);
}

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

while ($list_z=mysqli_fetch_array($list_v))
{
	$acc_r = '<code>';
	$acc_r .= ($list_z['policy_news'] == 1) ? 'N ' : '. ';
	$acc_r .= ($list_z['policy_regs'] == 1) ? 'P ' : '. ';
	$acc_r .= ($list_z['policy_mng'] == _MNG_BIG_INT_VALUE_) ? 'T ' : '. ';
	$acc_r .= ($list_z['policy_mng'] == _MNG_SMALL_INT_VALUE_) ? 't ' : '. ';
	$acc_r .= ($list_z['policy_adm'] == 1) ? 'S ' : '. ';
	$acc_r .= ($list_z['policy_fin'] == 1) ? 'F' : '.';
	$acc_r .= '</code>';
	
	if ($g_enable_mailinfo)
	{
		if ($list_z["mid"] == null)
		{
			$ei = 'Ne';
			$email_info = '<code>. . . .';
			if ($g_enable_finances)
				$email_info .= ' . .';
			$email_info .= '</code>';
		}
		else
		{
			$ei = 'Ano';
			$email_info = '<code>';
			$email_info .= ($list_z['active_news'])? 'N' : '.';
			$email_info .= ($list_z['active_tf'])? ' t' : ' .';
			$email_info .= ($list_z['active_ch'])? ' Z' : ' .';
			$email_info .= ($list_z['active_rg'])? ' T' : ' .';
			if ($g_enable_finances)
			{
				$email_info .= ($list_z['active_fin'])? ' f' : ' .';
				$email_info .= ($list_z['active_fin'])? ' F' : ' .';
			}
			$email_info .= '</code>';
		}
		echo $data_tbl->get_new_row($list_z["id"], $list_z["login"], $acc_r, $list_z["podpis"], (($list_z["locked"]) ? "A" : "-"), Date2String($list_z["last_visit"]),$ei,$email_info)."\n";
	}
	else
		echo $data_tbl->get_new_row($list_z["id"], $list_z["login"], $acc_r, $list_z["podpis"], (($list_z["locked"]) ? "A" : "-"), Date2String($list_z["last_visit"]))."\n";
}
echo $data_tbl->get_footer()."\n";
?>
<BR>
</CENTER>
<?php /* adminova stranka - editace clenu oddilu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageSubTitle('Email info / přehled');
?>
<CENTER>
<script language="JavaScript">
<!--
function confirm_delete(text) {
	return confirm('Opravdu chcete smazat tento řádek ? \n Email : "'+text+'"');
}
-->
</script>
<?


@$vysl=MySQL_Query('SELECT * FROM '.TBL_MAILINFO.' ORDER BY `id`')
	or die("Chyba při provádění dotazu do databáze.");

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'id',ALIGN_CENTER,40);
	$data_tbl->set_header_col($col++,'email',ALIGN_CENTER,120);
$data_tbl->set_header_col($col++,'jmeno',ALIGN_LEFT,140);
$data_tbl->set_header_col($col++,'hidden',ALIGN_CENTER,80);
$data_tbl->set_header_col($col++,'settings',ALIGN_CENTER,100);
$data_tbl->set_header_col($col++,'editace',ALIGN_CENTER,160);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

while ($zaznam=MySQL_Fetch_Array($vysl))
{
	$row = array();
	
	$err = false;
	$row[] = $zaznam['id'];
	$row[] = $zaznam['email'];
	$vysl2=MySQL_Query('SELECT * FROM '.TBL_USER.' WHERE id = \''.$zaznam['id_user'].'\' LIMIT 1');
	$zazn=MySQL_Fetch_Array($vysl2);
	if ($zazn != FALSE)
	{
		$err = $zazn['hidden'];
		$row[] = $zazn['prijmeni'].' '.$zazn['jmeno'];
		$row[] = ($err) ? '<span class="WarningText">skryt</span>' : '-';
	}
	else
	{
		$row[] = '<span class="WarningText">neni uzivatel</span>';
		$row[] = '-';
		$err = true;
	}
	$email_info = '<code>';
	$email_info .= ($zaznam['active_news'])? 'N' : '.';
	$email_info .= ($zaznam['active_tf'])? ' t' : ' .';
	$email_info .= ($zaznam['active_ch'])? ' Z' : ' .';
	$email_info .= ($zaznam['active_rg'])? ' T' : ' .';
	if ($g_enable_finances)
	{
		$email_info .= ($zaznam['active_fin'])? ' f' : ' .';
		$email_info .= ($zaznam['active_fin'])? ' F' : ' .';
	}
	$email_info .= '</code>';
	$row[] = $email_info;
	$row[] = ($err) ? '<A HREF="./ad_mailinfo_del_exc.php?id='.$zaznam['id'].'" onclick="return confirm_delete(\''.$zaznam['email'].'\')" class="Erase">Smazat</A></span>' : '-';
	
	echo $data_tbl->get_new_row_arr($row)."\n";
}
echo $data_tbl->get_footer()."\n";
?>
<BR>
</CENTER>
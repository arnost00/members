<?php /* adminova stranka - editace clenu oddilu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Skrytí èlenù',false);
?>
<CENTER>

<script language="JavaScript">
<!--
function confirm_hide(name) {
	return confirm('Opravdu chcete skrýt èlena oddílu ? \n Jméno èlena : "'+name+'"\nÈlen nebude viditelný v pøihláškách!');
}

function confirm_show(name) {
	return confirm('Opravdu chcete zviditelnit èlena oddílu ? \n Jméno èlena : "'+name+'"');
}

-->
</script>

<?
include "./common_user.inc.php";

@$vysledek=MySQL_Query("SELECT id,prijmeni,jmeno,reg,hidden FROM ".TBL_USER." ORDER BY sort_name ASC");

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Poø.è.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Pøíjmení',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Reg.è.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Skryt',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$i=1;
while ($zaznam=MySQL_Fetch_Array($vysledek))
{
//	if ($zaznam['hidden'] == 0) 
	$row = array();
	$row[] = $i++;
	$row[] = $zaznam['prijmeni'];
	$row[] = $zaznam['jmeno'];
	$row[] = RegNumToStr($zaznam['reg']);
	$val=GetUserAccountId_Users($zaznam['id']);
	if($zaznam['hidden'] == 0)
	{
		$hidd = 'Ne';
		$action = '<A HREF="./user_hide_exc.php?id='.$zaznam['id'].'" onclick="return confirm_hide(\''.$zaznam['jmeno'].' '.$zaznam['prijmeni'].'\')">Skrýt</A>';
	}
	else
	{
		$hidd = 'Ano';
		$action = '<A HREF="./user_hide_exc.php?id='.$zaznam['id'].'" onclick="return confirm_show(\''.$zaznam['jmeno'].' '.$zaznam['prijmeni'].'\')">Zviditelnit</A>';
	}
	$row[] = $hidd;
	$row[] = $action;
	echo $data_tbl->get_new_row_arr($row)."\n";
}
echo $data_tbl->get_footer()."\n";

?>
Upozornìní: Èlen skrytím neztratí možnost pøihlásit se do systému.<br>
<BR>
</CENTER>
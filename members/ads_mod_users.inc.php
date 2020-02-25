<?php /* adminova stranka - editace clenu oddilu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Skrytí členů a zamykání účtů');
?>
<CENTER>

<script language="JavaScript">
<!--
function confirm_hide(name) {
	return confirm('Opravdu chcete skrýt člena oddílu ? \n Jméno člena : "'+name+'"\nČlen nebude viditelný v přihláškách!');
}

function confirm_show(name) {
	return confirm('Opravdu chcete zviditelnit člena oddílu ? \n Jméno člena : "'+name+'"');
}

function confirm_lock(name) {
	return confirm('Opravdu chcete zamknout účet člena oddílu ? \n Jméno člena : "'+name+'" \n Člen nebude mít možnost se přihlásit do systému!');
}

function confirm_unlock(name) {
	return confirm('Opravdu chcete odemknout účet člena oddílu ? \n Jméno člena : "'+name+'"');
}

-->
</script>

<?
require_once "./common_user.inc.php";

$query = "SELECT u.id,prijmeni,jmeno,reg,hidden,locked,a.id aid FROM ".TBL_USER." u"
	." left join ".TBL_ACCOUNT." a on a.id_users = u.id ORDER BY sort_name ASC";
@$vysledek=query_db($query);

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Poř.č.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Příjmení',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$data_tbl->set_header_col_with_help($col++,'Reg.č.',ALIGN_CENTER,"Registrační číslo");
$data_tbl->set_header_col_with_help($col++,'Účet',ALIGN_CENTER,"Informace o existenci účtu");
$data_tbl->set_header_col_with_help($col++,'Skryt',ALIGN_CENTER,"Informace zda je uživatel skrytý");
$data_tbl->set_header_col_with_help($col++,'Zamčen',ALIGN_CENTER,"Informace o zamknutí účty uživateli");
$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$i=1;
while ($zaznam=mysqli_fetch_array($vysledek))
{
	$row = array();
	$row[] = $i++;
	$row[] = $zaznam['prijmeni'];
	$row[] = $zaznam['jmeno'];
	$row[] = RegNumToStr($zaznam['reg']);
	$action = '';
	if($zaznam['hidden'] == 0)
	{
		$hidd = 'Ne';
		$action = '<A HREF="./user_hide_exc.php?id='.$zaznam['id'].'" onclick="return confirm_hide(\''.$zaznam['jmeno'].' '.$zaznam['prijmeni'].'\')">Skrýt</A>';
	}
	else
	{
		$hidd = '<span class="WarningText">Ano</span>';
		$action = '<A HREF="./user_hide_exc.php?id='.$zaznam['id'].'" onclick="return confirm_show(\''.$zaznam['jmeno'].' '.$zaznam['prijmeni'].'\')"><font color="green">Zviditelnit</font></A>';
	}
	
	$acc = '<span class="DisableText">Ne</span>';
	$acc_r = 'Ne';

	if ($zaznam["aid"] != null)
	{
		$acc = 'Ano';
		if ($zaznam['locked'] != 0) 
		{
			$acc_r = '<span class="WarningText">Ano</span>';
			if ($zaznam['hidden'] == 0) 
				$action .= '&nbsp;/&nbsp;<A HREF="./user_lock_exc.php?id='.$zaznam['id'].'" onclick="return confirm_unlock(\''.$zaznam['jmeno'].' '.$zaznam['prijmeni'].'\')">Odemknout</A>';
		}
		else
		{
			$action .= '&nbsp;/&nbsp;<A HREF="./user_lock_exc.php?id='.$zaznam['id'].'" onclick="return confirm_lock(\''.$zaznam['jmeno'].' '.$zaznam['prijmeni'].'\')">Zamknout účet</A>';
		}
	}
	if ( $usr->user_id == $zaznam['id'])
	{
		$action = '-';
	}
	
	$row[] = $acc;
	$row[] = $hidd;
	$row[] = $acc_r;
	$row[] = $action;
	
	
	echo $data_tbl->get_new_row_arr($row)."\n";
}
echo $data_tbl->get_footer()."\n";

?>
<br>
Upozornění: Členu je skrytím zárověn i odebrána možnost přihlásit se do systému (zamčen účet).<br>
<BR>
</CENTER>
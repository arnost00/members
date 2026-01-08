<?php /* financnik - seznam sverencu pro finance */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Finance členů');
?>
<TABLE width="95%" border="0">
<TR>
<TD align="right">
<? if ( IsLoggedFinance() ) { ?>
<a href="javascript:open_win_ex('./fin_directory_export_exc.php','',600,600);">Export financí</a>
<? } ?>
</TD></TR>
</TABLE>
<br>

<CENTER>
<script language="javascript">
javascript:set_default_size(800,800);

function confirm_entry_lock(name) {
	return confirm('Opravdu chcete zamknout členu oddílu možnost se přihlašovat? \n Jméno člena : "'+name+'" \n Člen nebude mít možnost se přihlásit na závody!');
}

function confirm_entry_unlock(name) {
	return confirm('Opravdu chcete odemknout členu oddílu možnost se přihlašovat ? \n Jméno člena : "'+name+'"');
}
</script>

<?
	if (!isset($_GET["dateTo"]))
		$dateTo = '';
?>

<form action="">
<input type="hidden" name="id" value="<? echo _FINANCE_GROUP_ID_;?>"/>
<input type="hidden" name="subid" value="1"/>
<input type='date' name="dateTo" id='dateTo' value='<?echo($dateTo);?>'>
<input type="submit" value="Zobraz zůstatky k datu"/>
</form><br />

<?
require_once "./common_user.inc.php";
require_once('./csort.inc.php');

$sc = new column_sort_db();
$sc->add_column('sort_name','');
$sc->add_column('reg','');
$sc->set_url('index.php?id='._FINANCE_GROUP_ID_.'&subid=1',true);
$sub_query = $sc->get_sql_string();

$finance_dateTo_condition = isset($_GET["dateTo"])?' and date <= "'.$_GET["dateTo"].'"':"";

$query = 'SELECT u.id,prijmeni,jmeno,reg,hidden,entry_locked, ifnull(f.sum_amount,0) sum_amount, (n.amount+f.sum_amount) total_amount, u.chief_pay, ft.nazev, ft.popis, u.bank_account FROM '.TBL_USER.' u 
		left join (select sum(fin.amount) sum_amount, id_users_user from '.TBL_FINANCE.' fin where (fin.storno is null '.$finance_dateTo_condition.') group by fin.id_users_user) f on u.id=f.id_users_user 
		left join (select ui.chief_pay payer_id, ifnull(sum(fi.amount),0) amount from '.TBL_USER.' ui 
		left join '.TBL_FINANCE.' fi on fi.id_users_user = ui.id where ui.chief_pay is not null and (fi.storno is null or fi.storno != 1) group by ui.chief_pay) n on u.id=n.payer_id 
		left join '.TBL_FINANCE_TYPES.' ft on ft.id = u.finance_type
		group by u.id '.$sub_query;

@$vysledek=query_db($query);

require_once ('./common_fin.inc.php');
$enable_fin_types = IsFinanceTypeTblFilled();
$i=1;
if ($vysledek != FALSE && mysqli_num_rows($vysledek) > 0)
{
	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Poř.č.',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Příjmení',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
	$data_tbl->set_header_col_with_help($col++,'Reg.č.',ALIGN_CENTER,"Registrační číslo");
	$data_tbl->set_header_col_with_help($col++,'Fin.st.',ALIGN_CENTER,"Aktuální finanční stav\n* znamená že má platícího trenéra\n1. údaj je za člena, 2. za rodinu");
	$data_tbl->set_header_col_with_help($col++,'Přihl.',ALIGN_CENTER,"Možnost přihlašování se člena na závody");
	$data_tbl->set_header_col_with_help($col++,'Bank. účet.',ALIGN_CENTER,"Bankovní účet člena");
	if ($enable_fin_types)
		$data_tbl->set_header_col_with_help($col++,'Typ o.p.',ALIGN_CENTER,"Typ oddílových příspěvků");
	$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);

	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";

	$data_tbl->set_sort_col(1,$sc->get_col_content(0));
	$data_tbl->set_sort_col(3,$sc->get_col_content(1));
	echo $data_tbl->get_header_row_with_sort()."\n";
	
	while ($zaznam=mysqli_fetch_array($vysledek))
	{
		if (!$zaznam['hidden'])
		{
			$row = array();
			$row[] = $i++;
			$row[] = "<A href=\"javascript:open_win_ex('./view_address.php?id=".$zaznam["id"]."','',500,540)\" class=\"adr_name\">".$zaznam["prijmeni"]."</A>";
			$row[] = $zaznam['jmeno'];
			$row[] = RegNumToStr($zaznam['reg']);
			$class= ($zaznam['sum_amount']<0)? "red":"";
			$chief_sum="";
			if ($zaznam['total_amount']!=null)
			{
				$chief_sum="/".$zaznam['total_amount'];
				$class= ($zaznam['total_amount']<0)? "red":"";
			}
			$chief_pay_mark =($zaznam['chief_pay']>0 and $zaznam['chief_pay']<>$zaznam['id'])? "*":""; 
 			if ($zaznam['chief_pay']>0 and $zaznam['chief_pay']<>$zaznam['id'])
				$class="green";
			$row[] = "<span class='amount$class'>".$zaznam['sum_amount'].$chief_sum.$chief_pay_mark."</span>";
			
			if ($zaznam['entry_locked'] != 0)
				$row[] = '<span class="WarningText">Ne</span>';
			else
				$row[] = '';
			$row[] = $zaznam['bank_account'] != '' ? $zaznam['bank_account'] : '-';
			if ($enable_fin_types)
			{
				if ($zaznam['nazev'] != null)
				{
					$rowt = '';
					if ($zaznam['popis'] != '')
						$rowt ='<span style="cursor:help" title="'.$zaznam['popis'].'">';
					$rowt .= $zaznam['nazev'];
					if ($zaznam['popis'] != '')
						$rowt .='</span>';
					$row[] = $rowt;
				}
				else
					$row[] = '-';
			}
			$row_text = '';
			if ($enable_fin_types)
			{
				$row_text .= '<A HREF="user_finance_type.php?user_id='.$zaznam['id'].'">Změnit typ o.p.</A>';
				$row_text .= '&nbsp;/&nbsp;';
			}
			$row_text .= '<A HREF="javascript:open_win(\'./user_finance_view.php?user_id='.$zaznam['id'].'\',\'\')">Přehled</A>';
			$row_text .= '&nbsp;/&nbsp;';
			$lock = ($zaznam['entry_locked'] != 0) ? 'Odemknout' : 'Zamknout';
			$lock_onclick = ($zaznam['entry_locked'] != 0) ? 'confirm_entry_unlock' : 'confirm_entry_lock';
			$row_text .= '<A HREF="./user_lock2_exc.php?gr_id='._FINANCE_GROUP_ID_.'&id='.$zaznam['id'].'" onclick="return '.$lock_onclick.'(\''.$zaznam['jmeno'].' '.$zaznam['prijmeni'].'\')">'.$lock.'</A>';
			$row[] = $row_text;
			echo $data_tbl->get_new_row_arr($row)."\n";
		}
	}
	echo $data_tbl->get_footer()."\n";
}
?>

<? if ( IsLoggedAdmin() && ($enable_fin_types))
{
echo('<br><a href="javascript:open_win_ex(\'./adm_reset_ft.php\',\'\',600,600);">Reset typu oddílových příspěvků u všech členů</a><br>');
}
 ?>

<BR>

<?
require_once 'fin_directory_club_sum.php';
?>

</CENTER>

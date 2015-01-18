<?php /* financnik - seznam sverencu pro finance */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Finance èlenù');
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
<!-- 
javascript:set_default_size(800,800);

function confirm_entry_lock(name) {
	return confirm('Opravdu chcete zamknout èlenu oddílu možnost se pøihlašovat? \n Jméno èlena : "'+name+'" \n Èlen nebude mít možnost se pøihlásit na závody!');
}

function confirm_entry_unlock(name) {
	return confirm('Opravdu chcete odemknout èlenu oddílu možnost se pøihlašovat ? \n Jméno èlena : "'+name+'"');
}
//-->
</script>

<?
include "./common_user.inc.php";
include('./csort.inc.php');

$sc = new column_sort_db();
$sc->add_column('sort_name','');
$sc->add_column('reg','');
$sc->set_url('index.php?id=800&subid=10',true);
$sub_query = $sc->get_sql_string();
/*
$query = 'SELECT u.id,prijmeni,jmeno,reg,hidden,lic,lic_mtbo,lic_lob, ifnull(sum(f.amount),0) sum_amount FROM '.TBL_USER.' u 
		left join (select * from '.TBL_FINANCE.' fin where (fin.storno != 1 or fin.storno is null)) f on u.id=f.id_users_user group by u.id '.$sub_query;
*/
/*
$query = 'SELECT u.id,prijmeni,jmeno,reg,hidden,entry_locked, ifnull(sum(f.amount),0) sum_amount, chief_pay FROM '.TBL_USER.' u 
		left join (select * from '.TBL_FINANCE.' fin where (fin.storno != 1 or fin.storno is null)) f on u.id=f.id_users_user group by u.id '.$sub_query;
*/
$query = 'SELECT u.id,prijmeni,jmeno,reg,hidden,entry_locked, ifnull(f.sum_amount,0) sum_amount, (n.amount+f.sum_amount) total_amount, u.chief_pay, ft.nazev FROM '.TBL_USER.' u 
		left join (select sum(fin.amount) sum_amount, id_users_user from '.TBL_FINANCE.' fin where (fin.storno is null) group by fin.id_users_user) f on u.id=f.id_users_user 
		left join (select ui.chief_pay payer_id, ifnull(sum(fi.amount),0) amount from '.TBL_USER.' ui 
		left join '.TBL_FINANCE.' fi on fi.id_users_user = ui.id where ui.chief_pay is not null and (fi.storno is null or fi.storno != 1) group by ui.chief_pay) n on u.id=n.payer_id 
		left join '.TBL_FINANCE_TYPES.' ft on ft.id = u.finance_type
		group by u.id ORDER BY u.`sort_name` ASC;';

// echo "|$query|";

@$vysledek=MySQL_Query($query);

$i=1;
if ($vysledek != FALSE && mysql_num_rows($vysledek) > 0)
{
	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Poø.è.',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Pøíjmení',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
	$data_tbl->set_header_col_with_help($col++,'Reg.è.',ALIGN_CENTER,"Registraèní èíslo");
	$data_tbl->set_header_col_with_help($col++,'Fin.st.',ALIGN_CENTER,"Aktuální finanèní stav");
	$data_tbl->set_header_col_with_help($col++,'Pøihl.',ALIGN_CENTER,"Možnost pøihlašování se èlena na závody");
	$data_tbl->set_header_col_with_help($col++,'Typ o.p.',ALIGN_CENTER,"Typ oddílových pøíspìvkù");
	$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);

	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";

	$data_tbl->set_sort_col(1,$sc->get_col_content(0));
	$data_tbl->set_sort_col(3,$sc->get_col_content(1));
	echo $data_tbl->get_header_row_with_sort()."\n";
	
	while ($zaznam=MySQL_Fetch_Array($vysledek))
	{
		if (!$zaznam['hidden'])
		{
			$row = array();
			$row[] = $i++;
			$row[] = $zaznam['prijmeni'];
			$row[] = $zaznam['jmeno'];
			$row[] = RegNumToStr($zaznam['reg']);
			$zaznam['sum_amount']<0?$class="red":$class="";
			$zaznam['total_amount']!=null?$chief_sum="/".$zaznam['total_amount']:$chief_sum="";
			$chief_pay_mark = "";
			($zaznam['chief_pay']>0 and $zaznam['chief_pay']<>$zaznam['id'])?$chief_pay_mark = "*":$chief_pay_mark = ""; 
 			($zaznam['chief_pay']>0 and $zaznam['chief_pay']<>$zaznam['id'])?$class="green":$class=$class; 
			$row[] = "<span class='amount$class'>".$zaznam['sum_amount'].$chief_pay_mark.$chief_sum."</span>";
			if ($zaznam['entry_locked'] != 0)
				$row[] = '<span class="WarningText">Ne</span>';
			else
				$row[] = '';
			$row[] = ($zaznam['nazev'] != null)? $zaznam['nazev'] : '-';
			$row_text = '<A HREF="user_finance_type.php?user_id='.$zaznam['id'].'">Zmìnit typ o.p.</A>';
			$row_text .= '&nbsp;/&nbsp;';
			$row_text .= '<A HREF="javascript:open_win(\'./user_finance_view.php?user_id='.$zaznam['id'].'\',\'\')">Pøehled</A>';
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

<BR>

<?
include 'fin_directory_club_sum.php';
?>

</CENTER>

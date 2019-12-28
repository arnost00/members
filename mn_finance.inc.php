<?php /* trener stranka - seznam sverencu pro finance */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Finance členů');
?>
<CENTER>
<script language="javascript">
<!-- 
	javascript:set_default_size(800,800);
//-->
</script>
<?
require_once "./common_user.inc.php";
require_once('./csort.inc.php');

$sc = new column_sort_db();
$sc->add_column('sort_name','');
$sc->add_column('reg','');
$sc->set_url('index.php?id=500&subid=10',true);
$sub_query = $sc->get_sql_string();
/*
$query = 'SELECT u.id,prijmeni,jmeno,reg,hidden,lic,lic_mtbo,lic_lob, ifnull(sum(f.amount),0) sum_amount, ft.nazev FROM '.TBL_USER.' u 
		left join '.TBL_FINANCE_TYPES.' ft on ft.id = u.finance_type
		left join '.TBL_FINANCE.' f on u.id=f.id_users_user where f.storno is null 
		group by u.id '.$sub_query;
*/
$query = 'SELECT u.id,prijmeni,jmeno,reg,hidden,entry_locked, ifnull(f.sum_amount,0) sum_amount, (n.amount+f.sum_amount) total_amount, u.chief_pay, ft.nazev, ft.popis FROM '.TBL_USER.' u 
		left join (select sum(fin.amount) sum_amount, id_users_user from '.TBL_FINANCE.' fin where (fin.storno is null) group by fin.id_users_user) f on u.id=f.id_users_user 
		left join (select ui.chief_pay payer_id, ifnull(sum(fi.amount),0) amount from '.TBL_USER.' ui 
		left join '.TBL_FINANCE.' fi on fi.id_users_user = ui.id where ui.chief_pay is not null and (fi.storno is null or fi.storno != 1) group by ui.chief_pay) n on u.id=n.payer_id 
		left join '.TBL_FINANCE_TYPES.' ft on ft.id = u.finance_type
		group by u.id '.$sub_query;

@$vysledek=mysqli_query($db_conn, $query);

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
			$row[] = $zaznam['prijmeni'];
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
			if ($enable_fin_types)
				$row[] = ($zaznam['nazev'] != null)? $zaznam['nazev'] : '-';
			$row_text = '<A HREF="javascript:open_win(\'./user_finance_view.php?user_id='.$zaznam['id'].'\',\'\')">Přehled</A>';
			$row[] = $row_text;
			echo $data_tbl->get_new_row_arr($row)."\n";
		}
	}
	echo $data_tbl->get_footer()."\n";
}

?>
<BR>
</CENTER>
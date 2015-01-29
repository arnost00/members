<?php /* maly trener stranka - seznam sverencu pro finance */
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
include "./common_user.inc.php";
include('./csort.inc.php');

//priprava pro zobrazeni sverencu
$sc = new column_sort_db();
$sc->add_column('sort_name','');
$sc->add_column('reg','');
$sc->set_url('index.php?id=600&subid=10',true);
$sub_query = $sc->get_sql_string();
$query = 'SELECT u.id,prijmeni,jmeno,reg,hidden,lic,lic_mtbo,lic_lob, ifnull(sum(f.amount),0) sum_amount, ft.nazev FROM '.TBL_USER.' u 
		left join '.TBL_FINANCE_TYPES.' ft on ft.id = u.finance_type
		left join '.TBL_FINANCE.' f on u.id=f.id_users_user where f.storno is null AND u.chief_id = '.$usr->user_id.' and u.chief_pay is null group by u.id '.$sub_query;
// echo "|$query|";
@$vysledek=MySQL_Query($query);
//--------------------------------

//priprava pro zobrazeni rodiny = sverenci, za ktere platim
$sc_family = new column_sort_db();
$sc_family->add_column('sort_name','');
$sc_family->add_column('reg','');
$sc_family->set_url('index.php?id=600&subid=10',true);
$sub_query_family = $sc_family->get_sql_string();
$query_family = 'SELECT u.id,prijmeni,jmeno,reg,hidden,lic,lic_mtbo,lic_lob, ifnull(sum(f.amount),0) sum_amount, ft.nazev FROM '.TBL_USER.' u
		left join '.TBL_FINANCE_TYPES.' ft on ft.id = u.finance_type
		left join '.TBL_FINANCE.' f on u.id=f.id_users_user where f.storno is null AND u.chief_id = '.$usr->user_id.' and u.chief_pay = '.$usr->user_id.' group by u.id '.$sub_query;
@$vysledek_family=MySQL_Query($query_family);
//---------------------------------------------------------

//funkce pro zobrazeni tabulky se sverenci nebo rodinou
function showNursechildAndFamilyTables($vysledek, $sc, $showTotal)
{
	$i = 1;
	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Poř.č.',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Příjmení',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
	$data_tbl->set_header_col_with_help($col++,'Reg.č.',ALIGN_CENTER,"Registrační číslo");
	$data_tbl->set_header_col_with_help($col++,'Fin.st.',ALIGN_CENTER,"Aktuální finanční stav");
	$data_tbl->set_header_col_with_help($col++,'Typ o.p.',ALIGN_CENTER,"Typ oddílových příspěvků");
	$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);
	
	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";
	
	$data_tbl->set_sort_col(1,$sc->get_col_content(0));
	$data_tbl->set_sort_col(3,$sc->get_col_content(1));
	echo $data_tbl->get_header_row_with_sort()."\n";
	
	$total = 0;
	
	while ($zaznam=MySQL_Fetch_Array($vysledek))
	{
		if (!$zaznam['hidden'])
		{
			$row = array();
			$row[] = $i++;
			$row[] = $zaznam['prijmeni'];
			$row[] = $zaznam['jmeno'];
			$row[] = RegNumToStr($zaznam['reg']);
			$total+=$zaznam['sum_amount'];
			$zaznam['sum_amount']<0?$class="red":$class="";
			$row[] = "<span class='amount$class'>".$zaznam['sum_amount']."</span>";
			$row[] = ($zaznam['nazev'] != null)? $zaznam['nazev'] : '-';
			$row_text = '<A HREF="javascript:open_win(\'./user_finance_view.php?user_id='.$zaznam['id'].'\',\'\')">Přehled</A>';
			$row[] = $row_text;
			echo $data_tbl->get_new_row_arr($row)."\n";
		}
	}
	
	if ($showTotal)
	{
		$row = array();
		$row[] = "<b>Celkem</b>";
		$row[] = "";
		$row[] = "";
		$row[] = "";
		$total<0?$class="red":$class="";
		$row[] = "<b><span class='amount$class'>".$total."</span></b>";
		$row[] = "";
		echo $data_tbl->get_new_row_arr($row)."\n";
	}
	
	echo $data_tbl->get_footer()."\n<br>";
}
//------------------------------------------------------

$no_result = true;

if ($vysledek != FALSE && mysql_num_rows($vysledek) > 0)
{
	$no_result = false;
	echo "<hr>";
	DrawPageSubTitle("Svěřenci");
	showNursechildAndFamilyTables($vysledek, $sc, false);
}

if ($vysledek_family != FALSE && mysql_num_rows($vysledek_family) > 0)
{
	$no_result = false;
	echo "<hr>";
	DrawPageSubTitle("Rodina");
	
	showNursechildAndFamilyTables($vysledek_family, $sc_family, $showTotal=true);
}

//pokud nebyl nikdo zobrazen
if ($no_result)
{
	echo '<BR><BR>';
	echo '<span class="WarningText">Nemáte přiřazeného žádného člena oddílu. Požádejte někoho z "velkých" trenérů o nápravu.</span><BR>';
}

?>
<BR>
</CENTER>
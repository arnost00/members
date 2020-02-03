<?php /* trenerova stranka - editace clenu oddilu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Členská základna');
?>
<CENTER>

<script language="JavaScript">
<!--
function confirm_delete(name) {
	return confirm('Opravdu chcete smazat člena oddílu ? \n Jméno člena : "'+name+'" \n Člen bude nenávratně smazán !!');
}

-->
</script>

<?
require_once "./common_user.inc.php";
require_once('./csort.inc.php');

$sc = new column_sort_db();
$sc->add_column('sort_name','');
$sc->add_column('reg','');
$sc->set_url('index.php?id=500&subid=1',true);
$sub_query = $sc->get_sql_string();

$query = "SELECT u.id,prijmeni,jmeno,reg,hidden,lic,lic_mtbo,lic_lob,entry_locked, a.id aid, a.locked FROM ".TBL_USER." u"
	." left join ".TBL_ACCOUNT." a on a.id_users = u.id "
	.$sub_query;
@$vysledek=query_db($query);

if (IsSet($result) && is_numeric($result) && $result != 0)
{
	require_once('./const_strings.inc.php');
	$res_text = GetResultString($result);
	Print_Action_Result($res_text);
}

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Poř.č.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Příjmení',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$data_tbl->set_header_col_with_help($col++,'Reg.č.',ALIGN_CENTER,"Registrační číslo");
$data_tbl->set_header_col_with_help($col++,'Přihl.',ALIGN_CENTER,"Možnost přihlašování se člena na závody");
$data_tbl->set_header_col_with_help($col++,'L.OB',ALIGN_CENTER,"Licence pro OB");
$data_tbl->set_header_col_with_help($col++,'L.MTBO',ALIGN_CENTER,"Licence pro MTBO");
$data_tbl->set_header_col_with_help($col++,'L.LOB',ALIGN_CENTER,"Licence pro LOB");
$data_tbl->set_header_col_with_help($col++,'Účet',ALIGN_CENTER,"Stav a existence účtu");
$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";

$data_tbl->set_sort_col(1,$sc->get_col_content(0));
$data_tbl->set_sort_col(3,$sc->get_col_content(1));
echo $data_tbl->get_header_row_with_sort()."\n";

$i=1;
while ($zaznam=mysqli_fetch_array($vysledek))
{
	if (!$zaznam['hidden'])
	{
		$row = array();
		$row[] = $i++;
		$row[] = $zaznam['prijmeni'];
		$row[] = $zaznam['jmeno'];
		$row[] = RegNumToStr($zaznam['reg']);
		if ($zaznam['entry_locked'] != 0)
			$row[] = '<span class="WarningText">Ne</span>';
		else
			$row[] = '';
		$row[] = ($zaznam['lic'] != 'C' || $zaznam['lic'] != '-') ? '<B>'.$zaznam['lic'].'</B>' : $zaznam['lic'];
		$row[] = ($zaznam['lic_mtbo'] != 'C' || $zaznam['lic_mtbo'] != '-') ? '<B>'.$zaznam['lic_mtbo'].'</B>' : $zaznam['lic_mtbo'];
		$row[] = ($zaznam['lic_lob'] != 'C' || $zaznam['lic_lob'] != '-') ? '<B>'.$zaznam['lic_lob'].'</B>' : $zaznam['lic_lob'];
		$acc = '';
		if ($zaznam["aid"] != null)
		{
				if ($zaznam['locked'] != 0) 
					$acc = '<span class="WarningText">L</span> ';
				$acc .= 'Ano';
		}
		else
			$acc = '-';
		$row[] = $acc;
		$row[] = '<A HREF="./user_edit.php?id='.$zaznam['id'].'&cb=500">Edit</A>&nbsp;/&nbsp;<A HREF="./user_login_edit.php?id='.$zaznam["id"].'&cb=500">Účet</A>';
		echo $data_tbl->get_new_row_arr($row)."\n";
	}
}

echo $data_tbl->get_footer()."\n";

echo '<BR><BR>';
echo '(Červené <span class="WarningText">L</span> značí že účet je zablokován správcem. Tj. nejde se na něj přihlásit.)<BR>';
echo '<BR><hr><BR>';

require_once "./user_new.inc.php";
?>
<BR>
</CENTER>
<?php /* adminova stranka - editace clenu oddilu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Členská základna - Administrace');
?>
<CENTER>

<script language="JavaScript">
function confirm_delete(name) {
	return confirm('Opravdu chcete smazat člena oddílu ? \n Jméno člena : "'+name+'" \n Člen bude nenávratně smazán !!');
}

function confirm_entry_lock(name) {
	return confirm('Opravdu chcete zamknout členu oddílu možnost se přihlašovat? \n Jméno člena : "'+name+'" \n Člen nebude mít možnost se přihlásit na závody!');
}

function confirm_entry_unlock(name) {
	return confirm('Opravdu chcete odemknout členu oddílu možnost se přihlašovat ? \n Jméno člena : "'+name+'"');
}

function toggleShowHidden(element) {
	var showHidden = 0;
	if (element.value == 0) {
		showHidden = 1;
	};
	window.location.href = changeParameterValueInURL(this.location.href, 'showHidden', showHidden);
}
</script>

<style type="text/css">
<!--

.ctmc {
    table-layout: fixed;
    border-collapse: collapse;
	width: 975px;
}

.ctmc tbody {
	display: block;
	overflow: auto;
	height: 600px;
}

.ctmc thead tr {
	display: block;
}

.ctmc tr {
	max-height: 20px;
}

-->
</style>


<?
require_once "./common_user.inc.php";
require_once('./csort.inc.php');

if (!isset($_GET['showHidden'])) $showHidden = 0;

$sc = new column_sort_db();
$sc->add_column('sort_name','');
$sc->add_column('reg','');
$sc->set_url('index.php?id='._SMALL_ADMIN_GROUP_ID_.'&subid=1',true);
$sort_query = $sc->get_sql_string();
$where_query = ' where u.hidden is false ';
if ($showHidden) $where_query = ' ';

$query = "SELECT u.id,u.prijmeni,u.jmeno,u.reg,u.hidden,u.entry_locked, a.locked, a.policy_news, a.policy_regs, a.policy_mng, a.policy_adm, a.policy_fin, a.id aid FROM ".TBL_USER." u"
	." left join ".TBL_ACCOUNT." a on a.id_users = u.id "
	.$where_query
	.$sort_query;
@$vysledek=query_db($query);

if (IsSet($result) && is_numeric($result) && $result != 0)
{
	require_once('./const_strings.inc.php');
	$res_text = GetResultString($result);
	Print_Action_Result($res_text);
}

echo "<button id='showHidden' name='showHidden' onclick='toggleShowHidden(this)' value='".$showHidden."'>".($showHidden?'Skryj':'Zobraz')." skryté uživatele</button>";

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Poř.č.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Příjmení',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$data_tbl->set_header_col_with_help($col++,'Reg.č.',ALIGN_CENTER,"Registrační číslo");
$data_tbl->set_header_col_with_help($col++,'Uživatel',ALIGN_CENTER,"Editace uživatele");
$data_tbl->set_header_col_with_help($col++,'Účet',ALIGN_CENTER,"Vytvoř nebo edituj účet");
$data_tbl->set_header_col_with_help($col++,'Zamknut',ALIGN_CENTER,"Vypni uživateli možnost se přihlásit do systému");
$data_tbl->set_header_col_with_help($col++,'Skrytý',ALIGN_CENTER,"Uživatel je skrytý");
$data_tbl->set_header_col_with_help($col++,'Přihlášky',ALIGN_CENTER,"Možnost přihlašování se člena na závody");
$data_tbl->set_header_col_with_help($col++,'Práva',ALIGN_CENTER,"Přiřazená práva (zleva) : novinky, přihlašovatel, trenér, malý trenér, správce, finančník");

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";

$data_tbl->set_sort_col(1,$sc->get_col_content(0));
$data_tbl->set_sort_col(3,$sc->get_col_content(1));
echo '<thead>';
echo $data_tbl->get_header_row_with_sort()."\n";
echo '</thead><tbody>';

$i=1;
while ($zaznam=mysqli_fetch_array($vysledek))
{
	$row = array();
	$row[] = $i++;
	$row[] = '<a href="javascript:open_win(\'./view_adm_user_detail.php?id='.$zaznam['id'].'\',\'\')" class="adr_name">'.$zaznam['prijmeni'].'</a>';
	$row[] = $zaznam['jmeno'];
	$row[] = RegNumToStr($zaznam['reg']);
	//4. sloupec
	$row[] = '<A HREF="./user_edit.php?id='.$zaznam['id'].'&cb='._SMALL_ADMIN_GROUP_ID_.'">Uprav</A>';

	$acc_exist = '<span class="WarningText">Vytvoř</span>';

	$acc_hidden = '<A HREF="./user_hide_exc.php?id='.$zaznam['id'].'" onclick="return confirm_hide(\''.$zaznam['jmeno'].' '.$zaznam['prijmeni'].'\')">Skrýt</A>';
	$acc_locked = '<A HREF="./user_lock_exc.php?id='.$zaznam['id'].'" onclick="return confirm_lock(\''.$zaznam['jmeno'].' '.$zaznam['prijmeni'].'\')">Zamknout</A>';

	$acc_r = '<code>';
	if ($zaznam["hidden"] != 0)
	{
		$acc_hidden = '<A HREF="./user_hide_exc.php?id='.$zaznam['id'].'" onclick="return confirm_show(\''.$zaznam['jmeno'].' '.$zaznam['prijmeni'].'\')"><span class="WarningText">Zviditelnit</span></A>';
	}
	if ($zaznam["aid"] != null)
	{
		if ($zaznam['locked'] != 0)
		{			
			$acc_locked = '<A HREF="./user_lock_exc.php?id='.$zaznam['id'].'" onclick="return confirm_unlock(\''.$zaznam['jmeno'].' '.$zaznam['prijmeni'].'\')"><span class="WarningText">Odemknout</span></A>';
		}
		$acc_exist = 'Uprav';
		$acc_r .= ($zaznam['policy_news'] == 1) ? 'N ' : '. ';
		$acc_r .= ($zaznam['policy_regs'] == 1) ? 'P ' : '. ';
		$acc_r .= ($zaznam['policy_mng'] == _MNG_BIG_INT_VALUE_) ? 'T ' : '. ';
		$acc_r .= ($zaznam['policy_mng'] == _MNG_SMALL_INT_VALUE_) ? 't ' : '. ';
		$acc_r .= ($zaznam['policy_adm'] == 1) ? 'S ' : '. ';
		$acc_r .= ($zaznam['policy_fin'] == 1) ? 'F' : '.';
	}
	else
	{
		$acc_r .= '. . . . . .';
	}
	//if exist then label is Edit, if doesn't exist then label is Vytvor
	$row[] = '<A HREF="./user_login_edit.php?id='.$zaznam["id"].'&cb='._SMALL_ADMIN_GROUP_ID_.'">'.$acc_exist.'</A>';

	$row[] = $acc_locked;
	$row[] = $acc_hidden;
	if ($zaznam['entry_locked'] != 0)
		$row[] = '<A HREF="./user_lock2_exc.php?gr_id='._SMALL_ADMIN_GROUP_ID_.'&id='.$zaznam['id'].'" onclick="return confirm_entry_unlock(\''.$zaznam['jmeno'].' '.$zaznam['prijmeni'].'\')"><span class="WarningText">Odemknout</span></A>';
	else
		$row[] = '<A HREF="./user_lock2_exc.php?gr_id='._SMALL_ADMIN_GROUP_ID_.'&id='.$zaznam['id'].'" onclick="return confirm_entry_lock(\''.$zaznam['jmeno'].' '.$zaznam['prijmeni'].'\')">Zamknout</A>';
	$row[] = $acc_r.'</code>';
	echo $data_tbl->get_new_row_arr($row)."\n";
}
echo ('</tbody>');
echo $data_tbl->get_footer()."\n";

//echo '<BR><BR>';
//echo '(Červené <span class="WarningText">H</span> značí skrytého člena. Tj. vidí ho jen admin.)<BR>';
//echo '(Červené <span class="WarningText">L</span> značí že účet je zablokován. Tj. nejde se na něj přihlásit.)<BR>';
echo '<BR><hr><BR>';

?>
<script language="JavaScript">
(function() {
	var e_table = document.getElementsByClassName("ctmc")[0];

//set table header or table body cells same width as longer one
	var cols = e_table.children[0].rows[0].cells.length;
	for (var i = 0; i < cols; i++)
	{ 
		var head = e_table.children[0].rows[0].children[i];
		var col = e_table.children[1].rows[0].children[i];
		head.width = col.width = Math.max(head.offsetWidth,col.offsetWidth);
	}
})();
</script>
<?

require_once "./user_new.inc.php";
?>
<BR>
</CENTER>
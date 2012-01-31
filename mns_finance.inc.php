<? /* maly trener stranka - seznam sverencu pro finance */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<H1 class="ClubName"><?echo $g_www_name;?></H1>
<H2 class="PageName">Finance</H2>
<CENTER>

<?
include "./common_user.inc.php";
include('./csort.inc.php');

$sc = new column_sort_db();
$sc->add_column('sort_name','');
$sc->add_column('reg','');
$sc->set_url('index.php?id=600&subid=10',true);
$sub_query = $sc->get_sql_string();

//odstraneni sebe sama z vypisu
$query = 'SELECT id,prijmeni,jmeno,reg,hidden,lic,lic_mtbo,lic_lob FROM '.TBL_USER.' WHERE chief_id = '.$usr->user_id.$sub_query;
//$query = 'SELECT id,prijmeni,jmeno,reg,hidden,lic,lic_mtbo,lic_lob FROM '.TBL_USER.' WHERE chief_id = '.$usr->user_id.' OR id = '.$usr->user_id.$sub_query;
//$query = 'SELECT id,prijmeni,jmeno,reg,hidden,lic,lic_mtbo,lic_lob FROM '.TBL_USER.' WHERE chief_id = '.$usr->user_id.' OR id = '.$usr->user_id.' ORDER BY sort_name ASC '
@$vysledek=MySQL_Query($query);

if (IsSet($result) && is_numeric($result) && $result != 0)
{
	require('./const_strings.inc.php');
	$res_text = GetResultString($result);
	if($res_text != '')
	{
		echo "<BR><img src=\"imgs/line_navW.gif\" width=\"95%\" height=3 border=\"0\"><BR><BR>";
		echo "<font color=\"#FFCCFF\" size=\"+2\"><b>Výsledek poslední provedené úpravy :<BR>".$res_text."<BR></font><b>";
		echo "<BR><img src=\"imgs/line_navW.gif\" width=\"95%\" height=3 border=\"0\"><BR><BR>";
	}
}

$i=1;
if ($vysledek != FALSE && mysql_num_rows($vysledek) > 0)
{
	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Poø.è.',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Pøíjmení',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Reg.è.',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Finance',ALIGN_CENTER);

	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";
	echo $data_tbl->get_header_row()."\n";

	$data_tbl->set_sort_col(1,$sc->get_col_content(0));
	$data_tbl->set_sort_col(3,$sc->get_col_content(1));
	echo $data_tbl->get_sort_row()."\n";

	while ($zaznam=MySQL_Fetch_Array($vysledek))
	{
		if (!$zaznam['hidden'])
		{
			$row = array();
			$row[] = $i++;
			$row[] = $zaznam['prijmeni'];
			$row[] = $zaznam['jmeno'];
			$row[] = RegNumToStr($zaznam['reg']);
//odstraneno zobrazovani sebe sama ve vypisu sverencu
// 			if ($zaznam['id'] == $usr->user_id) 
// 			{
// 				$row_text = '<A HREF="./index.php?id=200&subid=10">Zobraz</A>';
// 				//$row_text .= '&nbsp;/&nbsp;<A HREF="./index.php?id=200&subid=1">Úèet</A>';
// 				$data_tbl->set_next_row_highlighted();
// 			}
// 			else
// 			{
				$val=GetUserAccountId_Users($zaznam['id']);
				$row_text = '<A HREF="./mns_user_finance_view.php?id='.$val.'">Zobraz</A>';
// 				if ($val)
// 					$row_text .= '&nbsp;/&nbsp;<A HREF="./mns_user_login_edit.php?id='.$zaznam['id'].'">Úèet</A>';
// 			}
			$row[] = $row_text;
			echo $data_tbl->get_new_row_arr($row)."\n";
		}
	}
	echo $data_tbl->get_footer()."\n";
}
else
{
	echo '<BR><BR>';
	echo '<font color="red">Nemáte pøiøazeného žádného èlena oddílu. Požádejte nìkoho z "velkých" trenérù o nápravu.</font><BR>';
}

?>
<BR>
</CENTER>
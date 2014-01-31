<? /* trenerova stranka - editace clenu oddilu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Èlenská základna');
?>
<CENTER>

<?
include "./common_user.inc.php";
include('./csort.inc.php');

$sc = new column_sort_db();
$sc->add_column('sort_name','');
$sc->add_column('reg','');
$sc->set_url('index.php?id=600&subid=1',true);
$sub_query = $sc->get_sql_string();

$query = 'SELECT id,prijmeni,jmeno,reg,hidden,lic,lic_mtbo,lic_lob FROM '.TBL_USER.' WHERE chief_id = '.$usr->user_id.' OR id = '.$usr->user_id.$sub_query;
//$query = 'SELECT id,prijmeni,jmeno,reg,hidden,lic,lic_mtbo,lic_lob FROM '.TBL_USER.' WHERE chief_id = '.$usr->user_id.' OR id = '.$usr->user_id.' ORDER BY sort_name ASC '
@$vysledek=MySQL_Query($query);

if (IsSet($result) && is_numeric($result) && $result != 0)
{
	require('./const_strings.inc.php');
	$res_text = GetResultString($result);
	Print_Action_Result($res_text);
}

$i=1;
if ($vysledek != FALSE && mysql_num_rows($vysledek) > 0)
{
	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Poø.è.',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Pøíjmení',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
	$data_tbl->set_header_col_with_help($col++,'Reg.è.',ALIGN_CENTER,"Registraèní èíslo");
	$data_tbl->set_header_col_with_help($col++,'L.OB',ALIGN_CENTER,"Licence pro OB");
	$data_tbl->set_header_col_with_help($col++,'L.MTBO',ALIGN_CENTER,"Licence pro MTBO");
	$data_tbl->set_header_col_with_help($col++,'L.LOB',ALIGN_CENTER,"Licence pro LOB");
	$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);

	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";
//	echo $data_tbl->get_header_row()."\n";

	$data_tbl->set_sort_col(1,$sc->get_col_content(0));
	$data_tbl->set_sort_col(3,$sc->get_col_content(1));
//	echo $data_tbl->get_sort_row()."\n";
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
			$row[] = ($zaznam['lic'] != 'C' || $zaznam['lic'] != '-') ? '<B>'.$zaznam['lic'].'</B>' : $zaznam['lic'];
			$row[] = ($zaznam['lic_mtbo'] != 'C' || $zaznam['lic_mtbo'] != '-') ? '<B>'.$zaznam['lic_mtbo'].'</B>' : $zaznam['lic_mtbo'];
			$row[] = ($zaznam['lic_lob'] != 'C' || $zaznam['lic_lob'] != '-') ? '<B>'.$zaznam['lic_lob'].'</B>' : $zaznam['lic_lob'];
			if ($zaznam['id'] == $usr->user_id) 
			{
				$row_text = '<A HREF="./index.php?id=200&subid=3">Editovat</A>';
				$row_text .= '&nbsp;/&nbsp;<A HREF="./index.php?id=200&subid=1">Úèet</A>';
				$data_tbl->set_next_row_highlighted();
			}
			else
			{
				$val=GetUserAccountId_Users($zaznam['id']);
				$row_text = '<A HREF="./mns_user_edit.php?id='.$zaznam['id'].'&cb=600">Editovat</A>';
				if ($val)
					$row_text .= '&nbsp;/&nbsp;<A HREF="./mns_user_login_edit.php?id='.$zaznam['id'].'&cb=600">Úèet</A>';
			}
			$row[] = $row_text;
			echo $data_tbl->get_new_row_arr($row)."\n";
		}
	}
	echo $data_tbl->get_footer()."\n";
}
else
{
	echo '<BR><BR>';
	echo '<span class="WarningText">Nemáte pøiøazeného žádného èlena oddílu. Požádejte nìkoho z "velkých" trenérù o nápravu.</span><BR>';
}

?>
<BR>
</CENTER>
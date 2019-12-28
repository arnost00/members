<?php /* adminova stranka - editace zavodu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Výpis změn v databázi');
?>
<CENTER>
<?
$sql_query = "SELECT * FROM ".TBL_MODLOG." ORDER BY id DESC";
$vysledek=mysqli_query($db_conn, $sql_query);
if($vysledek != FALSE)
{
	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Id',ALIGN_CENTER,0);
	$data_tbl->set_header_col($col++,'Čas',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Akce',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Tabulka',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Popis',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Autor',ALIGN_CENTER);

	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";
	echo $data_tbl->get_header_row()."\n";

	while ($zaznam=mysqli_fetch_array($vysledek))
	{
		$row = array();
		$row[] = $zaznam['id'];
		$row[] = TimeStamp2String($zaznam['timestamp']);
		$row[] = $zaznam['action'];
		$row[] = $zaznam['table'];
		$row[] = $zaznam['description'];
		$row[] = $zaznam['author'];
		echo $data_tbl->get_new_row_arr($row)."\n";
	}
	echo $data_tbl->get_footer()."\n";
}
?>
</CENTER>
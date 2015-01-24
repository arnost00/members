<?php /* zavody - zobrazeni zavodu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Pøehled typù oddílových pøíspìvkù');
?>
<CENTER>
<script language="JavaScript">
<!--
function confirm_delete() {
	return confirm('Opravdu chcete smazat tento typ oddílového pøíspìvku?');
}
-->
</script><?

@$vysledek=MySQL_Query("SELECT * FROM ".TBL_FINANCE_TYPES.' ORDER BY id');

if ($vysledek === FALSE )
{
	echo('Chyba v databázi, kontaktuje administrátora.<br>');
}
else
{
	$num_rows = mysql_num_rows($vysledek);
	if ($num_rows > 0)
	{

		$data_tbl = new html_table_mc();
		$col = 0;
		$data_tbl->set_header_col($col++,'Id',ALIGN_CENTER);
		$data_tbl->set_header_col($col++,'Název',ALIGN_LEFT);
		$data_tbl->set_header_col($col++,'Popis',ALIGN_LEFT);
		$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);

		echo $data_tbl->get_css()."\n";
		echo $data_tbl->get_header()."\n";
		echo $data_tbl->get_header_row()."\n";

		while ($zaznam=MySQL_Fetch_Array($vysledek))
		{
			$row = array();
			$row[] = $zaznam['id'];
			$row[] = $zaznam['nazev'];
			$row[] = nl2br ($zaznam['popis']);
			$row[] = '<A HREF="./fin_type_edit.php?id='.$zaznam['id'].'">Editovat</A>&nbsp;/&nbsp;<A HREF="./fin_type_del_exc.php?id='.$zaznam['id'].'" onclick="return confirm_delete()" class="Erase">Smazat</A>';

			echo $data_tbl->get_new_row_arr($row)."\n";
		}
		echo $data_tbl->get_footer()."\n";
	}
}
include ('fin_type_edit.inc.php');
?>



</CENTER>

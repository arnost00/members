<?php /* financnik - seznam sverencu pro finance */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Pøehled reklamací èlenù');
?>
<CENTER>
<script language="javascript">
<!-- 
	javascript:set_default_size(800,800);
//-->
</script>
<?
include "./common_user.inc.php";

$query = 'select * from `tst_finance` as f inner join
`tst_claim` as c on f.id = c.payment_id inner join
`tst_users` as u on f.id_users_user = u.id left join
`tst_zavod` as r on f.id_zavod = r.id
where f.claim = 1 and c.id in (select max(id) from `tst_claim` group by payment_id)
order by u.sort_name'; 

@$vysledek=MySQL_Query($query);

$i=1;
if ($vysledek != FALSE && mysql_num_rows($vysledek) > 0)
{
	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Poø.è.',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Èlen',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Závod',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Datum',ALIGN_LEFT);	
	$data_tbl->set_header_col($col++,'Èástka',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Poznámka',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Reklamace',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);

	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";

	echo $data_tbl->get_header_row()."\n";

	while ($zaznam=MySQL_Fetch_Array($vysledek))
	{
		$row = array();
		$row[] = $i++;
		$row[] = $zaznam['prijmeni']."&nbsp;".$zaznam['jmeno'];
		$row[] = $zaznam['nazev'];
		$row[] = $zaznam['date'];
		$row[] = $zaznam['amount'];
		$row[] = $zaznam['note'];
		$row[] = $zaznam['text'];
		$options = "Historie";
		$row[] = $options;
		echo $data_tbl->get_new_row_arr($row)."\n";
	}
	echo $data_tbl->get_footer()."\n";
}

?>

<BR>
</CENTER>
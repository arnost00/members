<?php /* financnik - seznam reklamaci */
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

$query = 'select *, f.id as fin_id from `'.TBL_FINANCE.'` as f inner join
`'.TBL_CLAIM.'` as c on f.id = c.payment_id inner join
`'.TBL_USER.'` as u on f.id_users_user = u.id left join
`'.TBL_RACE.'` as r on f.id_zavod = r.id
where f.claim = 1 and c.id in (select max(id) from `'.TBL_CLAIM.'` group by payment_id)
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
		$row[] = GetFormatedTextDel($zaznam['nazev'], $zaznam['cancelled']);
		$row[] = $zaznam['date'];
		$row[] = $zaznam['amount'];
		$row[] = $zaznam['note'];
		$row[] = $zaznam['text'];
		if ($g_enable_finances_claim)
			$row[] = '<A HREF="javascript:open_win(\'./claim.php?payment_id='.$zaznam['fin_id'].'\',\'\')">Problém?</A>';
		echo $data_tbl->get_new_row_arr($row)."\n";
	}
	echo $data_tbl->get_footer()."\n";
}

?>

<BR>
</CENTER>
<?
function createHTMLSelectFromSQLSelect($select_name, $option_value, $option_label, $SQLselect)
{	
	$select = "<select name=\"$select_name\">";
	$result=mysql_query($SQLselect);
	while ($record=MySQL_Fetch_Array($result))
	{
 		$select .= "<option value=$record[$option_value]>";		

 		$str = "\$select .= ".$option_label.";";
 		eval($str);
 		
 		$select .= "</option>";
	}
	$select .= '</select>';
	return $select;
}
?>

<hr>
<h3>Transfer mezi èleny</h3>
<form class="form" action="?<?=$return_url?>&payment=both" method="post" onsubmit="return isPositiveNumber(amount)">
<?
$data_tbl = new html_table_form();
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";

//pouze ja
$from_select = createHTMLSelectFromSQLSelect("id_from", "id", '$record["sort_name"]." :: ".$record["reg"]', "select id, reg, sort_name from `".TBL_USER."` where id = ".$user_id." order by sort_name asc;");
//vsichni krome me
$to_select = createHTMLSelectFromSQLSelect("id_to", "id", '$record["sort_name"]." :: ".$record["reg"]', "select id, reg, sort_name from `".TBL_USER."` where id <> ".$user_id." order by sort_name asc;");

echo $data_tbl->get_new_row('<label for="id_from">Pøevést od</label>', $from_select);
echo $data_tbl->get_new_row('<label for="id_to">Pøevést komu</label>', $to_select);
echo $data_tbl->get_new_row('<label for="amount">Èástka</label>', '<input name="amount" id="amount" type="text" size="5" maxlength="10" />');
echo $data_tbl->get_new_row('<label for="note">Poznámka</label>', '<input name="note" type="text" size="40" maxlength="200" />');

echo $data_tbl->get_empty_row();
echo $data_tbl->get_new_row('','<input type="submit" value="Provést transfer"/>');
echo $data_tbl->get_footer()."\n";
?>
</FORM>

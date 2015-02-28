<?
function createHTMLSelect($select_name, $options)
{	
	$select = "<select name=\"$select_name\">";
	foreach ($options as $option)
	{
		$select .= "<option value=".$option['value'].">".$option['label']."</option>";
	}
	$select .= '</select>';
	return $select;
}
?>

<hr>
<h3>Transfer mezi členy</h3>
<form class="form" action="?<?=$return_url?>&payment=both" method="post" onsubmit="return isPositiveNumber(amount)">
<?
$data_tbl = new html_table_form();
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";

$users_query = "select id, reg, sort_name  from `".TBL_USER."` where hidden=false order by sort_name asc;";
$users_result=mysql_query($users_query);
$from_options = array();
$to_options = array();

$opt = array();
$opt['value'] = -1;
$opt['label'] = ' - - - ';
$to_options[] = $opt;

while ($record=MySQL_Fetch_Array($users_result))
{
	$opt = array();
	$opt['value'] = $record['id'];
	$opt['label'] = $record["sort_name"]." :: ".$record["reg"];
	($record['id'] == $user_id)?$from_options[] = $opt:$to_options[] = $opt; 
}

$from_select = createHTMLSelect("id_from", $from_options);
$to_select = createHTMLSelect("id_to", $to_options);

echo $data_tbl->get_new_row('<label for="id_from">Převést od</label>', $from_select);
echo $data_tbl->get_new_row('<label for="id_to">Převést komu</label>', $to_select);
echo $data_tbl->get_new_row('<label for="amount">Částka</label>', '<input name="amount" id="amount" type="text" size="5" maxlength="10" />');
echo $data_tbl->get_new_row('<label for="note">Poznámka</label>', '<input name="note" type="text" size="40" maxlength="200" />');

echo $data_tbl->get_empty_row();
echo $data_tbl->get_new_row('','<input type="submit" value="Provést transfer"/>');
echo $data_tbl->get_footer()."\n";
?>
</FORM>

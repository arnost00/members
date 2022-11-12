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

$new_url = str_replace ('&payment=both','', $return_url);
?>

<hr>
<h3>Transfer mezi členy</h3>
<form class="form" action="?<?=$new_url?>&payment=both" method="post" onsubmit="return (isPositiveNumber(amount) && haveMoney(amount,sum_amount))">
<?
if ($sum_amount < 0 )
{
	echo('Nelze posíllat peníze členům, váš zůstatek není kladný.');
}
else
{
echo('<input name="sum_amount" hidden value="'.$sum_amount.'" />');
$data_tbl = new html_table_form();
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";

$users_query = "select id, reg, sort_name  from `".TBL_USER."` where hidden=false order by sort_name asc;";
$users_result=query_db($users_query);
$to_options = array();

$opt = array();
$opt['value'] = -1;
$opt['label'] = ' - - - ';
$to_options[] = $opt;

include ('common_user.inc.php');

while ($record=mysqli_fetch_array($users_result))
{
	$opt = array();
	$opt['value'] = $record['id'];
	$opt['label'] = $record["sort_name"]." :: ".$g_shortcut.RegNumToStr($record["reg"]) ;
	if ($record['id'] != $user_id)
		$to_options[] = $opt; 
}

$to_select = createHTMLSelect("id_to", $to_options);

if (is_array($zaznam_user_name))
{
	echo $data_tbl->get_new_row('<label for="id_from">Převést od</label>', $zaznam_user_name['name']);
	echo('<input name="id_from" hidden value="'.$user_id.'" />');

}
echo $data_tbl->get_new_row('<label for="id_to">Převést komu</label>', $to_select);
echo $data_tbl->get_new_row('<label for="amount">Částka</label>', '<input name="amount" id="amount" type="text" size="5" maxlength="10" />');
echo $data_tbl->get_new_row('<label for="note">Poznámka</label>', '<input name="note" type="text" size="40" maxlength="200" />');

echo $data_tbl->get_empty_row();
echo $data_tbl->get_new_row('','<input type="submit" value="Provést transfer"/>');
echo $data_tbl->get_footer()."\n";

}// else sum_amount
?>
</FORM>

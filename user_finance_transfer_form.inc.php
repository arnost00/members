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
$users_result=mysql_query($users_query);
$to_options = array();

$opt = array();
$opt['value'] = -1;
$opt['label'] = ' - - - ';
$to_options[] = $opt;

include ('common_user.inc.php');

while ($record=MySQL_Fetch_Array($users_result))
{
	$opt = array();
	$opt['value'] = $record['id'];
	$opt['label'] = $g_shortcut.RegNumToStr($record["reg"])." :: ".$record["sort_name"] ;
	if ($record['id'] != $user_id)
		$to_options[] = $opt; 
}

$to_select = createHTMLSelect("id_to", $to_options);

echo $data_tbl->get_new_row('<label for="id_to">Převést komu</label>', $to_select);
echo $data_tbl->get_new_row('<label for="amount">Částka</label>', '<input name="amount" id="amount" type="text" size="5" maxlength="10" />');
echo $data_tbl->get_new_row('<label for="note">Poznámka</label>', '<input name="note" type="text" size="40" maxlength="200" />');

echo $data_tbl->get_empty_row();
echo $data_tbl->get_new_row('','<input type="submit" value="Provést transfer"/>');
echo $data_tbl->get_footer()."\n";

}// else sum_amount
?>
</FORM>

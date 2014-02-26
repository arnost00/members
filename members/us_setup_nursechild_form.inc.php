<hr>
<h3>Platící èlen</h3>
<center>
<form class="form" action="?<?=$return_url?>&chiefPayFor=<?=$user_id?>" method="post">
<?
//get chief name
$chief_name = $chief_record["chief_name"];

$data_tbl = new html_table_form();
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";


$chief_record["chief_pay"]<>null?$checked = "CHECKED":$checked="";

$html_checkbox = "<input type='checkbox' name='chief_pay' id='chief_pay' value=$chief_id $checked>";

echo $data_tbl->get_new_row('<label for="chief_pay">Chci, aby za mne platil trenér '.$chief_name.'</label>', $html_checkbox);

echo $data_tbl->get_empty_row();
echo $data_tbl->get_new_row('','<input type="submit" value="Ulož"/>');
echo $data_tbl->get_footer()."\n";
?>
</FORM>
</center>
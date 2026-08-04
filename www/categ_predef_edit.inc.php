<?php 
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
if (IsLoggedRegistrator ())
	{
?>

<br><hr><br>
<?
	if(IsSet($update))
		DrawPageSubTitle('Formulář pro editaci předdefinovaných kategorií');
	else
	{
		DrawPageSubTitle('Formulář pro vložení nových předdefinovaných kategorií');
		$zaznam['id'] = -1;
		$zaznam['name'] = '';
		$zaznam['cat_list'] = '';
	}
?>
<FORM METHOD=POST ACTION="categ_predef_edit_exc.php<?if (IsSet($update)) echo "?update=".$update?>">
<?
$data_tbl = new html_table_form();
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";

echo $data_tbl->get_new_row('Název', '<INPUT TYPE="text" NAME="name" size="50" MAXLENGTH=50 VALUE="'.$zaznam['name'].'">');
echo $data_tbl->get_new_row('Seznam kategorií', '<TEXTAREA name="cat_list" cols="80" rows="3" wrap=virtual>'.$zaznam['cat_list'].'</TEXTAREA>');
echo $data_tbl->get_new_row('','Hodnoty oddělujte pouze středníkem');
echo $data_tbl->get_empty_row();
echo $data_tbl->get_new_row('','<INPUT TYPE="submit" VALUE="Odeslat">');
echo $data_tbl->get_footer()."\n";
?>
</FORM>
<?
	}
?>
<?php 
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
if (IsLoggedFinance ())
	{
?>

<br><hr><br>
<?
	if(IsSet($update))
		DrawPageSubTitle('Formulář pro editaci typu oddílového příspěvku');
	else
	{
		DrawPageSubTitle('Formulář pro vložení nového typu oddílového příspěvku');
		$zaznam['id'] = -1;
		$zaznam['nazev'] = '';
		$zaznam['popis'] = '';
	}
?>
<FORM METHOD=POST ACTION="fin_type_edit_exc.php<?if (IsSet($update)) echo "?update=".$update?>">
<?
$data_tbl = new html_table_form('fin_type');
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";

echo $data_tbl->get_new_row('Název', '<INPUT TYPE="text" NAME="nazev" size="50" MAXLENGTH=50 VALUE="'.$zaznam['nazev'].'">');
echo $data_tbl->get_new_row('Popis', '<TEXTAREA name="popis" cols="50" rows="10" wrap=virtual>'.$zaznam['popis'].'</TEXTAREA>');
echo $data_tbl->get_empty_row();
echo $data_tbl->get_new_row('','<INPUT TYPE="submit" VALUE="Odeslat">');
echo $data_tbl->get_footer()."\n";
?>
</FORM>
<?
	}
?>
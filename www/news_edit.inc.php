<?php /* adminova stranka - vlozeni clena */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
if (IsLoggedEditor ())
	{
?>

<SCRIPT LANGUAGE="JavaScript">
<!--
function change_font(object)
{
	if (object.value == "t_bold")
	{
		var start='<B>';
		var stop='</B>';
	}
	else
	{
		var start='<U>';
		var stop='</U>';
	}

	if (object.checked)
	{
		object.form.text.value=object.form.text.value+start;
		object.form.text.focus();
	}
	else
	{
		object.form.text.value=object.form.text.value+stop;
		object.form.text.focus();
	}
}

function check_form()
{ // checks ... date, text length
 var text=document.forms["form_news"]["text"].value;
 var datum=document.forms["form_news"]["datum"].value;
 var errors = "";
 
 if(text.length > <?echo(GC_NEWS_MAX_TEXT_LENGTH);?>)
 {
   errors += '\nPříliš mnoho znaků v textu. Prosím odstraňte '+ (text.length - <?echo(GC_NEWS_MAX_TEXT_LENGTH);?>)+ ' znaků.';
 }
 else if (text.length == 0)
 {
   errors +='\nChybí text novinky.';
 }
 if (datum.length == 0)
 {
   errors += '\nChybí datum novinky.';
 }
 else if (!isValidDate(datum))
 {
   errors += '\nNeplatné datum novinky.';
 }


 if (errors.length > 0)
 {
	alert ("Formulář nelze odeslat z následujících důvodů:\n" + errors);
	return false;
 }
 else
	return true;
}

//-->
</SCRIPT>

<br><hr><br>
<?
	if(IsSet($update))
		DrawPageSubTitle('Formulář pro editaci novinky');
	else
	{
		DrawPageSubTitle('Formulář pro vložení novinky');
		$zaznam['datum'] = GetCurrentDate();
		$zaznam['nadpis'] = '';
		$zaznam['text'] = '';
		$zaznam['internal'] = 0;
	}
?>
<FORM METHOD=POST ACTION="news_new_exc.php<?if (IsSet($update)) echo "?update=".$update?>" name="form_news" id="form_news" onsubmit="return check_form();">
<A name="addnews">&nbsp;</A>
<?
$data_tbl = new html_table_form('news');
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";

echo $data_tbl->get_new_row('Interní novinka', '<INPUT TYPE="checkbox" NAME="internal" value="1" '.(($zaznam['internal'] != 0) ? 'checked' :'').'> vidí ji pouze přihlášení<BR>');
echo $data_tbl->get_new_row('Datum', '<INPUT TYPE="text" NAME="datum" SIZE=10 MAXLENGTH=10 VALUE="'.Date2String($zaznam['datum']).'">&nbsp;&nbsp;(DD.MM.RRRR)');
echo $data_tbl->get_new_row('Nadpis', '<INPUT TYPE="text" NAME="nadpis" size="50" MAXLENGTH=50 VALUE="'.$zaznam['nadpis'].'">');
echo $data_tbl->get_new_row('Text', '<TEXTAREA name="text" cols="50" rows="10" wrap=virtual>'.$zaznam['text'].'</TEXTAREA>');
echo $data_tbl->get_new_row_text('', '<INPUT TYPE="checkbox" NAME="t_bold" onClick="javascript:change_font(this);" value="t_bold">Tučné písmo (<B>příklad</B>)<BR>
<INPUT TYPE="checkbox" NAME="t_unli" onClick="javascript:change_font(this);" value="t_unli">Podtržené písmo (<U>příklad</U>)<BR>
Upozornění - Vždy ukončujte změny písma, jinak může dojít k porušení formátování novinek.');
echo $data_tbl->get_empty_row();
echo $data_tbl->get_new_row('','<INPUT TYPE="submit" VALUE="Odeslat">');
echo $data_tbl->get_footer()."\n";
?>
</FORM>
<?
	}
?>
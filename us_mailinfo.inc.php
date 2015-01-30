<?php /* clenova stranka - editace informaci a nastaveni */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>

<script>
function changeVisibility(name, atr_id)
{
	var atr = document.getElementById(atr_id);
	var list = document.getElementsByName(name);
//	alert(list.length);
	if (atr.checked)
	{
		for (var i = 0; i < list.length; i++) {
			list[i].style.display = "table-row";
		}
	} else {
		for (var i = 0; i < list.length; i++) {
			list[i].style.display = "none";
		} 
	}
}

//funkce pro zobrazeni/schovani vybranych casti
function checkAllVisibilities()
{
	changeVisibility('daysbefore_hide', 'atf');
	changeVisibility('activechange_hide', 'ach');
<?
	if ($g_enable_finances)
	{
?>
	changeVisibility('finance_hide', 'afin');
<?
	}
?>
}

</script>
<?
DrawPageTitle('Upozorňování o termínech na email');
?>
<CENTER>
<?
if (IsLogged() && $g_enable_mailinfo)
{

require_once ('common_race.inc.php');
require_once ('common_fin.inc.php');

$vysledek=MySQL_Query("SELECT * FROM ".TBL_MAILINFO." WHERE id_user = '$usr->user_id' LIMIT 1");
$zaznam=MySQL_Fetch_Array($vysledek);
if ($zaznam == FALSE)
{
	$vysledek=MySQL_Query("SELECT * FROM ".TBL_USER." WHERE id = '$usr->user_id' LIMIT 1");
	$zaznam2=MySQL_Fetch_Array($vysledek);
	if ($zaznam2 == FALSE)
		$zaznam['email'] = '';
	else
		$zaznam['email'] = $zaznam2['email'];
	$zaznam['daysbefore'] = 3;
	$zaznam['type'] = 0;
	$zaznam['sub_type'] = 0;
	$zaznam['active_tf'] = 0;
	$zaznam['active_ch'] = 0;
	$zaznam['ch_data'] = 0;
	$zaznam['active_rg'] = 0;	// used only for registrator
	$zaznam['active_fin'] = 0;
	$zaznam['active_finf'] = 0;
	$zaznam['fin_type'] = 0;
	$zaznam['fin_limit'] = 0;
}
?>

<FORM METHOD=POST ACTION="./us_mailinfo_exc.php?id=<?echo $usr->user_id;?>">
<TABLE width="90%">
<TR>
	<TD width="40%" align="right">Email</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="email" SIZE=20 VALUE="<? echo $zaznam["email"]; ?>"></TD>
</TR>
<TR>
	<TD colspan="3"><br><hr></TD>
</TR>
<TR>
	<TD colspan="3" style="padding-left:20px;"><input onclick="changeVisibility('daysbefore_hide', this.id);" type="checkbox" name="active_tf" value="1" id="atf" <? echo(($zaznam['active_tf'])?' checked':'')?>><label for="active_tf">Blížící se konec termínu přihlášek ...</label></TD>
</TR>
<!-- id a name jsou zde kvuli chybe IE pro spravnou funkcnost getElementsByName -->
<TR name="daysbefore_hide" id="daysbefore_hide">
	<TD width="40%" align="right">Kolik dní před termínem upozorňovat</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="daysbefore" SIZE=3 VALUE="<? echo $zaznam["daysbefore"]; ?>"> [<? echo($g_mailinfo_minimal_daysbefore.' až '.$g_mailinfo_maximal_daysbefore);?> dní]</TD>
</TR>
<TR name="daysbefore_hide" id="daysbefore_hide">
	<TD width="40%" align="right">Typy závodů</TD>
	<TD width="5"></TD>
	<TD><span id="race_type">
<?
	for($ii=0; $ii<$g_racetype_cnt; $ii++)
	{
		echo('<input type="checkbox" name="racetype['.$ii.']" value="1" id="tid_'.$ii.'"');
		if(($zaznam['type'] & $g_racetype [$ii]['id']) != 0)
			echo(' checked');
		echo('><label for="tid_'.$ii.'">'.$g_racetype [$ii]['nm'].'</label>');
		echo('<br>');
	}
?>
	</span><a href="" onclick="checkAll('race_type',true); return false;">Vše</a> / <a href="" onclick="checkAll('race_type',false); return false;">Nic</a>
	</TD>
</TR>
<TR name="daysbefore_hide" id="daysbefore_hide">
	<TD width="40%" align="right">Žebříček</TD>
	<TD width="5"></TD>
	<TD><span id="zebricek">
<?
	for($ii=0; $ii<$g_zebricek_cnt; $ii++)
	{
		echo('<input type="checkbox" name="zebricek['.$ii.']" value="1" id="sid_'.$ii.'"');
		if(($zaznam['sub_type'] & $g_zebricek [$ii]['id']) != 0)
			echo(' checked');
		echo('><label for="sid_'.$ii.'">'.$g_zebricek [$ii]['nm'].'</label>');
		echo('<br>');
	}
?>
	</span><a href="" onclick="checkAll('zebricek',true); return false;">Vše</a> / <a href="" onclick="checkAll('zebricek',false); return false;">Nic</a>
	</TD>
</TR>
<TR>
	<TD colspan="3"><br><hr></TD>
</TR>
<TR>
	<TD colspan="3" style="padding-left:20px;"><input onclick="changeVisibility('activechange_hide', this.id);" type="checkbox" name="active_ch" value="1" id="ach" <? echo(($zaznam['active_ch'])?' checked':'')?>><label for="active_ch">Změny termínů nebo v kalendáři závodů ...</label></TD>
</TR>
<!-- id a name jsou zde kvuli chybe IE pro spravnou funkcnost getElementsByName -->
<TR name="activechange_hide" id="activechange_hide">
	<TD width="40%" align="right" valign="top">Posílat změny</TD>
	<TD width="5"></TD>
	<TD>
<?	
		echo('<input type="checkbox" name="ch_data[0]" value="1" id="chid_0"');
		if(($zaznam['ch_data'] & $g_modify_flag [0]['id']) != 0)
			echo(' checked');
		echo('><label for="chid_0">termínu přihlášek</label><br>');
		echo('<input type="checkbox" name="ch_data[1]" value="1" id="chid_1"');
		if(($zaznam['ch_data'] & $g_modify_flag [1]['id']) != 0)
			echo(' checked');
		echo('><label for="chid_1">přidání závodu do termínovky</label><br>');
		echo('<input type="checkbox" name="ch_data[2]" value="1" id="chid_2"');
		if(($zaznam['ch_data'] & $g_modify_flag [2]['id']) != 0)
			echo(' checked');
		echo('><label for="chid_2">termínu závodu</label><br>');
?>	
	</TD>
</TR>
<?
	if (IsLoggedRegistrator() || IsLoggedFinance())
	{
?>
<TR>
	<TD colspan="3"><br><hr></TD>
</TR>
<?
		if ( IsLoggedRegistrator())
		{
?>
<TR>
	<TD colspan="3" style="padding-left:20px;"><input type="checkbox" name="active_rg" value="1" id="arg" <? echo(($zaznam['active_rg'])?' checked':'')?>><label for="arg">Upozornit, že uplynul interní termín</label></TD>
</TR>
<?
		}
		if ( IsLoggedFinance() && $g_enable_finances)
		{
?>
<TR>
	<TD colspan="3" style="padding-left:20px;"><input type="checkbox" name="active_finf" value="1" id="afnf" <? echo(($zaznam['active_finf'])?' checked':'')?>><label for="afnf">Upozornit, na člena jež se dostal do mínusu ve financích.</label></TD>
</TR>
<?
		}
	}
if ($g_enable_finances)
{	//fin

?>
<TR>
	<TD colspan="3"><br><hr></TD>
</TR>
<TR>
	<TD colspan="3" style="padding-left:20px;"><input onclick="changeVisibility('finance_hide', this.id);" type="checkbox" name="active_fin" value="1" id="afin" <? echo(($zaznam['active_fin'])?' checked':'')?>><label for="active_fin">Upozornit o finančním stavu ...</label></TD>
</TR>
<!-- id a name jsou zde kvuli chybe IE pro spravnou funkcnost getElementsByName -->
<TR name="finance_hide" id="finance_hide">
	<TD width="40%" align="right" valign="top">Posílat stav účtu</TD>
	<TD width="5"></TD>
	<TD>
<?	
		echo('<input type="checkbox" name="fin_type[0]" value="1" id="fint_0"');
		if(($zaznam['fin_type'] & $g_fin_mail_flag [0]['id']) != 0)
			echo(' checked');
		echo('><label for="fint_0">při snížení pod limit</label> ');
		echo('<INPUT TYPE="text" NAME="fin_limit" SIZE=5 MAXLENGTH=5 VALUE="'.$zaznam["fin_limit"].'"> Kč');
		echo('<br>');
		echo('<input type="checkbox" name="fin_type[1]" value="1" id="fint_1"');
		if(($zaznam['fin_type'] & $g_fin_mail_flag [1]['id']) != 0)
			echo(' checked');
		echo('><label for="fint_1">při přechodu do mínusu</label><br>');
?>	
	</TD>
</TR>
<?
} // fin
?>
<TR>
	<TD colspan="3"><br><hr></TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3" align="center"><INPUT TYPE="submit" VALUE="Změnit údaje"></TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
</TABLE>
</FORM>
<?
}
?>
</CENTER>


<!-- pro aktualizaci zobrazeni/schovani casti, ktere nema uzivatel pouzity -->
<style onload="checkAllVisibilities()"/>
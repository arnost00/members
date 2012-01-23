<?php /* clenova stranka - editace informaci a nastaveni */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<H1 class="ClubName"><?echo $g_www_name;?></H1>
<H2 class="PageName">Upozoròování o termínech na email</H2>

<CENTER>
<?
if (IsLogged() && $g_enable_mailinfo)
{

include ("./common_race.inc.php");

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
}
//print_r($zaznam);
?>

<FORM METHOD=POST ACTION="./us_mailinfo_exc.php?id=<?echo $usr->user_id;?>">
<TABLE width="90%">
<TR>
	<TD width="40%" align="right">Email</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="email" SIZE=20 VALUE="<? echo $zaznam["email"]; ?>"></TD>
</TR>
<TR>
	<TD colspan="3"><br><img src="imgs/line_navW.gif" width="95%" height=3 border="0"></TD>
</TR>
<TR>
	<TD colspan="3"><input type="checkbox" name="active_tf" value="1" id="atf" <? echo(($zaznam['active_tf'])?' checked':'')?>><label for="atf">Blížící se konec termínu pøihlášek</label></TD>
</TR>
<TR>
	<TD width="40%" align="right">Kolik dní pøed termínem upozoròovat</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="daysbefore" SIZE=3 VALUE="<? echo $zaznam["daysbefore"]; ?>"> [<? echo($g_mailinfo_minimal_daysbefore.' až '.$g_mailinfo_maximal_daysbefore);?> dní]</TD>
</TR>
<TR>
	<TD width="40%" align="right">Typy závodù</TD>
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
<TR>
	<TD width="40%" align="right">Žebøíèek</TD>
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
	<TD colspan="3"><br><img src="imgs/line_navW.gif" width="95%" height=3 border="0"></TD>
</TR>
<TR>
	<TD colspan="3"><input type="checkbox" name="active_ch" value="1" id="ach" <? echo(($zaznam['active_ch'])?' checked':'')?>><label for="ach">Zmìny termínù nebo v kalendáøi závodù</label></TD>
</TR>
<TR>
	<TD width="40%" align="right">Posílat zmìny</TD>
	<TD width="5"></TD>
	<TD>
<?	
		echo('<input type="checkbox" name="ch_data[0]" value="1" id="chid_0"');
		if(($zaznam['ch_data'] & $g_modify_flag [0]['id']) != 0)
			echo(' checked');
		echo('><label for="chid_0">termínu pøihlášek</label><br>');
		echo('<input type="checkbox" name="ch_data[1]" value="1" id="chid_1"');
		if(($zaznam['ch_data'] & $g_modify_flag [1]['id']) != 0)
			echo(' checked');
		echo('><label for="chid_1">pøidání závodu do termínovky</label><br>');
		echo('<input type="checkbox" name="ch_data[2]" value="1" id="chid_2"');
		if(($zaznam['ch_data'] & $g_modify_flag [2]['id']) != 0)
			echo(' checked');
		echo('><label for="chid_2">termínu závodu</label><br>');
?>	
	</TD>
</TR>
<?
	if (IsLoggedRegistrator())
	{
?>
<TR>
	<TD colspan="3"><br><img src="imgs/line_navW.gif" width="95%" height=3 border="0"></TD>
</TR>
<TR>
	<TD colspan="3"><input type="checkbox" name="active_rg" value="1" id="arg" <? echo(($zaznam['active_rg'])?' checked':'')?>><label for="arg">Upozornit, že uplynul interní termín</label></TD>
</TR>
<?
	}
?>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3" align="center"><INPUT TYPE="submit" VALUE="Zmìnit údaje"></TD>
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
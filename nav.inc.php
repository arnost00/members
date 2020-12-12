<?php /* levy - navigacni sloupec */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>

<?
//______________________________________________________________
//	Menu creation function
//______________________________________________________________

function DrawMenuItem($name,$mi_id,$mi_subid) 
{
	global $id;
	global $subid;
	global $g_colors;

	$selected = ($mi_id == $id && $mi_subid == $subid);
	if ($selected)
	{
		echo '<TR><TD valign="top" align="center" bgcolor="'.$g_colors['nav_bgcolor_selected'].'"><span class="NaviColSmSel">'.$name.'</span></TD></TR>';
	}
	else
	{
		echo '<TR><TD valign="top" align="center"><a href="index.php?id='.$mi_id.(($mi_subid != 0) ? '&subid='.$mi_subid : '').'" class="NaviColSm">'.$name.'</a></TD></TR>';
	}
	echo "\n";
}

function DrawMenuItemStatic($name,$url,$target = '') 
{
	echo '<TR><TD valign="top" align="center"><a href="'.$url.'" class="NaviColSm"';
	if ($target != '')
		echo ' target="'.$target.'" ';
	echo '>'.$name.'</a></TD></TR>';
	echo "\n";
}

function DrawMenuGroupHeader($name)
{
	global $g_colors;

	echo '<TR><TD height="10"></TD></TR>'."\n";
	echo '<TR><TD valign="top" align="left" bgcolor="'.$g_colors['nav_bgcolor_group_header'].'">&nbsp;<span class="NaviGroup">'.$name.'&nbsp;:</span></TD></TR>';
	echo "\n";
}

//______________________________________________________________
?>

<TABLE border="0" cellpadding="0" cellspacing="0" width="180" bgcolor="<? echo $g_colors['nav_bgcolor_out'];?>">
<TR>
<TD rowspan="1" colspan="4" width="180" height="2"></TD>
</TR>
<TR>
<TD rowspan="3" colspan="1" width="4"></TD>
<TD rowspan="1" colspan="1" width="172" bgcolor="<? echo $g_colors['nav_bgcolor_in'];?>" valign="top">
<!-- Obsah Nav.Sloupce Begin -->
	<TABLE cellpadding="0" cellspacing="0" border="0" width="100%">
	<TR><TD height="5"></TD></TR>
	<?
	if (!empty($g_club_logo['FileN']))
	{
		echo('<TR><TD valign="top" align="center"><a href="'.$g_mainwww);
		echo('" target="_blank"><img src="imgs/'.$g_club_logo['FileN']);
		echo('" width="'.$g_club_logo['SizeW'].'" height="'.$g_club_logo['SizeH']);
		echo('" alt="'.$g_fullname.'" border="0"></a></TD></tr>');
	}
	?>
	<TR><TD height="5"></TD></TR>
<?
	DrawMenuItem('Novinky',0,0);
	DrawMenuItem('Adresář',1,0);
	if(!IsLogged())
		DrawMenuItem('Oddílová termínovka',2,0);
	if(!IsLogged() || IsLoggedAdmin())
	{
		DrawMenuItem('Aktualitky',4,0);
	}
	if(IsLogged())
	{
		echo '<TR><TD height="5"></TD></TR>';
		DrawMenuItem('Přihlášky členů',3,0);
		DrawMenuItemStatic ('Přehled přihlášek','race_show_all.php','_blank');
	}
	if(IsLoggedUser())
	{
		if (!IsLoggedAdmin())
		{
			DrawMenuItem('Aktualitky',4,0);
		}
		DrawMenuGroupHeader('Členské&nbsp;menu');
		DrawMenuItem('Přihlášky na závody',_USER_GROUP_ID_,2);
		if ($g_enable_finances)
			DrawMenuItem('Finance', _USER_GROUP_ID_, 10);
		DrawMenuItem('Nastavení přístupu',_USER_GROUP_ID_,1);
		DrawMenuItem('Nastavení zákl.údajů',_USER_GROUP_ID_,3);
		if ($g_enable_mailinfo)
			DrawMenuItem('Upozorňování',_USER_GROUP_ID_,4);
	}
	if(IsLoggedRegistrator())
	{
		DrawMenuGroupHeader('Menu&nbsp;přihlašovatele');
		DrawMenuItem('Přihlášky na závody',_REGISTRATOR_GROUP_ID_,1);
		DrawMenuItem('Editace závodů',_REGISTRATOR_GROUP_ID_,4);
	}
	if(IsLoggedManager())
	{
		DrawMenuGroupHeader('Menu&nbsp;trenéra');
		DrawMenuItem('Přihlášky na závody',_MANAGER_GROUP_ID_,2);
		DrawMenuItem('Členská základna',_MANAGER_GROUP_ID_,1);
		DrawMenuItem('Přiřazení skupin členů',_MANAGER_GROUP_ID_,3);
		DrawMenuItem('Přehled m.trenérů',_MANAGER_GROUP_ID_,4);
		if ($g_enable_finances)
			DrawMenuItem('Finance', _MANAGER_GROUP_ID_, 10);
	}
	else if (IsLoggedSmallManager())
	{
		DrawMenuGroupHeader('Menu&nbsp;malého&nbsp;trenéra');
		DrawMenuItem('Přihlášky na závody',_SMALL_MANAGER_GROUP_ID_,2);
		DrawMenuItem('Členská základna',_SMALL_MANAGER_GROUP_ID_,1);
		if ($g_enable_finances)
			DrawMenuItem('Finance', _SMALL_MANAGER_GROUP_ID_, 10);
	}
	if(IsLoggedSmallAdmin())
	{
		DrawMenuGroupHeader('Menu&nbsp;správce');
		DrawMenuItem('Členská základna',_SMALL_ADMIN_GROUP_ID_,1);
	}
	if(IsLoggedFinance() && $g_enable_finances)
	{
		DrawMenuGroupHeader('Menu&nbsp;finančníka');
		DrawMenuItem('Členská základna',_FINANCE_GROUP_ID_,1);
		DrawMenuItem('Přehled závodů',_FINANCE_GROUP_ID_,2);
		if ($g_enable_finances_claim) DrawMenuItem('Přehled reklamací',_FINANCE_GROUP_ID_,3);
		DrawMenuItem('Typy příspěvků',_FINANCE_GROUP_ID_,4);
	}
	if(IsLoggedAdmin())
	{
		DrawMenuGroupHeader('Administrace');
		DrawMenuItem('Servisní menu',_ADMIN_GROUP_ID_,1);
		DrawMenuItem('Přihlášky na závody',_ADMIN_GROUP_ID_,2);
		DrawMenuItem('Editace závodů',_ADMIN_GROUP_ID_,5);
		DrawMenuItem('Účty / Náhled',_ADMIN_GROUP_ID_,4);
		DrawMenuItem('Výpis změn',_ADMIN_GROUP_ID_,6);
		DrawMenuItem('Historie plateb',_ADMIN_GROUP_ID_,7);
		DrawMenuItem('Email info',_ADMIN_GROUP_ID_,8);
	}
?>
 	<TR><TD height="15"></TD></TR>
	<TR><td valign="top" align="center"><hr class="nav"></TD></TR>
	<TR><TD height="5"></TD></TR>
	<TR><TD valign="top" align="center">
<?
if (!IsLogged())
{
	if($g_is_system_running)
	{
?>
<script language="javascript">
<!-- 
function check_login_form(form)
{
	if(form.<? echo(_VAR_USER_LOGIN);?>.value == "")
	{
		alert("Není vyplněno pole 'Jméno'");
		form.<? echo(_VAR_USER_LOGIN);?>.focus();
		return false;
	}

	if(form.<? echo(_VAR_USER_PASS);?>.value == "")
	{
		alert("Není vyplněno pole 'Heslo'");
		form.<? echo(_VAR_USER_PASS);?>.focus();
		return false;
	}
	return true; // OK, submit form.
}
//-->
</script>

	<FORM METHOD=POST ACTION="./login.php" onSubmit="return check_login_form(this);">
	<TABLE border="0" cellpadding="0" cellspacing="2">
	<TR><TD class="login"><label>Jméno&nbsp;</TD><TD><INPUT TYPE="text" NAME="<? echo(_VAR_USER_LOGIN);?>" SIZE="10" class="login" tabindex="1"></label></TD></TR>
	<TR><TD class="login"><label>Heslo&nbsp;</TD><TD><INPUT TYPE="password" NAME="<? echo(_VAR_USER_PASS);?>" SIZE="10" class="login" tabindex="2"></label></TD></TR>
	<TR><TD colspan="2" height="4"></TD></TR>
	<TR><TD></TD><TD><INPUT TYPE="submit" VALUE="Přihlásit" tabindex="3"></TD></TR>
</TABLE>
	</FORM>
<?
	}
	else
		echo('<a href="sys_log.php">&nbsp;</a>');
}
else
{
	echo '<a href="./logoff.php" class="NaviColSm"><b>Odhlásit</b></a>';
}
?>
	</TD></TR>
	<TR><TD height="5"></TD></TR>
	</TABLE>
<!-- Obsah Nav.Sloupce End -->
</TD>
<TD rowspan="3" colspan="1" width="4"></TD>
</TR>
<?
if (IsLogged())
{
?>
<TR><TD rowspan="1" colspan="1" width="172" height="5" bgcolor="<? echo $g_colors['nav_bgcolor_in'];?>" valign="top" align="center">
<hr class="nav">
</TD></TR>
<TR><TD rowspan="1" colspan="1" width="172" height="25" bgcolor="<? echo $g_colors['nav_bgcolor_in'];?>" class="MemberText">
<?
require_once "./logged.inc.php";
?>
</TD></TR>
<?
}
?>
<TR>
<TD rowspan="1" colspan="3" width="180" height="4"></TD>
</TR>
</TABLE>

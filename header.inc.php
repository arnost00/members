<?php /* hlavicka - pocatecni definice stranky */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<? include ('version.inc.php'); ?>
<?php 
// Date in the past 
header("Expires: " . gmdate("D, d M Y H:i:s") . " GMT"); 

// always modified 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 

// HTTP/1.1 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false); 

// HTTP/1.0 
header("Pragma: no-cache"); 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
	<META http-equiv="Content-Type" content="text/html; charset=windows-1250">
	<META http-equiv="Content-language" content="cs">
	<META name="generator" content="<? echo SYSTEM_NAME.' '.GetCodeVersion(); ?>">
	<META name="description" content="<? echo $g_www_meta_description; ?>">
	<META name="keywords" content="ČSOB, Orientacni beh, Orientační běh, Orienteering, beh, běh, run, running, IOF, orientační, OB, <? echo $g_www_meta_keyword; ?>">
	<META name="copyright" content="(C) <? echo GetDevelopYears().' '.SYSTEM_AUTORS; ?>, All rights&nbsp;reserved.">
	<META name="authors" content="<? echo SYSTEM_AUTORS; ?>">
	<TITLE><? echo $g_www_title; ?></TITLE>
	<LINK href="main.css.php" rel="StyleSheet" type="text/css" >
	<script src="functions.js" type="text/javascript"></script>
	<link rel="alternate" type="application/rss+xml" title="RSS export" href="rss.php" />

	<script language="javascript">
	<!-- 
		javascript:set_default_race_url('<? echo $g_baseadr.'race_info_show.php?id_zav=';?>');
	//-->
	</script>
</HEAD>

<?
if (!isset($g_colors))
{
require("./cfg/_colors.php");
}
?>
<BODY text="<? echo $g_colors['body_text'];?>" bgcolor="<? echo $g_colors['body_bgcolor'];?>">

<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
<?
if(defined('IS_INDEX'))
{
?>
<td class="HdrClubName"><?echo $g_www_name;?></td>
</tr>
<tr>
<td height="2" bgcolor="<? echo $g_colors['nav_bgcolor_out'];?>"></td>
<?
}
else
{
?>
<td class="HdrAppName">oddílový přihláškový systém</td>
<td class="HdrClubName"><?echo $g_www_name;?></td>
</tr>
<tr>
<td colspan="2" height="2" bgcolor="<? echo $g_colors['nav_bgcolor_out'];?>"></td>
<?
}
?>
</tr>
</table>
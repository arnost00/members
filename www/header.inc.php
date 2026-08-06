<?php /* hlavicka - pocatecni definice stranky */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?php 

require_once ('./version.inc.php');
require_once ('./common.inc.php');

// Date in the past 
header("Expires: " . gmdate("D, d M Y H:i:s") . " GMT"); 

// always modified 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 

// HTTP/1.1 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false); 

// HTTP/1.0 
header("Pragma: no-cache"); 

if (!isset($head_addons)) $head_addons = '';
$head_addons .="\t".'<script src="functions.js" type="text/javascript"></script>'."\n";
$head_addons .="\t".'<script language="javascript">'."\n";
$head_addons .="\t".'<!-- '."\n";
$head_addons .="\t\t".'javascript:set_default_race_url(\''.$g_baseadr.'race_info_show.php?id_zav=\');'."\n";
$head_addons .="\t".'//-->'."\n";
$head_addons .="\t".'</script>'."\n";

require_once('./cfg/_colors.php');

$body_addons = 'text="'.$g_colors['body_text'].'" bgcolor="'.$g_colors['body_bgcolor'].'"';

HTML_Header($g_www_title, 'main.css.php', $body_addons, $head_addons);

?>

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
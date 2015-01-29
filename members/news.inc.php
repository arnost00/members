<?php /* novinky */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Novinky');
?>
<?
$curr_date = GetCurrentDate();

if (IsLoggedEditor())
{
	echo "<A href=\"#addnews\">Přidání novinky ...</A><BR>\n";
?>
<script language="JavaScript">
<!--
function confirm_delete(date) {
	return confirm('Opravdu chcete smazat tuto novinku ? \n Ze dne : "'+date+'" \n Novinka bude nenávratně smazána !!');
}
-->
</script>
<?
}
// news_sh
$news = (IsSet($news) && is_numeric($news) && $news > 0) ? 1 : 0;

$sql_query = 'SELECT '.TBL_NEWS.'.*, '.TBL_ACCOUNT.'.podpis FROM '.TBL_NEWS.' LEFT JOIN '.TBL_ACCOUNT.' ON '.TBL_NEWS.'.id_user = '.TBL_ACCOUNT.'.id ORDER BY datum DESC,id DESC';
if ($news != 1)
	$sql_query .= " LIMIT ".GC_NEWS_LIMIT;

@$vysledek=MySQL_Query($sql_query);
$cnt= ($vysledek != FALSE) ? mysql_num_rows($vysledek) : 0;
if($cnt > 0)
{
?>
<TABLE width="100%">
<?
	if ( IsLoggedAdmin() )
	{
		echo '<TR><TD></TD><TD class="LastDate">';
		echo 'Počet';
		if ($news != 1)
			echo ' zobrazených';
		echo ' novinek : '.$cnt;
		echo '</TD></TR>';
	}

	while ($zaznam=MySQL_Fetch_Array($vysledek))
	{
		$datum = Date2String($zaznam['datum']);
		echo '<TR><TD class="NewsItemDate">'.$datum.'&nbsp;&nbsp;</TD>';
		if ($zaznam['nadpis']!='') echo '<TD class="NewsItemTitle">'.$zaznam['nadpis'].' </TD></TR><TR><TD></TD>';
		$name_id = $zaznam['id_user'];

		echo '<TD class="NewsItem">'.$zaznam['text'];
		if ($name_id && $zaznam['podpis'] != '' && $name_id != $g_www_admin_id)
			echo '&nbsp;<span class="NewsAutor">[&nbsp;'.$zaznam['podpis'].'&nbsp;]</span>';
		if ( ($usr->account_id == $name_id) || IsLoggedAdmin() )
			echo '<span class="DisableText">&nbsp;&nbsp;(&nbsp;<A HREF="./news_edit.php?id='.$zaznam['id'].'" class="NewsEdit">Editovat</A>&nbsp;/&nbsp;<A HREF="./news_del_exc.php?id='.$zaznam['id'].'" onclick="return confirm_delete(\''.$datum.'\')" class="NewsErase">Smazat</A>&nbsp;)</span>';
		echo '</TD></TR>';
	}
//	news_sh
?>
</TABLE>
<?
if ($news != 1 && $cnt == GC_NEWS_LIMIT)
	echo '<BR><BR><CENTER><A href="index.php?id=0&news=1">Zobrazit všechny novinky</A></CENTER><BR>'."\n";
} // aspon jeden zaznam
else
{
	echo "Seznam novinek je prázdný.<BR>";
}
//	news_sh

include ('news_edit.inc.php');

?>
<BR>
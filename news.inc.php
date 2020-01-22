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

@$vysledek=query_db($sql_query);
$cnt= ($vysledek != FALSE) ? mysqli_num_rows($vysledek) : 0;
if($cnt > 0)
{
?>
<TABLE width="100%">
<?

include ('common_news.inc.php');

	if ( IsLoggedAdmin() )
	{
		echo '<TR><TD></TD><TD class="LastDate">';
		echo 'Počet';
		if ($news != 1)
			echo ' zobrazených';
		echo ' novinek : '.$cnt;
		echo '</TD></TR>';
	}

	while ($zaznam=mysqli_fetch_array($vysledek))
	{
		PrintNewsItem($zaznam,IsLoggedAdmin(),$usr,false);
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

require_once ('news_edit.inc.php');

?>
<BR>
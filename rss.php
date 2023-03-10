<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
require_once('./rss_generator.inc.php');
require_once('./connect.inc.php');
require_once('./cfg/_globals.php');
require_once('./common.inc.php');

$curr_xml_datetime = date('r');
$curr_xml_date = date('r',mktime(0,0,0,date('m'),date('d'),date('Y')));

$rss_channel = new rssGenerator_channel();
$rss_channel->title = 'Přihláškový systém';
$rss_channel->link = $g_baseadr;
$rss_channel->description = $g_www_meta_description;
$rss_channel->language = 'cs';
$rss_channel->generator = 'PHP RSS Feed Generator';
$rss_channel->managingEditor = $g_emailadr;
$rss_channel->webMaster = $g_emailadr;
$rss_channel->pubDate = $curr_xml_datetime;

if(db_Connect(true))
{
	$item = new rssGenerator_item();
	$item->title = 'Seznam závodů v nejbližších '.GC_SHOW_RACE_DAYS.' dnech';
	$item->description = '';
	//$item->link = $g_baseadr;
	$item->pubDate = $curr_xml_date;

	$curr_date = GetCurrentDate();

	@$vysledek=query_db("SELECT id,datum,datum2,nazev,typ,ranking,odkaz,prihlasky, prihlasky1,prihlasky2,prihlasky3,prihlasky4,prihlasky5, vicedenni,misto,oddil, vedouci,cancelled FROM ".TBL_RACE." WHERE datum >= ".$curr_date." AND datum <= ".IncDate($curr_date,GC_SHOW_RACE_DAYS)." ORDER BY datum, datum2, id");
	if (mysqli_num_rows($vysledek) > 0)
	{
		$item->link = $g_baseadr.'?id=4';
		$item->description = 'Jsou to:';
		while ($zaznam=mysqli_fetch_array($vysledek))
		{
			if($zaznam['vicedenni'])
				$datum=Date2StringFT($zaznam['datum'],$zaznam['datum2']);
			else
				$datum=Date2String($zaznam['datum']);
			$item->description .= '<br />';
			$item->description .= $datum.' - <b>'.GetFormatedTextDel($zaznam['nazev'], $zaznam['cancelled']).'</b>';
			if($zaznam['misto'] != '')
				$item->description .= ' - '.GetFormatedTextDel($zaznam['misto'], $zaznam['cancelled']);
			if($zaznam['oddil'] != '')
				$item->description .= ' - '.$zaznam['oddil'];
		}
	}
	else
	{
		$item->description = 'V nejbližších '.GC_SHOW_RACE_DAYS.' dnech není žádný závod.';
	}
	$rss_channel->items[] = $item;

	$item = new rssGenerator_item();
	$item->title = 'Seznam termínů přihlášek v nejbližších '.GC_SHOW_REG_DAYS.' dnech';
	$item->description = '';
	//$item->link = $g_baseadr;
	$item->pubDate = $curr_xml_date;

	$d1 = $curr_date;
	$d2 = IncDate($curr_date,GC_SHOW_REG_DAYS);
	$query = 'SELECT id, datum, datum2, nazev, typ, ranking, odkaz, prihlasky, prihlasky1, prihlasky2, prihlasky3, prihlasky4, prihlasky5, vicedenni, misto, oddil, vedouci, cancelled FROM '.TBL_RACE.' WHERE ((prihlasky1 >= '.$d1.' && prihlasky1 <= '.$d2.') || (prihlasky2 >= '.$d1.' && prihlasky2 <= '.$d2.') || (prihlasky3 >= '.$d1.' && prihlasky3 <= '.$d2.') || (prihlasky4 >= '.$d1.' && prihlasky4 <= '.$d2.') || (prihlasky5 >= '.$d1.' && prihlasky5 <= '.$d2.')) ORDER BY datum';
	@$vysledek=query_db($query);

	if (mysqli_num_rows($vysledek) > 0)
	{
		$item->link = $g_baseadr.'?id=4';
		$item->description = 'Jsou to:';
		while ($zaznam=mysqli_fetch_array($vysledek))
		{
			if($zaznam['vicedenni'])
				$datum=Date2StringFT($zaznam['datum'],$zaznam['datum2']);
			else
				$datum=Date2String($zaznam['datum']);
			$item->description .= '<br />';
			$item->description .= $datum.' - <b>'.GetFormatedTextDel($zaznam['nazev'], $zaznam['cancelled']).'</b>';
			if($zaznam['misto'] != '')
				$item->description .= ' - '.GetFormatedTextDel($zaznam['misto'], $zaznam['cancelled']);
			if($zaznam['oddil'] != '')
				$item->description .= ' - '.$zaznam['oddil'];
		}
	}
	else
	{
		$item->description = 'V nejbližších '.GC_SHOW_REG_DAYS.' dnech není žádná přihláška na závod.';
	}
	$rss_channel->items[] = $item;


	$item = new rssGenerator_item();
	$item->title = 'Seznam posledních novinek';
	$item->description = '';
	$item->link = $g_baseadr;
	$item->pubDate = $curr_xml_date;

	$sql_query = "SELECT * FROM ".TBL_NEWS." ORDER BY datum DESC,id DESC LIMIT 5";
	@$vysledek=query_db($sql_query);
	$NewsLastDate = 0;
	$j=0;
	if (mysqli_num_rows($vysledek) > 0)
	{
		$item->description = 'Jsou to:';
		while ($zaznam=mysqli_fetch_array($vysledek))
		{
			$datum = Date2String($zaznam["datum"]);
			if ($NewsLastDate < $zaznam["datum"])
				$NewsLastDate = $zaznam["datum"];
			$item->description .= '<br /><br />';
			if($zaznam['nadpis'] != '')
				$item->description .= $datum.' - <b>'.$zaznam['nadpis'].'</b><br />';
			else
				$item->description .= $datum.'<br />';
			
			$news_text = repair_html_text($zaznam['text']);
			$item->description .= $news_text;
		}
		$item->pubDate = date('r',$NewsLastDate);
	}

	$rss_channel->items[] = $item;
}
else
{
	$item = new rssGenerator_item();
	$item->description = $item->title = 'Nepodařilo se navázat spojení s databází.';
	$item->pubDate = $curr_xml_datetime;
	$rss_channel->items[] = $item;
}

$rss_feed = new rssGenerator_rss();
$rss_feed->encoding = 'utf-8';
$rss_feed->version = '2.0';

header('Content-Type: text/xml');
echo $rss_feed->createFeed($rss_channel);
?>

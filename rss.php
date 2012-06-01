<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
require('./rss_generator.inc.php');
require('./connect.inc.php');
require('./cfg/_globals.php');
require('./common.inc.php');

$curr_xml_datetime = date('r');
$curr_xml_date = date('r',mktime(0,0,0,date('m'),date('d'),date('Y')));

$rss_channel = new rssGenerator_channel();
$rss_channel->title = 'Pøihláškový systém';
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
	$item->title = 'Seznam závodù v nejbližších '.GC_SHOW_RACE_DAYS.' dnech';
	$item->description = '';
	//$item->link = $g_baseadr;
	$item->pubDate = $curr_xml_date;

	$curr_date = GetCurrentDate();

	@$vysledek=MySQL_Query("SELECT id,datum,datum2,nazev,typ,ranking,odkaz,prihlasky, prihlasky1,prihlasky2,prihlasky3,prihlasky4,prihlasky5, vicedenni,misto,oddil, vedouci FROM ".TBL_RACE." WHERE datum >= ".$curr_date." AND datum <= ".IncDate($curr_date,GC_SHOW_RACE_DAYS)." ORDER BY datum, datum2, id");
	if (mysql_num_rows($vysledek) > 0)
	{
		$item->link = $g_baseadr.'?id=4#races';
		$item->description = 'Jsou to:';
		while ($zaznam=MySQL_Fetch_Array($vysledek))
		{
			if($zaznam['vicedenni'])
				$datum=Date2StringFT($zaznam['datum'],$zaznam['datum2']);
			else
				$datum=Date2String($zaznam['datum']);
			$item->description .= '<br />';
			$item->description .= $datum.' - <b>'.$zaznam['nazev'].'</b>';
			if($zaznam['misto'] != '')
				$item->description .= ' - '.$zaznam['misto'];
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
	$item->title = 'Seznam termínù pøihlášek v nejbližších '.GC_SHOW_REG_DAYS.' dnech';
	$item->description = '';
	//$item->link = $g_baseadr;
	$item->pubDate = $curr_xml_date;

	$d1 = $curr_date;
	$d2 = IncDate($curr_date,GC_SHOW_REG_DAYS);
	$query = 'SELECT id, datum, datum2, nazev, typ, ranking, odkaz, prihlasky, prihlasky1, prihlasky2, prihlasky3, prihlasky4, prihlasky5, vicedenni, misto, oddil, vedouci FROM '.TBL_RACE.' WHERE ((prihlasky1 >= '.$d1.' && prihlasky1 <= '.$d2.') || (prihlasky2 >= '.$d1.' && prihlasky2 <= '.$d2.') || (prihlasky3 >= '.$d1.' && prihlasky3 <= '.$d2.') || (prihlasky4 >= '.$d1.' && prihlasky4 <= '.$d2.') || (prihlasky5 >= '.$d1.' && prihlasky5 <= '.$d2.')) ORDER BY datum';
	@$vysledek=MySQL_Query($query);

	if (mysql_num_rows($vysledek) > 0)
	{
		$item->link = $g_baseadr.'?id=4#regs';
		$item->description = 'Jsou to:';
		while ($zaznam=MySQL_Fetch_Array($vysledek))
		{
			if($zaznam['vicedenni'])
				$datum=Date2StringFT($zaznam['datum'],$zaznam['datum2']);
			else
				$datum=Date2String($zaznam['datum']);
			$item->description .= '<br />';
			$item->description .= $datum.' - <b>'.$zaznam['nazev'].'</b>';
			if($zaznam['misto'] != '')
				$item->description .= ' - '.$zaznam['misto'];
			if($zaznam['oddil'] != '')
				$item->description .= ' - '.$zaznam['oddil'];
		}
	}
	else
	{
		$item->description = 'V nejbližších '.GC_SHOW_REG_DAYS.' dnech není žádná pøihláška na závod.';
	}
	$rss_channel->items[] = $item;


	$item = new rssGenerator_item();
	$item->title = 'Seznam posledních novinek';
	$item->description = '';
	$item->link = $g_baseadr;
	$item->pubDate = $curr_xml_date;

	$sql_query = "SELECT * FROM ".TBL_NEWS." ORDER BY datum DESC,id DESC LIMIT 5";
	@$vysledek=MySQL_Query($sql_query);
	$NewsLastDate = 0;
	$j=0;
	if (mysql_num_rows($vysledek) > 0)
	{
		$item->description = 'Jsou to:';
		while ($zaznam=MySQL_Fetch_Array($vysledek))
		{
			$datum = Date2String($zaznam["datum"]);
			if ($NewsLastDate < $zaznam["datum"])
				$NewsLastDate = $zaznam["datum"];
			$item->description .= '<br /><br />';
			if($zaznam['nadpis'] != '')
				$item->description .= $datum.' - <b>'.$zaznam['nadpis'].'</b><br />';
			else
				$item->description .= $datum.'<br />';
			$item->description .= $zaznam['text'];
		}
		$item->pubDate = date('r',$NewsLastDate);
	}

	$rss_channel->items[] = $item;
}
else
{
	$item = new rssGenerator_item();
	$item->description = $item->title = 'Nepodaøilo se navázat spojení s databází.';
	$item->pubDate = $curr_xml_datetime;
	$rss_channel->items[] = $item;
}

$rss_feed = new rssGenerator_rss();
$rss_feed->encoding = 'windows-1250';
$rss_feed->version = '2.0';

header('Content-Type: text/xml');
echo $rss_feed->createFeed($rss_channel);
?>

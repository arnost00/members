<?php /* aktuality v prihlaskach */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Aktuální informace (Aktualitky)');
?>
<CENTER>
<script language="javascript">
<!-- 
/*	"menubar=yes,status=yes,width=600,height=600"	*/

	javascript:set_default_size(800,600);
//-->
</script>

<?
if(SHOW_USER)
{
	$date_limit = GetCurrentDate();
	$date_limit -= CG_INTERNAL_NEWS_DAYS_LIMIT *60 * 60 *24;
	
	$sql_query = 'SELECT '.TBL_NEWS.'.*, '.TBL_ACCOUNT.'.podpis FROM '.TBL_NEWS.' LEFT JOIN '.TBL_ACCOUNT.' ON '.TBL_NEWS.'.id_user = '.TBL_ACCOUNT.'.id WHERE '.TBL_NEWS.'.internal = 1 ORDER BY datum > '.$date_limit.' DESC,id DESC LIMIT '.GC_INTERNAL_NEWS_CNT_LIMIT;

	@$vysledek=query_db($sql_query);
	$cnt= ($vysledek != FALSE) ? mysqli_num_rows($vysledek) : 0;
	if($cnt > 0)
	{
		DrawPageSubTitle('Poslední interní novinky');
		include ('common_news.inc.php');
		echo('<TABLE width="100%">');
		while ($zaznam=mysqli_fetch_array($vysledek))
		{
			PrintNewsItem($zaznam,IsLoggedAdmin(),$usr,true);
		}
		echo('</TABLE>');
	}
}
DrawPageSubTitle('Nejbližší závody a přihlášky (do '.GC_SHOW_RACE_AND_REG_DAYS.' dní)');

require_once ('./common_race.inc.php');
require_once ('./url.inc.php');
require_once ('./ct_renderer_races.inc.php');

if(SHOW_USER)
{
	@$vysledek2=query_db("SELECT * FROM ".TBL_USER." where id=$usr->user_id");
	$entry_lock = false;
	if ($zaznam2=mysqli_fetch_array($vysledek2))
	{
		$entry_lock = ($zaznam2['entry_locked'] != 0);
	}
	$renderer_option['entry_lock'] = $entry_lock;
?>
<script language="javascript">
<!-- 
	function confirm_delete() {
		return confirm('Opravdu se chcete odhlasit?');
	}

	javascript:set_default_size(600,600);
//-->
</script>

<?	
}

$user_fields = '';
$user_join = '';
if(SHOW_USER)
{
	$user_fields = ' kat, termin,';
	$user_join =  '  LEFT JOIN '.TBL_ZAVXUS.' zu ON r.id = zu.id_zavod AND zu.id_user='.$usr->user_id;
}

$curr_date = GetCurrentDate();
$renderer_option['curr_date'] = $curr_date;

$d1 = $curr_date;
$d2 = IncDate($curr_date,GC_SHOW_REG_DAYS);
$query = 'SELECT r.id, r.datum, datum2, nazev, typ0, typ, ranking, odkaz, prihlasky, prihlasky1, prihlasky2, prihlasky3, prihlasky4,'
	.' prihlasky5, vicedenni, misto, oddil, kapacita, '. $user_fields .' vedouci, cancelled, concat(u.jmeno, \' \', u.prijmeni) as vedouci_jmeno '
	.' FROM '.TBL_RACE.' r left join '.TBL_USER.' u on r.vedouci = u.id ' . $user_join
	.' WHERE (((prihlasky1 >= '.$d1.' && prihlasky1 <= '.$d2.') || (prihlasky2 >= '.$d1.' && prihlasky2 <= '.$d2.') || (prihlasky3 >= '.$d1.' && prihlasky3 <= '.$d2.') || (prihlasky4 >= '.$d1.' && prihlasky4 <= '.$d2.') || (prihlasky5 >= '.$d1.' && prihlasky5 <= '.$d2.')) || ( r.datum >= '.$d1.' AND r.datum <= '.$d2.')) ORDER BY datum, datum2, r.id';

@$vysledek=query_db($query);

// Fetch all rows into array
$zaznamy  = $vysledek ? mysqli_fetch_all($vysledek, MYSQLI_ASSOC) : [];
$num_rows = count ($zaznamy);

if ($g_enable_race_capacity)
	$renderer_option['count_registered'] = GetCountRegistered ($zaznamy);

if ($num_rows > 0)
{
	if (SHOW_USER && $entry_lock)
	{
		echo('<span class="WarningText">Máte zamknutou možnost se přihlašovat.</span>'."<br>\n");
	}

	// define table
	$tbl_renderer = RacesRendererFactory::createTable();
	$tbl_renderer->addColumns('datum','nazev','misto','oddil','typ0','typ','odkaz');
	if ($g_enable_race_capacity)
		$tbl_renderer->addColumns('ucast');
	if(SHOW_USER)
		$tbl_renderer->addColumns('moznosti');
	else {
		$tbl_renderer->addColumns([new HelpHeaderRenderer('Př',ALIGN_CENTER,"Zobrazit přihlášené"),
			new FormatFieldRenderer ( 'id', function ($id) : string {
				return "<A HREF=\"javascript:open_win('./race_reg_view.php?id=".$id."','')\"><span class=\"TextAlertExpLight\">Zbr</span></A>";
			} )
		]);
	}
	$tbl_renderer->addColumns('prihlasky');
	if($g_enable_race_boss)
		$tbl_renderer->addColumns('vedouci');

	echo $tbl_renderer->render( new html_table_mc(), $zaznamy, $renderer_option );
}
else
{
	echo "V nejbližších ".GC_SHOW_REG_DAYS." dnech není žádná přihláška na závod.<BR>";
}
?>
<BR>

</CENTER>
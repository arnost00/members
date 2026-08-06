<?php
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */
?>
<?
DrawPageTitle('Historie transakcí');
?>
<CENTER>
<?
require_once('./ct_renderer_fin_history.inc.php');
require_once('./ct_renderer_races.inc.php');

if (!function_exists('render_fin_history_pagination')) {
	function render_fin_history_pagination(int $page, int $pages, string $style = 'margin-bottom: 10px;'): void
	{
		echo "<div style='".$style."'>";
		if ($page > 1) {
			echo "<a href='index.php?id="._FINANCE_GROUP_ID_."&subid=7&list_page=".($page - 1)."'><< Novější</a> | ";
		} else {
			echo "<< Novější | ";
		}

		echo "Stránka $page z $pages";

		if ($page < $pages) {
			echo " | <a href='index.php?id="._FINANCE_GROUP_ID_."&subid=7&list_page=".($page + 1)."'>Starší >></a>";
		} else {
			echo " | Starší >>";
		}
		echo "</div>";
	}
}

$limit = 50;
$page = isset($_GET['list_page']) ? (int)$_GET['list_page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$count_query = "SELECT COUNT(*) as cnt FROM `".TBL_FINANCE."` f WHERE f.storno is null";
$res_count = query_db($count_query);
$row_count = mysqli_fetch_assoc($res_count);
$total = $row_count['cnt'];
$pages = ceil($total / $limit);
if ($pages == 0) $pages = 1;

$query = "SELECT unix_timestamp(f.date) datum, u.reg as reg, u.sort_name as name, f.id_users_editor, e.sort_name as editor_name, f.amount, f.note, rc.nazev zavod_nazev, "
		." rc.datum zavod_datum FROM `".TBL_FINANCE."` f "
		." left join `".TBL_USER."` u on u.id = f.id_users_user "
		." left join `".TBL_USER."` e on e.id = f.id_users_editor "
		." left join `".TBL_RACE."` rc on f.id_zavod = rc.id where f.storno is null ORDER BY f.date desc, f.id desc LIMIT $limit OFFSET $offset";
@$vysl=query_db($query)
	or die("Chyba při provádění dotazu do databáze.");

$zaznamy = $vysl ? mysqli_fetch_all($vysl, MYSQLI_ASSOC) : [];

render_fin_history_pagination($page, $pages);

$tbl_renderer = FinanceHistoryRendererFactory::createTable();
$tbl_renderer->addColumns('datum', 'reg', 'name', 'editor_name', 'amount', 'zavod_datum', 'zavod_nazev', 'note');
$tbl_renderer->addBreak(new YearExpanderDetector());
$tbl_renderer->setRowAttrsExt(YearExpanderDetector::yearGroupRowAttrsExtender(...));

echo $tbl_renderer->render(new html_table_mc(), $zaznamy);

render_fin_history_pagination($page, $pages, 'margin-top: 10px;');

?>
<BR>
</CENTER>

<?php
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */
?>
<?
DrawPageTitle('Historie transakcí');
?>
<CENTER>
<?

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

$query = "SELECT f.date, concat('".$g_shortcut."',u.reg) as reg, u.sort_name as name, f.id_users_editor, f.amount, f.note, rc.nazev zavod_nazev, "
		." from_unixtime(rc.datum,'%Y-%m-%d') zavod_datum FROM `".TBL_FINANCE."` f "
		." left join `".TBL_USER."` u on u.id = f.id_users_user "
		." left join `".TBL_RACE."` rc on f.id_zavod = rc.id where f.storno is null ORDER BY f.date desc, f.id desc LIMIT $limit OFFSET $offset";
@$vysl=query_db($query)
	or die("Chyba při provádění dotazu do databáze.");

echo "<div style='margin-bottom: 10px;'>";
if ($page > 1) {
    echo "<a href='index.php?id="._FINANCE_GROUP_ID_."&subid=7&list_page=".($page-1)."'><< Novější</a> | ";
} else {
	echo "<< Novější | ";
}
echo "Stránka $page z $pages";
if ($page < $pages) {
    echo " | <a href='index.php?id="._FINANCE_GROUP_ID_."&subid=7&list_page=".($page+1)."'>Starší >></a>";
} else {
	echo " | Starší >>";
}
echo "</div>";

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'datum',ALIGN_CENTER,80);
$data_tbl->set_header_col($col++,'reg',ALIGN_LEFT,80);
$data_tbl->set_header_col($col++,'jméno',ALIGN_CENTER,160);
$data_tbl->set_header_col($col++,'zapsal',ALIGN_CENTER,60);
$data_tbl->set_header_col($col++,'částka',ALIGN_LEFT,100);
$data_tbl->set_header_col($col++,'závod d.',ALIGN_CENTER,80);
$data_tbl->set_header_col($col++,'závod n.',ALIGN_CENTER,160);
$data_tbl->set_header_col($col++,'kometář',ALIGN_CENTER,160);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

while ($zaznam=mysqli_fetch_array($vysl))
{
	$row = array();
	
	$row[] = $zaznam['date'];
	$row[] = $zaznam['reg'];
	$row[] = $zaznam['name'];
	
	$c = $zaznam['id_users_editor'] == 0 ? 'red': '';
	$row[] = "<span class='amount$c'>".$zaznam['id_users_editor']."</span>";
	$row[] = $zaznam['amount'];
	$row[] = $zaznam['zavod_datum'];
	$row[] = $zaznam['zavod_nazev'];
	$row[] = $zaznam['note'];

	echo $data_tbl->get_new_row_arr($row)."\n";
}
echo $data_tbl->get_footer()."\n";

echo "<div style='margin-top: 10px;'>";
if ($page > 1) {
    echo "<a href='index.php?id="._FINANCE_GROUP_ID_."&subid=7&list_page=".($page-1)."'><< Novější</a> | ";
} else {
	echo "<< Novější | ";
}
echo "Stránka $page z $pages";
if ($page < $pages) {
    echo " | <a href='index.php?id="._FINANCE_GROUP_ID_."&subid=7&list_page=".($page+1)."'>Starší >></a>";
} else {
	echo " | Starší >>";
}
echo "</div>";

?>
<BR>
</CENTER>
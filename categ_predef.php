<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
require_once("./cfg/_colors.php");

require_once ("./common.inc.php");
require_once ("./common_race.inc.php");
require_once ('./url.inc.php');
require_once ('./ctable.inc.php');

if (!IsLoggedRegistrator())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
DrawPageTitle('Přehled předdefinovaných kategorií');

db_Connect();

?>
<CENTER>
<script language="JavaScript">
function confirm_delete() {
	return confirm('Opravdu chcete smazat tyto předdefinované kategorie?');
}
</script><?

$query = "SELECT * FROM ".TBL_CATEGORIES_PREDEF.' ORDER BY id';
@$vysledek=query_db($query);

if ($vysledek === FALSE )
{
	echo('Chyba v databázi, kontaktuje administrátora.<br>');
}
else
{
	$num_rows = mysqli_num_rows($vysledek);
	if ($num_rows > 0)
	{

		$data_tbl = new html_table_mc();
		$col = 0;
		$data_tbl->set_header_col($col++,'Id',ALIGN_CENTER);
		$data_tbl->set_header_col($col++,'Název',ALIGN_LEFT);
		$data_tbl->set_header_col($col++,'Seznam kategorií',ALIGN_LEFT);
		$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);

		echo $data_tbl->get_css()."\n";
		echo $data_tbl->get_header()."\n";
		echo $data_tbl->get_header_row()."\n";

		while ($zaznam=mysqli_fetch_array($vysledek))
		{
			$row = array();
			$row[] = $zaznam['id'];
			$row[] = $zaznam['name'];
			$row[] = nl2br ($zaznam['cat_list']);
			$row[] = '<A HREF="./categ_predef_edit.php?id='.$zaznam['id'].'">Editovat</A>&nbsp;/&nbsp;<A HREF="./categ_predef_del_exc.php?id='.$zaznam['id'].'" onclick="return confirm_delete()" class="Erase">Smazat</A>';

			echo $data_tbl->get_new_row_arr($row)."\n";
		}
		echo $data_tbl->get_footer()."\n";
	}
}
require_once ('categ_predef_edit.inc.php');
?>
<BR><hr><BR>
<?
echo('<A HREF="index.php?id='._REGISTRATOR_GROUP_ID_.'&subid=4">Zpět</A><BR>');
?>
<BR><hr><BR>
</CENTER>

<?
HTML_Footer();
?>
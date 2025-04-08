<?php
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require_once ("connect.inc.php");
require_once ("sess.inc.php");
require_once("cfg/_globals.php");

if (!IsLoggedRegistrator())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

require_once ("ctable.inc.php");
require_once ("./connectors.php");
$connector = ConnectorFactory::create();

if (!isset($connector))
{
	echo('Chyba v nastavení, nenalezen žádny connector, kontaktuje administrátora.<br>');
	exit;
}
db_Connect();

require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
require_once ("./common.inc.php");

DrawPageTitle('Import závodu ze systému Oris');

DrawPageSubTitle('V rozmezí od '.$from.' do '.$to);
?>
<script language="javascript">
<!-- 
	javascript:set_default_size(800,600);
//-->
</script>

<TABLE width="100%" cellpadding="0" cellspacing="0" border="0">
<TR>
<TD width="2%"></TD>
<TD width="90%" ALIGN=left>
<CENTER>
<?

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'ID',ALIGN_CENTER,0);
$data_tbl->set_header_col($col++,'Datum',ALIGN_CENTER,0);
$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Název',ALIGN_LEFT);
$data_tbl->set_header_col_with_help($col++,'Poř.',ALIGN_CENTER,"Pořadatel");
$data_tbl->set_header_col($col++,'Poznámka',ALIGN_LEFT);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$edit_url = '<A HREF="javascript:open_win(\'./race_new.php?ext_id=#SEL#\',\'\')">Vytvořit</A>';
$list = $connector->getRacesList($from,$to);

foreach($list as $one) {
	$edit_url =  '<A HREF="javascript:open_win(\'./race_new.php?ext_id='.$one[0].'\',\'\')">Vytvořit</A>';
	$ext_id_info = '';

	// check if ID is is not yet used as ext_id
	$query_ext = 'SELECT id, datum, nazev, ext_id'.
	' FROM '.TBL_RACE.' WHERE ext_id = '.$one[0].
	' ORDER BY datum, datum2, id';
	$vysledek_ext=query_db($query_ext);

	if($vysledek_ext != FALSE) {
		while ($zaznam_ext=mysqli_fetch_array($vysledek_ext)) {
			if ($ext_id_info != '')
				$ext_id_info .= '<br />';
			$ext_id_info .= "ID již použito : ".Date2String($zaznam_ext['datum']).' - '.$zaznam_ext['nazev'];
		}
	}

	$one [] = (empty($ext_id_info)) ? $edit_url : '';
	$one [] = (!empty($ext_id_info)) ? $ext_id_info : '';

	echo $data_tbl->get_new_row_arr($one)."\n";

}

echo $data_tbl->get_footer()."\n";

?>
<BR><hr><BR>
<A HREF="index.php?id=<? echo _REGISTRATOR_GROUP_ID_;?>&subid=4">Zpět</A><BR>
<BR><hr><BR>
</CENTER>
</TD>
<TD width="2%"></TD>
</TR>
<TR><TD COLSPAN=4 ALIGN=CENTER>
<!-- Footer Begin -->
<?require_once ("footer.inc.php");?>
<!-- Footer End -->
</TD></TR>
</TABLE>

<?
HTML_Footer();
?>
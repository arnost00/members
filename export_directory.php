<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
@extract($_REQUEST);

require_once('./cfg/_colors.php');
require_once ('./connect.inc.php');
require_once ('./sess.inc.php');

if (!IsLoggedManager() && !IsLoggedRegistrator() && !IsLoggedSmallAdmin() && !IsLoggedAdmin())
{
	header('location: '.$g_baseadr.'error.php?code=21');
	exit;
}

require_once ('./header.inc.php'); // header obsahuje uvod html a konci <BODY>
require_once ('./common.inc.php');

DrawPageTitle('Export adresáře');

?>

<h3 class="LinksTitle">Parametry exportu :</h3>
<form method="post" action="export_directory_exc.php">
Oddělovač mezi sloupci :<br>
<input type="radio" name="par1" value="1" checked id="id_p1a"><label for="id_p1a">Středník</label><br>
<input type="radio" name="par1" value="2" id="id_p1b"><label for="id_p1b">Tabelátor</label><br>
<br>
Sloupce uzavřít do uvozovek :<br>
<input type="radio" name="par2" value="1" checked id="id_p2a"><label for="id_p2a">Ano</label><br>
<input type="radio" name="par2" value="0" id="id_p2b"><label for="id_p2b">Ne</label><br>
<br>
Vložit apostrof před numerické sloupce :<br>
<input type="radio" name="par3" value="1" checked id="id_p3a"><label for="id_p3a">Ano</label><br>
<input type="radio" name="par3" value="0" id="id_p3b"><label for="id_p3b">Ne</label><br>
<br>
<input type="submit" value="Exportovat">&nbsp;&nbsp;<button onclick="javascript:close_popup();">Zavřít</button>
</form>
<?
HTML_Footer();
?>
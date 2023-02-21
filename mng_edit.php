<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
@extract($_REQUEST);

require_once('./cfg/_colors.php');
require_once ('./connect.inc.php');
require_once ('./sess.inc.php');

if (!IsLoggedManager())
{
	header('location: '.$g_baseadr.'error.php?code=21');
	exit;
}
require_once ('./ctable.inc.php');
require_once ('./header.inc.php'); // header obsahuje uvod html a konci <BODY>
require_once ('./common.inc.php');
require_once ('./common_user.inc.php');

$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

DrawPageTitle('Editace přiřazení trenéra členu');
?>
<SCRIPT LANGUAGE="JavaScript">
function check_mng(vstup)
{
	if (vstup.mng.value < 0)
	{
		alert("Musíš zadat trenéra pro člena.");
		return false;
	}
	else
		return true;
}
</SCRIPT>
<?
db_Connect();

$query = "SELECT * FROM ".TBL_USER." WHERE id = $id LIMIT 1";
@$vysledek=query_db($query);
@$zaznam=mysqli_fetch_array($vysledek);

$data_tbl = new html_table_nfo;
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_new_row('Jméno',$zaznam['jmeno'].' '.$zaznam['prijmeni']);
echo $data_tbl->get_new_row('Registrační číslo',$g_shortcut.RegNumToStr($zaznam['reg']));
echo $data_tbl->get_new_row('Číslo SI čipu',SINumToStr($zaznam['si_chip']));
echo $data_tbl->get_new_row('Datum narození', SQLDate2String($zaznam['datum']));
echo $data_tbl->get_new_row('Licence OB', $zaznam['lic']);
echo $data_tbl->get_new_row('Licence MTBO', $zaznam['lic_mtbo']);
echo $data_tbl->get_new_row('Licence LOB', $zaznam['lic_lob']);
echo $data_tbl->get_footer()."\n";

echo '<H3 class="LinksTitle">Trenér pro člena</H2>'."\n";

echo '<FORM METHOD=POST ACTION="./mng_edit_exc.php?id='.$id.'" onsubmit="return check_mng(this);"> ';

?>
<TABLE>
<TR><TD>
<SELECT name="mng" size=10>
<?
$query = 'SELECT u.id,u.prijmeni,u.jmeno, u.hidden FROM '.TBL_USER.' as u, '.TBL_ACCOUNT.' WHERE '.TBL_ACCOUNT.'.id_users = u.id AND '.TBL_ACCOUNT.'.policy_mng = '._MNG_SMALL_INT_VALUE_." AND u.id <> $id";
@$vysl=query_db($query);

echo '<OPTION value="0'.(($zaznam['chief_id'] == 0) ? '" selected ':'"').'>-- bez malého trenéra --';

while ($zazn=mysqli_fetch_array($vysl))
{
	if(!$zazn['hidden'])
	{
		echo '<OPTION value="'.$zazn['id'].(($zazn['id'] == $zaznam['chief_id']) ? '" selected ':'"').'>'.$zazn['jmeno'].' '.$zazn['prijmeni'];
	}
}
?>
</SELECT>
</TD><TD width="10"></TD><TD valign="top">
<INPUT TYPE="submit" value='Proveď změny'>
<BR><BR>
</FORM>
<BUTTON onclick="javascript:close_popup();">Zpět</BUTTON>
</TD></TR></TABLE>

<?
HTML_Footer();
?>
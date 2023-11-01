<? /* adminova stranka - detail clena */
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require_once("./cfg/_colors.php");
require_once ("./connect.inc.php");
require_once ("./sess.inc.php");

if (!IsLogged())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

require_once ("./ctable.inc.php");

$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

db_Connect();

if (IsSet($edit) && $edit=true)
{
    $updateUser = "update ".TBL_USER." set hidden='".$hidden."', entry_locked='".$entry_locked."' where id=".$id;
    $updateAccount = "update ".TBL_ACCOUNT." set locked='".$locked."' where id_users=".$id;
    query_db($updateUser);
    query_db($updateAccount);
    ?>
    <SCRIPT LANGUAGE="JavaScript">
        window.opener.location.reload();
        window.opener.focus();
        window.close();
    </SCRIPT>
    <?
}

// id je z tabulky "users"
@$vysledek=query_db("SELECT u.prijmeni, u.jmeno, u.reg, u.hidden, u.entry_locked, a.locked, a.id aid FROM ".TBL_USER." u left join ".TBL_ACCOUNT." a on a.id_users = u.id WHERE u.id = '$id' LIMIT 1");
@$zaznam=mysqli_fetch_array($vysledek);

require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
require_once ("./common.inc.php");
require_once ("./common_user.inc.php");


DrawPageTitle('Informace o členovi');
?>

<script language="javascript">
    function save() {
        var locked = (document.getElementById('locked').checked)?1:0;
        var hidden = (document.getElementById('hidden').checked)?1:0;
        var entry_locked = (document.getElementById('entry_locked').checked)?1:0;
        window.location.href = changeParameterValueInURL(changeParameterValueInURL(changeParameterValueInURL(this.location.href, 'entry_locked', entry_locked), 'hidden', hidden), 'locked', locked)+'&edit=true';
    }
</script>

<TABLE width="100%" cellpadding="0" cellspacing="0" border="0">
<TR>
<TD width="2%"></TD>
<TD width="90%" ALIGN=left>
<CENTER>

<?
$data_tbl = new html_table_nfo;
$data_tbl->table_width = 100;
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";

echo $data_tbl->get_new_row('Jméno', $zaznam["jmeno"].' '.$zaznam["prijmeni"]);
echo $data_tbl->get_new_row('Registrační číslo', $g_shortcut.RegNumToStr($zaznam["reg"]));
$checkbox = "<input id='hidden' value='1' type='checkbox' ".($zaznam['hidden']?"checked":"")."/>";
echo $data_tbl->get_new_row('Skrytý člen', $checkbox);
if ($zaznam['aid']) //check if user has account
{
    $checkbox = "<input id='locked' value='1' type='checkbox' ".($zaznam['locked']?"checked":"")."/>";
    echo $data_tbl->get_new_row('Zamčený účet', $checkbox);
    $checkbox = "<input id='entry_locked' value='1' type='checkbox' ".($zaznam['entry_locked']?"checked":"")."/>";
    echo $data_tbl->get_new_row('Zamčené přihlášky', $checkbox);
} else
{
    echo $data_tbl->get_new_row("", '<span class="WarningText">Uživatel nemá účet</span>');
}
echo $data_tbl->get_footer()."\n";
?>

<BR><BUTTON onclick="javascript:close_popup();">Zavřít</BUTTON><BUTTON class="left-margin-50px" onclick="save()">Uložit</BUTTON></TD></TR>
</CENTER>
</TD>
<TD width="2%"></TD>
</TR>
</TABLE>

<?
HTML_Footer();
?>

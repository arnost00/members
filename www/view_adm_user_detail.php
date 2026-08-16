<? /* adminova stranka - detail clena */
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

$id = $_GET['id'] ?? null;

require_once("./cfg/_colors.php");
require_once ("./connect.inc.php");
require_once ("./sess.inc.php");

RequirePageAccess(IsLoggedAdmin());

require_once ("./ctable.inc.php");

$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

db_Connect();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['edit']))
{
    $hidden = isset($_POST['hidden']) ? 1 : 0;
    $entry_locked = isset($_POST['entry_locked']) ? 1 : 0;
    $locked = isset($_POST['locked']) ? 1 : 0;
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

<FORM METHOD="POST" ACTION="./view_adm_user_detail.php?id=<?=$id?>">
<INPUT TYPE="hidden" NAME="edit" VALUE="1">
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
$checkbox = "<input id='hidden' name='hidden' value='1' type='checkbox' ".($zaznam['hidden']?"checked":"")."/>";
echo $data_tbl->get_new_row('Skrytý člen', $checkbox);
if ($zaznam['aid']) //check if user has account
{
    $checkbox = "<input id='locked' name='locked' value='1' type='checkbox' ".($zaznam['locked']?"checked":"")."/>";
    echo $data_tbl->get_new_row('Zamčený účet', $checkbox);
    $checkbox = "<input id='entry_locked' name='entry_locked' value='1' type='checkbox' ".($zaznam['entry_locked']?"checked":"")."/>";
    echo $data_tbl->get_new_row('Zamčené přihlášky', $checkbox);
} else
{
    echo $data_tbl->get_new_row("", '<span class="WarningText">Uživatel nemá účet</span>');
}
echo $data_tbl->get_footer()."\n";
?>

<BR><BUTTON TYPE="button" onclick="javascript:close_popup();">Zavřít</BUTTON><BUTTON TYPE="submit" class="left-margin-50px">Uložit</BUTTON></TD></TR>
</CENTER>
</TD>
<TD width="2%"></TD>
</TR>
</TABLE>
</FORM>

<?
HTML_Footer();
?>

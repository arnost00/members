<? /* trenerova stranka - editace clenu oddilu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Èlenská základna - Editace uživatele');
?>
<CENTER>


<?
// id je z tabulky "users"
@$vysledek=MySQL_Query("SELECT * FROM ".TBL_USER." WHERE id = '$usr->user_id' LIMIT 1");
@$zaznam=MySQL_Fetch_Array($vysledek);
$update=$usr->user_id;
include ("./common_user.inc.php");
?>
<BR><hr><BR>
<? include "./user_new.inc.php"; ?>
</CENTER>

<?php /* adresar clenu oddilu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Adresář členů oddílu');
?>
<TABLE width="95%" border="0">
<TR>
<TD align="right">
<? if ( IsLoggedManager() || IsLoggedRegistrator() || IsLoggedSmallAdmin() || IsLoggedAdmin()) { ?>
<a href="javascript:open_win_ex('./export_directory.php','',600,600);">Export adresáře</a>
<? } ?>
</TD></TR>
</TABLE>
<br>
<? if ( IsLogged() ) { ?>
<script language="javascript">

	/*	"width=500,height=400"	*/

	javascript:set_default_size(500,540);
</script>
<? } ?>

<CENTER>
<?
require_once ("./common_user.inc.php");

$columns = 'id, prijmeni,jmeno,email,hidden,reg';
$query = "SELECT ".$columns." FROM ".TBL_USER." ORDER BY sort_name ASC";
@$vysledek=query_db($query);

if (($vysledek != FALSE) && mysqli_num_rows($vysledek) > 0)
{ // aspon jeden zaznam
$data_tbl = new html_table_mc();
$col = 0;

$data_tbl->set_header_col($col++,'Příjmení',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$data_tbl->set_header_col_with_help($col++,'Reg.č.',ALIGN_LEFT,"Registrační číslo");
if (IsLogged())
	$data_tbl->set_header_col($col++,'Email',ALIGN_LEFT);
else if ($g_mail_in_public_directory)
	$data_tbl->set_header_col($col++,'Email *',ALIGN_LEFT);
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

while ($zaznam=mysqli_fetch_array($vysledek))
{
	if ($zaznam["hidden"] == 0)
	{
		$row = array();
		if ( IsLogged() )
		{
			$row[] = "<A href=\"javascript:open_win('./view_address.php?id=".$zaznam["id"]."','')\" class=\"adr_name\">".$zaznam["prijmeni"]."</A>";
		}
		else
			$row[] = $zaznam["prijmeni"];
		$row[] = $zaznam["jmeno"];
		$row[] = $g_shortcut.RegNumToStr($zaznam["reg"]);

		if (IsLogged())
			$row[] = GetEmailHTML(ParseEmails($zaznam["email"]));
		else if($g_mail_in_public_directory)
			$row[] = GetEmailSecuredHTML(ParseEmails($zaznam["email"]));
		echo $data_tbl->get_new_row_arr($row)."\n";
	}
}

echo $data_tbl->get_footer()."\n";
if (!IsLogged() && $g_mail_in_public_directory)
{
	$n = rand(0,1);
	$char_at = ($n) ? '&#64;' : '&#x40;';
?>
* E-mailové adresy se zobrazují v upravené podobě.<br>
E-mailové adresy v neupravené podobě se zobrazují po přihlášení se do systému.<br>
Jiná možnost jak získat správnou e-mailovou adresu je nahrazením textu "(zavináč)" znakem "<? echo($char_at);?>" a "(tečka)" znakem ".".<br>
<?
}
} // aspon jeden zaznam
else
{
	echo "Seznam členů oddílu je prázdný.<BR>";
}
?>
<BR>
</CENTER>
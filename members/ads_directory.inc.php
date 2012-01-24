<?php /* adminova stranka - editace clenu oddilu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Èlenská základna - Administrace',false);
?>
<CENTER>

<script language="JavaScript">
<!--
function confirm_delete(name) {
	return confirm('Opravdu chcete smazat èlena oddílu ? \n Jméno èlena : "'+name+'" \n Èlen bude nenávratnì smazán !!');
}

-->
</script>

<?
include "./common_user.inc.php";

@$vysledek=MySQL_Query("SELECT id,prijmeni,jmeno,reg,hidden FROM ".TBL_USER." ORDER BY sort_name ASC");

if (IsSet($result) && is_numeric($result) && $result != 0)
{
	require('./const_strings.inc.php');
	$res_text = GetResultString($result);
	Print_Action_Result($res_text);
}

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Poø.è.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Pøíjmení',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Reg.è.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Info',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Práva',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$i=1;
while ($zaznam=MySQL_Fetch_Array($vysledek))
{
	if ($zaznam['hidden'] == 0) 
	{
		$row = array();
		$row[] = $i++;
		$row[] = $zaznam['prijmeni'];
		$row[] = $zaznam['jmeno'];
		$row[] = RegNumToStr($zaznam['reg']);
		$acc = '<code>';
		$acc_r = '<code>';
		$val=GetUserAccountId_Users($zaznam['id']);
		if ($val)
		{
			$vysl2=MySQL_Query("SELECT * FROM ".TBL_ACCOUNT." WHERE id = '$val'");
			$zaznam2=MySQL_Fetch_Array($vysl2);
			if ($zaznam2 != FALSE)
			{
				$acc .= 'U';
				if ($zaznam2['locked'] != 0) 
					$acc .= '<span class="WarningText"> L</span>';
				$acc_r .= ($zaznam2['policy_news'] == 1) ? 'N ' : '. ';
				$acc_r .= ($zaznam2['policy_regs'] == 1) ? 'P ' : '. ';
				$acc_r .= ($zaznam2['policy_mng'] == _MNG_BIG_INT_VALUE_) ? 'T ' : '. ';
				$acc_r .= ($zaznam2['policy_mng'] == _MNG_SMALL_INT_VALUE_) ? 't ' : '. ';
				$acc_r .= ($zaznam2['policy_adm'] == 1) ? 'S' : '.';
			}
			else
			{
				$acc .= '.';
				$acc_r .= '. . . . .';
			}
		}
		else
		{
			$acc .= '.';
			$acc_r .= '. . . . .';
		}
		$row[] = $acc.'</code>';
		$row[] = $acc_r.'</code>';
		$row[] = '<A HREF="./user_edit.php?id='.$zaznam['id'].'">Edit</A>&nbsp;/&nbsp;<A HREF="./user_login_edit.php?id='.$zaznam["id"].'">Úèet</A>&nbsp;/&nbsp;<A HREF="./user_del_exc.php?id='.$zaznam["id"]."\" onclick=\"return confirm_delete('".$zaznam["jmeno"].' '.$zaznam["prijmeni"]."')\" class=\"Erase\">Smazat</A>";
		echo $data_tbl->get_new_row_arr($row)."\n";
	}
}
echo $data_tbl->get_footer()."\n";

echo '<BR><BR>';
echo '(U znaèí že èlen má vytvoøen úèet.)<BR>';
echo '(Èervené <span class="WarningText">L</span> znaèí že úèet je zablokován. Tj. nejde se na nìj pøihlásit.)<BR>';
echo '<BR><hr><BR>';

include "./user_new.inc.php";
?>
<BR>
</CENTER>
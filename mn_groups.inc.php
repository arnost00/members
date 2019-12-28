<?php /* trenerova stranka - prirazeni clenu trenerum */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Přiřazení členů malým trenérům');
?>
<CENTER>

<script language="javascript">
<!-- 
	/*	"status=yes,width=600,height=460"	*/

	javascript:set_default_size(600,520);
	
function confirm_add() {
	return confirm('Opravdu chcete povolit platícího trenéra?');
}
function confirm_reset() {
	return confirm('Opravdu chcete resetovat trenéra?');
}

//-->
</script>

<?
require_once "./common_user.inc.php";

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Poř.č.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Příjmení',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$data_tbl->set_header_col_with_help($col++,'Reg.č.',ALIGN_CENTER,"Registrační číslo");
$data_tbl->set_header_col($col++,'Trenér',ALIGN_CENTER);
if (IsLoggedSmallAdmin())
	$data_tbl->set_header_col($col++,'Platící trenér',ALIGN_CENTER);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

// u.id, u.prijmeni, u.jmeno, u.reg, u.hidden, u.lic, u2.jmeno as ch_jmeno, u2.prijmeni as ch_prijmeni
// u.*

$query='SELECT u.*, u2.jmeno as ch_jmeno, u2.prijmeni as ch_prijmeni, u2.hidden as ch_hidden FROM '.TBL_USER.' as u LEFT JOIN '.TBL_USER.' as u2 ON u.chief_id = u2.id ORDER BY sort_name ASC';
@$vysledek=mysqli_query($db_conn, $query);

$i=1;
while ($zaznam=mysqli_fetch_array($vysledek))
{
	if (!$zaznam['hidden'])
	{
		$row = array();
		$row[] = $i++;
		$row[] = $zaznam['prijmeni'];
		$row[] = $zaznam['jmeno'];
		$row[] = RegNumToStr($zaznam['reg']);
		if ($zaznam['ch_jmeno'] == NULL || $zaznam['ch_prijmeni'] == NULL)
			$row[] = "<A HREF=\"javascript:open_win('./mng_edit.php?id=".$zaznam['id']."','')\">Edit</A>";
		else
		{
			if ($zaznam['ch_hidden'])
				$row[] = '<span class="TextAlert2">'.$zaznam['ch_jmeno'].' '.$zaznam['ch_prijmeni'].'</span>&nbsp;/&nbsp;<a HREF="./mn_groups_exc.php?type=1&id='.$zaznam["id"].'" onclick="return confirm_reset()" class="Erase">Reset</A>';
			else
				$row[] = $zaznam['ch_jmeno'].' '.$zaznam['ch_prijmeni'].'&nbsp;/&nbsp;'. "<A HREF=\"javascript:open_win('./mng_edit.php?id=".$zaznam['id']."','')\">Edit</A>";
		}
		if (IsLoggedSmallAdmin())
		{
			$val = '';
			if ($zaznam['chief_id'] != null && $zaznam['chief_id'] != 0)
			{
				if ($zaznam['chief_pay'] != null)
				{
					$query2='SELECT * FROM '.TBL_USER.' WHERE id = '.$zaznam['chief_pay'].' LIMIT 1';
					@$vysledek2=mysqli_query($db_conn, $query2);
					if ($zaznam2=mysqli_fetch_array($vysledek2))
					{
						if ($zaznam['chief_pay'] != $zaznam['chief_id'] || $zaznam2['hidden']) 
							$val.='<span class="TextAlert2">';
						$val.=$zaznam2['jmeno'].' '.$zaznam2['prijmeni'];
						if ($zaznam['chief_pay'] != $zaznam['chief_id'] || $zaznam2['hidden']) 
							$val.='</span>';
						$val.='&nbsp;/&nbsp;';
						$val.= '<a HREF="./mn_groups_exc.php?type=2&id='.$zaznam["id"].'" onclick="return confirm_reset()" class="Erase">Reset</A>';
					}
					else
						$val.=$zaznam['chief_pay'];
				}
				else
					$val.= '<a HREF="./mn_groups_exc.php?type=3&id='.$zaznam["id"].'" onclick="return confirm_add()">Povol</A>';
			}
			else
				$val.='-';
			$row[] = $val;
		}
		echo $data_tbl->get_new_row_arr($row)."\n";
	}

}

echo $data_tbl->get_footer()."\n";

?>
<BR>
</CENTER>
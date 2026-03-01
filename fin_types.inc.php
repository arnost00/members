<?php /* zavody - zobrazeni zavodu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Přehled typů oddílových příspěvků a plateb');
?>
<CENTER>
<script language="JavaScript">
<!--
function confirm_delete() {
	return confirm('Opravdu chcete smazat tento typ oddílového příspěvku?');
}
function confirm_delete_pay() {
	return confirm('Opravdu chcete smazat tuto definici plateb?');
}
-->
</script><?

require_once ('./common_race.inc.php');
require_once ('./common_fin.inc.php');

$configured = []; // all payements in tree

function payementRow ( $field, $nazev ) {

	global $configured, $g_payrule_keys, $g_zebricek;

	$row = array();
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = $nazev;

	foreach ( $configured as $key => $config ) {

		$firstconfig = reset($config);
		$val = $firstconfig[0][$field]; // any first record in array
		switch ( $field ) {
			case 'typ' : $row[] = GetRaceTypeName ($val); break;
			case 'termin' : $row[] = ( $val < 0 ) ? ( -$val . '+' ) : $val; break;
			case 'zebricek' :
				$flags = [];
				foreach ( $g_zebricek as $zeb ) {
					if ( ( $val & $zeb['id'] ) != 0 ) {
						$flags[] = $zeb['nm'];
					}
				}
				$row[] = implode ( '<BR>', $flags );
				break;
			default : $row[] = $val;
		}
	}

	return $row;
}

$query = "SELECT * FROM ".TBL_FINANCE_TYPES.' ORDER BY id';
@$vysledek=query_db($query);

$query = "SELECT * FROM ".TBL_PAYRULES.' ORDER BY ';

$comma = '';
foreach ( $g_payrule_keys as [$key,$label] ) {
	$query .= $comma . $key;
	$comma = ',';
}

@$platby=query_db($query);

if ($vysledek === FALSE || $platby == FALSE )
{
	echo('Chyba v databázi, kontaktuje administrátora.<br>');
}
else
{
	$num_rows = mysqli_num_rows($vysledek);
	if ($num_rows > 0)
	{

		while ($zaznam=mysqli_fetch_array($platby))
		{
			$key = '';
			foreach ($g_payrule_keys as $payKey) {
				$key .=  $payKey[0] . '=' . $zaznam[$payKey[0]] ?? '';
			}
			$configured[$key][$zaznam['finance_type']][] = $zaznam;
		}

		$data_tbl = new html_table_mc();
		$col = 0;
		$data_tbl->set_header_col($col++,'Id',ALIGN_CENTER);
		$data_tbl->set_header_col($col++,'Název',ALIGN_LEFT);
		$data_tbl->set_header_col($col++,'Popis',ALIGN_LEFT);
		$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);
		foreach ( $configured as $records ) {
			if ( array_key_exists ('', $records) ) {
				// generic type configuration in column
				$id = $records[''][0]['id'];
				$data_tbl->set_header_col($col++,'<A HREF="./fin_payrule_edit.php?id='.$id.'" title="Editovat platby" style="color:inherit;">&#9997;</A>&nbsp;/&nbsp;<A HREF="./fin_payrule_del_exc.php?id='.$id.'" onclick="return confirm_delete_pay()" class="Erase" title="Smazat platby">&#10799;</A>',ALIGN_CENTER);
			} else {
				$data_tbl->set_header_col($col++, '',ALIGN_CENTER);
			}
		}
		$data_tbl->set_header_col($col++,'<A HREF="./fin_payrule_edit.php?new" title="Přidat platby" style="color:inherit;">+</A>',ALIGN_CENTER);

		echo $data_tbl->get_css()."\n";
		echo $data_tbl->get_header()."\n";
		echo $data_tbl->get_header_row()."\n";

		foreach ($g_payrule_keys as [$key, $label]) {
			echo $data_tbl->get_new_row_arr(payementRow($key, $label));
		}

		echo $data_tbl->get_break_row(true);

		while ($zaznam=mysqli_fetch_array($vysledek))
		{
			$row = array();
			$row[] = $zaznam['id'];
			$row[] = $zaznam['nazev'];
			$row[] = nl2br ($zaznam['popis']);
			$row[] = '<A HREF="./fin_type_edit.php?id='.$zaznam['id'].'" title="Editovat \''.$zaznam['nazev'].'\'">&#9997;</A>&nbsp;/&nbsp;<A HREF="./fin_type_del_exc.php?id='.$zaznam['id'].'" onclick="return confirm_delete()" class="Erase" title="Smazat \''.$zaznam['nazev'].'\'">&#10799;</A>';

			foreach ( $configured as $key => $config ) {
				$typedconfigs = $config[$zaznam['id']] ?? $config[''] ?? null; // exact or undefined type

				$valcell = '';

				if ( $typedconfigs ) {
					foreach ( $typedconfigs as $typedconfig ) {
						$val = '';
						if ( $typedconfig['uctovano'] != 0 && $typedconfig['uctovano'] !== null && $typedconfig['uctovano'] != 7 ) {
							foreach ( $g_uctovano as $uct ) {
								if ( ( $typedconfig['uctovano'] & $uct['id'] ) != 0 ) {
									$val .= $uct['char'];
								}
							}
						}
						switch ( $typedconfig['druh_platby'] ) {

							case 'P' : $val .= $typedconfig['platba'] . ' Kč'; break; // direct 
							case 'R' : $val .= "&Delta; ";                     // of difference
							default :
								$val .= $typedconfig['platba'] . '%'; // % of diference or whole
						}				
						if ( array_key_exists ( $zaznam['id'], $config) ) {
							// multiple types, in place edit
							$val = '<A HREF="./fin_payrule_edit.php?id='.$typedconfig['id'].'" title="Editovat">' .$val . '</A>&nbsp;/&nbsp;<A HREF="./fin_payrule_del_exc.php?id='.$typedconfig['id'].'" onclick="return confirm_delete_pay()" class="Erase" title="Smazat">&#10799;</A>';
						}
						$valcell .= $val . '<BR>';
					}
				}

				$row[] = $valcell;
			}

			echo $data_tbl->get_new_row_arr($row)."\n";
		}

		echo $data_tbl->get_footer()."\n";
	}
}
require_once ('fin_type_edit.inc.php');
?>



</CENTER>

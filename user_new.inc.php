<?php /* adminova stranka - vlozeni clena */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>

<script language="JavaScript">
<!--
function focusOn(elem)
{
	console.log(elem);
	document.getElementById(elem).scrollIntoView();
}
-->
</script>

<?
if (IsLogged())
{
	if(IsSet($update))
		DrawPageSubTitle('Editace údajů vybraného člena oddílu');
	else
	{
		DrawPageSubTitle('Vložení nového člena', 'form_create_user');
		$zaznam['prijmeni'] = '';
		$zaznam['jmeno'] = '';
		$zaznam['reg'] = '';
		$zaznam['si_chip'] = '';
		$zaznam['datum'] = '';
		$zaznam['adresa'] = '';
		$zaznam['mesto'] = '';
		$zaznam['psc'] = '';
		$zaznam['email'] = '';
		$zaznam['tel_domu'] = '';
		$zaznam['tel_zam'] = '';
		$zaznam['tel_mobil'] = '';
		$zaznam['poh'] = 'H';
		$zaznam['lic'] = 'C';
		$zaznam['lic_mtbo'] = '-';
		$zaznam['lic_lob'] = '-';
		$zaznam['hidden'] = 0;
		$zaznam['fin'] = '';
		$zaznam['rc'] = '';
		$zaznam['narodnost'] = GC_DEFAULT_NATIONALITY;
		$zaznam['finance_type'] = 0;
	}
	$self_edit = IsSet($self_edit) ? (bool)$self_edit : false;

?>
<FORM METHOD=POST ACTION="./user_new_exc.php<?if (IsSet($update)) echo "?update=".$update?>">
<?
function GetLicenceComboBox($lic_name, $lic_value)
{
	$value = '<select name=\''.$lic_name.'\'>';
	$value .= '<option value=\'E\''.(($lic_value=='E') ? ' SELECTED' : '').'>E</option>';
	$value .= '<option value=\'A\''.(($lic_value=='A') ? ' SELECTED' : '').'>A</option>';
	$value .= '<option value=\'B\''.(($lic_value=='B') ? ' SELECTED' : '').'>B</option>';
	$value .= '<option value=\'C\''.(($lic_value=='C') ? ' SELECTED' : '').'>C</option>';
	$value .= '<option value=\'D\''.(($lic_value=='D') ? ' SELECTED' : '').'>D</option>';
	$value .= '<option value=\'R\''.(($lic_value=='R') ? ' SELECTED' : '').'>R</option>';
	$value .= '<option value=\'-\''.(($lic_value=='-') ? ' SELECTED' : '').'>-</option>';
	$value .= '</select>';
	return $value;
}

function GetFinTypeLine($data_tbl, $update, $field_name, $type_value) 
{
    $finTypesList = query_db("SELECT id, nazev FROM " . TBL_FINANCE_TYPES);

    $value = '';

    if ($finTypesList !== FALSE && $finTypesList !== null) {
		// only when types defined
        $displayValue = null;
        $selectOptions = '';
        $isDefined = 0;

        while ($displayType = MySQLi_Fetch_Array($finTypesList)) {
			if ( $displayType['id'] == $type_value ) {
				$isDefined = 1;
				$selected = ' selected';
			} else {
				$selected = '';
			}

            $selectOptions .= '<option value="' . $displayType['id'] . '"' . $selected . '>' . htmlspecialchars($displayType['nazev']) . '</option>';

            if ($displayType['id'] == $type_value) {
                $displayValue = $displayType['nazev'];
            }
        }

		// row undefined - 0
        $selectOptions .= '<option value="0"' . ( $isDefined ? '' : ' selected' ) . '>Není definováno</option>';

		// the same check is done in user_new_exc.php unify if changed
        if (IsLoggedFinance() || !IsSet($update)) // or may be in february for everyone
        {
            $input = '<select name="' . $field_name . '">' . $selectOptions . '</select>';
        } else {
            $input = '<input type="text" value="' . htmlspecialchars($displayValue ?? 'nezadán') . '" readonly />';
        }
        $value = $data_tbl->get_new_row('Typ příspěvku', $input);
    }

    return $value;
}
$data_tbl = new html_table_form("createUser");
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";

require_once ('country_list_array.inc.php');

if ($self_edit)
{
	echo $data_tbl->get_new_row('Příjmení', $zaznam["prijmeni"]);
	echo $data_tbl->get_new_row('Jméno', $zaznam["jmeno"]);
	echo $data_tbl->get_new_row('Registrační číslo', $g_shortcut.RegNumToStr($zaznam['reg']));
	echo '<INPUT TYPE="hidden" NAME="prijmeni" VALUE="'.$zaznam["prijmeni"].'">';
	echo '<INPUT TYPE="hidden" NAME="jmeno" VALUE="'.$zaznam["jmeno"].'">';
	echo '<INPUT TYPE="hidden" NAME="reg" VALUE="'.$g_shortcut.RegNumToStr($zaznam['reg']).'">';
}
else
{
	echo $data_tbl->get_new_row('Příjmení', '<INPUT onfocus="focusOn(\'form_create_user\');" accessKey="C" TYPE="text" NAME="prijmeni" SIZE=30 MAXLENGTH=30 VALUE="'.$zaznam["prijmeni"].'">');
	echo $data_tbl->get_new_row('Jméno', '<INPUT TYPE="text" NAME="jmeno" SIZE=30 MAXLENGTH=20 VALUE="'.$zaznam["jmeno"].'">');
	$find_reg_text = (!IsSet($update)) ? ' <a href="javascript:open_win_ex(\'./find_reg.php\',\'\',600,400)">Hledání volných reg.č.</a>': '';
	echo $data_tbl->get_new_row('Registrační číslo', $g_shortcut.'&nbsp;&nbsp;<INPUT TYPE="text" NAME="reg" SIZE=4 MAXLENGTH=4 VALUE="'.RegNumToStr($zaznam['reg']).'">'.$find_reg_text);
}
echo $data_tbl->get_new_row('Číslo SI čipu', '<INPUT TYPE="text" NAME="si" SIZE=10 MAXLENGTH=9 VALUE="'.$zaznam["si_chip"].'">');
if ($self_edit)
{
	echo $data_tbl->get_new_row('Datum narození', SQLDate2String($zaznam["datum"]));
	echo $data_tbl->get_new_row('Národnost', get_country_string($zaznam['narodnost']));
	echo '<INPUT TYPE="hidden" NAME="datum" VALUE="'.SQLDate2String($zaznam["datum"]).'">';
	echo '<INPUT TYPE="hidden" NAME="narodnost" VALUE="'.$zaznam["narodnost"].'">';
	
}
else
{
	echo $data_tbl->get_new_row('Datum narození', '<INPUT TYPE="text" NAME="datum" SIZE=10 VALUE="'.SQLDate2String($zaznam["datum"]).'">&nbsp;&nbsp;(DD.MM.RRRR)');
	$country_sel = '<SELECT NAME="narodnost">';
	$country_sel .= generate_combobox_data($zaznam['narodnost']);
	$country_sel .= '</SELECT>';
	echo $data_tbl->get_new_row('Národnost', $country_sel);
}
echo $data_tbl->get_new_row('Adresa', '<INPUT TYPE="text" NAME="adresa" SIZE=60 MAXLENGTH=50 VALUE="'.$zaznam["adresa"].'">');
echo $data_tbl->get_new_row('Město', '<INPUT TYPE="text" NAME="mesto" SIZE=30 MAXLENGTH=25 VALUE="'.$zaznam["mesto"].'">');
echo $data_tbl->get_new_row('PSČ', '<INPUT TYPE="text" NAME="psc" SIZE=10 MAXLENGTH=6 VALUE="'.$zaznam["psc"].'">');
echo $data_tbl->get_new_row('Email', '<INPUT TYPE="text" NAME="email" SIZE=60 MAXLENGTH=50 VALUE="'.$zaznam["email"].'">');
echo $data_tbl->get_new_row('Tel. domů', '<INPUT TYPE="text" NAME="domu" SIZE=20 MAXLENGTH=25 VALUE="'.$zaznam["tel_domu"].'">');
echo $data_tbl->get_new_row('Tel. zaměstnání', '<INPUT TYPE="text" NAME="zam" SIZE=20 MAXLENGTH=25 VALUE="'.$zaznam["tel_zam"].'">');
echo $data_tbl->get_new_row('Mobil', '<INPUT TYPE="text" NAME="mobil" SIZE=20 MAXLENGTH=25 VALUE="'.$zaznam["tel_mobil"].'">');
if ($self_edit)
{
	echo $data_tbl->get_new_row('Pohlavi', $zaznam["poh"]);
	echo '<INPUT TYPE="hidden" NAME="poh" VALUE="'.$zaznam["poh"].'">';
}
else
{
	$value = '<select name=\'poh\'>';
	$value .= '<option value=\'H\' '.(($zaznam["poh"]=="H") ? 'SELECTED' : '').'>'.GC_KATEG_M.'</option>';
	$value .= '<option value=\'D\' '.(($zaznam["poh"]=="D") ? 'SELECTED' : '').'>'.GC_KATEG_W.'</option>';
	$value .= '</select>';
	echo $data_tbl->get_new_row('Pohlavi', $value);
}
echo $data_tbl->get_new_row('Licence OB', GetLicenceComboBox('lic',$zaznam["lic"]));
echo $data_tbl->get_new_row('Licence MTBO', GetLicenceComboBox('lic_mtbo',$zaznam["lic_mtbo"]));
echo $data_tbl->get_new_row('Licence LOB', GetLicenceComboBox('lic_lob',$zaznam["lic_lob"]));
echo GetFinTypeLine($data_tbl, $update, 'finance_type',$zaznam["finance_type"]);

if (IsLoggedSmallAdmin())
{
	echo $data_tbl->get_new_row_text('', '<INPUT TYPE="checkbox" NAME="hidden" SIZE=15 VALUE="1"'.(($zaznam["hidden"] == 1) ? ' checked' : '').'> Skrytý člen (vidí ho jen admin)');
}
if (IsLoggedSmallAdmin() || !IsSet($update))
{
	echo $data_tbl->get_new_row('Rodné číslo', '<INPUT TYPE="text" NAME="rc" SIZE=30 MAXLENGTH=10 VALUE="'.$zaznam["rc"].'"> (9999999999)');
}
/*
	prijmeni 	varchar(30) 
	jmeno 	varchar(20)
	adresa 	varchar(50)
	mesto 	varchar(25)
	psc 	varchar(6)
	tel_domu 	varchar(25) 
	tel_zam 	varchar(25) 
	tel_mobil 	varchar(25) 
	email 	varchar(50)
	rc 	varchar(10)
*/

echo $data_tbl->get_empty_row();
echo $data_tbl->get_new_row('','<INPUT TYPE="submit" VALUE="'.((IsSet($update)) ? 'Změnit údaje člena' : 'Vytvořit nového člena').'">');
echo $data_tbl->get_empty_row();
echo $data_tbl->get_new_row_simple('<b>Upozornění:</b> Zadané jméno a další údaje se používají i pro potřeby registrace závodníka do centrální registrace a při prihlašování na závody.');
echo $data_tbl->get_footer()."\n";
?>
</FORM>
<?
}
?>

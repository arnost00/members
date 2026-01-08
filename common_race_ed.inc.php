<?php if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?

// generate table row with current set date and if system date is different a button to modify
// the button has system date displayed, but sets date shifted by current offset in days
function generateDateField($connector, $fieldName, $currentDate, $systemDate, $currentOfffset) {
	$displayDate = Date2String($currentDate);
	$buttonHtml = '';
	if ( !empty ( $systemDate ) ) {
		$systemDisplayDate = Date2String($systemDate);
		$systemOffsetDate = Date2String($systemDate+$currentOfffset*86400);

		if ($displayDate !== $systemOffsetDate) {
			$buttonHtml = '<button type="button" onclick="document.getElementById(\'' . $fieldName . '\').value=\'' . $systemOffsetDate . '\'">' . "\u{21D0} " . $connector->getSystemName() .' (' . $systemDisplayDate . ')</button>';
		}
	}

	return '<TD class="DataValue">
				<INPUT TYPE="text" ID="' . $fieldName . '" NAME="' . $fieldName . '" SIZE=8 value="' . $displayDate . '">&nbsp;&nbsp;(DD.MM.RRRR)
				' . $buttonHtml . '
			</TD>';
}

function generateTextFieldWithValidator($fieldValue,$uiSize,$rc_arr)
{
	return '<TD class="DataError">
				<INPUT TYPE="text" ID="'.$rc_arr['input'].'" NAME="'.$rc_arr['input'].'" SIZE='.$uiSize.'
				maxlength='.$rc_arr['len'].' value="'.$fieldValue.'" oninput="'.$rc_arr['func'].'">
				<div id="'.$rc_arr['msg'].'"></div>
			</TD>';
}

// defined data for race new & edit 
$rc_form['name']['input'] = 'nazev';
$rc_form['name']['len'] = 70;

$rc_form['misto']['input'] = 'misto';
$rc_form['misto']['len'] = 50;

foreach ($rc_form as &$rc) {
	$rc['msg'] = $rc['input'].'Msg';
	$rc['func'] = "raceEditValidateLength('".$rc['input']."','".$rc['msg']."',".$rc['len'].");";
}
//print_r($rc_form);

?>
<SCRIPT LANGUAGE="JavaScript">

// validace delky textu
function raceEditValidateLength(inputId,messageId, maxLength) {
	var input = document.getElementById(inputId);
	var message = document.getElementById(messageId);
	
	if (input.value.length > maxLength) {
		message.textContent = "Zadaný text je příliš dlouhý";
	} else {
		message.textContent = ""; // Žádná chyba
	}
}

// na konci nacitani provezt kontrolu vsech vstupu
function raceEditDocuOnLoad() {
<?
foreach ($rc_form as &$rc) {
	echo($rc['func']);
}
?>
}
</SCRIPT>

<?

function insertDocuOnLoad()
{
	return '<SCRIPT>document.onload=raceEditDocuOnLoad();</SCRIPT>';
}





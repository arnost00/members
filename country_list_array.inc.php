<?
function get_county_list_array()
{
	$myFile = "country-list-iso-codes_cz.txt";
	$fh = fopen($myFile, 'r');
	$theData = fread($fh, filesize($myFile));
	fclose($fh);
	$data = explode("\n",$theData);
	foreach($data as $row)
	{
//		list($cd,$nm) = explode(':',$row);
//		$data2[$cd] = $nm;
		$data2[] = explode(':',$row);
	}
	return $data2;
}

function generate_combobox_data($selected) 
{
	$dta = get_county_list_array();
	$text = '';
	foreach($dta as $row)
	{
		$text .= '<option value="'.$row[0].'"';
		if ($selected == $row[0])
			$text .= ' selected';
		$text .= '>'.$row[1];
		$text .= '</option>';
	}
	return $text;
}

function get_country_string($code)
{
	$myFile = "country-list-iso-codes_cz.txt";
	$fh = fopen($myFile, 'r');
	$theData = fread($fh, filesize($myFile));
	fclose($fh);
	$data = explode("\n",$theData);
	foreach($data as $row)
	{
		$data2 = explode(':',$row);
		if ($data2[0] == $code)
			return $data2[1];
	}
	return '';
}
?>


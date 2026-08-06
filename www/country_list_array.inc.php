<?

// fix the path so that it also works when importing
$COUNTRY_ISO_CODES_FILE = dirname(__FILE__) . "/country-list-iso-codes_cz.txt";

function get_county_list_array()
{
	global $COUNTRY_ISO_CODES_FILE;

	$file = fopen($COUNTRY_ISO_CODES_FILE, 'r');
	$data = fread($file, filesize($COUNTRY_ISO_CODES_FILE));
	fclose($file);
	
	$data = explode("\n", $data);
	
	foreach($data as $row)
	{
		// list($cd,$nm) = explode(':',$row);
		// $data2[$cd] = $nm;
		$data2[] = explode(':', trim($row)); // also trim whitespaces
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
	global $COUNTRY_ISO_CODES_FILE;

	$file = fopen($COUNTRY_ISO_CODES_FILE, 'r');
	$data = fread($file, filesize($COUNTRY_ISO_CODES_FILE));
	fclose($file);
	
	$data = explode("\n", $data);
	
	foreach($data as $row)
	{
		$data2 = explode(':', trim($row)); // also trim whitespaces
		if ($data2[0] == $code) return $data2[1];
	}
	return '';
}
?>


<?
if (!defined('ENTRY_EXPORT_CLASS_INCLUDED'))
{
	define('ENTRY_EXPORT_CLASS_INCLUDED', 1);
//==================================================================
// CSOB entry export class
//==================================================================

define('SPACE_CHAR',' ');

define('KAT_LEN',10);
define('SI_LEN',6);
define('SI_LEN2005',10);
define('NAME_LEN',25);

//==================================================================

class csob_entry_export
{
//protected
	public $data;
	public $version;
	public $date_born;
	public $shortcut;
	
	function csob_entry_export($version, $date_born, $shortcut)
	{
		$this->version = $version;
		$this->date_born = $date_born;
		$this->shortcut = $shortcut;
		$this->data = array();
	}

	//__________________________________________________________________
	public function add_line($prijmeni, $jmeno, $reg, $lic, $kat, $si, $pozn, $date_born = '0000-00-00')
	{
		$line = array();
		$line['prijmeni'] = $prijmeni;
		$line['jmeno'] = $jmeno;
		$line['reg'] = $reg;
		$line['lic'] = $lic;
		$line['kat'] = $kat;
		$line['si'] = $si;
		$line['pozn'] = $pozn;
		$line['date_born'] = $date_born;
		$this->data[] = $line;
	}

	//__________________________________________________________________
	public function generate()
	{
		$text = '';
		foreach($this->data as $zaznam)
		{
			$str = RegNumToStr($zaznam['reg']).SPACE_CHAR;
			$str .= str_pad($zaznam['kat'],KAT_LEN,SPACE_CHAR).SPACE_CHAR;
			if($this->version == 1)
				$str .= str_pad($zaznam['si'],SI_LEN2005,'0',STR_PAD_LEFT).SPACE_CHAR;
			else
				$str .= str_pad($zaznam['si'],SI_LEN,SPACE_CHAR).SPACE_CHAR;
			$str .= str_pad($zaznam['prijmeni'].' '.$zaznam['jmeno'],NAME_LEN,SPACE_CHAR).SPACE_CHAR;
			$str .= $zaznam['lic'];
			if($this->date_born == 1)
			{	// datum narození (59-64) ve tvaru rrmmdd
				$str .= SPACE_CHAR.SQLDate2StringReg($zaznam['date_born']);
			}
			$str .= SPACE_CHAR;
			$str .= $zaznam['pozn'];
			$text .= $this->shortcut.$str;
			$text .= "\n";
		}
		return $text;
	}
}

//==================================================================
}	// define (ENTRY_EXPORT_CLASS_INCLUDED)
?>
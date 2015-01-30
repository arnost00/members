<?
//==================================================================
// CSOB entry export class
//==================================================================

define('SPACE_CHAR',' ');
define('SEMICOLON_CHAR',';');

class CSOB_Export_Entry
{
//protected
	public $data;
	public $shortcut;

	function CSOB_Export_Entry($shortcut)
	{
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
	public function generate($with_born_dates = false)
	{
		define('KAT_LEN',10);
		define('SI_LEN2005',10);
		define('NAME_LEN',25);

		$text = '';
		foreach($this->data as $zaznam)
		{
			$str = RegNumToStr($zaznam['reg']).SPACE_CHAR;
			$str .= mb_str_pad($zaznam['kat'],KAT_LEN,SPACE_CHAR).SPACE_CHAR;
			$str .= mb_str_pad($zaznam['si'],SI_LEN2005,SPACE_CHAR).SPACE_CHAR;
			$str .= mb_str_pad($zaznam['prijmeni'].' '.$zaznam['jmeno'],NAME_LEN,SPACE_CHAR).SPACE_CHAR;
			$str .= $zaznam['lic'];
			if($with_born_dates != false)
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
// ORIS exports
//==================================================================

class ORIS_Export
{
//protected
	public $data;
	public $shortcut;

	function ORIS_Export($shortcut)
	{
		$this->shortcut = $shortcut;
		$this->data = array();
	}

	//__________________________________________________________________
	public function add_line_registration($prijmeni, $jmeno, $reg)
	{
		$line = array();
		$line['prijmeni'] = $prijmeni;
		$line['jmeno'] = $jmeno;
		$line['reg'] = $reg;
		$this->data[] = $line;
	}

	//__________________________________________________________________
	public function generate_registration()
	{
		$text = '';
		foreach($this->data as $zaznam)
		{
			$str = RegNumToStr($zaznam['reg']).SEMICOLON_CHAR;
			$str.= $zaznam['jmeno'].SEMICOLON_CHAR;
			$str.= $zaznam['prijmeni'];
			$text .= $this->shortcut.$str;
			$text .= "\n";
		}
		return $text;
	}
	
	//__________________________________________________________________
	public function add_line_user($prijmeni, $jmeno, $reg, $si_chip, $pohlavi, $narodnost, $date_born, $rc)
	{
		$line = array();
		
		$line['prijmeni'] = $prijmeni;
		$line['jmeno'] = $jmeno;
		$line['reg'] = $reg;
		$line['si_chip'] = $si_chip;
		$line['poh'] = $pohlavi;
		$line['narodnost'] = $narodnost;
		$line['datum'] = $date_born;
		$line['rc'] = $rc;
		$this->data[] = $line;
	}

	//__________________________________________________________________
	public function generate_users()
	{
		$text = '';
		foreach($this->data as $zaznam)
		{
			$str = RegNumToStr($zaznam['reg']).SEMICOLON_CHAR;
			$str .= $zaznam['jmeno'].SEMICOLON_CHAR;
			$str .= $zaznam['prijmeni'].SEMICOLON_CHAR;
			$str .= $zaznam["datum"].SEMICOLON_CHAR;
			if ($zaznam["rc"] == '')
				$str .= $this->SQLDatumToRc($zaznam["datum"],$zaznam["poh"] == 'D').'/0000';
			else
				$str .= substr($zaznam["rc"],0,6).'/'.substr($zaznam["rc"],6);
			$str .= SEMICOLON_CHAR;
			$str .= $zaznam['narodnost'].SEMICOLON_CHAR;
			$str .= ($zaznam["poh"] == 'D') ? 'F' : 'M';
			$str .= SEMICOLON_CHAR;
			$str .= $zaznam["si_chip"];
			$text .= $this->shortcut.$str;
			$text .= "\n";
		}
		return $text;
	}

	//__________________________________________________________________
	public function add_line_contact($reg, $email, $mobil)
	{
		$line = array();
		
		$line['email'] = $email;
		$line['mobil'] = $mobil;
		$line['reg'] = $reg;
		$this->data[] = $line;
	}

	//__________________________________________________________________
	public function generate_contacts()
	{
		$text = '';
		foreach($this->data as $zaznam)
		{
			$str = RegNumToStr($zaznam['reg']).SEMICOLON_CHAR;
			$str .= $zaznam['email'].SEMICOLON_CHAR;
			$str .= $zaznam['mobil'];
			$text .= $this->shortcut.$str;
			$text .= "\n";
		}
		return $text;
	}

	//__________________________________________________________________
	protected function SQLDatumToRc($date,$female)
	{	// for users
		$dat=explode('-',$date);
		if (sizeof($dat) == 3) // YYYY-MM-DD
		{
			$day = (int) $dat[2];
			$month = (int) $dat[1];
			if ($female)
				$month += 50;
			$year = (int) $dat[0];
			$result = mb_str_pad($year % 100,2,'0',STR_PAD_LEFT);
			$result .= mb_str_pad($month,2,'0',STR_PAD_LEFT);
			$result .= mb_str_pad($day,2,'0',STR_PAD_LEFT);
			return $result;
		}
		else
			return '000000';
	}

}

//==================================================================
// CSV exports
//==================================================================

class CSV_Export
{
	protected $data;
	protected $shortcut;
	protected $delim;
	protected $quotes;
	protected $apostrophe;

	function CSV_Export($shortcut, $delim, $quotes, $apostrophe)
	{
		$this->shortcut = $shortcut;
		$this->delim = $delim;
		$this->quotes = (bool)$quotes;
		$this->apostrophe = (bool)$apostrophe;
		$this->data = array();
	}

	//__________________________________________________________________
	public function add_line_user($zaznam)
	{	// complete row from table user
		$this->data[] = $zaznam;
	}

	//__________________________________________________________________
	public function generate_users()
	{
		$text = '';
		$text .= 'příjmeni;jméno;datum narození;reg;email;adresa;mesto;psc;tel.domů;tel.práce; tel.mobilní;si.čip;licence OB;licence MTBO;licence LOB;národnost;';
		if (IsLoggedSmallAdmin() || IsLoggedAdmin())
			$text .= 'rodné číslo;';
		$text .= "\n";

		foreach($this->data as $zaznam)
		{
			$str = '';
			$str .= $this->get_col($zaznam['prijmeni']);
			$str .= $this->delim;
			$str .= $this->get_col($zaznam['jmeno']);
			$str .= $this->delim;
			$str .= $this->get_col(SQLDate2String($zaznam["datum"]));
			$str .= $this->delim;
			$str .= $this->get_col($this->shortcut.RegNumToStr($zaznam['reg']));
			$str .= $this->delim;
			$str .= $this->get_col($zaznam['email']);
			$str .= $this->delim;
			$str .= $this->get_col($zaznam['adresa']);
			$str .= $this->delim;
			$str .= $this->get_col($zaznam['mesto']);
			$str .= $this->delim;
			$str .= $this->get_col($zaznam['psc']);
			$str .= $this->delim;
			$str .= $this->get_col($zaznam['tel_domu']);
			$str .= $this->delim;
			$str .= $this->get_col($zaznam['tel_zam']);
			$str .= $this->delim;
			$str .= $this->get_col($zaznam['tel_mobil']);
			$str .= $this->delim;
			$str .= $this->get_col($zaznam['si_chip']);
			$str .= $this->delim;
			$str .= $this->get_col($zaznam['lic']);
			$str .= $this->delim;
			$str .= $this->get_col($zaznam['lic_mtbo']);
			$str .= $this->delim;
			$str .= $this->get_col($zaznam['lic_lob']);
			$str .= $this->delim;
			$str .= $this->get_col($zaznam['narodnost']);
			$str .= $this->delim;
			if (IsLoggedSmallAdmin() || IsLoggedAdmin())
			{
				$str .= $this->get_col($zaznam['rc']);
				$str .= $this->delim;
			}
			$text .= $str;
			$text .= "\n";
		}
		$text .= "\n";
		return $text;
	}
	
	public function generate_csv($arr_labels, $arr_fields)
	{
		$text = '';
		$text = implode($this->delim, $arr_labels);
		$text .= "\n";

		foreach($this->data as $zaznam)
		{
			$str = '';
			foreach($arr_fields as $field)
			{
				$str .= $this->get_col($zaznam[$field]);
				$str .= $this->delim;
			}

			$text .= $str;
			$text .= "\n";
		}
		$text .= "\n";
		return $text;
	}
	

	protected function get_col($text)
	{
		$str = '';
		if($this->quotes)
			$str .= '"';
		if($this->apostrophe && is_numeric($text))
			$str .= '\'';
		$str .= $text;
		if($this->quotes)
			$str .= '"';
		return $str;
	}
}


//==================================================================
?>
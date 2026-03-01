<?php if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?

require_once "cfg/race_enums.php";

// payement keys - renderer, db
$g_payrule_keys = [
    ['typ', 'Sport', 's'],
    ['typ0', 'Typ akce', 's'],
    ['termin', 'Termín', 'i'],
    ['zebricek', 'Žebříček', 'i'],
];


function CreateFinMailFlag(&$mflags)
{
	global $g_fin_mail_flag_cnt;
	global $g_fin_mail_flag;

	$result = 0;
	for($ii=0; $ii<$g_fin_mail_flag_cnt; $ii++)
	{
		if(isset($mflags[$ii]) && $mflags[$ii] == 1)
			$result += $g_fin_mail_flag [$ii]['id'];
	}
	return $result;
}

function GetFinMailFlagDesc(&$mflags)
{
	global $g_fin_mail_flag_cnt;
	global $g_fin_mail_flag;

	$result = '';
	for($ii=0; $ii<$g_fin_mail_flag_cnt; $ii++)
	{
		if(($mflags & $g_fin_mail_flag[$ii]['id']) != 0)
			$result .= (($result != '')?', ':'').$g_fin_mail_flag [$ii]['nm'];
	}
	return $result;
}

function IsFinanceTypeTblFilled()
{
	global $db_conn;
	
	@$vysledek=query_db("SELECT id FROM ".TBL_FINANCE_TYPES.' ORDER BY id');
	if ($vysledek === FALSE || $vysledek == null)
	{
		return 0;
	}
	else
	{
		return mysqli_num_rows($vysledek);
	}
}

class CheckboxRow {
    private string $name;    // e.g. Kategorie
    private string $key;     // e.g. cat
	private bool $hasAll;    // has all checkbox
	private bool $disabled;  // all entries unmodifiable
	private ?string $namePrefix; // generate input names if set
    private array $entries = [];   // store added entries
	private array $idToEntry = []; // loopback map id → label

	/**
	 * Constructor
	 * @param string $name   Visible name
	 * @param string $key    Unique key for HTML data-key attribute
	 * @param bool   $hasAll Include "all" checkbox
	 * @param bool   $disabled  Unmodifiable all entries
	 */
    public function __construct(string $name, string $key, bool $hasAll = true, string $namePrefix = null, bool $disabled = false) {
        $this->name = $name;
        $this->key  = $key;
		$this->hasAll = $hasAll;
		$this->namePrefix = $namePrefix;
		$this->disabled = $disabled;
    }

    /**
     * Add an entry by unique label, return the entry id
     * @param string     $label   Visible label text
     * @param string     $title   Tooltip text (optional)
     * @param int|string $id      Entry value (optional, defaults to index)
     * @param bool       $checked Pre-checked state
     * @param bool       $active  Add immediately to active list
     */
    public function addEntry(
        string $label,
        ?string $title = null,
        ?string $id = null,
        bool $checked = false,
        bool $active = false
    ): int|string {
		if ( isset($this->entries[$label]) ) {
			// allready exist, might activate
			if ($active) {
				$this->entries[$label]['active'] = true;
			}
			return $this->entries[$label]['id'];
		}

		// create new entry
        $id = ($id === '' ? 'None' : $id) ?? count($this->entries);
        $this->entries[$label] = $entry = [
            'label'   => $label,
            'title'   => $title,
            'id'      => $id,
            'checked' => $checked,
            'active'  => $active
        ];

		// update loopback map for fast id → label lookup
    	$this->idToEntry[$id] = $entry;

		return $id;
    }

	/**
	 * Get label by entry ID, automatically activates the entry if found
	 */
	public function getLabel(int|string $id): ?string {

		$entry = $this->idToEntry[$id];
		if ( isset($entry) && !$entry['active'] ) {
			// activate if not active yet (avoid Copy on write)
			$this->entries[$entry['label']]['active'] = true;
			return $entry['label'] ?? null;
		}
		return null;
	}

    public function setActive(string $label): ?int {
		if ( isset($this->entries[$label]) ) {
			// allready exist, might activate
			$this->entries[$label]['active'] = true;
			return $this->entries[$label]['id'];
		}
        return null;
    }

    /**
     * Render innerHTML
     */
    public function render(): string {
    	$html_pre = $this->name;

 		$all_checked = true;

        $html = '';
		$id = 0;
        foreach ($this->entries as $label => $entry) {
            // if activeIds filter is set, skip others
            if (!$entry['active']) {
                continue;
            }

            $html .= '<input type="checkbox" data-role="one" data-key="' . htmlspecialchars($this->key) . '" value="' . $entry['id'] . '"';
			if ( isset ($this->namePrefix) ) {
				if ( !$this->disabled ) $html .= ' name="' . htmlspecialchars($this->namePrefix) . '[]"'; // name on active only
				$html .= ' id="' . htmlspecialchars($this->namePrefix) . '_' . $id++ . '"';
			}
            if (!empty($entry['title'])) {
                $html .= ' title="' . htmlspecialchars($entry['title']) . '"';
            }
            if ($entry['checked']) {
                $html .= ' checked';
            } else {
				$all_checked = false;
			}
			if ( $this->disabled )
				$html .= ' disabled';
            $html .= '>';
			if ( $entry['checked'] && $this->disabled && isset ($this->namePrefix) ) {
				// additional input field for disabled checkbox
				$html .=  '<input type="hidden" name="'. htmlspecialchars($this->namePrefix) . '[]" value="' . $entry['id'] . '">';
			}

			if ( isset ($this->namePrefix) ) {
	            $html .= '<label for="' . htmlspecialchars($this->namePrefix) . '_' . ($id - 1) . '">';
			}
			$html .= empty($entry['label']) ? '(None)' : htmlspecialchars($entry['label']);
			if ( isset ($this->namePrefix) ) {
 				$html .= '</label>';
			}
		}

        // top-level "all" checkbox
		if ($this->hasAll) {
	        $html_pre .= '<input type="checkbox" data-role="all" data-key="' . htmlspecialchars($this->key) . '"';
			if ( !$this->disabled  && isset ($this->namePrefix) ) {
				$html_pre .= ' name="' . htmlspecialchars($this->namePrefix) . '_all" value="1"';
			};			
			if ( $all_checked )
				 $html_pre .= ' checked';
			if ( $this->disabled )
				$html_pre .= ' disabled';
			$html_pre .= '>';
			if ( $all_checked && $this->disabled && isset ($this->namePrefix) ) {
				// additional input field for disabled checkbox
				$html_pre .=  '<input type="hidden" name="'. htmlspecialchars($this->namePrefix) . '_all" value="1">';
			}
			$html_pre .= '&nbsp;=&gt;&nbsp;&nbsp;';
		}


		return $html_pre . $html;
    }
}

?>

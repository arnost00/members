<?
//==================================================================
// Rendered TABLE class
//==================================================================
require_once("ctable.inc.php");
require_once("common_race.inc.php");
require_once("ct_renderer.inc.php");

class DatumRenderer implements IColumnContentRenderer {
    public function __construct(private string $field) {}

    public function render(RowData $row, array $options = []): string {
        if($row->rec['vicedenni'])
			return Date2StringFT($row->rec['datum'],$row->rec['datum2']);
		else
			return Date2String($row->rec['datum']);
    }
}

// Field renderer with visualised cancelation
class NameLinkRenderer extends CancelableRenderer {
    public function render(RowData $row, array $options = [] ): string {
        $value = parent::render($row) ?? '';
		return '<A href="javascript:open_race_info('.$row->rec['id'].')" class="adr_name">'.$value.'</A>';
    }
}

class BossRenderer implements IColumnContentRenderer {

    public function render(RowData $row, array $options = []): string {

        global $usr;

        $link_to_participation = " / <A HREF=\"javascript:open_win('./api_race_entry.view.php?race_id=".$row->rec['id']."','')\">Účast</A>";
        $show_link = ($row->rec['vedouci'] == $usr->user_id) && (GetTimeToRace($row->rec['datum']) <= 0);
        $boss = '-';
        if($row->rec['vedouci_jmeno'] != '-')
        {
            $boss = $row->rec['vedouci_jmeno'].($show_link ? $link_to_participation : '');
        }
        return $boss;
    }
}

class RegistrationRenderer implements IColumnContentRenderer {

    public function render(RowData $row, array $options = []): string {

		$prihlasky_curr = raceterms::GetActiveRegDateArr($row->rec);
		$prihlasky_out_term = Date2String($prihlasky_curr[0]);
		if($row->rec['prihlasky'] > 1)
			$prihlasky_out_term .= '&nbsp;/&nbsp;'.$prihlasky_curr[1];
		$time_to_reg = GetTimeToReg($prihlasky_curr[0]);

		return raceterms::ColorizeTermUser($time_to_reg,$prihlasky_curr,$prihlasky_out_term);
    }
}

class ActivityRenderer implements IColumnContentRenderer {

    public function render(RowData $row, array $options = []): string {

        global $usr;

        $entry_lock = $options['entry_lock'] ?? true; // locked if undefined
        if (!isset($options['curr_date'])) {
            echo "Option 'curr_date' not set, using default.\n";
        }
        $curr_date = $options['curr_date'] ?? GetCurrentDate();

        $prihlasky_curr = raceterms::GetActiveRegDateArr($row->rec);
		$time_to_reg = GetTimeToReg($prihlasky_curr[0]);

		$prihl_finish = ($time_to_reg == -1 && $prihlasky_curr[0] != 0) || ($prihlasky_curr[0] == 0 && $row->rec['datum'] <= $curr_date);
		$zbr = "<A HREF=\"javascript:open_win('./race_reg_view.php?id=".$row->rec['id']."','')\"><span class=\"TextAlertExpLight\">Zbr</span></A>";

		if($row->rec['kat'] == NULL)
		{	// neni prihlasen
			if (!$prihl_finish && !$entry_lock)
			{
				return "<A HREF=\"javascript:open_win('./us_race_regon.php?id_zav=".$row->rec["id"]."&id_us=".$usr->user_id."','')\">Přihl.</A> / ".$zbr;
			}
			else
			{
				return "<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._USER_GROUP_ID_."&id=".$row->rec["id"]."&us=1','')\"><span class=\"TextAlertExpLight\">Zobrazit</span></A>";
			}
		}
		else
		{	// je prihlasen
			$prihl_finish2 = $prihl_finish || ( $prihlasky_curr[0] != 0 && $prihlasky_curr[1] != $row->rec['termin']);
			if($prihl_finish2 != $prihl_finish)
			{
				return "<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._USER_GROUP_ID_.'&id='.$row->rec['id']."&us=1','')\"><span class=\"Highlight\">".$row->rec['kat'].'</span></A> / '.$row->rec['termin'];
			}
			else if (!$prihl_finish && !$entry_lock)
			{
				return "<A HREF=\"javascript:open_win('./us_race_regon.php?id_zav=".$row->rec['id']."&id_us=".$usr->user_id."','')\" class=\"Highlight\">".$row->rec['kat']."</A> / <A HREF=\"javascript:open_win('./us_race_regoff_exc.php?id_zav=".$row->rec['id']."&id_us=".$usr->user_id."','')\" onclick=\"return confirm_delete();\" class=\"Erase\">Od.</A>";
			}
			else
			{
				return "<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._USER_GROUP_ID_.'&id='.$row->rec['id']."&us=1','')\"><span class=\"Highlight\">".$row->rec['kat'].'</span></A>';
			}
		}

        return '';
    }
}

class ParticipantsRenderer implements IColumnContentRenderer {

    public function render(RowData $row, array $options = []): string {

        $count_registered = $options['count_registered'] ?? [];

        $registered = isset($count_registered[$row->rec['id']]) ? $count_registered[$row->rec['id']] : 0;
        $kapacita = (int)$row->rec['kapacita'];
        $alert = $kapacita - $registered < 10 ? 'class="TextAlert7"' : '';
    
        if ($kapacita > 0) {
            return '<span '.$alert.'>' .$registered . '/' . $kapacita . '</span>';
        } else {
            return $registered ?: ''; // show nothing if zero
		}        
    }
}

class RacesRendererFactory extends AColumnRendererFactory {
    public static function createColRenderer(string $column_name): IColumnContentRenderer {
        return match ($column_name) {
            'datum' => new DatumRenderer($column_name),
            'nazev' => new NameLinkRenderer($column_name),
            'misto' => new CancelableRenderer($column_name),
            'ext_id' => new FormatFieldRenderer($column_name, function ( $ext_id ) : string { return !empty ($ext_id) ? 'A' : '-';}  ),
            'typ0' => new FormatFieldRenderer($column_name,'GetRaceType0'),
            'typ' => new FormatFieldRenderer($column_name,'GetRaceTypeImg'),
            'odkaz' => new FormatFieldRenderer($column_name,'GetRaceLinkHTML'),
            'ucast' => new ParticipantsRenderer(),
            'moznosti' => new ActivityRenderer(),
            'prihlasky' => new RegistrationRenderer(),
            'vedouci' => new BossRenderer(),
           default => new DefaultRenderer($column_name),
        };
    }
    public static function createHeaderRenderer(string $column_name): IColumnHeaderRenderer {
        return match ($column_name) {
            'datum' => new DefaultHeaderRenderer('Datum',ALIGN_CENTER),
            'nazev' => new DefaultHeaderRenderer('Název'),
            'misto' => new DefaultHeaderRenderer('Místo'),
            'oddil' => new HelpHeaderRenderer('Poř.',ALIGN_CENTER,"Pořadatel"),
            'ext_id' => new HelpHeaderRenderer('O',ALIGN_CENTER,"závod v ORISu"),
            'typ0' => new HelpHeaderRenderer('T',ALIGN_CENTER,"Typ akce"),
            'typ' => new HelpHeaderRenderer('S',ALIGN_CENTER,"Sport"),
            'odkaz' => new HelpHeaderRenderer('W',ALIGN_CENTER,"Web závodu"),
            'kategorie' => new HelpHeaderRenderer('Kat',ALIGN_CENTER,"Zadané kategorie"),
		    'ucast' => new HelpHeaderRenderer('Účast',ALIGN_CENTER,"Přihlášeno/Kapacita"),
	        'moznosti' => new DefaultHeaderRenderer('Možnosti',ALIGN_CENTER),
            'prihlasky' => new DefaultHeaderRenderer('Přihlášky',ALIGN_CENTER),
            'vedouci' => new DefaultHeaderRenderer('Vedoucí',ALIGN_CENTER),
            default => new DefaultHeaderRenderer($column_name),
        };
    }
}

// modifies plain texts on row, evaluated once per row
class GreyOldPainter implements IRowTextPainter {
	public function getPrefixSuffix(RowData $row, array $options = [] ): array {
		$race_is_old = (GetTimeToRace($row->rec['datum']) == -1);

		return [ ($race_is_old) ? '<span class="TextAlertExpLight">' : '', ($race_is_old) ? '</span>' : ''	];
	}
}

// Break between years
class YearBreakDetector implements IBreakRowDetector {
    public function needsBreak(array $prev, RowData $curr): bool {
        return Date2Year($prev['datum']) !== Date2Year($curr->rec['datum']);
    }

    public function renderBreak(html_table_mc $tbl, RowData $row): string {
        return $tbl->get_break_row(true);
    }
}

// Break Before First Future Race
class FutureRaceBreakDetector implements IBreakRowDetector {
    private ?bool $descending = null; // order unknown at start
    private bool $alreadyBroken = false;

    public function needsBreak(array $prev, RowData $curr): bool {
        if ($this->alreadyBroken) return false;

        if ( !isset( $this->descending ) ) {
            if ( $prev['datum'] < $curr->rec['datum'] ) $this->descending = false;
            if ( $prev['datum'] > $curr->rec['datum'] ) $this->descending = true;
        }

        if ( isset( $this->descending ) ) {
            $now = GetCurrentDate();
            if ( $this->descending && ( $prev['datum'] > $now && $curr->rec['datum'] <= $now ) || 
                 !$this->descending && ( $prev['datum'] <= $now && $curr->rec['datum'] > $now) )
            {
                    $this->alreadyBroken = true;
                    return true;
            }
        }
        return false;
    }

    public function renderBreak(html_table_mc $tbl, RowData $row): string {
        return $tbl->get_break_row();
    }
}

// Button Break between years
class YearExpanderDetector implements IBreakRowDetector {
    public function needsBreak(array $prev, RowData $curr): bool {
        return Date2Year($prev['datum']) !== Date2Year($curr->rec['datum']);
    }

    public function renderBreak(html_table_mc $tbl, RowData $row): string {
        $year = Date2Year($row->rec['datum']);
        $odkaz = '<span class="year-expander"
            onclick="toggle_expand_by_group(\''. $year . '\', this)">' .
            ($year < date('Y') ? '▼' : '▲') . ' '. $year . '</span>';
        return $tbl->get_info_row($odkaz)."\n";
    }

    // helper function for attribute setting
    public static function yearGroupRowAttrsExtender(RowData $row): array
    {
        $year = Date2Year($row->rec['datum']);

        $attrs = ['data-group' => $year];

        if ($year < date('Y')) {
            $attrs['style'] = 'display:none';
        }

        return $attrs;
    }
}

?>
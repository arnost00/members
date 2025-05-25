<?
//==================================================================
// Rendered TABLE class
//==================================================================
require_once("ctable.inc.php");
require_once("common_race.inc.php");
require_once("ct_renderer.inc.php");

class DatumRenderer implements IColumnContentRenderer {
    public function __construct(private string $field) {}

    public function render(array $record, array $options = []): string {
        if($record['vicedenni'])
			return Date2StringFT($record['datum'],$record['datum2']);
		else
			return Date2String($record['datum']);
    }
}

// Field renderer with visualised cancelation
class NameLinkRenderer extends CancelableRenderer {
    public function render(array $record, array $options = [] ): string {
        $value = parent::render($record) ?? '';
		return '<A href="javascript:open_race_info('.$record['id'].')" class="adr_name">'.$value.'</A>';
    }
}

class BossRenderer implements IColumnContentRenderer {

    public function render(array $record, array $options = []): string {

        global $usr;

        $link_to_participation = " / <A HREF=\"javascript:open_win('./api_race_entry.view.php?race_id=".$record['id']."','')\">Účast</A>";
        $show_link = ($record['vedouci'] == $usr->user_id) && (GetTimeToRace($record['datum']) <= 0);
        $boss = '-';
        if($record['vedouci_jmeno'] != '-')
        {
            $boss = $record['vedouci_jmeno'].($show_link ? $link_to_participation : '');
        }
        return $boss;
    }
}

class RegistrationRenderer implements IColumnContentRenderer {

    public function render(array $record, array $options = []): string {

		$prihlasky_curr = raceterms::GetActiveRegDateArr($record);
		$prihlasky_out_term = Date2String($prihlasky_curr[0]);
		if($record['prihlasky'] > 1)
			$prihlasky_out_term .= '&nbsp;/&nbsp;'.$prihlasky_curr[1];
		$time_to_reg = GetTimeToReg($prihlasky_curr[0]);

		return raceterms::ColorizeTermUser($time_to_reg,$prihlasky_curr,$prihlasky_out_term);
    }
}

class ActivityRenderer implements IColumnContentRenderer {

    public function render(array $record, array $options = []): string {

        global $usr;

        $entry_lock = $options['entry_lock'] ?? true; // locked if undefined
        if (!isset($options['curr_date'])) {
            echo "Option 'curr_date' not set, using default.\n";
        }
        $curr_date = $options['curr_date'] ?? GetCurrentDate();

        $prihlasky_curr = raceterms::GetActiveRegDateArr($record);
		$time_to_reg = GetTimeToReg($prihlasky_curr[0]);

		$prihl_finish = ($time_to_reg == -1 && $prihlasky_curr[0] != 0) || ($prihlasky_curr[0] == 0 && $record['datum'] <= $curr_date);
		$zbr = "<A HREF=\"javascript:open_win('./race_reg_view.php?id=".$record['id']."','')\"><span class=\"TextAlertExpLight\">Zbr</span></A>";

		if($record['kat'] == NULL)
		{	// neni prihlasen
			if (!$prihl_finish && !$entry_lock)
			{
				return "<A HREF=\"javascript:open_win('./us_race_regon.php?id_zav=".$record["id"]."&id_us=".$usr->user_id."','')\">Přihl.</A> / ".$zbr;
			}
			else
			{
				return "<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._USER_GROUP_ID_."&id=".$record["id"]."&us=1','')\"><span class=\"TextAlertExpLight\">Zobrazit</span></A>";
			}
		}
		else
		{	// je prihlasen
			$prihl_finish2 = $prihl_finish || ( $prihlasky_curr[0] != 0 && $prihlasky_curr[1] != $record['termin']);
			if($prihl_finish2 != $prihl_finish)
			{
				return "<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._USER_GROUP_ID_.'&id='.$record['id']."&us=1','')\"><span class=\"Highlight\">".$record['kat'].'</span></A> / '.$record['termin'];
			}
			else if (!$prihl_finish && !$entry_lock)
			{
				return "<A HREF=\"javascript:open_win('./us_race_regon.php?id_zav=".$record['id']."&id_us=".$usr->user_id."','')\" class=\"Highlight\">".$record['kat']."</A> / <A HREF=\"javascript:open_win('./us_race_regoff_exc.php?id_zav=".$record['id']."&id_us=".$usr->user_id."','')\" onclick=\"return confirm_delete();\" class=\"Erase\">Od.</A>";
			}
			else
			{
				return "<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._USER_GROUP_ID_.'&id='.$record['id']."&us=1','')\"><span class=\"Highlight\">".$record['kat'].'</span></A>';
			}
		}

        return '';
    }
}

class ParticipantsRenderer implements IColumnContentRenderer {

    public function render(array $record, array $options = []): string {

        $count_registered = $options['count_registered'] ?? [];

        $registered = isset($count_registered[$record['id']]) ? $count_registered[$record['id']] : 0;
        $kapacita = (int)$record['kapacita'];
        $alert = $kapacita - $registered < 10 ? 'class="TextAlert7"' : '';
    
        if ($kapacita > 0) {
            return '<span '.$alert.'>' .$registered . '/' . $kapacita . '</span>';
        } else {
            return $registered ?: ''; // show nothing if zero
		}        
    }
}

class RacesColumnRendererFactory {
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
    public static function create(string $column_name) {
        return [RacesColumnRendererFactory::createHeaderRenderer($column_name),
                RacesColumnRendererFactory::createColRenderer($column_name)];
    }
}

// modifies plain texts on row, evaluated once per row
class GreyOldPainter implements IRowTextPainter {
	public function getPrefixSuffix(array $record, array $options = [] ): array {
		$race_is_old = (GetTimeToRace($record['datum']) == -1);

		return [ ($race_is_old) ? '<span class="TextAlertExpLight">' : '', ($race_is_old) ? '</span>' : ''	];
	}
}

// Break between years
class YearBreakDetector implements IBreakRowDetector {
    public function needsBreak(array $prev, array $curr): bool {
        return Date2Year($prev['datum']) !== Date2Year($curr['datum']);
    }

    public function renderBreak(html_table_mc $tbl, array $record): string {
        return $tbl->get_break_row(true);
    }
}

// Break Before First Future Race
class FutureRaceBreakDetector implements IBreakRowDetector {
    private ?bool $descending = null; // order unknown at start
    private bool $alreadyBroken = false;

    public function needsBreak(array $prev, array $curr): bool {
        if ($this->alreadyBroken) return false;

        if ( !isset( $this->descending ) ) {
            if ( $prev['datum'] < $curr['datum'] ) $this->descending = false;
            if ( $prev['datum'] > $curr['datum'] ) $this->descending = true;
        }

        if ( isset( $this->descending ) ) {
            $now = GetCurrentDate();
            if ( $this->descending && ( $prev['datum'] > $now && $curr['datum'] <= $now ) || 
                 !$this->descending && ( $prev['datum'] <= $now && $curr['datum'] > $now) )
            {
                    $this->alreadyBroken = true;
                    return true;
            }
        }
        return false;
    }

    public function renderBreak(html_table_mc $tbl, array $record): string {
        return $tbl->get_break_row();
    }
}

class RacesTableColumn {
    public IColumnHeaderRenderer $header;
    public IColumnContentRenderer $content;

    public function __construct(
        public string|IColumnHeaderRenderer $headerDef,
        public IColumnContentRenderer $contentDef
    ) {
        if ( is_string ($headerDef) ) 
            $this->header = RacesColumnRendererFactory::createHeaderRenderer($headerDef);
        else
            $this->header = $headerDef;
        $this->content = $contentDef;
    }
}

class RacesRenderedTable {
    /** @var RacesTableColumn[] */
    private array $columns = [];
    /** @var IBreakRowDetector[] */
    private array $breakRowDetectors = [];

    // text painter add prefic=x and suffix to palin text in row
    private ?IRowTextPainter $rowTextPainter = null;

    // row class/attributes extender
    private $rowAttrsExt = null;

    public function addColumns(string|array ...$coldefs) {
        foreach ($coldefs as $coldef) {
            if (is_string($coldef)) {
                $arr = RacesColumnRendererFactory::create($coldef);
                $this->addColumn( new RacesTableColumn ( $arr[0], $arr[1] ) );
            } elseif (is_array($coldef)) {
                if ( count ($coldef) > 1 ) 
                    $this->addColumn( new RacesTableColumn( $coldef[0], $coldef[1] ) );
                else
                    $this->addColumn( new RacesTableColumn( new DefaultHeaderRenderer('undef'), new NoRenderer('undef') ) );
            }
        }
    }

    public function addBreak(IBreakRowDetector $detector) {
        $this->breakRowDetectors[] = $detector;
    }

    public function addColumn(RacesTableColumn $col): void {
        $this->columns[] = $col;
    }

    public function setRowTextPainter(IRowTextPainter $painter): void {
        $this->rowTextPainter = $painter;
    }

    // define row class/attr extender
    // the function must return name/value pairs
    public function setRowAttrsExt ( callable $fn ) {
        $this->rowAttrsExt = $fn;
    }

    public function render( html_table_mc $tbl, array $records, array $options = []): string {

        // Render headers
        $col = 0;
        foreach ($this->columns as $column) {
            // get all defined headers
            if ( $column->header instanceof HelpHeaderRenderer )
                $tbl->set_header_col_with_help ($col++,$column->header->label,$column->header->align,$column->header->help);
            else
                $tbl->set_header_col($col++,$column->header->label,$column->header->align);
        }

        $rnd = $tbl->get_css()."\n";
        $rnd .= $tbl->get_header()."\n";
        $rnd .= $tbl->get_header_row()."\n";

        $prev = null;
        foreach ($records as $record) {
            if ($prev !== null) {
                // create first break between $prev and $record
                foreach ($this->breakRowDetectors as $detector) {
                    if ($detector->needsBreak($prev, $record)) {
                        $rnd .= $detector->renderBreak($tbl,$record) . "\n";
                        break; // only first one
                    }
                }
            }

            if ( isset($this->rowTextPainter) ) {
                // evaluate once per record
                $ps = $this->rowTextPainter->getPrefixSuffix($record,$options);
                $prefix = $ps[0] ?? '';
                $suffix = $ps[1] ?? '';
            }

            $row = [];
            foreach ($this->columns as $column) {
                // get all defined cells
                $rowValue = $column->content->render($record,$options);
                if ( isset($this->rowTextPainter) ) {
                    // apply on each value
                    $rowValue = preg_replace ( '/(\>|^)([^<]+)(\<|$)/', '${1}' . $prefix . '${2}' . $suffix . '${3}', $rowValue);
                }
                $row[] = $rowValue;
            }

            $row_add_attrs = ( $this->rowAttrsExt !== null ) ? ($this->rowAttrsExt) ( $record ) : [];
            $rnd .= $tbl->get_new_row_arr($row,$row_add_attrs) . "\n";
            $prev = $record;
        }

        return $rnd . $tbl->get_footer() . "\n";
    }
}
?>
<?
//==================================================================
// Rendered TABLE class
//==================================================================
require_once("ctable.inc.php");
require_once("common_race.inc.php");
require_once("ct_renderer.inc.php");

class LimitBreakDetector implements IBreakRowDetector {
    private int $limit;

    public function __construct(int $limit) {
        $this->limit = $limit;
    }

    public function needsBreak(array $prev, RowData $curr): bool {
        return $curr->number === $this->limit;
    }

    public function renderBreak(html_table_mc $tbl, RowData $row): string {
        return $tbl->get_break_row(true);
    }
}

class GreyLastNPainter implements IRowTextPainter {
    private int $limit;

    public function __construct(int $limit) {
        $this->limit = $limit;
    }

    public function getPrefixSuffix(RowData $row, array $options = []): array {
        $is_last = ($row->number >= $this->limit);

        return [
            $is_last ? '<span class="TextAlertExpLight">' : '',
            $is_last ? '</span>' : ''
        ];
    }
}

class KategoryHeadderRenderer  extends HelpHeaderRenderer {

    public function render(html_table_mc $tbl, int $col): void {
        parent::render( $tbl, $col);
        $tbl->mod_header_col_onclick($col,'toggleCategoriesAndScroll()');
    }
}

class RaceRendererFactory extends AColumnRendererFactory {
    public static function createColRenderer(string $column_name): IColumnContentRenderer {
        return match ($column_name) {
            'id' => new CallbackRenderer(function ( RowData $row, array $options ) : string {
                        return ($row->number + 1).'<!-- '.$row->rec['id'].' -->'; }),
            'reg' => new FormatFieldRenderer($column_name, function($reg) { 
                        global $g_shortcut ; return $g_shortcut.RegNumToStr($reg);
                     }),
            'si_chip' => new CallbackRenderer(function ( RowData $row, array $options ) : string {
                        if ($row->rec['t_si_chip'] != 0) 
                            return '<span class="TemporaryChip">'.SINumToStr($row->rec['t_si_chip']).'</span>';
                        if ($row->rec['si_chip'] != 0)
                            return SINumToStr($row->rec['si_chip']);
                        return '';
                     }),
            'kat' => new FormatFieldRenderer($column_name, function($kat) { return '<B>'.htmlspecialchars($kat).'</B>'; }),
            'transport', 'ubytovani' => new FormatFieldRenderer($column_name, function ( $bl ) : string {
                            return $bl ? '<B>&#x2714;</B>' : ''; }  ),
            'sedadel' => new CallbackRenderer(function ( RowData $row, array $options ) : string {
                            $dummy = 0;
                            return GetSharedTransportValue($row->rec["transport"], $row->rec["sedadel"], $dummy );
                         }),
            default => new DefaultRenderer($column_name),
        };
    }
    public static function createHeaderRenderer(string $column_name): IColumnHeaderRenderer {
        return match ($column_name) {
            'id' => new DefaultHeaderRenderer('Poř.',ALIGN_CENTER),
            'jmeno' => new DefaultHeaderRenderer('Jméno'),
            'prijmeni' => new DefaultHeaderRenderer('Příjmení'),
            'reg' => new HelpHeaderRenderer('Reg.č.',ALIGN_CENTER,"Registrační číslo"),
            'si_chip' => new DefaultHeaderRenderer('SI čip',ALIGN_RIGHT),
            'kat' => new KategoryHeadderRenderer('Kategorie',ALIGN_CENTER,"Zobrazí počet účastníků v jednotlivých kategoriích"),
            'transport' => new HelpHeaderRenderer('SD',ALIGN_CENTER,"Společná"),
            'sedadel' => new HelpHeaderRenderer('&#x1F697;',ALIGN_CENTER,'Nabízených sedadel'),
            'ubytovani' => new HelpHeaderRenderer('SU',ALIGN_CENTER,"Společné ubytování"),
		    'termin' => new DefaultHeaderRenderer('Termín',ALIGN_CENTER),
	        'pozn' => new DefaultHeaderRenderer('Pozn.'),
            'pozn_in' => new DefaultHeaderRenderer('Pozn.(i)'),
            default => new DefaultHeaderRenderer($column_name),
        };
    }
}

?>
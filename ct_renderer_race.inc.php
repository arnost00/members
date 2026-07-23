<?
//==================================================================
// Rendered TABLE class
//==================================================================
require_once("ctable.inc.php");
require_once("common_race.inc.php");
require_once("ct_renderer.inc.php");
require_once("connectors.php");

function RenderRaceSyncStatusSymbol($sync_status, bool $oris_entry_future): string {
    return match ($sync_status) {
        'SYNCED' => '&#x2714;',
        'PENDING_CREATE', 'PENDING_UPDATE', 'PENDING_DELETE' => $oris_entry_future ? '&#x1F552;' : '&#x27F3;',
        'FAILED_CREATE', 'FAILED_UPDATE', 'FAILED_DELETE' => '&#x26A0;',
        default => '',
    };
}

// create and return new sync column if required
function CreateRaceSyncStatusColumn($race): ?TableColumn {
    $connector = ConnectorFactory::create();
    // only for not performed races
    // $race_date = is_array($race) ? (int)($race['datum'] ?? 0) : 0;
    // $show_sync_status = is_array($race) && $race_date > GetCurrentDate() && !empty($race['ext_id']) && !empty($oris_club_key) && $connector !== null;
    global $g_oris_club_key;
    $show_sync_status = !empty($race['ext_id']) && !empty($g_oris_club_key) && $connector !== null;

    if (!$show_sync_status) {
        return null;
    }

    $entry_start_future = false;
    if (is_array($race) && !empty($race['entry_start'])) {
        $entry_start = strtotime($race['entry_start']);
        $entry_start_future = ($entry_start !== false && $entry_start > time());
    }

    return RaceRendererFactory::createColumn(
        new HelpHeaderRenderer('&#x21C4;', ALIGN_CENTER, 'Synchronizace s '.$connector->getSystemName()),
        new CallbackRenderer(function (RowData $row, array $options) use ($entry_start_future): string {
            return RenderRaceSyncStatusSymbol($row->rec['sync_status'] ?? '', $entry_start_future);
        })
    );
}

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

// Break between termin
class TerminBreakDetector implements IBreakRowDetector {
    public function needsBreak(array $prev, RowData $curr): bool {
        return $prev['termin'] !== $curr->rec['termin'];
    }

    public function renderBreak(html_table_mc $tbl, RowData $row): string {
        return $tbl->get_break_row(false);
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
            'sync_status' => new CallbackRenderer(function ( RowData $row, array $options ) : string {
                            return RenderRaceSyncStatusSymbol($row->rec['sync_status'] ?? '', (bool)($options['oris_entry_future'] ?? false));
                         }),
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
            'sync_status' => new HelpHeaderRenderer('&#x21C4;', ALIGN_CENTER, 'Synchronizace'),
		    'termin' => new DefaultHeaderRenderer('Termín',ALIGN_CENTER),
	        'pozn' => new DefaultHeaderRenderer('Pozn.'),
            'pozn_in' => new DefaultHeaderRenderer('Pozn.(i)'),
            default => new DefaultHeaderRenderer($column_name),
        };
    }
}

?>

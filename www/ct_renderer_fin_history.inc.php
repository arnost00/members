<?
//==================================================================
// Finance history table renderers
//==================================================================
require_once("ct_renderer.inc.php");

class FinanceHistoryEditorRenderer implements IColumnContentRenderer {
    public function render(RowData $row, array $options = []): string {
        $is_system = empty($row->rec['id_users_editor']);
        $editor_name = $is_system ? 'Systém' : (string)($row->rec['editor_name'] ?? '');
        $class = $is_system ? 'red' : '';

        return "<span class='amount".$class."'>".htmlspecialchars($editor_name)."</span>";
    }
}

class FinanceHistoryNoteRenderer implements IColumnContentRenderer {
    public function render(RowData $row, array $options = []): string {
        $note = htmlspecialchars((string)($row->rec['note'] ?? ''));
        return str_replace(['&lt;i&gt;', '&lt;/i&gt;'], ['<i>', '</i>'], $note);
    }
}

// Month expander break: renders a month heading (including its year) on
// every month change.
class MonthExpanderDetector extends TimeRangeExpanderDetector {
    private const MONTHS = ['leden', 'únor', 'březen', 'duben', 'květen', 'červen',
        'červenec', 'srpen', 'září', 'říjen', 'listopad', 'prosinec'];

    private static function monthKey(int $ts): string {
        return date('Y-m', $ts);
    }

    public function getRangeKey(array $record): string {
        return self::monthKey((int)$record['datum']);
    }

    public function needsBreak(array $prev, RowData $curr): bool {
        return self::monthKey($prev['datum']) !== self::monthKey($curr->rec['datum']);
    }

    public function renderBreak(html_table_mc $tbl, RowData $row): string {
        $ts = $row->rec['datum'];
        $monthKey = self::monthKey($ts);
        $arrow = $monthKey < date('Y-m') ? '▼' : '▲';
        $monthLabel = self::MONTHS[(int)date('n', $ts) - 1].' '.date('Y', $ts);

        return $tbl->get_info_row(
            '<span class="time-range-expander month-expander" onclick="toggle_expand_by_group(\''.$monthKey.'\', this)">'.$arrow.' '.$monthLabel.'</span>'
        );
    }

    protected function getRangeLabel(string $range): ?string {
        $date = DateTimeImmutable::createFromFormat('!Y-m', $range);
        if ($date === false || $date->format('Y-m') !== $range) {
            return null;
        }

        return self::MONTHS[(int)$date->format('n') - 1].' '.$date->format('Y');
    }

    public static function rowAttrsExtender(RowData $row): array {
        $monthKey = self::monthKey($row->rec['datum']);
        $attrs = ['data-group' => $monthKey];

        if ($monthKey < date('Y-m')) {
            $attrs['style'] = 'display:none';
        }

        return $attrs;
    }
}

class FinanceHistoryRendererFactory extends AColumnRendererFactory {
    public static function createHistoryTable(): RenderedTable {
        $table = self::createTable();
        $table->addColumns('datum', 'reg', 'name', 'editor_name', 'amount', 'zavod_datum', 'zavod_nazev', 'note');
        $table->addBreak(new MonthExpanderDetector());
        return $table;
    }

    public static function createColRenderer(string $column_name): IColumnContentRenderer {
        return match ($column_name) {
            'datum' => new DateFieldRenderer($column_name),
            'editor_name' => new FinanceHistoryEditorRenderer(),
            'note' => new FinanceHistoryNoteRenderer(),
            'zavod_datum' => new DateFieldRenderer($column_name),
            default => new DefaultRenderer($column_name),
        };
    }

    public static function createHeaderRenderer(string $column_name): IColumnHeaderRenderer {
        return match ($column_name) {
            'datum' => new DefaultHeaderRenderer('Datum'),
            'reg' => new DefaultHeaderRenderer('Reg. č.', ALIGN_CENTER),
            'name' => new DefaultHeaderRenderer('Jméno'),
            'editor_name' => new DefaultHeaderRenderer('Zapsal'),
            'amount' => new DefaultHeaderRenderer('Částka',ALIGN_RIGHT),
            'zavod_datum' => new DefaultHeaderRenderer('Závod d.'),
            'zavod_nazev' => new DefaultHeaderRenderer('Závod n.'),
            'note' => new DefaultHeaderRenderer('Komentář'),
            default => new DefaultHeaderRenderer($column_name),
        };
    }
}
?>

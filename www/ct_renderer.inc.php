<?
//==================================================================
// Rendered TABLE class
//==================================================================
require_once("ctable.inc.php");
require_once("common_race.inc.php");
require_once("sql_filter.inc.php");

interface IColumnHeaderRenderer {
    public function render(html_table_mc $tbl, int $col): void;
}

interface IColumnContentRenderer {
    public function render(RowData $row, array $options = []): string;
}

class DefaultHeaderRenderer implements IColumnHeaderRenderer {
    public function __construct(
        public string $label,
        public string $align = ALIGN_LEFT
    ) {}

    public function render(html_table_mc $tbl, int $col): void {
        $tbl->set_header_col($col, $this->label, $this->align);
    }
}

class HelpHeaderRenderer implements IColumnHeaderRenderer {
    public function __construct(
        public string $label,
        public string $align,
        public string $help
    ) {}

    public function render(html_table_mc $tbl, int $col): void {
        $tbl->set_header_col_with_help($col, $this->label, $this->align, $this->help);
    }
}

class NoRenderer implements IColumnContentRenderer {
    public function __construct(private string $field) {}

    public function render(RowData $row, array $options = []): string {
        return htmlspecialchars((string)($this->field ?? ''));
    }
}

class DefaultRenderer implements IColumnContentRenderer {
    public function __construct(private string $field) {}

    public function render(RowData $row, array $options = []): string {
        return htmlspecialchars((string)($row->rec[$this->field] ?? ''));
    }
}

// plain field renderer. Display direct value.
class FieldRenderer implements IColumnContentRenderer {
    public function __construct(protected string $field) {}

    public function render(RowData $row, array $options = []): string {
        return htmlspecialchars((string)($row->rec[$this->field] ?? ''));
    }
}

class DateFieldRenderer extends FieldRenderer {
    public function render(RowData $row, array $options = []): string {
        $value = $row->rec[$this->field] ?? '';
        if ($value === '' || $value === null || $value === 0 || $value === '0') {
            return '';
        }

        if (is_numeric($value)) {
            return Date2String((int)$value);
        }

        $value = (string)$value;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
            return SQLDate2String($value);
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return htmlspecialchars($value);
        }

        return date('j.n.Y', $timestamp);
    }
}

class DateTimeFieldRenderer extends FieldRenderer {
    public function __construct(string $field, private string $format = 'd.m.Y H:i:s') {
        parent::__construct($field);
    }

    public function render(RowData $row, array $options = []): string {
        $value = (string)($row->rec[$this->field] ?? '');
        if ($value === '') {
            return '';
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return htmlspecialchars($value);
        }

        return date($this->format, $timestamp);
    }
}

// Field renderer with visualised cancelation
class CancelableRenderer extends FieldRenderer {
    public function render(RowData $row, array $options = []): string {
        $value = parent::render($row) ?? '';
        return GetFormatedTextDel ( $value, $row->rec['cancelled'] );
    }
}

// formated by extern function, the function must ensure using htmlspecialchars 
class FormatFieldRenderer implements IColumnContentRenderer {
    private $fn;
    private $field;

    public function __construct(string $field,callable $fn) {
        $this->fn = $fn;
        $this->field = $field;
    }

    public function render(RowData $row, array $options = []): string {
        $val = $row->rec[$this->field] ?? '';
        return ($this->fn)( $val );
    }
}

// collected and formated by extern function, the function must ensure using htmlspecialchars 
class CallbackRenderer implements IColumnContentRenderer {
    private $fn;

    public function __construct(callable $fn) {
        $this->fn = $fn;
    }

    public function render(RowData $row, array $options = []): string {
        return ($this->fn)( $row, $options );
    }
}

// modifies plain texts on row, evaluated once per row
interface IRowTextPainter {
    public function getPrefixSuffix(RowData $row, array $options = [] ): array;
}

// Checks and creates table break
interface IBreakRowDetector {
    public function needsBreak(array $prev, RowData $curr): bool;
    public function renderBreak(html_table_mc $tbl, RowData $row): string;
}

// Optional extension for break detectors which can represent unloaded time ranges.
interface ITimeRangeExpander extends IBreakRowDetector {
    public function getRangeKey(array $record): string;
    public function renderRangeBreak(
        html_table_mc $tbl,
        string $range,
        bool $expanded,
        string $expansionUrl
    ): string;
    public function getRangeRowAttrs(RowData $row, bool $expanded): array;
}

abstract class TimeRangeExpanderDetector implements ITimeRangeExpander {
    abstract protected function getRangeLabel(string $range): ?string;

    public function renderRangeBreak(
        html_table_mc $tbl,
        string $range,
        bool $expanded,
        string $expansionUrl
    ): string {
        $label = $this->getRangeLabel($range);
        if ($label === null) {
            return '';
        }

        $arrow = $expanded ? '▲' : '▼';
        $content = '<span class="time-range-expander" role="button" tabindex="0"'
            .' aria-expanded="'.($expanded ? 'true' : 'false').'"'
            .' data-range="'.htmlspecialchars($range).'"'
            .' data-loaded="'.($expanded ? '1' : '0').'"'
            .' data-expand-url="'.htmlspecialchars($expansionUrl).'"'
            .' onclick="toggle_lazy_time_range(this)"'
            .' onkeydown="toggle_lazy_time_range_by_key(event, this)">'
            .'<span class="time-range-arrow">'.$arrow.'</span> '
            .htmlspecialchars($label)
            .'<span class="time-range-status" aria-live="polite"></span></span>';

        return $tbl->get_info_row($content, ['data-range-heading' => $range]);
    }

    public function getRangeRowAttrs(RowData $row, bool $expanded): array {
        $attrs = ['data-group' => $this->getRangeKey($row->rec)];
        if (!$expanded) {
            $attrs['style'] = 'display:none';
        }
        return $attrs;
    }
}

// table column descriptor, holds header and content rendered
class TableColumn {
    public IColumnHeaderRenderer $header;
    public IColumnContentRenderer $content;

    public function __construct(
        public IColumnHeaderRenderer $headerDef,
        public IColumnContentRenderer $contentDef
    ) {
        $this->header = $headerDef;
        $this->content = $contentDef;
    }
}

// row information for renderig
class RowData {
    public int $number;// current line
    public int $count; // total count of lines
    public array $rec; // record

    public function __construct(int $count) {
        $this->number = 0;
        $this->count = $count;
        $this->rec = [];
    }    
}

abstract class AColumnRendererFactory {
    abstract public static function createColRenderer(string $column_name): IColumnContentRenderer;
    abstract public static function createHeaderRenderer(string $column_name): IColumnHeaderRenderer;

    public static function createTable () : RenderedTable {
        return new RenderedTable(static::class);
    }

    public static function create(string $column_name) {
        return [
            static::createHeaderRenderer($column_name),
            static::createColRenderer($column_name)
        ];
    }

    public static function createColumn(
        string|IColumnHeaderRenderer $headerDef,
        IColumnContentRenderer $contentDef
    ): TableColumn {
        if (is_string($headerDef)) {
            $headerDef = static::createHeaderRenderer($headerDef);
        }

        return new TableColumn($headerDef, $contentDef);
    }
}

class RenderedTable {
    /** @var TableColumn[] */
    private array $columns = [];
    /** @var IBreakRowDetector[] */
    private array $breakRowDetectors = [];

    // text painter add prefic=x and suffix to palin text in row
    private ?IRowTextPainter $rowTextPainter = null;

    // row class/attributes extender function ( RowData row ) : array
    private $rowAttrsExt = null;

    // row filter function ( RowData row ) : bool
    private $rowFilter = null;

    // mandatory renderer factory for column creation
    private string $rendererFactoryClass;

    private ?SqlFilter $filter = null;
    private ?string $rangeExpansionEndpoint = null;

    public function __construct(string $rendererFactoryClass) {
        $this->rendererFactoryClass = $rendererFactoryClass;
    }

    public function addColumns(string|array ...$coldefs) {
        foreach ($coldefs as $coldef) {
            if (is_string($coldef)) {
                $arr = call_user_func ( [$this->rendererFactoryClass, 'create'], $coldef);
                $this->addColumn( call_user_func ( [$this->rendererFactoryClass, 'createColumn'], $arr[0], $arr[1] ) );
            } elseif (is_array($coldef)) {
                if ( count ($coldef) > 1 ) 
                    $this->addColumn( call_user_func ( [$this->rendererFactoryClass, 'createColumn'], $coldef[0], $coldef[1] ) );
                else
                    $this->addColumn( call_user_func ( [$this->rendererFactoryClass, 'createColumn'],
                         new DefaultHeaderRenderer('undef'), new NoRenderer('undef') ) );
            }
        }
    }

    public function addBreak(IBreakRowDetector $detector) {
        $this->breakRowDetectors[] = $detector;
    }

    public function addColumn(TableColumn $col): void {
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

    public function setRowFilter ( callable $fn ) {
        $this->rowFilter = $fn;
    }

    public function setFilter(SqlFilter $filter): void {
        $this->filter = $filter;
    }

    public function setRangeExpansionEndpoint(string $endpoint): void {
        $this->rangeExpansionEndpoint = $endpoint;
    }

    private function configureTable(html_table_mc $tbl): void {
        $col = 0;
        foreach ($this->columns as $column) {
            $column->header->render($tbl, $col++);
        }
        // Initializes column count and alignments for both full and rows-only output.
        $tbl->get_header_row();
    }

    private function getTimeRangeExpander(): ?ITimeRangeExpander {
        foreach ($this->breakRowDetectors as $detector) {
            if ($detector instanceof ITimeRangeExpander) {
                return $detector;
            }
        }
        return null;
    }

    private function renderRecords(
        html_table_mc $tbl,
        array $records,
        array $options,
        bool $includeBreaks,
        ?ITimeRangeExpander $rangeExpander = null,
        bool $rangeExpanded = true
    ): string {
        $rnd = '';
        $prev = null;
        $row = new RowData(count($records));
        foreach ($records as $record) {
            $row->rec = $record;

            if ($this->rowFilter !== null && !($this->rowFilter)($row)) {
                continue;
            }

            if ($includeBreaks && $prev !== null) {
                foreach ($this->breakRowDetectors as $detector) {
                    if ($detector->needsBreak($prev, $row)) {
                        $rnd .= $detector->renderBreak($tbl, $row)."\n";
                        break;
                    }
                }
            }

            $prefix = '';
            $suffix = '';
            if ($this->rowTextPainter !== null) {
                $ps = $this->rowTextPainter->getPrefixSuffix($row, $options);
                $prefix = $ps[0] ?? '';
                $suffix = $ps[1] ?? '';
            }

            $rowCells = [];
            foreach ($this->columns as $column) {
                $rowValue = $column->content->render($row, $options);
                if ($this->rowTextPainter !== null) {
                    $rowValue = preg_replace('/(\>|^)([^<]+)(\<|$)/', '${1}'.$prefix.'${2}'.$suffix.'${3}', $rowValue);
                }
                $rowCells[] = $rowValue;
            }

            $rowAttrs = $rangeExpander !== null
                ? $rangeExpander->getRangeRowAttrs($row, $rangeExpanded)
                : [];
            if ($this->rowAttrsExt !== null) {
                // Range grouping and visibility are authoritative on key collisions.
                $rowAttrs = array_merge(($this->rowAttrsExt)($row), $rowAttrs);
            }
            $rnd .= $tbl->get_new_row_arr($rowCells, $rowAttrs)."\n";
            $prev = $record;
            $row->number++;
        }

        return $rnd;
    }

    public function renderRows(html_table_mc $tbl, array $records, array $options = []): string {
        $this->configureTable($tbl);
        return $this->renderRecords(
            $tbl,
            $records,
            $options,
            false,
            $this->getTimeRangeExpander(),
            true
        );
    }


    public function render( html_table_mc $tbl, array $records, array $options = []): string {

        $this->configureTable($tbl);

        $rnd = $tbl->get_css()."\n";
        $rnd .= $tbl->get_header()."\n";
        $rnd .= $tbl->get_header_row()."\n";

        $rangeExpander = $this->getTimeRangeExpander();
        $lazyRanges = $rangeExpander !== null
            && $this->filter instanceof TimeRangeSqlFilter
            && $this->rangeExpansionEndpoint !== null;

        if ($lazyRanges) {
            $availableRanges = $this->filter->getAvailableRanges();
            $expandedRanges = $this->filter->getExpandedRanges();
            $recordsByRange = [];
            foreach ($records as $record) {
                $recordsByRange[$rangeExpander->getRangeKey($record)][] = $record;
            }

            if (count($availableRanges) === 0) {
                $rnd .= $tbl->get_info_row('Nebyly nalezeny žádné záznamy.')."\n";
            }

            foreach ($availableRanges as $range) {
                $expanded = in_array($range, $expandedRanges, true);
                $separator = str_contains($this->rangeExpansionEndpoint, '?') ? '&' : '?';
                $url = $this->rangeExpansionEndpoint.$separator.http_build_query(['range' => $range]);
                $rnd .= $rangeExpander->renderRangeBreak($tbl, $range, $expanded, $url)."\n";
                if ($expanded) {
                    $rnd .= $this->renderRecords(
                        $tbl,
                        $recordsByRange[$range] ?? [],
                        $options,
                        false,
                        $rangeExpander,
                        true
                    );
                }
            }
        } else {
            $rnd .= $this->renderRecords($tbl, $records, $options, true);
        }

        return $rnd . $tbl->get_footer() . "\n";
    }
}


?>

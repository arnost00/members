<?
//==================================================================
// Rendered TABLE class
//==================================================================
require_once("ctable.inc.php");
require_once("common_race.inc.php");

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
    public function __construct(private string $field) {}

    public function render(RowData $row, array $options = []): string {
        return htmlspecialchars((string)($row->rec[$this->field] ?? ''));
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


    public function render( html_table_mc $tbl, array $records, array $options = []): string {

        // Render headers
        $col = 0;
        foreach ($this->columns as $column) {
            // get all defined headers
            $column->header->render ( $tbl, $col++ );
        }

        $rnd = $tbl->get_css()."\n";
        $rnd .= $tbl->get_header()."\n";
        $rnd .= $tbl->get_header_row()."\n";

        $prev = null;
        $row = new RowData( count($records) );
        foreach ($records as $record) {
            $row->rec = $record;

            if ( $this->rowFilter !== null ) {
                if ( ! ($this->rowFilter) ( $row ) ) {
                    // filtered
                    continue;
                }
            }

            if ($prev !== null) {
                // create first break between $prev and $record
                foreach ($this->breakRowDetectors as $detector) {
                    if ($detector->needsBreak($prev, $row)) {
                        $rnd .= $detector->renderBreak($tbl,$row) . "\n";
                        break; // only first one
                    }
                }
            }

            if ( $this->rowTextPainter !== null ) {
                // evaluate once per record
                $ps = $this->rowTextPainter->getPrefixSuffix($row,$options);
                $prefix = $ps[0] ?? '';
                $suffix = $ps[1] ?? '';
            }

            $rowCells = [];
            foreach ($this->columns as $column) {
                // get all defined cells
                $rowValue = $column->content->render($row,$options);

                if ( $this->rowTextPainter !== null ) {
                    // apply on each value
                    $rowValue = preg_replace ( '/(\>|^)([^<]+)(\<|$)/', '${1}' . $prefix . '${2}' . $suffix . '${3}', $rowValue);
                }
                $rowCells[] = $rowValue;
            }

            $row_add_attrs = ( $this->rowAttrsExt !== null ) ? ($this->rowAttrsExt) ( $row ) : [];
            $rnd .= $tbl->get_new_row_arr($rowCells,$row_add_attrs) . "\n";
            $prev = $record;
            $row->number++;
        }

        return $rnd . $tbl->get_footer() . "\n";
    }
}


?>
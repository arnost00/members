<?
//==================================================================
// Rendered TABLE class
//==================================================================
require_once("ctable.inc.php");
require_once("common_race.inc.php");

interface IColumnHeaderRenderer {
    public function render(html_table_mc $tbl, int &$col): void;
}

interface IColumnContentRenderer {
    public function render(array $record, array $options = []): string;
}

class DefaultHeaderRenderer implements IColumnHeaderRenderer {
    public function __construct(
        public string $label,
        public string $align = ALIGN_LEFT
    ) {}

    public function render(html_table_mc $tbl, int &$col): void {
        $tbl->set_header_col($col, $this->label, $this->align);
    }
}

class HelpHeaderRenderer implements IColumnHeaderRenderer {
    public function __construct(
        public string $label,
        public string $align,
        public string $help
    ) {}

    public function render(html_table_mc $tbl, int &$col): void {
        $tbl->set_header_col_with_help($col++, $this->label, $this->align, $this->help);
    }
}

class NoRenderer implements IColumnContentRenderer {
    public function __construct(private string $field) {}

    public function render(array $record, array $options = []): string {
        return htmlspecialchars((string)($this->field ?? ''));
    }
}

class DefaultRenderer implements IColumnContentRenderer {
    public function __construct(private string $field) {}

    public function render(array $record, array $options = []): string {
        return htmlspecialchars((string)($record[$this->field] ?? ''));
    }
}

// plain field renderer. Display direct value.
class FieldRenderer implements IColumnContentRenderer {
    public function __construct(private string $field) {}

    public function render(array $record, array $options = []): string {
        return htmlspecialchars((string)($record[$this->field] ?? ''));
    }
}

// Field renderer with visualised cancelation
class CancelableRenderer extends FieldRenderer {
    public function render(array $record, array $options = []): string {
        $value = parent::render($record) ?? '';
        return GetFormatedTextDel ( $value, $record['cancelled'] );
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

    public function render(array $record, array $options = []): string {
        $val = $record[$this->field] ?? '';
        return ($this->fn)( $val );
    }
}

// collected and formated by extern function, the function must ensure using htmlspecialchars 
class CallbackRenderer implements IColumnContentRenderer {
    private $fn;

    public function __construct(callable $fn) {
        $this->fn = $fn;
    }

    public function render(array $record, array $options = []): string {
        return ($this->fn)( $record, $options );
    }
}

// modifies plain texts on row, evaluated once per row
interface IRowTextPainter {
    public function getPrefixSuffix(array $record, array $options = [] ): array;
}

// Checks and creates table break
interface IBreakRowDetector {
    public function needsBreak(array $prev, array $curr): bool;
    public function renderBreak(html_table_mc $tbl, array $record): string;
}

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

    // row class/attributes extender
    private $rowAttrsExt = null;

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
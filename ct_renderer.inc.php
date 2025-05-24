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

?>
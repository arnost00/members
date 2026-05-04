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

class FinanceHistoryRendererFactory extends AColumnRendererFactory {
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

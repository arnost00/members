<?
//==================================================================
// Bank orphan payments table renderers
//==================================================================
require_once("ct_renderer.inc.php");

class BankOrphanActionRenderer implements IColumnContentRenderer {
    public function render(RowData $row, array $options = []): string {
        $id = (int)($row->rec['id'] ?? 0);
        return '<A HREF="javascript:open_win(\'./fin_bank_orphan_assign.php?tx_id='.$id.'\',\'\')">Přiřadit</A>';
    }
}

class BankOrphanRendererFactory extends AColumnRendererFactory {
    public static function createColRenderer(string $column_name): IColumnContentRenderer {
        return match ($column_name) {
            'poradi' => new CallbackRenderer(function (RowData $row, array $options): string {
                return (string)($row->number + 1);
            }),
            'created_at' => new DateTimeFieldRenderer($column_name),
            'moznosti' => new BankOrphanActionRenderer(),
            default => new DefaultRenderer($column_name),
        };
    }

    public static function createHeaderRenderer(string $column_name): IColumnHeaderRenderer {
        return match ($column_name) {
            'poradi' => new DefaultHeaderRenderer('Poř.č.', ALIGN_CENTER),
            'created_at' => new DefaultHeaderRenderer('Datum'),
            'amount' => new DefaultHeaderRenderer('Částka',ALIGN_RIGHT),
            'currency' => new DefaultHeaderRenderer('Měna'),
            'variable_symbol' => new DefaultHeaderRenderer('VS'),
            'originator_message' => new DefaultHeaderRenderer('Zpráva'),
            'moznosti' => new DefaultHeaderRenderer('Možnosti', ALIGN_CENTER),
            default => new DefaultHeaderRenderer($column_name),
        };
    }
}
?>

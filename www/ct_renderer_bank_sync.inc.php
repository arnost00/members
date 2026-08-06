<?
//==================================================================
// Bank sync import table renderers
//==================================================================
require_once("ct_renderer.inc.php");

class BankSyncImportRenderer implements IColumnContentRenderer {
    public function render(RowData $row, array $options = []): string {
        $tx_id = htmlspecialchars((string)($row->rec['transaction_id'] ?? ''));
        $amount = htmlspecialchars((string)($row->rec['amount'] ?? ''));
        $vs = htmlspecialchars((string)($row->rec['vs'] ?? ''));
        $msg = htmlspecialchars((string)($row->rec['msg'] ?? ''));
        $matched_user_id = htmlspecialchars((string)($row->rec['matched_user_id'] ?? ''));

        return '<input type="checkbox" name="tx['.$tx_id.'][import]" value="1" checked>'
            . '<input type="hidden" name="tx['.$tx_id.'][amount]" value="'.$amount.'">'
            . '<input type="hidden" name="tx['.$tx_id.'][vs]" value="'.$vs.'">'
            . '<input type="hidden" name="tx['.$tx_id.'][msg]" value="'.$msg.'">'
            . '<input type="hidden" name="tx['.$tx_id.'][matched_user_id]" value="'.$matched_user_id.'">';
    }
}

class BankSyncStatusRenderer implements IColumnContentRenderer {
    public function render(RowData $row, array $options = []): string {
        if (($row->rec['status'] ?? '') === 'PROCESSED') {
            return '<span class="status-processed">Přiřazeno</span>';
        }

        return '<span class="status-orphan">Sirotek</span>';
    }
}

class BankSyncMatchedUserRegRenderer implements IColumnContentRenderer {
    public function render(RowData $row, array $options = []): string {
        $matched_users = $options['matched_users'] ?? [];
        $uid = (int)($row->rec['matched_user_id'] ?? 0);

        if ($uid > 0 && isset($matched_users[$uid])) {
            $user = $matched_users[$uid];
            return htmlspecialchars((string)$user['reg']);
        }

        return '-';
    }
}

class BankSyncMatchedUserNameRenderer implements IColumnContentRenderer {
    public function render(RowData $row, array $options = []): string {
        $matched_users = $options['matched_users'] ?? [];
        $uid = (int)($row->rec['matched_user_id'] ?? 0);

        if ($uid > 0 && isset($matched_users[$uid])) {
            $user = $matched_users[$uid];
            return htmlspecialchars(trim($user['jmeno'] . ' ' . $user['prijmeni']));
        }

        return '-';
    }
}

class BankSyncRendererFactory extends AColumnRendererFactory {
    public static function createColRenderer(string $column_name): IColumnContentRenderer {
        return match ($column_name) {
            'import' => new BankSyncImportRenderer(),
            'created_at' => new DateTimeFieldRenderer($column_name, 'd.m.Y H:i'),
            'status' => new BankSyncStatusRenderer(),
            'matched_user_reg' => new BankSyncMatchedUserRegRenderer(),
            'matched_user_name' => new BankSyncMatchedUserNameRenderer(),
            default => new DefaultRenderer($column_name),
        };
    }

    public static function createHeaderRenderer(string $column_name): IColumnHeaderRenderer {
        return match ($column_name) {
            'import' => new DefaultHeaderRenderer('Import?', ALIGN_CENTER),
            'created_at' => new DefaultHeaderRenderer('Datum'),
            'amount' => new DefaultHeaderRenderer('Částka', ALIGN_RIGHT),
            'currency' => new DefaultHeaderRenderer('Měna'),
            'vs' => new DefaultHeaderRenderer('VS'),
            'msg' => new DefaultHeaderRenderer('Zpráva'),
            'status' => new DefaultHeaderRenderer('Stav'),
            'matched_user_reg' => new DefaultHeaderRenderer('Reg. č.'),
            'matched_user_name' => new DefaultHeaderRenderer('Jméno'),
            default => new DefaultHeaderRenderer($column_name),
        };
    }
}
?>

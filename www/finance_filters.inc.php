<?php

require_once __DIR__.'/sql_filter.inc.php';

abstract class FinanceSqlFilter extends SqlFilter
{
    public static function stringValue(array $input, string $name, string $default = ''): string
    {
        if (!array_key_exists($name, $input)) {
            return $default;
        }
        return is_scalar($input[$name]) ? trim((string)$input[$name]) : '';
    }

    public static function dateValue(array $input, string $name, string $default = ''): string
    {
        $value = self::stringValue($input, $name, $default);
        if ($value === '') {
            $value = $default;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value ? $value : '';
    }

    public static function numberValue(array $input, string $name): string
    {
        $value = self::stringValue($input, $name);
        return $value !== '' && is_numeric($value) ? $value : '';
    }

    public function getValue(string $name): string|bool
    {
        return property_exists($this, $name) ? $this->{$name} : '';
    }
}

final class FinanceHistoryFilter extends TimeRangeSqlFilter
{
    private string $date_from;
    private string $date_to;
    private string $member;
    private string $amount_from;
    private string $amount_to;
    private string $note;
    private bool $claim_only;

    public function __construct(array $input)
    {
        $this->date_from = FinanceSqlFilter::dateValue($input, 'date_from');
        $this->date_to = FinanceSqlFilter::dateValue($input, 'date_to');
        $this->member = FinanceSqlFilter::stringValue($input, 'member');
        $this->amount_from = FinanceSqlFilter::numberValue($input, 'amount_from');
        $this->amount_to = FinanceSqlFilter::numberValue($input, 'amount_to');
        $this->note = FinanceSqlFilter::stringValue($input, 'note');
        $this->claim_only = FinanceSqlFilter::stringValue($input, 'claim_only') === '1';
    }

    public function getValue(string $name): string|bool
    {
        return property_exists($this, $name) ? $this->{$name} : '';
    }

    public function getActiveParams(): array
    {
        $params = [];
        foreach (['date_from', 'date_to', 'member', 'amount_from', 'amount_to', 'note'] as $name) {
            if ($this->{$name} !== '') {
                $params[$name] = $this->{$name};
            }
        }
        if ($this->claim_only) {
            $params['claim_only'] = '1';
        }
        return $params;
    }

    public static function isValidRangeKey(string $range): bool
    {
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $range) !== 1) {
            return false;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m', $range);
        return $date !== false && $date->format('Y-m') === $range;
    }

    public function getBaseSqlFragment(): SqlFragment
    {
        $fragment = new SqlFragment();

        if ($this->date_from !== '') {
            $fragment = $fragment->and(new SqlFragment('f.date >= ?', 's', [$this->date_from]));
        }
        if ($this->date_to !== '') {
            $fragment = $fragment->and(new SqlFragment('f.date <= ?', 's', [$this->date_to]));
        }
        if ($this->member !== '') {
            $value = '%'.$this->member.'%';
            $fragment = $fragment->and(new SqlFragment(
                '(u.reg LIKE ? OR u.sort_name LIKE ?)',
                'ss',
                [$value, $value]
            ));
        }
        if ($this->amount_from !== '') {
            $fragment = $fragment->and(new SqlFragment('f.amount >= ?', 'd', [(float)$this->amount_from]));
        }
        if ($this->amount_to !== '') {
            $fragment = $fragment->and(new SqlFragment('f.amount <= ?', 'd', [(float)$this->amount_to]));
        }
        if ($this->note !== '') {
            $fragment = $fragment->and(new SqlFragment('f.note LIKE ?', 's', ['%'.$this->note.'%']));
        }
        if ($this->claim_only) {
            $fragment = $fragment->and(new SqlFragment('f.claim = 1'));
        }

        return $fragment;
    }

    protected function loadAvailableRanges(): array
    {
        $fragment = $this->getBaseSqlFragment();
        $sql = "SELECT DATE_FORMAT(f.date, '%Y-%m') range_key FROM `".TBL_FINANCE."` f "
            ."LEFT JOIN `".TBL_USER."` u ON u.id = f.id_users_user "
            ."WHERE f.storno IS NULL";
        if (!$fragment->isEmpty()) {
            $sql .= ' AND '.$fragment->sql;
        }
        $sql .= ' GROUP BY range_key ORDER BY range_key DESC';

        $stmt = db_prepare($sql);
        if ($stmt === false) {
            throw new RuntimeException('Unable to prepare available finance ranges query.');
        }
        $result = db_execute(true, $stmt, $fragment->types, $fragment->params);
        if ($result === false) {
            throw new RuntimeException('Unable to execute available finance ranges query.');
        }

        return array_map(
            static fn(array $row): string => (string)$row['range_key'],
            $result->fetch_all(MYSQLI_ASSOC)
        );
    }

    protected function getCurrentRangeKey(): string
    {
        return date('Y-m');
    }

    public function getSqlFragmentForRanges(array $ranges): SqlFragment
    {
        $fragment = $this->getBaseSqlFragment();
        $parts = [];
        $types = '';
        $params = [];
        foreach ($ranges as $range) {
            $range = (string)$range;
            if (!self::isValidRangeKey($range)) {
                continue;
            }

            $start = DateTimeImmutable::createFromFormat('!Y-m', $range);
            $parts[] = '(f.date >= ? AND f.date < ?)';
            $types .= 'ss';
            $params[] = $start->format('Y-m-d');
            $params[] = $start->modify('+1 month')->format('Y-m-d');
        }

        if (count($parts) === 0) {
            return $fragment->and(new SqlFragment('1 = 0'));
        }
        return $fragment->and(new SqlFragment(implode(' OR ', $parts), $types, $params));
    }
}

final class BankTransactionFilter extends FinanceSqlFilter
{
    protected string $date_from;
    protected string $date_to;
    protected string $variable_symbol;
    protected string $amount_from;
    protected string $amount_to;
    protected string $message;

    public function __construct(array $input)
    {
        $this->date_from = self::dateValue($input, 'date_from', date('Y-m-d', strtotime('-30 day')));
        $this->date_to = self::dateValue($input, 'date_to', date('Y-m-d'));
        $this->variable_symbol = self::stringValue($input, 'variable_symbol');
        $this->amount_from = self::numberValue($input, 'amount_from');
        $this->amount_to = self::numberValue($input, 'amount_to');
        $this->message = self::stringValue($input, 'message');
    }

    public function getActiveParams(): array
    {
        $params = [];
        foreach (['date_from', 'date_to', 'variable_symbol', 'amount_from', 'amount_to', 'message'] as $name) {
            if ($this->{$name} !== '') {
                $params[$name] = $this->{$name};
            }
        }
        return $params;
    }

    public function getSqlFragment(): SqlFragment
    {
        $fragment = new SqlFragment();
        if ($this->date_from !== '') {
            $fragment = $fragment->and(new SqlFragment('created_at >= ?', 's', [$this->date_from.' 00:00:00']));
        }
        if ($this->date_to !== '') {
            $end = (new DateTimeImmutable($this->date_to))->modify('+1 day')->format('Y-m-d').' 00:00:00';
            $fragment = $fragment->and(new SqlFragment('created_at < ?', 's', [$end]));
        }
        if ($this->variable_symbol !== '') {
            $fragment = $fragment->and(new SqlFragment('variable_symbol LIKE ?', 's', ['%'.$this->variable_symbol.'%']));
        }
        if ($this->amount_from !== '') {
            $fragment = $fragment->and(new SqlFragment('amount >= ?', 'd', [(float)$this->amount_from]));
        }
        if ($this->amount_to !== '') {
            $fragment = $fragment->and(new SqlFragment('amount <= ?', 'd', [(float)$this->amount_to]));
        }
        if ($this->message !== '') {
            $fragment = $fragment->and(new SqlFragment('originator_message LIKE ?', 's', ['%'.$this->message.'%']));
        }
        return $fragment;
    }
}

final class FinanceHistoryRepository
{
    public static function findForRanges(FinanceHistoryFilter $filter, array $ranges): array
    {
        $fragment = $filter->getSqlFragmentForRanges($ranges);
        if (count($ranges) === 0) {
            return [];
        }

        $sql = "SELECT unix_timestamp(f.date) datum, u.reg reg, u.sort_name name, "
            ."f.id_users_editor, e.sort_name editor_name, f.amount, f.note, "
            ."rc.nazev zavod_nazev, rc.datum zavod_datum FROM `".TBL_FINANCE."` f "
            ."LEFT JOIN `".TBL_USER."` u ON u.id = f.id_users_user "
            ."LEFT JOIN `".TBL_USER."` e ON e.id = f.id_users_editor "
            ."LEFT JOIN `".TBL_RACE."` rc ON f.id_zavod = rc.id "
            ."WHERE f.storno IS NULL";
        if (!$fragment->isEmpty()) {
            $sql .= ' AND '.$fragment->sql;
        }
        $sql .= ' ORDER BY f.date DESC, f.id DESC';

        $stmt = db_prepare($sql);
        if ($stmt === false) {
            throw new RuntimeException('Unable to prepare finance history query.');
        }
        $result = db_execute(true, $stmt, $fragment->types, $fragment->params);
        if ($result === false) {
            throw new RuntimeException('Unable to execute finance history query.');
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}

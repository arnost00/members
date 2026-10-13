<?php

/**
 * A parameterized SQL predicate without a WHERE/AND prefix.
 */
final class SqlFragment
{
    public function __construct(
        public readonly string $sql = '',
        public readonly string $types = '',
        public readonly array $params = []
    ) {
        if (strlen($types) !== count($params)) {
            throw new InvalidArgumentException('SQL bind types and values must have the same length.');
        }
    }

    public function isEmpty(): bool
    {
        return $this->sql === '';
    }

    public function and(self $other): self
    {
        if ($this->isEmpty()) {
            return $other;
        }
        if ($other->isEmpty()) {
            return $this;
        }

        return new self(
            '('.$this->sql.') AND ('.$other->sql.')',
            $this->types.$other->types,
            array_merge($this->params, $other->params)
        );
    }
}

abstract class SqlFilter
{
    abstract public function getSqlFragment(): SqlFragment;

    abstract public function getValue(string $name): string|bool;

    /**
     * Normalized active values suitable for links and follow-up requests.
     */
    abstract public function getActiveParams(): array;

    public function hasActiveFilters(): bool
    {
        return count($this->getActiveParams()) > 0;
    }
}

abstract class TimeRangeSqlFilter extends SqlFilter
{
    private ?array $availableRanges = null;

    final public function getAvailableRanges(): array
    {
        if ($this->availableRanges === null) {
            $this->availableRanges = array_values(array_unique($this->loadAvailableRanges()));
        }

        return $this->availableRanges;
    }

    public function getExpandedRanges(): array
    {
        $available = $this->getAvailableRanges();
        if (count($available) === 0) {
            return [];
        }

        $current = $this->getCurrentRangeKey();
        return in_array($current, $available, true) ? [$current] : [$available[0]];
    }

    final public function getSqlFragment(): SqlFragment
    {
        return $this->getSqlFragmentForRanges($this->getExpandedRanges());
    }

    abstract protected function loadAvailableRanges(): array;
    abstract protected function getCurrentRangeKey(): string;
    abstract public function getSqlFragmentForRanges(array $ranges): SqlFragment;
}

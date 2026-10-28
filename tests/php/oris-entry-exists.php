<?php
require_once __DIR__ . '/../../www/lib/oris_entry_lookup.php';

class EntryLookupStub {
    private $response;
    public function __construct($response) { $this->response = $response; }
    public function getEventEntries($eventId) {
        if ($eventId !== 123) throw new RuntimeException('Unexpected event ID');
        if ($this->response instanceof Exception) throw $this->response;
        return $this->response;
    }
}

$cases = [
    'present entry' => [[['ID' => 3337759]], true],
    'present string ID' => [[['ID' => '3337759']], true],
    'confirmed absence' => [[['ID' => 42]], false],
    'empty list' => [[], false],
    'associative list' => [[42 => ['ID' => '42']], false],
    'associative match' => [[3337759 => ['ID' => '3337759']], true],
    'lookup failure' => [new RuntimeException('Lookup failed'), true],
    'non-array response' => [null, true],
    'error envelope' => [['error' => 'Unavailable'], true],
    'missing ID after valid entry' => [[['ID' => 42], ['Name' => 'Invalid']], true],
    'null ID' => [[['ID' => null]], true],
    'array ID' => [[['ID' => []]], true],
    'boolean ID' => [[['ID' => true]], true],
    'float ID' => [[['ID' => 42.5]], true],
    'empty ID' => [[['ID' => '']], true],
    'zero ID' => [[['ID' => 0]], true],
    'negative ID' => [[['ID' => -42]], true],
    'non-numeric ID' => [[['ID' => 'invalid']], true],
];
foreach ($cases as $name => $case) {
    $actual = orisEntryExists(new EntryLookupStub($case[0]), 123, '3337759');
    if ($actual !== $case[1]) {
        throw new RuntimeException('Failed: ' . $name);
    }
}
echo count($cases) . " existence-check cases passed\n";

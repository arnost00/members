<?php
require_once __DIR__.'/../../www/lib/race_deadlines.php';
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
$cutoff = ParseOrisDeadline('2030-07-05 17:23:45');
$race = ['datum' => strtotime('2030-07-10 UTC'), 'prihlasky' => 1, 'prihlasky1' => $cutoff,
    'transport' => 3, 'ubytovani' => 1, 'transport_do' => null, 'ubytovani_do' => null];
foreach (['UTC', 'America/Los_Angeles', 'Asia/Tokyo', 'Europe/Prague'] as $zone) {
    date_default_timezone_set($zone);
    check(ParseOrisDeadline('2030-07-05 17:23:45') === $cutoff, 'ORIS parsing depends on PHP timezone');
    check(ParseOrisDeadline('2030-07-05T17:23:45+02:00') === $cutoff, 'Explicit offset lost');
    check(ParseOrisDeadline('2030-01-05 17:23:45') === strtotime('2030-01-05T16:23:45Z'), 'Winter offset');
    check(ParseRaceDeadline('05.07.2030 17:23:45') === $cutoff, 'Editor round trip');
    check(FormatRaceDeadline($cutoff) === '05.07.2030 17:23:45', 'Display zone');
    check(RaceRegistrationTerm($race, $cutoff-1) === 1, 'Before registration cutoff');
    check(RaceRegistrationTerm($race, $cutoff) === 0, 'At registration cutoff');
    check(RaceRegistrationTerm($race, $cutoff+1) === 0, 'After registration cutoff');
    check(RaceServiceOpen($race, 'transport', $cutoff-1), 'Before service cutoff');
    check(!RaceServiceOpen($race, 'transport', $cutoff), 'At service cutoff');
}
foreach (['31.02.2030 10:00:00', '31.03.2030 02:30:00', 'garbage'] as $invalid) {
    try { ParseRaceDeadline($invalid); throw new RuntimeException('Accepted invalid local time: '.$invalid); }
    catch (InvalidArgumentException $expected) {}
}
check(RaceDeadlineTimestamp($cutoff) === $cutoff, 'Stored timestamps must be used without adjustment');
$race['prihlasky1'] = $cutoff+500;
check(RaceServiceDeadline($race, 'transport') === $cutoff+500, 'Local first deadline inheritance');
$race['prihlasky1'] = $cutoff;
$race['ubytovani_do'] = $cutoff+500;
$race['transport_do'] = $cutoff-500;
check(RaceServiceDeadline($race, 'transport') === $cutoff-500, 'Explicit override');
check(RaceServiceOpen($race, 'accommodation', $cutoff+1), 'Independent service after registration closes');
$entry = ['transport' => 1, 'sedadel' => 3, 'ubytovani' => 1];
$values = RaceServiceValues($race, $entry, ['ubytovani' => 1], false, $cutoff);
check($values['transport'] === 1 && $values['sedadel'] === 3, 'Omitted closed fields must persist');
try { RaceServiceValues($race, $entry, ['sedadel' => 4], false, $cutoff); throw new RuntimeException('Forged closed service accepted'); }
catch (InvalidArgumentException $expected) {}
check(RaceServiceValues($race, $entry, ['sedadel' => 4], true, $cutoff)['sedadel'] === 4, 'Staff override');
check(RaceHasLockedBooking($race, $entry, $cutoff), 'Locked booking cancellation');
$html = RaceServiceIndicators($race, $cutoff-1);
check(str_contains($html, '<s>D</s>') && !str_contains($html, '<s>U</s>'), 'Independent indicators');
check(str_contains(RaceServiceIndicators($race, $cutoff), '<s>D</s>'), 'Show an independently closed transport deadline after registration closes');
$race['transport'] = 2;
check(RaceServiceValues($race, null, [], false, $cutoff)['transport'] === 1, 'Automatic booking ignores service deadline');
check(RaceServiceValues($race, null, [], false, $cutoff-501)['transport'] === 1, 'Automatic open booking');
foreach ([null, 0, 1] as $legacyValue) {
    $automaticRace = array_merge($race, ['transport' => 2, 'ubytovani' => 2]);
    $legacyEntry = ['transport' => $legacyValue, 'ubytovani' => $legacyValue];
    check(!RaceHasLockedBooking($automaticRace, $legacyEntry, $cutoff), 'Automatic services must not block cancellation');
    check(RaceServiceValues($automaticRace, $legacyEntry, [], false, $cutoff)['transport'] === $legacyValue, 'Preserve legacy automatic value');
    check(RaceServiceIndicators($automaticRace, $cutoff) === '', 'No independent automatic service indicators');
}
$race['transport'] = 3;
$race['ubytovani_do'] = null; $race['transport_do'] = null;
check(RaceServiceIndicators($race, $cutoff) === '', 'Equal deadlines should hide indicators');
$zeroDeadlineRace = array_merge($race, ['transport_do' => 0, 'ubytovani_do' => 0]);
check(RaceServiceDeadline($zeroDeadlineRace, 'transport') === $cutoff, 'Zero transport deadline must inherit');
check(RaceServiceDeadline($zeroDeadlineRace, 'accommodation') === $cutoff, 'Zero accommodation deadline must inherit');
check(RaceServiceIndicators($zeroDeadlineRace, $cutoff-1) === '', 'Unset zero service deadlines must hide indicators before registration closes');
$explicitEqualRace = array_merge($race, ['transport_do' => $cutoff, 'ubytovani_do' => $cutoff]);
check(RaceServiceIndicators($explicitEqualRace, $cutoff) === '', 'Explicit service deadlines equal to registration must render as inherited');
$laterTermRace = array_merge($race, ['prihlasky' => 2, 'prihlasky2' => $cutoff+1000]);
check(RaceServiceIndicators($laterTermRace, $cutoff-1) === '', 'Open inherited services need no indicators');
$html = RaceServiceIndicators($laterTermRace, $cutoff);
check(RaceRegistrationTerm($laterTermRace, $cutoff) === 2, 'Second registration term is open');
check(str_contains($html, '<s>D</s>') && str_contains($html, '<s>U</s>'), 'Closed inherited services must show beside an open later registration term');

$race['prihlasky'] = 0; $race['prihlasky1'] = 0;
check(RaceServiceOpen($race, 'transport', $cutoff), 'No-deadline fallback');
echo "Deadline tests passed\n";

// Exercise the actual ORIS refresh mapping without contacting the external API.
define('__HIDE_TEST__', true);
require_once __DIR__.'/../../www/common.inc.php';
require_once __DIR__.'/../../www/url.inc.php';
require_once __DIR__.'/../../www/common_race2.inc.php';
require_once __DIR__.'/../../www/lib/OrisDTOs.php';
require_once __DIR__.'/../../www/lib/race_import.php';
$cancelledRace = ['prihlasky'=>1, 'prihlasky1'=>$cutoff+86400, 'cancelled'=>1];
check(raceterms::GetActiveRegDateArr($cancelledRace) === [0, 0], 'Cancelled race has no active registration term in list views');
$g_modify_flag = [['id'=>1], ['id'=>2], ['id'=>4]];
$existing = ['datum'=>0, 'datum2'=>0, 'prihlasky'=>5,
    'prihlasky1'=>$cutoff, 'prihlasky2'=>$cutoff, 'prihlasky3'=>$cutoff, 'prihlasky4'=>$cutoff, 'prihlasky5'=>$cutoff,
    'modify_flag'=>0, 'cancelled'=>0, 'transport_do'=>$cutoff-100, 'ubytovani_do'=>null];
$dto = new RaceDTO(['datum'=>(string)($cutoff+864000), 'prihlasky'=>$cutoff, 'prihlasky1'=>$cutoff+60, 'prihlasky2'=>$cutoff+120, 'typ'=>1]);
$updates = createRaceUpdateMap($dto, $existing, [['id'=>1,'enum'=>'ob']]);
check($updates['prihlasky1'] === $cutoff && $updates['prihlasky2'] === $cutoff+60 && $updates['prihlasky3'] === $cutoff+120, 'ORIS exact refresh timestamps');
check($updates['prihlasky4'] === $cutoff && $updates['prihlasky5'] === $cutoff, 'Local terms must remain unchanged');
$merged = array_replace($existing, $updates);
check(RaceServiceDeadline($merged, 'transport') === $cutoff-100, 'Refresh must preserve service override');
check(RaceServiceDeadline($merged, 'accommodation') === $cutoff, 'Inherited service must follow refreshed ORIS cutoff');
$dto->prihlasky += 7200;
$merged = array_replace($merged, createRaceUpdateMap($dto, $merged, [['id'=>1,'enum'=>'ob']]));
check(RaceServiceDeadline($merged, 'accommodation') === $cutoff+7200, 'Later refresh updates inheritance');
check($merged['prihlasky4'] === $cutoff, 'Repeated refresh must preserve local terms');
$merged['prihlasky1'] = $cutoff+10800;
check(RaceServiceDeadline($merged, 'accommodation') === $cutoff+10800, 'Local edits override the imported default');
check(RaceServiceDeadline($merged, 'transport') === $cutoff-100, 'Local edits preserve custom service overrides');
echo "ORIS refresh mapping tests passed\n";

// Invalid deadlines must fail only their race, allowing the batch to continue.
$g_external_is_connector = 'OrisCZConnector';
require_once __DIR__.'/../../www/connectors.php';
$service = new class {
    public array $event = [
        'ID'=>1, 'Date'=>'2030-07-10', 'Name'=>'Deadline test', 'Place'=>'Prague',
        'Sport'=>['ID'=>1], 'Level'=>['ID'=>4], 'Ranking'=>0, 'Stages'=>1,
        'EntryDate1'=>'2030-07-05 17:23:45', 'EntryDate2'=>'', 'EntryDate3'=>'',
        'EntryKoef2'=>0, 'EntryKoef3'=>0,
    ];
    public function getEvent($raceId) { return $this->event; }
};
$connectorClass = new ReflectionClass(OrisCZConnector::class);
$connector = $connectorClass->newInstanceWithoutConstructor();
$serviceProperty = $connectorClass->getProperty('service');
$serviceProperty->setAccessible(true);
$serviceProperty->setValue($connector, $service);
foreach (['EntryDate1', 'EntryDate2', 'EntryDate3'] as $field) {
    $original = $service->event[$field];
    foreach (['garbage', '2030-02-31 17:23:45'] as $invalid) {
        $service->event[$field] = $invalid;
        check($connector->getRaceInfo('1') === null, 'Invalid '.$field.' must fail only this race');
        $service->event[$field] = $original;
        $nextRace = $connector->getRaceInfo('2');
        check($nextRace instanceof RaceDTO && $nextRace->prihlasky === $cutoff,
            'A valid race after an invalid deadline must still load with its exact deadline');
        check($nextRace->prihlasky1 === 0 && $nextRace->prihlasky2 === 0,
            'Empty optional ORIS deadlines must remain valid');
    }
}
echo "ORIS connector deadline failure tests passed\n";

$autumnFirst = ParseOrisDeadline('2030-10-27T02:30:00+02:00');
$autumnSecond = ParseOrisDeadline('2030-10-27T02:30:00+01:00');
foreach ([$autumnFirst, $autumnSecond] as $instant) {
    $stored = ['prihlasky1'=>$instant, 'transport_do'=>$instant];
    $values = RaceDeadlineFormValues(['prihlasky1'=>FormatRaceDeadline($instant), 'transport_do'=>FormatRaceDeadline($instant)], $stored);
    check($values['prihlasky1'] === $instant && $values['transport_do'] === $instant, 'Repeated autumn time must round trip unchanged');
}
echo "Autumn round-trip tests passed\n";
$sameDay = ['datum'=>ParseOrisDeadline('2030-07-05 00:00:00'), 'prihlasky'=>1, 'prihlasky1'=>$cutoff];
check(RaceRegistrationTerm($sameDay, $cutoff-1) === 1, 'An exact race-day deadline must not close at midnight');
check(RaceRegistrationTerm($sameDay, $cutoff) === 0, 'Exact race-day deadline closes at its timestamp');
echo "Race-day deadline tests passed\n";

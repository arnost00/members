<?php
// All stored deadlines are absolute timestamps.
function RaceDeadlineTimestamp($value): int
{
    if (empty($value)) return 0;
    return (int)$value;
}

function ParseOrisDeadline($value): int
{
    if (empty($value)) return 0;
    try {
        $date = new DateTimeImmutable($value, new DateTimeZone('Europe/Prague'));
        $errors = DateTimeImmutable::getLastErrors();
        if ($errors && ($errors['warning_count'] || $errors['error_count'])) throw new InvalidArgumentException('Invalid ORIS date');
        return $date->getTimestamp();
    } catch (Exception $e) {
        throw new InvalidArgumentException('Neplatný termín ORIS.');
    }
}

function ParseRaceDeadline($value): int
{
    $value = trim((string)$value);
    if ($value === '') return 0;
    // Date-only input remains accepted for existing clients, at the end of that Czech day.
    if (preg_match('/^\d{1,2}\.\d{1,2}\.\d{4}$/', $value)) $value .= ' 23:59:59';
    foreach (['!j.n.Y H:i:s', '!j.n.Y H:i'] as $format) {
        $date = DateTimeImmutable::createFromFormat($format, $value, new DateTimeZone('Europe/Prague'));
        $errors = DateTimeImmutable::getLastErrors();
        if ($date && (!$errors || (!$errors['warning_count'] && !$errors['error_count']))) {
            // Reject nonexistent local times during the spring daylight-saving transition.
            $normalized = preg_replace('/\b0(\d)(?=\.)/', '$1', $value);
            if ($date->format(str_contains($format, ':s') ? 'j.n.Y H:i:s' : 'j.n.Y H:i') === $normalized) return $date->getTimestamp();
        }
    }
    throw new InvalidArgumentException('Neplatný termín. Použijte DD.MM.RRRR HH:MM:SS (Europe/Prague).');
}

function FormatRaceDeadline($timestamp): string
{
    return empty($timestamp) ? '' : (new DateTimeImmutable('@'.(int)$timestamp))
        ->setTimezone(new DateTimeZone('Europe/Prague'))->format('d.m.Y H:i:s');
}

function RaceDeadlineDisplay(array $race, $value): string
{
    if (empty($value)) return '';
    $deadline = (new DateTimeImmutable('@'.(int)$value))->setTimezone(new DateTimeZone('Europe/Prague'));
    if ($deadline->format('H:i:s') === '23:59:59') return $deadline->format('d.m.Y');

    // Calendar subtraction preserves the previous Czech day across DST changes.
    $day = $deadline->modify('-1 day')->format('d.m.Y');
    $exact = htmlspecialchars($deadline->format('d.m.Y H:i:s T'), ENT_QUOTES);
    return '<span title="'.$exact.'">'.$day.'&nbsp;<span aria-label="Přesný termín: '.$exact.'">◷</span></span>';
}

function RaceServiceDeadline(array $race, string $service): int
{
    $field = $service === 'transport' ? 'transport_do' : 'ubytovani_do';
    if (!empty($race[$field])) return (int)$race[$field];
    return RaceDeadlineTimestamp($race['prihlasky1'] ?? 0);
}

function RaceServiceOpen(array $race, string $service, ?int $now = null): bool
{
    $now = $now ?? time();
    $setting = $service === 'transport' ? 'transport' : 'ubytovani';
    $enabled = $service === 'transport' ? ($GLOBALS['g_enable_race_transport'] ?? true) : ($GLOBALS['g_enable_race_accommodation'] ?? true);
    if (!$enabled || empty($race[$setting]) || !empty($race['cancelled'])) return false;
    // Automatic services belong to the registration and cannot be edited independently.
    if ((int)$race[$setting] === 2) return false;
    $deadline = RaceServiceDeadline($race, $service);
    if ($deadline) return $now < $deadline;
    return RaceRegistrationTerm($race, $now) !== 0;
}

function RaceRegistrationTerm(array $race, ?int $now = null): int
{
    $now = $now ?? time();
    if (!empty($race['cancelled'])) return 0;
    // Registration without a deadline retains the race-day cutoff.
    $today = (new DateTimeImmutable('@'.$now))->setTimezone(new DateTimeZone('Europe/Prague'))->format('Y-m-d');
    if (empty($race['prihlasky']) && !empty($race['datum']) && (new DateTimeImmutable('@'.(int)$race['datum']))->setTimezone(new DateTimeZone('Europe/Prague'))->format('Y-m-d') <= $today) return 0;
    if (empty($race['prihlasky'])) return 1;
    for ($i = 1; $i <= (int)$race['prihlasky']; $i++) {
        $deadline = RaceDeadlineTimestamp($race['prihlasky'.$i] ?? 0);
        if ($deadline && $now < $deadline) return $i;
    }
    return 0;
}

function RaceHasLockedBooking(array $race, array $entry, ?int $now = null): bool
{
    foreach (['transport' => 'transport', 'accommodation' => 'ubytovani'] as $service => $field) {
        if ((int)($race[$field] ?? 0) === 2) continue;
        $booked = !empty($entry[$field]);
        if ($booked && !RaceServiceOpen($race, $service, $now)) return true;
    }
    return false;
}

function RaceServiceIndicators(array $race, ?int $now = null, ?string $editUrl = null): string
{
    $html = '';
    $first = RaceDeadlineTimestamp($race['prihlasky1'] ?? 0);
    foreach (['transport' => ['transport', 'D'], 'accommodation' => ['ubytovani', 'U']] as $service => [$setting, $letter]) {
        $enabled = $service === 'transport' ? ($GLOBALS['g_enable_race_transport'] ?? true) : ($GLOBALS['g_enable_race_accommodation'] ?? true);
        $deadline = RaceServiceDeadline($race, $service);
        if (!$enabled || empty($race[$setting]) || (int)$race[$setting] === 2) continue;
        $open = RaceServiceOpen($race, $service, $now);
        // An inherited first-term deadline can close before later registration terms.
        if ($deadline === $first && ($open || RaceRegistrationTerm($race, $now) === 0)) continue;
        $label = $open ? $letter : '<s>'.$letter.'</s>';
        if ($open && $editUrl !== null) {
            $label = '<a href="'.htmlspecialchars($editUrl, ENT_QUOTES).'" onclick="open_win(this.href, \'\'); return false;">'.$letter.'</a>';
        }
        $html .= ' <span title="'.htmlspecialchars(FormatRaceDeadline($deadline).' Europe/Prague', ENT_QUOTES).'">'.$label.'</span>';
    }
    return $html;
}

function RaceDeadlineFormValues(array $request, array $existing = []): array
{
    try {
        $values = [];
        for ($i = 1; $i <= 5; $i++) {
            $field = 'prihlasky'.$i;
            $previous = RaceDeadlineTimestamp($existing[$field] ?? 0);
            // Preserve the exact instant when a repeated autumn clock time is unchanged.
            $values[$field] = !array_key_exists($field, $request) || trim((string)$request[$field]) === FormatRaceDeadline($previous)
                ? $previous : ParseRaceDeadline($request[$field]);
        }
        foreach (['transport_do', 'ubytovani_do'] as $field) {
            $previous = $existing[$field] ?? null;
            $values[$field] = !array_key_exists($field, $request) || trim((string)$request[$field]) === FormatRaceDeadline($previous)
                ? ($previous ?? 'NULL') : (ParseRaceDeadline($request[$field]) ?: 'NULL');
        }
        return $values;
    } catch (InvalidArgumentException $e) {
        http_response_code(400);
        exit(htmlspecialchars($e->getMessage(), ENT_QUOTES));
    }
}

function RenderServiceDeadlineInput(string $service, array $race): string
{
    $field = $service === 'transport' ? 'transport_do' : 'ubytovani_do';
    $value = FormatRaceDeadline($race[$field] ?? 0);
    $inherited = $race;
    $inherited[$field] = null;
    $default = FormatRaceDeadline(RaceServiceDeadline($inherited, $service));
    return '<br><label>Registrace do: <input type="text" size="19" name="'.$field.'" value="'.$value.'" placeholder="'.$default.'"></label>'
        .' <small>DD.MM.RRRR HH:MM:SS, Europe/Prague.</small>';
}

// Normalize only enabled services; missing disabled fields preserve existing bookings.
function RaceServiceValues(array $race, ?array $entry, array $request, bool $staff = false, ?int $now = null): array
{
    $values = ['transport' => 0, 'sedadel' => null, 'ubytovani' => 0];
    foreach (['transport' => 'transport', 'accommodation' => 'ubytovani'] as $service => $field) {
        $mode = (int)($race[$field] ?? 0);
        if ($mode === 2) {
            // Preserve legacy values: automatic participation follows the category, not this flag.
            $values[$field] = $entry ? ($entry[$field] ?? null) : 1;
            if ($service === 'transport') $values['sedadel'] = $entry['sedadel'] ?? null;
            continue;
        }
        $open = $staff || RaceServiceOpen($race, $service, $now);
        $old = $entry[$field] ?? null;
        if (!$open) {
            $values[$field] = $entry ? $old : 0;
            if ($service === 'transport') $values['sedadel'] = $entry['sedadel'] ?? null;
            if (isset($request[$field]) && (int)(bool)$request[$field] !== (int)(bool)$old) {
                throw new InvalidArgumentException('Termín dopravy nebo ubytování již vypršel. Kontaktujte přihlašovatele.');
            }
            if ($service === 'transport' && isset($request['sedadel']) && (string)$request['sedadel'] !== (string)($entry['sedadel'] ?? '')) {
                throw new InvalidArgumentException('Termín sdílené dopravy již vypršel.');
            }
            continue;
        }
        if ($mode === 1) $values[$field] = isset($request[$field]) ? 1 : 0;
        elseif ($service === 'transport' && $mode === 3) {
            $seats = $request['sedadel'] ?? '';
            if ($seats !== '' && $seats !== 'null') {
                if (filter_var($seats, FILTER_VALIDATE_INT) === false) throw new InvalidArgumentException('Neplatný počet sedadel.');
                $values['sedadel'] = (int)$seats;
                $values[$field] = 1;
            }
        }
    }
    return $values;
}

function RaceServiceSqlValue($value): string
{
    return $value === null ? 'NULL' : (string)(int)$value;
}

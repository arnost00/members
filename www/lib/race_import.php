<?php
// Pure ORIS-to-race mapping shared by refresh and regression tests.
require_once __DIR__.'/race_deadlines.php';
function mapRaceTypeToDbEnum($typeValue, array $raceTypes): string
{
	foreach ($raceTypes as $raceType) {
		if ((string)$typeValue === (string)$raceType['id'] || (string)$typeValue === (string)$raceType['enum']) {
			return (string)$raceType['enum'];
		}
	}

	return (string)$typeValue;
}

function createRaceUpdateMap(RaceDTO $raceInfo, array $raceRow, array $raceTypes): array
{
	$datum = (int)$raceInfo->datum;
	$vicedenni = ($raceInfo->vicedenni == 1) ? 1 : 0;
	$datum2 = $vicedenni ? (int)$raceInfo->datum2 : 0;
	$etap = $vicedenni ? max((int)$raceInfo->etap, 1) : 1;
	// Keep exact ORIS instants and leave locally defined terms 4/5 unchanged.
	$prihlasky1 = !empty($raceInfo->prihlasky) ? (int)$raceInfo->prihlasky : 0;
	$prihlasky2 = !empty($raceInfo->prihlasky1) ? (int)$raceInfo->prihlasky1 : 0;
	$prihlasky3 = !empty($raceInfo->prihlasky2) ? (int)$raceInfo->prihlasky2 : 0;
	$prihlasky4 = RaceDeadlineTimestamp($raceRow['prihlasky4']);
	$prihlasky5 = RaceDeadlineTimestamp($raceRow['prihlasky5']);

	$prihlasky = 0;
	if ($prihlasky1 != 0) $prihlasky++;
	if ($prihlasky2 != 0) $prihlasky++;
	if ($prihlasky3 != 0) $prihlasky++;
	if ($prihlasky4 != 0) $prihlasky++;
	if ($prihlasky5 != 0) $prihlasky++;

	$modify_flag = ($prihlasky != $raceRow['prihlasky']
		|| $prihlasky1 != $raceRow['prihlasky1']
		|| $prihlasky2 != $raceRow['prihlasky2']
		|| $prihlasky3 != $raceRow['prihlasky3']
		|| $prihlasky4 != $raceRow['prihlasky4']
		|| $prihlasky5 != $raceRow['prihlasky5']) ? $GLOBALS['g_modify_flag'][0]['id'] : 0;

	if ($datum != $raceRow['datum'] || $datum2 != $raceRow['datum2'])
		$modify_flag += $GLOBALS['g_modify_flag'][2]['id'];

	$modify_flag = gen_modify_flag_v2b($raceRow['modify_flag'], $modify_flag);

	$odkaz = (string)$raceInfo->odkaz;
	if ($odkaz != '')
		$odkaz = cononize_url($odkaz, 1);
	$typ = mapRaceTypeToDbEnum($raceInfo->typ, $raceTypes);

	$update = [];
	$update['ext_id'] = (string)$raceInfo->ext_id;
	$update['datum'] = $datum;
	$update['datum2'] = $datum2;
	$update['nazev'] = (string)$raceInfo->nazev;
	$update['misto'] = (string)$raceInfo->misto;
	$update['typ0'] = 'Z';
	$update['typ'] = $typ;
	$update['zebricek'] = (int)$raceInfo->zebricek2;
	$update['ranking'] = (string)$raceInfo->ranking;
	$update['odkaz'] = $odkaz;
	$update['prihlasky'] = $prihlasky;
	$update['prihlasky1'] = $prihlasky1;
	$update['prihlasky2'] = $prihlasky2;
	$update['prihlasky3'] = $prihlasky3;
	$update['prihlasky4'] = $prihlasky4;
	$update['prihlasky5'] = $prihlasky5;
	$update['etap'] = $etap;
	$update['oddil'] = (string)$raceInfo->oddil;
	$update['kategorie'] = (string)$raceInfo->kategorie;
	$update['vicedenni'] = $vicedenni;
	$update['cancelled'] = ($raceInfo->cancelled === null) ? (int)$raceRow['cancelled'] : (int)$raceInfo->cancelled;
	$update['entry_start'] = $raceInfo->entry_start;
	$update['modify_flag'] = $modify_flag;

	return $update;
}


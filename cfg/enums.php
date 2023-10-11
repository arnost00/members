<?php

// $g_zebricek [0]['id'] = 0x0001;
// $g_zebricek [0]['nm'] = 'Celostátní';
// $g_zebricek [1]['id'] = 0x0002;
// $g_zebricek [1]['nm'] = 'Morava';
// $g_zebricek [2]['id'] = 0x0004;
// $g_zebricek [2]['nm'] = 'Čechy';
// $g_zebricek [3]['id'] = 0x0008;
// $g_zebricek [3]['nm'] = 'Oblastní';
// $g_zebricek [4]['id'] = 0x0010;
// $g_zebricek [4]['nm'] = 'Mistrovství';
// $g_zebricek [5]['id'] = 0x0020;
// $g_zebricek [5]['nm'] = 'Štafety';
// $g_zebricek [6]['id'] = 0x0080;
// $g_zebricek [6]['nm'] = 'Veřejný';

$g_zebricek = [
	[ "id" => 1, "nm" => 'Celostátní' ],
	[ "id" => 2, "nm" => 'Morava' ],
	[ "id" => 4, "nm" => 'Čechy' ],
	[ "id" => 8, "nm" => 'Oblastní' ],
	[ "id" => 16, "nm" => 'Mistrovství' ],
	[ "id" => 32, "nm" => 'Štafety' ],
	[ "id" => 128, "nm" => 'Veřejný' ],
];

// $g_zebricek_cnt = 7;
$g_zebricek_cnt = count($g_zebricek);

// $g_racetype [0]['id'] = 0x0001;
// $g_racetype [0]['nm'] = 'OB';
// $g_racetype [0]['enum'] = 'ob';
// $g_racetype [0]['img'] = 'fot';
// $g_racetype [1]['id'] = 0x0002;
// $g_racetype [1]['nm'] = 'MTBO';
// $g_racetype [1]['enum'] = 'mtbo';
// $g_racetype [1]['img'] = 'mbo';
// $g_racetype [2]['id'] = 0x0004;
// $g_racetype [2]['nm'] = 'LOB';
// $g_racetype [2]['enum'] = 'lob';
// $g_racetype [2]['img'] = 'ski';
// $g_racetype [3]['id'] = 0x0008;
// $g_racetype [3]['nm'] = 'O-Trail';
// $g_racetype [3]['enum'] = 'trail';
// $g_racetype [3]['img'] = 'trl';
// $g_racetype [4]['id'] = 0x0010;
// $g_racetype [4]['nm'] = 'Jiné';
// $g_racetype [4]['enum'] = 'jine';
// $g_racetype [4]['img'] = 'mcs';

$g_racetype = [
	[ 'id' => 1, 'nm' => 'OB', 'enum' => 'ob', 'img' => 'fot' ],
	[ 'id' => 2, 'nm' => 'MTBO', 'enum' => 'mtbo', 'img' => 'mbo' ],
	[ 'id' => 4, 'nm' => 'LOB', 'enum' => 'lob', 'img' => 'ski' ],
	[ 'id' => 8, 'nm' => 'O-Trail', 'enum' => 'trail', 'img' => 'trl' ],
	[ 'id' => 16, 'nm' => 'Jiné', 'enum' => 'jine', 'img' => 'mcs' ],
];

// $g_racetype_cnt = 5;
$g_racetype_cnt = count($g_racetype);

// $g_modify_flag [0]['id'] = 0x0001;
// $g_modify_flag [0]['nm'] = 'Termín přihlášek';
// $g_modify_flag [1]['id'] = 0x0002;
// $g_modify_flag [1]['nm'] = 'Závod přidán';
// $g_modify_flag [2]['id'] = 0x0004;
// $g_modify_flag [2]['nm'] = 'Termin závodu';

$g_modify_flag = [
	['id' => 1, 'nm' => 'Termín přihlášek'],
	['id' => 2, 'nm' => 'Závod přidán'],
	['id' => 4, 'nm' => 'Termin závodu'],
];

// $g_modify_flag_cnt = 3;
$g_modify_flag_cnt = count($g_modify_flag);

$g_racetype0 = array(
	'Z' => 'Závod',
	'T' => 'Trénink',
	'S' => 'Soustředění',
	'V' => 'Sportovní vyšetření',
	'N' => 'Nákup oblečení',
	'J' => 'Jiné'
);

// $g_racetype0_idx[0] = 'Z';
// $g_racetype0_idx[1] = 'T';
// $g_racetype0_idx[2] = 'S';
// $g_racetype0_idx[3] = 'V';
// $g_racetype0_idx[4] = 'N';
// $g_racetype0_idx[5] = 'J';

$g_racetype0_idx = array_keys($g_racetype0);

// $g_racetype0_cnt = 6;
$g_racetype0_cnt = count($g_racetype0_idx);

?>
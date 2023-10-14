<?php

$g_zebricek = [
	[ "id" => 1, "nm" => 'Celostátní' ],
	[ "id" => 2, "nm" => 'Morava' ],
	[ "id" => 4, "nm" => 'Čechy' ],
	[ "id" => 8, "nm" => 'Oblastní' ],
	[ "id" => 16, "nm" => 'Mistrovství' ],
	[ "id" => 32, "nm" => 'Štafety' ],
	[ "id" => 128, "nm" => 'Veřejný' ],
];
$g_zebricek_cnt = count($g_zebricek);

$g_racetype = [
	[ 'id' => 1, 'nm' => 'OB', 'enum' => 'ob', 'img' => 'fot' ],
	[ 'id' => 2, 'nm' => 'MTBO', 'enum' => 'mtbo', 'img' => 'mbo' ],
	[ 'id' => 4, 'nm' => 'LOB', 'enum' => 'lob', 'img' => 'ski' ],
	[ 'id' => 8, 'nm' => 'O-Trail', 'enum' => 'trail', 'img' => 'trl' ],
	[ 'id' => 16, 'nm' => 'Jiné', 'enum' => 'jine', 'img' => 'mcs' ],
];
$g_racetype_cnt = count($g_racetype);

$g_modify_flag = [
	['id' => 1, 'nm' => 'Termín přihlášek'],
	['id' => 2, 'nm' => 'Závod přidán'],
	['id' => 4, 'nm' => 'Termin závodu'],
];
$g_modify_flag_cnt = count($g_modify_flag);

$g_racetype0 = array(
	'Z' => 'Závod',
	'T' => 'Trénink',
	'S' => 'Soustředění',
	'V' => 'Sportovní vyšetření',
	'N' => 'Nákup oblečení',
	'J' => 'Jiné'
);
$g_racetype0_idx = array_keys($g_racetype0);
$g_racetype0_cnt = count($g_racetype0_idx);

?>
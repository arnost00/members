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

$g_racetype0 = [
	'Z' => 'Závod',
	'T' => 'Trénink',
	'S' => 'Soustředění',
	'V' => 'Sportovní vyšetření',
	'N' => 'Nákup oblečení',
	'J' => 'Jiné'
];
$g_racetype0_idx = array_keys($g_racetype0);
$g_racetype0_cnt = count($g_racetype0_idx);

// Typy plateb
$g_payement_type = [
	'C' => 'celá',
	'P' => 'pevná',
	'R' => 'rozdíl',
];

// Uctovani plateb
$g_uctovano = [
	[ "id" => 1, "nm" => 'Startovné', "char" => '🏁' ],
	[ "id" => 2, "nm" => 'Doprava', "char" => '🚌' ],
	[ "id" => 4, "nm" => 'Ubytování', "char" => '🛏️' ]
];

// Volby pro externi systemy
// Identifikator A oblasti v informacnim systemu
$g_external_is_region_A = 'ČR';

// Volby pro sdilenou dopravu
$g_sedadel_cnt = [
	null => "nejedu",
	-1 => "potřebuji místo",
	4 => "vezmu 4 osoby",
	3 => "vezmu 3 osoby",
	2 => "vezmu 2 osoby",
	1 => "vezmu 1 osobu",
	0 => "vezmu 0 osob",
	5 => "vezmu 5 osob",
	6 => "vezmu 6 osob",
	7 => "vezmu 7 osob",
	8 => "vezmu 8 osob",
];

$g_fin_mail_flag = [
	['id' => 1, 'nm' => 'Učet pod hranicí'],
	['id' => 2, 'nm' => 'Účet v mínusu'],
];
$g_fin_mail_flag_cnt = count($g_fin_mail_flag);

$g_notify_type_flag = [
	['id' => 1, 'nm' => 'Upozornění na email'],
	['id' => 2, 'nm' => 'Upozornění push notifikaci'],
];
$g_notify_type_flag_cnt = count($g_notify_type_flag);

?>

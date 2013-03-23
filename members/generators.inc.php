<?php if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>

<?

function GeneratePassword($length)
{
// based on https://gist.github.com/tylerhall/521810#file-strong-passwords-php

	$sets = array();
	$sets[] = 'abcdefghjkmnopqrstuvwxyz';
	$sets[] = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
	$sets[] = '23456789';

	$all = '';
	$password = '';
	foreach($sets as $set)
	{
		$password .= $set[array_rand(str_split($set))];
		$all .= $set;
	}

	$all = str_split($all);
	for($i = 0; $i < $length - count($sets); $i++)
		$password .= $all[array_rand($all)];

	$password = str_shuffle($password);

	return $password;
}

// codepage 1250 to ascii
function cp2ascii($text)
{
	$text=strtr($text, 'áèïéìíòóøšúùýžÁÈÏÉÌÍÒÓØŠÚÝŽ','acdeeinorstuuyzACDEEINORSTUYZ');
	return $text;
}

function GenerateLogin(&$zaznam)
{
	$login = $zaznam['jmeno']{0}.$zaznam['prijmeni']{0}.RegNumToStr($zaznam['reg']);
	$login = cp2ascii($login);
	return $login;
}
?>
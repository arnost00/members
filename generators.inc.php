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

// cz to ascii
function cp2ascii($text)
{
//	$text=strtr($text, 'áčďéěíňóřšťúůýžÁČĎÉĚÍŇÓŘŠŤÚÝŽ','acdeeinorstuuyzACDEEINORSTUYZ');

	// Slavic Latin 
	$table = array(
		'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
		'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
		'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
		'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
		'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
		'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
		'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
		'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r',
		// + czech 
		'ď'=>'d', 'Ď'=>'D', 'ě'=>'e', 'Ě'=>'E', 'ň'=>'n', 'Ň'=>'N', 'ř'=>'r', 'Ř'=>'R', 'ť'=>'t', 'Ť'=>'T',
		'ů'=>'u', 'Ů'=>'U',
	);

	return strtr($text, $table);
}

function GenerateLogin(&$zaznam)
{
//	$login = $zaznam['jmeno']{0}.$zaznam['prijmeni']{0}.RegNumToStr($zaznam['reg']);
	
	mb_internal_encoding("UTF-8");
	$login = mb_substr($zaznam['jmeno'],0,1);
	$login .= mb_substr($zaznam['prijmeni'],0,1);
	$login .= RegNumToStr($zaznam['reg']);
	$login = cp2ascii($login);
	return $login;
}
?>
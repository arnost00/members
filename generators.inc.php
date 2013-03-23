<?php if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>

<?

function __InitPassChars()
{
	$passchars = '';
	for ($i=ord('a');$i<=ord('z');$i++)
	{
		$passchars .= chr($i);
	}
	for ($i=ord('A');$i<=ord('Z');$i++)
	{
		$passchars .= chr($i);
	}
	for ($i=ord('0');$i<=ord('9');$i++)
	{
		$passchars .= chr($i);
	}
	return $passchars;
}

function GeneratePassword($p_len)
{
	$passchars = __InitPassChars();
	$passchars_cnt = 62; // add sizeof
	$newpass = '';
	for ($i=0;$i<$p_len;$i++)
	{
		$newpass .= $passchars{rand(0,$passchars_cnt-1)};
	}
	return $newpass;
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
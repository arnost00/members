<?php if (!defined('__HIDE_TEST__')) exit; /* zamezeni samostatneho vykonani */ ?>
<?
include('./race_kateg_list.inc.php');


$kk = $zaznam['kategorie'];
$kat_nf = '';
$kategorie=explode(';',$kk);

if(GetPhpVersion() < 40200)	// 4.2.0
	define('ARRAY_SEARCH_NOT_FOUND',NULL); // php < 4.2.0
else
	define('ARRAY_SEARCH_NOT_FOUND',FALSE);	// php >= 4.2.0

function GetKategorieCheckBox($type,$zeb,$kat)
{
	global $kategorie;

	$result ='<INPUT TYPE="checkbox" NAME="';
	$result .= $type.'['.$kat.']['.(($zeb == '') ? 'X' : $zeb).']" value="1"';

	$k= ($zeb == '') ? $type.$kat : $type.$kat.$zeb;

	if (($key = array_search($k, $kategorie)) !== ARRAY_SEARCH_NOT_FOUND)
	{
		$result .= ' CHECKED';
		unset($kategorie[$key]);
	}
	/*	old code
		if (in_array($k,$kategorie))
			$result .= ' CHECKED';
	*/
	$result .= '>';
	return $result;
}
?>

<table>
<TR>
<TD></TD>
<TD colspan="6" align="center">H</TD>
<TD></TD>
<TD colspan="6" align="center">D</TD>
</TR>
<TR>
<TD></TD>
<?
$str_zeb = '';
foreach ($zebricek_vypis as $zeb)
{
	$str_zeb .= '<TD align="center">'.$zeb.'</TD>';
}
$str_zeb .= '<TD align="center">&nbsp;</TD>';

echo($str_zeb);	// H
echo('<TD>|</TD>');
echo($str_zeb);	// D
?>
</TR>
<?

foreach ($kategorie_vypis as $kat)
{
	$tr_item = ($kat == 21) ? ' bgcolor="'.$g_colors['table_row_highlight'].'"' : '';
	echo ('<TR'.$tr_item.'><TD align="center">'.$kat.'</TD>');
	// H -->
	foreach ($zebricek_vypis as $zeb)
	{	// zeb E .. D
		echo('<TD align="center">');
		echo(GetKategorieCheckBox('H',$zeb,$kat));
		echo('</TD>');
	}
	echo('<TD align="center">');
	echo(GetKategorieCheckBox('H','',$kat));
	echo('</TD>');

	echo('<TD align="center">|</TD>');

	// D -->
	foreach ($zebricek_vypis as $zeb)
	{	// zeb E .. D
		echo('<TD align="center">');
		echo(GetKategorieCheckBox('D',$zeb,$kat));
		echo('</TD>');
	}
	echo('<TD align="center">');
	echo(GetKategorieCheckBox('D','',$kat));
	echo('</TD>');
?>
</TR>
<?
}

$kat_nf = implode(';',$kategorie);
?>

</table>

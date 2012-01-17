<?
$reg = (isset($reg)) ? $reg : '';
$year = (isset($year)) ? (int)$year : 0;
$sex = (isset($sex)) ? $sex : 'H';

?>
<form method=post action="find_reg.php">
Reg - <input type="text" name="reg"><br>
Year - <input type="text" name="year"><br>
Sex - <input type="radio" name="sex" value="D">D <input type="radio" name="sex" value="H">H<br>
<input type="submit">
</form>
<hr>
<hr>
<?
require ("./connect.inc.php");

if($reg != 0)
{
	echo('<u>Reg</u>');
	echo("<br>\n");
	echo('Reg = '.$reg);
	echo("<br>\n");
	db_Connect();
	@$vysledek=MySQL_Query("SELECT * FROM ".TBL_USER." WHERE reg = ".$reg." ORDER BY reg ASC")
		or die("Chyba pøi provádìní dotazu do databáze.");
	$i = 0;
	while ($zaznam=MySQL_Fetch_Array($vysledek))
	{
		echo($zaznam['prijmeni'].' '.$zaznam['jmeno'].' - '.$zaznam['reg']);
		echo("<br>\n");
		$i++;
	}
	if($i == 0)
		echo('Reg '.$reg.' not found.');
}
else if($year != 0)
{
	echo('<u>Year</u>');
	echo("<br>\n");
	echo('Year = '.$year);
	echo("<br>\n");
	echo('Sex = '.$sex);
	echo("<br>\n");
	echo('<hr>');
	db_Connect();
	if($year > 100)
		$year = $year % 100;
	$year *= 100;
	if($sex == 'D')
	{
		$y1 = $year + 50;
		$y2 = $year + 99;
	}
	else if($sex == 'H')
	{
		$y1 = $year;
		$y2 = $year + 49;
	}
	@$vysledek=MySQL_Query("SELECT * FROM ".TBL_USER." WHERE reg >= ".$y1." AND reg <= ".$y2." ORDER BY reg ASC")
		or die("Chyba pøi provádìní dotazu do databáze.");
	$i = 0;
	while ($zaznam=MySQL_Fetch_Array($vysledek))
	{
		echo($zaznam['prijmeni'].' '.$zaznam['jmeno'].' - '.$zaznam['reg']);
		echo("<br>\n");
		$i++;
	}
	if($i == 0)
		echo('In this year any member found.');
	else
	{
		echo('<hr>');
		echo('Found '.$i.' members.');
	}
}

?>
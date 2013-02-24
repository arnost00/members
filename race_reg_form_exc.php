<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require ('./connect.inc.php');
require ('./sess.inc.php');
require ('./common_user.inc.php');
require ('./common_race.inc.php');
require ('./common.inc.php');

if (!IsLoggedRegistrator())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

$id_zav = (IsSet($id_zav)&& is_numeric($id_zav)) ? (int)$id_zav : 0;
$termin = (IsSet($termin)&& is_numeric($termin)) ? (($termin >= 0 && $termin <= 5) ? (int)$termin : 0) : 0;
$ff = (IsSet($ff)&& is_numeric($ff)) ? (($ff >= 0 && $ff <= 1) ? (int)$ff : 0) : 0;	// output format
$creg = (IsSet($creg)&& is_numeric($creg)) ? (($creg >= 1) ? 1 : 0) : 0;	// output for central registration

if($ff == 1)
{
	HTML_Header('Pøihláška - Náhled');
?>
<pre>
<?
}
else
{
	TXT_Header();
}

define('SPACE_CHAR',' ');

define('KAT_LEN',10);
define('SI_LEN2005',10);
define('NAME_LEN',25);

db_Connect();

if($id_zav > 0)
{
	@$vysledek_z=MySQL_Query("SELECT typ FROM ".TBL_RACE." WHERE id=$id_zav");
	$zaznam_z = MySQL_Fetch_Array($vysledek_z);

	$sub_query = ($termin != 0) ? ' AND z.termin='.$termin : '';
		
	$query = 'SELECT u.jmeno, u.prijmeni, u.reg, u.si_chip, u.datum, u.lic, u.lic_mtbo, u.lic_lob, z.kat, z.pozn, z.termin, z.si_chip as t_si_chip FROM '.TBL_ZAVXUS.' as z, '.TBL_USER.' as u WHERE z.id_user = u.id AND z.id_zavod='.$id_zav.' AND u.hidden = 0'.$sub_query.' ORDER by reg, z.termin ASC, z.id ASC';

	$race_type = ($zaznam_z['typ']=='mtbo') ? 2 : (($zaznam_z['typ']=='lob')? 1 : 0);
}
else
{
	$query= 'SELECT prijmeni, jmeno, reg, si_chip, lic, lic_mtbo, lic_lob, datum FROM '.TBL_USER.' WHERE `hidden` = 0 ORDER by reg';
	$race_type = 0; // OB
	$creg = 1; // central reg.
}

@$vysledek=MySQL_Query($query);

if (mysql_num_rows($vysledek) == 0)
{
	echo "Nikdo není pøihlášen.";
}
else
{
	while ($zaznam=MySQL_Fetch_Array($vysledek))
	{
		$lic = GetLicence($zaznam['lic'],$zaznam['lic_mtbo'],$zaznam['lic_lob'],$race_type);
		$str = RegNumToStr($zaznam['reg']).SPACE_CHAR;
		$str .= str_pad(($id_zav > 0) ? $zaznam['kat'] : '',KAT_LEN,SPACE_CHAR).SPACE_CHAR;
		$si_chip = (int)$zaznam['si_chip'];
		if($id_zav > 0)
		{
			if ($si_chip == 0 || $zaznam['t_si_chip'] != 0)
				$si_chip = $zaznam['t_si_chip'];
		}
		$str .= str_pad($si_chip,SI_LEN2005,'0',STR_PAD_LEFT).SPACE_CHAR;

		$str .= str_pad($zaznam['prijmeni'].' '.$zaznam['jmeno'],NAME_LEN,SPACE_CHAR).SPACE_CHAR;
		$str .= $lic;
		if($creg == 1)
		{	// datum narození (59-64) ve tvaru rrmmdd
			
			$str .= SPACE_CHAR.SQLDate2StringReg($zaznam['datum']);
		}
		$str .= SPACE_CHAR;
		if($id_zav > 0)
			$str .= $zaznam['pozn'];
		echo $g_shortcut.$str."\n";//<BR>";
	}
}

if($ff == 1)
{
?>
</pre>
<?
	HTML_Footer();
}
?>
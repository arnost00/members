<?
// Date in the past 
header("Expires: " . gmdate("D, d M Y H:i:s") . " GMT"); 

// always modified 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 

// HTTP/1.1 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false); 

// HTTP/1.0 
header("Pragma: no-cache"); 

// CORS
header("Access-Control-Allow-Origin: *");
?>
<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?

require_once ('timestamp.inc.php');
require_once('cfg/_globals.php');
require_once ('connect.inc.php');
require_once ('./version.inc.php');
require_once ('common.inc.php');
require_once ('common_rg_race.inc.php');

db_Connect();

require_once ('common_race.inc.php');
require_once ('url.inc.php');

$curr_date = GetCurrentDate();
//$curr_date = mktime (0,0,0,6,1,2010);

$d1 = $curr_date;

$query="SELECT id,datum,typ,datum2,prihlasky,prihlasky1,prihlasky2,prihlasky3,prihlasky4,prihlasky5,nazev,vicedenni,odkaz,vedouci, oddil,send,misto,cancelled,typ0, ubytovani, transport, zebricek, ranking, etap, poznamka FROM ".TBL_RACE.' WHERE datum >= '.$d1.' || datum2 >= '.$d1.' ORDER BY datum, datum2, id';
@$vysledek=$db_conn->query($query);

$data = array();
$data['Format'] = 'json';
$data['Source'] = 'members';
$data['Data'] = [];

if (mysqli_num_rows($vysledek) > 0)
{
	while ($zaznam=mysqli_fetch_array($vysledek))
	{
		$race_key = 'Race_'.$zaznam['id'];
		
		if($zaznam['vicedenni'])
		{
			$data['Data'][$race_key]['Date1']=Date2ISO($zaznam['datum']);
			$data['Data'][$race_key]['Date2']=Date2ISO($zaznam['datum2']);
		}
		else
		{
			$data['Data'][$race_key]['Date1']=Date2ISO($zaznam['datum']);
		}
		
		if ($zaznam['prihlasky1'] != 0 && $zaznam['prihlasky'] > 0 )
			$data['Data'][$race_key]['Entry1']=Date2ISO($zaznam['prihlasky1']);
		if ($zaznam['prihlasky2'] != 0 && $zaznam['prihlasky'] > 1 )
			$data['Data'][$race_key]['Entry2']=Date2ISO($zaznam['prihlasky2']);
		if ($zaznam['prihlasky3'] != 0 && $zaznam['prihlasky'] > 2 )
			$data['Data'][$race_key]['Entry3']=Date2ISO($zaznam['prihlasky3']);
		if ($zaznam['prihlasky4'] != 0 && $zaznam['prihlasky'] > 3 )
			$data['Data'][$race_key]['Entry4']=Date2ISO($zaznam['prihlasky4']);
		if ($zaznam['prihlasky5'] != 0 && $zaznam['prihlasky'] > 4 )
			$data['Data'][$race_key]['Entry5']=Date2ISO($zaznam['prihlasky5']);

		$data['Data'][$race_key]['Name'] = $zaznam['nazev'];
		$data['Data'][$race_key]['Cancelled'] = $zaznam['cancelled'];
		$data['Data'][$race_key]['Club'] = $zaznam['oddil'];
		$data['Data'][$race_key]['Link'] = $zaznam['odkaz'];
		$data['Data'][$race_key]['Place'] = $zaznam['misto'];
		$data['Data'][$race_key]['Type'] = $zaznam['typ0'];
		$data['Data'][$race_key]['Sport'] = $zaznam['typ'];
		$data['Data'][$race_key]['Rankings'] = $zaznam['zebricek'];
		$data['Data'][$race_key]['Rank21'] = $zaznam['ranking'];
		$data['Data'][$race_key]['Note'] = $zaznam['poznamka'];
		
		
		if($zaznam['vicedenni'])
			$data['Data'][$race_key]['NumberOfRaces'] = $zaznam['etap'];
		if ($g_enable_race_transport)
		{
			switch($zaznam['transport'])
			{
			case 0: 
				$transport = 'No';
				break;
			case 1: 
				$transport = 'Yes';
				break;
			case 2: 
				$transport = 'Auto Yes';
				break;
			}
			$data['Data'][$race_key]['Transport'] = $transport;
		}
		if ($g_enable_race_accommodation)
		{
			switch($zaznam['ubytovani'])
			{
			case 0: 
				$ubytovani = 'No';
				break;
			case 1: 
				$ubytovani = 'Yes';
				break;
			case 2: 
				$ubytovani = 'Auto Yes';
				break;
			}
			$data['Data'][$race_key]['Accomodation'] = $ubytovani;
		}
	}
	
	$data['Status'] = 'OK';
}
else
{
	$data['Status'] = 'empty';
	// empty data request
}

// enums

foreach ( $g_racetype0 as $key => &$value )
{
	$data['Enums']['Type'][$key] = $value;
}

for($ii=0; $ii<$g_zebricek_cnt; $ii++)
{
	$data['Enums']['Rankings'][$g_zebricek [$ii]['id']] = $g_zebricek [$ii]['nm'];
}

for($ii=0; $ii<$g_racetype_cnt; $ii++)
{
	$data['Enums']['Sports'][$g_racetype[$ii]['enum']] = $g_racetype [$ii]['nm'];
}

echo (json_encode($data));
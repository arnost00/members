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

require_once ('functions.php');

$curr_date = GetCurrentDate();

$race_id = $_GET['id_race'];

$data = array(); //variable for return in json

$userSelected = (isset($_GET['id_user']));

if ($userSelected)
{
	// user selected
	$id_user = $_GET['id_user'];
	// now select what to do with user
	$action = (isset($_GET['action'])) ? $_GET['action'] : 'detail';
	switch ($action) {
		case 'participate':
			//id_race=X&id_user=X&action=participate
			$query="UPDATE ".TBL_ZAVXUS." zu SET participated = not(if(zu.participated is null or zu.participated = 0, 0, 1)) where id_zavod = $race_id and id_user = (select id from ".TBL_USER." where id = '$id_user')";
			@$result=$db_conn->query($query);
			$data = $db_conn->affected_rows;
			break;
		case 'entryByFin':
			//id_race=X&id_user=X&action=entryByFin
			$query="SELECT id FROM ".TBL_ZAVXUS." WHERE id_user = (select id from ".TBL_USER." where id = '$id_user') and id_zavod = '$race_id' and add_by_fin = 1;";
			if (mysqli_num_rows($db_conn->query($query)) > 0) {
				//zaznam v db existuje, pozadavek na smazani
				$query="DELETE FROM ".TBL_ZAVXUS." WHERE id_user = (select id from ".TBL_USER." where id = '$id_user') and id_zavod = '$race_id' and add_by_fin = 1;";
				@$result=$db_conn->query($query);
				$data = 'deleted:'.$db_conn->affected_rows;
			} else {
				$kat = isset($_GET['kat']) ? $_GET['kat'] : '';
				$pozn = '';
				$pozn2 = 'vlozeno financnikem na miste';
				$termin = 0;
				$transport = 0;
				$ubytovani = 0;
				$participated = 1;
				$addByFin = 1;
				$query="INSERT INTO ".TBL_ZAVXUS." (id_user, id_zavod, kat, pozn, pozn_in, termin, transport, ubytovani, participated, add_by_fin) VALUES ((select id from ".TBL_USER." where id = '$id_user'), '$race_id', '$kat', '$pozn', '$pozn2', '$termin', '$transport', '$ubytovani', '$participated', '$addByFin');";
				@$result=$db_conn->query($query);
				$data = 'inserted:'.$db_conn->affected_rows;
			}
			break;
		case 'detail':
		default:
			// return entry detail about user in race
			$query="select * from ".TBL_ZAVXUS." z where id_zavod = $race_id and id_user = (select id from ".TBL_USER." where id = '$id_user')";
			@$result=$db_conn->query($query);
			$data = mysqli_fetch_array($result);
			break;
	}
} else {
	$query="SELECT sort_name as `name`, reg, u.id as id_user, zu.id as id, kat, if(zu.participated is null or zu.participated = 1, 1, 0) as participated, if(zu.add_by_fin is null or zu.add_by_fin = 0, 0, 1) as add_by_fin  FROM ".TBL_USER." u left join ".TBL_ZAVXUS." zu on u.id=zu.id_user and zu.id_zavod = $race_id where u.hidden = 0 order by zu.kat desc, u.sort_name asc";
	@$result=$db_conn->query($query);
	if (mysqli_num_rows($result) > 0)
	{
		while ($record=mysqli_fetch_array($result))
		{
			$data[] = $record;
		}
	}
	else
	{
		$data = 'empty';
		// empty request
	}
}

echo (json_encode($data));
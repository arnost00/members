<?
 
/*
/* ---- konfigurace ----
*/

require ('./cfg/_cfg.php');

//nastavit vsem heslo na '12345' - 1 = True, 0 = False
$set_default_password=1;
$default_password=md5('12345');
$default_password_except = "'admin'";
//nastavit vsem login stejny jako registracka
//zatim nefunkcni, neni zadna rozumna logika, co tam dat
$set_default_login=0;
$default_login_except = "'admin', 'kenia', 'arnost'";


//odkud se bude kopirovat
$production_schema = 'zbm';
//kam se bude kopirovat
$copy_schema = 'tst2';
//jake tabulky se budou kopirovat
$tables_to_copy = ['zavod', 'news', 'users', 'accounts', 'zavxus', 'modify_log', 'mailinfo', 'finance', 'claim', 'finance_types'];


/*
/* ---- vykonny kod ----
*/

$mysqli = new mysqli($g_dbserver, $g_dbuser, $g_dbpass, $g_dbname);

/* check connection */
if ($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
} 
 

foreach ($tables_to_copy as $table_name)
{
	$source = $production_schema."_".$table_name;
	$dest = $copy_schema."_".$table_name;
	
	//promazani tabulek
	//dokud nebude pripraveno automaticke zakladani tabulek ze skriptu kvuli indexum apod., tak pouze promazavat
	$query = "delete from $dest";
	if ($mysqli->query($query) === TRUE) {
		printf("Table $dest successfully deleted.<br/>");
	} else 
	echo "<b>$mysqli->error</b><br/>";
	
	//skopirovani dat z tabulek
	$query = "INSERT INTO $dest SELECT * FROM $source";
	if ($mysqli->query($query) === TRUE) {	
		//nastavit vsem stejne defaultni heslo?
		$password_updated = "";
		$login_updates = "";
		if ($table_name == 'accounts' and $set_default_password) {
			$query = "update $dest set heslo = '".$default_password."' where $dest.login NOT IN (".$default_password_except.")";
			($mysqli->query($query) === TRUE)?$password_updated="and password was updated ":"";
/*
			//zatim nedoreseno, co tam dat jako defaultni login
			if ($set_default_login)
			{
				$query = "UPDATE $dest a, `".$production_schema."_users` u SET a.login = u.reg WHERE a.id_users = u.id AND u.hidden = 0 AND a.login NOT IN (".$default_login_except.")";
				echo $query;
			}
*/
		}
		printf("Table $source successfully copied ".$password_updated."to $dest table.<br/><br/>");
	} else echo "<b>$mysqli->error</b><br/><br/>";
}

$mysqli->close();

?>
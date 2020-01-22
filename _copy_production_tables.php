<?
 
/*
/* ---- konfigurace ----
*/

$g_dbserver='localhost';
$g_dbuser='root';
$g_dbpass='root';
$g_dbname='members_rls';

//nastavit vsem heslo na '12345' - 1 = True, 0 = False
$set_default_password=1;
$default_password=md5('12345');

//odkud se bude kopirovat
$production_schema = 'zbm';
//kam se bude kopirovat
$copy_schema = 'copy_'.$production_schema;
//jake tabulky se budou kopirovat
$tables_to_copy = ['zavod', 'news', 'users', 'accounts', 'usxus', 'zavxus', 'modify_log', 'mailinfo', 'finance', 'claim', 'finance_types'];


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
	
	//smazani tabulek
	$query = "drop table if exists $dest";
	if ($mysqli->query($query) === TRUE) {
		printf("Table $dest successfully deleted.<br/>");
	} else 
	echo "<b>$mysqli->error</b><br/>";
	
	//skpirovani tabulek
	$query = "create table $dest as select * from $source";
	if ($mysqli->query($query) === TRUE) {	
		//nastavit vsem stejne defaultni heslo?
		$password_updated = "";
		if ($table_name == 'accounts' and $set_default_password) {
			$query = "update $dest set heslo = '".$default_password."'";
			($mysqli->query($query) === TRUE)?$password_updated="and password was updated ":"";
		}
		printf("Table $source successfully copied ".$password_updated."to $dest table.<br/><br/>");
	} else echo "<b>$mysqli->error</b><br/><br/>";
}

$mysqli->close();

?>
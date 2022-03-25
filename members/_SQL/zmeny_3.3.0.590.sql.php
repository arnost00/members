<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '3.3.0.590';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** rozsireni pole login a heslo
$sql[1] = 'ALTER TABLE `'.TBL_ACCOUNT."` CHANGE `login` `login` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL DEFAULT '', CHANGE `heslo` `heslo` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL DEFAULT ''";

//#############################################################################
// Specialni funkce 
//#############################################################################

function post_sql_function()
{	//recode_passwords
	global $db_conn;
	echo("Aktualizace db - spustit pouze 1x !!<br>");
	
	$result=mysqli_query($db_conn, "SELECT `id`, `heslo` FROM `".TBL_ACCOUNT."`");
	while ($zaznam = mysqli_fetch_assoc($result))
	{
		$nh = password_hash($zaznam['heslo'], PASSWORD_DEFAULT);
		$query = 'UPDATE '.TBL_ACCOUNT." SET `heslo`='".$nh."' WHERE `id` = '".$zaznam['id']."'";
		$result2=mysqli_query($db_conn, $query);
		if ($result2 == FALSE)
			echo ('Selhala aktualizace id - '.$zaznam['id'].'<br>');
		else
			echo ('Aktualizování id - '.$zaznam['id'].'<br>');
	}
}

require_once ('action.inc.php');
?>

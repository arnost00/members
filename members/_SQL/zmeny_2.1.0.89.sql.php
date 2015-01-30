<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '2.1.0.89';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** zmena odkazu z accounts na users u tabulky finance
$sql[1] = 'ALTER TABLE `'.TBL_FINANCE."` CHANGE `id_accounts_editor` `id_users_editor` SMALLINT(5);";
$sql[2] = 'ALTER TABLE `'.TBL_FINANCE."` CHANGE `id_accounts_user` `id_users_user` SMALLINT(5);";

//#############################################################################

require_once ('action.inc.php');
?>
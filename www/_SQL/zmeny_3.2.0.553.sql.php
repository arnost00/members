<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '3.2.0.553';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** do tabulky accounts pridej cizi klic do tabulky users
$sql[1] = 'ALTER TABLE `'.TBL_ACCOUNT.'` ADD `id_users` smallint(5) NULL default NULL AFTER `id`, ADD INDEX (`id_users`)';
# *** nakopiruj odkaz do tabulky accounts z usxus tabulky do tabulky users
$sql[2] = 'UPDATE `'.TBL_ACCOUNT.'` a , `'.TBL_USXUS.'` x SET a.id_users = x.id_users WHERE a.id = x.id_accounts';
# *** prejmenovani tabulky usxus
$sql[3] = 'RENAME TABLE `'.TBL_USXUS.'` TO `'.TBL_USXUS.'_OLD`';

//#############################################################################

require_once ('action.inc.php');
?>

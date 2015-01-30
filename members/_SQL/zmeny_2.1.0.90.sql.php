<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '2.1.0.90';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** zmena odkazu z accounts na users u tabulky finance
$sql[1] = 'ALTER TABLE `'.TBL_MAILINFO.'` ADD `active_fin` TINYINT( 1 ) UNSIGNED NOT NULL , ADD `active_finf` TINYINT( 1 ) UNSIGNED NOT NULL , ADD `fin_type` INT( 11 ) UNSIGNED NOT NULL , ADD `fin_limit` SMALLINT( 5 ) NOT NULL ';
//$sql[2] = '';

//#############################################################################

require_once ('action.inc.php');
?>
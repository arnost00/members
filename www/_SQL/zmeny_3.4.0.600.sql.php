<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '3.4.0.600';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** do tabulky zavxus pridej nove sloupce pro funkcionalitu Ucast
$sql[1] = 'ALTER TABLE `'.TBL_ZAVXUS.'` ADD `participated` tinyint(1) NULL default NULL AFTER `ubytovani`';
$sql[2] = 'ALTER TABLE `'.TBL_ZAVXUS.'` ADD `add_by_fin` tinyint(1) NULL default NULL AFTER `participated`';

//#############################################################################

require_once ('action.inc.php');
?>

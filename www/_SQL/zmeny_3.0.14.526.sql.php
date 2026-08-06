<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '3.0.14.526';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** pridani sloupcu pro interni novinku a info o editaci
$sql[1] = 'ALTER TABLE `'.TBL_NEWS.'` ADD `internal` TINYINT( 1 ) NOT NULL DEFAULT \'0\'';
$sql[2] = 'ALTER TABLE `'.TBL_NEWS.'` ADD `modify_flag` TINYINT( 1 ) NOT NULL DEFAULT \'0\'';

# *** pridani sloupcu pro informovani o novinkach
$sql[3] = 'ALTER TABLE `'.TBL_MAILINFO.'` ADD `active_news` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT \'0\'';

//#############################################################################

require_once ('action.inc.php');
?>

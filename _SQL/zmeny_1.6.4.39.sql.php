<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '1.6.4.39';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** zmena sloupce odkaz v tabulce zavod

$sql[1] = 'ALTER TABLE `'.TBL_RACE.'` CHANGE `odkaz` `odkaz` VARCHAR( 100 ) DEFAULT NULL';

# *** pridani sloupce chief_id do tabulky users

$sql[2] = 'ALTER TABLE `'.TBL_USER.'` ADD `chief_id` SMALLINT( 5 ) UNSIGNED NOT NULL';


//#############################################################################

require_once ('action.inc.php');
?>
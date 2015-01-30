<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '2.9.9.455';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** prevod db do UTF-8
$sql[1] = 'ALTER DATABASE `'.$g_dbname.'` CHARACTER SET utf8 COLLATE utf8_czech_ci';

#  *** prevod tabulek do UTF-8
$sql[2] = 'ALTER TABLE `'.TBL_RACE.'` CONVERT TO CHARACTER SET utf8 COLLATE utf8_czech_ci';
$sql[3] = 'ALTER TABLE `'.TBL_NEWS.'` CONVERT TO CHARACTER SET utf8 COLLATE utf8_czech_ci';
$sql[4] = 'ALTER TABLE `'.TBL_USER.'` CONVERT TO CHARACTER SET utf8 COLLATE utf8_czech_ci';
$sql[5] = 'ALTER TABLE `'.TBL_ACCOUNT.'` CONVERT TO CHARACTER SET utf8 COLLATE utf8_czech_ci';
$sql[6] = 'ALTER TABLE `'.TBL_USXUS.'` CHARACTER SET utf8 COLLATE utf8_czech_ci';	// don't need to convert
$sql[7] = 'ALTER TABLE `'.TBL_ZAVXUS.'` CONVERT TO CHARACTER SET utf8 COLLATE utf8_czech_ci';
$sql[8] = 'ALTER TABLE `'.TBL_MODLOG.'` CONVERT TO CHARACTER SET utf8 COLLATE utf8_czech_ci';
$sql[9] = 'ALTER TABLE `'.TBL_MAILINFO.'` CONVERT TO CHARACTER SET utf8 COLLATE utf8_czech_ci';
$sql[10] = 'ALTER TABLE `'.TBL_FINANCE.'` CONVERT TO CHARACTER SET utf8 COLLATE utf8_czech_ci';
$sql[11] = 'ALTER TABLE `'.TBL_CLAIM.'` CONVERT TO CHARACTER SET utf8 COLLATE utf8_czech_ci';
$sql[12] = 'ALTER TABLE `'.TBL_FINANCE_TYPES.'` CONVERT TO CHARACTER SET utf8 COLLATE utf8_czech_ci';

//#############################################################################

require_once ('action.inc.php');
?>

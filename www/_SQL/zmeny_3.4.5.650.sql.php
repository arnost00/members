<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '3.4.5.650';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################


# *** pridani sloupcu pro externi referenci
$sql[1] = 'ALTER TABLE `'.TBL_RACE.'` ADD `ext_id` VARCHAR( 8 ) NULL DEFAULT NULL AFTER `id`';

# *** rozsireni sloupce oddily, kvuli tomu ze CSOS ma 4 pismena
$sql[2] = 'ALTER TABLE `'.TBL_RACE.'` CHANGE `oddil` `oddil` VARCHAR( 8 )';

# *** rozsireni sloupce nazev, v ORIS byvaji delsi nazvy
$sql[3] = 'ALTER TABLE `'.TBL_RACE.'` CHANGE `nazev` `nazev` VARCHAR( 70 )';

//#############################################################################

require_once ('action.inc.php');
?>

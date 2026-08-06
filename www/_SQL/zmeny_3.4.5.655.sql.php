<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '3.4.5.655';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** pridani sloupcu pro dopravu
$sql[1] = 'ALTER TABLE `'.TBL_RACE.'` ADD `kapacita` SMALLINT NULL DEFAULT NULL AFTER `ubytovani`';
$sql[2] = 'ALTER TABLE `'.TBL_RACE.'` ADD `prihlasenych` SMALLINT NOT NULL DEFAULT 0 AFTER `kapacita`';
$sql[3] = 'ALTER TABLE `'.TBL_RACE.'` ENGINE=InnoDB;';

$sql[4] = 'UPDATE `'.TBL_RACE.'` r LEFT JOIN ( SELECT id_zavod, COUNT(*) AS cnt FROM `'.TBL_ZAVXUS.
'` GROUP BY id_zavod ) z ON z.id_zavod = r.id SET r.prihlasenych = COALESCE(z.cnt, 0);';

//#############################################################################

require_once ('action.inc.php');
?>

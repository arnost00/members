<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '1.7.7.57';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** uprava nazvu sloupce

$sql[1] = 'ALTER TABLE `'.TBL_RACE."` CHANGE `prihlasky` `prihlasky1` INT( 11 ) DEFAULT '0'";

# *** pridani sloupce datum2 do tabulky

$sql[2] = 'ALTER TABLE `'.TBL_RACE."` ADD `datum2` INT( 11 ) NOT NULL default '0' AFTER `datum`";

# *** pridani sloupce prihlasky do tabulky

$sql[3] = 'ALTER TABLE `'.TBL_RACE."` ADD `prihlasky` TINYINT( 4 ) UNSIGNED DEFAULT '0' NOT NULL AFTER `odkaz`";

# *** aktualizace hodnoty sloupce

$sql[4] = 'UPDATE `'.TBL_RACE.'` SET `prihlasky` = 1 WHERE `prihlasky1` != 0';

# *** pridani sloupcu prihlasky2 .. prihlasky5 do tabulky

$sql[5] = 'ALTER TABLE `'.TBL_RACE."` ADD `prihlasky2` INT( 11 ) DEFAULT '0' NOT NULL AFTER `prihlasky1` , ADD `prihlasky3` INT( 11 ) DEFAULT '0' NOT NULL AFTER `prihlasky2` , ADD `prihlasky4` INT( 11 ) DEFAULT '0' NOT NULL AFTER `prihlasky3` , ADD `prihlasky5` INT( 11 ) DEFAULT '0' NOT NULL AFTER `prihlasky4`";

# *** pridani sloupce etap do tabulky

$sql[6] = 'ALTER TABLE `'.TBL_RACE."` ADD `etap` TINYINT( 4 ) UNSIGNED DEFAULT '0' NOT NULL AFTER `prihlasky5`";

# *** pridani sloupce poznamka do tabulky

$sql[7] = 'ALTER TABLE `'.TBL_RACE."` ADD `poznamka` VARCHAR( 255 )";

# *** pridani sloupce termin do tabulky

$sql[8] = 'ALTER TABLE `'.TBL_ZAVXUS."` ADD `termin` TINYINT( 4 ) UNSIGNED DEFAULT '1' NOT NULL";

# *** pridani sloupce typ do tabulky

$sql[9] = 'ALTER TABLE `'.TBL_RACE."` ADD `typ` ENUM( 'ob', 'mtbo', 'lob', 'jine' ) NOT NULL AFTER `misto`";

# *** uprava sloupce zebricek v tabulce

$sql[10] = 'ALTER TABLE `'.TBL_RACE."` CHANGE `zebricek` `zebricek` INT UNSIGNED DEFAULT '0' NOT NULL";

# *** pridani sloupce vicedenni do tabulky

$sql[11] = 'ALTER TABLE `'.TBL_RACE."` ADD `vicedenni` TINYINT( 1 ) UNSIGNED DEFAULT '0' NOT NULL AFTER `typ`";

# *** pridani indexu do tabulky

$sql[12] = 'ALTER TABLE `'.TBL_ZAVXUS."` ADD INDEX `id_termin` ( `termin` , `id` )";

# *** pridani sloupce vedouci do tabulky

$sql[13] = 'ALTER TABLE `'.TBL_RACE."` ADD `vedouci` INT( 10 ) UNSIGNED NOT NULL";

//#############################################################################

require_once ('action.inc.php');
?>
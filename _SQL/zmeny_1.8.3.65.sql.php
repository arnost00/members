<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '1.8.3.65';

//#############################################################################

require ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** aktualizace hodnoty sloupce
$sql[1] = 'CREATE TABLE `'.TBL_MODLOG."` (`id` int(10) unsigned NOT NULL auto_increment, `timestamp` int(10) unsigned NOT NULL default '0', `action` enum('unknown','add','edit','delete') NOT NULL default 'unknown', `table` varchar(20) NOT NULL default '', `description` varchar(255) NOT NULL default '', `author` int(10) unsigned NOT NULL default '0',  PRIMARY KEY  (`id`)) TYPE=MyISAM";

# *** pridani sloupce oddil do tabulky

$sql[2] = 'ALTER TABLE `'.TBL_RACE."` ADD `oddil` varchar(7) NOT NULL default ''";

# *** pridani sloupce kategorie do tabulky

$sql[3] = 'ALTER TABLE `'.TBL_RACE."` ADD `kategorie` TEXT NOT NULL default '' AFTER `kat_n`";

# *** vytvoreni hodnoty ve sloupci `kategorie`

$sql[4] = 'UPDATE `'.TBL_RACE."` SET `kat` = '' WHERE `kat` IS NULL";

$sql[5] = 'UPDATE `'.TBL_RACE."` SET `kat_n` = '' WHERE `kat_n` IS NULL";

$sql[6] = 'UPDATE `'.TBL_RACE."` SET `kategorie` = CONCAT(`kat`,`kat_n`)";

# *** vytvoreni pomocnych klicu

$sql[7] = 'ALTER TABLE `'.TBL_USXUS.'` ADD INDEX ( `id_users` )';

$sql[8] = 'ALTER TABLE `'.TBL_USXUS.'` ADD INDEX ( `id_accounts` )';

$sql[9] = 'ALTER TABLE `'.TBL_ACCOUNT.'` ADD INDEX ( `policy_mng` )';

$sql[10] = 'ALTER TABLE `'.TBL_USER.'` ADD INDEX ( `chief_id` )';

//$sql[11] = 'ALTER TABLE `'.TBL_RACE.'` ADD `send` TINYINT( 1 ) UNSIGNED DEFAULT \'0\' NOT NULL';

//#############################################################################

require ('action.inc.php');
?>
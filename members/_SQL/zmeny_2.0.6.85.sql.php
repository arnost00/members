<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '2.0.6.85';

//#############################################################################

require ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** pridani tabulky mailinfo
$sql[1] = 'CREATE TABLE `'.TBL_MAILINFO.'` ( 
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT, 
  `id_user` int(11) unsigned NOT NULL, 
  `email` varchar(50) COLLATE cp1250_czech_cs NOT NULL, 
  `active_tf` tinyint(1) unsigned NOT NULL, 
  `active_ch` tinyint(1) unsigned NOT NULL, 
  `active_rg` tinyint(1) unsigned NOT NULL, 
  `daysbefore` int(2) NOT NULL,
  `type` int(11) NOT NULL,
  `sub_type` int(11) unsigned NOT NULL,
  `ch_data` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1250 COLLATE=cp1250_czech_cs';

# *** pridani sloupce modify_flag v tabulce race
$sql[2] = 'ALTER TABLE `'.TBL_RACE.'` ADD `modify_flag` INT UNSIGNED NOT NULL';

# *** uprava sloupce typ v tabulce race
$sql[3] = 'ALTER TABLE `'.TBL_RACE."` CHANGE `typ` `typ` ENUM( 'ob', 'mtbo', 'lob', 'jine', 'trail' ) CHARACTER SET cp1250 COLLATE cp1250_czech_cs NOT NULL";

//#############################################################################

require ('action.inc.php');
?>
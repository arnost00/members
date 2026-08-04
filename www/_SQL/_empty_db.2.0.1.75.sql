-- phpMyAdmin SQL Dump
-- version 2.9.1.1
-- http://www.phpmyadmin.net
-- 
-- Počítač: localhost
-- Vygenerováno: Pondělí 01. února 2010, 19:28
-- Verze MySQL: 5.0.17
-- Verze PHP: 5.1.1
-- 

-- --------------------------------------------------------

-- 
-- Struktura tabulky `accounts`
-- 

DROP TABLE IF EXISTS `accounts`;
CREATE TABLE IF NOT EXISTS `accounts` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `login` varchar(10) collate cp1250_czech_cs NOT NULL default '',
  `heslo` varchar(33) collate cp1250_czech_cs NOT NULL default '',
  `policy_news` tinyint(1) unsigned NOT NULL default '0',
  `policy_regs` tinyint(1) unsigned NOT NULL default '0',
  `policy_mng` tinyint(1) unsigned NOT NULL default '0',
  `policy_adm` tinyint(1) unsigned NOT NULL default '0',
  `podpis` varchar(15) collate cp1250_czech_cs NOT NULL default '',
  `locked` tinyint(1) unsigned NOT NULL default '0',
  `last_visit` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `login` (`login`),
  KEY `policy_mng` (`policy_mng`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1250 COLLATE=cp1250_czech_cs COMMENT='loginy a hesla uzivatelu' AUTO_INCREMENT=2 ;

-- 
-- Vypisuji data pro tabulku `accounts`
-- 

INSERT INTO `accounts` (`id`, `login`, `heslo`, `policy_news`, `policy_regs`, `policy_mng`, `policy_adm`, `podpis`, `locked`, `last_visit`) VALUES 
(1, 'admin', '827ccb0eea8a706c4c34a16891f84e7b', 1, 0, 0, 0, '', 0, 0);

-- admin heslo = 12345

-- --------------------------------------------------------

-- 
-- Struktura tabulky `modify_log`
-- 

DROP TABLE IF EXISTS `modify_log`;
CREATE TABLE IF NOT EXISTS `modify_log` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `timestamp` int(10) unsigned NOT NULL default '0',
  `action` enum('unknown','add','edit','delete') collate cp1250_czech_cs NOT NULL default 'unknown',
  `table` varchar(20) collate cp1250_czech_cs NOT NULL default '',
  `description` varchar(255) collate cp1250_czech_cs NOT NULL default '',
  `author` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1250 COLLATE=cp1250_czech_cs AUTO_INCREMENT=1 ;

-- 
-- Vypisuji data pro tabulku `modify_log`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabulky `news`
-- 

DROP TABLE IF EXISTS `news`;
CREATE TABLE IF NOT EXISTS `news` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_user` smallint(5) unsigned default NULL,
  `datum` int(11) NOT NULL default '0',
  `nadpis` varchar(50) collate cp1250_czech_cs NOT NULL default '',
  `text` longtext collate cp1250_czech_cs NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `sort_datum` (`datum`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1250 COLLATE=cp1250_czech_cs COMMENT='novinky' AUTO_INCREMENT=1 ;

-- 
-- Vypisuji data pro tabulku `news`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabulky `users`
-- 

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `prijmeni` varchar(30) collate cp1250_czech_cs NOT NULL default '',
  `jmeno` varchar(20) collate cp1250_czech_cs NOT NULL default '',
  `datum` date default NULL,
  `adresa` varchar(50) collate cp1250_czech_cs default NULL,
  `mesto` varchar(25) collate cp1250_czech_cs NOT NULL,
  `psc` varchar(6) collate cp1250_czech_cs NOT NULL,
  `tel_domu` varchar(25) collate cp1250_czech_cs default NULL,
  `tel_zam` varchar(25) collate cp1250_czech_cs default NULL,
  `tel_mobil` varchar(25) collate cp1250_czech_cs default NULL,
  `email` varchar(50) collate cp1250_czech_cs default NULL,
  `reg` int(4) unsigned zerofill NOT NULL default '0000',
  `si_chip` int(9) unsigned NOT NULL default '0',
  `hidden` tinyint(1) unsigned NOT NULL default '0',
  `sort_name` varchar(50) collate cp1250_czech_cs NOT NULL default '',
  `poh` enum('H','D') collate cp1250_czech_cs NOT NULL default 'H',
  `lic` enum('E','A','B','C','D','R','-') collate cp1250_czech_cs default '-',
  `lic_mtbo` enum('E','A','B','C','D','R','-') collate cp1250_czech_cs default '-',
  `lic_lob` enum('E','A','B','C','D','R','-') collate cp1250_czech_cs default '-',
  `fin` int(11) NOT NULL default '0',
  `chief_id` smallint(5) unsigned NOT NULL default '0',
  `rc` varchar(10) collate cp1250_czech_cs NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `name2` (`sort_name`),
  KEY `chief_id` (`chief_id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1250 COLLATE=cp1250_czech_cs COMMENT='vsechny informace o uzivatelich' AUTO_INCREMENT=1 ;

-- 
-- Vypisuji data pro tabulku `users`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabulky `usxus`
-- 

DROP TABLE IF EXISTS `usxus`;
CREATE TABLE IF NOT EXISTS `usxus` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `id_accounts` smallint(5) unsigned NOT NULL default '0',
  `id_users` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_users` (`id_users`),
  KEY `id_accounts` (`id_accounts`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1250 COLLATE=cp1250_czech_cs COMMENT='mezitabulka mezi ID user a ID users' AUTO_INCREMENT=2 ;

-- 
-- Vypisuji data pro tabulku `usxus`
-- 

INSERT INTO `usxus` (`id`, `id_accounts`, `id_users`) VALUES 
(1, 1, 0);

-- --------------------------------------------------------

-- 
-- Struktura tabulky `zavod`
-- 

DROP TABLE IF EXISTS `zavod`;
CREATE TABLE IF NOT EXISTS `zavod` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `datum` int(11) NOT NULL default '0',
  `datum2` int(11) NOT NULL default '0',
  `nazev` varchar(50) collate cp1250_czech_cs default NULL,
  `misto` varchar(50) collate cp1250_czech_cs default NULL,
  `typ` enum('ob','mtbo','lob','jine') collate cp1250_czech_cs NOT NULL,
  `vicedenni` tinyint(1) unsigned NOT NULL default '0',
  `zebricek` int(10) unsigned NOT NULL default '0',
  `ranking` enum('0','1') collate cp1250_czech_cs NOT NULL default '0',
  `odkaz` varchar(100) collate cp1250_czech_cs default NULL,
  `prihlasky` tinyint(4) unsigned NOT NULL default '0',
  `prihlasky1` int(11) default '0',
  `prihlasky2` int(11) NOT NULL default '0',
  `prihlasky3` int(11) NOT NULL default '0',
  `prihlasky4` int(11) NOT NULL default '0',
  `prihlasky5` int(11) NOT NULL default '0',
  `etap` tinyint(4) unsigned NOT NULL default '0',
  `kategorie` text collate cp1250_czech_cs NOT NULL,
  `poznamka` text collate cp1250_czech_cs NOT NULL,
  `vedouci` int(10) unsigned NOT NULL,
  `poslano` tinyint(3) unsigned NOT NULL,
  `oddil` varchar(7) collate cp1250_czech_cs NOT NULL default '',
  `send` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `misto` (`misto`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1250 COLLATE=cp1250_czech_cs COMMENT='tabulka popisu zavodu' AUTO_INCREMENT=1 ;

-- 
-- Vypisuji data pro tabulku `zavod`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabulky `zavxus`
-- 

DROP TABLE IF EXISTS `zavxus`;
CREATE TABLE IF NOT EXISTS `zavxus` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_user` smallint(5) unsigned NOT NULL default '0',
  `id_zavod` smallint(5) unsigned NOT NULL default '0',
  `kat` varchar(10) collate cp1250_czech_cs NOT NULL default '',
  `pozn` varchar(255) collate cp1250_czech_cs default NULL,
  `pozn_in` varchar(255) collate cp1250_czech_cs default NULL,
  `termin` tinyint(4) unsigned NOT NULL default '1',
  `si_chip` int(9) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_termin` (`termin`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1250 COLLATE=cp1250_czech_cs COMMENT='tabulka prihlasek - clovek X zavod' AUTO_INCREMENT=1 ;

-- 
-- Vypisuji data pro tabulku `zavxus`
-- 


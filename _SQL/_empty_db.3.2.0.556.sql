-- phpMyAdmin SQL Dump
-- version 4.0.5
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1:3306

-- Generation Time: Feb 04, 2020 at 12:47 PM
-- Server version: 5.5.33
-- PHP Version: 5.4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `tst2_accounts` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `id_users` smallint(5) DEFAULT NULL,
  `login` varchar(10) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `heslo` varchar(33) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `policy_news` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `policy_regs` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `policy_mng` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `policy_adm` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `podpis` varchar(15) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `locked` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `last_visit` int(11) NOT NULL DEFAULT '0',
  `policy_fin` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`),
  KEY `policy_mng` (`policy_mng`),
  KEY `id_users` (`id_users`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='loginy a hesla uzivatelu' AUTO_INCREMENT=10;

-- --------------------------------------------------------

--
-- Dumping data for table `accounts`
--

INSERT INTO `tst2_accounts` (`id`, `id_users`, `login`, `heslo`, `policy_news`, `policy_regs`, `policy_mng`, `policy_adm`, `podpis`, `locked`, `last_visit`, `policy_fin`) VALUES
(1, null, 'admin', '827ccb0eea8a706c4c34a16891f84e7b', 1, 0, 0, 0, '', 0, 1392508800, 0);

-- admin heslo = 12345
-- --------------------------------------------------------

--
-- Table structure for table `claim`
--

CREATE TABLE `tst2_claim` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `payment_id` int(10) unsigned NOT NULL,
  `text` mediumtext COLLATE utf8_czech_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance`
--

CREATE TABLE `tst2_finance` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_users_editor` smallint(5) unsigned NOT NULL,
  `id_users_user` smallint(5) unsigned NOT NULL,
  `id_zavod` int(10) unsigned DEFAULT NULL,
  `amount` int(11) NOT NULL,
  `date` date NOT NULL,
  `note` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `storno` tinyint(1) DEFAULT NULL,
  `storno_by` int(10) unsigned DEFAULT NULL,
  `storno_date` date DEFAULT NULL,
  `storno_note` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `claim` tinyint(1) DEFAULT NULL COMMENT 'null = bez reklamace, 1 = aktivni reklamace, 0 = uzavrena reklamace',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_types`
--

CREATE TABLE `tst2_finance_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nazev` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `popis` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mailinfo`
--

CREATE TABLE `tst2_mailinfo` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(11) unsigned NOT NULL,
  `email` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `active_tf` tinyint(1) unsigned NOT NULL,
  `active_ch` tinyint(1) unsigned NOT NULL,
  `active_rg` tinyint(1) unsigned NOT NULL,
  `daysbefore` int(2) NOT NULL,
  `type` int(11) NOT NULL,
  `sub_type` int(11) unsigned NOT NULL,
  `ch_data` int(11) unsigned NOT NULL,
  `active_fin` tinyint(1) unsigned NOT NULL,
  `active_finf` tinyint(1) unsigned NOT NULL,
  `fin_type` int(11) unsigned NOT NULL,
  `fin_limit` smallint(5) NOT NULL,
  `active_news` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table `modify_log`
--

CREATE TABLE `tst2_modify_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `action` enum('unknown','add','edit','delete') COLLATE utf8_czech_ci NOT NULL DEFAULT 'unknown',
  `table` varchar(20) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `description` varchar(255) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `author` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `tst2_news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` smallint(5) unsigned DEFAULT NULL,
  `datum` int(11) NOT NULL DEFAULT '0',
  `nadpis` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `text` longtext COLLATE utf8_czech_ci NOT NULL,
  `internal` tinyint(1) NOT NULL DEFAULT '0',
  `modify_flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `sort_datum` (`datum`,`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='novinky';

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `tst2_users` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `prijmeni` varchar(30) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `jmeno` varchar(20) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `datum` date DEFAULT NULL,
  `adresa` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL,
  `mesto` varchar(25) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `psc` varchar(6) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `tel_domu` varchar(25) COLLATE utf8_czech_ci DEFAULT NULL,
  `tel_zam` varchar(25) COLLATE utf8_czech_ci DEFAULT NULL,
  `tel_mobil` varchar(25) COLLATE utf8_czech_ci DEFAULT NULL,
  `email` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL,
  `reg` int(4) unsigned zerofill NOT NULL DEFAULT '0000',
  `si_chip` int(9) unsigned NOT NULL DEFAULT '0',
  `hidden` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `sort_name` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `poh` enum('H','D') COLLATE utf8_czech_ci NOT NULL DEFAULT 'H',
  `lic` enum('E','A','B','C','D','R','-') COLLATE utf8_czech_ci DEFAULT '-',
  `lic_mtbo` enum('E','A','B','C','D','R','-') COLLATE utf8_czech_ci DEFAULT '-',
  `lic_lob` enum('E','A','B','C','D','R','-') COLLATE utf8_czech_ci DEFAULT '-',
  `fin` int(11) NOT NULL DEFAULT '0',
  `chief_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `rc` varchar(10) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `narodnost` varchar(2) COLLATE utf8_czech_ci NOT NULL DEFAULT 'CZ',
  `entry_locked` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `chief_pay` smallint(5) unsigned DEFAULT NULL,
  `finance_type` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name2` (`sort_name`),
  KEY `chief_id` (`chief_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='vsechny informace o uzivatelich';

-- --------------------------------------------------------

--
-- Table structure for table `zavod`
--

CREATE TABLE `tst2_zavod` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `datum` int(11) NOT NULL DEFAULT '0',
  `datum2` int(11) NOT NULL DEFAULT '0',
  `nazev` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL,
  `misto` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL,
  `typ` enum('ob','mtbo','lob','jine','trail') COLLATE utf8_czech_ci NOT NULL,
  `typ0` enum('Z','T','S','V','N','J') COLLATE utf8_czech_ci NOT NULL DEFAULT 'Z',
  `vicedenni` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `zebricek` int(10) unsigned NOT NULL DEFAULT '0',
  `ranking` enum('0','1') COLLATE utf8_czech_ci NOT NULL DEFAULT '0',
  `odkaz` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL,
  `prihlasky` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `prihlasky1` int(11) DEFAULT '0',
  `prihlasky2` int(11) NOT NULL DEFAULT '0',
  `prihlasky3` int(11) NOT NULL DEFAULT '0',
  `prihlasky4` int(11) NOT NULL DEFAULT '0',
  `prihlasky5` int(11) NOT NULL DEFAULT '0',
  `etap` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `kategorie` text COLLATE utf8_czech_ci NOT NULL,
  `poznamka` text COLLATE utf8_czech_ci NOT NULL,
  `vedouci` int(10) unsigned NOT NULL DEFAULT '0',
  `poslano` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `oddil` varchar(7) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `send` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `modify_flag` int(10) unsigned NOT NULL,
  `transport` tinyint(1) DEFAULT NULL,
  `ubytovani` tinyint(1) DEFAULT NULL,
  `cancelled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `misto` (`misto`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='tabulka popisu zavodu';

-- --------------------------------------------------------

--
-- Table structure for table `zavxus`
--

CREATE TABLE `tst2_zavxus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` smallint(5) unsigned NOT NULL DEFAULT '0',
  `id_zavod` smallint(5) unsigned NOT NULL DEFAULT '0',
  `kat` varchar(10) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `pozn` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL,
  `pozn_in` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `termin` tinyint(4) unsigned NOT NULL DEFAULT '1',
  `si_chip` int(9) unsigned NOT NULL DEFAULT '0',
  `transport` tinyint(1) DEFAULT NULL,
  `ubytovani` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_termin` (`termin`,`id`),
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='tabulka prihlasek - clovek X zavod';

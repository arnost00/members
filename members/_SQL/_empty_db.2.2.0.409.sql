-- phpMyAdmin SQL Dump
-- version 3.5.5
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1:3306

-- Generation Time: Feb 16, 2014 at 10:47 PM
-- Server version: 5.5.29
-- PHP Version: 5.4.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `members_empty`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(10) COLLATE cp1250_czech_cs NOT NULL DEFAULT '',
  `heslo` varchar(33) COLLATE cp1250_czech_cs NOT NULL DEFAULT '',
  `policy_news` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `policy_regs` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `policy_mng` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `policy_adm` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `podpis` varchar(15) COLLATE cp1250_czech_cs NOT NULL DEFAULT '',
  `locked` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `last_visit` int(11) NOT NULL DEFAULT '0',
  `policy_fin` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`),
  KEY `policy_mng` (`policy_mng`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1250 COLLATE=cp1250_czech_cs COMMENT='loginy a hesla uzivatelu' AUTO_INCREMENT=2 ;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `login`, `heslo`, `policy_news`, `policy_regs`, `policy_mng`, `policy_adm`, `podpis`, `locked`, `last_visit`, `policy_fin`) VALUES
(1, 'admin', '827ccb0eea8a706c4c34a16891f84e7b', 1, 0, 0, 0, '', 0, 1392508800, 0);

-- admin heslo = 12345
-- --------------------------------------------------------

--
-- Table structure for table `claim`
--

CREATE TABLE `claim` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `payment_id` int(10) unsigned NOT NULL,
  `text` text COLLATE cp1250_czech_cs NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1250 COLLATE=cp1250_czech_cs AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `finance`
--

CREATE TABLE `finance` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_users_editor` smallint(5) unsigned NOT NULL,
  `id_users_user` smallint(5) unsigned NOT NULL,
  `id_zavod` int(10) unsigned DEFAULT NULL,
  `amount` int(11) NOT NULL,
  `date` date NOT NULL,
  `note` varchar(255) COLLATE cp1250_czech_cs DEFAULT NULL,
  `storno` tinyint(1) DEFAULT NULL,
  `storno_by` int(10) unsigned DEFAULT NULL,
  `storno_date` date DEFAULT NULL,
  `storno_note` varchar(255) COLLATE cp1250_czech_cs DEFAULT NULL,
  `claim` tinyint(1) DEFAULT NULL COMMENT 'null = bez reklamace, 1 = aktivni reklamace, 0 = uzavrena reklamace',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=cp1250 COLLATE=cp1250_czech_cs AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `mailinfo`
--

CREATE TABLE `mailinfo` (
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
  `active_fin` tinyint(1) unsigned NOT NULL,
  `active_finf` tinyint(1) unsigned NOT NULL,
  `fin_type` int(11) unsigned NOT NULL,
  `fin_limit` smallint(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1250 COLLATE=cp1250_czech_cs AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `modify_log`
--

CREATE TABLE `modify_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `action` enum('unknown','add','edit','delete') COLLATE cp1250_czech_cs NOT NULL DEFAULT 'unknown',
  `table` varchar(20) COLLATE cp1250_czech_cs NOT NULL DEFAULT '',
  `description` varchar(255) COLLATE cp1250_czech_cs NOT NULL DEFAULT '',
  `author` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1250 COLLATE=cp1250_czech_cs AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` smallint(5) unsigned DEFAULT NULL,
  `datum` int(11) NOT NULL DEFAULT '0',
  `nadpis` varchar(50) COLLATE cp1250_czech_cs NOT NULL DEFAULT '',
  `text` longtext COLLATE cp1250_czech_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sort_datum` (`datum`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1250 COLLATE=cp1250_czech_cs COMMENT='novinky' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `prijmeni` varchar(30) COLLATE cp1250_czech_cs NOT NULL DEFAULT '',
  `jmeno` varchar(20) COLLATE cp1250_czech_cs NOT NULL DEFAULT '',
  `datum` date DEFAULT NULL,
  `adresa` varchar(50) COLLATE cp1250_czech_cs DEFAULT NULL,
  `mesto` varchar(25) COLLATE cp1250_czech_cs NOT NULL,
  `psc` varchar(6) COLLATE cp1250_czech_cs NOT NULL,
  `tel_domu` varchar(25) COLLATE cp1250_czech_cs DEFAULT NULL,
  `tel_zam` varchar(25) COLLATE cp1250_czech_cs DEFAULT NULL,
  `tel_mobil` varchar(25) COLLATE cp1250_czech_cs DEFAULT NULL,
  `email` varchar(50) COLLATE cp1250_czech_cs DEFAULT NULL,
  `reg` int(4) unsigned zerofill NOT NULL DEFAULT '0000',
  `si_chip` int(9) unsigned NOT NULL DEFAULT '0',
  `hidden` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `sort_name` varchar(50) COLLATE cp1250_czech_cs NOT NULL DEFAULT '',
  `poh` enum('H','D') COLLATE cp1250_czech_cs NOT NULL DEFAULT 'H',
  `lic` enum('E','A','B','C','D','R','-') COLLATE cp1250_czech_cs DEFAULT '-',
  `lic_mtbo` enum('E','A','B','C','D','R','-') COLLATE cp1250_czech_cs DEFAULT '-',
  `lic_lob` enum('E','A','B','C','D','R','-') COLLATE cp1250_czech_cs DEFAULT '-',
  `fin` int(11) NOT NULL DEFAULT '0',
  `chief_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `rc` varchar(10) COLLATE cp1250_czech_cs NOT NULL,
  `narodnost` varchar(2) COLLATE cp1250_czech_cs NOT NULL DEFAULT 'CZ',
  PRIMARY KEY (`id`),
  KEY `name2` (`sort_name`),
  KEY `chief_id` (`chief_id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1250 COLLATE=cp1250_czech_cs COMMENT='vsechny informace o uzivatelich' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `usxus`
--

CREATE TABLE `usxus` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `id_accounts` smallint(5) unsigned NOT NULL DEFAULT '0',
  `id_users` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_users` (`id_users`),
  KEY `id_accounts` (`id_accounts`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1250 COLLATE=cp1250_czech_cs COMMENT='mezitabulka mezi ID user a ID users' AUTO_INCREMENT=2 ;

--
-- Dumping data for table `usxus`
--

INSERT INTO `usxus` (`id`, `id_accounts`, `id_users`) VALUES
(1, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `zavod`
--

CREATE TABLE `zavod` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `datum` int(11) NOT NULL DEFAULT '0',
  `datum2` int(11) NOT NULL DEFAULT '0',
  `nazev` varchar(50) COLLATE cp1250_czech_cs DEFAULT NULL,
  `misto` varchar(50) COLLATE cp1250_czech_cs DEFAULT NULL,
  `typ` enum('ob','mtbo','lob','jine','trail') COLLATE cp1250_czech_cs NOT NULL,
  `vicedenni` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `zebricek` int(10) unsigned NOT NULL DEFAULT '0',
  `ranking` enum('0','1') COLLATE cp1250_czech_cs NOT NULL DEFAULT '0',
  `odkaz` varchar(100) COLLATE cp1250_czech_cs DEFAULT NULL,
  `prihlasky` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `prihlasky1` int(11) DEFAULT '0',
  `prihlasky2` int(11) NOT NULL DEFAULT '0',
  `prihlasky3` int(11) NOT NULL DEFAULT '0',
  `prihlasky4` int(11) NOT NULL DEFAULT '0',
  `prihlasky5` int(11) NOT NULL DEFAULT '0',
  `etap` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `kategorie` text COLLATE cp1250_czech_cs NOT NULL,
  `poznamka` text COLLATE cp1250_czech_cs NOT NULL,
  `vedouci` int(10) unsigned NOT NULL,
  `poslano` tinyint(3) unsigned NOT NULL,
  `oddil` varchar(7) COLLATE cp1250_czech_cs NOT NULL DEFAULT '',
  `send` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `modify_flag` int(10) unsigned NOT NULL,
  `transport` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `misto` (`misto`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1250 COLLATE=cp1250_czech_cs COMMENT='tabulka popisu zavodu' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `zavxus`
--

CREATE TABLE `zavxus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` smallint(5) unsigned NOT NULL DEFAULT '0',
  `id_zavod` smallint(5) unsigned NOT NULL DEFAULT '0',
  `kat` varchar(10) COLLATE cp1250_czech_cs NOT NULL DEFAULT '',
  `pozn` varchar(255) COLLATE cp1250_czech_cs DEFAULT NULL,
  `pozn_in` varchar(255) COLLATE cp1250_czech_cs DEFAULT NULL,
  `termin` tinyint(4) unsigned NOT NULL DEFAULT '1',
  `si_chip` int(9) unsigned NOT NULL DEFAULT '0',
  `transport` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_termin` (`termin`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1250 COLLATE=cp1250_czech_cs COMMENT='tabulka prihlasek - clovek X zavod' AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Apr 14, 2026 at 03:44 AM
-- Server version: 10.11.16-MariaDB-ubu2204
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `d235220_members`
--

-- --------------------------------------------------------

--
-- Table structure for table `tokens`
--

CREATE TABLE `tokens` (
  `device` varchar(36) NOT NULL COMMENT 'https://capacitorjs.com/docs/apis/device#deviceid',
  `device_name` varchar(32) NOT NULL,
  `user_id` smallint(5) NOT NULL,
  `fcm_token` varchar(4096) NOT NULL,
  `fcm_token_timestamp` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'update on token update',
  `app_version` varchar(16) NOT NULL,
  `app_last_opened` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tst_accounts`
--

CREATE TABLE `tst_accounts` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `id_users` smallint(5) DEFAULT NULL,
  `login` varchar(25) NOT NULL DEFAULT '',
  `heslo` varchar(255) NOT NULL DEFAULT '',
  `policy_news` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `policy_regs` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `policy_mng` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `policy_adm` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `podpis` varchar(15) NOT NULL DEFAULT '',
  `locked` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `last_visit` int(11) NOT NULL DEFAULT 0,
  `policy_fin` tinyint(1) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_czech_ci COMMENT='loginy a hesla uzivatelu';

--
-- Dumping data for table `tst_accounts`
--

INSERT INTO `tst_accounts` (`id`, `id_users`, `login`, `heslo`, `policy_news`, `policy_regs`, `policy_mng`, `policy_adm`, `podpis`, `locked`, `last_visit`, `policy_fin`) VALUES
(1, 0, 'admin', '$2y$10$R1tJ3QEZsG490bMvfao2jOKnhrJUOnHntqKQwgp14vbIN88BMRMje', 1, 0, 0, 0, '', 0, 1710802800, 0),
(10, 2, 'test_k', '$2y$10$R1tJ3QEZsG490bMvfao2jOKnhrJUOnHntqKQwgp14vbIN88BMRMje', 1, 1, 0, 1, 'Kenia', 0, 1578524400, 1),
(11, 1, 'test_a', '$2y$10$R1tJ3QEZsG490bMvfao2jOKnhrJUOnHntqKQwgp14vbIN88BMRMje', 1, 1, 4, 1, 'arnost', 0, 1578438000, 1),
(12, 3, 'test_v', '$2y$10$R1tJ3QEZsG490bMvfao2jOKnhrJUOnHntqKQwgp14vbIN88BMRMje', 1, 1, 4, 1, 'Veseláček', 0, 1359586800, 1),
(13, 4, 'tnov_1', '$2y$10$R1tJ3QEZsG490bMvfao2jOKnhrJUOnHntqKQwgp14vbIN88BMRMje', 1, 1, 0, 0, 'tnov_1', 0, 1700262000, 0),
(14, 5, 'tnov_2', '$2y$10$R1tJ3QEZsG490bMvfao2jOKnhrJUOnHntqKQwgp14vbIN88BMRMje', 1, 0, 4, 0, 'tnov_2', 0, 1708988400, 0),
(15, 6, 'tnov_3', '$2y$10$R1tJ3QEZsG490bMvfao2jOKnhrJUOnHntqKQwgp14vbIN88BMRMje', 1, 1, 4, 1, 'tnov_3', 0, 1708988400, 1),
(16, 7, 'tnov_4', '$2y$10$R1tJ3QEZsG490bMvfao2jOKnhrJUOnHntqKQwgp14vbIN88BMRMje', 0, 0, 2, 0, 'tnov_4', 0, 1708988400, 0),
(17, 8, 'tnov_5', '$2y$10$R1tJ3QEZsG490bMvfao2jOKnhrJUOnHntqKQwgp14vbIN88BMRMje', 0, 0, 0, 0, 'tnov_5', 0, 1701903600, 0),
(18, 11, 'test_d1', '$2y$10$R1tJ3QEZsG490bMvfao2jOKnhrJUOnHntqKQwgp14vbIN88BMRMje', 1, 0, 2, 0, 'Drbča', 0, 1393455600, 0),
(19, 14, 'hanah', '$2y$10$R1tJ3QEZsG490bMvfao2jOKnhrJUOnHntqKQwgp14vbIN88BMRMje', 1, 0, 2, 0, 'Hanka', 0, 1420758000, 0),
(20, 13, 'test_k1', '$2y$10$R1tJ3QEZsG490bMvfao2jOKnhrJUOnHntqKQwgp14vbIN88BMRMje', 1, 0, 2, 0, 'Verča', 0, 1342648800, 0),
(21, 18, 'test_s1', '$2y$10$R1tJ3QEZsG490bMvfao2jOKnhrJUOnHntqKQwgp14vbIN88BMRMje', 1, 0, 2, 0, 'Martin', 0, 1340056800, 0),
(22, 12, 'test_z1', '$2y$10$R1tJ3QEZsG490bMvfao2jOKnhrJUOnHntqKQwgp14vbIN88BMRMje', 1, 0, 2, 0, 'Zhusta', 0, 1361919600, 1),
(23, 15, 'test_z3', '$2y$10$R1tJ3QEZsG490bMvfao2jOKnhrJUOnHntqKQwgp14vbIN88BMRMje', 1, 0, 2, 0, 'Tomi1', 0, 1358982000, 0),
(24, 32, 'test_c1', '$2y$10$R1tJ3QEZsG490bMvfao2jOKnhrJUOnHntqKQwgp14vbIN88BMRMje', 1, 1, 4, 1, 'Cvrcek', 0, 1359932400, 0),
(25, 23, 'MB7605', '$2y$10$R1tJ3QEZsG490bMvfao2jOKnhrJUOnHntqKQwgp14vbIN88BMRMje', 0, 0, 0, 0, 'Maroš', 0, 0, 0),
(26, 21, 'PB8101', '$2y$10$R1tJ3QEZsG490bMvfao2jOKnhrJUOnHntqKQwgp14vbIN88BMRMje', 0, 0, 0, 0, 'Palo', 0, 0, 0),
(27, 33, 'VM8951', '$2y$10$R1tJ3QEZsG490bMvfao2jOKnhrJUOnHntqKQwgp14vbIN88BMRMje', 1, 0, 0, 0, 'Věra', 0, 1421622000, 1),
(28, 9, 'tnov_6', '$2y$10$R1tJ3QEZsG490bMvfao2jOKnhrJUOnHntqKQwgp14vbIN88BMRMje', 0, 0, 0, 0, 'tnov_6', 0, 1700262000, 1),
(29, 34, 'majkl', '$2y$10$R1tJ3QEZsG490bMvfao2jOKnhrJUOnHntqKQwgp14vbIN88BMRMje', 0, 1, 2, 0, 'majkl', 0, 1393714800, 1),
(30, 36, 'RS8006', '$2y$10$R1tJ3QEZsG490bMvfao2jOKnhrJUOnHntqKQwgp14vbIN88BMRMje', 0, 0, 0, 0, 'Řehoř', 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tst_bank_transactions`
--

CREATE TABLE `tst_bank_transactions` (
  `id` int(10) UNSIGNED NOT NULL,
  `transaction_id` varchar(64) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL,
  `variable_symbol` varchar(20) DEFAULT NULL,
  `constant_symbol` varchar(20) DEFAULT NULL,
  `specific_symbol` varchar(20) DEFAULT NULL,
  `originator_message` text DEFAULT NULL,
  `status` enum('PROCESSED','ORPHAN','ERROR') NOT NULL DEFAULT 'ORPHAN',
  `finance_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_czech_ci COMMENT='Bankovni transakce z API';

-- --------------------------------------------------------

--
-- Table structure for table `tst_categories_predef`
--

CREATE TABLE `tst_categories_predef` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `cat_list` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_czech_ci;

--
-- Dumping data for table `tst_categories_predef`
--

INSERT INTO `tst_categories_predef` (`id`, `name`, `cat_list`) VALUES
(1, 'Oblž', 'D10N;D12;D14;D16;D18;D21C;D21D;D35;D45;D55;H10N;H12;H14;H16;H18;H21C;H21D;H35;H45;H55;HDR;'),
(2, 'Oblž větší', 'D10N;D12C;D14C;D16C;D18C;D21C;D21D;D35C;D45C;D55C;H10N;H12C;H14C;H16C;H18C;H21C;H21D;H35C;H45C;H55C;HDR;'),
(3, 'žebříček B', 'D12B;D14B;D16B;D18B;D20B;D21B;D21C;D35B;D40B;D45B;D50B;D55B;D60B;D65B;H12B;H14B;H16B;H18B;H20B;H21B;H21C;H35B;H40B;H45B;H50B;H55B;H60B;H65B;H70B;H75B;'),
(4, 'žebříček A', 'D16A;D18A;D20A;D21A;D21E;H16A;H18A;H20A;H21A;H21E;'),
(5, 'Štafety', 'D14;D18;D21;D105;D140;H14;H18;H21;H105;H140;H165;dorost;dospělí;HD175;HD235;'),
(6, 'MTBO', 'W11;W14;W17;W20;W21E;W21A;W21B;W40;W50;W60;M11;M14;M17;M20;M21E;M21A;M21B;M40A;M40B;M50;M60;OPEN;');

-- --------------------------------------------------------

--
-- Table structure for table `tst_claim`
--

CREATE TABLE `tst_claim` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `payment_id` int(10) UNSIGNED NOT NULL,
  `text` mediumtext NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_czech_ci;

--
-- Dumping data for table `tst_claim`
--

INSERT INTO `tst_claim` (`id`, `user_id`, `payment_id`, `text`, `date`) VALUES
(1, 2, 23, '¨ěščěšřžčýřžýářžýýöïüÿt¨rewëw¨dfs¨ds¨dfas', '2015-02-16 15:56:11'),
(2, 2, 9, '¨;+ě+čřžščýřžýíáýáéýáí', '2015-02-16 15:56:37'),
(3, 2, 13, '', '2015-02-16 16:08:56'),
(4, 9, 87, 'vyreseno', '2018-08-28 18:46:37');

-- --------------------------------------------------------

--
-- Table structure for table `tst_finance`
--

CREATE TABLE `tst_finance` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_users_editor` smallint(5) UNSIGNED NOT NULL,
  `id_users_user` smallint(5) UNSIGNED NOT NULL,
  `id_zavod` int(10) UNSIGNED DEFAULT NULL,
  `amount` int(11) NOT NULL,
  `date` date NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `storno` tinyint(1) DEFAULT NULL,
  `storno_by` int(10) UNSIGNED DEFAULT NULL,
  `storno_date` date DEFAULT NULL,
  `storno_note` varchar(255) DEFAULT NULL,
  `claim` tinyint(1) DEFAULT NULL COMMENT 'null = bez reklamace, 1 = aktivni reklamace, 0 = uzavrena reklamace'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_czech_ci;

--
-- Dumping data for table `tst_finance`
--

INSERT INTO `tst_finance` (`id`, `id_users_editor`, `id_users_user`, `id_zavod`, `amount`, `date`, `note`, `storno`, `storno_by`, `storno_date`, `storno_note`, `claim`) VALUES
(1, 0, 8, 3, -5000, '2013-02-05', '', NULL, NULL, NULL, NULL, NULL),
(2, 0, 8, 4, 5400, '2013-02-05', '', NULL, NULL, NULL, NULL, NULL),
(3, 1, 1, NULL, 4000, '2013-02-05', 'členský příspěvek', NULL, NULL, NULL, NULL, NULL),
(4, 1, 1, 11, -200, '2013-02-05', 'startovné', NULL, NULL, NULL, NULL, NULL),
(5, 1, 1, 11, -60, '2013-02-05', 'ubytování', NULL, NULL, NULL, NULL, NULL),
(6, 2, 22, NULL, -32, '2013-02-05', '32', NULL, NULL, NULL, NULL, NULL),
(7, 2, 22, 17, 543, '2013-02-05', '543', NULL, NULL, NULL, NULL, NULL),
(8, 2, 2, 3, -32768, '2013-02-05', 'er3', 1, 2, '2013-02-06', 'storno', NULL),
(9, 2, 2, 3, -100, '2013-02-06', 'stovka', NULL, NULL, NULL, NULL, 0),
(10, 2, 22, 37, 0, '2013-02-06', 'trista', NULL, NULL, NULL, NULL, NULL),
(11, 1, 7, 10, 300, '2013-02-21', 'test', 1, 2, '2013-03-23', '', NULL),
(12, 1, 1, 38, -130, '2013-02-25', 'startovné', NULL, NULL, NULL, NULL, NULL),
(13, 1, 2, 38, -130, '2013-02-25', 'startovné', NULL, NULL, NULL, NULL, 0),
(14, 1, 11, 38, 130, '2013-02-25', 'startovné', NULL, NULL, NULL, NULL, NULL),
(15, 1, 6, 38, -70, '2013-02-25', 'storno startovné', NULL, NULL, NULL, NULL, NULL),
(16, 1, 5, 35, -70, '2013-02-25', 'storno startovné', NULL, NULL, NULL, NULL, NULL),
(17, 12, 22, NULL, 500, '2013-02-01', 'Oddílové přípsěvky', NULL, NULL, NULL, NULL, NULL),
(18, 12, 1, 1, 80, '2013-02-27', 'závod Jml', NULL, NULL, NULL, NULL, NULL),
(19, 12, 2, 1, 80, '2013-02-27', 'závod Jml', NULL, NULL, NULL, NULL, NULL),
(20, 12, 3, 1, 80, '2013-02-27', 'závod Jml', NULL, NULL, NULL, NULL, NULL),
(21, 12, 4, 1, 0, '2013-02-27', 'závod Jml', NULL, NULL, NULL, NULL, NULL),
(22, 12, 5, 1, 80, '2013-02-27', 'závod Jml', NULL, NULL, NULL, NULL, NULL),
(23, 12, 2, NULL, 1800, '2013-02-27', 'Oddílové příspěvky', NULL, NULL, NULL, NULL, 0),
(24, 2, 22, NULL, -100, '2013-03-23', 'platba', NULL, NULL, NULL, NULL, NULL),
(25, 2, 1, 20, 400, '2013-03-23', 'adfds', NULL, NULL, NULL, NULL, NULL),
(26, 33, 33, 33, -40, '2013-05-14', 'vkld', NULL, NULL, NULL, NULL, NULL),
(27, 2, 29, 26, 100, '2013-05-15', 'sto podruhe', NULL, NULL, NULL, NULL, NULL),
(28, 2, 3, 36, 400, '2013-05-28', '+??š?+?š??š?šžž?ýáýú?ú?', NULL, NULL, NULL, NULL, NULL),
(29, 2, 22, 36, 100, '2013-05-28', 'sto', NULL, NULL, NULL, NULL, NULL),
(30, 1, 8, 10, -500, '2013-05-28', '?š??žýáíé', NULL, NULL, NULL, NULL, NULL),
(31, 1, 9, 15, 200, '2013-05-28', 'test2', NULL, NULL, NULL, NULL, NULL),
(32, 1, 8, 15, 300, '2013-05-28', 'test2', NULL, NULL, NULL, NULL, NULL),
(33, 9, 13, 40, -40, '2014-01-31', '', NULL, NULL, NULL, NULL, NULL),
(34, 9, 13, NULL, 40, '2014-01-31', '', NULL, NULL, NULL, NULL, NULL),
(35, 0, 26, NULL, 200, '2014-02-04', '', NULL, NULL, NULL, NULL, NULL),
(36, 9, 27, NULL, 300, '2014-02-04', 'aser', 1, 9, '2014-02-04', 'chybka', NULL),
(37, 9, 4, 41, 150, '2014-02-06', 'Startovne', NULL, NULL, NULL, NULL, NULL),
(38, 9, 5, 41, 150, '2014-02-06', 'Startovne', NULL, NULL, NULL, NULL, NULL),
(39, 9, 6, 41, 150, '2014-02-06', 'Startovne', NULL, NULL, NULL, NULL, NULL),
(40, 9, 7, 41, 80, '2014-02-06', '', NULL, NULL, NULL, NULL, NULL),
(41, 9, 8, 41, 80, '2014-02-06', '', NULL, NULL, NULL, NULL, NULL),
(42, 9, 4, NULL, 1000, '2014-02-06', 'vklad na účet', NULL, NULL, NULL, NULL, NULL),
(43, 2, 22, 11, 100, '2014-02-07', '', NULL, NULL, NULL, NULL, NULL),
(44, 2, 22, 11, -200, '2014-02-07', '', NULL, NULL, NULL, NULL, NULL),
(45, 2, 22, 11, 300, '2014-02-07', 'vklad', NULL, NULL, NULL, NULL, NULL),
(46, 9, 4, 42, 80, '2014-02-11', '', NULL, NULL, NULL, NULL, NULL),
(47, 9, 5, 42, 80, '2014-02-11', '', NULL, NULL, NULL, NULL, NULL),
(48, 9, 6, 42, 80, '2014-02-11', '', NULL, NULL, NULL, NULL, NULL),
(49, 9, 7, 42, 80, '2014-02-11', '', NULL, NULL, NULL, NULL, NULL),
(50, 9, 8, 42, 80, '2014-02-11', '', NULL, NULL, NULL, NULL, NULL),
(51, 9, 4, 42, 80, '2014-02-11', '', 1, 9, '2014-02-11', '', NULL),
(52, 9, 5, 42, 80, '2014-02-11', '', NULL, NULL, NULL, NULL, NULL),
(53, 9, 6, 42, 80, '2014-02-11', '', NULL, NULL, NULL, NULL, NULL),
(54, 9, 7, 42, 80, '2014-02-11', '', NULL, NULL, NULL, NULL, NULL),
(55, 9, 8, 42, 80, '2014-02-11', '', NULL, NULL, NULL, NULL, NULL),
(56, 9, 4, 42, 80, '2014-02-11', '', NULL, NULL, NULL, NULL, NULL),
(57, 9, 5, 42, 80, '2014-02-11', '', NULL, NULL, NULL, NULL, NULL),
(58, 9, 6, 42, 80, '2014-02-11', '', NULL, NULL, NULL, NULL, NULL),
(59, 9, 7, 42, 80, '2014-02-11', '', NULL, NULL, NULL, NULL, NULL),
(60, 9, 8, 42, 80, '2014-02-11', '', NULL, NULL, NULL, NULL, NULL),
(61, 7, 7, 0, -400, '2014-02-26', '', NULL, NULL, NULL, NULL, NULL),
(62, 7, 9, 0, 400, '2014-02-26', '', NULL, NULL, NULL, NULL, NULL),
(63, 5, 5, 0, -400, '2014-02-26', '', NULL, NULL, NULL, NULL, NULL),
(64, 5, 8, 0, 400, '2014-02-26', '', NULL, NULL, NULL, NULL, NULL),
(65, 9, 4, 8, -4000, '2014-02-27', 'test velke platby', NULL, NULL, NULL, NULL, NULL),
(66, 13, 13, 0, -100, '2014-02-27', '', NULL, NULL, NULL, NULL, NULL),
(67, 13, 11, 27, 1000, '2014-02-27', '', NULL, NULL, NULL, NULL, NULL),
(68, 11, 11, 0, -200, '2014-02-27', '', NULL, NULL, NULL, NULL, NULL),
(69, 11, 13, 0, 200, '2014-02-27', '', NULL, NULL, NULL, NULL, NULL),
(70, 9, 4, 43, -80, '2014-03-02', '', NULL, NULL, NULL, NULL, NULL),
(71, 9, 5, 43, -80, '2014-03-02', '', NULL, NULL, NULL, NULL, NULL),
(72, 9, 6, 43, -80, '2014-03-02', '', NULL, NULL, NULL, NULL, NULL),
(73, 9, 7, 43, -80, '2014-03-02', '', NULL, NULL, NULL, NULL, NULL),
(74, 9, 8, 43, -80, '2014-03-02', '', NULL, NULL, NULL, NULL, NULL),
(75, 2, 1, 21, 1000, '2014-04-29', '', NULL, NULL, NULL, NULL, NULL),
(76, 2, 2, 0, -12, '2015-02-16', 'asfs', NULL, NULL, NULL, NULL, NULL),
(77, 2, 22, 0, 12, '2015-02-16', 'asfs', NULL, NULL, NULL, NULL, NULL),
(78, 1, 23, 36, 100, '2015-02-16', '', NULL, NULL, NULL, NULL, NULL),
(79, 0, 22, 15, 100, '2015-02-17', '[\'2222', NULL, NULL, NULL, NULL, NULL),
(80, 9, 14, 0, 500, '2016-02-15', 'Vklad', NULL, NULL, NULL, NULL, NULL),
(81, 9, 14, 31, -30, '2016-02-15', '', NULL, NULL, NULL, NULL, NULL),
(82, 9, 13, 31, -500, '2016-02-15', '', NULL, NULL, NULL, NULL, NULL),
(83, 9, 33, 31, -13, '2016-02-15', '', NULL, NULL, NULL, NULL, NULL),
(84, 9, 9, 0, -20, '2016-02-15', 'zkouška transferu', NULL, NULL, NULL, NULL, NULL),
(85, 9, 10, 0, 20, '2016-02-15', 'zkouška transferu', NULL, NULL, NULL, NULL, NULL),
(86, 1, 1, 0, -200, '2016-02-20', '', NULL, NULL, NULL, NULL, NULL),
(87, 1, 9, 0, 200, '2016-02-20', '', NULL, NULL, NULL, NULL, 0),
(88, 22, 22, 0, -1000, '2016-05-11', '', 1, 9, '2016-05-11', '', NULL),
(89, 22, 32, 0, 1000, '2016-05-11', '', NULL, NULL, NULL, NULL, NULL),
(90, 2, 7, NULL, 2000, '2017-02-27', '', NULL, NULL, NULL, NULL, NULL),
(91, 2, 8, 29, -840, '2017-02-27', '', NULL, NULL, NULL, NULL, NULL),
(92, 2, 8, 30, -1820, '2017-02-27', '', NULL, NULL, NULL, NULL, NULL),
(93, 2, 4, 0, 2840, '2017-02-27', '', NULL, NULL, NULL, NULL, NULL),
(94, 2, 5, 0, -150, '2017-02-27', '', NULL, NULL, NULL, NULL, NULL),
(95, 2, 5, 0, 300, '2017-02-27', '', NULL, NULL, NULL, NULL, NULL),
(96, 2, 6, 0, -170, '2017-02-27', '', NULL, NULL, NULL, NULL, NULL),
(97, 2, 9, 0, -780, '2017-02-27', '', NULL, NULL, NULL, NULL, NULL),
(98, 2, 7, 0, -20, '2017-02-27', '', NULL, NULL, NULL, NULL, NULL),
(99, 7, 7, 0, -1819, '2017-02-27', '', NULL, NULL, NULL, NULL, NULL),
(100, 7, 8, 0, 1819, '2017-02-27', '', NULL, NULL, NULL, NULL, NULL),
(101, 2, 7, 0, 1819, '2017-02-27', '', NULL, NULL, NULL, NULL, NULL),
(102, 2, 8, 0, -1819, '2017-02-27', '', NULL, NULL, NULL, NULL, NULL),
(103, 1, 8, 0, 1820, '2017-02-27', '', NULL, NULL, NULL, NULL, NULL),
(104, 1, 5, 0, -1820, '2017-02-27', '', NULL, NULL, NULL, NULL, NULL),
(105, 2, 5, 34, 0, '2017-02-27', '', NULL, NULL, NULL, NULL, NULL),
(106, 2, 8, 0, 10000, '2018-02-09', '', NULL, NULL, NULL, NULL, NULL),
(107, 2, 9, 0, 10000, '2018-02-09', '', NULL, NULL, NULL, NULL, NULL),
(108, 2, 7, 0, 10000, '2018-02-09', '', NULL, NULL, NULL, NULL, NULL),
(109, 0, 37, 4, -500, '2018-03-10', '', NULL, NULL, NULL, NULL, NULL),
(110, 9, 37, 0, -500, '2018-03-10', 'oddilove prispevky', NULL, NULL, NULL, NULL, NULL),
(111, 9, 37, 0, 3000, '2018-03-10', 'vklad na ucet', NULL, NULL, NULL, NULL, NULL),
(112, 9, 37, 1, -150, '2018-03-10', '', NULL, NULL, NULL, NULL, NULL),
(113, 37, 37, 0, -300, '2018-03-10', ' <i>[Gross Michal->Mocvesely Osvald]</i> ', NULL, NULL, NULL, NULL, NULL),
(114, 37, 34, 0, 300, '2018-03-10', ' <i>[Gross Michal->Mocvesely Osvald]</i> ', NULL, NULL, NULL, NULL, NULL),
(115, 37, 37, 0, -200, '2018-03-10', ' <i>[Kočová Hana->Mocvesely Osvald]</i> ', NULL, NULL, NULL, NULL, NULL),
(116, 37, 28, 0, 200, '2018-03-10', ' <i>[Kočová Hana->Mocvesely Osvald]</i> ', NULL, NULL, NULL, NULL, NULL),
(117, 7, 7, 0, -1, '2018-03-10', ' <i>[Nováková Eva->Bukovac Dušan]</i> ', NULL, NULL, NULL, NULL, NULL),
(118, 7, 22, 0, 1, '2018-03-10', ' <i>[Nováková Eva->Bukovac Dušan]</i> ', NULL, NULL, NULL, NULL, NULL),
(119, 2, 2, 0, -2, '2018-03-10', '', NULL, NULL, NULL, NULL, NULL),
(120, 2, 22, 0, 2, '2018-03-10', '', NULL, NULL, NULL, NULL, NULL),
(121, 2, 2, 0, -3, '2018-03-10', '', NULL, NULL, NULL, NULL, NULL),
(122, 2, 23, 0, 3, '2018-03-10', '', NULL, NULL, NULL, NULL, NULL),
(123, 2, 2, 0, -1, '2018-03-13', ' <i>[König Lukáš->Bukovac Dušan]</i> poznamka 1', NULL, NULL, NULL, NULL, NULL),
(124, 2, 22, 0, 1, '2018-03-13', ' <i>[König Lukáš->Bukovac Dušan]</i> poznamka 1', NULL, NULL, NULL, NULL, NULL),
(125, 22, 22, 0, -2, '2018-03-13', ' <i>[König Lukáš->Bukovac Dušan]</i> poznamka 2', NULL, NULL, NULL, NULL, NULL),
(126, 22, 2, 0, 2, '2018-03-13', ' <i>[König Lukáš->Bukovac Dušan]</i> poznamka 2', NULL, NULL, NULL, NULL, NULL),
(127, 22, 22, 0, -2, '2018-03-13', ' <i>[König Lukáš->Bukovac Dušan]</i> poznamka 2', NULL, NULL, NULL, NULL, NULL),
(128, 22, 2, 0, 2, '2018-03-13', ' <i>[König Lukáš->Bukovac Dušan]</i> poznamka 2', NULL, NULL, NULL, NULL, NULL),
(129, 2, 2, 0, -1, '2018-03-16', ' <i>[König Lukáš->Bukovac Dušan]</i> ', NULL, NULL, NULL, NULL, NULL),
(130, 2, 22, 0, 1, '2018-03-16', ' <i>[König Lukáš->Bukovac Dušan]</i> ', NULL, NULL, NULL, NULL, NULL),
(131, 2, 2, 0, -2, '2018-03-16', ' <i>[König Lukáš->Nováková Eva]</i> ', NULL, NULL, NULL, NULL, NULL),
(132, 2, 7, 0, 2, '2018-03-16', ' <i>[König Lukáš->Nováková Eva]</i> ', NULL, NULL, NULL, NULL, NULL),
(133, 7, 7, 0, -1, '2018-03-16', ' <i>[Nováková Eva->Bukovac Dušan]</i> ', NULL, NULL, NULL, NULL, NULL),
(134, 7, 22, 0, 1, '2018-03-16', ' <i>[Nováková Eva->Bukovac Dušan]</i> ', NULL, NULL, NULL, NULL, NULL),
(135, 7, 7, 0, -2, '2018-03-16', ' <i>[Novák Karel->Nováková Eva]</i> ', NULL, NULL, NULL, NULL, NULL),
(136, 7, 5, 0, 2, '2018-03-16', ' <i>[Novák Karel->Nováková Eva]</i> ', NULL, NULL, NULL, NULL, NULL),
(137, 7, 7, 0, -3, '2018-03-16', ' <i>[Novák Karel->Nováková Eva]</i> novakova->novak', NULL, NULL, NULL, NULL, NULL),
(138, 7, 5, 0, 3, '2018-03-16', ' <i>[Novák Karel->Nováková Eva]</i> novakova->novak', NULL, NULL, NULL, NULL, NULL),
(139, 2, 2, 0, -12, '2018-03-22', ' <i>[König Lukáš->Bukovac Dušan]</i> ', NULL, NULL, NULL, NULL, NULL),
(140, 2, 22, 0, 12, '2018-03-22', ' <i>[König Lukáš->Bukovac Dušan]</i> ', NULL, NULL, NULL, NULL, NULL),
(141, 2, 2, 0, -13, '2018-03-22', ' <i>[König Lukáš->Nováková Eva]</i> ', NULL, NULL, NULL, NULL, NULL),
(142, 2, 7, 0, 13, '2018-03-22', ' <i>[König Lukáš->Nováková Eva]</i> ', NULL, NULL, NULL, NULL, NULL),
(143, 2, 2, 0, -300, '2018-03-22', ' <i>[König Lukáš->Novák Martin]</i> ', NULL, NULL, NULL, NULL, NULL),
(144, 2, 6, 0, 300, '2018-03-22', ' <i>[König Lukáš->Novák Martin]</i> ', NULL, NULL, NULL, NULL, NULL),
(145, 2, 2, 0, -300, '2018-03-22', ' <i>[König Lukáš->Nováková Eva]</i> ', NULL, NULL, NULL, NULL, NULL),
(146, 2, 7, 0, 300, '2018-03-22', ' <i>[König Lukáš->Nováková Eva]</i> ', NULL, NULL, NULL, NULL, NULL),
(147, 6, 6, 0, -1, '2018-03-22', ' <i>[Novák Martin->König Lukáš]</i> nov-kenia', NULL, NULL, NULL, NULL, NULL),
(148, 6, 2, 0, 1, '2018-03-22', ' <i>[Novák Martin->König Lukáš]</i> nov-kenia', NULL, NULL, NULL, NULL, NULL),
(149, 6, 6, 0, -2, '2018-03-22', ' <i>[Novák Martin->Nováková Eva]</i> mart-eva', NULL, NULL, NULL, NULL, NULL),
(150, 6, 7, 0, 2, '2018-03-22', ' <i>[Novák Martin->Nováková Eva]</i> mart-eva', NULL, NULL, NULL, NULL, NULL),
(151, 7, 7, 0, -5, '2018-03-22', ' <i>[Nováková Eva->Novák Martin]</i> eva-martin', NULL, NULL, NULL, NULL, NULL),
(152, 7, 6, 0, 5, '2018-03-22', ' <i>[Nováková Eva->Novák Martin]</i> eva-martin', NULL, NULL, NULL, NULL, NULL),
(153, 7, 7, 0, -6, '2018-03-22', ' <i>[Nováková Eva->König Lukáš]</i> eva-kenia', NULL, NULL, NULL, NULL, NULL),
(154, 7, 2, 0, 6, '2018-03-22', ' <i>[Nováková Eva->König Lukáš]</i> eva-kenia', NULL, NULL, NULL, NULL, NULL),
(155, 9, 21, 5, -50, '2018-08-28', '', NULL, NULL, NULL, NULL, NULL),
(156, 9, 11, 5, -100, '2018-08-28', '', NULL, NULL, NULL, NULL, NULL),
(157, 2, 2, 0, 10000, '2020-01-08', 'test', NULL, NULL, NULL, NULL, NULL),
(158, 2, 2, 0, -1000, '2019-01-08', 'test 2019', NULL, NULL, NULL, NULL, NULL),
(159, 2, 2, 0, -1, '2020-01-08', ' <i>[König Lukáš->Bukovac Dušan]</i> ', NULL, NULL, NULL, NULL, NULL),
(160, 2, 22, 0, 1, '2020-01-08', ' <i>[König Lukáš->Bukovac Dušan]</i> ', NULL, NULL, NULL, NULL, NULL),
(161, 2, 2, 0, -2, '2020-01-08', ' <i>[König Lukáš->Bukovac Dušan]</i> ', NULL, NULL, NULL, NULL, NULL),
(162, 2, 22, 0, 2, '2020-01-08', ' <i>[König Lukáš->Bukovac Dušan]</i> ', NULL, NULL, NULL, NULL, NULL),
(163, 2, 2, 0, -3, '2020-01-08', ' <i>[König Lukáš->Novák Martin]</i> ', NULL, NULL, NULL, NULL, NULL),
(164, 2, 6, 0, 3, '2020-01-08', ' <i>[König Lukáš->Novák Martin]</i> ', NULL, NULL, NULL, NULL, NULL),
(165, 6, 4, 46, 50, '2020-10-20', '', NULL, NULL, NULL, NULL, NULL),
(166, 6, 6, 0, -34, '2020-10-20', ' <i>[Novák Martin->Bukovacová Alena]</i> ', NULL, NULL, NULL, NULL, NULL),
(167, 6, 25, 0, 34, '2020-10-20', ' <i>[Novák Martin->Bukovacová Alena]</i> ', NULL, NULL, NULL, NULL, NULL),
(168, 6, 4, 47, 44, '2020-10-20', '', NULL, NULL, NULL, NULL, NULL),
(169, 6, 5, 47, 55, '2020-10-20', '', NULL, NULL, NULL, NULL, NULL),
(170, 6, 4, 47, 44, '2020-10-20', '', NULL, NULL, NULL, NULL, NULL),
(171, 6, 5, 47, 55, '2020-10-20', '', NULL, NULL, NULL, NULL, NULL),
(172, 6, 4, 47, 44, '2020-10-20', '', NULL, NULL, NULL, NULL, NULL),
(173, 6, 5, 47, 55, '2020-10-20', '', NULL, NULL, NULL, NULL, NULL),
(174, 6, 4, 47, 44, '2020-10-20', '', NULL, NULL, NULL, NULL, NULL),
(175, 6, 5, 47, 55, '2020-10-20', '', NULL, NULL, NULL, NULL, NULL),
(176, 9, 21, 44, 100, '2023-11-18', 'Startovne', NULL, NULL, NULL, NULL, NULL),
(177, 6, 22, 41, 100, '2023-11-18', 'Cesta', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tst_finance_types`
--

CREATE TABLE `tst_finance_types` (
  `id` int(10) UNSIGNED NOT NULL,
  `nazev` varchar(50) NOT NULL,
  `popis` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_czech_ci;

--
-- Dumping data for table `tst_finance_types`
--

INSERT INTO `tst_finance_types` (`id`, `nazev`, `popis`) VALUES
(1, 'Základní platba', 'nikam nejezdím nebo málo'),
(2, 'Žabičky', 'JML'),
(3, 'Pulci', ''),
(4, 'Reprezentant ČR, licence A a E', ''),
(5, 'Licence R', ''),
(6, 'Licence B (žactvo + dorost)', ''),
(7, 'Ostatní = jezdím jen na Jihomoravskou ligu', ''),
(8, 'Ostatní = jezdím Jihomoravskou ligu a ŽB-M', ''),
(9, 'Ostatní = jezdím všude', '');

-- --------------------------------------------------------

--
-- Table structure for table `tst_modify_log`
--

CREATE TABLE `tst_modify_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `timestamp` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `action` enum('unknown','add','edit','delete') NOT NULL DEFAULT 'unknown',
  `table` varchar(20) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `author` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_czech_ci;

--
-- Dumping data for table `tst_modify_log`
--

INSERT INTO `tst_modify_log` (`id`, `timestamp`, `action`, `table`, `description`, `author`) VALUES
(1, 1289933547, 'add', 'tst_users', 'Richard Pátek [7609]', 1),
(2, 1289933580, 'add', 'tst_users', 'Lukáš König [8001]', 1),
(3, 1289933584, 'edit', 'tst_users', 'Richard Pátek [7609]', 1),
(4, 1289934520, 'add', 'tst_accounts', 'acc.id = 10 login = \"test_k\" [Kenia]', 1),
(5, 1289934537, 'add', 'tst_accounts', 'acc.id = 11 login = \"test_a\" [arnost]', 1),
(6, 1295343992, 'add', 'tst_users', 'Martin Veselý [7502]', 11),
(7, 1295344023, 'add', 'tst_accounts', 'acc.id = 12 login = \"test_v\" [Veseláček]', 11),
(8, 1295344047, 'edit', 'tst_accounts', 'acc.id = 12 login = \"test_v\" [Veseláček]', 1),
(9, 1327942602, 'add', 'tst_users', 'Jan Novák [9111]', 1),
(10, 1327942626, 'add', 'tst_users', 'Karel Novák [9312]', 1),
(11, 1327942656, 'add', 'tst_users', 'Martin Novák [9513]', 1),
(12, 1327942693, 'add', 'tst_users', 'Eva Nováková [9751]', 1),
(13, 1327942827, 'add', 'tst_accounts', 'acc.id = 13 login = \"tnov_1\" [tnov_1]', 1),
(14, 1327942849, 'add', 'tst_accounts', 'acc.id = 14 login = \"tnov_2\" [tnov_2]', 1),
(15, 1327942865, 'add', 'tst_accounts', 'acc.id = 15 login = \"tnov_3\" [tnov_3]', 1),
(16, 1327942891, 'add', 'tst_accounts', 'acc.id = 16 login = \"tnov_4\" [tnov_4]', 1),
(17, 1327942936, 'add', 'tst_users', 'Zuzana Nováková [9952]', 1),
(18, 1327942953, 'add', 'tst_accounts', 'acc.id = 17 login = \"tnov_5\" [tnov_5]', 1),
(19, 1327942997, 'add', 'tst_users', 'Jitka Nováková [8357]', 1),
(20, 1339023196, 'add', 'tst_users', 'Filil Mazaný [8801]', 1),
(21, 1339420963, 'add', 'tst_accounts', 'acc.id = 18 login = \"test_d1\" [Drbča]', 1),
(22, 1339420994, 'add', 'tst_accounts', 'acc.id = 19 login = \"test_h1\" [Hanka]', 1),
(23, 1339421206, 'add', 'tst_accounts', 'acc.id = 20 login = \"test_k1\" [Verča]', 1),
(24, 1339421245, 'add', 'tst_accounts', 'acc.id = 21 login = \"test_s1\" [Martin]', 1),
(25, 1339421292, 'add', 'tst_accounts', 'acc.id = 22 login = \"test_z1\" [Zhusta]', 1),
(26, 1339421316, 'add', 'tst_accounts', 'acc.id = 23 login = \"test_z2\" [Tomi]', 1),
(27, 1339421726, 'delete', 'tst_users', 'id = 19', 1),
(28, 1339421730, 'delete', 'tst_users', 'id = 20', 1),
(29, 1339421733, 'delete', 'tst_users', 'id = 24', 1),
(30, 1339445000, 'edit', 'tst_accounts', 'acc.id = 23 login = \"test_z2\" [Tomi]', 23),
(31, 1339445014, 'edit', 'tst_accounts', 'acc.id = 23 login = \"test_z3\" [Tomi1]', 23),
(32, 1339445055, 'edit', 'tst_accounts', 'acc.id = 23 login = \"test_z3\" [1Tomi1]', 23),
(33, 1339445081, 'edit', 'tst_accounts', 'acc.id = 23 login = \"1test_z3\" [Tomi1]', 23),
(34, 1339445414, 'edit', 'tst_accounts', 'acc.id = 23 login = \"cdfrt\" [Tomi1]', 23),
(35, 1339445419, 'edit', 'tst_accounts', 'acc.id = 23 login = \"1test_z3\" [Tomi1]', 23),
(36, 1339445535, 'edit', 'tst_accounts', 'acc.id = 23 login = \"test_z3\" [Tomi1]', 23),
(37, 1339493823, 'edit', 'tst_accounts', 'acc.id = 21 - pass', 1),
(38, 1339493887, 'edit', 'tst_accounts', 'acc.id = 21 login = \"test_s1\" [Martin]', 1),
(39, 1339493951, 'edit', 'tst_accounts', 'acc.id = 20 login = \"test_k1\" [Verča]', 1),
(40, 1339493959, 'edit', 'tst_accounts', 'acc.id = 20 - pass', 1),
(41, 1339498535, 'edit', 'tst_accounts', 'acc.id = 20 login = \"test_k1\" [Verča Verča]', 20),
(42, 1339498904, 'edit', 'tst_accounts', 'acc.id = 20 login = \"test_k1\" [Verča]', 20),
(43, 1339586830, 'edit', 'tst_accounts', 'acc.id = 19 - pass', 1),
(44, 1339590326, 'edit', 'tst_users', 'Hana Hlavová [8888]', 19),
(45, 1339590349, 'edit', 'tst_accounts', 'acc.id = 19 login = \"hanah\" [Hanka]', 19),
(46, 1339621838, 'edit', 'tst_accounts', 'acc.id = 10 login = \"test_k\" [Keniaa]', 10),
(47, 1339621865, 'edit', 'tst_accounts', 'acc.id = 10 login = \"test_k\" [Kenia]', 10),
(48, 1339621988, 'edit', 'tst_accounts', 'acc.id = 10 login = \"test_ka\" [Kenia]', 10),
(49, 1339622014, 'edit', 'tst_accounts', 'acc.id = 10 login = \"test_k\" [Kenia]', 10),
(50, 1339622045, 'edit', 'tst_accounts', 'acc.id = 10 - pass', 10),
(51, 1339622076, 'edit', 'tst_accounts', 'acc.id = 10 - pass', 10),
(52, 1339622089, 'edit', 'tst_accounts', 'acc.id = 10 login = \"test_kk\" [Kenia]', 10),
(53, 1339622093, 'edit', 'tst_accounts', 'acc.id = 10 login = \"test_k\" [Kenia]', 10),
(54, 1339622203, 'edit', 'tst_users', 'Lukáš König [8001]', 10),
(55, 1339622214, 'edit', 'tst_users', 'Lukáš König [8001]', 10),
(56, 1339622225, 'edit', 'tst_users', 'Lukáš König [8001]', 10),
(57, 1339622231, 'edit', 'tst_users', 'Lukáš König [8001]', 10),
(58, 1339622235, 'edit', 'tst_users', 'Lukáš König [8001]', 10),
(59, 1339622242, 'edit', 'tst_users', 'Lukáš König [8001]', 10),
(60, 1339622640, 'edit', 'tst_users', 'Lukáš König [8001]', 10),
(61, 1339622670, 'edit', 'tst_users', 'Lukáš König [8001]', 10),
(62, 1339624484, 'edit', 'tst_accounts', 'acc.id = 14 - lock (1)', 10),
(63, 1339624491, 'edit', 'tst_accounts', 'acc.id = 14 - lock (0)', 10),
(64, 1339624496, 'edit', 'tst_accounts', 'acc.id = 10 - lock (1)', 10),
(65, 1339624586, 'edit', 'tst_accounts', 'acc.id = 10 - lock (0)', 11),
(66, 1339624594, 'edit', 'tst_users', 'user.id = 2 - hide (1)', 11),
(67, 1339624594, 'edit', 'tst_accounts', 'acc.id = 10 - lock (1)', 11),
(68, 1339624629, 'edit', 'tst_users', 'user.id = 2 - hide (0)', 11),
(69, 1339624629, 'edit', 'tst_accounts', 'acc.id = 10 - lock (0)', 11),
(70, 1339624637, 'edit', 'tst_users', 'user.id = 2 - hide (1)', 11),
(71, 1339624637, 'edit', 'tst_accounts', 'acc.id = 10 - lock (1)', 11),
(72, 1339624822, 'edit', 'tst_users', 'user.id = 29 - hide (1)', 11),
(73, 1339624837, 'edit', 'tst_users', 'user.id = 2 - hide (0)', 11),
(74, 1339624837, 'edit', 'tst_accounts', 'acc.id = 10 - lock (0)', 11),
(75, 1339624848, 'edit', 'tst_users', 'user.id = 29 - hide (0)', 11),
(76, 1339806911, 'edit', 'tst_users', 'Lukáš König [8001]', 10),
(77, 1340112199, 'edit', 'tst_users', 'Hana Kočová [8676]', 21),
(78, 1340576247, 'edit', 'tst_accounts', 'acc.id = 23 - pass', 1),
(79, 1340614010, 'edit', 'tst_accounts', 'acc.id = 11 login = \"11test_a\" [arnost]', 11),
(80, 1340614019, 'edit', 'tst_accounts', 'acc.id = 11 login = \"test_a\" [arnost]', 11),
(81, 1342708090, 'edit', 'tst_accounts', 'acc.id = 20 - pass', 20),
(82, 1342708146, 'edit', 'tst_users', 'Veronika Křístková [8379]', 20),
(83, 1342709476, 'edit', 'tst_users', 'Alexandr Jevsejenko [8110]', 20),
(84, 1342709542, 'edit', 'tst_users', 'Jaroslav Koča [8200]', 20),
(85, 1358973306, 'edit', 'tst_users', 'Lukáš König [8001]', 10),
(86, 1358976836, 'edit', 'tst_users', 'user.id = 8 - hide (1)', 10),
(87, 1358976836, 'edit', 'tst_accounts', 'acc.id = 17 - lock (1)', 10),
(88, 1358976897, 'edit', 'tst_accounts', 'acc.id = 16 - lock (1)', 10),
(89, 1358976902, 'edit', 'tst_accounts', 'acc.id = 16 - lock (0)', 10),
(90, 1358978129, 'edit', 'tst_users', 'user.id = 2 - hide (1)', 10),
(91, 1358978129, 'edit', 'tst_accounts', 'acc.id = 10 - lock (1)', 10),
(92, 1358978284, 'edit', 'tst_users', 'user.id = 2 - hide (0)', 10),
(93, 1358978284, 'edit', 'tst_accounts', 'acc.id = 10 - lock (0)', 10),
(94, 1358978290, 'edit', 'tst_accounts', 'acc.id = 10 - lock (1)', 10),
(95, 1358978313, 'edit', 'tst_accounts', 'acc.id = 10 - lock (0)', 10),
(96, 1359376315, 'edit', 'tst_accounts', 'acc.id = 12 - pass', 12),
(97, 1359376503, 'edit', 'tst_users', 'user.id = 8 - hide (0)', 12),
(98, 1359376503, 'edit', 'tst_accounts', 'acc.id = 17 - lock (0)', 12),
(99, 1359376508, 'edit', 'tst_accounts', 'acc.id = 17 - lock (1)', 12),
(100, 1359620742, 'edit', 'tst_users', 'Martin Veselý [7502]', 12),
(101, 1359620895, 'edit', 'tst_users', 'Martin Veselý [7502]', 12),
(102, 1359624656, 'add', 'tst_users', 'Jiří Urválek [6107]', 1),
(103, 1359624701, 'add', 'tst_accounts', 'acc.id = 24 login = \"test_c1\" [Cvrcek]', 1),
(104, 1359635865, 'edit', 'tst_accounts', 'acc.id = 24 - pass', 1),
(105, 1359636459, 'edit', 'tst_users', 'user.id = 29 - hide (1)', 24),
(106, 1359636477, 'edit', 'tst_users', 'user.id = 29 - hide (0)', 24),
(107, 1359636520, 'edit', 'tst_accounts', 'acc.id = 22 - lock (1)', 24),
(108, 1359636525, 'edit', 'tst_accounts', 'acc.id = 22 - lock (0)', 24),
(109, 1359636529, 'edit', 'tst_users', 'user.id = 12 - hide (1)', 24),
(110, 1359636529, 'edit', 'tst_accounts', 'acc.id = 22 - lock (1)', 24),
(111, 1359636534, 'edit', 'tst_users', 'user.id = 12 - hide (0)', 24),
(112, 1359636534, 'edit', 'tst_accounts', 'acc.id = 22 - lock (0)', 24),
(113, 1360093952, 'edit', 'tst_accounts', 'acc.id = 12 login = \"test_v\" [Veseláček]', 1),
(114, 1360093958, 'edit', 'tst_accounts', 'acc.id = 11 login = \"test_a\" [arnost]', 1),
(115, 1360093969, 'edit', 'tst_accounts', 'acc.id = 10 login = \"test_k\" [Kenia]', 1),
(116, 1360093983, 'edit', 'tst_accounts', 'acc.id = 24 login = \"test_c1\" [Cvrcek]', 1),
(117, 1361814777, 'edit', 'tst_accounts', 'acc.id = 22 login = \"test_z1\" [Zhusta]', 1),
(118, 1363134063, 'edit', 'tst_users', 'user.id = 29 - hide (1)', 10),
(119, 1363134073, 'edit', 'tst_users', 'user.id = 29 - hide (0)', 10),
(120, 1363134088, 'edit', 'tst_users', 'user.id = 11 - hide (1)', 10),
(121, 1363134088, 'edit', 'tst_accounts', 'acc.id = 18 - lock (1)', 10),
(122, 1363134098, 'edit', 'tst_users', 'user.id = 11 - hide (0)', 10),
(123, 1363134098, 'edit', 'tst_accounts', 'acc.id = 18 - lock (0)', 10),
(124, 1363134112, 'edit', 'tst_users', 'user.id = 28 - hide (1)', 10),
(125, 1363134282, 'edit', 'tst_users', 'user.id = 4 - hide (1)', 10),
(126, 1363134282, 'edit', 'tst_accounts', 'acc.id = 13 - lock (1)', 10),
(127, 1363134304, 'edit', 'tst_accounts', 'acc.id = 19 - lock (1)', 10),
(128, 1363134322, 'edit', 'tst_accounts', 'acc.id = 13 - lock (0)', 10),
(129, 1363134356, 'edit', 'tst_users', 'user.id = 4 - hide (0)', 10),
(130, 1363134356, 'edit', 'tst_accounts', 'acc.id = 13 - lock (0)', 10),
(131, 1363134359, 'edit', 'tst_users', 'user.id = 28 - hide (0)', 10),
(132, 1363134366, 'edit', 'tst_accounts', 'acc.id = 19 - lock (0)', 10),
(133, 1363134371, 'edit', 'tst_accounts', 'acc.id = 17 - lock (0)', 10),
(134, 1366383192, 'add', 'tst_accounts', 'acc.id = 25 login = \"MB7605\" [Maroš]', 11),
(135, 1366383286, 'add', 'tst_accounts', 'acc.id = 26 login = \"PB8101\" [Palo]', 11),
(136, 1367166256, 'add', 'tst_users', 'Věra Mádlová [8951]', 1),
(137, 1367166361, 'add', 'tst_accounts', 'acc.id = 27 login = \"VM8951\" [Věra]', 1),
(138, 1367166373, 'edit', 'tst_accounts', 'acc.id = 27 login = \"VM8951\" [Věra]', 1),
(139, 1368537895, 'add', 'tst_finance', 'id=26|user_id=33|amount=-40', 27),
(140, 1368537907, 'edit', 'tst_finance', 'id=26|user_id=33|amount=-40|note=vkld', 27),
(141, 1368609991, 'add', 'tst_finance', 'id=27|user_id=29|amount=100', 10),
(142, 1368610004, 'edit', 'tst_finance', 'id=27|user_id=2|amount=100|note=sto podruhe', 10),
(143, 1368724164, 'edit', 'tst_finance', 'id=26|user_id=33|amount=-40|note=vkld', 27),
(144, 1368724207, 'edit', 'tst_finance', 'id=26|user_id=33|amount=|note=', 27),
(145, 1368724218, 'edit', 'tst_finance', 'id=26|user_id=33|amount=-40|note=vkld', 27),
(146, 1369739191, 'add', 'tst_finance', 'id=28|user_id=3|amount=200', 10),
(147, 1369739285, 'edit', 'tst_finance', 'id=28|user_id=2|amount=+?š??žýáíéú?|note=', 10),
(148, 1369739301, 'edit', 'tst_finance', 'id=28|user_id=2|amount=sto|note=', 10),
(149, 1369739341, 'edit', 'tst_finance', 'id=28|user_id=2|amount=sto|note=', 10),
(150, 1369739373, 'add', 'tst_finance', 'id=29|user_id=22|amount=100', 10),
(151, 1369739377, 'edit', 'tst_finance', 'id=28|user_id=2|amount=dveste|note=', 10),
(152, 1369739377, 'edit', 'tst_finance', 'id=29|user_id=2|amount=sto|note=', 10),
(153, 1369739385, 'edit', 'tst_finance', 'id=28|user_id=2|amount=sto|note=', 10),
(154, 1369739385, 'edit', 'tst_finance', 'id=29|user_id=2|amount=sto|note=', 10),
(155, 1369739435, 'edit', 'tst_finance', 'id=28|user_id=2|amount=100|note=sto', 10),
(156, 1369739450, 'edit', 'tst_finance', 'id=28|user_id=2|amount=dve|note=', 10),
(157, 1369739450, 'edit', 'tst_finance', 'id=29|user_id=2|amount=sto|note=', 10),
(158, 1369739532, 'add', 'tst_finance', 'id=30|user_id=8|amount=-500', 11),
(159, 1369739646, 'add', 'tst_finance', 'id=31|user_id=9|amount=200', 11),
(160, 1369739646, 'add', 'tst_finance', 'id=32|user_id=8|amount=300', 11),
(161, 1369739897, 'edit', 'tst_finance', 'id=28|user_id=2|amount=sto|note=', 10),
(162, 1369739897, 'edit', 'tst_finance', 'id=29|user_id=2|amount=sto|note=', 10),
(163, 1369739904, 'edit', 'tst_finance', 'id=28|user_id=2|amount=dve|note=', 10),
(164, 1369739904, 'edit', 'tst_finance', 'id=29|user_id=2|amount=sto|note=', 10),
(165, 1369741196, 'edit', 'tst_finance', 'id=36|user_id=2|amount=200|note=dve', 10),
(166, 1369741196, 'edit', 'tst_finance', 'id=36|user_id=2|amount=100|note=sto', 10),
(167, 1369741209, 'edit', 'tst_finance', 'id=28|user_id=2|amount=200|note=dve', 10),
(168, 1369741221, 'edit', 'tst_finance', 'id=36|user_id=2|amount=300|note=tri', 10),
(169, 1369741221, 'edit', 'tst_finance', 'id=36|user_id=2|amount=100|note=sto', 10),
(170, 1369741290, 'edit', 'tst_finance', 'id=36|user_id=2|amount=200|note=dve', 10),
(171, 1369741290, 'edit', 'tst_finance', 'id=36|user_id=2|amount=200|note=dve', 10),
(172, 1369741470, 'edit', 'tst_finance', 'id=28|user_id=2|amount=100|note=sto', 10),
(173, 1369741470, 'edit', 'tst_finance', 'id=29|user_id=2|amount=100|note=sto', 10),
(174, 1369741479, 'edit', 'tst_finance', 'id=28|user_id=2|amount=200|note=dve', 10),
(175, 1369741479, 'edit', 'tst_finance', 'id=29|user_id=2|amount=100|note=sto', 10),
(176, 1369741494, 'edit', 'tst_finance', 'id=28|user_id=2|amount=300|note=tri', 10),
(177, 1369741494, 'edit', 'tst_finance', 'id=29|user_id=2|amount=100|note=sto', 10),
(178, 1369741764, 'edit', 'tst_finance', 'id=28|user_id=2|amount=400|note=styri', 10),
(179, 1369741764, 'edit', 'tst_finance', 'id=29|user_id=2|amount=100|note=sto', 10),
(180, 1369742365, 'edit', 'tst_finance', 'id=28|user_id=2|amount=400|note=+??š?+?š??š?šžž?ýáýú?ú?', 10),
(181, 1369742365, 'edit', 'tst_finance', 'id=29|user_id=2|amount=100|note=sto', 10),
(182, 1387190044, 'add', 'tst_accounts', 'acc.id = 28 login = \"tnov_6\" [Jitka]', 1),
(183, 1387190861, 'edit', 'tst_accounts', 'acc.id = 28 login = \"tnov_6\" [tnov_6]', 1),
(184, 1391192876, 'add', 'tst_finance', 'id=33|user_id=13|amount=-40', 28),
(185, 1391192889, 'add', 'tst_finance', 'id=34|user_id=13|amount=40', 28),
(186, 1391537721, 'add', 'tst_finance', 'id=35|user_id=26|amount=200', 1),
(187, 1391537834, 'add', 'tst_finance', 'id=36|user_id=27|amount=300', 28),
(188, 1391537853, 'add', 'tst_finance', 'id=36|note=chybka', 28),
(189, 1391725840, 'add', 'tst_finance', 'id=37|user_id=4|amount=80', 28),
(190, 1391725840, 'add', 'tst_finance', 'id=38|user_id=5|amount=80', 28),
(191, 1391725840, 'add', 'tst_finance', 'id=39|user_id=6|amount=80', 28),
(192, 1391725840, 'add', 'tst_finance', 'id=40|user_id=7|amount=80', 28),
(193, 1391725840, 'add', 'tst_finance', 'id=41|user_id=8|amount=80', 28),
(194, 1391726066, 'add', 'tst_finance', 'id=42|user_id=4|amount=1000', 28),
(195, 1391782211, 'add', 'tst_finance', 'id=43|user_id=22|amount=100', 10),
(196, 1391782287, 'add', 'tst_finance', 'id=44|user_id=22|amount=-200', 10),
(197, 1391782303, 'add', 'tst_finance', 'id=45|user_id=22|amount=300', 10),
(198, 1391803186, 'edit', 'tst_finance', 'id=37|user_id=9|amount=80|note=', 28),
(199, 1391803186, 'edit', 'tst_finance', 'id=38|user_id=9|amount=80|note=', 28),
(200, 1391803186, 'edit', 'tst_finance', 'id=39|user_id=9|amount=80|note=', 28),
(201, 1391803186, 'edit', 'tst_finance', 'id=40|user_id=9|amount=80|note=', 28),
(202, 1391803186, 'edit', 'tst_finance', 'id=41|user_id=9|amount=80|note=', 28),
(203, 1391804991, 'edit', 'tst_finance', 'id=38|user_id=9|amount=80|note=', 28),
(204, 1391804991, 'edit', 'tst_finance', 'id=39|user_id=9|amount=80|note=', 28),
(205, 1391804991, 'edit', 'tst_finance', 'id=40|user_id=9|amount=80|note=', 28),
(206, 1391804991, 'edit', 'tst_finance', 'id=41|user_id=9|amount=80|note=', 28),
(207, 1391804997, 'edit', 'tst_finance', 'id=38|user_id=9|amount=80|note=', 28),
(208, 1391804997, 'edit', 'tst_finance', 'id=39|user_id=9|amount=80|note=', 28),
(209, 1391804997, 'edit', 'tst_finance', 'id=40|user_id=9|amount=80|note=', 28),
(210, 1391804997, 'edit', 'tst_finance', 'id=41|user_id=9|amount=80|note=', 28),
(211, 1391805011, 'edit', 'tst_finance', 'id=38|user_id=9|amount=80|note=', 28),
(212, 1391805011, 'edit', 'tst_finance', 'id=39|user_id=9|amount=80|note=', 28),
(213, 1391805011, 'edit', 'tst_finance', 'id=40|user_id=9|amount=80|note=', 28),
(214, 1391805011, 'edit', 'tst_finance', 'id=41|user_id=9|amount=80|note=', 28),
(215, 1391805028, 'edit', 'tst_finance', 'id=38|user_id=9|amount=80|note=', 28),
(216, 1391805028, 'edit', 'tst_finance', 'id=39|user_id=9|amount=80|note=', 28),
(217, 1391805028, 'edit', 'tst_finance', 'id=40|user_id=9|amount=80|note=', 28),
(218, 1391805028, 'edit', 'tst_finance', 'id=41|user_id=9|amount=80|note=', 28),
(219, 1391805035, 'edit', 'tst_finance', 'id=37|user_id=9|amount=80|note=', 28),
(220, 1391805035, 'edit', 'tst_finance', 'id=38|user_id=9|amount=80|note=', 28),
(221, 1391805035, 'edit', 'tst_finance', 'id=39|user_id=9|amount=80|note=', 28),
(222, 1391805035, 'edit', 'tst_finance', 'id=40|user_id=9|amount=80|note=', 28),
(223, 1391805035, 'edit', 'tst_finance', 'id=41|user_id=9|amount=80|note=', 28),
(224, 1391805064, 'edit', 'tst_finance', 'id=21|user_id=9|amount=0|note=závod Jml', 28),
(225, 1392143449, 'add', 'tst_finance', 'id=46|user_id=4|amount=80', 28),
(226, 1392143449, 'add', 'tst_finance', 'id=47|user_id=5|amount=80', 28),
(227, 1392143449, 'add', 'tst_finance', 'id=48|user_id=6|amount=80', 28),
(228, 1392143449, 'add', 'tst_finance', 'id=49|user_id=7|amount=80', 28),
(229, 1392143449, 'add', 'tst_finance', 'id=50|user_id=8|amount=80', 28),
(230, 1392143456, 'add', 'tst_finance', 'id=51|user_id=4|amount=80', 28),
(231, 1392143456, 'add', 'tst_finance', 'id=52|user_id=5|amount=80', 28),
(232, 1392143456, 'add', 'tst_finance', 'id=53|user_id=6|amount=80', 28),
(233, 1392143456, 'add', 'tst_finance', 'id=54|user_id=7|amount=80', 28),
(234, 1392143456, 'add', 'tst_finance', 'id=55|user_id=8|amount=80', 28),
(235, 1392143502, 'add', 'tst_finance', 'id=56|user_id=4|amount=80', 28),
(236, 1392143502, 'add', 'tst_finance', 'id=57|user_id=5|amount=80', 28),
(237, 1392143502, 'add', 'tst_finance', 'id=58|user_id=6|amount=80', 28),
(238, 1392143502, 'add', 'tst_finance', 'id=59|user_id=7|amount=80', 28),
(239, 1392143502, 'add', 'tst_finance', 'id=60|user_id=8|amount=80', 28),
(240, 1392143529, 'add', 'tst_finance', 'id=51|note=', 28),
(241, 1393332212, 'edit', 'tst_users', 'Karel Novák [9312] - entry lock (1)', 27),
(242, 1393332216, 'edit', 'tst_users', 'Karel Novák [9312] - entry lock (0)', 27),
(243, 1393332221, 'edit', 'tst_users', 'Věra Mádlová [8951] - entry lock (1)', 27),
(244, 1393332230, 'edit', 'tst_users', 'Věra Mádlová [8951] - entry lock (0)', 27),
(245, 1393449298, 'add', 'tst_finance', 'id=61|user_id=7|amount=-400', 16),
(246, 1393449298, 'add', 'tst_finance', 'id=62|user_id=9|amount=400', 16),
(247, 1393449318, 'add', 'tst_finance', 'id=63|user_id=5|amount=-400', 16),
(248, 1393449318, 'add', 'tst_finance', 'id=64|user_id=8|amount=400', 16),
(249, 1393495022, 'add', 'tst_finance', 'id=65|user_id=4|amount=-4000', 28),
(250, 1393498007, 'add', 'tst_finance', 'id=66|user_id=13|amount=-100', 10),
(251, 1393498007, 'add', 'tst_finance', 'id=67|user_id=11|amount=100', 10),
(252, 1393498069, 'add', 'tst_finance', 'id=68|user_id=11|amount=-200', 10),
(253, 1393498069, 'add', 'tst_finance', 'id=69|user_id=13|amount=200', 10),
(254, 1393777075, 'add', 'tst_finance', 'id=70|user_id=4|amount=-80', 28),
(255, 1393777075, 'add', 'tst_finance', 'id=71|user_id=5|amount=-80', 28),
(256, 1393777112, 'edit', 'tst_finance', 'id=70|user_id=9|amount=-80|note=', 28),
(257, 1393777112, 'edit', 'tst_finance', 'id=71|user_id=9|amount=-80|note=', 28),
(258, 1393777112, 'add', 'tst_finance', 'id=72|user_id=6|amount=-80', 28),
(259, 1393777112, 'add', 'tst_finance', 'id=73|user_id=7|amount=-80', 28),
(260, 1393777112, 'add', 'tst_finance', 'id=74|user_id=8|amount=-80', 28),
(261, 1393778030, 'add', 'tst_users', 'Michal Gross [6946]', 15),
(262, 1393778159, 'add', 'tst_accounts', 'acc.id = 29 login = \"majkl\" [majkl]', 15),
(263, 1398782135, 'add', 'tst_finance', 'id=75|user_id=1|amount=100', 10),
(264, 1398782139, 'edit', 'tst_finance', 'id=75|user_id=2|amount=1000|note=', 10),
(265, 1420830069, 'edit', 'tst_accounts', 'acc.id = 19 - pass', 10),
(266, 1422644190, 'edit', 'tst_finance', 'id=67|user_id=2|amount=1000|note=', 10),
(267, 1422644208, 'edit', 'tst_users', 'Dušan Bukovac [7503] - entry lock (1)', 10),
(268, 1422644227, 'edit', 'tst_users', 'Maroš Bukovac [7605] - entry lock (1)', 10),
(269, 1422644230, 'edit', 'tst_users', 'Dušan Bukovac [7503] - entry lock (0)', 10),
(270, 1422644233, 'edit', 'tst_users', 'Maroš Bukovac [7605] - entry lock (0)', 10),
(271, 1422644457, 'edit', 'tst_users', 'Richard Pátek [7609] - entry lock (1)', 10),
(272, 1422644464, 'edit', 'tst_users', 'Richard Pátek [7609] - entry lock (0)', 10),
(273, 1422644468, 'edit', 'tst_users', 'Richard Pátek [7609] - entry lock (1)', 10),
(274, 1422644611, 'edit', 'tst_users', 'Richard Pátek [7609] - entry lock (0)', 10),
(275, 1424102245, 'add', 'tst_finance', 'id=0|user_id=2|amount=-12', 10),
(276, 1424102245, 'add', 'tst_finance', 'id=275|user_id=22|amount=12', 10),
(277, 1424102259, 'add', 'tst_finance', 'id=76|user_id=2|amount=-12', 10),
(278, 1424102259, 'add', 'tst_finance', 'id=77|user_id=22|amount=12', 10),
(279, 1424102267, 'add', 'tst_finance', 'id=0|user_id=2|amount=-23', 10),
(280, 1424102267, 'add', 'tst_finance', 'id=279|user_id=22|amount=23', 10),
(281, 1424102311, 'add', 'tst_finance', 'id=0|user_id=2|amount=-456', 10),
(282, 1424102311, 'add', 'tst_finance', 'id=281|user_id=22|amount=456', 10),
(283, 1424102315, 'add', 'tst_finance', 'id=0|user_id=2|amount=-456', 10),
(284, 1424102315, 'add', 'tst_finance', 'id=283|user_id=22|amount=456', 10),
(285, 1424102565, 'add', 'tst_finance', 'id=0|user_id=23|amount=100', 11),
(286, 1424102581, 'add', 'tst_finance', 'id=78|user_id=23|amount=100', 11),
(287, 1424102800, 'add', 'tst_finance', 'id=0|user_id=21|amount=200', 11),
(288, 1424103042, 'add', 'tst_users', 'asdfasd \'adsfdas [0011]', 10),
(289, 1424103056, 'edit', 'tst_users', 'asdfasd ¨\\\'adsfdas [0011]', 10),
(290, 1424103075, 'edit', 'tst_users', 'asdfasd adsfd\'as\'\\dasf?> [0011]', 10),
(291, 1424103082, 'edit', 'tst_users', 'asdfasd adsfd\'as\'\\dasf?> [0011]', 10),
(292, 1424103088, 'edit', 'tst_users', 'asdfasd  [0011]', 10),
(293, 1424103107, 'delete', 'tst_users', 'id = 35', 10),
(294, 1424103163, 'add', 'tst_finance', 'id=0|user_id=29|amount=-12', 10),
(295, 1424103258, 'add', 'tst_finance', 'id=0|user_id=2|amount=6', 10),
(296, 1424103258, 'add', 'tst_finance', 'id=295|user_id=16|amount=6', 10),
(297, 1424103258, 'add', 'tst_finance', 'id=296|user_id=17|amount=6', 10),
(298, 1424103258, 'edit', 'tst_finance', 'id=10|user_id=2|amount=300|note=trista', 10),
(299, 1424103260, 'add', 'tst_finance', 'id=0|user_id=2|amount=6', 10),
(300, 1424103260, 'add', 'tst_finance', 'id=299|user_id=16|amount=6', 10),
(301, 1424103260, 'add', 'tst_finance', 'id=300|user_id=17|amount=6', 10),
(302, 1424103260, 'edit', 'tst_finance', 'id=10|user_id=2|amount=300|note=trista', 10),
(303, 1424174537, 'add', 'tst_finance', 'id=79|user_id=22|amount=100', 1),
(304, 1425430171, 'add', 'tst_users', 'Řehoř Štěrbák [8006]', 1),
(305, 1425430183, 'add', 'tst_accounts', 'acc.id = 30 login = \"RS8006\" [Řehoř]', 1),
(306, 1455563721, 'add', 'tst_finance', 'id=80|user_id=14|amount=500', 28),
(307, 1455563753, 'add', 'tst_finance', 'id=81|user_id=14|amount=-30', 28),
(308, 1455563753, 'add', 'tst_finance', 'id=82|user_id=13|amount=-500', 28),
(309, 1455563753, 'add', 'tst_finance', 'id=83|user_id=33|amount=-13', 28),
(310, 1455563808, 'add', 'tst_finance', 'id=0|user_id=|amount=-200', 28),
(311, 1455563808, 'add', 'tst_finance', 'id=310|user_id=13|amount=200', 28),
(312, 1455563839, 'add', 'tst_finance', 'id=0|user_id=|amount=-300', 28),
(313, 1455563839, 'add', 'tst_finance', 'id=312|user_id=13|amount=300', 28),
(314, 1455563849, 'add', 'tst_finance', 'id=0|user_id=|amount=-34', 28),
(315, 1455563849, 'add', 'tst_finance', 'id=314|user_id=21|amount=34', 28),
(316, 1455563902, 'add', 'tst_finance', 'id=84|user_id=9|amount=-20', 28),
(317, 1455563902, 'add', 'tst_finance', 'id=85|user_id=10|amount=20', 28),
(318, 1455563944, 'add', 'tst_finance', 'id=0|user_id=|amount=-100', 28),
(319, 1455563944, 'add', 'tst_finance', 'id=318|user_id=28|amount=100', 28),
(320, 1455979543, 'add', 'tst_finance', 'id=86|user_id=1|amount=-200', 28),
(321, 1455979543, 'add', 'tst_finance', 'id=87|user_id=9|amount=200', 28),
(322, 1462959637, 'edit', 'tst_users', 'user.id = 11 - hide (1)', 15),
(323, 1462959637, 'edit', 'tst_accounts', 'acc.id = 18 - lock (1)', 15),
(324, 1462959645, 'edit', 'tst_users', 'user.id = 11 - hide (0)', 15),
(325, 1462959645, 'edit', 'tst_accounts', 'acc.id = 18 - lock (0)', 15),
(326, 1462959649, 'edit', 'tst_accounts', 'acc.id = 18 - lock (1)', 15),
(327, 1462959653, 'edit', 'tst_users', 'user.id = 11 - hide (1)', 15),
(328, 1462959653, 'edit', 'tst_accounts', 'acc.id = 18 - lock (1)', 15),
(329, 1462959657, 'edit', 'tst_users', 'user.id = 11 - hide (0)', 15),
(330, 1462959657, 'edit', 'tst_accounts', 'acc.id = 18 - lock (0)', 15),
(331, 1462960041, 'add', 'tst_finance', 'id=88|user_id=22|amount=-1000', 28),
(332, 1462960041, 'add', 'tst_finance', 'id=89|user_id=32|amount=1000', 28),
(333, 1462960072, 'add', 'tst_finance', 'id=88|note=', 28),
(334, 1488208658, 'add', 'tst_finance', 'id=90|user_id=7|amount=3150', 10),
(335, 1488208717, 'add', 'tst_finance', 'id=91|user_id=8|amount=840', 10),
(336, 1488208728, 'edit', 'tst_finance', 'id=91|user_id=2|amount=840|note=', 10),
(337, 1488208769, 'edit', 'tst_finance', 'id=91|user_id=2|amount=-840|note=', 10),
(338, 1488208802, 'add', 'tst_finance', 'id=92|user_id=8|amount=-1820', 10),
(339, 1488208830, 'edit', 'tst_finance', 'id=90|user_id=2|amount=2000|note=', 10),
(340, 1488208999, 'add', 'tst_finance', 'id=93|user_id=4|amount=2840', 10),
(341, 1488209008, 'add', 'tst_finance', 'id=94|user_id=5|amount=-150', 10),
(342, 1488209016, 'add', 'tst_finance', 'id=95|user_id=5|amount=300', 10),
(343, 1488209027, 'add', 'tst_finance', 'id=96|user_id=6|amount=-170', 10),
(344, 1488209486, 'add', 'tst_finance', 'id=97|user_id=9|amount=-780', 10),
(345, 1488209539, 'add', 'tst_finance', 'id=98|user_id=7|amount=-20', 10),
(346, 1488209758, 'add', 'tst_finance', 'id=99|user_id=7|amount=-1819', 16),
(347, 1488209759, 'add', 'tst_finance', 'id=100|user_id=8|amount=1819', 16),
(348, 1488209854, 'add', 'tst_finance', 'id=101|user_id=7|amount=1819', 10),
(349, 1488209868, 'add', 'tst_finance', 'id=102|user_id=8|amount=-1819', 10),
(350, 1488210089, 'add', 'tst_finance', 'id=103|user_id=8|amount=1820', 11),
(351, 1488210100, 'add', 'tst_finance', 'id=104|user_id=5|amount=-1820', 11),
(352, 1488210444, 'add', 'tst_finance', 'id=105|user_id=5|amount=-10', 10),
(353, 1488210610, 'edit', 'tst_finance', 'id=105|user_id=2|amount=0|note=', 10),
(354, 1510648905, 'edit', 'tst_finance', 'id=9|user_id=9|amount=-100|note=stovka', 28),
(355, 1510648905, 'edit', 'tst_finance', 'id=1|user_id=9|amount=-5000|note=', 28),
(356, 1518180051, 'edit', 'tst_accounts', 'acc.id = 10 - pass', 15),
(357, 1518180122, 'add', 'tst_finance', 'id=106|user_id=8|amount=10000', 10),
(358, 1518180132, 'add', 'tst_finance', 'id=107|user_id=9|amount=10000', 10),
(359, 1518180142, 'add', 'tst_finance', 'id=108|user_id=7|amount=10000', 10),
(360, 1518180179, 'add', 'tst_finance', 'id=0|user_id=|amount=-2', 16),
(361, 1518180179, 'add', 'tst_finance', 'id=360|user_id=5|amount=2', 16),
(362, 1518180319, 'add', 'tst_finance', 'id=0|user_id=|amount=-2', 16),
(363, 1518180319, 'add', 'tst_finance', 'id=362|user_id=5|amount=2', 16),
(364, 1518180325, 'add', 'tst_finance', 'id=0|user_id=|amount=-2', 16),
(365, 1518180325, 'add', 'tst_finance', 'id=364|user_id=5|amount=2', 16),
(366, 1518443815, 'add', 'tst_finance', 'id=0|user_id=|amount=-2', 16),
(367, 1518443815, 'add', 'tst_finance', 'id=366|user_id=5|amount=2', 16),
(368, 1520712175, 'add', 'tst_users', 'Osvald Mocvesely [4400]', 1),
(369, 1520712193, 'edit', 'tst_users', 'Osvald Mocvesely [4400] - entry lock (1)', 1),
(370, 1520712235, 'add', 'tst_finance', 'id=109|user_id=37|amount=-500', 1),
(371, 1520712391, 'add', 'tst_finance', 'id=110|user_id=37|amount=-500', 28),
(372, 1520712416, 'add', 'tst_finance', 'id=111|user_id=37|amount=3000', 28),
(373, 1520712428, 'add', 'tst_finance', 'id=112|user_id=37|amount=-150', 28),
(374, 1520712442, 'add', 'tst_finance', 'id=113|user_id=37|amount=-300', 28),
(375, 1520712442, 'add', 'tst_finance', 'id=114|user_id=34|amount=300', 28),
(376, 1520713908, 'add', 'tst_finance', 'id=115|user_id=37|amount=-200', 28),
(377, 1520713908, 'add', 'tst_finance', 'id=116|user_id=28|amount=200', 28),
(378, 1520716394, 'add', 'tst_finance', 'id=117|user_id=7|amount=-1', 10),
(379, 1520716394, 'add', 'tst_finance', 'id=118|user_id=22|amount=1', 10),
(380, 1520716418, 'add', 'tst_finance', 'id=119|user_id=2|amount=-2', 10),
(381, 1520716418, 'add', 'tst_finance', 'id=120|user_id=22|amount=2', 10),
(382, 1520716549, 'add', 'tst_finance', 'id=121|user_id=2|amount=-3', 10),
(383, 1520716549, 'add', 'tst_finance', 'id=122|user_id=23|amount=3', 10),
(384, 1520934283, 'add', 'tst_finance', 'id=123|user_id=2|amount=-1', 10),
(385, 1520934283, 'add', 'tst_finance', 'id=124|user_id=22|amount=1', 10),
(386, 1520934357, 'add', 'tst_finance', 'id=125|user_id=22|amount=-2', 10),
(387, 1520934357, 'add', 'tst_finance', 'id=126|user_id=2|amount=2', 10),
(388, 1520934682, 'add', 'tst_finance', 'id=127|user_id=22|amount=-2', 10),
(389, 1520934682, 'add', 'tst_finance', 'id=128|user_id=2|amount=2', 10),
(390, 1521221577, 'add', 'tst_finance', 'id=129|user_id=2|amount=-1', 10),
(391, 1521221578, 'add', 'tst_finance', 'id=130|user_id=22|amount=1', 10),
(392, 1521221590, 'add', 'tst_finance', 'id=131|user_id=2|amount=-2', 10),
(393, 1521221590, 'add', 'tst_finance', 'id=132|user_id=7|amount=2', 10),
(394, 1521221627, 'add', 'tst_finance', 'id=133|user_id=7|amount=-1', 10),
(395, 1521221627, 'add', 'tst_finance', 'id=134|user_id=22|amount=1', 10),
(396, 1521221665, 'add', 'tst_finance', 'id=135|user_id=7|amount=-2', 10),
(397, 1521221665, 'add', 'tst_finance', 'id=136|user_id=5|amount=2', 10),
(398, 1521221695, 'add', 'tst_finance', 'id=137|user_id=7|amount=-3', 10),
(399, 1521221695, 'add', 'tst_finance', 'id=138|user_id=5|amount=3', 10),
(400, 1521721796, 'add', 'tst_finance', 'id=139|user_id=2|amount=-12', 10),
(401, 1521721796, 'add', 'tst_finance', 'id=140|user_id=22|amount=12', 10),
(402, 1521721816, 'add', 'tst_finance', 'id=141|user_id=2|amount=-13', 10),
(403, 1521721816, 'add', 'tst_finance', 'id=142|user_id=7|amount=13', 10),
(404, 1521721917, 'add', 'tst_finance', 'id=143|user_id=2|amount=-300', 10),
(405, 1521721917, 'add', 'tst_finance', 'id=144|user_id=6|amount=300', 10),
(406, 1521721927, 'add', 'tst_finance', 'id=145|user_id=2|amount=-300', 10),
(407, 1521721928, 'add', 'tst_finance', 'id=146|user_id=7|amount=300', 10),
(408, 1521721965, 'add', 'tst_finance', 'id=147|user_id=6|amount=-1', 15),
(409, 1521721965, 'add', 'tst_finance', 'id=148|user_id=2|amount=1', 15),
(410, 1521721984, 'add', 'tst_finance', 'id=149|user_id=6|amount=-2', 15),
(411, 1521721984, 'add', 'tst_finance', 'id=150|user_id=7|amount=2', 15),
(412, 1521722027, 'add', 'tst_finance', 'id=151|user_id=7|amount=-5', 16),
(413, 1521722027, 'add', 'tst_finance', 'id=152|user_id=6|amount=5', 16),
(414, 1521722067, 'add', 'tst_finance', 'id=153|user_id=7|amount=-6', 16),
(415, 1521722067, 'add', 'tst_finance', 'id=154|user_id=2|amount=6', 16),
(416, 1535481765, 'edit', 'tst_users', 'Maroš Bukovac [7605] - entry lock (1)', 28),
(417, 1535481786, 'add', 'tst_finance', 'id=155|user_id=21|amount=-50', 28),
(418, 1535481786, 'add', 'tst_finance', 'id=156|user_id=11|amount=-100', 28),
(419, 1578498014, 'add', 'tst_finance', 'id=157|user_id=2|amount=10000', 10),
(420, 1578498030, 'add', 'tst_finance', 'id=158|user_id=2|amount=-1000', 10),
(421, 1578498138, 'add', 'tst_finance', 'id=159|user_id=2|amount=-1', 10),
(422, 1578498138, 'add', 'tst_finance', 'id=160|user_id=22|amount=1', 10),
(423, 1578498143, 'add', 'tst_finance', 'id=161|user_id=2|amount=-2', 10),
(424, 1578498143, 'add', 'tst_finance', 'id=162|user_id=22|amount=2', 10),
(425, 1578498149, 'add', 'tst_finance', 'id=163|user_id=2|amount=-3', 10),
(426, 1578498149, 'add', 'tst_finance', 'id=164|user_id=6|amount=3', 10),
(427, 1603201124, 'edit', 'tst_users', 'user.id = 37 - hide (1)', 15),
(428, 1603201130, 'edit', 'tst_users', 'user.id = 37 - hide (0)', 15),
(429, 1603201473, 'edit', 'tst_accounts', 'acc.id = 15 login = \"tnov_3\" [tnov_3]', 15),
(430, 1603204414, 'add', 'tst_finance', 'id=165|user_id=4|amount=50', 15),
(431, 1603205168, 'edit', 'tst_finance', 'id=10|user_id=6|amount=300|note=trista', 15),
(432, 1603205183, 'edit', 'tst_finance', 'id=10|user_id=6|amount=0|note=trista', 15),
(433, 1603205199, 'edit', 'tst_finance', 'id=10|user_id=6|amount=0|note=trista', 15),
(434, 1603205268, 'edit', 'tst_users', 'Dušan Bukovac [7503] - entry lock (1)', 15),
(435, 1603214270, 'edit', 'tst_users', 'Hana Hlavová [8888] - entry lock (1)', 15),
(436, 1603215016, 'edit', 'tst_users', 'Palo Bukovac [8101] - entry lock (1)', 15),
(437, 1603215024, 'edit', 'tst_users', 'user.id = 22 - hide (1)', 15),
(438, 1603215031, 'edit', 'tst_users', 'user.id = 22 - hide (0)', 15),
(439, 1603215364, 'edit', 'tst_users', 'Alena Bukovacová [7454] - entry lock (1)', 15),
(440, 1603215432, 'add', 'tst_finance', 'id=166|user_id=6|amount=-34', 15),
(441, 1603215432, 'add', 'tst_finance', 'id=167|user_id=25|amount=34', 15),
(442, 1603215471, 'add', 'tst_finance', 'id=168|user_id=4|amount=44', 15),
(443, 1603215471, 'add', 'tst_finance', 'id=169|user_id=5|amount=55', 15),
(444, 1603215482, 'add', 'tst_finance', 'id=170|user_id=4|amount=44', 15),
(445, 1603215482, 'add', 'tst_finance', 'id=171|user_id=5|amount=55', 15),
(446, 1603215511, 'add', 'tst_finance', 'id=172|user_id=4|amount=44', 15),
(447, 1603215511, 'add', 'tst_finance', 'id=173|user_id=5|amount=55', 15),
(448, 1603215515, 'add', 'tst_finance', 'id=174|user_id=4|amount=44', 15),
(449, 1603215515, 'add', 'tst_finance', 'id=175|user_id=5|amount=55', 15),
(450, 1671476006, 'add', 'tst_users', 'Petr Matula [8202]', 15),
(451, 1671476334, 'edit', 'tst_users', 'Petr Matula [8202] - entry lock (1)', 15),
(452, 1671476353, 'edit', 'tst_users', 'Petr Matula [8202] - entry lock (0)', 15),
(453, 1671476363, 'edit', 'tst_users', 'Petr Matula [8202] - entry lock (1)', 15),
(454, 1671476495, 'edit', 'tst_users', 'Petr Matula [8202] - entry lock (0)', 15),
(455, 1671476645, 'edit', 'tst_users', 'Petr Matula [8202] - entry lock (1)', 15),
(456, 1671476649, 'edit', 'tst_users', 'Petr Matula [8202] - entry lock (0)', 15),
(457, 1700315171, 'add', 'tst_finance', 'id=176|user_id=21|amount=100', 28),
(458, 1700315793, 'edit', 'tst_finance', 'id=37|user_id=9|amount=150|note=Startovne', 28),
(459, 1700315793, 'edit', 'tst_finance', 'id=38|user_id=9|amount=150|note=Startovne', 28),
(460, 1700315793, 'edit', 'tst_finance', 'id=39|user_id=9|amount=150|note=Startovne', 28),
(461, 1700315793, 'edit', 'tst_finance', 'id=40|user_id=9|amount=80|note=', 28),
(462, 1700315793, 'edit', 'tst_finance', 'id=41|user_id=9|amount=80|note=', 28),
(463, 1700317985, 'add', 'tst_finance', 'id=177|user_id=22|amount=100', 15);

-- --------------------------------------------------------

--
-- Table structure for table `tst_news`
--

CREATE TABLE `tst_news` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_user` smallint(5) UNSIGNED DEFAULT NULL,
  `datum` int(11) NOT NULL DEFAULT 0,
  `nadpis` varchar(50) NOT NULL DEFAULT '',
  `text` longtext NOT NULL,
  `internal` tinyint(1) NOT NULL DEFAULT 0,
  `modify_flag` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_czech_ci COMMENT='novinky';

--
-- Dumping data for table `tst_news`
--

INSERT INTO `tst_news` (`id`, `id_user`, `datum`, `nadpis`, `text`, `internal`, `modify_flag`) VALUES
(7, 11, 1295305200, '', 'Update na RC2.', 0, 0),
(5, 11, 1292281200, '', 'Spuštěn test cronu.', 0, 0),
(6, 11, 1294268400, '', 'Update na RC1.', 0, 0),
(8, 1, 1327878000, 'Nová doba', 'První RC verze 2.1 byl nasazen pro testovani. Konkrétně jde o build 234. Slouží k ověření  funkčnosti systému, zatím bez financí.\r\n', 0, 0),
(9, 11, 1327878000, '', 'Update na build 236.', 0, 0),
(10, 11, 1327878000, '', 'Update na build 238.', 0, 0),
(11, 1, 1327878000, '', 'Update na build 239.', 0, 0),
(12, 1, 1338760800, 'Testování začíná', 'Druhá RC verze 2.1 byla nasazena pro testovani. Konkrétně jde o build 261. Slouží k ověření funkčnosti modernizace systému, pořád bez financí. Finance nejspíš budou až v další větší verzi.', 0, 0),
(13, 1, 1338847200, '', 'Update na build 262.', 0, 0),
(14, 1, 1338847200, 'Seznam změn', '<ul>\r\n<li>modifikace vzhledu cele aplikace\r\n<li>zruseno omezeni na jeden termin prihlasek / nyni jednodenni i vicedenni maji stejne moznosti (az 5 terminu)\r\n<li>do seznamu zavodu pridana rozsahlejsi moznost filtrace\r\n<li>moznost vytvaret zjednodusene prihlasky clenu (jen generovani formatu prihlasek bez zapisu do db.)\r\n<li>pri vytvareni noveho clena pridana moznost pro hledani volnych reg. cisel\r\n<li>upozornovani ne email o terminech prihlasek\r\n<li>Pokud je prihlasen uzivatel, uz se nezobrazuje oddilova terminovka samostatne.\r\n<li>prihlasovatel - odhlasovani prevedeno do hromadne prihlasky.\r\n<li>upravena moznost zmenit SI cip pro zavod i zavodnikovi s vlastnim cipem.\r\n<li>spousta drobnych oprav / uprav / vylepseni\r\n</ul>', 0, 0),
(15, 1, 1339365600, '', 'Testeři vpuštěni :)', 0, 0),
(17, 23, 1339365600, 'mam pravo na pridávanie noviniek?  ', '<B>Mám</B> normalniho <U>uzivatele</U> + maleho trenera (2 nahodni sverenci). \r\n', 0, 0),
(19, 1, 1339452000, 'Upozornovani', 'Tak jsem konecne zapnul spravne upozornovani na email.', 0, 0),
(22, 19, 1339538400, 'Ahoj', 'Tak už jsem se taky pustila do testování:)', 0, 0),
(30, 21, 1340056800, 'Ahoj', 'Taky <B>testuju</B> !', 0, 0),
(32, 18, 1340143200, 'Zdar jak sviňa', 'Co <B> ta šipka </B> na liště v <B><U>aktualitách.</U> </B> Nezmátla <U>vás</U>? ;-)<B>tak já tedy edituji</B>', 0, 0),
(33, 20, 1342648800, 'testujeme, ale...', '...ono se to postupně mění! takže testujeme vkládání novinek ještě jednou...i tučné písmo...i podtržené<U><B>', 0, 0),
(34, 20, 1342648800, '', 'tak ještě jednou <B>tučné</B> a <U>podtržené</U>', 0, 0),
(35, 1, 1358809200, '', 'Update na RC3 build 291.', 0, 0),
(36, 1, 1358809200, 'Seznam změn ', '<ul>\r\n<li>různé menší změny a sjednocování vzhledu\r\n<li>přidána možnost editace novinky\r\n<li>několik jednotících úprav u malého trenéra\r\n<li>odkaz na seznam přihlášených již není pod ikonou typu závodu, pokus o sjednocení v rámci různých typů zobrazení\r\n<li>v aktualitkách přidána možnost přihlašování\r\n<li>další opravy dle nahlášených do mantisu.\r\n<li>drobné opravy a úpravy nahlášené při testování\r\n</ul>', 0, 0),
(38, 10, 1358895600, 'test', 'test2', 0, 0),
(41, 1, 1358982000, '', 'Update na build 292. (Práva u editace novinky)', 0, 0),
(42, 1, 1359241200, '', 'Update na RC4 build 296.', 0, 0),
(43, 1, 1359241200, '	Seznam změn ', '<ul>\r\n<li>pridani bublinkove napovedy v hlavickach tabulek\r\n<li>odstraneno zobrazeni uzivateli, ze ma povoleno psani novinek\r\n<li>uprava menu spravce, slouceno do jednoho a znemozneno editovat sebe samu\r\n<li>zrusen starsi format prihlasky\r\n<li>drobne graficke upravy v upozornovani\r\n</ul>', 0, 0),
(44, 1, 1359327600, '', 'Update na build 302. Vylepseni dialogu pro upozornovani emailem.', 0, 0),
(49, 12, 1359500400, 'Tak trochu test', 'Trochu <B>testu</B> Blabla', 0, 0),
(47, 1, 1359327600, '', 'Update na build 303. Upravy barvy zvyrazneni v tabulce.', 0, 0),
(48, 1, 1359327600, '', 'Update na build 304. Opravena funkncnost Upozornovani pod IE.', 0, 0),
(51, 1, 1360018800, '', 'Update na build 312. Přidáno testování základního nástřelu financí.', 0, 0),
(53, 1, 1360105200, '', 'Update na build 328.', 0, 0),
(54, 1, 1360623600, '', 'Update na build 341. Změny ohledně financí.', 0, 0),
(55, 1, 1361746800, '', 'Update na build 355.', 0, 0),
(56, 1, 1361746800, '', 'Update na build 356.', 0, 0),
(57, 1, 1361746800, 'Seznam změn ', '<ul>\r\n<li>Upraveno zobrazení informací o závodě, většinou se teď zobrazuje minimální info s možností zobrazit celé info.\r\n<li>Zobrazení přehledu financí pro oba typy trenerů.\r\n<li>Různé úpravy vzhledu u financí. A to jak zobrazení, tak i formulářů.\r\n<li>Zakladní funkční platby pro závody.\r\n<li>Oprava generování RSS.\r\n</ul>\r\n', 0, 0),
(61, 1, 1366322400, '', 'Update na build 379.', 0, 0),
(62, 1, 1366322400, 'Seznam změn ', '<ul>\r\n<li>Přidána možnost pro odesílání emailu při vytvoření účtu a při změně hesla.\r\n<li>Aktualizace předdefinovaných kategorii pro MTBO.\r\n<li>Zrušeno zobrazení datumu v záhlaví některých stránek, další sjednocování vzhledu.\r\n<li>Odstraněny červené nápisy o refreshi. Provádí se automaticky pri změně.\r\n<li>Uživateli přidánan vlastno národnost, pro budoucí export do ORISu. Přidána podpora pro editaci národnosti.\r\n</ul>', 0, 0),
(63, 1, 1367100000, '', 'Update na build 386.', 0, 0),
(64, 1, 1367100000, 'Seznam změn ', '<ul>\r\n<li>Pridana moznost hromadneho zadani castky a poznamky pro platby k zavodu.\r\n<li>Opraveno zobrazni lidi pro platby k zavodu.\r\n</ul>', 0, 0),
(65, 1, 1368568800, '', 'Update na build 392.', 0, 0),
(66, 1, 1368568800, 'Seznam změn ', '<ul>\r\n<li>Přidána možnost změnit závod při update jednotlivé platby\r\n</ul>', 0, 0),
(68, 11, 1369692000, '', 'ěěščřžýáíé\r\n12345', 0, 0),
(74, 1, 1391468400, '', 'Update na build 413.', 0, 0),
(71, 10, 1369692000, '+ěščšěřšěřžčýřřýžáýžíéíáýúůúů', 'ěěšžščýřáýžáýžéíýáúůúůúěčřšžčůáýíúůýžúřčůšěč', 0, 0),
(75, 1, 1391468400, 'Seznam změn', '<ul>\r\n<li>Posílena role správce\r\n<li>Možnost v závodě volby společné dopravy\r\n<li>Možnost reklamace k platbám u financí\r\n</ul>', 0, 0),
(76, 1, 1392591600, '', 'Update na build 418.', 0, 0),
(77, 1, 1392591600, 'Seznam změn', '<ul>\r\n<li>Možnost označení závodu jako zrušeného\r\n<li>Možnost spec. textu v seznamu přihlášek člena\r\n</ul>', 0, 0),
(78, 1, 1393282800, '', 'Update na build 424.', 0, 0),
(79, 1, 1393282800, 'Seznam změn ', '<ul>\r\n<li>Do exportu financí přidána registračka.\r\n<li>Přidány sumy za závod a za celý oddíl do financí.\r\n<li>Přidána možnost transferu peněz mezi členy.\r\n<li>Přidána možnost blokovat přihlášky na závody.\r\n</ul>', 0, 0),
(80, 1, 1393455600, '', 'Update na build 432.', 0, 0),
(81, 1, 1393455600, 'Seznam změn', '<ul>\r\n<li>Vylepšená verze možnosti transferu peněz mezi členy. \r\n<li>Přidána možnost malého trenéra přiřadit si členy do financí.\r\n</ul>', 0, 0),
(82, 1, 1400018400, '', 'Update na build 437.', 0, 0),
(83, 1, 1400018400, 'Seznam změn ', '<ul>\r\n<li>Sjednocení zobrazení v malém trenérovi\r\n<li>Rozšíření informací pro malého trenéra (suma za \"rodinu\", atp).\r\n<li>U finančníka doplněn sloupec s celkovou platbou za závod.\r\n<li>Odstraneni \'0\' z exportu přihlášek do jednotného formátu.\r\n</ul>\r\n', 0, 0),
(84, 1, 1412028000, '', 'Update na build 439.', 0, 0),
(85, 1, 1412028000, 'Seznam změn', '<ul>\r\n<li>Doplnění společné dopravy do hromadného přihlašování [LuF]\r\n</ul>', 0, 0),
(86, 1, 1420498800, '', 'Update na build 451.', 0, 0),
(87, 1, 1420498800, 'Seznam změn ', '<ul>\r\n<li>Rozšíření společné dopravy o automaticky stav. Nyní lze mít závod buď :<ul>\r\n<li>Bez společné dopravy\r\n<li>Společná doprava s výběrem účasti\r\n<li>Automatická společná doprava\r\n</ul> Nová třetí možnost neumožňuje závodníkovy vybrat zda využije hromadnou dopravu, počítá ji automaticky.\r\n</ul>', 0, 0),
(88, 1, 1421535600, '', 'Update na build 454.', 0, 0),
(89, 1, 1421535600, 'Seznam změn ', '<ul>\r\n<li>Přidána editace a zobrazeni typu oddílového příspěvku pro člena. Možnost editace finančníkem.\r\n</ul>', 0, 0),
(90, 1, 1422918000, '', 'Update na build 461.', 0, 0),
(91, 1, 1422918000, 'Seznam změn ', '<ul>\r\n<li>Komplet překódováno do UTF-8\r\n<li>Vnitřní opravy a rušení obsolete kódu\r\n<li>Přidána další varianta filtru pro zobrazení závodu, a to cca měsíc staré závody\r\n</ul>', 0, 0),
(92, 1, 1423004400, '', 'Update na build 464.', 0, 0),
(93, 1, 1423004400, 'Seznam změn ', '<ul>\r\n<li>Oprava pravidelného rozesílání emailů.\r\n</ul>', 0, 0),
(94, 10, 1424041200, '+ěřčžřčýřžýážýáííéˇqňˇočóöüï¨zaä¨c¨v¨b¨n¨n¨dgfrëwt', 'asdfadsfljalřěžýýčážýáíýířěřáěíářýíčáíščř\'qwe;++;°;ˇěčřetˇGhDYˇHyˇyˇyuˇUÍˇOˇˇjˇghfďfšˇXC', 0, 0),
(95, 11, 1424041200, '\'', '\'', 0, 0),
(96, 1, 1424127600, '', 'Update na build 466.', 0, 0),
(97, 1, 1424127600, 'Seznam změn', '<ul>\r\n<li>Oprava zpracování duplicit v editaci kategorií.\r\n<li>Opraveno chování poznámky ve financích.\r\n</ul>', 0, 0),
(98, 1, 1425423600, '', 'Update na build 470.', 0, 0),
(99, 1, 1425423600, 'Seznam změn ', '<ul>\r\n<li>Oprava drobností při přechodu na jiné kódování (emaily, generování loginu)\r\n<li>Defaultně je v převodu peněz prázdný příjemnce \r\n</ul>\r\n', 0, 0),
(100, 1, 1426633200, '', 'Update na build 473.', 0, 0),
(101, 1, 1426633200, 'Seznam změn ', '<ul>\r\n<li>Opraveno zasilání upozorňování o finančním stavu člena.\r\n</ul>\r\n', 0, 0),
(102, 10, 1429740000, 'Páteční trénink', 'Páteční trénink od 17 hodin na kraji lesa severně od univerzitního \r\nkampusu Bohunice (N 49°10.871\', E 16°34.173\'). Krátká trať (hodně \r\nkontrol, krátké postupy), pro menší nefáborkovaná linie, pro začátečníky \r\nhvězdice (vždy 1 kontrola tam a zpět). Les je pěkný humus, zakrytí všech \r\núdů doporučeno.', 0, 0),
(103, 10, 1429740000, 'Páteční trénink od 17 hodin na kraji lesa severně ', 'Páteční trénink od 17 hodin na kraji lesa severně od univerzitního kampusu Bohunice (N 49°10.871\', E 16°34.173\'). Krátká trať (hodně kontrol, krátké postupy), pro menší nefáborkovaná linie, pro začátečníky hvězdice (vždy 1 kontrola tam a zpět). Les je pěkný humus, zakrytí všech údů doporučeno. Páteční trénink od 17 hodin na kraji lesa severně od univerzitního kampusu Bohunice (N 49°10.871\', E 16°34.173\'). Krátká trať (hodně kontrol, krátké postupy), pro menší nefáborkovaná linie, pro začátečníky hvězdice (vždy 1 kontrola tam a zpět). Les je pěkný humus, zakrytí všech údů doporučeno. Páteční trénink od 17 hodin na kraji lesa severně od univerzitního kampusu Bohunice (N 49°10.871\', E 16°34.173\'). Krátká trať (hodně kontrol, krátké postupy), pro menší nefáborkovaná linie, pro začátečníky hvězdice (vždy 1 kontrola tam a zpět). Les je pěkný humus, zakrytí všech údů doporučeno. Páteční trénink od 17 hodin na kraji lesa severně od univerzitního kampusu Bohunice (N 49°10.871\', E 16°34.173\'). Krátká trať (hodně kontrol, krátké postupy), pro menší nefáborkovaná linie, pro začátečníky hvězdice (vždy 1 kontrola tam a zpět). Les je pěkný humus, zakrytí všech údů doporučeno. Páteční trénink od 17 hodin na kraji lesa severně od univerzitního kampusu Bohunice (N 49°10.871\', E 16°34.173\'). Krátká trať (hodně kontrol, krátké postupy), pro menší nefáborkovaná linie, pro začátečníky hvězdice (vždy 1 kontrola tam a zpět). Les je pěkný humus, zakrytí všech údů doporučeno. Páteční trénink od 17 hodin na kraji lesa severně od univerzitního kampusu Bohunice (N 49°10.871\', E 16°34.173\'). Krátká trať (hodně kontrol, krátké postupy), pro menší nefáborkovaná linie, pro začátečníky hvězdice (vždy 1 kontrola tam a zpět). Les je pěkný humus, zakrytí všech údů doporučeno.', 0, 0),
(105, 1, 1430172000, '', 'Update na build 477.', 0, 0),
(106, 1, 1430172000, 'Seznam změn ', '<ul>\r\n<li>Sjednoceny aktualitky do jednoho seznamu jež zobrazuje datum a přihlášky X dní dopředu.\r\n<li>Oprava chyby, kdy při editaci platby se vždy vybral nějaký závod i když platba nepatřila závodu.\r\n<li>Opraveno zpracování seznamu kategorií, kdy občas vypadla poslední.\r\n</ul>\r\n', 0, 0),
(107, 1, 1455318000, '', 'Update na build 493.', 0, 0),
(108, 1, 1455318000, 'Seznam změn ', '<ul>\r\n<li>Trenér může u nového člena zadat i rodné číslo.\r\n<li>Přidáno společné ubytování v přihláškách na závody.\r\n<li>Doplněny kontroly při převodech mezi členy (zůstatek, mínusové částky atp.)\r\n<li>Možnost admina resetovat plošně typ oddílových příspěvků.\r\n</ul>\r\n', 0, 0),
(109, 1, 1455663600, '', 'Update na build 497.', 0, 0),
(110, 1, 1455663600, 'Seznam změn ', '<ul>\r\n<li>Rozšířen export financí o informace o přiřazeném závodě.\r\n<li>Oprava posílání peněz pro finančníka.\r\n</ul>\r\n', 0, 0),
(111, 1, 1483830000, '', 'Update na build 502.', 0, 0),
(112, 1, 1483830000, 'Seznam změn ', 'Jen opravy chyb.', 0, 0),
(113, 1, 1514070000, '', 'Update na build 508.', 0, 0),
(114, 1, 1514070000, 'Seznam změn ', '<ul>\r\n<li>Nové typy pro závody/akce</li>\r\n<li>Přihlašování se doopravdy zavře až 2 hodiny po půlnoci</li>\r\n</ul>\r\n', 0, 0),
(115, 1, 1515020400, '', 'Update na build 513.', 0, 0),
(116, 1, 1515020400, 'Seznam změn ', '<ul>\r\n<li>Opraveno zobrazení financí pro finančníka a trenéry</li>\r\n<li>Opraveno generování emailů o financích</li>\r\n</ul>\r\n', 0, 0),
(117, 1, 1515452400, '', 'Update na build 515.', 0, 0),
(118, 1, 1515452400, 'Seznam změn', 'Jen opravy chyb. ', 0, 0),
(119, 1, 1518562800, '', 'Update na build 519.', 0, 0),
(120, 1, 1518562800, 'Seznam změn', '<ul>\r\n<li>Vylepsena podpora vice emailovych adres.</li>\r\n<li>Opraven problem s posilam penez u maleho trenera</li>\r\n<li>Finance - prehled u financnika - pridano zobrazeni k nejakemu datu</li>\r\n<li>U prihlasovatale uz nesjou zaskrtnuty stare zavody</li>\r\n</ul>\r\n', 0, 0),
(121, 1, 1520636400, '', 'Update na build 527.', 0, 0),
(122, 1, 1520636400, 'HTTPS', 'Testovací provoz na HTTPS.', 0, 0),
(123, 1, 1530655200, '', 'Update na build 531.', 0, 0),
(124, 1, 1530655200, 'Seznam změn', '<ul>\r\n<li>Zmena poradi trideni zavodniku pri prihlasovani.</li>\r\n<li>Pridany interni novinky</li>\r\n<li>Pridano posilani poslednich novinek emailem.</li>\r\n<li>Pridano zobrazeni poslednich internich novinek do aktualitek.</li>\r\n<li>Normalni uzivatel si nemuze editovat sam nektere udaje</li>\r\n</ul>', 0, 0),
(125, 1, 1530655200, 'Ukazkova interni novinka', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 1, 0),
(126, 1, 1532642400, '', 'Update na build 533.', 0, 0),
(127, 1, 1532642400, 'Seznam změn', '<ul>\r\n<li>API pro seznam zavodu.</li>\r\n</ul>', 0, 0),
(129, 14, 1533592800, 'Jendova pokusna novinka', 'Vypada to dobre! <B>Muzu editovat novinky ostatnim?</B>', 1, 0),
(130, 1, 1540677600, '', 'Update na build 537.', 0, 0),
(131, 1, 1543273200, '', 'Update na build 538.', 0, 0),
(132, 1, 1543273200, 'Seznam změn', '<ul>\r\n<li>Pokus o urychleni kodu - prihlasovani po 1 zavodnikovi pro trenery a prihlasovatele.</li>\r\n</ul>', 0, 0),
(133, 11, 1577487600, '', 'Update na build 539.', 0, 0),
(134, 1, 1603144800, '', 'Update na build 570.', 0, 1),
(135, 1, 1607209200, '', 'Update na build 576.', 0, 1),
(136, 1, 1653170400, '', 'Update na build 607.', 0, 2),
(137, 1, 1653170400, 'Seznam změn', '<ul>\r\n<li>Předefinované kategorie lze nyní přímo editovat a rozšiřovat</li>\r\n<li>Vylepšeno zabezpečení hesel</li>\r\n<li>Přidán link s detaily o členovy do části finance</li>\r\n<li>Přihlašování po jednom nyní umožnuje schovávat již přihlášené z výběru na příhlášení</li>\r\n<li>Drobné opravy a úpravy UI, CSS, překlepy</li>\r\n</ul>', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tst_payment_rules`
--

CREATE TABLE `tst_payment_rules` (
  `id` int(10) UNSIGNED NOT NULL,
  `typ` enum('ob','mtbo','lob','jine','trail') DEFAULT NULL,
  `typ0` enum('Z','T','S','V','N','J') DEFAULT NULL,
  `finance_type` int(10) UNSIGNED DEFAULT NULL,
  `termin` tinyint(1) DEFAULT NULL COMMENT 'Platný termín pro pozitivní hodnoutu, první platný pro negativní',
  `zebricek` int(10) UNSIGNED DEFAULT NULL,
  `druh_platby` enum('C','P','R') DEFAULT NULL,
  `platba` int(10) DEFAULT NULL,
  `uctovano` tinyint(1) UNSIGNED DEFAULT NULL COMMENT '1 startovné, 2 doprava, 4 ubytování'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_czech_ci COMMENT='podminene predpisy plateb uzivate';

-- --------------------------------------------------------

--
-- Table structure for table `tst_users`
--

CREATE TABLE `tst_users` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `prijmeni` varchar(30) NOT NULL DEFAULT '',
  `jmeno` varchar(20) NOT NULL DEFAULT '',
  `datum` date DEFAULT NULL,
  `adresa` varchar(50) DEFAULT NULL,
  `mesto` varchar(25) NOT NULL,
  `psc` varchar(6) NOT NULL,
  `tel_domu` varchar(25) DEFAULT NULL,
  `tel_zam` varchar(25) DEFAULT NULL,
  `tel_mobil` varchar(25) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `reg` int(4) UNSIGNED ZEROFILL NOT NULL DEFAULT 0000,
  `si_chip` int(9) UNSIGNED NOT NULL DEFAULT 0,
  `hidden` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `sort_name` varchar(50) NOT NULL DEFAULT '',
  `poh` enum('H','D') NOT NULL DEFAULT 'H',
  `lic` enum('E','A','B','C','D','R','-') DEFAULT '-',
  `lic_mtbo` enum('E','A','B','C','D','R','-') DEFAULT '-',
  `lic_lob` enum('E','A','B','C','D','R','-') DEFAULT '-',
  `fin` int(11) NOT NULL DEFAULT 0,
  `bank_account` varchar(255) DEFAULT NULL,
  `chief_id` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `rc` varchar(10) NOT NULL,
  `narodnost` varchar(2) NOT NULL DEFAULT 'CZ',
  `entry_locked` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `chief_pay` smallint(5) UNSIGNED DEFAULT NULL,
  `finance_type` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_czech_ci COMMENT='vsechny informace o uzivatelich';

--
-- Dumping data for table `tst_users`
--

INSERT INTO `tst_users` (`id`, `prijmeni`, `jmeno`, `datum`, `adresa`, `mesto`, `psc`, `tel_domu`, `tel_zam`, `tel_mobil`, `email`, `reg`, `si_chip`, `hidden`, `sort_name`, `poh`, `lic`, `lic_mtbo`, `lic_lob`, `fin`, `bank_account`, `chief_id`, `rc`, `narodnost`, `entry_locked`, `chief_pay`, `finance_type`) VALUES
(1, 'Pátek', 'Richard', '0000-00-00', '', '', '', '', '', '', 'arnost.p@centrum.cz', 7609, 130, 0, 'Pátek Richard', 'H', 'C', '-', '-', 0, NULL, 0, '', 'CZ', 0, NULL, 2),
(2, 'König', 'Lukáš', '1980-06-17', '', '', '', '', '', '', 'kenia@seznam.cz', 8001, 121, 0, 'König Lukáš', 'H', 'C', '-', '-', 0, NULL, 0, '', 'CZ', 0, NULL, 0),
(3, 'Veselý', 'Martin', '0000-00-00', '<a href =\\\"zhusta.sky.cz\\\">Předklášteří </a>', '', '', '', '', '', '', 7502, 53201, 0, 'Veselý Martin', 'H', 'C', '-', '-', 0, NULL, 12, '', 'CZ', 0, NULL, 0),
(4, 'Novák', 'Jan', '1991-01-01', '', '', '', '', '', '', '', 9111, 0, 0, 'Novák Jan', 'H', 'C', '-', '-', 0, NULL, 7, '', 'CZ', 0, 7, 3),
(5, 'Novák', 'Karel', '1993-01-01', '', '', '', '', '', '', '', 9312, 0, 0, 'Novák Karel', 'H', 'A', '-', '-', 0, NULL, 7, '', 'CZ', 0, 7, 0),
(6, 'Novák', 'Martin', '1995-01-01', '', '', '', '', '', '', '', 9513, 1341, 0, 'Novák Martin', 'H', 'E', '-', '-', 0, NULL, 0, '', 'CZ', 0, NULL, 0),
(7, 'Nováková', 'Eva', '1997-01-01', '', '', '', '', '', '', '', 9751, 55555, 0, 'Nováková Eva', 'D', 'E', 'E', '-', 0, NULL, 0, '', 'CZ', 0, NULL, 0),
(8, 'Nováková', 'Zuzana', '1999-01-01', '', '', '', '', '', '', '', 9952, 1341431, 0, 'Nováková Zuzana', 'D', 'R', '-', '-', 0, NULL, 7, '', 'CZ', 0, 7, 0),
(9, 'Nováková', 'Jitka', '1983-01-01', '', '', '', '', '', '', '', 8357, 49494, 0, 'Nováková Jitka', 'D', 'C', '-', '-', 0, NULL, 7, '', 'CZ', 0, 7, 0),
(10, 'Mazaný', 'Filil', '1988-05-01', '', '', '', '', '', '', '', 8801, 1486785, 0, 'Mazaný Filil', 'H', 'E', '-', 'A', 0, NULL, 0, '', 'CZ', 0, NULL, 2),
(11, 'Drábek', 'Jan', '1985-11-04', 'Škroupova 10', 'Brno - Židenice', '636 00', '', '', '608477026', 'jan_drabek@volny.cz', 8511, 49690, 0, 'Drábek Jan', 'H', 'C', '-', '-', 0, NULL, 13, '', 'CZ', 0, 13, 1),
(12, 'Zřídkaveselý', 'Libor', '1972-07-03', 'Skorkovského 153', 'Brno', '636 00', '549246395', '545321282', '604776993', 'zr@jaroska.cz', 7207, 101, 0, 'Zřídkaveselý Libor', 'H', 'C', '-', '-', 0, NULL, 0, '', 'CZ', 0, NULL, 0),
(13, 'Křístková', 'Veronika', '1983-01-28', 'Za kovárnou 405', 'Smržice', '79817', '', '', '736120094', 'fricco@seznam.cz', 8379, 52865, 0, 'Křístková Veronika', 'D', 'C', '-', '-', 0, NULL, 0, '', 'CZ', 0, NULL, 0),
(14, 'Hlavová', 'Hana', '1988-05-12', 'Tyršova 397', 'Konice', '798 52', '608131017', '', '608131017', 'hanah.kon@centrum.cz', 8888, 515450, 0, 'Hlavová Hana', 'D', 'E', '-', '-', 0, NULL, 0, '', 'CZ', 1, NULL, 3),
(15, 'Zvarik', 'Tomáš', '1977-01-29', '', '', '000 00', '', '', '', 'ciselko@gmail.com, icq 220735915', 7702, 0, 0, 'Zvarik Tomáš', 'H', 'C', '-', '-', 0, NULL, 0, '', 'CZ', 0, NULL, 0),
(16, 'Zřídkaveselý', 'Adam', '2005-11-11', 'Skorkovského 153', 'Brno', '636 00', '', '', '', '', 0505, 0, 0, 'Zřídkaveselý Adam', 'H', 'C', '-', '-', 0, NULL, 12, '', 'CZ', 0, NULL, 0),
(17, 'Zřídkaveselý', 'Martin', '2008-09-01', 'Skorkovského 153', 'Brno', '636 00', '', '', '', '', 0808, 0, 0, 'Zřídkaveselý Martin', 'H', 'C', '-', '-', 0, NULL, 12, '', 'CZ', 0, NULL, 0),
(18, 'Stehlík', 'Martin', '1985-06-25', 'Tichého 7', 'Brno', '616 00', '543240819', '', '608438928', 'stehlik.m@atlas.cz', 8503, 1985625, 0, 'Stehlík Martin', 'H', 'C', '-', '-', 0, NULL, 0, '', 'CZ', 0, NULL, 3),
(32, 'Urválek', 'Jiří', '0000-00-00', '', '', '', '', '', '', '', 6107, 0, 0, 'Urválek Jiří', 'H', 'C', '-', '-', 0, NULL, 0, '', 'CZ', 0, NULL, 0),
(21, 'Bukovac', 'Palo', '1981-09-27', 'Nezvalova 27', 'Bratislava, Slovensko', '821 06', '00421245525925', '', '00421905570266', 'bukki.pallo@zoznam.sk', 8101, 515478, 0, 'Bukovac Palo', 'H', 'E', '-', '-', 0, NULL, 14, '', 'CZ', 1, NULL, 0),
(22, 'Bukovac', 'Dušan', '1975-03-07', 'Nezvalova 27', 'Bratislava, Slovensko', '821 06', '00421245525925', '', '00421905296601', 'dusan.bukovac@vertical.sk ', 7503, 232004, 0, 'Bukovac Dušan', 'H', 'C', '-', '-', 0, NULL, 11, '', 'CZ', 1, NULL, 3),
(23, 'Bukovac', 'Maroš', '1976-05-25', 'Nezvalova 27', 'Bratislava,  Slovensko', '821 06', '00421245525925', '00421268206015', '00421905974050', 'bukovaci@centrum.sk maros.bukovac@vertical.sk', 7605, 232006, 0, 'Bukovac Maroš', 'H', 'C', '-', '-', 0, NULL, 11, '', 'CZ', 1, NULL, 0),
(25, 'Bukovacová', 'Alena', '1974-04-22', 'Nezvalova 27', 'Bratislava, Slovensko', '821 06', '00421245525925', '', '00421905194946', 'alena.bukovacova@gmail.com', 7454, 232008, 0, 'Bukovacová Alena', 'D', 'C', '-', '-', 0, NULL, 14, '', 'CZ', 1, NULL, 0),
(26, 'Jevsejenko', 'Alexandr', '1981-01-23', 'Orlí 9', 'Brno', '602 00', '542213452', '', '605534547', 'jevsejenko@gmail.com', 8110, 52073, 0, 'Jevsejenko Alexandr', 'D', 'C', '-', '-', 0, NULL, 13, '', 'CZ', 0, NULL, 5),
(27, 'Koča', 'Jaroslav', '1982-06-15', 'Dusíkova 29', 'Brno', '638 00', '', '', '777269786', 'jerry.koca@email.cz', 8200, 301217, 0, 'Koča Jaroslav', 'H', 'C', '-', '-', 0, NULL, 13, '', 'CZ', 0, NULL, 4),
(28, 'Kočová', 'Hana', '1986-02-27', 'Vranov 260', 'Vranov', '664 32', '', '', '602 344 660', 'hankoc@seznam.cz', 8676, 52165, 0, 'Kočová Hana', 'D', 'A', '-', '-', 0, NULL, 18, '', 'CZ', 0, NULL, 1),
(29, 'Kolbaba', 'Tomáš', '1982-02-21', '', '', '', '', '', '724243655', 'kolbic@seznam.cz', 8243, 52022, 0, 'Kolbaba Tomáš', 'H', 'C', '-', '-', 0, NULL, 18, '', 'CZ', 0, NULL, 0),
(30, 'Zimmermann', 'Jakub', '1989-01-11', 'Blatnická 12', 'Brno', '628 00', '', '', '723 967 341', 'jarazim@seznam.cz ,skype:jara.zimmermann', 8928, 995810, 0, 'Zimmermann Jakub', 'H', 'C', '-', '-', 0, NULL, 15, '', 'CZ', 0, NULL, 0),
(31, 'Zimmermann', 'Štěpán', '1991-12-20', 'Blatnická 12', 'Brno', '628 00', '', '', '721062310', 'stepazdepa@seznam.cz, xzimmermanns@gmail.com', 9101, 911220, 0, 'Zimmermann Štěpán', 'H', 'R', '-', '-', 0, NULL, 15, '', 'CZ', 0, NULL, 0),
(33, 'Mádlová', 'Věra', '0000-00-00', '', '', '', '', '', '', '', 8951, 0, 0, 'Mádlová Věra', 'D', 'C', '-', '-', 0, NULL, 0, '', 'CZ', 0, NULL, 6),
(34, 'Gross', 'Michal', '0000-00-00', '', '', '', '', '', '', '', 6946, 0, 0, 'Gross Michal', 'H', 'C', '-', '-', 0, NULL, 0, '', 'CZ', 0, NULL, 0),
(36, 'Štěrbák', 'Řehoř', '0000-00-00', '', '', '', '', '', '', 'arnost@eob.cz', 8006, 0, 0, 'Štěrbák Řehoř', 'H', 'C', '-', '-', 0, NULL, 0, '', 'CZ', 0, NULL, 0),
(37, 'Mocvesely', 'Osvald', '1944-01-01', '', '', '', '', '', '', '', 4400, 0, 0, 'Mocvesely Osvald', 'H', 'C', '-', '-', 0, NULL, 0, '', 'BW', 1, NULL, 8),
(38, 'Matula', 'Petr', '1982-05-17', '', '', '', '', '', '', '', 8202, 2042531, 0, 'Matula Petr', 'H', 'C', '-', '-', 0, NULL, 0, '', 'CZ', 0, NULL, 5);

-- --------------------------------------------------------

--
-- Table structure for table `tst_xmailinfo`
--

CREATE TABLE `tst_xmailinfo` (
  `id` int(11) UNSIGNED NOT NULL,
  `id_user` int(11) UNSIGNED NOT NULL,
  `email` varchar(50) NOT NULL,
  `active_tf` tinyint(1) UNSIGNED NOT NULL,
  `active_ch` tinyint(1) UNSIGNED NOT NULL,
  `active_rg` tinyint(1) UNSIGNED NOT NULL,
  `daysbefore` int(2) NOT NULL,
  `type` int(11) NOT NULL,
  `sub_type` int(11) UNSIGNED NOT NULL,
  `ch_data` int(11) UNSIGNED NOT NULL,
  `active_fin` tinyint(1) UNSIGNED NOT NULL,
  `active_finf` tinyint(1) UNSIGNED NOT NULL,
  `fin_type` int(11) UNSIGNED NOT NULL,
  `fin_limit` smallint(5) NOT NULL,
  `active_news` tinyint(1) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_czech_ci;

--
-- Dumping data for table `tst_xmailinfo`
--

INSERT INTO `tst_xmailinfo` (`id`, `id_user`, `email`, `active_tf`, `active_ch`, `active_rg`, `daysbefore`, `type`, `sub_type`, `ch_data`, `active_fin`, `active_finf`, `fin_type`, `fin_limit`, `active_news`) VALUES
(1, 1, 'arnost@eob.cz', 1, 1, 1, 3, 31, 191, 7, 1, 0, 1, 500, 0),
(2, 2, 'kenia@seznam.cz', 1, 1, 1, 11, 31, 191, 7, 0, 0, 0, 0, 0),
(3, 11, 'jan_drabek@volny.cz', 0, 0, 0, 3, 1, 43, 0, 0, 0, 0, 0, 0),
(4, 13, 'fricco@seznam.cz', 0, 0, 0, 14, 31, 191, 7, 0, 0, 0, 0, 0),
(5, 18, 'stehlik.m@atlas.cz', 0, 0, 0, 3, 1, 0, 7, 0, 0, 0, 0, 0),
(6, 3, 'veselacek@atlas.cz', 0, 0, 0, 3, 31, 191, 7, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tst_zavod`
--

CREATE TABLE `tst_zavod` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `ext_id` varchar(8) DEFAULT NULL,
  `datum` int(11) NOT NULL DEFAULT 0,
  `datum2` int(11) NOT NULL DEFAULT 0,
  `nazev` varchar(70) DEFAULT NULL,
  `misto` varchar(50) DEFAULT NULL,
  `typ` enum('ob','mtbo','lob','jine','trail') NOT NULL,
  `typ0` enum('Z','T','S','V','N','J') NOT NULL DEFAULT 'Z',
  `vicedenni` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `zebricek` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `ranking` enum('0','1') NOT NULL DEFAULT '0',
  `odkaz` varchar(100) DEFAULT NULL,
  `prihlasky` tinyint(4) UNSIGNED NOT NULL DEFAULT 0,
  `prihlasky1` int(11) DEFAULT 0,
  `prihlasky2` int(11) NOT NULL DEFAULT 0,
  `prihlasky3` int(11) NOT NULL DEFAULT 0,
  `prihlasky4` int(11) NOT NULL DEFAULT 0,
  `prihlasky5` int(11) NOT NULL DEFAULT 0,
  `etap` tinyint(4) UNSIGNED NOT NULL DEFAULT 0,
  `kategorie` mediumtext NOT NULL,
  `poznamka` mediumtext NOT NULL,
  `vedouci` int(10) UNSIGNED NOT NULL,
  `poslano` tinyint(3) UNSIGNED NOT NULL,
  `oddil` varchar(8) DEFAULT NULL,
  `send` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `modify_flag` int(10) UNSIGNED NOT NULL,
  `transport` tinyint(1) DEFAULT NULL,
  `ubytovani` tinyint(1) DEFAULT NULL,
  `kapacita` smallint(6) DEFAULT NULL,
  `prihlasenych` smallint(6) NOT NULL DEFAULT 0,
  `cancelled` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_czech_ci COMMENT='tabulka popisu zavodu';

--
-- Dumping data for table `tst_zavod`
--

INSERT INTO `tst_zavod` (`id`, `ext_id`, `datum`, `datum2`, `nazev`, `misto`, `typ`, `typ0`, `vicedenni`, `zebricek`, `ranking`, `odkaz`, `prihlasky`, `prihlasky1`, `prihlasky2`, `prihlasky3`, `prihlasky4`, `prihlasky5`, `etap`, `kategorie`, `poznamka`, `vedouci`, `poslano`, `oddil`, `send`, `modify_flag`, `transport`, `ubytovani`, `kapacita`, `prihlasenych`, `cancelled`) VALUES
(1, NULL, 1545433200, 0, 'Vánoční běh Brnem', 'Brno', 'ob', 'V', 0, 0, '1', '', 1, 1543618800, 0, 0, 0, 0, 1, '', '', 0, 0, 'ABC', 0, 0, 0, 0, NULL, 0, 1),
(2, NULL, 1545778800, 0, 'Štěpánský běh', 'Radostice2', 'jine', 'T', 0, 0, '1', '', 1, 1545174000, 0, 0, 0, 0, 1, '', '', 0, 0, 'BBM', 0, 0, 0, 0, NULL, 0, 1),
(3, NULL, 1671750000, 1672009200, 'Svátky ', 'Doma u krbu', 'lob', 'T', 1, 2, '1', '', 4, 1670626800, 1670886000, 1671231600, 1671577200, 0, 0, '', '', 0, 0, 'XMA', 0, 5, 0, 0, NULL, 0, 1),
(4, NULL, 1546210800, 0, 'Silvestrovské poježdění', 'Velodrom', 'mtbo', 'V', 0, 8, '1', '', 1, 1545865200, 0, 0, 0, 0, 1, '', '', 0, 0, 'ACH', 0, 0, 0, 0, NULL, 4, 0),
(5, NULL, 1546124400, 0, 'Hromniční trápení', 'Blansko', 'ob', 'Z', 0, 0, '0', '', 1, 1545692400, 0, 0, 0, 0, 1, '', '', 0, 0, 'RBK', 0, 0, 0, 0, NULL, 0, 0),
(6, NULL, 1675378800, 1675724400, 'Další pracovní týden', 'Brno', 'jine', 'T', 1, 0, '0', '', 5, 1672873200, 1672959600, 1673046000, 1674774000, 1675033200, 5, '', '', 0, 0, 'WRK', 0, 5, 0, 0, NULL, 1, 0),
(7, NULL, 1675378800, 0, 'testovaci nejakej', 'misto', 'ob', 'J', 0, 191, '1', 'www.asdad.sd', 1, 1672873200, 0, 0, 0, 0, 1, '', '', 0, 0, 'asdf', 0, 5, 0, 0, NULL, 1, 0),
(8, NULL, 1673737200, 0, 'MČR klasická trať, 1.ČP', 'Strážné ', 'lob', 'Z', 0, 17, '0', 'www.ob.spartak-vrchlabi.cz/zavody2011/lob/', 1, 1672786800, 0, 0, 0, 0, 1, '', '', 0, 0, 'VRL', 0, 5, 0, 0, NULL, 0, 0),
(9, NULL, 1674946800, 0, '3.ČP na krátké trati', 'Zinnwald, DE', 'lob', 'Z', 0, 1, '0', '', 1, 1672786800, 0, 0, 0, 0, 1, '', '', 0, 0, 'LIV', 0, 5, 1, 0, NULL, 0, 0),
(10, NULL, 1676761200, 0, 'MČR dvoučlenných družstev', 'Tři Studně', 'lob', 'T', 0, 48, '0', 'www.ski-adventure.cz/2011/rozpis-lob/', 1, 1675897200, 0, 0, 0, 0, 1, '', '', 28, 0, 'OSN+TBM', 0, 5, 0, 0, NULL, 1, 0),
(11, NULL, 1708902000, 0, 'MČR ve sprintu, 6.ČP', 'Nová Ves u Rýmařova', 'lob', 'Z', 0, 16, '0', '', 0, 0, 0, 0, 0, 0, 1, '', '', 0, 0, 'VRB', 0, 4, 0, 0, NULL, 1, 0),
(13, NULL, 1710802800, 1710889200, 'Brutus Extreme Orienteering', '', 'jine', 'Z', 1, 0, '0', '', 3, 1705964400, 1707260400, 1709334000, 0, 0, 2, 'D10N;D12C;D14C;D16C;D18C;D21C;D21D;D35C;D45C;D55C;H10N;H12C;H14C;H16C;H18C;H21C;H21D;H35C;H45C;H55C;HDR', '', 0, 0, 'GEMMA', 0, 5, 0, 0, NULL, 2, 0),
(14, NULL, 1675465200, 0, 'Hromniční trápení II', 'Blansko', 'ob', 'Z', 0, 8, '0', 'www.hromnicni.wz.cz/', 1, 1675033200, 0, 0, 0, 0, 1, 'H14;D14;H19;D19;H35;D35;P;HDR', '', 0, 0, 'RBK', 0, 5, 0, 0, NULL, 1, 0),
(15, NULL, 1680213600, 1680300000, 'CESOM 2015', 'Borský Mikuláš', 'ob', 'Z', 1, 0, '1', '', 2, 1677193200, 1678834800, 0, 0, 0, 3, 'H10N;D10N;H12C;D12C;H14C;D14C;H16C;D16C;H18C;D18C;H21C;D21C;H21D;D21D;H35C;D35C;H45C;D45C;H55C;D55C;HDR;D12B;D14B;D16B;D18B;D20B;D21B;D35B;D40B;D45B;D50B;D55B;D60B;D65B;H12B;H14B;H16B;H18B;H20B;H21B;H35B;H40B;H45B;H50B;H55B;H60B;H65B;H70B;H75B', '', 0, 0, 'BBA', 0, 5, 0, 0, NULL, 9, 0),
(16, NULL, 1687989600, 1688162400, 'Cena střední Moravy', 'Okrouhlá – Melkov', 'mtbo', 'Z', 1, 0, '1', 'csm12.kobkon.cz/', 1, 1686693600, 0, 0, 0, 0, 3, 'H10N;D10N;H12C;D12C;D12;H14C;D14C;D14;H16C;D16C;D16;H18C;D18C;D18;H21C;D21C;H21D;D21D;H35C;D35C;D35;H45C;D45C;D45;H55C;D55C;D55;D10N;D12C;D14C;D16C;D18C;D21C;D21D;D35C;D45C;D55C;HDR;D10N;D12C;D14C;D16C;D18C;D21C;D21D;D35C;D45C;D55C;H10N;H12C;H14C;H16C;H18C;H21C;H21D;H35C;H45C;H55C;HDR;', '', 0, 0, 'KON+ZBM', 0, 5, 1, 0, NULL, 5, 0),
(18, NULL, 1689199200, 1689372000, 'H.S.H. Vysočina cup', 'Leština u Skály', 'ob', 'Z', 1, 128, '1', 'www.obchrast.com/', 2, 1686866400, 1688508000, 0, 0, 0, 3, '', '', 0, 0, 'CHT', 0, 5, 1, 1, NULL, 2, 0),
(19, NULL, 1689112800, 0, 'H.S.H. Vysočina cup - noční ', 'Leština u Skály', 'ob', 'Z', 0, 128, '1', 'obchrast.com/nz.php?nzid=891', 1, 1683842400, 0, 0, 0, 0, 1, '', '2154365ěřčěšžžřážíáýöïÿ¨tëw¨s¨ds¨d¨x¨vdgf¨ds¨sä', 23, 0, 'CHT', 0, 5, 1, 0, NULL, 4, 0),
(20, NULL, 1721426400, 1721599200, 'Veteran cup', 'Obec Záměl', 'ob', 'Z', 1, 0, '1', 'obvamberk.aspone.cz/VETERAN_CUP_2012_podrobny_rozpis.pdf', 3, 1715551200, 1719525600, 1720735200, 0, 0, 3, '', '', 0, 0, 'VAM', 0, 5, 0, 0, NULL, 1, 0),
(21, NULL, 1721944800, 1722204000, 'Grand Prix Silesia 20XX', 'Vidnava', 'ob', 'Z', 1, 128, '1', 'silesia.obopava.cz/silesia_2012/', 3, 1709074800, 1719525600, 1720821600, 0, 0, 5, 'D10N;D12;D14;D16;D18;D21C;D21D;D35;D45;D55;H10N;H12;H14;H16;H18;H21C;H21D;H35;H45;H55;HDR', '', 0, 0, 'AOP', 1, 5, 1, 1, NULL, 1, 0),
(22, NULL, 1724536800, 1724623200, 'Pěkné prázdniny', 'Malá Skála, Český ráj', 'ob', 'Z', 1, 0, '1', 'www.tur.cz/pekneprazdniny/', 2, 1722031200, 1723327200, 0, 0, 0, 3, '', '', 0, 0, 'TUR', 0, 5, 2, 0, NULL, 2, 0),
(23, NULL, 1535061600, 0, 'Pěkné prázdniny - sprint  ', 'Turnov', 'ob', 'Z', 0, 0, '1', 'tur.cz/pekneprazdniny/', 2, 1532642400, 1532728800, 0, 0, 0, 1, '', 'Prihlasuje se spolu prihlaskou na PPcka', 0, 0, 'TUR', 1, 0, 0, 0, NULL, 2, 0),
(24, NULL, 1723932000, 1724018400, '3 etapový OB Jičín ', 'Sportcentrum Brada', 'ob', 'Z', 1, 0, '1', 'objicin.tpc.cz/jicin/main.php?&zavody=40&menu=100&jazyk=0', 1, 1720821600, 0, 0, 0, 0, 3, '', 'E1 a E2 bez rankingu', 0, 0, 'SJC', 0, 5, 1, 0, NULL, 0, 0),
(25, NULL, 1725055200, 1725228000, 'Cena východních Čech', 'Seč', 'ob', 'Z', 1, 0, '1', 'lpu.cz/cvc15', 2, 1719957600, 1720821600, 0, 0, 0, 3, '', '', 0, 0, 'LPU', 0, 5, 0, 0, NULL, 2, 0),
(26, NULL, 1727474400, 1727560800, 'MČR na klasice', 'Skokovy', 'ob', 'Z', 1, 17, '1', 'www.ok99.cz/mct2012', 1, 1725746400, 0, 0, 0, 0, 0, '', '', 0, 0, 'PHK+TUV', 0, 5, 0, 0, NULL, 3, 0),
(27, NULL, 1538776800, 0, 'MČR štafet', 'Březina', 'ob', 'Z', 0, 48, '1', 'mcrdruzstva.eob.cz', 1, 1537567200, 0, 0, 0, 0, 1, 'a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d,a,b,c,d', '', 0, 0, 'ZBM', 0, 0, 1, 0, NULL, 1, 0),
(28, NULL, 1728252000, 0, 'MČR klubů a oblastních výběrů žactva', 'Březina', 'ob', 'Z', 0, 0, '1', 'mcrdruzstva.eob.cz', 1, 1726956000, 0, 0, 0, 0, 1, 'H65D;', '', 0, 0, 'ZBM', 0, 5, 0, 0, NULL, 0, 0),
(29, NULL, 1536357600, 0, 'ČP, ŽA , ŽB - klasika', 'Nová Ves, lyžařský stadion', 'ob', 'Z', 0, 131, '1', 'cp12.aljosa.org', 1, 1534975200, 0, 0, 0, 0, 1, '', '', 0, 0, 'ASU', 0, 0, 0, 0, NULL, 3, 0),
(30, NULL, 1536444000, 0, 'ČP, ŽA , ŽB - krátká trať', 'Nová Ves, lyžařský stadion', 'ob', 'Z', 0, 131, '1', 'cp12.aljosa.org', 1, 1534975200, 0, 0, 0, 0, 1, '', '', 0, 0, 'ASU', 0, 0, 0, 0, NULL, 0, 0),
(31, NULL, 1726351200, 0, '9. JmL - klasika', 'Předklášteří', 'ob', 'Z', 0, 136, '1', 'www.tbm.cz/zavody/', 0, 0, 0, 0, 0, 0, 1, '', '', 0, 0, 'TBM', 0, 4, 1, 0, NULL, 0, 0),
(32, NULL, 1726956000, 0, '10. JmL - klasika', 'Nová Dědina', 'ob', 'Z', 0, 136, '1', 'www.smerkromeriz.cz/poradame/', 5, 1715292000, 1718056800, 1720735200, 1723500000, 1726264800, 1, '', '', 0, 0, 'SKM', 0, 5, 2, 0, NULL, 1, 0),
(33, NULL, 1539381600, 0, '11. JmL - klasika', 'Dambořice', 'ob', 'Z', 0, 136, '1', 'abm.eob.cz/', 1, 1538863200, 0, 0, 0, 0, 1, 'H10N;D10N;H10;D10;H12;D12;H14;D14;H16;D16;H18;D18;H21C;D21C;H21D;D21D;H35;D35;H45;D45;H55;D55;H65;HDR;P;pořadatel;', '', 0, 0, 'ABM', 0, 0, 0, 0, NULL, 0, 0),
(34, NULL, 1539986400, 0, '12. JmL - klasika2', 'Bílý potok', 'ob', 'Z', 0, 136, '1', 'vsk-mendelu.cz/', 0, 0, 0, 0, 0, 0, 1, '', 'Test', 0, 0, 'VBM', 0, 0, 0, 0, NULL, 0, 0),
(35, NULL, 1540591200, 0, '13. JmL - klasika', 'Senetářov', 'ob', 'Z', 0, 136, '1', 'radioklub.blansko.net/', 1, 1536098400, 0, 0, 0, 0, 1, 'D12E;', '', 0, 0, 'RBK', 0, 0, 0, 0, NULL, 1, 0),
(36, NULL, 1675292400, 0, 'Hromniční trápení', 'Blansko', 'ob', 'Z', 0, 7, '1', 'hromnicni.wz.cz/', 2, 1674860400, 1675033200, 0, 0, 0, 1, '', 'Testovací závod', 0, 0, 'RBK', 0, 5, 2, 0, NULL, 2, 0),
(37, NULL, 1551999600, 0, 'Kvetinovy zavod bez dopravy', 'Brno', 'ob', 'Z', 0, 24, '0', '', 2, 1549234800, 1549407600, 0, 0, 0, 1, '', '', 0, 0, 'PBM', 0, 0, 0, 0, NULL, 4, 0),
(38, NULL, 1678402800, 1678575600, 'Perina kup', 'Podoli', 'ob', 'S', 1, 132, '1', '', 3, 1675292400, 1676588400, 1677625200, 0, 0, 4, 'D10N;D12;D14;D16;D18;D21C;D21D;D35;D45;D55;H10N;H12;H14;H16;H18;H21C;H21D;H35;H45;H55;HDR;', '', 0, 0, 'PGP', 0, 5, 0, 0, NULL, 7, 0),
(40, NULL, 1710198000, 0, 'bjlsdjvn', 'Brno', 'ob', 'Z', 0, 128, '1', 'o-mikron.czechian.net/', 2, 1710025200, 1710111600, 0, 0, 0, 1, 'H35A;D35A;H35D;D35D;', 'fsdgdgh', 0, 0, 'MBM', 0, 5, 0, 0, NULL, 1, 1),
(41, NULL, 1707433200, 0, 'POKUS ABM', 'Žabiny', 'ob', 'Z', 0, 0, '1', '', 3, 1694124000, 1699657200, 1703977200, 0, 0, 1, 'D10N;D12;D14;D16;D18;D21C;D21D;D35;D45;D55;H10N;H12;H14;H16;H18;H21C;H21D;H35;H45;H55;HDR;', '', 0, 0, 'ZBM', 0, 5, 0, 0, NULL, 5, 0),
(42, NULL, 1708815600, 0, 'pokus ABM 2', 'Brno', 'ob', 'Z', 0, 1, '1', '', 1, 1708729200, 0, 0, 0, 0, 1, 'D10N;D12;D14;D16;D18;D21C;D21D;D35;D45;D55;H10N;H12;H14;H16;H18;H21C;H21D;H35;H45;H55;HDR;', '', 0, 0, 'ABM', 0, 5, 1, 0, NULL, 5, 0),
(43, NULL, 1678402800, 0, 'ABM 3', '', 'ob', 'J', 0, 0, '1', '', 1, 1678402800, 0, 0, 0, 0, 1, 'D10N;D12;D14;D16;D18;D21C;D21D;D35;D45;D55;H10N;H12;H14;H16;H18;H21C;H21D;H35;H45;H55;HDR;', '', 0, 0, 'ABM', 0, 5, 0, 0, NULL, 4, 0),
(44, NULL, 1738364400, 0, 'Testovaci zavod pro dopravu', 'Misto', 'ob', 'Z', 0, 52, '1', '', 0, 0, 0, 0, 0, 0, 1, '', '', 0, 0, 'XYZ', 0, 4, 1, 0, NULL, 8, 0),
(45, NULL, 1720994400, 0, 'ěščřžýáíé', 'BřBr = ěščřžýáíé', 'ob', 'J', 0, 0, '1', '', 0, 0, 0, 0, 0, 0, 1, '', '', 0, 0, 'AZT', 0, 4, 1, 0, NULL, 1, 0),
(46, NULL, 1602712800, 0, 'Test', 'Brno', 'ob', 'Z', 0, 1, '1', '', 1, 1603058400, 0, 0, 0, 0, 1, '', '', 38, 0, 'YBV', 0, 2, 2, 1, NULL, 0, 0),
(47, NULL, 1615590000, 0, 'Pokus', 'XXX', 'ob', 'Z', 0, 1, '1', '', 2, 1607727600, 1610406000, 0, 0, 0, 1, 'D10N;D12C;D14C;D16C;D18C;D21C;D21D;D35C;D45C;D55C;H10N;H12C;H14C;H16C;H18C;H21C;H21D;H35C;H45C;H55C;HDR', '', 0, 0, 'kkk', 1, 2, 0, 0, NULL, 5, 0),
(48, NULL, 1658354400, 0, 'Letni test', 'Brno - Salingrad', 'jine', 'Z', 0, 17, '0', '', 2, 1658095200, 1658268000, 0, 0, 0, 1, 'D16A;D18A;D20A;D21A;D21E;H16A;H18A;H20A;H21A;H21E', '', 0, 0, 'FAK', 0, 2, 2, 2, NULL, 9, 0),
(49, NULL, 1675378800, 0, 'MČR Klasika', 'Dolní Lhota', 'ob', 'Z', 0, 1, '1', '', 2, 1674169200, 1674255600, 0, 0, 0, 1, 'H18D;H18N;H20B;H20C;H21E;H21A;D10N;D12;D14;D16;D18;D21C;D21D;D35;D45;D55;H10N;H12;H14;H16;H18;H21C;H21D;H35;H45;H55;HDR;D16A;D18A;D20A;D21A;D21E;H16A;H18A;H20A', '', 0, 0, 'GBM', 0, 2, 1, 2, NULL, 1, 0),
(50, NULL, 1699311600, 0, 'Kamenec pod Vtáčnikom Middle sobota', 'Kamenec pod Vtáčnikom', 'ob', 'Z', 0, 1, '1', 'is.orienteering.sk/competitions/1759', 3, 1696111200, 1696370400, 1698793200, 0, 0, 1, 'D10N;D12;D14;D16;D18;D21C;D21D;D35;D45;D55;H10N;H12;H14;H16;H18;H21C;H21D;H35;H45;H55;HDR', 'Slovenský rebríček jednotlivcov - E1	07.10.2023	Stredná trať	205\r\nSlovenský rebríček jednotlivcov - E2	08.10.2023	Stredná trať	212', 0, 0, 'SKS', 0, 2, 0, 0, NULL, 2, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tst_zavxus`
--

CREATE TABLE `tst_zavxus` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_user` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `id_zavod` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `kat` varchar(10) NOT NULL DEFAULT '',
  `pozn` varchar(255) DEFAULT NULL,
  `pozn_in` varchar(255) DEFAULT NULL,
  `termin` tinyint(4) UNSIGNED NOT NULL DEFAULT 1,
  `si_chip` int(9) UNSIGNED NOT NULL DEFAULT 0,
  `transport` tinyint(1) DEFAULT NULL,
  `sedadel` tinyint(1) DEFAULT NULL,
  `ubytovani` tinyint(1) DEFAULT NULL,
  `participated` tinyint(1) DEFAULT NULL,
  `add_by_fin` tinyint(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_czech_ci COMMENT='tabulka prihlasek - clovek X zavod';

--
-- Dumping data for table `tst_zavxus`
--

INSERT INTO `tst_zavxus` (`id`, `id_user`, `id_zavod`, `kat`, `pozn`, `pozn_in`, `termin`, `si_chip`, `transport`, `sedadel`, `ubytovani`, `participated`, `add_by_fin`) VALUES
(2, 1, 14, 'H19', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(3, 1, 15, 'H21', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(4, 2, 15, 'H21', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(5, 3, 15, 'H21', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(6, 4, 15, 'H21', 'postel', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(7, 5, 15, 'H20', '', 'jedu vlakem', 1, 0, NULL, NULL, NULL, NULL, NULL),
(8, 6, 15, 'H18', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(9, 7, 15, 'D16', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(10, 8, 15, 'D14', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(11, 9, 15, 'D21', 'stan', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(12, 1, 21, 'H21A', 'ubytovani na louce', '', 2, 0, NULL, NULL, NULL, NULL, NULL),
(13, 1, 23, 'H', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(14, 1, 22, 'H21A', 'louka', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(16, 15, 18, 'H10', 'chci F1', 'a teba', 1, 0, NULL, NULL, NULL, NULL, NULL),
(19, 11, 16, 'H21A', 'ubytování v lese', '', 1, 0, 1, NULL, NULL, NULL, NULL),
(20, 11, 19, 'H21A', '', '', 1, 0, 0, NULL, NULL, NULL, NULL),
(21, 22, 29, 'H21B', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(22, 14, 25, 'D21', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(27, 29, 26, 'H21', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(28, 18, 26, 'H21', 'Pojedu', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(29, 28, 26, 'D21', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(30, 13, 23, 'D21', 'tohle je jen testovací přihláška:)', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(31, 27, 22, 'HDR', 'TESTING', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(34, 3, 10, 'H105', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(35, 3, 36, 'H21', 'Co nejdřív', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(41, 8, 38, 'H21D', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(40, 5, 38, 'D35', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(39, 32, 38, 'H50', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(42, 11, 38, 'D21C', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(43, 14, 38, 'H12', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(44, 2, 38, 'H21C', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(45, 1, 35, 'H21', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(46, 1, 20, 'H21', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(47, 1, 38, 'H21', '', '', 3, 0, NULL, NULL, NULL, NULL, NULL),
(48, 4, 40, 'D35A', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(49, 4, 41, 'H21C', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(50, 5, 41, 'H21C', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(51, 6, 41, 'H21C', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(52, 7, 41, 'D18', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(53, 8, 41, 'D16', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(54, 4, 42, 'H21C', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(55, 5, 42, 'H21D', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(56, 6, 42, 'H18', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(57, 7, 42, 'D21C', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(58, 8, 42, 'D16', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(103, 2, 45, 'H21B', '', '', 1, 0, 1, NULL, 0, NULL, NULL),
(60, 5, 43, 'H21D', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(61, 6, 43, 'H21C', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(62, 7, 43, 'D18', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(63, 8, 43, 'D16', '', '', 1, 0, NULL, NULL, NULL, NULL, NULL),
(64, 8, 16, 'h', '', '', 1, 0, 0, NULL, NULL, NULL, NULL),
(65, 33, 16, 'D21', '', '', 1, 0, 1, NULL, NULL, NULL, NULL),
(66, 16, 36, '12', '', '', 1, 0, 0, NULL, NULL, NULL, NULL),
(67, 16, 37, '12', '', '', 1, 0, 0, NULL, NULL, NULL, NULL),
(68, 2, 44, '123', '', '', 1, 0, 1, NULL, NULL, NULL, NULL),
(69, 21, 44, '12', '', '', 1, 0, 1, NULL, NULL, NULL, NULL),
(70, 26, 44, '122', '', '', 1, 0, 1, NULL, NULL, NULL, NULL),
(71, 16, 44, '321', '', '', 1, 0, 1, NULL, NULL, NULL, NULL),
(72, 27, 44, '1', '', '', 1, 0, 0, NULL, NULL, NULL, NULL),
(73, 34, 44, '999', '', '', 1, 0, 1, NULL, NULL, NULL, NULL),
(74, 25, 44, '45', '', '', 1, 0, 0, NULL, NULL, NULL, NULL),
(75, 14, 44, '2', '', '', 1, 0, 0, NULL, NULL, NULL, NULL),
(76, 1, 32, 'H19', '', '', 1, 0, 0, NULL, NULL, NULL, NULL),
(77, 1, 25, 'H21C', '', '', 1, 0, 0, NULL, NULL, NULL, NULL),
(78, 1, 19, 'H35', '', '', 1, 0, 0, NULL, NULL, NULL, NULL),
(79, 1, 18, 'H35', '', '', 1, 0, 0, NULL, NULL, NULL, NULL),
(80, 17, 37, '10', '', '', 1, 0, 0, NULL, NULL, NULL, NULL),
(81, 2, 37, 'H21', '', '', 1, 0, 0, NULL, NULL, NULL, NULL),
(82, 16, 16, 'H10', '', '', 1, 0, 1, NULL, NULL, NULL, NULL),
(83, 2, 16, 'D34', 'ěčřčžčýýíýéýéˇoö\'i\'u\'öïüÿ¨tëë¨q¨s¨d¨g¨h¨kj¨m¨bv¨xc¨xz¨zčďˇFˇgRěˇqˇWeťˇyúˇiKˇhˇfć', '', 1, 0, 1, NULL, NULL, NULL, NULL),
(84, 2, 19, 'D123', '\\\'\\\\\\\'adsfsad', '', 1, 0, 0, NULL, NULL, NULL, NULL),
(85, 4, 19, 'H21A', '', '', 1, 0, 0, NULL, NULL, NULL, NULL),
(100, 8, 7, 'H40', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(102, 8, 6, 'G11', '', '', 2, 0, 0, NULL, 0, NULL, NULL),
(104, 16, 4, 'D21Q', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(105, 17, 4, 'q12w', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(106, 32, 4, 'weq1', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(107, 37, 4, 'e221', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(108, 4, 29, 'D21B', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(109, 8, 29, 'H21B', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(110, 2, 27, 'a,b,c,d,a,', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(111, 28, 37, 'D12C', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(112, 4, 47, 'H55C', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(113, 5, 47, 'D35C', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(118, 6, 47, 'H35C', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(115, 7, 47, 'D18C', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(117, 8, 47, 'H18C', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(119, 11, 48, 'H21E', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(120, 34, 48, 'H21A', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(121, 27, 48, 'H21A', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(122, 2, 48, 'H16A', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(123, 8, 48, 'D21E', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(124, 15, 48, 'H21E', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(125, 26, 48, 'H21A', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(126, 28, 48, 'D21E', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(127, 29, 48, 'H21A', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(128, 8, 49, 'H21C', 'poznámka do přihlášky', 'poznámka interní', 1, 0, 1, NULL, 0, NULL, NULL),
(129, 6, 50, 'H45', 'tester 1', '', 3, 0, 0, NULL, 0, NULL, NULL),
(130, 4, 50, 'H45', '', '', 3, 0, 0, NULL, 0, NULL, NULL),
(131, 6, 13, 'D21C', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(132, 4, 11, 'H', '', '', 1, 0, 0, NULL, 0, NULL, NULL),
(133, 4, 13, 'H', '', '', 1, 0, 0, NULL, 0, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tokens`
--
ALTER TABLE `tokens`
  ADD PRIMARY KEY (`device`);

--
-- Indexes for table `tst_accounts`
--
ALTER TABLE `tst_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`),
  ADD KEY `policy_mng` (`policy_mng`),
  ADD KEY `id_users` (`id_users`);

--
-- Indexes for table `tst_bank_transactions`
--
ALTER TABLE `tst_bank_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `tst_categories_predef`
--
ALTER TABLE `tst_categories_predef`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tst_claim`
--
ALTER TABLE `tst_claim`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tst_finance`
--
ALTER TABLE `tst_finance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tst_finance_types`
--
ALTER TABLE `tst_finance_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tst_modify_log`
--
ALTER TABLE `tst_modify_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tst_news`
--
ALTER TABLE `tst_news`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sort_datum` (`datum`,`id`);

--
-- Indexes for table `tst_payment_rules`
--
ALTER TABLE `tst_payment_rules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tst_users`
--
ALTER TABLE `tst_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name2` (`sort_name`),
  ADD KEY `chief_id` (`chief_id`);

--
-- Indexes for table `tst_xmailinfo`
--
ALTER TABLE `tst_xmailinfo`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tst_zavod`
--
ALTER TABLE `tst_zavod`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `tst_zavod` ADD FULLTEXT KEY `misto` (`misto`);

--
-- Indexes for table `tst_zavxus`
--
ALTER TABLE `tst_zavxus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_termin` (`termin`,`id`),
  ADD KEY `id_user` (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tst_accounts`
--
ALTER TABLE `tst_accounts`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `tst_bank_transactions`
--
ALTER TABLE `tst_bank_transactions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tst_categories_predef`
--
ALTER TABLE `tst_categories_predef`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tst_claim`
--
ALTER TABLE `tst_claim`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tst_finance`
--
ALTER TABLE `tst_finance`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=178;

--
-- AUTO_INCREMENT for table `tst_finance_types`
--
ALTER TABLE `tst_finance_types`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tst_modify_log`
--
ALTER TABLE `tst_modify_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=464;

--
-- AUTO_INCREMENT for table `tst_news`
--
ALTER TABLE `tst_news`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- AUTO_INCREMENT for table `tst_payment_rules`
--
ALTER TABLE `tst_payment_rules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tst_users`
--
ALTER TABLE `tst_users`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `tst_xmailinfo`
--
ALTER TABLE `tst_xmailinfo`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tst_zavod`
--
ALTER TABLE `tst_zavod`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `tst_zavxus`
--
ALTER TABLE `tst_zavxus`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

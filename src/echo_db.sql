-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 11, 2021 at 11:21 AM
-- Server version: 10.3.23-MariaDB-cll-lve
-- PHP Version: 7.3.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


-- --------------------------------------------------------

--
-- Table structure for table `commands`
--

DROP TABLE IF EXISTS `commands`;
CREATE TABLE IF NOT EXISTS `commands` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `mac` varchar(6) NOT NULL,
  `command` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mac` (`mac`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- --------------------------------------------------------

--
-- Table structure for table `readers`
--

DROP TABLE IF EXISTS `readers`;
CREATE TABLE IF NOT EXISTS `readers` (
  `mac` varchar(6) NOT NULL,
  `name` varchar(20) NOT NULL DEFAULT "New Reader",
  `location` varchar(20) NOT NULL DEFAULT "Not Set",
  `last_update` timestamp NOT NULL DEFAULT current_timestamp(),
  `reader_status` tinyint(1) NOT NULL,
  `battery_status` int(3) NOT NULL,
  PRIMARY KEY (`mac`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- --------------------------------------------------------

--
-- Table structure for table `timing_data`
--

DROP TABLE IF EXISTS `timing_data`;
CREATE TABLE IF NOT EXISTS `timing_data` (
  `id` varchar(20) NOT NULL,
  `mac` varchar(6) NOT NULL,
  `chip` bigint(20) NOT NULL,
  `time` bigint(20) NOT NULL,
  `millis` varchar(3) NOT NULL,
  `antenna` int(11) NOT NULL,
  `reader` int(11) NOT NULL,
  `logid` bigint(20) NOT NULL,
  `timestamp` timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`id`),
  KEY `mac` (`mac`),
  KEY `MAC-n-Time Index` (`time`,`mac`) USING BTREE,
  KEY `timestamp` (`timestamp`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

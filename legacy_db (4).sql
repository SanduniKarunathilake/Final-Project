-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 07, 2025 at 01:47 PM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `legacy_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbladmin`
--

DROP TABLE IF EXISTS `tbladmin`;
CREATE TABLE IF NOT EXISTS `tbladmin` (
  `AID` varchar(10) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Address` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Age` int NOT NULL,
  `TeleNum` text NOT NULL,
  `pwd` text NOT NULL,
  `Type` varchar(50) NOT NULL,
  PRIMARY KEY (`AID`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbladmin`
--

INSERT INTO `tbladmin` (`AID`, `Name`, `Address`, `Email`, `Age`, `TeleNum`, `pwd`, `Type`) VALUES
('A788888888', 'sandu', '', 'uuuuuuuu@gamil.com', 32, '55555555555', '2003', 'Active'),
('A788888856', 'sandu', 'colombo', 'g@gmail.com', 34, '456789023', '7yuuuuuu', 'Inactive'),
('AA20037045', 'Sanduni', 'galle', 'sandunikarunathilake2003@gmail.com', 23, '0809765476', '2003073124', 'Inactive'),
('A200456788', 'Sithara', 'kandy', 'kalana@gmail.com', 23, '0794567432', '2008765', 'Inactive'),
('A200456785', 'Hiruni', 'hambantota', 'hiru@gmail.com', 24, '07089657412', '', 'Inactive'),
('A2002', 'sithara', 'galle', 'thara@gmail.com', 22, '1234567890', '1234', 'Active'),
('A126456788', 'thara', 'hahhh', 'thrya@gmail.com', 15, '123654789', 'wathsal', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `tblcoach`
--

DROP TABLE IF EXISTS `tblcoach`;
CREATE TABLE IF NOT EXISTS `tblcoach` (
  `CID` varchar(10) NOT NULL,
  `TeleNum` text NOT NULL,
  `Address` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Age` int NOT NULL,
  `Sport` varchar(50) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Type` varchar(20) NOT NULL,
  `Qualific` varchar(100) NOT NULL,
  `pwd` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `Status` varchar(10) NOT NULL,
  PRIMARY KEY (`CID`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tblcoach`
--

INSERT INTO `tblcoach` (`CID`, `TeleNum`, `Address`, `Email`, `Age`, `Sport`, `Name`, `Type`, `Qualific`, `pwd`, `Status`) VALUES
('C200324354', '070312549876', 'kithulampitiya rd galle', 'priyanga@gmail.com', 47, 'Tennis', 'Priyanga Nagodavithana', 'Individual Session', 'ifjwjwjjjwj', 'priyanga', 'Active'),
('C200370311', '0708956477', 'kandy road mawanalla', 'thishakya@gmail.com', 34, 'Chess', 'Adhil Thishakya', 'Group Session', 'grand master in chess', 'thishakya', 'Inactive'),
('C200283901', '12345678910', 'galle', 'sithara12333@gmail.com', 22, 'Tennis', 'sithara kavindi', 'Individual Session', 'grand master', '77777777', 'Active'),
('C200371311', '0703004535', 'Kandy', 'wathsal@gmail.com', 34, 'Swimming', 'Ayuka wathsal', 'Group Session', 'grandmaster', '34rtyyt', 'Inactive'),
('C123456788', '07089123456', 'hahhh', 'thrya@gmail.com', 23, 'Badminton', 'thurya', 'Group Session', 'uiiii', 'fhhhhhh', ''),
('C200213256', '1593574862', 'nuwara', 'thn@gmail.com', 22, 'Chess', 'sasi', 'Individual Session', 'bla bla', '123456', '');

-- --------------------------------------------------------

--
-- Table structure for table `tbldonation`
--

DROP TABLE IF EXISTS `tbldonation`;
CREATE TABLE IF NOT EXISTS `tbldonation` (
  `DID` int NOT NULL AUTO_INCREMENT,
  `Date` date NOT NULL,
  `Amount` double NOT NULL,
  `SDID` varchar(10) NOT NULL,
  `PID` varchar(10) NOT NULL,
  PRIMARY KEY (`DID`),
  UNIQUE KEY `SDID` (`SDID`,`PID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbldonation`
--

INSERT INTO `tbldonation` (`DID`, `Date`, `Amount`, `SDID`, `PID`) VALUES
(1, '2025-05-07', 788888, 'D988844333', 'P019');

-- --------------------------------------------------------

--
-- Table structure for table `tblpayment`
--

DROP TABLE IF EXISTS `tblpayment`;
CREATE TABLE IF NOT EXISTS `tblpayment` (
  `PayID` varchar(10) NOT NULL,
  `Amount` double NOT NULL,
  `Date` date NOT NULL,
  `PID` varchar(10) NOT NULL,
  PRIMARY KEY (`PayID`),
  UNIQUE KEY `PID` (`PID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblplayer`
--

DROP TABLE IF EXISTS `tblplayer`;
CREATE TABLE IF NOT EXISTS `tblplayer` (
  `PID` varchar(10) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Address` varchar(100) NOT NULL,
  `Email` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `Age` int NOT NULL,
  `Sport` varchar(50) NOT NULL,
  `pwd` text NOT NULL,
  `TeleNum` text NOT NULL,
  `Type` varchar(50) NOT NULL,
  `GNCertifi` varchar(100) NOT NULL,
  `Status` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `CID` varchar(50) NOT NULL,
  `MedReco` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`PID`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tblplayer`
--

INSERT INTO `tblplayer` (`PID`, `Name`, `Address`, `Email`, `Age`, `Sport`, `pwd`, `TeleNum`, `Type`, `GNCertifi`, `Status`, `CID`, `MedReco`) VALUES
('P670000000', 'Ayuka', 'kandy', 'ayuka@gmail.com', 24, 'Tennis', 'wathsal', '0723456789', 'after_school', '', 'active', '', ''),
('P123456789', 'aryan', 'kandy', 'aryan@gmail.com', 24, 'Badminton', '123456', '0712549303', 'school', '', 'active', '', ''),
('P345678910', 'saumya', 'hambantota', 'saumya@gmail.com', 23, 'Chess', 'trrrrrr', '1234567', 'school', '', 'inactiv', '', ''),
('P200455566', 'Hiruni Pramodya', 'galle', 'pramodya@gamil.com', 21, 'Badminton', 'pra12345', '0701911306', 'school', '', 'active', '', ''),
('P200370131', 'NIrmal Karunathilaka', 'jaffna', 'nirmal@gmail.com', 27, 'Badminton', 'Nirmal2003', '071456790', 'school', '', 'yes', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `tblplayer_coach`
--

DROP TABLE IF EXISTS `tblplayer_coach`;
CREATE TABLE IF NOT EXISTS `tblplayer_coach` (
  `BDate` date NOT NULL,
  `Duration` varchar(500) NOT NULL,
  `PID` varchar(10) NOT NULL,
  `CID` varchar(10) NOT NULL,
  `Feedback` text NOT NULL,
  UNIQUE KEY `PID` (`PID`,`CID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tblplayer_coach`
--

INSERT INTO `tblplayer_coach` (`BDate`, `Duration`, `PID`, `CID`, `Feedback`) VALUES
('2025-04-22', '0000-00-00 00:00:00', 'P019', 'C012', 'need practise'),
('2025-04-11', '0000-00-00 00:00:00', 'P0999', 'C888', 'good'),
('2025-04-24', '0000-00-00 00:00:00', 'P020', 'C021', 'Excellent'),
('2025-04-11', '0000-00-00 00:00:00', 'P10000', 'C199999', 'should improve a lot'),
('2025-04-10', '3 weeks', 'P200370311', 'C200234541', 'have a good strength'),
('2025-04-22', '0000-00-00 00:00:00', 'P019', 'C011', 'need practise');

-- --------------------------------------------------------

--
-- Table structure for table `tblplayer_spon_donr`
--

DROP TABLE IF EXISTS `tblplayer_spon_donr`;
CREATE TABLE IF NOT EXISTS `tblplayer_spon_donr` (
  `Date` date NOT NULL,
  `Amount` double NOT NULL,
  `PID` varchar(10) NOT NULL,
  `SDID` varchar(10) NOT NULL,
  UNIQUE KEY `PID` (`PID`,`SDID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tblplayer_spon_donr`
--

INSERT INTO `tblplayer_spon_donr` (`Date`, `Amount`, `PID`, `SDID`) VALUES
('2025-05-26', 100, 'tT002', 'D002'),
('2025-05-14', 49999999, 'P200370311', 'D345879600'),
('2025-05-27', 8999999999, 'P200370311', 'D988844333');

-- --------------------------------------------------------

--
-- Table structure for table `tblplayer_tournmt`
--

DROP TABLE IF EXISTS `tblplayer_tournmt`;
CREATE TABLE IF NOT EXISTS `tblplayer_tournmt` (
  `Date` date NOT NULL,
  `PID` varchar(10) NOT NULL,
  `TID` varchar(10) NOT NULL,
  `Details` varchar(500) NOT NULL,
  UNIQUE KEY `PID` (`PID`,`TID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblschedule`
--

DROP TABLE IF EXISTS `tblschedule`;
CREATE TABLE IF NOT EXISTS `tblschedule` (
  `SchID` int NOT NULL AUTO_INCREMENT,
  `Dtls` varchar(100) NOT NULL,
  `Time` time(6) NOT NULL,
  `Date` date NOT NULL,
  `CID` varchar(10) NOT NULL,
  PRIMARY KEY (`SchID`),
  UNIQUE KEY `CID` (`CID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tblschedule`
--

INSERT INTO `tblschedule` (`SchID`, `Dtls`, `Time`, `Date`, `CID`) VALUES
(1, 'karate', '06:52:00.000000', '2025-03-31', ''),
(2, 'chess practise', '06:46:00.000000', '2025-05-14', 'C012');

-- --------------------------------------------------------

--
-- Table structure for table `tbltournament`
--

DROP TABLE IF EXISTS `tbltournament`;
CREATE TABLE IF NOT EXISTS `tbltournament` (
  `TID` varchar(100) NOT NULL,
  `Sport` varchar(100) NOT NULL,
  `Tname` varchar(100) NOT NULL,
  `Picture` varchar(1000) NOT NULL,
  `link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `Description` varchar(1000) NOT NULL,
  PRIMARY KEY (`TID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbltournament`
--

INSERT INTO `tbltournament` (`TID`, `Sport`, `Tname`, `Picture`, `link`, `Description`) VALUES
('T003', 'swimming', 'eran eager swimming championship', 'swimming.jpg', 'https://onlineregistration.cc/', 'International game'),
('T004', 'chess', 'alice accedamy', 'chess.png', 'https://onlineregistration.cc/', 'Nationat champion'),
('T005', 'chess', 'southern lanka', 'c3.jpg', 'https://onlineregistration.cc/', 'test match with national team'),
('T008', 'rugby', 'aron rugby championship', 'ru.png', 'https://www.asiarugby.com/unions/sri-lanka/', 'National Rugby tournament');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_sponsor_donor`
--

DROP TABLE IF EXISTS `tbl_sponsor_donor`;
CREATE TABLE IF NOT EXISTS `tbl_sponsor_donor` (
  `SDID` varchar(100) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Address` text NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Age` int NOT NULL,
  `pwd` text NOT NULL,
  `Type` varchar(50) NOT NULL,
  `TeleNum` text NOT NULL,
  `Status` varchar(10) NOT NULL,
  PRIMARY KEY (`SDID`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_sponsor_donor`
--

INSERT INTO `tbl_sponsor_donor` (`SDID`, `Name`, `Address`, `Email`, `Age`, `pwd`, `Type`, `TeleNum`, `Status`) VALUES
('D3458796000', 'YUUU', 'SDDD', 'H@gmail.com', 45, '34RTTTT', 'sponsorships', '0703224402', 'Active'),
('D200298635269', 'kavindi', 'mathara', 'hhh@gmail.com', 32, '20021204', 'donate', '7419638520', 'Active');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

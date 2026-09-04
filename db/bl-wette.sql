/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.18-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: bl_wette
-- ------------------------------------------------------
-- Server version	10.11.18-MariaDB-0+deb12u1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `tblgamer`
--

DROP TABLE IF EXISTS `tblgamer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblgamer` (
  `lngIndex` int(11) NOT NULL AUTO_INCREMENT,
  `strAlias` varchar(100) NOT NULL DEFAULT '',
  `strEmail` varchar(100) DEFAULT NULL,
  `strPasswort` varchar(100) DEFAULT NULL,
  `intPoint` int(11) NOT NULL DEFAULT 0,
  `strTeamname` varchar(255) DEFAULT NULL,
  `intMoney` int(10) unsigned DEFAULT 40,
  `boolRemember` tinyint(4) NOT NULL DEFAULT 0,
  `intFormelPoint` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`lngIndex`)
) ENGINE=MyISAM AUTO_INCREMENT=54 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblgamer`
--

LOCK TABLES `tblgamer` WRITE;
/*!40000 ALTER TABLE `tblgamer` DISABLE KEYS */;
INSERT INTO `tblgamer` VALUES
(21,'Fischy','ralf.hering@freenet.de','165a5b6edf23ce35207a0afbd92e641f',11,NULL,0,0,0),
(16,'Roland','roland.prinz@online.de',NULL,10,NULL,0,0,0),
(17,'Uta','uta.liebeck@freenet.de',NULL,13,NULL,0,1,0),
(15,'Beate','beate.zuehlke@online.de','917fa02c3e25b59a6aba2aafe93ad687',6,NULL,0,0,0),
(23,'Det','detlef.feldmeier@freenet.de',NULL,13,NULL,0,0,0),
(26,'Andre','',NULL,9,NULL,0,0,0),
(27,'Joe','joerg_achenbach@web.de','9d4eb0def0393ece24159d514743ccda',8,NULL,0,0,0),
(33,'Reneee','Rene',NULL,2,NULL,0,0,0),
(50,'Arne','',NULL,12,NULL,0,0,0),
(40,'Hebi','',NULL,14,NULL,0,0,0),
(47,'Elke','',NULL,8,NULL,0,0,0),
(46,'bubi','juergen@buerhop.de',NULL,8,NULL,0,1,0),
(49,'Carsten','carsten.honemann@t-online.de','a6a5f39a09f58731c672eaa4a0565e55',9,NULL,0,0,0),
(51,'Volker','Volker','d5538c3cdd9acb41dd9bc8f9a6a95ce7',10,NULL,0,0,0),
(52,'Dennis','polster@web.de','23940e287e0c8ff53c671e1cf335f995',5,NULL,0,1,0);
/*!40000 ALTER TABLE `tblgamer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblinfo`
--

DROP TABLE IF EXISTS `tblinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblinfo` (
  `intDay` int(11) NOT NULL DEFAULT 0,
  `dtmClose` datetime DEFAULT NULL,
  `dtmEndOfMatchDay` datetime DEFAULT NULL,
  PRIMARY KEY (`intDay`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblinfo`
--

LOCK TABLES `tblinfo` WRITE;
/*!40000 ALTER TABLE `tblinfo` DISABLE KEYS */;
INSERT INTO `tblinfo` VALUES
(2,'2026-09-04 20:30:00','2026-09-06 19:30:00');
/*!40000 ALTER TABLE `tblinfo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblmessage`
--

DROP TABLE IF EXISTS `tblmessage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblmessage` (
  `lngIndex` int(11) NOT NULL AUTO_INCREMENT,
  `lngUserFromID` int(11) unsigned NOT NULL DEFAULT 0,
  `lngUserToID` int(11) unsigned NOT NULL DEFAULT 0,
  `strMessage` text NOT NULL,
  `dtmCreate` datetime NOT NULL DEFAULT '2004-03-18 12:41:33',
  `ysnRead` tinyint(4) unsigned NOT NULL DEFAULT 0,
  `strTitle` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`lngIndex`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblmessage`
--

LOCK TABLES `tblmessage` WRITE;
/*!40000 ALTER TABLE `tblmessage` DISABLE KEYS */;
INSERT INTO `tblmessage` VALUES
(1,46,16,'wollte nur mal testen, ob und wie das geht.<br />\r\nalso, wenn du einen tipp für deinen tipp brauchst?<br />\r\nbayern-schlke spielen unendschieden, es sei denn, ich tippe was anderes ;-)<br />\r\ngruss<br />\r\njürgen','2009-11-02 21:40:18',0,'nah du spasstipper'),
(5,46,16,'Moin Roland<br />\r\nWie sieht das mit der Tabellen-aktualisierung aus?<br />\r\nImmer Freitags 17:23Uhr ???<br />\r\nMontags einen Kaffee weniger und dafür die Ergebnisse eingeben, oder es ermöglichen, das einer von uns das macht. Es würden sich mehrere für den Service anbieten. Det, Hagen, Ich, ...<br />\r\n\"Die ewig wartenden grüßen dich\"<br />\r\nJürgen','2010-10-27 13:30:31',1,'Aktualisierung'),
(3,46,16,'Wie sieht es hinterm Hotizont aus. Hab mein Fernglas nicht dabei. Warte in 4 Wochen an der Ziellinie auf dich. Falls du ankommst.','2010-04-14 16:13:36',1,'Welche nähe?'),
(4,46,16,'Warum ist die Rankingliste immer aus dem letzten Jahrhundert? War mal anders. Faule Socke...','2010-04-14 16:15:33',1,'Wochenendupdate??!'),
(6,16,46,'Halt die Füße still Jürgen. Durch den Umzug funktionierte die Auswertungsseite nicht mehr richtig. <br />\r\nKonnte ich leider erst heute geradeziehen, ab jetzt wieder den, von mir bekannten und zuverlässigen Service.<br />\r\n<br />\r\nGruß Roland','2010-10-29 13:30:07',1,'Aktualisierung'),
(7,46,27,'Hallo Herr Achenbach,<br />\r\nnach  Auswertung, durch den Videoschiedsrichter, mÃ¼ssen wir ihnen 4Pkt. abziehen. Es war eindeutig, ein selbstgefÃ¤lliges Grinsen, bei der Niederlage des SV Werder Bremen und ein freudiges Schmunzeln, bei dem Sieg des FC Bayern MÃ¼nchen, zu erkennen. Angemessen wÃ¤re ein leidend widerwilliger Ausdruck, wie bei dem Sportsfreund J.Buerhop, bei Entgegennahme seiner wohlverdienten Punkte, gewesen. Nehmen sie sich ein Beispiel an ihm!<br />\r\nDes Weiteren kÃ¼ndigen wir Repressalien an, die ihre Manie zu Bayern-UnterwÃ¤sche und Bayern-BettwÃ¤sche betreffen. Diese werden umgesetzt, wenn sie Ã¼berhaupt nicht damit rechnen!<br />\r\nZusammengefasst bedeutet das fÃ¼r sie: HAU AB DA !!!<br />\r\nSportliche GrÃ¼ÃŸe<br />\r\nDFB-Inquisitionsabteilung<br />\r\nGez. Inquisitor Heinrich Kramer','2018-12-17 10:28:06',0,'Hau ab da!!!');
/*!40000 ALTER TABLE `tblmessage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblsession`
--

DROP TABLE IF EXISTS `tblsession`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblsession` (
  `strSessionId` varchar(100) NOT NULL DEFAULT '',
  `intUser` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`strSessionId`,`intUser`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblsession`
--

LOCK TABLES `tblsession` WRITE;
/*!40000 ALTER TABLE `tblsession` DISABLE KEYS */;
INSERT INTO `tblsession` VALUES
('00e9f5f7a1dcdb4e4bbf00781f317e90',21),
('05ee81085bb9a40a4c6254de56174508',49),
('097bb71f32b2e15f9703d1a4f5f94757',51),
('0bb68296a0e60d1af1338ad16bfb1ab8',23),
('0ed4478b583f6ae234e7bc66a076456e',46),
('0edb3cf52954dda0c05744d65c775b87',50),
('0fa22b5b2b64e79fb27262c20c29f2ab',33),
('10d6d2056cfe03ad6e99bd60a3304ea3',52),
('13b589577532b6209efce1d599537fad',17),
('15d3383e952965b79df8b949233dc62b',40),
('164226913457d6accc749bd6d60a2d44',15),
('1786767c363a860f96fe12b8c92ca567',52),
('24e6d6c5ebb1a1f915c4d2f311a48d08',23),
('28f1f2b072730725e90b2a5a1bd06a45',40),
('291911f775a090ef409f5daa81ee600f',17),
('3416ad2f29ba1b78a35c070db3652ba5',51),
('37ed2895c280f7011c53c65b87ed9e47',33),
('3df858ae46ab389a048fafa659e2b61d',16),
('4073d652071fa64c87b87856c447e358',16),
('44c03dee459b2ccd5f8d14351f3c10d0',21),
('45a9013cd757cdd9aabde03ca25dcbe9',50),
('484c2e65db8ccf823784bc0c2b21f9f5',50),
('4a489950e32119dba29cc1e120a5ac97',16),
('4c439fedd3aef25986d1560a44278d2f',47),
('4cc4955db15d9fd37cb726cef0c63428',40),
('5abee51bd5b91b09e2773154e05ceddc',21),
('5b2529a2afd4f2eb5db3901f77a7a62e',21),
('5de4b6a77861022859454ab5d81ce85e',49),
('5e9df09a718ce2d195ae83c5ee910d68',51),
('6f52262c57b44d903704c640db8160e8',17),
('73157dcee6a00a103b841252ee2d62cf',40),
('76654d13353fb3e7bb7d0f96e1a58470',17),
('7c3737d37795d348937cd3d7546c83a4',17),
('7e72206b2f4834f1e9cd4a980ece9257',21),
('813b5ad50111ff872db5a27b01992afd',51),
('861ce558378f822f83a144c68f8aae08',15),
('880c12a1016aed13ce10ce72d8f97f4b',51),
('8fe9651e3b7fb6a1842d0fe1c1b8c422',16),
('9554039209a4ee10475eba37eb147689',51),
('9e1fb9b109fbaa24fc103ad0061e3f4c',46),
('9ec47c75c5be27098aa79eb6b78d96af',51),
('a6a42cd9e9bd32b4a4b334b69e98e60e',33),
('a7138319b0ac845eb96872a6f1c3e836',27),
('aa3b2b884b993a6fc2e525da9821317d',33),
('b2abe81e60df0d8680498eb0643d3bab',51),
('b3600eca544ba3f224a552ea1cb648c7',33),
('c8808add31a73989eb27ac34890e9beb',21),
('ca6e06bf075a9a901d394991eca8633f',51),
('cd2908a6ac2ed327841771993ef9a0f8',23),
('cd44a3d95a9351502572ce72cfb9d7e8',51),
('cfd7423f819157ac8302a8387b95db0d',46),
('d105050303d8f16bd21229320136e3bb',33),
('d4a2431e96605d35e81f0b837b01181c',52),
('d9a5163a40d2104a7f14aa58eb939c25',33),
('da5941cf9ba0c5685f90bb29062cd200',16),
('dec90bd6024d4c1451b5f2e8e2f9e228',16),
('e0fa8443458007c6288391965aa24126',50),
('f027bcb3f1ed56969741c6afdef3ce49',26),
('f20a7f9a1544aaf6887ed8adf1313d01',49),
('f378a36cc83195712e1ee9d6b31b6769',33),
('f4e2ac33e7596d4de7847e24fb52186d',17),
('f668af00149e216f173c930f8e72852e',50),
('f7d67bdbc0186a4695321b0ac19df0e7',50);
/*!40000 ALTER TABLE `tblsession` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblspieltag`
--

DROP TABLE IF EXISTS `tblspieltag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblspieltag` (
  `lngIndex` int(11) NOT NULL AUTO_INCREMENT,
  `intVerein1` int(11) NOT NULL DEFAULT 0,
  `intVerein2` int(11) NOT NULL DEFAULT 0,
  `intGoal1` int(11) NOT NULL DEFAULT 0,
  `intGoal2` int(11) NOT NULL DEFAULT 0,
  `intTag` int(11) NOT NULL DEFAULT 0,
  `intStatus` int(11) NOT NULL DEFAULT 0,
  `strSpielbericht` varchar(99) DEFAULT NULL,
  `intMatchIdFromOpenLigaDB` int(11) DEFAULT NULL,
  PRIMARY KEY (`lngIndex`)
) ENGINE=MyISAM AUTO_INCREMENT=307 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblspieltag`
--

LOCK TABLES `tblspieltag` WRITE;
/*!40000 ALTER TABLE `tblspieltag` DISABLE KEYS */;
INSERT INTO `tblspieltag` VALUES
(1,12,6,5,1,1,2,NULL,83156),
(2,18,3,3,0,1,2,NULL,83158),
(3,15,65,0,0,1,2,NULL,83161),
(4,8,1,3,3,1,2,NULL,83162),
(5,16,14,3,2,1,2,NULL,83163),
(6,2,10,3,2,1,2,NULL,83164),
(7,7,11,2,0,1,2,NULL,83157),
(8,4,17,4,1,1,2,NULL,83159),
(9,5,9,3,0,1,2,NULL,83160),
(10,6,16,0,0,2,0,NULL,83165),
(11,14,7,0,0,2,0,NULL,83166),
(12,10,8,0,0,2,0,NULL,83167),
(13,3,2,0,0,2,0,NULL,83169),
(14,17,18,0,0,2,0,NULL,83171),
(15,65,4,0,0,2,0,NULL,83173),
(16,9,12,0,0,2,0,NULL,83172),
(17,11,15,0,0,2,0,NULL,83170),
(18,1,5,0,0,2,0,NULL,83168),
(19,8,9,0,0,3,0,NULL,83180),
(20,7,65,0,0,3,0,NULL,83174),
(21,14,6,0,0,3,0,NULL,83176),
(22,4,3,0,0,3,0,NULL,83177),
(23,5,10,0,0,3,0,NULL,83178),
(24,15,1,0,0,3,0,NULL,83179),
(25,16,17,0,0,3,0,NULL,83181),
(26,18,11,0,0,3,0,NULL,83175),
(27,2,12,0,0,3,0,NULL,83182),
(28,12,8,0,0,4,0,NULL,83183),
(29,1,4,0,0,4,0,NULL,83186),
(30,3,15,0,0,4,0,NULL,83187),
(31,11,16,0,0,4,0,NULL,83188),
(32,17,5,0,0,4,0,NULL,83189),
(33,6,7,0,0,4,0,NULL,83184),
(34,10,18,0,0,4,0,NULL,83185),
(35,9,2,0,0,4,0,NULL,83190),
(36,65,14,0,0,4,0,NULL,83191),
(37,7,17,0,0,5,0,NULL,83192),
(38,18,1,0,0,5,0,NULL,83193),
(39,14,11,0,0,5,0,NULL,83194),
(40,4,9,0,0,5,0,NULL,83195),
(41,5,12,0,0,5,0,NULL,83196),
(42,15,10,0,0,5,0,NULL,83197),
(43,8,2,0,0,5,0,NULL,83198),
(44,16,3,0,0,5,0,NULL,83199),
(45,65,6,0,0,5,0,NULL,83200),
(46,12,18,0,0,6,0,NULL,83201),
(47,10,4,0,0,6,0,NULL,83202),
(48,1,16,0,0,6,0,NULL,83203),
(49,8,7,0,0,6,0,NULL,83204),
(50,3,14,0,0,6,0,NULL,83205),
(51,11,6,0,0,6,0,NULL,83206),
(52,17,65,0,0,6,0,NULL,83207),
(53,9,15,0,0,6,0,NULL,83208),
(54,2,5,0,0,6,0,NULL,83209),
(55,7,1,0,0,7,0,NULL,83210),
(56,18,2,0,0,7,0,NULL,83211),
(57,6,3,0,0,7,0,NULL,83212),
(58,14,10,0,0,7,0,NULL,83213),
(59,4,12,0,0,7,0,NULL,83214),
(60,5,8,0,0,7,0,NULL,83215),
(61,15,17,0,0,7,0,NULL,83216),
(62,16,9,0,0,7,0,NULL,83217),
(63,65,11,0,0,7,0,NULL,83218),
(64,12,7,0,0,8,0,NULL,83219),
(65,10,6,0,0,8,0,NULL,83220),
(66,1,11,0,0,8,0,NULL,83221),
(67,5,4,0,0,8,0,NULL,83222),
(68,8,16,0,0,8,0,NULL,83223),
(69,3,65,0,0,8,0,NULL,83224),
(70,17,14,0,0,8,0,NULL,83225),
(71,9,18,0,0,8,0,NULL,83226),
(72,2,15,0,0,8,0,NULL,83227),
(73,7,2,0,0,9,0,NULL,83228),
(74,18,5,0,0,9,0,NULL,83229),
(75,6,17,0,0,9,0,NULL,83230),
(76,14,9,0,0,9,0,NULL,83231),
(77,4,8,0,0,9,0,NULL,83232),
(78,15,12,0,0,9,0,NULL,83233),
(79,11,3,0,0,9,0,NULL,83234),
(80,16,10,0,0,9,0,NULL,83235),
(81,65,1,0,0,9,0,NULL,83236),
(82,12,16,0,0,10,0,NULL,83237),
(83,10,65,0,0,10,0,NULL,83238),
(84,1,14,0,0,10,0,NULL,83239),
(85,5,15,0,0,10,0,NULL,83240),
(86,8,18,0,0,10,0,NULL,83241),
(87,3,7,0,0,10,0,NULL,83242),
(88,17,11,0,0,10,0,NULL,83243),
(89,9,6,0,0,10,0,NULL,83244),
(90,2,4,0,0,10,0,NULL,83245),
(91,7,10,0,0,11,0,NULL,83246),
(92,6,1,0,0,11,0,NULL,83247),
(93,14,5,0,0,11,0,NULL,83248),
(94,4,18,0,0,11,0,NULL,83249),
(95,15,8,0,0,11,0,NULL,83250),
(96,11,12,0,0,11,0,NULL,83251),
(97,16,2,0,0,11,0,NULL,83252),
(98,17,3,0,0,11,0,NULL,83253),
(99,65,9,0,0,11,0,NULL,83254),
(100,12,65,0,0,12,0,NULL,83255),
(101,18,15,0,0,12,0,NULL,83256),
(102,10,3,0,0,12,0,NULL,83257),
(103,4,14,0,0,12,0,NULL,83258),
(104,1,17,0,0,12,0,NULL,83259),
(105,5,16,0,0,12,0,NULL,83260),
(106,8,11,0,0,12,0,NULL,83261),
(107,9,7,0,0,12,0,NULL,83262),
(108,2,6,0,0,12,0,NULL,83263),
(109,7,5,0,0,13,0,NULL,83264),
(110,6,8,0,0,13,0,NULL,83265),
(111,14,12,0,0,13,0,NULL,83266),
(112,15,4,0,0,13,0,NULL,83267),
(113,3,1,0,0,13,0,NULL,83268),
(114,11,9,0,0,13,0,NULL,83269),
(115,16,18,0,0,13,0,NULL,83270),
(116,17,10,0,0,13,0,NULL,83271),
(117,65,2,0,0,13,0,NULL,83272),
(118,12,17,0,0,14,0,NULL,83273),
(119,18,7,0,0,14,0,NULL,83274),
(120,10,1,0,0,14,0,NULL,83275),
(121,4,6,0,0,14,0,NULL,83276),
(122,5,65,0,0,14,0,NULL,83277),
(123,15,16,0,0,14,0,NULL,83278),
(124,8,14,0,0,14,0,NULL,83279),
(125,9,3,0,0,14,0,NULL,83280),
(126,2,11,0,0,14,0,NULL,83281),
(127,7,15,0,0,15,0,NULL,83282),
(128,6,5,0,0,15,0,NULL,83283),
(129,14,2,0,0,15,0,NULL,83284),
(130,1,9,0,0,15,0,NULL,83285),
(131,3,12,0,0,15,0,NULL,83286),
(132,11,10,0,0,15,0,NULL,83287),
(133,16,4,0,0,15,0,NULL,83288),
(134,17,8,0,0,15,0,NULL,83289),
(135,65,18,0,0,15,0,NULL,83290),
(136,12,10,0,0,16,0,NULL,83291),
(137,18,6,0,0,16,0,NULL,83292),
(138,4,11,0,0,16,0,NULL,83293),
(139,5,3,0,0,16,0,NULL,83294),
(140,15,14,0,0,16,0,NULL,83295),
(141,8,65,0,0,16,0,NULL,83296),
(142,16,7,0,0,16,0,NULL,83297),
(143,9,17,0,0,16,0,NULL,83298),
(144,2,1,0,0,16,0,NULL,83299),
(145,7,4,0,0,17,0,NULL,83300),
(146,6,15,0,0,17,0,NULL,83301),
(147,14,18,0,0,17,0,NULL,83302),
(148,10,9,0,0,17,0,NULL,83303),
(149,1,12,0,0,17,0,NULL,83304),
(150,3,8,0,0,17,0,NULL,83305),
(151,11,5,0,0,17,0,NULL,83306),
(152,17,2,0,0,17,0,NULL,83307),
(153,65,16,0,0,17,0,NULL,83308),
(154,6,12,0,0,18,0,NULL,83309),
(155,14,16,0,0,18,0,NULL,83310),
(156,10,2,0,0,18,0,NULL,83311),
(157,1,8,0,0,18,0,NULL,83312),
(158,3,18,0,0,18,0,NULL,83313),
(159,11,7,0,0,18,0,NULL,83314),
(160,17,4,0,0,18,0,NULL,83315),
(161,9,5,0,0,18,0,NULL,83316),
(162,65,15,0,0,18,0,NULL,83317),
(163,12,9,0,0,19,0,NULL,83318),
(164,7,14,0,0,19,0,NULL,83319),
(165,18,17,0,0,19,0,NULL,83320),
(166,4,65,0,0,19,0,NULL,83321),
(167,5,1,0,0,19,0,NULL,83322),
(168,15,11,0,0,19,0,NULL,83323),
(169,8,10,0,0,19,0,NULL,83324),
(170,16,6,0,0,19,0,NULL,83325),
(171,2,3,0,0,19,0,NULL,83326),
(172,12,2,0,0,20,0,NULL,83327),
(173,6,14,0,0,20,0,NULL,83328),
(174,10,5,0,0,20,0,NULL,83329),
(175,1,15,0,0,20,0,NULL,83330),
(176,3,4,0,0,20,0,NULL,83331),
(177,11,18,0,0,20,0,NULL,83332),
(178,17,16,0,0,20,0,NULL,83333),
(179,9,8,0,0,20,0,NULL,83334),
(180,65,7,0,0,20,0,NULL,83335),
(181,7,6,0,0,21,0,NULL,83336),
(182,18,10,0,0,21,0,NULL,83337),
(183,14,65,0,0,21,0,NULL,83338),
(184,4,1,0,0,21,0,NULL,83339),
(185,5,17,0,0,21,0,NULL,83340),
(186,15,3,0,0,21,0,NULL,83341),
(187,8,12,0,0,21,0,NULL,83342),
(188,16,11,0,0,21,0,NULL,83343),
(189,2,9,0,0,21,0,NULL,83344),
(190,12,5,0,0,22,0,NULL,83345),
(191,6,65,0,0,22,0,NULL,83346),
(192,10,15,0,0,22,0,NULL,83347),
(193,1,18,0,0,22,0,NULL,83348),
(194,3,16,0,0,22,0,NULL,83349),
(195,11,14,0,0,22,0,NULL,83350),
(196,17,7,0,0,22,0,NULL,83351),
(197,9,4,0,0,22,0,NULL,83352),
(198,2,8,0,0,22,0,NULL,83353),
(199,7,8,0,0,23,0,NULL,83354),
(200,18,12,0,0,23,0,NULL,83355),
(201,6,11,0,0,23,0,NULL,83356),
(202,14,3,0,0,23,0,NULL,83357),
(203,4,10,0,0,23,0,NULL,83358),
(204,5,2,0,0,23,0,NULL,83359),
(205,15,9,0,0,23,0,NULL,83360),
(206,16,1,0,0,23,0,NULL,83361),
(207,65,17,0,0,23,0,NULL,83362),
(208,12,4,0,0,24,0,NULL,83363),
(209,10,14,0,0,24,0,NULL,83364),
(210,1,7,0,0,24,0,NULL,83365),
(211,8,5,0,0,24,0,NULL,83366),
(212,3,6,0,0,24,0,NULL,83367),
(213,11,65,0,0,24,0,NULL,83368),
(214,17,15,0,0,24,0,NULL,83369),
(215,9,16,0,0,24,0,NULL,83370),
(216,2,18,0,0,24,0,NULL,83371),
(217,7,12,0,0,25,0,NULL,83372),
(218,18,9,0,0,25,0,NULL,83373),
(219,6,10,0,0,25,0,NULL,83374),
(220,14,17,0,0,25,0,NULL,83375),
(221,4,5,0,0,25,0,NULL,83376),
(222,15,2,0,0,25,0,NULL,83377),
(223,11,1,0,0,25,0,NULL,83378),
(224,16,8,0,0,25,0,NULL,83379),
(225,65,3,0,0,25,0,NULL,83380),
(226,12,15,0,0,26,0,NULL,83381),
(227,10,16,0,0,26,0,NULL,83382),
(228,1,65,0,0,26,0,NULL,83383),
(229,5,18,0,0,26,0,NULL,83384),
(230,8,4,0,0,26,0,NULL,83385),
(231,3,11,0,0,26,0,NULL,83386),
(232,17,6,0,0,26,0,NULL,83387),
(233,9,14,0,0,26,0,NULL,83388),
(234,2,7,0,0,26,0,NULL,83389),
(235,7,3,0,0,27,0,NULL,83390),
(236,18,8,0,0,27,0,NULL,83391),
(237,6,9,0,0,27,0,NULL,83392),
(238,14,1,0,0,27,0,NULL,83393),
(239,4,2,0,0,27,0,NULL,83394),
(240,15,5,0,0,27,0,NULL,83395),
(241,11,17,0,0,27,0,NULL,83396),
(242,16,12,0,0,27,0,NULL,83397),
(243,65,10,0,0,27,0,NULL,83398),
(244,12,11,0,0,28,0,NULL,83399),
(245,18,4,0,0,28,0,NULL,83400),
(246,10,7,0,0,28,0,NULL,83401),
(247,1,6,0,0,28,0,NULL,83402),
(248,5,14,0,0,28,0,NULL,83403),
(249,8,15,0,0,28,0,NULL,83404),
(250,3,17,0,0,28,0,NULL,83405),
(251,9,65,0,0,28,0,NULL,83406),
(252,2,16,0,0,28,0,NULL,83407),
(253,7,9,0,0,29,0,NULL,83408),
(254,6,2,0,0,29,0,NULL,83409),
(255,14,4,0,0,29,0,NULL,83410),
(256,15,18,0,0,29,0,NULL,83411),
(257,3,10,0,0,29,0,NULL,83412),
(258,11,8,0,0,29,0,NULL,83413),
(259,16,5,0,0,29,0,NULL,83414),
(260,17,1,0,0,29,0,NULL,83415),
(261,65,12,0,0,29,0,NULL,83416),
(262,12,14,0,0,30,0,NULL,83417),
(263,18,16,0,0,30,0,NULL,83418),
(264,10,17,0,0,30,0,NULL,83419),
(265,4,15,0,0,30,0,NULL,83420),
(266,1,3,0,0,30,0,NULL,83421),
(267,5,7,0,0,30,0,NULL,83422),
(268,8,6,0,0,30,0,NULL,83423),
(269,9,11,0,0,30,0,NULL,83424),
(270,2,65,0,0,30,0,NULL,83425),
(271,7,18,0,0,31,0,NULL,83426),
(272,6,4,0,0,31,0,NULL,83427),
(273,14,8,0,0,31,0,NULL,83428),
(274,1,10,0,0,31,0,NULL,83429),
(275,3,9,0,0,31,0,NULL,83430),
(276,11,2,0,0,31,0,NULL,83431),
(277,16,15,0,0,31,0,NULL,83432),
(278,17,12,0,0,31,0,NULL,83433),
(279,65,5,0,0,31,0,NULL,83434),
(280,12,3,0,0,32,0,NULL,83435),
(281,18,65,0,0,32,0,NULL,83436),
(282,10,11,0,0,32,0,NULL,83437),
(283,4,16,0,0,32,0,NULL,83438),
(284,5,6,0,0,32,0,NULL,83439),
(285,15,7,0,0,32,0,NULL,83440),
(286,8,17,0,0,32,0,NULL,83441),
(287,9,1,0,0,32,0,NULL,83442),
(288,2,14,0,0,32,0,NULL,83443),
(289,7,16,0,0,33,0,NULL,83444),
(290,6,18,0,0,33,0,NULL,83445),
(291,14,15,0,0,33,0,NULL,83446),
(292,10,12,0,0,33,0,NULL,83447),
(293,1,2,0,0,33,0,NULL,83448),
(294,3,5,0,0,33,0,NULL,83449),
(295,11,4,0,0,33,0,NULL,83450),
(296,17,9,0,0,33,0,NULL,83451),
(297,65,8,0,0,33,0,NULL,83452),
(298,12,1,0,0,34,0,NULL,83453),
(299,18,14,0,0,34,0,NULL,83454),
(300,4,7,0,0,34,0,NULL,83455),
(301,5,11,0,0,34,0,NULL,83456),
(302,15,6,0,0,34,0,NULL,83457),
(303,8,3,0,0,34,0,NULL,83458),
(304,16,65,0,0,34,0,NULL,83459),
(305,9,10,0,0,34,0,NULL,83460),
(306,2,17,0,0,34,0,NULL,83461);
/*!40000 ALTER TABLE `tblspieltag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblverein`
--

DROP TABLE IF EXISTS `tblverein`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblverein` (
  `lngIndex` int(11) NOT NULL AUTO_INCREMENT,
  `strName` varchar(100) NOT NULL DEFAULT '',
  `intGoal` int(10) unsigned DEFAULT 0,
  `intGGoal` int(10) unsigned DEFAULT 0,
  `intPoint` int(10) unsigned DEFAULT 0,
  `strURL` varchar(255) DEFAULT NULL,
  `binLogo` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`lngIndex`)
) ENGINE=MyISAM AUTO_INCREMENT=334 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblverein`
--

LOCK TABLES `tblverein` WRITE;
/*!40000 ALTER TABLE `tblverein` DISABLE KEYS */;
INSERT INTO `tblverein` VALUES
(14,'Hoffenheim',2,3,0,'www.tsg-hoffenheim.de',NULL),
(12,'Bayern München',5,1,3,'www.fcbayern.com',NULL),
(1,'Eintracht Frankfurt',3,3,1,'www.eintracht.de',NULL),
(9,'FC Schalke 04',0,3,0,'www.schalke04.de',NULL),
(11,'Hamburger SV',0,2,0,'www.hsv.de',NULL),
(15,'1. FC Mainz 05',0,0,1,'www.mainz05.de',NULL),
(65,'SC Paderborn 07',0,0,1,'www.scp07.de',NULL),
(8,'1. FC Union Berlin',3,3,1,'www.fc-union-berlin.de',NULL),
(3,'Borussia Mönchengladbach',0,3,0,'www.vfl-wolfsburg.de',NULL),
(17,'Werder Bremen',1,4,0,'www.werder.de',NULL),
(5,'FC Augsburg',3,0,3,'www.fcaugsburg.de',NULL),
(6,'VFB Stuttgart',1,5,0,'www.vfb.de',NULL),
(10,'Bayer Leverkusen',2,3,0,'www.bayer04.de',NULL),
(16,'1. FC Köln',3,2,3,'www.fc.de',NULL),
(18,'RB Leipzig',3,0,3,'www.rbleipzig.com',NULL),
(4,'SC Freiburg',4,1,3,'www.scfreiburg.com',NULL),
(7,'Borussia Dortmund',2,0,3,'www.bvb.de',NULL),
(2,'SV Elversberg',3,2,3,'www.sv07elversberg.de',NULL);
/*!40000 ALTER TABLE `tblverein` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblwette`
--

DROP TABLE IF EXISTS `tblwette`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblwette` (
  `lngIndex` int(11) NOT NULL AUTO_INCREMENT,
  `intUserid` int(11) NOT NULL DEFAULT 0,
  `intTag` int(11) NOT NULL DEFAULT 0,
  `intSpielid` int(11) NOT NULL DEFAULT 0,
  `intGoal1` int(11) NOT NULL DEFAULT 0,
  `intGoal2` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`lngIndex`)
) ENGINE=MyISAM AUTO_INCREMENT=208 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblwette`
--

LOCK TABLES `tblwette` WRITE;
/*!40000 ALTER TABLE `tblwette` DISABLE KEYS */;
INSERT INTO `tblwette` VALUES
(1,50,1,9,3,1),
(2,46,1,1,3,1),
(3,46,1,2,2,1),
(4,46,1,3,2,1),
(5,46,1,4,1,2),
(6,46,1,5,1,1),
(7,46,1,6,0,2),
(8,46,1,7,3,1),
(9,46,1,8,2,1),
(10,46,1,9,2,1),
(11,27,1,1,3,2),
(12,27,1,2,3,0),
(13,27,1,3,3,1),
(14,27,1,4,1,2),
(15,27,1,5,2,2),
(16,27,1,6,0,2),
(17,27,1,7,3,1),
(18,27,1,8,1,1),
(19,27,1,9,2,1),
(20,40,1,1,3,1),
(21,40,1,2,2,0),
(22,40,1,3,2,1),
(23,40,1,4,2,2),
(24,40,1,5,1,2),
(25,40,1,6,0,2),
(26,40,1,7,3,0),
(27,40,1,8,3,1),
(28,40,1,9,3,0),
(29,17,1,1,3,1),
(30,17,1,2,3,1),
(31,17,1,3,2,1),
(32,17,1,4,1,1),
(33,17,1,5,2,1),
(34,17,1,6,1,2),
(35,17,1,7,3,1),
(36,17,1,8,2,1),
(37,17,1,9,2,1),
(38,26,1,1,1,0),
(39,26,1,2,3,1),
(40,26,1,3,2,1),
(41,26,1,4,1,1),
(42,26,1,5,1,2),
(43,26,1,6,0,3),
(44,26,1,7,2,0),
(45,26,1,8,1,2),
(46,26,1,9,1,1),
(47,21,1,1,3,0),
(48,21,1,2,2,1),
(49,21,1,3,2,0),
(50,21,1,4,1,1),
(51,21,1,5,1,1),
(52,21,1,6,0,2),
(53,21,1,7,2,0),
(54,21,1,8,2,1),
(55,21,1,9,2,1),
(56,16,1,1,5,0),
(57,16,1,2,3,1),
(58,16,1,3,1,1),
(59,16,1,4,2,1),
(60,16,1,5,1,1),
(61,16,1,6,1,3),
(62,16,1,7,2,0),
(63,16,1,8,1,2),
(64,16,1,9,1,1),
(65,47,1,1,3,1),
(66,47,1,2,2,2),
(67,47,1,3,0,0),
(68,47,1,4,1,2),
(69,47,1,5,0,2),
(70,47,1,6,1,4),
(71,47,1,7,3,1),
(72,47,1,8,1,2),
(73,47,1,9,1,1),
(74,50,1,8,2,1),
(75,15,1,1,4,1),
(76,15,1,2,3,1),
(77,15,1,3,3,1),
(78,15,1,4,2,3),
(79,15,1,5,1,3),
(80,15,1,6,1,3),
(81,15,1,7,2,1),
(82,15,1,8,2,3),
(83,15,1,9,2,2),
(84,51,1,1,3,1),
(85,51,1,2,2,1),
(86,51,1,3,1,1),
(87,51,1,4,2,1),
(88,51,1,5,2,1),
(89,51,1,6,2,1),
(90,51,1,7,1,2),
(91,51,1,8,1,1),
(92,51,1,9,2,1),
(93,50,1,1,4,1),
(94,50,1,2,3,1),
(95,50,1,3,3,0),
(96,50,1,4,1,1),
(97,50,1,5,1,1),
(98,50,1,6,0,2),
(99,50,1,7,3,0),
(100,52,1,1,3,0),
(101,52,1,2,2,1),
(102,52,1,3,1,0),
(103,52,1,4,0,2),
(104,52,1,5,1,2),
(105,52,1,6,0,3),
(106,52,1,7,3,0),
(107,52,1,8,2,0),
(108,52,1,9,1,2),
(109,49,1,1,3,1),
(110,49,1,2,2,1),
(111,49,1,3,1,0),
(112,49,1,4,0,2),
(113,49,1,5,1,2),
(114,49,1,6,1,2),
(115,49,1,7,2,0),
(116,49,1,8,3,1),
(117,49,1,9,1,1),
(118,23,1,1,3,1),
(119,23,1,2,2,1),
(120,23,1,3,4,1),
(121,23,1,4,1,1),
(122,23,1,5,1,1),
(123,23,1,6,0,3),
(124,23,1,7,2,0),
(125,23,1,8,2,1),
(126,23,1,9,3,1),
(127,33,1,1,2,2),
(128,33,1,2,2,2),
(129,33,1,3,1,0),
(130,33,1,4,1,2),
(131,33,1,5,1,1),
(132,33,1,6,1,3),
(133,33,1,7,3,2),
(134,33,1,8,1,2),
(135,33,1,9,2,1),
(136,51,2,10,2,1),
(137,51,2,11,1,3),
(138,51,2,12,2,1),
(139,51,2,13,2,1),
(140,51,2,14,1,3),
(141,51,2,15,1,3),
(142,51,2,16,1,4),
(143,51,2,17,2,1),
(144,51,2,18,1,1),
(145,17,2,10,2,1),
(146,17,2,11,1,2),
(147,17,2,12,2,1),
(148,17,2,13,2,1),
(149,17,2,14,1,2),
(150,17,2,15,0,2),
(151,17,2,16,1,4),
(152,17,2,17,2,1),
(153,17,2,18,1,1),
(154,33,2,10,2,2),
(155,33,2,11,1,2),
(156,33,2,12,3,2),
(157,33,2,13,1,1),
(158,33,2,14,1,3),
(159,33,2,15,1,2),
(160,33,2,16,0,4),
(161,33,2,17,0,0),
(162,33,2,18,3,1),
(163,40,2,10,3,1),
(164,40,2,11,2,1),
(165,40,2,12,3,1),
(166,40,2,13,2,1),
(167,40,2,14,1,3),
(168,40,2,15,0,2),
(169,40,2,16,0,4),
(170,40,2,17,2,0),
(171,40,2,18,3,2),
(172,27,2,10,2,1),
(173,27,2,11,1,3),
(174,27,2,12,2,1),
(175,27,2,13,1,1),
(176,27,2,14,2,1),
(177,27,2,15,1,2),
(178,27,2,16,1,4),
(179,27,2,17,2,1),
(180,27,2,18,2,2),
(181,46,2,10,1,1),
(182,46,2,11,1,3),
(183,46,2,12,2,1),
(184,46,2,13,2,1),
(185,46,2,14,1,1),
(186,46,2,15,1,3),
(187,46,2,16,0,4),
(188,46,2,17,1,2),
(189,46,2,18,2,2),
(190,26,2,10,3,0),
(191,26,2,11,2,1),
(192,26,2,12,2,1),
(193,26,2,13,1,1),
(194,26,2,14,2,1),
(195,26,2,15,0,2),
(196,26,2,16,0,3),
(197,26,2,17,1,0),
(198,26,2,18,2,0),
(199,21,2,10,2,1),
(200,21,2,11,1,2),
(201,21,2,12,2,1),
(202,21,2,13,2,1),
(203,21,2,14,1,3),
(204,21,2,15,0,2),
(205,21,2,16,0,3),
(206,21,2,17,2,1),
(207,21,2,18,2,1);
/*!40000 ALTER TABLE `tblwette` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'bl_wette'
--

--
-- Dumping routines for database 'bl_wette'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-04 11:21:50

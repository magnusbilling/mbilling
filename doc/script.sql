-- MySQL dump 10.13  Distrib 5.6.34, for Linux (x86_64)
--
-- Host: localhost    Database: mbilling
-- ------------------------------------------------------
-- Server version 5.6.34

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `pkg_balance`
--

DROP TABLE IF EXISTS `pkg_balance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_balance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `last_use` int(2) NOT NULL,
  `id_prefix` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_balance`
--

LOCK TABLES `pkg_balance` WRITE;
/*!40000 ALTER TABLE `pkg_balance` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_balance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_boleto`
--

DROP TABLE IF EXISTS `pkg_boleto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_boleto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(20) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` mediumtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `status` varchar(4) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `payment` varchar(10) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `vencimento` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `fk_pkg_user_pkg_boleto` (`id_user`),
  CONSTRAINT `fk_pkg_user_pkg_boleto` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_boleto`
--

LOCK TABLES `pkg_boleto` WRITE;
/*!40000 ALTER TABLE `pkg_boleto` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_boleto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_call_chart`
--

DROP TABLE IF EXISTS `pkg_call_chart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_call_chart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `answer` int(11) DEFAULT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `total` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_call_chart`
--

LOCK TABLES `pkg_call_chart` WRITE;
/*!40000 ALTER TABLE `pkg_call_chart` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_call_chart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_call_online`
--

DROP TABLE IF EXISTS `pkg_call_online`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_call_online` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `canal` varchar(50) DEFAULT NULL,
  `tronco` varchar(50) DEFAULT NULL,
  `ndiscado` varchar(25) DEFAULT '0',
  `codec` varchar(5) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `status` varchar(16) NOT NULL,
  `duration` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `reinvite` varchar(5) NOT NULL,
  `from_ip` varchar(50) DEFAULT NULL,
  `server` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `starttime` (`status`),
  KEY `fk_pkg_user_pkg_call_online` (`id_user`),
  CONSTRAINT `fk_pkg_user_pkg_call_online` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_call_online`
--

LOCK TABLES `pkg_call_online` WRITE;
/*!40000 ALTER TABLE `pkg_call_online` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_call_online` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_callback`
--

DROP TABLE IF EXISTS `pkg_callback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_callback` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `uniqueid` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `entry_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `server_ip` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `num_attempt` int(11) NOT NULL DEFAULT '0',
  `last_attempt_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `manager_result` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `agi_result` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `callback_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `channel` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `exten` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `context` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `priority` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `application` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `data` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `timeout` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `callerid` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `variable` varchar(300) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `account` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `async` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `actionid` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `id_server` int(11) DEFAULT NULL,
  `id_server_group` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pkg_callback_uniqueid_key` (`uniqueid`),
  KEY `fk_pkg_user_pkg_callback` (`id_user`),
  CONSTRAINT `fk_pkg_user_pkg_callback` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_callback`
--

LOCK TABLES `pkg_callback` WRITE;
/*!40000 ALTER TABLE `pkg_callback` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_callback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_callerid`
--

DROP TABLE IF EXISTS `pkg_callerid`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_callerid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `id_user` int(11) NOT NULL,
  `activated` char(1) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 't',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cons_pkg_callerid_cid` (`cid`),
  KEY `fk_pkg_user_pkg_callerid` (`id_user`),
  CONSTRAINT `fk_pkg_user_pkg_callerid` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_callerid`
--

LOCK TABLES `pkg_callerid` WRITE;
/*!40000 ALTER TABLE `pkg_callerid` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_callerid` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_callshop`
--

DROP TABLE IF EXISTS `pkg_callshop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_callshop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionid` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_prefix` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `buycost` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `price` decimal(15,5) NOT NULL,
  `calledstation` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sessiontime` int(11) NOT NULL,
  `cabina` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `markup` decimal(15,5) NOT NULL DEFAULT '0.00000',
  PRIMARY KEY (`id`),
  KEY `id_prefix` (`id_prefix`),
  KEY `cabina` (`cabina`),
  KEY `fk_pkg_user_pkg_callshop` (`id_user`),
  CONSTRAINT `fk_pkg_user_pkg_callshop` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_callshop`
--

LOCK TABLES `pkg_callshop` WRITE;
/*!40000 ALTER TABLE `pkg_callshop` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_callshop` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_campaign`
--

DROP TABLE IF EXISTS `pkg_campaign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_campaign` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `name` char(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `creationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `startingdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expirationdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `description` mediumtext CHARACTER SET utf8 COLLATE utf8_bin,
  `secondusedreal` int(11) DEFAULT '0',
  `nb_callmade` int(11) DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '1',
  `frequency` int(11) NOT NULL DEFAULT '0',
  `forward_number` char(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `daily_start_time` time NOT NULL DEFAULT '10:00:00',
  `daily_stop_time` time NOT NULL DEFAULT '18:00:00',
  `monday` tinyint(4) NOT NULL DEFAULT '1',
  `tuesday` tinyint(4) NOT NULL DEFAULT '1',
  `wednesday` tinyint(4) NOT NULL DEFAULT '1',
  `thursday` tinyint(4) NOT NULL DEFAULT '1',
  `friday` tinyint(4) NOT NULL DEFAULT '1',
  `saturday` tinyint(4) NOT NULL DEFAULT '0',
  `sunday` tinyint(4) NOT NULL DEFAULT '0',
  `audio` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `audio_2` varchar(100) DEFAULT NULL,
  `type` int(10) DEFAULT '1',
  `restrict_phone` int(1) NOT NULL DEFAULT '0',
  `enable_max_call` int(1) NOT NULL DEFAULT '0',
  `digit_authorize` smallint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_pkg_user_pkg_campaign` (`id_user`),
  CONSTRAINT `fk_pkg_user_pkg_campaign` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_campaign`
--

LOCK TABLES `pkg_campaign` WRITE;
/*!40000 ALTER TABLE `pkg_campaign` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_campaign` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_campaign_log`
--

DROP TABLE IF EXISTS `pkg_campaign_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_campaign_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `total` int(11) NOT NULL DEFAULT '0',
  `loops` int(11) NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `trunks` varchar(200) DEFAULT NULL,
  `campaigns` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_campaign_log`
--

LOCK TABLES `pkg_campaign_log` WRITE;
/*!40000 ALTER TABLE `pkg_campaign_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_campaign_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_campaign_phonebook`
--

DROP TABLE IF EXISTS `pkg_campaign_phonebook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_campaign_phonebook` (
  `id_campaign` int(11) NOT NULL,
  `id_phonebook` int(11) NOT NULL,
  PRIMARY KEY (`id_campaign`,`id_phonebook`),
  KEY `fk_pkg_campaign_pkg_campaign_phonebook` (`id_campaign`),
  KEY `fk_pkg_phonebook_pkg_campaign_phonebook` (`id_phonebook`),
  CONSTRAINT `fk_pkg_campaign_pkg_campaign_phonebook` FOREIGN KEY (`id_campaign`) REFERENCES `pkg_campaign` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pkg_phonebook_pkg_campaign_phonebook` FOREIGN KEY (`id_phonebook`) REFERENCES `pkg_phonebook` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_campaign_phonebook`
--

LOCK TABLES `pkg_campaign_phonebook` WRITE;
/*!40000 ALTER TABLE `pkg_campaign_phonebook` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_campaign_phonebook` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_campaign_poll`
--

DROP TABLE IF EXISTS `pkg_campaign_poll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_campaign_poll` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_campaign` int(11) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `arq_audio` varchar(100) DEFAULT NULL,
  `ordem_exibicao` int(11) DEFAULT NULL,
  `option0` varchar(30) NOT NULL,
  `option1` varchar(30) NOT NULL,
  `option2` varchar(30) NOT NULL,
  `option3` varchar(30) NOT NULL,
  `option4` varchar(50) NOT NULL,
  `option5` varchar(50) NOT NULL,
  `option6` varchar(30) NOT NULL,
  `option7` varchar(30) NOT NULL,
  `option8` varchar(30) NOT NULL,
  `option9` varchar(30) NOT NULL,
  `digit_authorize` int(1) NOT NULL DEFAULT '1',
  `request_authorize` int(1) NOT NULL DEFAULT '0',
  `repeat` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_pkg_campaign_pkg_campaign_poll` (`id_campaign`),
  CONSTRAINT `fk_pkg_campaign_pkg_campaign_poll` FOREIGN KEY (`id_campaign`) REFERENCES `pkg_campaign` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_campaign_poll`
--

LOCK TABLES `pkg_campaign_poll` WRITE;
/*!40000 ALTER TABLE `pkg_campaign_poll` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_campaign_poll` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_campaign_poll_info`
--

DROP TABLE IF EXISTS `pkg_campaign_poll_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_campaign_poll_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_campaign_poll` int(11) NOT NULL,
  `resposta` smallint(2) NOT NULL,
  `number` varchar(18) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `obs` varchar(200) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pkg_campaign_poll_pkg_campaign_poll_info` (`id_campaign_poll`),
  CONSTRAINT `fk_pkg_campaign_poll_pkg_campaign_poll_info` FOREIGN KEY (`id_campaign_poll`) REFERENCES `pkg_campaign_poll` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_campaign_poll_info`
--

LOCK TABLES `pkg_campaign_poll_info` WRITE;
/*!40000 ALTER TABLE `pkg_campaign_poll_info` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_campaign_poll_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_campaign_restrict_phone`
--

DROP TABLE IF EXISTS `pkg_campaign_restrict_phone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_campaign_restrict_phone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `number` (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_campaign_restrict_phone`
--

LOCK TABLES `pkg_campaign_restrict_phone` WRITE;
/*!40000 ALTER TABLE `pkg_campaign_restrict_phone` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_campaign_restrict_phone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_cdr`
--

DROP TABLE IF EXISTS `pkg_cdr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_cdr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_plan` int(11) DEFAULT NULL,
  `id_trunk` int(11) DEFAULT NULL,
  `id_did` int(11) DEFAULT NULL,
  `id_offer` int(11) DEFAULT '0',
  `id_prefix` int(11) DEFAULT NULL,
  `id_campaign` int(11) DEFAULT NULL,
  `sessionid` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `uniqueid` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `starttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `stoptime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sessiontime` int(11) DEFAULT NULL,
  `calledstation` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `sessionbill` float DEFAULT NULL,
  `sipiax` int(11) DEFAULT '0',
  `src` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `buycost` decimal(15,6) DEFAULT '0.000000',
  `real_sessiontime` int(11) DEFAULT NULL,
  `terminatecauseid` int(1) DEFAULT '1',
  `agent_bill` decimal(15,6) DEFAULT '0.000000',
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_plan` (`id_plan`),
  KEY `id_trunk` (`id_trunk`),
  KEY `calledstation` (`calledstation`),
  KEY `terminatecauseid` (`terminatecauseid`),
  KEY `id_prefix` (`id_prefix`),
  KEY `uniqueid` (`uniqueid`),
  KEY `sessiontime` (`sessiontime`),
  KEY `sessionbill` (`sessionbill`),
  KEY `buycost` (`buycost`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_cdr`
--

LOCK TABLES `pkg_cdr` WRITE;
/*!40000 ALTER TABLE `pkg_cdr` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_cdr` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_cdr_archive`
--

DROP TABLE IF EXISTS `pkg_cdr_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_cdr_archive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_plan` int(11) DEFAULT NULL,
  `id_trunk` int(11) DEFAULT NULL,
  `id_did` int(11) DEFAULT NULL,
  `id_offer` int(11) DEFAULT '0',
  `id_prefix` int(11) DEFAULT NULL,
  `sessionid` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `uniqueid` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `starttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `stoptime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sessiontime` int(11) DEFAULT NULL,
  `calledstation` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `sessionbill` float DEFAULT NULL,
  `sipiax` int(11) DEFAULT '0',
  `src` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `buycost` decimal(15,5) DEFAULT '0.00000',
  `real_sessiontime` int(11) DEFAULT NULL,
  `terminatecauseid` int(1) DEFAULT '1',
  `agent_bill` decimal(15,5) DEFAULT '0.00000',
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_plan` (`id_plan`),
  KEY `id_trunk` (`id_trunk`),
  KEY `calledstation` (`calledstation`),
  KEY `terminatecauseid` (`terminatecauseid`),
  KEY `id_prefix` (`id_prefix`),
  KEY `uniqueid` (`uniqueid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_cdr_archive`
--

LOCK TABLES `pkg_cdr_archive` WRITE;
/*!40000 ALTER TABLE `pkg_cdr_archive` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_cdr_archive` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_cdr_failed`
--

DROP TABLE IF EXISTS `pkg_cdr_failed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_cdr_failed` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_plan` int(11) DEFAULT NULL,
  `id_trunk` int(11) DEFAULT NULL,
  `id_prefix` int(11) DEFAULT NULL,
  `sessionid` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `uniqueid` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `starttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `calledstation` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `sipiax` int(11) DEFAULT '0',
  `src` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `terminatecauseid` int(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_plan` (`id_plan`),
  KEY `id_trunk` (`id_trunk`),
  KEY `calledstation` (`calledstation`),
  KEY `terminatecauseid` (`terminatecauseid`),
  KEY `id_prefix` (`id_prefix`),
  KEY `uniqueid` (`uniqueid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_cdr_failed`
--

LOCK TABLES `pkg_cdr_failed` WRITE;
/*!40000 ALTER TABLE `pkg_cdr_failed` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_cdr_failed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_configuration`
--

DROP TABLE IF EXISTS `pkg_configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config_title` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `config_key` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `config_value` varchar(200) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `config_description` varchar(500) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `config_group_title` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `status` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=260 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_configuration`
--

LOCK TABLES `pkg_configuration` WRITE;
/*!40000 ALTER TABLE `pkg_configuration` DISABLE KEYS */;
INSERT INTO `pkg_configuration` VALUES (1,'System Currency','base_currency','0','System Currency','global',1),(2,'Language','base_language','en','Allow \n en English \nes Espanhol \npt_BR Portugues','global',1),(3,'Version','version','5.3.0','MBilling Version','global',1),(4,'Licence','licence','free','MBilling Licence','global',0),(5,'Server IP','ip_servers','','Ip do servidor MBilling','global',1),(6,'Template','template','blue-neptune','Allowed values:\ngreen, gray, blue, yellow, red, orange, purple','global',0),(7,'Country','base_country','','ISO CODE values\nUSA United States,\nBRL Brasil,\nARG Argentina,\nNLD Netherlands,\nESP Spanish','global',1),(8,'Desktop layout','layout','0','Active Desktop template, only to FULL version\n1 - Enable (Only to full version)\n0 - Disable','global',0),(9,'Wallpaper','wallpaper','Azul','Default Wallpaper, only FULL version.','global',0),(10,'SMTP Host','smtp_host','mail.magnusbilling.com','SMTP Hostname','global',0),(11,'SMTP UserName','smtp_username','billing@magnusbilling.com','SMTP server Username','global',0),(12,'SMTP Password','smtp_password','magnus','SMTP server Password','global',0),(13,'SMTP Encryption','smtp_encryption','null','SMTP Encryption: tls, ssl or blank','global',0),(14,'SMTP Port','smtp_port','587','SMTP Port','global',0),(15,'Admin Email','admin_email','info@magnusbilling.com','Email for receive notifications','global',1),(16,'Send email copy to admin','admin_received_email','1','Send copy for admin email','global',1),(19,'Days notification','delay_notifications','3','Number of days to generate low balance warning to customers','global',1),(20,'Rounding calls','bloc_time_call','1','Round the lead time as charging sales.\n1: Yes\n0: No','global',1),(21,'Days to pay offers','planbilling_daytopay','5','Set how many days before maturity you wanna collect the bid offers','global',1),(22,'Agent refill limit','agent_limit_refill','5','Limit to agent refill yours customers','global',1),(23,'Archive cdr','archive_call_prior_x_month','4','Calls to file before 10 months.','global',1),(24,'Payment, Accepted values','purchase_amount','8:10:12:15:18:20:30:50:100','Accepted values ​​in module payments, no enable in FREE version.','global',1),(25,'Decimal precision','decimal_precision','0000','Decimal precision.','global',1),(26,'Active paypal for new customer','paypal_new_user','0','Active paypal for new customer. \n\n0 - Disable (RECOMENDED )\n1 - Enable','global',1),(29,'Portabilidade Usuário','portabilidadeUsername','0','Usuário da portabilidade para consulta via WebService','global',0),(30,'Portabilidade Senha','portabilidadePassword','0','Senha da portabilidade para consulta via WebService','global',0),(31,'AGI 1 - Answer Call','answer_call','0','If enabled the MBilling answers the call that starts.\nDefault: 0','agi-conf1',1),(32,'AGI 1 - User DNID','use_dnid','1','If the client does not need active schedule again the number he wish to call after entering the PIN.\n\n1 - Enable (DEFAULT)\n0 - Disable','agi-conf1',1),(33,'AGI 1 - Notices with Audio','play_audio','1','Notices with Audio, if disable, MBilling will send code 603.\n\n1 - Ativo\n0 - Desativado\n','agi-conf1',1),(34,'AGI 1 - Intro Prompt','intro_prompt','','To specify a prompt to play at the beginning of the calls','agi-conf1',1),(36,'AGI 1 - Recording calls','record_call','0','Enables recording of all customers.\nCAUTION, THIS OPTION REQUIRES A LOT OF SERVER PERFORMANCE. SO YOU CAN RECORD CUSTOMER SPECIFIC.\n\n0: Disable\n1: Enable','agi-conf1',1),(37,'AGI 1 - International prefixes','international_prefixes','00,09','List the prefixes you want stripped off if the call number','agi-conf1',1),(38,'AGI 1 - Say sell price','say_rateinitial','0','Play the initial cost of the tariff.\n\n0 - No\n1 - Yes','agi-conf1',1),(39,'AGI 1 - Say Duration','say_timetocall','0','Play the amount of time that the user can call.\n\n0 - No\n1 - Yes','agi-conf1',1),(40,'AGI 1 - CallerID Authentication','cid_enable','0','Active CallerID Authentication.\n\n0 - Disable\n1 - Enable','agi-conf1',1),(41,'AGI 1 - FailOver LCR/LCD','failover_lc_prefix','1','If anable and have two hidden tariff in de plan, MBilling gonna get the cheaper','agi-conf1',1),(42,'AGI 1 - Dial Command Params','dialcommand_param',',60,L(%timeout%:61000:30000)','More info: http://voip-info.org/wiki-Asterisk+cmd+dial','agi-conf1',1),(43,'AGI 1 - Internal Call, Dial Command Params','dialcommand_param_sipiax_friend',',60,TtiL(3600000:61000:30000)','Dial paramater for call between users.\n\nby default (3600000  =  1HOUR MAX CALL).','agi-conf1',1),(44,'AGI 1 - DID Dial Command Params','dialcommand_param_call_2did',',45,TtiL(%timeout%:61000:30000)','Dial paramater to DID calls','agi-conf1',1),(45,'AGI 1 - Failover Retry Limit','failover_recursive_limit','5','Define how many time we want to authorize the research of the failover trunk when a call fails','agi-conf1',1),(46,'AGI 1 - Number of attempt','number_try','1','Number of attempts to dial the number\n Minimum value 1','agi-conf1',1),(47,'AGI 1 - Outbound Call','switchdialcommand','0','Define the order to make the outbound call<br>YES -> SIP/number@trunk - NO  SIP/trunk/number<br>Both should work exactly the same but i experimented one case when gateway was supporting number@trunk, So in case of trouble, try it out.','agi-conf1',1),(48,'AGI 1 - Say Balance After Call','say_balance_after_call','0','Play the balance to the user after the call\n\n0 - No\n1 - Yes','agi-conf1',1),(85,'SIP Account for spy call','channel_spy','0','SIP Account for spy call','global',1),(142,'Username Auto Generate ','username_generate','0','Generate username automatically ','global',1),(143,'Username Prefix','generate_prefix','0','Prefix for username ','global',1),(144,'Username length','generate_length','0','Username Length','global',1),(145,'Answer Callback','answer_callback','0','Answer callback and play audio','global',1),(146,'Callback add Prefix','callback_add_prefix','','Add prefix in callerd in callback call','global',1),(147,'Callback remove Prefix','callback_remove_prefix','','Remove prefix in callerd in callback call','global',1),(233,'Menu color','color_menu','White','Menu color, Black or White','global',0),(234,'Menu color','color_menu','White','Menu color, Black or White','global',1),(235,'Charge Sip Call','charge_sip_call','0','Charge sip call between clients','global',1),(236,'URL to extra module','module_extra','index.php/extra/read','Url to extra module, default: index.php/extra/read','global',1),(237,'intra/inter Billing','intra-inter','0','Enable Intra-Inter Billing. If you enable this option, and you have another plan with the same name + Intra on the name Mbilling use the new plan to intra call','global',1),(238,'Enable Paypal on Softphone','paypal-softphone','0','Active Paypal on Android Softphones, valid only Softphone version 2.5. 0 - Disable 1 - Enable','global',1),(239,'Enable CallBack on Softphone','callback-softphone','0','Active Callback on Android Softphones, valid only Softphone version 2.5. 0 - Disable 1 - Enable','global',1),(240,'Invoice tax','invoice_tax','0','Tax to add in Invoice.','global',1),(241,'Log nivel','log','0','min 0 - max 5 ','global',1),(242,'Asterisk','asterisk_version','11','Set your Asterisk Version instaled. Default 1.8','global',1),(243,'Tts URL','tts_url','https://translate.google.com/translate_tts?ie=UTF-8&q=$name&tl=pt-BR&total=1&idx=0&textlen=25&client=t&tk=55786|34299.','Set here the URL to use in Massive Call. Use variable $name in the string field','global',1),(244,'fm.transfer-to.com username','fm_transfer_to_username',NULL,'Username in Transfer To','global',1),(245,'fm.transfer-to.com token','fm_transfer_to_ token',NULL,'Token to Transfer To API','global',1),(246,'fm.transfer-to.com Agent profits','fm_transfer_to_profit','0','Percent to Agent','global',1),(247,'Days to find in Summary per user','summary_per_user_days','7','Days to find when you open the menu Summary per User','global',1),(248,'Days to find in Summary per agent','summary_per_agent_days','7','Days to find when you open the menu Summary per Agent','global',1),(249,'MixMonitor Format','MixMonitor_format','gsm','see the availables extensions in http://www.voip-info.org/wiki/view/MixMonitor','global',1),(250,'fm.transfer-to.com show selling price','fm_transfer_show_selling_price','0','Show recommended selling price in the form after select the amount. Set the total to increase. Less than 99%','global',1),(251,'fm.transfer-to.com print Header','fm_transfer_print_header','Change it in configuration menu','Description to print header','global',1),(252,'fm.transfer-to.com print Footer','fm_transfer_print_footer','Change it in configuration menu','Description to print footer','global',1),(253,'fm.transfer-to.com Currency','fm_transfer_currency','€','Set the transfer-to currency','global',1),(254,'Sip trunk short duration call','trunk_short_duration_call','3','SIP TRUNK short duration call','global',1),(255,'Sip trunk short total calls','trunk_short_total_calls','0','Sip trunk short total calls','global',1),(256,'Enable IAX','enable_izx','3','Enable IAX','global',0),(257,'Use CDR Cache','cache','0','Use CDR cache','global',0),(258,'AGI 1 - Use amd macro','amd','','Use amd. Set to CM(amd) . \r\n       Add this macro in your extension_magnus.conf\r\n\r\n        [macro-amd]\r\n       exten => s,1,AMD\r\n        exten => s,n,Noop(AMD_NUMERO - ${CALLERID(num)})\r\n        exten => s,n,Noop(AMD_STATUS - ${AMDSTATUS})\r\n        exten => s,n,Noop(AMD_CAUSE - ${AMDCAUSE})\r\n        exten => s,n,GotoIf($[${AMDSTATUS}=HUMAN]?humn:mach)\r\n        exten => s,n(mach),SoftHangup(${CHANNEL})\r\n       exten => s,n,Hangup()\r\n       exten => s,n(humn),WaitForSilence(20)','agi-conf1',1),(259,'BDService Url','BDService_url','http://takasend.net','Default http://takasend.net','global',1);
/*!40000 ALTER TABLE `pkg_configuration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_did`
--

DROP TABLE IF EXISTS `pkg_did`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_did` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `activated` int(11) NOT NULL DEFAULT '1',
  `reserved` tinyint(11) DEFAULT '0',
  `did` char(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `creationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `startingdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expirationdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `description` mediumtext CHARACTER SET utf8 COLLATE utf8_bin,
  `secondusedreal` int(11) DEFAULT '0',
  `billingtype` int(11) DEFAULT '0',
  `fixrate` float NOT NULL DEFAULT '0',
  `connection_charge` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `expression_1` varchar(150) NOT NULL DEFAULT '*',
  `selling_rate_1` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `expression_2` varchar(150) NOT NULL DEFAULT '*',
  `selling_rate_2` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `connection_sell` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `minimal_time_charge` int(11) NOT NULL DEFAULT '0',
  `initblock` int(11) NOT NULL DEFAULT '1',
  `increment` int(11) NOT NULL DEFAULT '1',
  `block_expression_1` smallint(2) NOT NULL DEFAULT '0',
  `block_expression_2` smallint(2) NOT NULL DEFAULT '0',
  `expression_3` varchar(150) NOT NULL DEFAULT '*',
  `selling_rate_3` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `block_expression_3` smallint(2) NOT NULL DEFAULT '0',
  `charge_of` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `did` (`did`),
  KEY `fk_pkg_user_pkg_did` (`id_user`),
  CONSTRAINT `fk_pkg_user_pkg_did` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_did`
--

LOCK TABLES `pkg_did` WRITE;
/*!40000 ALTER TABLE `pkg_did` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_did` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_did_destination`
--

DROP TABLE IF EXISTS `pkg_did_destination`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_did_destination` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_ivr` int(11) DEFAULT NULL,
  `id_sip` int(11) DEFAULT NULL,
  `id_queue` int(11) DEFAULT NULL,
  `id_did` int(11) NOT NULL,
  `destination` varchar(120) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `priority` int(11) NOT NULL DEFAULT '0',
  `creationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `activated` int(11) NOT NULL DEFAULT '1',
  `secondusedreal` int(11) DEFAULT '0',
  `voip_call` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_pkg_did_pkg_did_destination` (`id_did`),
  KEY `fk_pkg_user_pkg_did_destination` (`id_user`),
  CONSTRAINT `fk_pkg_did_pkg_did_destination` FOREIGN KEY (`id_did`) REFERENCES `pkg_did` (`id`),
  CONSTRAINT `fk_pkg_user_pkg_did_destination` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_did_destination`
--

LOCK TABLES `pkg_did_destination` WRITE;
/*!40000 ALTER TABLE `pkg_did_destination` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_did_destination` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_did_use`
--

DROP TABLE IF EXISTS `pkg_did_use`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_did_use` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `id_did` int(11) DEFAULT NULL,
  `reservationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `releasedate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` int(11) DEFAULT '0',
  `month_payed` int(11) DEFAULT '0',
  `reminded` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_card` (`id_user`),
  KEY `id_did` (`id_did`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_did_use`
--

LOCK TABLES `pkg_did_use` WRITE;
/*!40000 ALTER TABLE `pkg_did_use` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_did_use` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_firewall`
--

DROP TABLE IF EXISTS `pkg_firewall`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_firewall` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `action` int(1) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_firewall`
--

LOCK TABLES `pkg_firewall` WRITE;
/*!40000 ALTER TABLE `pkg_firewall` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_firewall` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_group_module`
--

DROP TABLE IF EXISTS `pkg_group_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_group_module` (
  `id_group` int(11) NOT NULL,
  `id_module` int(11) NOT NULL,
  `action` varchar(45) NOT NULL,
  `show_menu` tinyint(1) NOT NULL DEFAULT '1',
  `createShortCut` tinyint(1) NOT NULL DEFAULT '0',
  `createQuickStart` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_group`,`id_module`),
  KEY `fk_pkg_module_group_pkg_module` (`id_module`),
  CONSTRAINT `fk_pkg_group_user_pkg_group_module` FOREIGN KEY (`id_group`) REFERENCES `pkg_group_user` (`id`),
  CONSTRAINT `fk_pkg_module_group_pkg_module` FOREIGN KEY (`id_module`) REFERENCES `pkg_module` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_group_module`
--

LOCK TABLES `pkg_group_module` WRITE;
/*!40000 ALTER TABLE `pkg_group_module` DISABLE KEYS */;
INSERT INTO `pkg_group_module` VALUES (1,1,'crud',1,0,0),(1,2,'crud',0,0,0),(1,3,'crud',1,0,0),(1,4,'rcud',0,0,0),(1,5,'crud',1,0,0),(1,6,'crud',1,0,0),(1,7,'crud',1,0,0),(1,8,'crud',1,0,0),(1,9,'crud',1,0,0),(1,10,'crud',1,0,0),(1,12,'crud',1,0,0),(1,13,'crud',1,0,0),(1,14,'crud',1,0,0),(1,15,'crud',1,1,1),(1,16,'crud',1,0,0),(1,17,'crud',1,1,1),(1,19,'crud',1,1,1),(1,20,'crud',1,0,0),(1,21,'crud',1,1,1),(1,22,'crud',1,0,0),(1,23,'crud',1,1,1),(1,25,'crud',1,0,0),(1,26,'crud',1,0,0),(1,27,'crud',1,0,0),(1,28,'crud',1,0,0),(1,29,'crud',1,1,1),(1,30,'rud',1,1,1),(1,31,'crud',1,0,0),(1,32,'crud',1,0,0),(1,33,'crud',1,0,0),(1,34,'crud',1,0,0),(1,36,'crud',1,0,0),(1,40,'crud',1,1,1),(1,41,'crud',1,0,0),(1,42,'crud',1,0,0),(1,43,'crud',1,0,0),(1,44,'crud',1,0,0),(1,45,'crud',1,0,0),(1,46,'crud',1,0,0),(1,48,'crud',1,0,0),(1,49,'crud',1,0,0),(1,50,'crud',1,0,0),(1,51,'crud',1,0,0),(1,52,'crud',1,0,0),(1,53,'crud',1,0,0),(1,54,'crud',1,0,0),(1,56,'crud',1,0,0),(1,57,'crud',1,0,0),(1,58,'crud',1,0,0),(1,59,'crud',1,0,0),(1,60,'crud',1,1,0),(1,62,'crud',1,0,0),(1,63,'crud',1,1,1),(1,64,'r',1,1,1),(1,66,'crud',1,1,1),(1,67,'rud',1,1,1),(1,70,'r',1,1,1),(1,71,'r',1,1,1),(1,72,'crud',1,1,1),(1,77,'crud',1,1,1),(1,78,'r',1,1,1),(1,80,'r',1,1,1),(1,81,'r',1,1,1),(1,82,'crud',1,0,0),(1,83,'crud',1,1,1),(2,1,'crud',1,0,0),(2,2,'crud',1,0,0),(2,7,'crud',1,0,0),(2,8,'crud',1,0,0),(2,9,'crud',1,0,0),(2,15,'crud',1,1,1),(2,19,'crud',1,1,1),(2,20,'crud',1,0,0),(2,21,'r',1,1,1),(2,23,'crud',1,1,1),(2,25,'r',0,0,0),(2,28,'crud',1,0,0),(2,29,'crud',1,1,1),(2,30,'r',1,1,1),(2,33,'r',0,0,0),(2,40,'r',1,1,0),(2,51,'r',1,0,0),(2,53,'r',1,0,0),(2,61,'r',1,1,1),(3,1,'ru',1,0,0),(3,5,'r',1,0,0),(3,7,'r',1,0,0),(3,8,'r',1,0,0),(3,9,'r',1,0,0),(3,15,'ru',1,1,1),(3,19,'ru',1,0,0),(3,20,'crud',1,0,0),(3,21,'r',1,1,1),(3,22,'crud',1,0,0),(3,23,'r',1,1,1),(3,25,'r',0,0,0),(3,28,'r',0,0,0),(3,29,'r',1,1,1),(3,30,'r',1,1,1),(3,31,'r',1,0,0),(3,32,'crud',1,0,0),(3,33,'r',0,0,0),(3,34,'r',0,0,0),(3,40,'r',1,0,0),(3,61,'r',1,1,1);
/*!40000 ALTER TABLE `pkg_group_module` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_group_user`
--

DROP TABLE IF EXISTS `pkg_group_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_group_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `id_user_type` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pkg_user_type_pkg_group_user` (`id_user_type`),
  CONSTRAINT `pkg_user_type_pkg_group_user` FOREIGN KEY (`id_user_type`) REFERENCES `pkg_user_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_group_user`
--

LOCK TABLES `pkg_group_user` WRITE;
/*!40000 ALTER TABLE `pkg_group_user` DISABLE KEYS */;
INSERT INTO `pkg_group_user` VALUES (1,'Administrator',1),(2,'Agent',2),(3,'Client',3);
/*!40000 ALTER TABLE `pkg_group_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_iax`
--

DROP TABLE IF EXISTS `pkg_iax`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_iax` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL DEFAULT '0',
  `name` varchar(80) COLLATE utf8_bin NOT NULL,
  `accountcode` varchar(20) COLLATE utf8_bin NOT NULL,
  `regexten` varchar(20) COLLATE utf8_bin NOT NULL,
  `amaflags` char(7) COLLATE utf8_bin DEFAULT NULL,
  `callgroup` char(10) COLLATE utf8_bin DEFAULT NULL,
  `callerid` varchar(80) COLLATE utf8_bin NOT NULL,
  `canreinvite` varchar(20) COLLATE utf8_bin NOT NULL,
  `context` varchar(80) COLLATE utf8_bin NOT NULL,
  `DEFAULTip` char(15) COLLATE utf8_bin DEFAULT NULL,
  `dtmfmode` char(7) COLLATE utf8_bin NOT NULL DEFAULT 'RFC2833',
  `fromuser` varchar(80) COLLATE utf8_bin NOT NULL,
  `fromdomain` varchar(80) COLLATE utf8_bin NOT NULL,
  `host` varchar(31) COLLATE utf8_bin NOT NULL,
  `insecure` varchar(20) COLLATE utf8_bin NOT NULL,
  `language` char(2) COLLATE utf8_bin DEFAULT NULL,
  `mailbox` varchar(50) COLLATE utf8_bin NOT NULL,
  `md5secret` varchar(80) COLLATE utf8_bin NOT NULL,
  `nat` varchar(25) COLLATE utf8_bin DEFAULT 'yes',
  `permit` varchar(95) COLLATE utf8_bin NOT NULL,
  `deny` varchar(95) COLLATE utf8_bin NOT NULL,
  `mask` varchar(95) COLLATE utf8_bin NOT NULL,
  `pickupgroup` char(10) COLLATE utf8_bin DEFAULT NULL,
  `port` char(5) COLLATE utf8_bin NOT NULL DEFAULT '',
  `qualify` char(7) COLLATE utf8_bin DEFAULT 'yes',
  `restrictcid` char(1) COLLATE utf8_bin DEFAULT NULL,
  `rtptimeout` char(3) COLLATE utf8_bin DEFAULT NULL,
  `rtpholdtimeout` char(3) COLLATE utf8_bin DEFAULT NULL,
  `secret` varchar(80) COLLATE utf8_bin NOT NULL,
  `type` char(6) COLLATE utf8_bin NOT NULL DEFAULT 'friend',
  `username` varchar(80) COLLATE utf8_bin NOT NULL,
  `disallow` varchar(100) COLLATE utf8_bin NOT NULL,
  `allow` varchar(100) COLLATE utf8_bin NOT NULL,
  `musiconhold` varchar(100) COLLATE utf8_bin NOT NULL,
  `regseconds` int(11) NOT NULL DEFAULT '0',
  `ipaddr` char(15) COLLATE utf8_bin NOT NULL DEFAULT '',
  `cancallforward` char(3) COLLATE utf8_bin DEFAULT 'yes',
  `trunk` char(3) COLLATE utf8_bin DEFAULT 'no',
  `useragent` varchar(200) COLLATE utf8_bin NOT NULL DEFAULT '',
  `requirecalltoken` varchar(3) COLLATE utf8_bin NOT NULL DEFAULT 'no',
  `calllimit` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cons_pkg_iax_name` (`name`),
  KEY `name` (`name`),
  KEY `host` (`host`),
  KEY `ipaddr` (`ipaddr`),
  KEY `port` (`port`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_iax`
--

LOCK TABLES `pkg_iax` WRITE;
/*!40000 ALTER TABLE `pkg_iax` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_iax` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_ivr`
--

DROP TABLE IF EXISTS `pkg_ivr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_ivr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_did` int(11) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `monFriStart` varchar(5) NOT NULL DEFAULT '09:00',
  `monFriStop` varchar(5) NOT NULL DEFAULT '18:00',
  `satStart` varchar(5) NOT NULL DEFAULT '09:00',
  `satStop` varchar(5) NOT NULL DEFAULT '12:00',
  `sunStart` varchar(5) NOT NULL DEFAULT '09:00',
  `sunStop` varchar(5) NOT NULL DEFAULT '12:00',
  `option_0` varchar(50) DEFAULT NULL,
  `option_1` varchar(50) DEFAULT NULL,
  `option_2` varchar(50) DEFAULT NULL,
  `option_3` varchar(50) DEFAULT NULL,
  `option_4` varchar(50) DEFAULT NULL,
  `option_5` varchar(50) DEFAULT NULL,
  `option_6` varchar(50) DEFAULT NULL,
  `option_7` varchar(50) DEFAULT NULL,
  `option_8` varchar(50) DEFAULT NULL,
  `option_9` varchar(50) DEFAULT NULL,
  `option_10` varchar(50) DEFAULT NULL,
  `workaudio` varchar(100) DEFAULT NULL,
  `noworkaudio` varchar(100) DEFAULT NULL,
  `option_out_0` varchar(50) DEFAULT NULL,
  `option_out_1` varchar(50) DEFAULT NULL,
  `option_out_2` varchar(50) DEFAULT NULL,
  `option_out_3` varchar(50) DEFAULT NULL,
  `option_out_4` varchar(50) DEFAULT NULL,
  `option_out_5` varchar(50) DEFAULT NULL,
  `option_out_6` varchar(50) DEFAULT NULL,
  `option_out_7` varchar(50) DEFAULT NULL,
  `option_out_8` varchar(50) DEFAULT NULL,
  `option_out_9` varchar(50) DEFAULT NULL,
  `option_out_10` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pkg_user_pkg_ivr` (`id_user`),
  KEY `fk_pkg_did_pkg_ivr` (`id_did`),
  CONSTRAINT `fk_pkg_user_pkg_ivr` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_ivr`
--

LOCK TABLES `pkg_ivr` WRITE;
/*!40000 ALTER TABLE `pkg_ivr` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_ivr` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_log`
--

DROP TABLE IF EXISTS `pkg_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `description` text CHARACTER SET utf8 COLLATE utf8_bin,
  `action` varchar(10) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varbinary(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pkg_user_pkg_log` (`id_user`),
  CONSTRAINT `fk_pkg_user_pkg_log` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_log`
--

LOCK TABLES `pkg_log` WRITE;
/*!40000 ALTER TABLE `pkg_log` DISABLE KEYS */;
INSERT INTO `pkg_log` VALUES (4,1,'','LOGIN','2016-03-15 12:26:59','::1');
/*!40000 ALTER TABLE `pkg_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_method_pay`
--

DROP TABLE IF EXISTS `pkg_method_pay`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_method_pay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `payment_method` char(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `show_name` varchar(100) NOT NULL,
  `country` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `active` tinyint(11) NOT NULL DEFAULT '0',
  `active_agent` tinyint(11) NOT NULL DEFAULT '0',
  `obs` varchar(300) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `url` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `username` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `pagseguro_TOKEN` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `fee` tinyint(1) NOT NULL DEFAULT '0',
  `boleto_convenio` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `boleto_banco` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `boleto_agencia` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `boleto_conta_corrente` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `boleto_inicio_nosso_numeroa` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `boleto_carteira` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `boleto_taxa` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `boleto_instrucoes` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `boleto_nome_emp` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `boleto_end_emp` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `boleto_cidade_emp` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `boleto_estado_emp` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `boleto_cpf_emp` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `P2P_CustomerSiteID` varchar(100) NOT NULL DEFAULT '',
  `P2P_KeyID` varchar(50) NOT NULL DEFAULT '',
  `P2P_Passphrase` varchar(50) NOT NULL DEFAULT '',
  `P2P_RecipientKeyID` varchar(30) NOT NULL DEFAULT '',
  `P2P_tax_amount` varchar(10) NOT NULL DEFAULT '0',
  `client_id` varchar(500) DEFAULT NULL,
  `client_secret` varchar(500) DEFAULT NULL,
  `SLAppToken` varchar(50) DEFAULT NULL,
  `SLAccessToken` varchar(50) DEFAULT NULL,
  `SLSecret` varchar(50) DEFAULT NULL,
  `SLIdProduto` int(11) DEFAULT NULL,
  `SLvalidationtoken` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pkg_user_pkg_method_pay` (`id_user`),
  CONSTRAINT `fk_pkg_user_pkg_method_pay` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_method_pay`
--

LOCK TABLES `pkg_method_pay` WRITE;
/*!40000 ALTER TABLE `pkg_method_pay` DISABLE KEYS */;
INSERT INTO `pkg_method_pay` VALUES (5,1,'Pagseguro','Pagseguro','Brasil',0,0,'asd','','info@magnussolution.com','E2E11539547C4C1D988DFAC3790C5004',0,'','','','','','','','','','','','','','','','','','0',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(6,1,'BoletoBancario','BoletoBancario','Brasil',0,0,'','','','',0,'','hsbc','','','','18','1.20','Até vencimento pagável em qualquer banco e agência','','','','','','','','','','0',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(7,1,'Moip','Moip','Brasil',0,0,'','https://www.moip.com.br/PagamentoMoIP.do','magnus','',0,'','','','','','','','','','','','','','','','','','0',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(8,1,'Paypal','Paypal','Global',1,0,'','https://www.paypal.com/cgi-bin/webscr','financiero@magnusbilling.com','',0,'','','','','','','','','','','','','','','','','','0',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(9,1,'CuentaDigital','PagoFacil o Rapipago','Argentina',1,0,'','http://www.cuentadigital.com/api.php','6200','',0,'','','','','','','','','','','','','','','','','','0',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(10,1,'DineroMail','Tarjeta de crédito, DineroMail','Brasil',1,0,'','https://argentina.dineromail.com/Shop/Shop_Ingreso.asp','0416248','',0,'','','','','','','','','','','','','','','','','','0',NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `pkg_method_pay` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_module`
--

DROP TABLE IF EXISTS `pkg_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` varchar(100) NOT NULL,
  `module` varchar(100) DEFAULT NULL,
  `icon_cls` varchar(100) DEFAULT NULL,
  `id_module` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pkg_module_pkg_module` (`id_module`),
  CONSTRAINT `fk_pkg_module_pkg_module` FOREIGN KEY (`id_module`) REFERENCES `pkg_module` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_module`
--

LOCK TABLES `pkg_module` WRITE;
/*!40000 ALTER TABLE `pkg_module` DISABLE KEYS */;
INSERT INTO `pkg_module` VALUES (1,'t(\'users Module\')',NULL,'customer',NULL),(2,'t(\'groupmodule\')','groupmodule',NULL,12),(3,'t(\'groupuser\')','groupuser','modules',12),(4,'t(\'module\')','module','module',12),(5,'t(\'did Module\')',NULL,'did',NULL),(6,'t(\'settings\')','configuration','config',12),(7,'t(\'billing Module\')',NULL,'finances',NULL),(8,'t(\'rates Module\')',NULL,'rates',NULL),(9,'t(\'reports Module\')',NULL,'report',NULL),(10,'t(\'routes Module\')',NULL,'routes',NULL),(12,'t(\'config Module\')',NULL,'administrators',NULL),(13,'t(\'callcenter Module\')',NULL,'callcenter',NULL),(14,'t(\'callshop Module\')',NULL,'callshop',NULL),(15,'t(\'users\')','user','users',1),(16,'t(\'provider\')','provider','provider',10),(17,'t(\'trunk\')','trunk','trunk',10),(19,'t(\'sip\')','sip','sipbuddies',1),(20,'t(\'sipuras\')','sipuras','sipuras',1),(21,'t(\'callonline\')','callonline','callonline',1),(22,'t(\'callerid\')','callerid','callerid',1),(23,'t(\'refill\')','refill','refill',7),(25,'t(\'paymentmethods\')','methodpay','paymentmethods',7),(26,'t(\'voucher\')','voucher','voucher',7),(27,'t(\'refillprovider\')','refillprovider','refillprovider',7),(28,'t(\'plans\')','plan','plan',8),(29,'t(\'tariffs\')','rate','tariffs',8),(30,'t(\'cdr\')','call','cdr',9),(31,'t(\'did\')','did','did',5),(32,'t(\'diddestination\')','diddestination','diddestination',5),(33,'t(\'prefixs\')','prefix','prefixs',8),(34,'t(\'offer\')','offer','offer',8),(36,'t(\'diduse\')','diduse','diduse',5),(40,'t(\'callsummary\')','callsummary','callsummary',9),(41,'t(\'campaigns\')','campaign','callcenter',13),(42,'t(\'polls\')','campaignpoll','polls',13),(43,'t(\'phonebooks\')','phonebook','phonebooks',13),(44,'t(\'numbers\')','phonenumber','numbers',13),(45,'t(\'offercdr\')','offercdr','offercdr',8),(46,'t(\'offeruse\')','offeruse','offeruse',8),(48,'t(\'restrictedphonenumber\')','restrictedphonenumber','restrictedphonenumber',1),(49,'t(\'sms\')','sms','sms',13),(50,'t(\'cabinas\')','callshop','cabinas',14),(51,'t(\'callshopcdr\')','callshopcdr','cdr',14),(52,'t(\'boleto\')','boleto','boleto',7),(53,'t(\'callshoptariffs\')','ratecallshop','tariffs',14),(54,'t(\'templatemail\')','templatemail','templatemail',12),(55,'t(\'callback\')','callback','callback',1),(56,'t(\'ivr\')','ivr','ivr',5),(57,'t(\'campaignpollinfo\')','campaignpollinfo','campaignpollinfo',13),(58,'t(\'queues\')','queue','queues',5),(59,'t(\'queuesmember\')','queuemember','queuesmember',5),(60,'t(\'callsummarybymonth\')','callsummarybymonth','callsummarybymonth',9),(61,'t(\'buycredit\')','buycredit','buycredit',1),(62,'t(\'Restrict Phone\')','campaignrestrictphone','restrictedphonenumber',13),(63,'t(\'Quick Campaign\')','campaignsend','campaignsend',13),(64,'t(\'Log Users\')','logusers','diddestination',12),(65,'t(\'Call per Minutes\')','campaignlog','diddestination',12),(66,'t(\'User Rate\')','userrate','callback',8),(67,'t(\'Call Failed\')','callfailed','cdr',9),(68,'t(\'Buy DID\')','didbuy','buycredit',5),(69,'t(\'Extra\')','extra','buycredit',12),(70,'t(\'Trunk Report\')','trunkreport','campaignpollinfo',9),(71,'t(\'Dashboard\')','dashboard','callback',12),(72,'t(\'Smtp\')','smtps','templatemail',12),(73,'t(\'CallShop per Day\')','callsummarycallshop','callsummary',14),(77,'t(\'Servers\')','servers','tariffs',10),(78,'t(\'Queue DashBoard\')','dashboardqueue','tariffs',5),(79,'t(\'Transfer to Mobile\')','transfertomobile','refillprovider',1),(80,'t(\'callsummaryperuser\')','callsummaryperuser','callsummarybymonth',9),(81,'t(\'callsummaryperagent\')','callsummaryperagent','callsummarybymonth',9),(82,'t(\'Firewall\')','firewall','modules',12),(83,'t(\'Iax\')','iax','sipbuddies',1);
/*!40000 ALTER TABLE `pkg_module` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_offer`
--

DROP TABLE IF EXISTS `pkg_offer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_offer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `label` varchar(70) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `packagetype` int(11) NOT NULL,
  `billingtype` int(11) NOT NULL,
  `startday` int(11) NOT NULL,
  `freetimetocall` int(11) NOT NULL,
  `price` decimal(10,3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_offer`
--

LOCK TABLES `pkg_offer` WRITE;
/*!40000 ALTER TABLE `pkg_offer` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_offer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_offer_cdr`
--

DROP TABLE IF EXISTS `pkg_offer_cdr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_offer_cdr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_offer` int(11) NOT NULL,
  `date_consumption` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `used_secondes` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ind_pkg_offer_cdr_date_consumption` (`date_consumption`),
  KEY `fk_pkg_user_pkg_offer_cdr` (`id_user`),
  KEY `fk_pkg_offer_pkg_offer_cdr` (`id_offer`),
  CONSTRAINT `fk_pkg_offer_pkg_offer_cdr` FOREIGN KEY (`id_offer`) REFERENCES `pkg_offer` (`id`),
  CONSTRAINT `fk_pkg_user_pkg_offer_cdr` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_offer_cdr`
--

LOCK TABLES `pkg_offer_cdr` WRITE;
/*!40000 ALTER TABLE `pkg_offer_cdr` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_offer_cdr` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_offer_use`
--

DROP TABLE IF EXISTS `pkg_offer_use`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_offer_use` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `id_offer` int(11) NOT NULL,
  `reservationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `releasedate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` int(11) DEFAULT '0',
  `month_payed` int(11) DEFAULT '0',
  `reminded` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_pkg_user_pkg_offer_use` (`id_user`),
  KEY `fk_pkg_offer_pkg_offer_use` (`id_offer`),
  CONSTRAINT `fk_pkg_offer_pkg_offer_use` FOREIGN KEY (`id_offer`) REFERENCES `pkg_offer` (`id`),
  CONSTRAINT `fk_pkg_user_pkg_offer_use` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_offer_use`
--

LOCK TABLES `pkg_offer_use` WRITE;
/*!40000 ALTER TABLE `pkg_offer_use` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_offer_use` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_phonebook`
--

DROP TABLE IF EXISTS `pkg_phonebook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_phonebook` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `name` char(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `description` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_pkg_user_pkg_phonebook` (`id_user`),
  CONSTRAINT `fk_pkg_user_pkg_phonebook` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_phonebook`
--

LOCK TABLES `pkg_phonebook` WRITE;
/*!40000 ALTER TABLE `pkg_phonebook` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_phonebook` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_phonenumber`
--

DROP TABLE IF EXISTS `pkg_phonenumber`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_phonenumber` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_phonebook` int(11) NOT NULL,
  `number` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `name` char(40) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `creationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` smallint(6) NOT NULL DEFAULT '1',
  `info` mediumtext CHARACTER SET utf8 COLLATE utf8_bin,
  `try` smallint(1) NOT NULL DEFAULT '0',
  `city` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pkg_phonebook_pkg_phonenumber` (`id_phonebook`),
  KEY `number` (`number`),
  CONSTRAINT `fk_pkg_phonebook_pkg_phonenumber` FOREIGN KEY (`id_phonebook`) REFERENCES `pkg_phonebook` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_phonenumber`
--

LOCK TABLES `pkg_phonenumber` WRITE;
/*!40000 ALTER TABLE `pkg_phonenumber` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_phonenumber` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_plan`
--

DROP TABLE IF EXISTS `pkg_plan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_plan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL DEFAULT '0',
  `name` char(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `lcrtype` int(11) NOT NULL DEFAULT '0',
  `creationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `removeinterprefix` int(11) NOT NULL DEFAULT '1',
  `signup` int(11) NOT NULL DEFAULT '0',
  `portabilidadeMobile` tinyint(1) NOT NULL DEFAULT '0',
  `portabilidadeFixed` tinyint(1) NOT NULL DEFAULT '0',
  `ini_credit` decimal(10,5) NOT NULL DEFAULT '0.00000',
  `techprefix` varchar(7) NOT NULL DEFAULT '',
  `play_audio` smallint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_pkg_user_pkg_plan` (`id_user`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `fk_pkg_user_pkg_plan` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_plan`
--

LOCK TABLES `pkg_plan` WRITE;
/*!40000 ALTER TABLE `pkg_plan` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_plan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_prefix`
--

DROP TABLE IF EXISTS `pkg_prefix`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_prefix` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prefix` varchar(18) NOT NULL,
  `destination` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `prefix` (`prefix`),
  KEY `destination` (`destination`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_prefix`
--

LOCK TABLES `pkg_prefix` WRITE;
/*!40000 ALTER TABLE `pkg_prefix` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_prefix` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_provider`
--

DROP TABLE IF EXISTS `pkg_provider`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_provider` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_name` char(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `creationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` mediumtext CHARACTER SET utf8 COLLATE utf8_bin,
  `credit` decimal(20,5) NOT NULL DEFAULT '0.00000',
  `credit_control` smallint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cons_pkg_provider_provider_name` (`provider_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_provider`
--

LOCK TABLES `pkg_provider` WRITE;
/*!40000 ALTER TABLE `pkg_provider` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_provider` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_queue`
--

DROP TABLE IF EXISTS `pkg_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `language` varchar(5) NOT NULL,
  `musiconhold` varchar(128) DEFAULT NULL,
  `announce` varchar(128) DEFAULT NULL,
  `context` varchar(128) DEFAULT NULL,
  `timeout` int(11) DEFAULT NULL,
  `announce-frequency` int(11) DEFAULT NULL,
  `announce-round-seconds` int(11) DEFAULT NULL,
  `announce-holdtime` varchar(128) DEFAULT NULL,
  `announce-position` varchar(5) NOT NULL DEFAULT 'yes',
  `retry` int(11) DEFAULT NULL,
  `wrapuptime` int(11) DEFAULT NULL,
  `maxlen` int(11) DEFAULT NULL,
  `servicelevel` int(11) DEFAULT NULL,
  `strategy` varchar(128) DEFAULT NULL,
  `joinempty` varchar(128) DEFAULT NULL,
  `leavewhenempty` varchar(128) DEFAULT NULL,
  `eventmemberstatus` tinyint(1) DEFAULT NULL,
  `eventwhencalled` tinyint(1) DEFAULT NULL,
  `reportholdtime` tinyint(1) DEFAULT NULL,
  `memberdelay` int(11) DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  `timeoutrestart` tinyint(1) DEFAULT NULL,
  `periodic-announce` varchar(200) DEFAULT NULL,
  `periodic-announce-frequency` int(11) DEFAULT NULL,
  `ringinuse` varchar(3) DEFAULT NULL,
  `setinterfacevar` varchar(3) DEFAULT 'yes',
  `setqueuevar` varchar(3) NOT NULL DEFAULT 'yes',
  `setqueueentryvar` varchar(3) NOT NULL DEFAULT 'yes',
  `var_holdtime` int(11) NOT NULL DEFAULT '0',
  `var_talktime` int(11) NOT NULL DEFAULT '0',
  `var_totalCalls` int(11) NOT NULL DEFAULT '0',
  `var_answeredCalls` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `fk_pkg_user_pkg_queue` (`id_user`),
  CONSTRAINT `fk_pkg_user_pkg_queue` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_queue`
--

LOCK TABLES `pkg_queue` WRITE;
/*!40000 ALTER TABLE `pkg_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_queue_agent_status`
--

DROP TABLE IF EXISTS `pkg_queue_agent_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_queue_agent_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `id_queue` int(11) NOT NULL,
  `agentName` varchar(40) DEFAULT NULL,
  `agentStatus` varchar(30) DEFAULT NULL,
  `totalCalls` int(11) NOT NULL DEFAULT '0',
  `last_call` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_queue` (`id_queue`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_queue_agent_status`
--

LOCK TABLES `pkg_queue_agent_status` WRITE;
/*!40000 ALTER TABLE `pkg_queue_agent_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_queue_agent_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_queue_member`
--

DROP TABLE IF EXISTS `pkg_queue_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_queue_member` (
  `uniqueid` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `membername` varchar(40) DEFAULT NULL,
  `queue_name` varchar(128) DEFAULT NULL,
  `interface` varchar(128) DEFAULT NULL,
  `penalty` int(11) DEFAULT NULL,
  `paused` tinyint(11) DEFAULT NULL,
  PRIMARY KEY (`uniqueid`),
  UNIQUE KEY `queue_interface` (`queue_name`,`interface`),
  KEY `fk_pkg_user_pkg_queue_member` (`id_user`),
  CONSTRAINT `fk_pkg_user_pkg_queue_member` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_queue_member`
--

LOCK TABLES `pkg_queue_member` WRITE;
/*!40000 ALTER TABLE `pkg_queue_member` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_queue_member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_queue_status`
--

DROP TABLE IF EXISTS `pkg_queue_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_queue_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_queue` int(11) DEFAULT NULL,
  `id_agent` int(11) DEFAULT NULL,
  `callId` varchar(40) NOT NULL,
  `callerId` varchar(60) NOT NULL,
  `status` varchar(30) NOT NULL,
  `time` timestamp NULL DEFAULT NULL,
  `queue_name` varchar(25) NOT NULL,
  `priority` int(11) NOT NULL DEFAULT '0',
  `channel` varchar(50) NOT NULL,
  `holdtime` varchar(11) DEFAULT '',
  `totalCalls` int(11) DEFAULT NULL,
  `answeredCalls` int(11) DEFAULT NULL,
  `callduration` int(11) DEFAULT NULL,
  `oldtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `callerId` (`callerId`),
  KEY `status` (`status`),
  KEY `timestamp` (`time`),
  KEY `queue_name` (`queue_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_queue_status`
--

LOCK TABLES `pkg_queue_status` WRITE;
/*!40000 ALTER TABLE `pkg_queue_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_queue_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_rate`
--

DROP TABLE IF EXISTS `pkg_rate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_rate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_plan` int(11) NOT NULL,
  `id_trunk` int(11) NOT NULL,
  `id_prefix` int(11) NOT NULL,
  `buyrate` decimal(15,6) DEFAULT '0.000000',
  `buyrateinitblock` int(11) NOT NULL DEFAULT '0',
  `buyrateincrement` int(11) NOT NULL DEFAULT '0',
  `rateinitial` decimal(15,6) DEFAULT '0.000000',
  `initblock` int(11) NOT NULL DEFAULT '0',
  `billingblock` int(11) NOT NULL DEFAULT '0',
  `connectcharge` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `disconnectcharge` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `startdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `stopdate` timestamp NOT NULL DEFAULT '2033-01-01 12:00:00',
  `starttime` smallint(5) unsigned DEFAULT '0',
  `endtime` smallint(5) unsigned DEFAULT '10079',
  `musiconhold` char(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `additional_grace` varchar(11) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '0',
  `minimal_time_charge` int(2) NOT NULL DEFAULT '0',
  `package_offer` smallint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `minimal_time_buy` smallint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_pkg_plan_pkg_rate` (`id_plan`),
  KEY `fk_pkg_prefix_pkg_rate` (`id_prefix`),
  KEY `fk_pkg_trunk_pkg_rate` (`id_trunk`),
  CONSTRAINT `fk_pkg_plan_pkg_rate` FOREIGN KEY (`id_plan`) REFERENCES `pkg_plan` (`id`),
  CONSTRAINT `fk_pkg_prefix_pkg_rate` FOREIGN KEY (`id_prefix`) REFERENCES `pkg_prefix` (`id`),
  CONSTRAINT `fk_pkg_trunk_pkg_rate` FOREIGN KEY (`id_trunk`) REFERENCES `pkg_trunk` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_rate`
--

LOCK TABLES `pkg_rate` WRITE;
/*!40000 ALTER TABLE `pkg_rate` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_rate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_rate_agent`
--

DROP TABLE IF EXISTS `pkg_rate_agent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_rate_agent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_plan` int(11) NOT NULL,
  `id_prefix` int(11) NOT NULL,
  `rateinitial` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `initblock` int(11) NOT NULL DEFAULT '0',
  `billingblock` int(11) NOT NULL DEFAULT '0',
  `minimal_time_charge` smallint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_pkg_plan_pkg_rate_agent` (`id_plan`),
  KEY `fk_pkg_prefix_pkg_rate_agent` (`id_prefix`),
  CONSTRAINT `fk_pkg_plan_pkg_rate_agent` FOREIGN KEY (`id_plan`) REFERENCES `pkg_plan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pkg_prefix_pkg_rate_agent` FOREIGN KEY (`id_prefix`) REFERENCES `pkg_prefix` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_rate_agent`
--

LOCK TABLES `pkg_rate_agent` WRITE;
/*!40000 ALTER TABLE `pkg_rate_agent` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_rate_agent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_rate_callshop`
--

DROP TABLE IF EXISTS `pkg_rate_callshop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_rate_callshop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `dialprefix` char(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `destination` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `buyrate` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `minimo` int(10) NOT NULL DEFAULT '1',
  `block` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `dialprefix` (`dialprefix`),
  KEY `fk_pkg_user_pkg_rate_callshop` (`id_user`),
  CONSTRAINT `fk_pkg_user_pkg_rate_callshop` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_rate_callshop`
--

LOCK TABLES `pkg_rate_callshop` WRITE;
/*!40000 ALTER TABLE `pkg_rate_callshop` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_rate_callshop` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_refill`
--

DROP TABLE IF EXISTS `pkg_refill`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_refill` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `credit` decimal(15,5) NOT NULL,
  `description` mediumtext CHARACTER SET utf8 COLLATE utf8_bin,
  `refill_type` tinyint(4) NOT NULL DEFAULT '0',
  `payment` tinyint(1) DEFAULT '0',
  `invoice_number` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `fk_pkg_user_pkg_logrefill` (`id_user`),
  CONSTRAINT `fk_pkg_user_pkg_logrefill` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_refill`
--

LOCK TABLES `pkg_refill` WRITE;
/*!40000 ALTER TABLE `pkg_refill` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_refill` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_refill_icepay`
--

DROP TABLE IF EXISTS `pkg_refill_icepay`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_refill_icepay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `credit` decimal(15,5) NOT NULL,
  `description` mediumtext CHARACTER SET utf8 COLLATE utf8_bin,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_refill_icepay`
--

LOCK TABLES `pkg_refill_icepay` WRITE;
/*!40000 ALTER TABLE `pkg_refill_icepay` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_refill_icepay` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_refill_provider`
--

DROP TABLE IF EXISTS `pkg_refill_provider`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_refill_provider` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_provider` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `credit` decimal(15,5) NOT NULL,
  `description` mediumtext CHARACTER SET utf8 COLLATE utf8_bin,
  `payment` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_pkg_provider_pkg_logrefill_provider` (`id_provider`),
  CONSTRAINT `fk_pkg_provider_pkg_logrefill_provider` FOREIGN KEY (`id_provider`) REFERENCES `pkg_provider` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_refill_provider`
--

LOCK TABLES `pkg_refill_provider` WRITE;
/*!40000 ALTER TABLE `pkg_refill_provider` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_refill_provider` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_restrict_phone`
--

DROP TABLE IF EXISTS `pkg_restrict_phone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_restrict_phone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `number` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pkg_user_pkg_restricted_phonenumber` (`id_user`),
  KEY `number` (`number`),
  CONSTRAINT `fk_pkg_user_pkg_restricted_phonenumber` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_restrict_phone`
--

LOCK TABLES `pkg_restrict_phone` WRITE;
/*!40000 ALTER TABLE `pkg_restrict_phone` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_restrict_phone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_servers`
--

DROP TABLE IF EXISTS `pkg_servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(50) NOT NULL,
  `port` char(10) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `type` varchar(20) NOT NULL DEFAULT 'freeswitch',
  `weight` int(1) NOT NULL DEFAULT '1',
  `description` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `host` (`host`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_servers`
--

LOCK TABLES `pkg_servers` WRITE;
/*!40000 ALTER TABLE `pkg_servers` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_servers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_sip`
--

DROP TABLE IF EXISTS `pkg_sip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_sip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `accountcode` varchar(30) NOT NULL,
  `regexten` varchar(40) DEFAULT NULL,
  `amaflags` varchar(40) DEFAULT NULL,
  `callcounter` enum('yes','no') DEFAULT NULL,
  `busylevel` int(11) DEFAULT NULL,
  `allowoverlap` enum('yes','no') DEFAULT NULL,
  `allowsubscribe` enum('yes','no') DEFAULT NULL,
  `videosupport` enum('yes','no') DEFAULT NULL,
  `callgroup` char(10) DEFAULT NULL,
  `callerid` varchar(80) DEFAULT NULL,
  `context` varchar(80) DEFAULT NULL,
  `DEFAULTip` char(15) DEFAULT NULL,
  `dtmfmode` char(7) DEFAULT 'RFC2833',
  `fromuser` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `fromdomain` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `host` varchar(31) DEFAULT NULL,
  `insecure` varchar(20) DEFAULT NULL,
  `language` char(2) DEFAULT NULL,
  `mailbox` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `session-timers` enum('accept','refuse','originate') DEFAULT NULL,
  `session-expires` int(11) DEFAULT NULL,
  `session-minse` int(11) DEFAULT NULL,
  `session-refresher` enum('uac','uas') DEFAULT NULL,
  `t38pt_usertpsource` varchar(40) DEFAULT NULL,
  `md5secret` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `nat` char(20) DEFAULT 'yes',
  `deny` varchar(95) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `permit` varchar(95) DEFAULT NULL,
  `pickupgroup` char(10) DEFAULT NULL,
  `port` int(5) DEFAULT NULL,
  `qualify` char(7) DEFAULT 'yes',
  `rtptimeout` int(11) DEFAULT NULL,
  `rtpholdtimeout` int(11) DEFAULT NULL,
  `secret` varchar(80) DEFAULT NULL,
  `type` char(6) DEFAULT 'friend',
  `disallow` varchar(100) DEFAULT 'all',
  `allow` varchar(100) DEFAULT NULL,
  `regseconds` int(11) DEFAULT '0',
  `ipaddr` char(45) DEFAULT NULL,
  `fullcontact` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `setvar` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `regserver` varchar(20) DEFAULT NULL,
  `lastms` varchar(11) DEFAULT NULL,
  `defaultuser` varchar(40) DEFAULT NULL,
  `auth` varchar(10) DEFAULT NULL,
  `subscribemwi` varchar(10) DEFAULT NULL,
  `vmexten` varchar(20) DEFAULT NULL,
  `cid_number` varchar(40) DEFAULT NULL,
  `callingpres` varchar(20) DEFAULT NULL,
  `usereqphone` varchar(10) DEFAULT NULL,
  `mohsuggest` varchar(20) DEFAULT NULL,
  `allowtransfer` varchar(20) DEFAULT 'no',
  `autoframing` varchar(10) DEFAULT NULL,
  `maxcallbitrate` int(11) DEFAULT NULL,
  `rfc2833compensate` enum('yes','no') DEFAULT NULL,
  `outboundproxy` varchar(40) DEFAULT NULL,
  `rtpkeepalive` int(11) DEFAULT NULL,
  `useragent` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `calllimit` int(11) DEFAULT NULL,
  `status` smallint(1) DEFAULT '1',
  `directmedia` enum('yes','no','nonat','update') DEFAULT 'no',
  `sippasswd` varchar(50) DEFAULT NULL,
  `callshopnumber` varchar(15) DEFAULT NULL,
  `callshoptime` int(11) DEFAULT '0',
  `callbackextension` varchar(40) DEFAULT NULL,
  `group` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `host` (`host`),
  KEY `ipaddr` (`ipaddr`),
  KEY `port` (`port`),
  KEY `pkg_sip_hp_index` (`host`,`port`),
  KEY `pkg_sip_ip_index` (`ipaddr`,`port`),
  KEY `fk_pkg_user_pkg_sip_buddies` (`id_user`),
  KEY `name` (`name`),
  CONSTRAINT `fk_pkg_user_pkg_sip_buddies` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_sip`
--

LOCK TABLES `pkg_sip` WRITE;
/*!40000 ALTER TABLE `pkg_sip` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_sip` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_sipura`
--

DROP TABLE IF EXISTS `pkg_sipura`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_sipura` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `macadr` varchar(12) NOT NULL DEFAULT '',
  `senha_admin` varchar(8) NOT NULL DEFAULT '',
  `senha_user` varchar(8) NOT NULL DEFAULT '',
  `antireset` char(3) NOT NULL DEFAULT '',
  `lastmov` varchar(20) NOT NULL DEFAULT '',
  `estado` char(2) NOT NULL DEFAULT '',
  `obs` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(60) NOT NULL DEFAULT '',
  `DHCP` char(3) NOT NULL DEFAULT 'yes',
  `Static_IP` varchar(15) NOT NULL DEFAULT '',
  `NetMask` varchar(15) NOT NULL DEFAULT '',
  `Gateway` varchar(15) NOT NULL DEFAULT '',
  `Primary_DNS` varchar(15) NOT NULL DEFAULT '',
  `Secondary_DNS` varchar(15) NOT NULL DEFAULT '',
  `Provision_Enable` char(3) NOT NULL DEFAULT 'yes',
  `Profile_Rule` varchar(60) DEFAULT 'http://billing.magnusbilling.com/x.php',
  `SIP_Port_1` varchar(5) NOT NULL DEFAULT '5060',
  `Proxy_1` varchar(60) NOT NULL DEFAULT '',
  `Use_Outbound_Proxy_1` char(3) NOT NULL DEFAULT 'no',
  `Outbound_Proxy_1` varchar(60) NOT NULL DEFAULT '',
  `Use_OB_Proxy_In_Dialog_1` char(3) NOT NULL DEFAULT 'yes',
  `Register_1` char(3) NOT NULL DEFAULT 'yes',
  `Make_Call_Without_Reg_1` char(3) NOT NULL DEFAULT 'no',
  `Register_Expires_1` varchar(4) NOT NULL DEFAULT '90',
  `Ans_Call_Without_Reg_1` char(3) NOT NULL DEFAULT 'no',
  `Use_DNS_SRV_1` char(3) NOT NULL DEFAULT 'yes',
  `DNS_SRV_Auto_Prefix_1` char(3) NOT NULL DEFAULT 'no',
  `Proxy_Fallback_Intvl_1` varchar(4) NOT NULL DEFAULT '3600',
  `Voice_Mail_Server_1` varchar(30) NOT NULL DEFAULT '',
  `SIP_Port_2` varchar(5) NOT NULL DEFAULT '5061',
  `Proxy_2` varchar(60) NOT NULL DEFAULT '',
  `Use_Outbound_Proxy_2` char(3) NOT NULL DEFAULT 'no',
  `Outbound_Proxy_2` varchar(60) NOT NULL DEFAULT '',
  `Use_OB_Proxy_In_Dialog_2` char(3) NOT NULL DEFAULT 'yes',
  `Register_2` char(3) NOT NULL DEFAULT 'yes',
  `Make_Call_Without_Reg_2` char(3) NOT NULL DEFAULT 'no',
  `Register_Expires_2` varchar(4) NOT NULL DEFAULT '120',
  `Ans_Call_Without_Reg_2` char(3) NOT NULL DEFAULT 'no',
  `Use_DNS_SRV_2` char(3) NOT NULL DEFAULT 'yes',
  `DNS_SRV_Auto_Prefix_2` char(3) NOT NULL DEFAULT 'no',
  `Proxy_Fallback_Intvl_2` varchar(4) NOT NULL DEFAULT '3600',
  `Voice_Mail_Server_2` varchar(30) NOT NULL DEFAULT '',
  `Display_Name_1` varchar(30) NOT NULL DEFAULT '',
  `Display_Name_2` varchar(30) NOT NULL DEFAULT '',
  `User_ID_1` varchar(25) NOT NULL DEFAULT '',
  `User_ID_2` varchar(25) NOT NULL DEFAULT '',
  `Password_1` varchar(25) NOT NULL DEFAULT '',
  `Password_2` varchar(25) NOT NULL DEFAULT '',
  `Use_Auth_ID_1` char(3) NOT NULL DEFAULT 'no',
  `Use_Auth_ID_2` char(3) NOT NULL DEFAULT '',
  `Auth_ID_1` varchar(25) NOT NULL DEFAULT '',
  `Auth_ID_2` varchar(25) NOT NULL DEFAULT '',
  `Preferred_Codec_1` varchar(8) NOT NULL DEFAULT 'G729a',
  `Preferred_Codec_2` varchar(8) NOT NULL DEFAULT 'G729a',
  `Use_Pref_Codec_Only_1` char(3) NOT NULL DEFAULT 'no',
  `Use_Pref_Codec_Only_2` char(3) NOT NULL DEFAULT 'no',
  `Dial_Plan_1` longtext NOT NULL,
  `Dial_Plan_2` longtext NOT NULL,
  `Caller_Conn_Polarity_1` varchar(10) NOT NULL DEFAULT 'Forward',
  `Caller_Conn_Polarity_2` varchar(10) NOT NULL DEFAULT 'Forward',
  `altera` char(3) NOT NULL DEFAULT 'si',
  `fultmov` varchar(30) NOT NULL DEFAULT '0000-00-00',
  `fultlig` varchar(30) NOT NULL DEFAULT '0000-00-00',
  `Enable_Web_Server` char(3) NOT NULL DEFAULT 'yes',
  `origem` varchar(5) NOT NULL DEFAULT '',
  `username` varchar(20) NOT NULL DEFAULT 'patel',
  `last_ip` varchar(15) NOT NULL DEFAULT '',
  `last_port` varchar(5) NOT NULL DEFAULT '',
  `Web_Server_Port` varchar(5) NOT NULL DEFAULT '80',
  `conf01` char(1) NOT NULL DEFAULT '',
  `conf02` char(1) NOT NULL DEFAULT '',
  `conf03` char(1) NOT NULL DEFAULT '',
  `borrado` char(2) NOT NULL DEFAULT '',
  `marca` char(2) NOT NULL DEFAULT 'X',
  `contador` int(5) NOT NULL DEFAULT '0',
  `nserie` varchar(15) NOT NULL DEFAULT '',
  `Callee_Conn_Polarity_1` varchar(10) NOT NULL DEFAULT 'Forward',
  `Callee_Conn_Polarity_2` varchar(10) NOT NULL DEFAULT 'Forward',
  `Idle_Conn_Polarity_1` varchar(10) NOT NULL DEFAULT 'Forward',
  `Idle_Conn_Polarity_2` varchar(10) NOT NULL DEFAULT 'Forward',
  `Jitter_1` varchar(20) NOT NULL DEFAULT 'high',
  `Jitter_2` varchar(20) NOT NULL DEFAULT 'high',
  `RTP_Packet_Size` varchar(10) NOT NULL DEFAULT '0.030',
  `AVT_Dynamic_Payload` varchar(10) NOT NULL DEFAULT '101',
  `STUN_Enable` char(3) NOT NULL DEFAULT 'no',
  `STUN_Server` varchar(80) NOT NULL DEFAULT '',
  `Dial_Tone` varchar(80) NOT NULL DEFAULT '420@-16;10(*/0/1)',
  `Busy_Tone` varchar(80) NOT NULL DEFAULT '480@-19,620@-19;10(.5/.5/1+2)',
  `Reorder_Tone` varchar(80) NOT NULL DEFAULT '480@-19,620@-19;10(.25/.25/1+2)',
  `Ring_Back_Tone` varchar(80) NOT NULL DEFAULT '440@-19,480@-19;*(2/4/1+2)',
  `Hook_Flash_Timer_Min` varchar(10) NOT NULL DEFAULT '.1',
  `Hook_Flash_Timer_Max` varchar(10) NOT NULL DEFAULT '.9',
  `Time_Zone` varchar(20) NOT NULL DEFAULT 'GMT',
  `FXS_Port_Input_Gain` varchar(10) NOT NULL DEFAULT '-3',
  `FXS_Port_Output_Gain` varchar(10) NOT NULL DEFAULT '-3',
  `Echo_Supp_Enable_1_` char(3) NOT NULL DEFAULT 'yes',
  `Echo_Supp_Enable_2_` char(3) NOT NULL DEFAULT 'yes',
  `NAT_Keep_Alive_Enable_1_` char(3) NOT NULL DEFAULT 'no',
  `NAT_Keep_Alive_Enable_2_` char(3) NOT NULL DEFAULT 'no',
  `NAT_Mapping_Enable_1_` char(3) NOT NULL DEFAULT 'no',
  `NAT_Mapping_Enable_2_` char(3) NOT NULL DEFAULT 'no',
  `NAT_Keep_Alive_Dest_1_` varchar(80) DEFAULT '$PROXY',
  `NAT_Keep_Alive_Dest_2_` varchar(80) DEFAULT '$PROXY',
  `SIP_TOS_DiffServ_Value_1_` varchar(20) NOT NULL DEFAULT '0x68',
  `SIP_TOS_DiffServ_Value_2_` varchar(20) NOT NULL DEFAULT '0x68',
  `RTP_TOS_DiffServ_Value_1_` varchar(20) NOT NULL DEFAULT '0xb8',
  `RTP_TOS_DiffServ_Value_2_` varchar(20) NOT NULL DEFAULT '0xb8',
  `Upgrade_Enable` char(3) NOT NULL DEFAULT 'no',
  `Upgrade_Rule` varchar(80) NOT NULL DEFAULT '',
  `falta` date NOT NULL DEFAULT '0000-00-00',
  `Silence_Supp_Enable_1_` char(3) NOT NULL DEFAULT 'no',
  `Silence_Supp_Enable_2_` char(3) NOT NULL DEFAULT 'no',
  `DTMF_Tx_Method_1_` varchar(20) NOT NULL DEFAULT 'Auto',
  `DTMF_Tx_Method_2_` varchar(20) NOT NULL DEFAULT 'Auto',
  `Enable_IP_Dialing_1_` char(3) NOT NULL DEFAULT 'no',
  `Enable_IP_Dialing_2_` char(3) NOT NULL DEFAULT 'no',
  `SAS_DLG_Refresh_Intvl_1_` varchar(10) NOT NULL DEFAULT '30',
  `SAS_DLG_Refresh_Intvl_2_` varchar(10) NOT NULL DEFAULT '30',
  `SAS_Enable_1_` char(3) NOT NULL DEFAULT 'no',
  `SAS_Enable_2_` char(3) NOT NULL DEFAULT 'no',
  `ruleb` varchar(20) NOT NULL DEFAULT '',
  `RTP_Port_Min` varchar(10) NOT NULL DEFAULT '16384',
  `RTP_Port_Max` varchar(10) NOT NULL DEFAULT '16482',
  `Syslog_Server` varchar(20) NOT NULL DEFAULT '',
  `Debug_Server` varchar(20) NOT NULL DEFAULT '',
  `Debug_Level` char(1) NOT NULL DEFAULT '0',
  `SIP_Debug_Option_1_` varchar(25) NOT NULL DEFAULT 'none',
  `SIP_Debug_Option_2_` varchar(25) NOT NULL DEFAULT 'none',
  `Blind_Attn_Xfer_Enable_1_` char(3) NOT NULL DEFAULT 'no',
  `Blind_Attn_Xfer_Enable_2_` char(3) NOT NULL DEFAULT 'no',
  `Xfer_When_Hangup_Conf_1_` char(3) NOT NULL DEFAULT 'yes',
  `Xfer_When_Hangup_Conf_2_` char(3) NOT NULL DEFAULT 'yes',
  `Three_Way_Call_Serv_1_` char(3) NOT NULL DEFAULT 'yes',
  `Three_Way_Call_Serv_2_` char(3) NOT NULL DEFAULT 'yes',
  `Attn_Transfer_Serv_1_` char(3) NOT NULL DEFAULT 'yes',
  `Attn_Transfer_Serv_2_` char(3) NOT NULL DEFAULT 'yes',
  `Three_Way_Conf_Serv_1_` char(3) NOT NULL DEFAULT 'yes',
  `Three_Way_Conf_Serv_2_` char(3) NOT NULL DEFAULT 'yes',
  `Unattn_Transfer_Serv_1_` char(3) NOT NULL DEFAULT 'yes',
  `Unattn_Transfer_Serv_2_` char(3) NOT NULL DEFAULT 'yes',
  `nombre` varchar(20) NOT NULL DEFAULT 'monica',
  `Handle_VIA_received` char(3) NOT NULL DEFAULT 'no',
  `Handle_VIA_rport` char(3) NOT NULL DEFAULT 'no',
  `Insert_VIA_received` char(3) NOT NULL DEFAULT 'no',
  `Insert_VIA_rport` char(3) NOT NULL DEFAULT 'no',
  `Substitute_VIA_Addr` char(3) NOT NULL DEFAULT 'no',
  `Send_Resp_To_Src_Port` char(3) NOT NULL DEFAULT 'no',
  `STUN_Test_Enable` char(3) NOT NULL DEFAULT 'no',
  `Interdigit_Long_Timer` char(3) NOT NULL DEFAULT '10',
  `Speed_Dial_2_1_` varchar(30) NOT NULL DEFAULT '',
  `Speed_Dial_3_1_` varchar(30) NOT NULL DEFAULT '',
  `Speed_Dial_4_1_` varchar(30) NOT NULL DEFAULT '',
  `Speed_Dial_5_1_` varchar(30) NOT NULL DEFAULT '',
  `Speed_Dial_6_1_` varchar(30) NOT NULL DEFAULT '',
  `Speed_Dial_7_1_` varchar(30) NOT NULL DEFAULT '',
  `Speed_Dial_8_1_` varchar(30) NOT NULL DEFAULT '',
  `Speed_Dial_9_1_` varchar(30) NOT NULL DEFAULT '',
  `Speed_Dial_2_2_` varchar(30) NOT NULL DEFAULT '',
  `Speed_Dial_3_2_` varchar(30) NOT NULL DEFAULT '',
  `Speed_Dial_4_2_` varchar(30) NOT NULL DEFAULT '',
  `Speed_Dial_5_2_` varchar(30) NOT NULL DEFAULT '',
  `Speed_Dial_6_2_` varchar(30) NOT NULL DEFAULT '',
  `Speed_Dial_7_2_` varchar(30) NOT NULL DEFAULT '',
  `Speed_Dial_8_2_` varchar(30) NOT NULL DEFAULT '',
  `Speed_Dial_9_2_` varchar(30) NOT NULL DEFAULT '',
  `Caller_ID_Method` varchar(80) NOT NULL DEFAULT '',
  UNIQUE KEY `interno` (`id`),
  KEY `email` (`email`,`id_user`),
  KEY `username` (`username`),
  KEY `fk_pkg_user_sipuras` (`id_user`),
  CONSTRAINT `fk_pkg_user_sipuras` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_sipura`
--

LOCK TABLES `pkg_sipura` WRITE;
/*!40000 ALTER TABLE `pkg_sipura` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_sipura` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_sms`
--

DROP TABLE IF EXISTS `pkg_sms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_sms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `prefix` int(11) NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `telephone` varchar(50) NOT NULL DEFAULT '',
  `sms` mediumtext NOT NULL,
  `result` varchar(50) NOT NULL DEFAULT '',
  `rate` decimal(15,5) NOT NULL DEFAULT '0.00000',
  PRIMARY KEY (`id`),
  KEY `fk_pkg_user_pkg_sms` (`id_user`),
  KEY `prefix` (`prefix`),
  KEY `date` (`date`),
  CONSTRAINT `fk_pkg_user_pkg_sms` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_sms`
--

LOCK TABLES `pkg_sms` WRITE;
/*!40000 ALTER TABLE `pkg_sms` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_sms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_smtp`
--

DROP TABLE IF EXISTS `pkg_smtp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_smtp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `host` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `username` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `password` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `port` varchar(10) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `encryption` varchar(10) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pkg_user_pkg_smtp` (`id_user`),
  CONSTRAINT `fk_pkg_user_pkg_smtp` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_smtp`
--

LOCK TABLES `pkg_smtp` WRITE;
/*!40000 ALTER TABLE `pkg_smtp` DISABLE KEYS */;
INSERT INTO `pkg_smtp` VALUES (1,1,'mail.magnusbilling.com','billing@magnusbilling.com','magnus','587','null');
/*!40000 ALTER TABLE `pkg_smtp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_templatemail`
--

DROP TABLE IF EXISTS `pkg_templatemail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_templatemail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `mailtype` char(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `fromemail` char(70) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `fromname` char(70) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `subject` varchar(130) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `messagehtml` varchar(3000) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `language` varchar(5) DEFAULT 'br',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_templatemail`
--

LOCK TABLES `pkg_templatemail` WRITE;
/*!40000 ALTER TABLE `pkg_templatemail` DISABLE KEYS */;
INSERT INTO `pkg_templatemail` VALUES (1,1,'signup','noreply@site.com','VoIP','Ativação de conta','<p>$firstname$ $lastname$, bem vindo ao nosso sistema de comunicação IP!</p>\n<p>Para concluir o processo de ativação de sua conta por favor clique no link abaixo:</p>\n<p><a href=\"http://ip/mbilling//index.php/signup/$idcard$?username=$cardnumber$&password=$password$&loginkey=$loginkey$\">\nhttp://ip/mbilling/index.php/signup/$idcard$?username=$cardnumber$&password=$password$&loginkey=$loginkey$</a></p>\n<br>\n<p>Por favor não esqueça de ativar sua conta.</p>\n<br>\n<p>  Usuário=$login$  </p>\n<p> Senha:$password$  </p>\n<br> \n<p>Atenciosamente,<br>','br'),(2,1,'reminder','noreply@site.com','VoIP','Sua conta esta com saldo baixo ( R$ $credit$)','<p>Olá $firstname$ $lastname$, </p>\n<p>Sua conta VoIP número $cardnumber$ está com saldo baixo.</p>\n<br>\n<p>Seu saldo atual é de R$ $credit$, valor abaixo do mánimo configurado em sua conta (R$ $credit_notification$,00).</p>\n<p>Adquiria novos créditos e não fique sem falar.</p>\n<br>  \n<p>Para adquirir créditos, entrar em contato conosco ou desativar o aviso de saldo baixo acesse sua Ã¡rea de cliente através de nosso site.</p>\n<p>Observação: Caso possua limite de crédito conosco ele Ã© somado ao seu saldo para o envio de notificação de saldo baixo.</p>\n<br> \n<p>Atenciosamente,<br>\n ','br'),(3,1,'signupconfirmed','noreply@site.com','VoIP','Bem Vindo','<p>Bem vindo !</p>\n<p>Configure seu softphone com os dados abaixo e comece a ligar agora mesmo:</p>\n<br>\n<p>Usuário=$login$  </p>\n<p>Senha:$password$  </p>\n<br> \n<p>Atenciosamente,<br>','br'),(4,1,'did_paid','noreply@site.com','VoIP','Pagamento de Numero Virtual - ($did$)','<p>SALDO DISPONÍVEL $balance_remaining$ $base_currency$</p>\n<p>Foi debitado automaticamente de seu saldo o valor de R$ $did_cost$,00   referente ao pagamento de seu número virtual (DID) ($did$).</p> <p>Custo mensal de seu número virtual: R$ $did_cost$,00</p> \n<br> \n<p>Atenciosamente,<br> ','br'),(5,1,'did_unpaid','noreply@site.com','VoIP','Notificação para pagamento de Número Virtual ($did$)','<p>Olá.</p>\n<p>Seu crédito atual (R$ $balance_remaining$ $base_currency$) não é suficiente para o pagamento mensal de seu número virtual DID ($did$).</p>\n<p>O custo mensal de seu DID é  $did_cost$ $base_currency$</p>\n<br>\n<p>Você tem $days_remaining$ para efetuar uma recarga no valor suficiente para o pagamento do seu número virtual.</p>\n<p>Qualquer dúvida por favor entre em contato através de nosso suporte<br> </p> \n<br> \n<p>Atenciosamente,<br>','br'),(6,1,'did_released','noreply@site.com','VoIP','Did Cancelado','<p>Olá</p> <p>Seu Número Virtual (DID) $did$ foi automaticamente cancelado até que o pagamento de sua sua ultima fatura seja confirmado. </p> <p>Durante esse período não será possí­vel receber chamadas através do mesmo.</p>\n<br> \n<p>Atenciosamente,<br>','br'),(7,1,'did_confirmation','noreply@site.com','VoIP','Número DID $did$ ativado','<p>Olá  $firstname$ $lastname$</p>\n<p>O número  $did$ foi ativado para a conta $login$.</p>\n<br>\n<p>Usuário=$login$  </p>\n<p>Senha:$password$  </p>\n<br> \n<p>Atenciosamente,<br>','br'),(8,1,'refill','noreply@site.com','VoIP','Recarga de crédito confirmada','<p>Olá $firstname$ $lastname$</p>\n<p>Uma recarga de crédito foi realizada com sucesso em sua conta $login$.</p>\n<p>Número da recarga = $itemID$</p>\n<br> \n<p>Valor = $itemAmount$ $base_currency$</p>\n<br>\n<p>Descrição= $description$</p>\n<br>\n<p>Seu crédito atual é de $credit$ $base_currency$</p>\n<br> \n<p>Atenciosamente,<br>\n','br'),(9,1,'plan_unpaid','noreply@site.com','VoIP','Aviso de Vencimento do Plano Mensal','<p>Estimado $firstname$ $lastname$. <br>\n  Enviamos esta mensagem para avisar que seu plano esta por vencer e não tem crédito para efetuar o pagamento. </p>\n<p>Por favor, entre em sua área de cliente, menu, Comprar Crédito, e compre mínimo $plancost$  $base_currency$ de crédito. </p>\n<br>\n<p>Usário=$login$  </p>\n<p>Senha:$password$  </p>\n<br> \n<p>Você tem $dias_vencimento$ dias para fazer o pagamento, ou seu plano sera cancelado.</p>\n<br> \n<p>Atenciosamente,<br>','br'),(10,1,'plan_paid','noreply@site.com','VoIP','Pagamento do plano recebido','<p>Estimado $firstname$ $lastname$.</p>\n<p>O pagamento do seu plano já foi efetuado.</p>\n<br>\n<p>Seu crédito atual é de $credit$ $base_currency$</p>\n<p>Usuário=$login$  </p>\n<p>Senha:$password$  </p>\n<br> \n<p>Atenciosamente,<br>','br'),(11,1,'plan_released','noreply@site.com','VoIP','Plano cancelado','<p>Estimado $firstname$ $lastname$.</p>\n<p>Seu plano foi cancelado por falta de pagamento</p>\n<br>\n<p>Usuário=$login$  </p>\n<p>Senha:$password$  </p>\n<br> \n<p>Atenciosamente,<br>','br'),(32,1,'signup','noreply@site.com','VoIP','Confirmación de email','<p style=\"color: rgb(0, 0, 0);\">$lastname$ $firstname$, gracias por registrarse con nosotros.</p>\n<p style=\"color: rgb(0, 0, 0);\">Por favor, copie y pegue el código a continuación en la página de registro para activar su cuenta. <br></p>\n<p style=\"color: rgb(0, 0, 0);\">$loginkey$</p>\n<p style=\"color: rgb(0, 0, 0);\">o haga clic aquí &nbsp;<a href=\"http://SU_IP/mbilling/index.php/signup/$iduser$?username=$login$&amp;password=$password$&amp;loginkey=$loginkey$\">http://SU_IP/mbilling/index.php/signup/$iduser$?username=$login$&amp;password=$password$&amp;loginkey=$loginkey$</a></p>\n<p style=\"color: rgb(0, 0, 0);\"><br>\n  \n\n\n  Por favor, asegúrese de activar su cuenta.</p><p style=\"color: rgb(0, 0, 0);\">Un cordial saludo<br></p>','es'),(33,1,'reminder','noreply@site.com','VoIP','Su cuenta VoIP tiene poco crédito ( $credit$ $currency$)','Hola $lastname$ $firstname$, su cuenta esta con poco crédito USUARIO $login$.\n\n<p>En la actualidad, solo hay $credit$ $currency$ en su cuenta, que es menor que el nivel de alerta definido por usted, que es de ($credit_notification$ ).</p>\n<p>Por favor recargue su cuenta lo antes posible para garantizar la continuidad del servicio.</p>\n<p><a href=\"http://SU_IP/mbilling\">http://SU_IP/mbilling</a><br></p><p>Nombre de usuario: $login$</p>\n<p>Contraseña: $password$</p>\n<p>Un cordial saludo</p>\n\n\n','es'),(34,1,'signupconfirmed','noreply@site.com','VoIP','Confirmación de registro','<p><strong>Ahora usted es un usuario registrado.</strong></p>\n<p>Para entrar en su panel de cliente:</p><p><br>\n  <a href=\"http://SU_IP/mbilling\">http://SU_IP/mbilling</a><br>\n  Su usuario es: $login$<br>\n  Su clave es : $password$</p>\n<p></p><p><br></p>\n<p>Un cordial saludo</p>\n','es'),(35,1,'did_paid','noreply@site.com','VoIP','Cobranza de su número DID - $did$','<p>Hola $lastname$ $firstname$.</p>\n<p>El importe de $did_cost$ $base_currency$ fue cobrado de su cuenta por el pago mensual de su DID  ($did$)</p>\n<p>Costo mensual por su DID : $did_cost$ $base_currency$</p>\n<p>El crédito actual de su cuenta $login$, es de $credit$  $base_currency$</p>\n<p>Un cordial saludo</p>\n<p><b>$obs$</b><br></p>','es'),(36,1,'did_unpaid','noreply@site.com','VoIP','Notificación de vencimiento de su número $did$','<p>Hola $firstname$ $lastname$, el crédito actual de su cuenta $login$ es de $balance_remaining$ $base_currency$</p>\n<p>Su crédito no es suficiente para pagar su número DID  ($did$), el costo mensual por su DID es de $did_cost$ $base_currency$</p>\n<p>Por favor agregue crédito a su cuenta antes del día de vencimiento del DID, o el DID será dado de baja automáticamente.</p>\n<p>Para cargar crédito, entre en su cuenta, y haga clic en el menú Comprar Crédito.<br>\n</p>\n<p>Un cordial saludo</p>\n<b>$obs$</b>','es'),(37,1,'did_released','noreply@site.com','VoIP','DID desactivado - ($did$)','<p>Hola $firstname$ $lastname$</p>\n<p>Esta es una notificación que el número DID  $did$ ha sido desactivado de su cuenta $login$.</p>\n<p>Se dará de baja definitivamente si no se renueva inmediatamente.<br>\n  <br>Una notificación anterior ha sido enviada al correo electrónico de contacto que aparece en su cuenta.</p>\n<p>Un cordial saludo<br><br></p>\n<b>$obs$</b>\n','es'),(38,1,'did_confirmation','noreply@site.com','VoIP','Activación de número Fijo DID','<p style=\"color: rgb(0, 0, 0);\">Hola  $firstname$ $lastname$, Felicitaciones! </p>\n<p style=\"color: rgb(0, 0, 0);\">El número  $did$ fue activado con éxito para la cuenta $login$.</p>\n<p style=\"color: rgb(0, 0, 0);\">Usted puede reenviar su número para un fijo o celular, para esto entre en su panel de cliente.</p><p style=\"color: rgb(0, 0, 0);\"><br><a href=\"http://SU_IP/mbilling\">\n  http://SU_IP/mbilling</a><br>\n  Nombre de usuario : $login$<br>\n  Contraseña : $password$</p>\n<p style=\"color: rgb(0, 0, 0);\">Un cordial saludo<br></p>','es'),(39,1,'refill','noreply@site.com','VoIP','Recarga de crédito confirmada','<p>Hola $firstname$ $lastname$</p>\n<p>Una recarga de crédito fue realizada con éxito en su cuenta $login$.</p>\n\n<p>Número de la recarga = $itemID$</p>\n<p>Importe = $itemAmount$ $base_currency$</p>\n<p>Descripción = $description$</p>\n\n<p>Su crédito actual es de $credit$ $base_currency$</p>\n<br>\nUn cordial saludo<br>\n','es'),(40,1,'plan_unpaid','noreply@site.com','VoIP','Aviso de Vencimiento del Plan Mensual','<p>Estimado $firstname$ $lastname$. <br>\n  Le enviamos este mensaje para comunicarle que su plan esta por vencer y no posee crédito para efectuar el pago. </p>\n<p>Ud. puede ingresar a su área de Cliente con estos datos:<br></p><p><br><a href=\"http://SU_IP/mbilling\">http://SU_IP/mbilling</a><br>\n  Usuario=$login$ <br>\n  Clave:$password$ </p>\n<p>Por favor recargue su cuenta o su plan sera dado de baja automáticamente en el dia del vencimiento. </p>\n<p>Un cordial saludo<br></p>\n<p><br></p>\n\n<b>$obs$</b>','es'),(41,1,'plan_paid','noreply@site.com','VoIP','Confirmación de pago del plan mensual recibido','<p>Estimado $firstname$ $lastname$.</p>\n<p>El pago de su plan mensual ya fue efectuado.</p>\n<p>Su crédito actual es de $credit$ $base_currency$</p>\n<p>Quedamos a su entera disposición ante  cualquier inquietud.</p>\n<p>Un cordial saludo</p>\n<b>$obs$</b>','es'),(42,1,'plan_released','noreply@site.com','VoIP','Su plan fue dado de baja.','<p>Estimado $firstname$ $lastname$.</p>\n<p>Su plan venció hoy y no recibimos la confirmación de pago.</p>\n<p>Su plan fue suspendido automáticamente hasta que nos sea informado el pago.</p>\n<p>Ud. puede ingresar a su Área de Cliente con estos datos:&nbsp;<br></p><p><a href=\"http://SU_IP/mbilling\">http://SU_IP/mbilling</a><br>\n  Usuario=$login$<br>\n  Clave:$password$</p>\n<p>Un cordial saludo<br></p>\n$obs$','es'),(43,1,'signup','noreply@site.com','VoIP','Account Activation','<p><span style=\"font-family: arial, sans, sans-serif;\">$lastname$ $firstname$, thank you for signing up with us</span><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Please click in this link to activate your account.</span><br style=\"font-family: arial, sans, sans-serif;\"><font face=\"arial, sans, sans-serif\"><a href=\"http://your_ip/mbilling/index.php/signup/$iduser$?username=$login$&amp;password=$password$&amp;loginkey=$loginkey$\">http://your_ip/mbilling/index.php/signup/$iduser$?username=$login$&amp;password=$password$&amp;loginkey=$loginkey$</a></font><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Please make sure to activate your account.</span><br style=\"font-family: arial, sans, sans-serif;\"><br></p><p><span style=\"font-family: arial, sans, sans-serif;\">Best regards</span></p>','en'),(44,1,'reminder','noreply@site.com','VoIP','Your VOIP Account has a low credit balance of ( $credit$ $currency$)','<span style=\"font-family: arial, sans, sans-serif;\">Hello $lastname$ $firstname$, your account is low on credit. User $login$.</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Right now, there is $credit$ $currency$ in your account, which is lower than the alert level you defined, ($credit_notification$ ).</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">To make sure your account continues working, please recharge your account as soon as possible.</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">If you don\'t want to receive this notification any longer or want to change the balance amount for this alert, please login in the customer web page and change the alerts settings as needed.</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><a moz-do-not-send=\"true\" href=\"http://your_ip/mbilling\" style=\"font-family: arial, sans, sans-serif;\">http://your_ip/mbilling</a><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Username: $login$</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Password: $password$</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Greetings</span><br>\n\n\n','en'),(45,1,'signupconfirmed','noreply@site.com','VoIP','Welcome. Your account login information in included','<p><span style=\"font-family: arial, sans, sans-serif;\">Hello $lastname$ $firstname$&nbsp;</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Here is you account infornation to access the client web site portal:</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><a moz-do-not-send=\"true\" href=\"http://your_ip/mbilling\" style=\"font-family: arial, sans, sans-serif;\">http://your_ip/mbilling</a><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Username : $login$&nbsp;</span><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Password : $password$&nbsp;</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Greetings</span><br></p>\n','en'),(46,1,'did_paid','noreply@site.com','VoIP','Your DID number charges - $did$','<p><span style=\"font-family: arial, sans, sans-serif;\">Hello $lastname$ $firstname$.</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">The following charge $did_cost$ $base_currency$, was charged to your account for the monthly fees for your DID ($did$)</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">DID monthly fees: $did_cost$ $base_currency$</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Your current credit balance for account $login$, is $credit$ $base_currency$</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Greetings</span><br></p>','en'),(47,1,'did_unpaid','noreply@site.com','VoIP','Notification alert, DID due date $did$','<p><span style=\"font-family: arial, sans, sans-serif;\">Hello $firstname$ $lastname$, your account $login$ current credit is $balance_remaining$ $base_currency$</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Your credit is insufficient to pay for your DID number ($did$), as the monthly cost is $did_cost$ $base_currency$</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Please add credit to your account so that the DID this is not released from your account automatically on the due date.</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">To add credits to your account, login to the client web site and click on Buy Credits.</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Greetings</span><br></p>','en'),(48,1,'did_released','noreply@site.com','VoIP','Cancelled DID - ($did$)','<p><span style=\"font-family: arial, sans, sans-serif;\">Hello $firstname$ $lastname$</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">This is a notification that the DID number $did$ has been deactivated from your account $login$.</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">It will be released if its not paid immediately.</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Past notifications has been send to the contact Email address set in your account.</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Greetings</span><br></p>','en'),(49,1,'did_confirmation','noreply@site.com','VoIP','DID number $did$ activated','<p style=\"color: rgb(0, 0, 0);\"><span style=\"font-family: arial, sans, sans-serif;\">Hello $firstname$ $lastname$,&nbsp;</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Congratulations!&nbsp;</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">The DID number $did$ was successfully activated in your account $login$.</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">You can forward your DID number to a PSTN or Mobile number.&nbsp; If you wish to do this, login to your account at:</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><a moz-do-not-send=\"true\" href=\"http://your_ip/mbilling\" style=\"font-family: arial, sans, sans-serif;\">http://your_ip/mbilling</a><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Username : $login$</span><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Password : $password$</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">If you have a problem getting it to forward to the desired number, please contact us.</span><br></p><p style=\"color: rgb(0, 0, 0);\"><span style=\"font-family: arial, sans, sans-serif;\"><br></span></p><p style=\"color: rgb(0, 0, 0);\"><span style=\"font-family: arial, sans, sans-serif;\">Greetings</span><span style=\"font-family: arial, sans, sans-serif;\"><br></span></p>','en'),(50,1,'refill','noreply@site.com','VoIP','Credit recharge confirmed','<p><span style=\"font-family: arial, sans, sans-serif;\">Hello $firstname$ $lastname$</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">A credit recharge was applied to the account $login$.</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Recharge number = $itemID$</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Value = $itemAmount$ $base_currency$</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Description = $description$</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Your current account credit balance is $credit$ $base_currency$</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Greetings</span><br></p>\n','en'),(51,1,'plan_unpaid','noreply@site.com','VoIP','Balance Due Alert for your Monthly plan','<p><span style=\"font-family: arial, sans, sans-serif;\">Dear $firstname$ $lastname$.&nbsp;</span><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">We are sending you this message to notify that your call plan is approaching the due date and your account does not have enough credit balance to make the payment.</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Please, login in you account area, select: Menu, Buy Credits, and recharge your account with at least $plancost$ $base_currency$.</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">You can login the client are with the following data:</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><a moz-do-not-send=\"true\" href=\"http://your_ip/mbilling\" style=\"font-family: arial, sans, sans-serif;\">http://your_ip/mbilling</a><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Username=$login$&nbsp;</span><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Password=$password$&nbsp;</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Please recharge your account or your call plan will be deactivated automatically on the due date.</span><br style=\"font-family: arial, sans, sans-serif;\"><br><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Greetings</span><br></p>','en'),(52,1,'plan_paid','noreply@site.com','VoIP','Confirmation of call plan payment','<p><span style=\"font-family: arial, sans, sans-serif;\">Dear $firstname$ $lastname$.</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">We have received your monthly call plan payment.</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Your current credit balance is $credit$ $base_currency$</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">If you have any questions or need additional help please contact us.</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Greetings</span><br></p>','en'),(53,1,'plan_released','noreply@site.com','VoIP','Your call plan has been suspended','<p><span style=\"font-family: arial, sans, sans-serif;\">Dear $firstname$ $lastname$.</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Your call plan is due today and we have not receive a payment confirmation.</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Your plan has been automatically suspended until we receive a payment confirmation.</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">To reactivate your call plan, please login in your account web site, select: Menu, Buy Credits and recharge your account with a minimum credit of $plancost$ $base_currency$.</span><br style=\"font-family: arial, sans, sans-serif;\"><br style=\"font-family: arial, sans, sans-serif;\"><span style=\"font-family: arial, sans, sans-serif;\">Greetings</span><br></p>','en');
/*!40000 ALTER TABLE `pkg_templatemail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_trunk`
--

DROP TABLE IF EXISTS `pkg_trunk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_trunk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_provider` int(11) NOT NULL,
  `failover_trunk` int(11) DEFAULT NULL,
  `trunkcode` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `host` varchar(100) NOT NULL,
  `fromdomain` varchar(100) NOT NULL,
  `trunkprefix` char(20) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `providertech` char(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `providerip` char(80) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `removeprefix` char(20) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `secondusedreal` int(11) DEFAULT '0',
  `call_answered` int(11) DEFAULT '0',
  `call_total` int(11) DEFAULT '0',
  `creationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `addparameter` char(120) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `inuse` int(11) DEFAULT '0',
  `maxuse` int(11) DEFAULT '-1',
  `status` int(11) DEFAULT '1',
  `if_max_use` int(11) DEFAULT '0',
  `user` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `secret` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `allow` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `link_sms` varchar(250) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `directmedia` char(6) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'no',
  `context` char(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'billing',
  `dtmfmode` char(7) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'RFC2833',
  `insecure` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'port,invite',
  `nat` char(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'yes',
  `qualify` char(7) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'yes',
  `type` char(6) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'peer',
  `disallow` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'all',
  `sms_res` varchar(50) NOT NULL,
  `register` int(11) NOT NULL DEFAULT '0',
  `language` varchar(10) NOT NULL,
  `allow_error` int(11) NOT NULL DEFAULT '0' COMMENT 'Allow send for backup trunk incluse busy',
  `short_time_call` int(11) NOT NULL DEFAULT '0',
  `fromuser` varchar(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `fk_pkg_provider_pkg_trunk` (`id_provider`),
  KEY `fk_pkg_trunk_pkg_trunk` (`failover_trunk`),
  CONSTRAINT `fk_pkg_provider_pkg_trunk` FOREIGN KEY (`id_provider`) REFERENCES `pkg_provider` (`id`),
  CONSTRAINT `fk_pkg_trunk_pkg_trunk` FOREIGN KEY (`failover_trunk`) REFERENCES `pkg_trunk` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_trunk`
--

LOCK TABLES `pkg_trunk` WRITE;
/*!40000 ALTER TABLE `pkg_trunk` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_trunk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_user`
--

DROP TABLE IF EXISTS `pkg_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `id_group` int(11) NOT NULL,
  `id_group_agent` int(11) DEFAULT NULL,
  `id_plan` int(11) DEFAULT NULL,
  `id_offer` int(11) DEFAULT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(100) NOT NULL,
  `credit` decimal(15,4) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `creationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `firstusedate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expirationdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `enableexpire` tinyint(1) DEFAULT '0',
  `expiredays` int(11) DEFAULT '0',
  `lastname` varchar(50) NOT NULL DEFAULT '',
  `firstname` varchar(50) NOT NULL DEFAULT '',
  `address` varchar(40) DEFAULT NULL,
  `city` varchar(50) NOT NULL DEFAULT '',
  `state` varchar(50) NOT NULL DEFAULT '',
  `country` varchar(50) NOT NULL DEFAULT '',
  `zipcode` varchar(20) DEFAULT '',
  `phone` varchar(50) NOT NULL DEFAULT '',
  `mobile` varchar(20) DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `vat` varchar(50) DEFAULT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `company_website` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `lastuse` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `typepaid` tinyint(1) DEFAULT '0',
  `creditlimit` int(11) NOT NULL DEFAULT '0',
  `language` char(5) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT 'en',
  `redial` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `loginkey` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `last_notification` timestamp NULL DEFAULT NULL,
  `credit_notification` int(11) NOT NULL DEFAULT '10',
  `restriction` tinyint(4) NOT NULL DEFAULT '0',
  `callingcard_pin` int(6) NOT NULL,
  `prefix_local` varchar(50) NOT NULL DEFAULT '',
  `callshop` varchar(20) DEFAULT '',
  `plan_day` tinyint(1) DEFAULT NULL,
  `record_call` tinyint(1) NOT NULL DEFAULT '0',
  `active_paypal` tinyint(1) NOT NULL DEFAULT '0',
  `boleto` tinyint(1) NOT NULL DEFAULT '0',
  `boleto_day` smallint(2) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `last_login` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `googleAuthenticator_enable` tinyint(1) NOT NULL DEFAULT '0',
  `google_authenticator_key` varchar(50) NOT NULL DEFAULT '',
  `doc` varchar(50) DEFAULT NULL,
  `id_sacado_sac` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `callingcard_pin` (`callingcard_pin`),
  KEY `fk_pkg_group_user_pkg_user` (`id_group`),
  KEY `fk_pkg_plan_pkg_user` (`id_plan`),
  KEY `username` (`username`),
  KEY `fk_pkg_user_pkg_user` (`id_user`),
  KEY `fk_pkg_group_user_pkg_user_agent` (`id_group_agent`),
  CONSTRAINT `fk_pkg_group_user_pkg_user` FOREIGN KEY (`id_group`) REFERENCES `pkg_group_user` (`id`),
  CONSTRAINT `fk_pkg_group_user_pkg_user_agent` FOREIGN KEY (`id_group_agent`) REFERENCES `pkg_group_user` (`id`),
  CONSTRAINT `fk_pkg_plan_pkg_user` FOREIGN KEY (`id_plan`) REFERENCES `pkg_plan` (`id`),
  CONSTRAINT `fk_pkg_user_pkg_user` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_user`
--

LOCK TABLES `pkg_user` WRITE;
/*!40000 ALTER TABLE `pkg_user` DISABLE KEYS */;
INSERT INTO `pkg_user` VALUES (1,1,1,NULL,NULL,NULL,'root','9f4ca770b638615ac5c3e0d2da16b77c80c2f2c6',0.0000,1,'0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,'','','','','','','','','','','0','','','0000-00-00 00:00:00',0,0,'en','','',NULL,10,0,534565,'21','0',1,0,0,0,0,'','0000-00-00 00:00:00',0,'',NULL,NULL);
/*!40000 ALTER TABLE `pkg_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_user_rate`
--

DROP TABLE IF EXISTS `pkg_user_rate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_user_rate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_prefix` int(11) NOT NULL,
  `rateinitial` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `initblock` int(11) NOT NULL DEFAULT '0',
  `billingblock` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_pkg_user_pkg_user_rate` (`id_user`),
  KEY `fk_pkg_prefix_pkg_user_rate` (`id_prefix`),
  CONSTRAINT `fk_pkg_prefix_pkg_user_rate` FOREIGN KEY (`id_prefix`) REFERENCES `pkg_prefix` (`id`),
  CONSTRAINT `fk_pkg_user_pkg_user_rate` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_user_rate`
--

LOCK TABLES `pkg_user_rate` WRITE;
/*!40000 ALTER TABLE `pkg_user_rate` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_user_rate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pkg_user_type`
--

DROP TABLE IF EXISTS `pkg_user_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_user_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(18) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_user_type`
--

LOCK TABLES `pkg_user_type` WRITE;
/*!40000 ALTER TABLE `pkg_user_type` DISABLE KEYS */;
INSERT INTO `pkg_user_type` VALUES (1,'t(\'admin\')'),(2,'t(\'agent\')'),(3,'t(\'client\')');
/*!40000 ALTER TABLE `pkg_user_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `pkg_voicemail_users`
--

DROP TABLE IF EXISTS `pkg_voicemail_users`;
/*!50001 DROP VIEW IF EXISTS `pkg_voicemail_users`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `pkg_voicemail_users` AS SELECT 
 1 AS `id`,
 1 AS `customer_id`,
 1 AS `context`,
 1 AS `mailbox`,
 1 AS `password`,
 1 AS `fullname`,
 1 AS `email`,
 1 AS `pager`,
 1 AS `stamp`,
 1 AS `uniqueid`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `pkg_voucher`
--

DROP TABLE IF EXISTS `pkg_voucher`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pkg_voucher` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `id_plan` int(11) DEFAULT NULL,
  `creationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usedate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expirationdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `voucher` int(6) NOT NULL,
  `tag` char(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `credit` float NOT NULL DEFAULT '0',
  `used` int(11) DEFAULT '0',
  `prefix_local` smallint(4) DEFAULT NULL,
  `language` char(5) NOT NULL DEFAULT 'en',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cons_pkg_voucher_voucher` (`voucher`),
  KEY `fk_pkg_user_pkg_voucher` (`id_user`),
  CONSTRAINT `fk_pkg_user_pkg_voucher` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pkg_voucher`
--

LOCK TABLES `pkg_voucher` WRITE;
/*!40000 ALTER TABLE `pkg_voucher` DISABLE KEYS */;
/*!40000 ALTER TABLE `pkg_voucher` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `pkg_voicemail_users`
--

/*!50001 DROP VIEW IF EXISTS `pkg_voicemail_users`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `pkg_voicemail_users` AS select `pkg_sip`.`id` AS `id`,`pkg_sip`.`id_user` AS `customer_id`,'billing' AS `context`,`pkg_sip`.`name` AS `mailbox`,`pkg_user`.`callingcard_pin` AS `password`,`pkg_user`.`firstname` AS `fullname`,`pkg_user`.`email` AS `email`,`pkg_sip`.`md5secret` AS `pager`,`pkg_user`.`creationdate` AS `stamp`,'' AS `uniqueid` from (`pkg_sip` join `pkg_user` on((`pkg_sip`.`id_user` = `pkg_user`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-11-30 20:38:50

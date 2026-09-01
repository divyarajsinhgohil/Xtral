-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: xtral
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
-- Table structure for table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `role` enum('super_admin','sales_team','support_team','inventory_manager','purchase_manager','dispatch_manager','mis_viewer') NOT NULL,
  `assigned_states` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`assigned_states`)),
  `assigned_cities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`assigned_cities`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_users`
--

LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
INSERT INTO `admin_users` VALUES (1,'admin','Admin','Admin@gmail.com',NULL,'super_admin',NULL,NULL,1,'2026-07-04 15:01:44','2026-07-04 09:31:44','$2y$10$vojT1bY0roAQ.4MDsG4Tq.2KeyBAe/xP3xs9ygwrRyoFuATBiNhlm','2025-12-05 12:51:00'),(2,'testsales','Test Sales','testsale@gmail.com','9033555666','sales_team',NULL,'[\"Nashik\",\"Pune\"]',1,'2026-06-08 18:37:00','2026-06-08 13:07:00','$2y$10$Tc/9MqV0FjonzgySTu4Rd.WX4OGs9T1F0/iOl8nyflguq8xv8XL8K','2026-04-04 12:01:56'),(3,'dispatch','Test Dispatch Manager','dispatch@test.com',NULL,'dispatch_manager',NULL,NULL,1,'2026-06-08 18:34:46','2026-06-08 13:04:46','$2y$10$RfFpxLOXAbRtv9CvCs8yMei6ZtiYghLn0wHDyl/JYrHBktTl.wmR6','2026-06-08 07:52:51'),(4,'mis','Test MIS Viewer','mis@test.com',NULL,'mis_viewer',NULL,NULL,1,'2026-06-08 13:40:31','2026-06-08 08:10:31','$2y$10$oCYs3hy9bMVZ/a9QJbPVy.iftA3c99jSS8vWvaIylWJUA8tAha9ya','2026-06-08 07:52:51'),(5,'support','Test Support Team','support@test.com',NULL,'support_team',NULL,NULL,1,'2026-06-08 17:22:04','2026-06-08 11:52:04','$2y$10$5KlfGTfmDSkrUVy9kG4N/uEOwWUSPSpzZfOITnXo1G4d.AdR7uSwC','2026-06-08 08:30:40');
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_usage`
--

DROP TABLE IF EXISTS `asset_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asset_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `campaign_id` int(11) DEFAULT NULL COMMENT 'If used in campaign',
  `used_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_asset_id` (`asset_id`),
  KEY `idx_campaign_id` (`campaign_id`),
  CONSTRAINT `asset_usage_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tracks where assets are used';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_usage`
--

LOCK TABLES `asset_usage` WRITE;
/*!40000 ALTER TABLE `asset_usage` DISABLE KEYS */;
INSERT INTO `asset_usage` VALUES (1,2,NULL,'2025-12-11 13:37:02'),(3,2,NULL,'2025-12-11 13:43:12'),(8,5,NULL,'2025-12-11 14:18:24'),(9,2,NULL,'2025-12-11 14:21:16'),(10,4,NULL,'2025-12-11 14:22:07'),(11,5,NULL,'2025-12-11 14:25:27'),(12,5,NULL,'2025-12-11 14:36:08'),(13,4,NULL,'2025-12-11 14:36:34'),(14,2,NULL,'2025-12-11 14:38:20'),(15,2,NULL,'2025-12-12 05:51:51'),(16,2,NULL,'2025-12-12 06:05:11'),(17,2,NULL,'2025-12-12 06:15:31'),(18,2,NULL,'2025-12-12 06:16:11'),(19,2,NULL,'2025-12-12 06:31:12'),(20,2,NULL,'2025-12-12 06:32:39'),(21,2,NULL,'2025-12-12 06:37:31'),(22,2,NULL,'2025-12-12 06:38:52'),(24,6,NULL,'2025-12-12 06:42:44'),(25,2,NULL,'2025-12-12 13:12:18'),(26,4,NULL,'2026-01-17 13:51:49'),(27,2,NULL,'2026-01-24 05:38:00'),(28,6,NULL,'2026-01-24 05:39:22'),(29,5,NULL,'2026-01-24 05:43:54'),(30,5,NULL,'2026-02-03 07:15:48'),(31,6,NULL,'2026-02-27 06:04:34'),(32,4,NULL,'2026-02-27 06:13:07'),(33,6,NULL,'2026-03-30 14:39:36');
/*!40000 ALTER TABLE `asset_usage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assets`
--

DROP TABLE IF EXISTS `assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'User-friendly name for the asset',
  `type` enum('image','video','document') NOT NULL COMMENT 'Media type',
  `file_path` varchar(500) NOT NULL COMMENT 'Relative path in uploads/assets/',
  `mime_type` varchar(100) NOT NULL COMMENT 'MIME type of the file',
  `file_size` int(11) NOT NULL COMMENT 'File size in bytes',
  `thumbnail_path` varchar(500) DEFAULT NULL COMMENT 'Thumbnail for videos/documents',
  `description` text DEFAULT NULL COMMENT 'Optional description',
  `created_by` int(11) NOT NULL COMMENT 'Admin user ID who uploaded',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assets`
--

LOCK TABLES `assets` WRITE;
/*!40000 ALTER TABLE `assets` DISABLE KEYS */;
INSERT INTO `assets` VALUES (2,'BellCatlogueOLD','document','asset_1765263920_6937ca308095f.pdf','application/pdf',53738435,NULL,'BellCatlogueOLD',1,'2025-12-09 07:05:20','2025-12-09 07:05:20'),(4,'Sample Video','video','asset_1765263985_6937ca71a3aae.mp4','video/mp4',3227819,NULL,'sample video',1,'2025-12-09 07:06:25','2025-12-09 07:06:25'),(5,'test image','image','asset_1765462676_693ad29452dc2.jpg','image/jpeg',183499,NULL,'',1,'2025-12-11 14:17:56','2025-12-11 14:17:56'),(6,'Sample Product','image','asset_1765521732_693bb94497e07.jpeg','image/jpeg',52348,NULL,'',1,'2025-12-12 06:42:12','2025-12-12 06:42:12');
/*!40000 ALTER TABLE `assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audience`
--

DROP TABLE IF EXISTS `audience`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audience` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `mobile` varchar(20) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `fcm_token` varchar(500) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL COMMENT 'Email address',
  `address` text DEFAULT NULL COMMENT 'Full address',
  `state` varchar(100) DEFAULT NULL COMMENT 'State - for filtering and reporting',
  `district` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL COMMENT 'City - for filtering and reporting',
  `pincode` varchar(10) DEFAULT NULL COMMENT 'Pincode',
  `assigned_cities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Cities this TSM/Salesman can access. NULL = their own city only.' CHECK (json_valid(`assigned_cities`)),
  `birthdate` date DEFAULT NULL,
  `notes` text DEFAULT NULL COMMENT 'Notes/remarks about the contact',
  `password` varchar(255) DEFAULT NULL COMMENT 'Password for Plumber/Dealer/Distributor/Salesman',
  `firm_name` varchar(255) DEFAULT NULL COMMENT 'Firm/Company name',
  `firm_type` varchar(50) DEFAULT NULL COMMENT 'Type of firm (Proprietorship, Partnership, etc.)',
  `gst_number` varchar(20) DEFAULT NULL COMMENT 'GST Number',
  `pan` varchar(10) DEFAULT NULL COMMENT 'PAN Number',
  `msme_udyam` varchar(50) DEFAULT NULL COMMENT 'MSME/Udyam Registration Number (Optional)',
  `bank_name` varchar(100) DEFAULT NULL COMMENT 'Bank name',
  `account_holder` varchar(255) DEFAULT NULL COMMENT 'Account holder name',
  `account_number` varchar(50) DEFAULT NULL COMMENT 'Bank account number',
  `ifsc_code` varchar(20) DEFAULT NULL COMMENT 'IFSC Code',
  `photo` varchar(255) DEFAULT NULL,
  `last_msg_received_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile` (`mobile`),
  KEY `idx_mobile` (`mobile`),
  KEY `idx_group_id` (`group_id`),
  KEY `idx_last_msg` (`last_msg_received_at`),
  KEY `idx_birthdate` (`birthdate`),
  KEY `idx_email` (`email`),
  KEY `idx_state` (`state`),
  KEY `idx_city` (`city`),
  KEY `idx_pincode` (`pincode`),
  KEY `idx_firm_name` (`firm_name`),
  KEY `idx_gst_number` (`gst_number`),
  KEY `idx_pan` (`pan`),
  KEY `idx_location` (`state`,`city`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `audience_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=177 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audience`
--

LOCK TABLES `audience` WRITE;
/*!40000 ALTER TABLE `audience` DISABLE KEYS */;
INSERT INTO `audience` VALUES (60,1,'anand','gohel','918140824102','$2y$10$4/MpqWqVudeotWtdwCnFYO7xjl3MPpnQjOLS9oJ7FZtOwOsDtPvyi',1,NULL,NULL,'suresh.patil@gmail.com','456 Park Road, Vile Parle','Gujarat','Rajkot','Rajkot','360005',NULL,'1990-08-20','Experienced plumber','test1234','Patil Plumbing Services','Proprietary Firm','27GGGGG6666G7Z1','GHIJK7890L','UDYAM-MH-27-6789012','HDFC Bank','Suresh Patil','7.89012E+13','HDFC0007890','audience_699f0bde87db9_1772030942.jpg','2026-06-25 17:26:26','2026-01-23 10:58:17','2026-06-25 11:56:26'),(99,7,'Vishal','Mehta','919998999801','$2y$10$p5OeodY0mQtCjXhVFz1Teuunb0hfFYRB8x5fhY8tZIByxMstypmYW',1,NULL,NULL,'vishalkumar@gmail.com','150 Feet Ring Road','Gujarat','Rajkot','Rajkot','360005','[\"Rajkot\",\"Jamnagar\"]','1990-05-12','Marketing Person','test1234',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'audience_69e1db62eaddc_1776409442.jpg',NULL,'2026-04-06 05:57:09','2026-06-08 12:00:21'),(107,7,'Tsm2','Shivam','919033611222','$2y$10$GXwTPtOZFlip7fqgGUo1v.fruaBWOgf35k/aadEWUHLvYQ33F.zVW',1,NULL,NULL,'Shivam@gmail.com','Morbi','Gujarat','Morbi','Morbi','360007','[\"morbi\"]','1991-06-12','tsm notes','112233',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'audience_69e1dbc7dabf3_1776409543.jpg',NULL,'2026-04-17 07:05:43','2026-05-17 11:55:05'),(112,7,'Tsm3','Jay','919988777788','$2y$10$XPRR0Umm82pZjsxNxaPkDOMzEQ28Ak4sjtZrUH4/uH10aogEEdDki',1,NULL,NULL,'Jay@GMAIL.COM','Jamnagar','Gujarat','Jamnagar','Jamnagar','360006','[\"Rajkot\",\"Jamnagar\"]','1991-06-12',NULL,'112233',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'audience_69e8ce86bc251_1776864902.jpg',NULL,'2026-04-22 13:35:02','2026-05-17 11:56:12'),(115,12,'Arjun','Sharma','919033611401','$2y$10$p5OeodY0mQtCjXhVFz1Teuunb0hfFYRB8x5fhY8tZIByxMstypmYW',1,NULL,NULL,'arjun@gmail.com','1108/1109 RK Prime, Nana Mava Main Road, Rajkot 360005','Gujarat','Jamnagar','Jamnagar','360006',NULL,'1991-06-12',NULL,'test1234','Arjun Architects','Company','112233445566778','BJLPA7193K',NULL,'HDFC Bank','Arjun Architech','20051186144','HDFCINBB',NULL,NULL,'2026-05-17 11:54:02','2026-06-08 12:00:21'),(116,11,'Vishal','Patel','919033611444','$2y$10$p5OeodY0mQtCjXhVFz1Teuunb0hfFYRB8x5fhY8tZIByxMstypmYW',1,NULL,NULL,'vishal@gmail.com','1108/1109 RK Prime, Nana Mava Main Road, Rajkot 360005','Maharashtra','Nashik','Nashik','360044',NULL,'1991-05-12',NULL,'test1234','Patel Constructions','Partnership Firm','24AAACC1206D1ZM','GHIJK7890L',NULL,'HDFC Bank','Vishal Architech','9911456684','HDCINBB',NULL,NULL,'2026-05-20 07:25:59','2026-06-08 12:00:21'),(117,12,'Nitin','Joshi','1122335544','$2y$10$p5OeodY0mQtCjXhVFz1Teuunb0hfFYRB8x5fhY8tZIByxMstypmYW',1,NULL,NULL,'aaa@gmail.com','150 Ft Ring Road','Gujarat',NULL,'Jamnagar','365050',NULL,'2026-06-04',NULL,'test1234','Joshi Designs','Pvt Ltd',NULL,'BJLPA7193K',NULL,'Hdfc bank','Aaa bbb','20051186144','HDFCINBB',NULL,NULL,'2026-06-05 14:04:53','2026-06-08 12:00:21'),(118,2,'Hardik','Shah','1122335555','$2y$10$p5OeodY0mQtCjXhVFz1Teuunb0hfFYRB8x5fhY8tZIByxMstypmYW',1,NULL,NULL,'aaa@gmail.com','150 Ft Ring Road','Gujarat',NULL,'Jamnagar','365050',NULL,'2026-06-04',NULL,'test1234','Shah Bath Mart','Pvt Ltd',NULL,'BJLPA7193K',NULL,'Hdfc bank','Aaa bbb','20051186144','HDFCINBB',NULL,NULL,'2026-06-05 14:26:53','2026-06-08 12:00:21'),(119,3,'Rajesh','Sharma','919900000001','$2y$10$p5OeodY0mQtCjXhVFz1Teuunb0hfFYRB8x5fhY8tZIByxMstypmYW',1,NULL,NULL,'distributor@test.com',NULL,'Gujarat',NULL,'Rajkot',NULL,NULL,NULL,NULL,'test1234','Sharma Trading Pvt Ltd',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-08 07:52:51','2026-06-08 12:00:21'),(120,9,'Mahesh','Kumar','919900000002','$2y$10$p5OeodY0mQtCjXhVFz1Teuunb0hfFYRB8x5fhY8tZIByxMstypmYW',1,NULL,NULL,'retailer@test.com',NULL,'Gujarat',NULL,'Rajkot',NULL,NULL,NULL,NULL,'test1234','Kumar Bath Studio',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-08 07:52:52','2026-06-08 12:00:21'),(121,4,'dasdf','Spartan','919033611456',NULL,1,NULL,NULL,'appshceek@gmail.com','1108/1109 RK Prime, Nana Mava Main Road, Rajkot 360005','Gujarat','Rajkot','Rajkot','360005',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-08 11:54:14','2026-06-16 14:18:50'),(122,4,'Animesh','sindhav','919712875874',NULL,1,NULL,NULL,'ani@gmail.com','1108/1109 RK Prime, Nana Mava Main Road, Rajkot 360005','Gujarat','Rajkot','Rajkot','360005',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-09 07:14:50','2026-06-25 11:25:53'),(123,6,'Unknown','','7996101311',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-09 17:22:01','2026-06-09 11:52:01','2026-06-09 11:52:01'),(124,6,'Unknown','','9654297000',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-16 19:18:06','2026-06-16 13:48:06','2026-06-16 13:48:06'),(125,2,'Test','Spartan','919000000006','$2y$10$ysqN7gQLuG4QI7qcCYhCf.VTJzzL5Rml/i0UQEtvKb0wn10.rTUEe',1,NULL,NULL,'appshceek@gmail.com','1108/1109 RK Prime, Nana Mava Main Road, Rajkot 360005','Gujarat','Rajkot','Rajkot','360005',NULL,NULL,NULL,'test1234','Old Lead Stale','Proprietorship',NULL,NULL,NULL,'HDFC Bank','Haarshil patel','20051186144',NULL,NULL,NULL,'2026-06-23 11:00:40','2026-06-23 11:00:40'),(176,4,'Animesh','sindhav','918758707750',NULL,1,NULL,NULL,'ani@gmail.com','1108/1109 RK Prime, Nana Mava Main Road, Rajkot 360005','Gujarat','Rajkot','Rajkot','360005',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-25 11:27:50','2026-06-25 11:27:50');
/*!40000 ALTER TABLE `audience` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `birthday_settings`
--

DROP TABLE IF EXISTS `birthday_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `birthday_settings` (
  `id` int(10) unsigned NOT NULL DEFAULT 1,
  `template_id` int(10) unsigned NOT NULL,
  `template_params` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`template_params`)),
  `template_header_param` varchar(50) DEFAULT NULL,
  `frame_id` int(10) unsigned DEFAULT NULL,
  `custom_media_path` varchar(255) DEFAULT NULL,
  `custom_media_filename` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `birthday_settings`
--

LOCK TABLES `birthday_settings` WRITE;
/*!40000 ALTER TABLE `birthday_settings` DISABLE KEYS */;
INSERT INTO `birthday_settings` VALUES (1,51,'[\"full_name\",\"company_name\",\"company_name\"]','frame_with_photo',2,NULL,NULL,'2025-12-12 07:51:03');
/*!40000 ALTER TABLE `birthday_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaign_delivery`
--

DROP TABLE IF EXISTS `campaign_delivery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campaign_delivery` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) unsigned NOT NULL,
  `contact_id` int(11) unsigned NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `status` enum('pending','sent','failed','skipped') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `meta_message_id` varchar(100) DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_campaign_id` (`campaign_id`),
  KEY `idx_contact_id` (`contact_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `campaign_delivery_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=153 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaign_delivery`
--

LOCK TABLES `campaign_delivery` WRITE;
/*!40000 ALTER TABLE `campaign_delivery` DISABLE KEYS */;
INSERT INTO `campaign_delivery` VALUES (141,110,93,'919099910204','sent',NULL,'wamid.HBgMOTE5MDk5OTEwMjA0FQIAERgSNjk4RDVDNkQ1OTk2RDMwNDVGAA==','2026-03-30 11:09:45','2026-03-30 14:39:45'),(142,110,60,'918460148508','sent',NULL,'wamid.HBgMOTE4NDYwMTQ4NTA4FQIAERgSNUI3REM0ODIyRTY1MTA3MDgxAA==','2026-03-30 11:09:45','2026-03-30 14:39:45'),(143,110,89,'919033611459','sent',NULL,'wamid.HBgMOTE5MDMzNjExNDU5FQIAERgSNEI1MEQzNjI0OUNDQjg5OEJBAA==','2026-03-30 11:09:46','2026-03-30 14:39:46'),(144,110,97,'919033611453','sent',NULL,'wamid.HBgMOTE5MDMzNjExNDUzFQIAERgSMDZCQUE4Nzk3RjZBNzBDNkUwAA==','2026-03-30 11:09:47','2026-03-30 14:39:47'),(145,110,96,'919033611256','sent',NULL,'wamid.HBgMOTE5MDMzNjExMjU2FQIAERgSNEExNzkzNDFCNENDMjdCRjFBAA==','2026-03-30 11:09:48','2026-03-30 14:39:48'),(146,110,90,'919911223344','sent',NULL,'wamid.HBgMOTE5OTExMjIzMzQ0FQIAERgSM0M1MDI3NTE1MDQzNTAyNEJFAA==','2026-03-30 11:09:49','2026-03-30 14:39:49'),(147,110,92,'919999999999','sent',NULL,'wamid.HBgMOTE5OTk5OTk5OTk5FQIAERgSQkIzMjM2RURGQUYwN0Y3NjI0AA==','2026-03-30 11:09:49','2026-03-30 14:39:49'),(148,110,86,'919033611456','sent',NULL,'wamid.HBgMOTE5MDMzNjExNDU2FQIAERgSMjZEMjExNkVEOEM5ODQxOUE1AA==','2026-03-30 11:09:50','2026-03-30 14:39:50'),(149,110,87,'919033611457','sent',NULL,'wamid.HBgMOTE5MDMzNjExNDU3FQIAERgSM0Y0Nzg2MEZEQkZCQjA5MDU0AA==','2026-03-30 11:09:50','2026-03-30 14:39:50'),(150,110,88,'919033611458','sent',NULL,'wamid.HBgMOTE5MDMzNjExNDU4FQIAERgUQ0FEQ0I1OTE0MTYxNDk5NTY5OUIA','2026-03-30 11:09:51','2026-03-30 14:39:51'),(151,110,94,'919033611444','sent',NULL,'wamid.HBgMOTE5MDMzNjExNDQ0FQIAERgSOEYzQkM3MDkyOTIwNTNBRDA1AA==','2026-03-30 11:09:51','2026-03-30 14:39:51'),(152,110,95,'919033611666','sent',NULL,'wamid.HBgMOTE5MDMzNjExNjY2FQIAERgSQUI5QzU3NDIwN0NGMzg5RDY2AA==','2026-03-30 11:09:52','2026-03-30 14:39:52');
/*!40000 ALTER TABLE `campaign_delivery` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaigns`
--

DROP TABLE IF EXISTS `campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campaigns` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` enum('text','media','template','frame') NOT NULL,
  `group_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`group_ids`)),
  `recipients` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`recipients`)),
  `message_body` text DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`content`)),
  `template_id` int(11) unsigned DEFAULT NULL,
  `media_path` varchar(255) DEFAULT NULL,
  `frame_id` int(11) unsigned DEFAULT NULL,
  `total_contacts` int(11) DEFAULT 0,
  `sent_count` int(11) DEFAULT 0,
  `failed_count` int(11) DEFAULT 0,
  `stats` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`stats`)),
  `status` enum('draft','pending','processing','completed','failed','cancelled') DEFAULT 'draft',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(50) DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_scheduled` (`scheduled_at`),
  KEY `idx_type` (`type`),
  KEY `idx_status_scheduled` (`status`,`scheduled_at`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaigns`
--

LOCK TABLES `campaigns` WRITE;
/*!40000 ALTER TABLE `campaigns` DISABLE KEYS */;
INSERT INTO `campaigns` VALUES (110,'birthdway wish rajkot','template','[]','[93,60,89,97,96,90,92,86,87,88,94,95]',NULL,'{\"template_params\":[\"full_name\"],\"template_header_param\":\"custom_media\",\"frame_id\":\"\",\"custom_media_path\":\"assets\\/asset_1765521732_693bb94497e07.jpeg\",\"custom_media_filename\":\"Sample Product.jpeg\"}',139,NULL,NULL,0,12,0,'{\"sent\":12,\"failed\":0,\"skipped\":0}','completed',NULL,'2026-03-30 14:39:36','1','2026-03-30 14:39:52');
/*!40000 ALTER TABLE `campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalogue_banners`
--

DROP TABLE IF EXISTS `catalogue_banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalogue_banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `link_url` varchar(500) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalogue_banners`
--

LOCK TABLES `catalogue_banners` WRITE;
/*!40000 ALTER TABLE `catalogue_banners` DISABLE KEYS */;
INSERT INTO `catalogue_banners` VALUES (1,'Banner 1','699862e218719_1771594466.webp',NULL,1,0,'2026-02-20 13:34:26','2026-07-03 04:56:09'),(2,'Banner 2','699862eb17362_1771594475.webp',NULL,2,0,'2026-02-20 13:34:35','2026-07-03 04:59:40'),(3,'Banner 3','699862f3d0083_1771594483.webp',NULL,3,0,'2026-02-20 13:34:43','2026-07-03 04:59:46'),(4,'Banner 4','699862fc34a0a_1771594492.webp',NULL,4,0,'2026-02-20 13:34:52','2026-07-03 04:59:52'),(5,'Banner 5','69986303c0027_1771594499.webp',NULL,5,0,'2026-02-20 13:34:59','2026-07-03 04:59:57'),(8,'banner 6','6a4746a0cec09_1783056032.png',NULL,6,1,'2026-07-03 05:00:12','2026-07-03 05:20:32'),(9,'banner7','6a475d414c75a_1783061825.png',NULL,7,1,'2026-07-03 05:00:27','2026-07-03 06:57:05'),(10,'banner 8','6a475d4c1a7e7_1783061836.png',NULL,8,1,'2026-07-03 05:00:40','2026-07-03 06:57:16'),(11,'banner9','6a47644015985_1783063616.png',NULL,9,1,'2026-07-03 07:25:03','2026-07-03 07:26:56');
/*!40000 ALTER TABLE `catalogue_banners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalogue_categories`
--

DROP TABLE IF EXISTS `catalogue_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalogue_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `has_dual_price` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalogue_categories`
--

LOCK TABLES `catalogue_categories` WRITE;
/*!40000 ALTER TABLE `catalogue_categories` DISABLE KEYS */;
INSERT INTO `catalogue_categories` VALUES (1,'Sanitarywares','6a48b0f63765a_1783148790.png','Sanitarywares',1,1,0,'2026-02-19 10:13:57','2026-07-04 07:06:30'),(2,'Bath Fittings','6a48acf44d108_1783147764.png','Bath Fittings',2,1,0,'2026-02-19 10:16:21','2026-07-04 06:49:24'),(3,'Kitchen Sinks','6a48b114d0247_1783148820.png','Kitchen Sinks',3,1,0,'2026-02-19 10:16:45','2026-07-04 07:07:00'),(4,'Wellness','6a48ad01e8739_1783147777.png','Wellness',4,1,1,'2026-02-19 10:17:01','2026-07-04 06:49:37'),(5,'PTMT Faucets','6a48ad0bad1c2_1783147787.png','PTMT Faucets',5,1,0,'2026-02-19 10:17:34','2026-07-04 06:49:47');
/*!40000 ALTER TABLE `catalogue_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalogue_colors`
--

DROP TABLE IF EXISTS `catalogue_colors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalogue_colors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` enum('solid','texture') NOT NULL DEFAULT 'solid',
  `hex_code` varchar(7) DEFAULT NULL,
  `texture_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalogue_colors`
--

LOCK TABLES `catalogue_colors` WRITE;
/*!40000 ALTER TABLE `catalogue_colors` DISABLE KEYS */;
INSERT INTO `catalogue_colors` VALUES (1,'Crystal White','solid','#ffffff',NULL,1,'2026-02-18 07:38:46'),(2,'Blue','solid','#cbebfa',NULL,1,'2026-02-18 07:39:01'),(3,'Ming Green','solid','#cfe6ca',NULL,1,'2026-02-18 07:39:14'),(4,'Euro Ivory','solid','#fdf7dd',NULL,1,'2026-02-18 07:39:27'),(5,'Pink','solid','#f5c8c4',NULL,1,'2026-02-18 07:39:38'),(6,'Royal Grey','solid','#bcbdc0',NULL,1,'2026-02-18 07:39:55'),(7,'Royal Black','solid','#231f20',NULL,1,'2026-02-18 07:40:16'),(8,'Peach Magenta','solid','#e97677',NULL,1,'2026-02-18 07:40:29'),(9,'Gun Metal Grey','solid','#57585a',NULL,1,'2026-02-18 07:40:44'),(10,'Sandalwood','solid','#f7c48a',NULL,1,'2026-02-18 07:40:57'),(11,'Sorento Blue','solid','#5e98d0',NULL,1,'2026-02-18 07:41:08'),(12,'Burgundy','solid','#6a191f',NULL,1,'2026-02-18 07:41:14'),(13,'METALLIC COLORS','texture',NULL,'6995a2796949a_1771414137.png',1,'2026-02-18 11:28:57'),(14,'MODERN COLORS','texture',NULL,'6995a28c1941b_1771414156.png',1,'2026-02-18 11:29:16'),(15,'Ivory','solid','#fbe4ca',NULL,1,'2026-03-07 08:12:27'),(16,'White','solid','#ffffff',NULL,1,'2026-03-07 08:15:30');
/*!40000 ALTER TABLE `catalogue_colors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalogue_features`
--

DROP TABLE IF EXISTS `catalogue_features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalogue_features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `icon_url` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalogue_features`
--

LOCK TABLES `catalogue_features` WRITE;
/*!40000 ALTER TABLE `catalogue_features` DISABLE KEYS */;
/*!40000 ALTER TABLE `catalogue_features` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalogue_product_features`
--

DROP TABLE IF EXISTS `catalogue_product_features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalogue_product_features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `feature_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `feature_id` (`feature_id`),
  CONSTRAINT `catalogue_product_features_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `catalogue_products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `catalogue_product_features_ibfk_2` FOREIGN KEY (`feature_id`) REFERENCES `catalogue_features` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalogue_product_features`
--

LOCK TABLES `catalogue_product_features` WRITE;
/*!40000 ALTER TABLE `catalogue_product_features` DISABLE KEYS */;
/*!40000 ALTER TABLE `catalogue_product_features` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalogue_product_images`
--

DROP TABLE IF EXISTS `catalogue_product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalogue_product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `catalogue_product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `catalogue_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=331 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalogue_product_images`
--

LOCK TABLES `catalogue_product_images` WRITE;
/*!40000 ALTER TABLE `catalogue_product_images` DISABLE KEYS */;
INSERT INTO `catalogue_product_images` VALUES (309,156,'prod_1773412834_69b421e227b5f.jpg',1,1,'2026-03-13 14:40:34'),(310,157,'prod_1773412834_69b421e2293fc.jpg',1,1,'2026-03-13 14:40:34'),(311,158,'prod_1773412834_69b421e22ab79.jpg',1,1,'2026-03-13 14:40:34'),(312,159,'prod_1773412834_69b421e22c330.jpg',1,1,'2026-03-13 14:40:34'),(313,160,'prod_1773412834_69b421e22dd85.jpg',1,1,'2026-03-13 14:40:34'),(314,161,'prod_1773412834_69b421e22f489.jpg',1,1,'2026-03-13 14:40:34'),(315,162,'prod_1773412834_69b421e230a57.jpg',1,1,'2026-03-13 14:40:34'),(316,163,'prod_1773412834_69b421e23233a.jpg',1,1,'2026-03-13 14:40:34'),(317,164,'prod_1773412834_69b421e233a9f.jpg',1,1,'2026-03-13 14:40:34'),(318,165,'prod_1773412834_69b421e234fab.jpg',1,1,'2026-03-13 14:40:34'),(322,169,'prod_1774071555_69be2f034aa0c.png',1,1,'2026-03-21 05:39:15'),(326,93,'6996e6c9b582d_1771497161.jpg',1,1,'2026-04-22 13:30:30'),(327,93,'prod_1771913763_699d4223dcc5e.png',0,2,'2026-04-22 13:30:30'),(328,94,'6996e6f701661_1771497207.jpg',1,1,'2026-04-22 13:30:30'),(329,173,'prod_1776864630_69e8cd768485a.jpg',1,1,'2026-04-22 13:30:30'),(330,174,'69f2380032e16_1777481728.jpg',1,0,'2026-04-29 16:55:28');
/*!40000 ALTER TABLE `catalogue_product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalogue_product_variants`
--

DROP TABLE IF EXISTS `catalogue_product_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalogue_product_variants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `color_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `hsn_code` varchar(15) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_zone2` decimal(10,2) DEFAULT NULL,
  `attribute_value` varchar(100) NOT NULL COMMENT 'Size or Color Name',
  `image_url` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `fk_product_variant_product_id` (`product_id`),
  CONSTRAINT `fk_product_variant_product_id` FOREIGN KEY (`product_id`) REFERENCES `catalogue_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalogue_product_variants`
--

LOCK TABLES `catalogue_product_variants` WRITE;
/*!40000 ALTER TABLE `catalogue_product_variants` DISABLE KEYS */;
INSERT INTO `catalogue_product_variants` VALUES (31,169,2,'','D001',NULL,2135.00,NULL,'Matt Red','prod_1774071555_69be2f034b1a4.png',0,1),(32,169,11,'','D001',NULL,2135.00,NULL,'Matt Dark Blue','prod_1774071555_69be2f034b419.png',1,1),(33,169,11,'','D001',NULL,2135.00,NULL,'Matt Black','prod_1774071555_69be2f034bfe7.png',2,1),(34,169,11,'','D001',NULL,2135.00,NULL,'Matt Blue','prod_1774071555_69be2f034c55b.png',3,1),(35,169,11,'','D001',NULL,2135.00,NULL,'Matt Dark Red','prod_1774071555_69be2f034ca22.png',4,1),(36,169,11,'','D001',NULL,2135.00,NULL,'Matt Brown','prod_1774071555_69be2f034d0cd.png',5,1),(37,169,11,'','D001',NULL,2135.00,NULL,'Matt Orange','prod_1774071555_69be2f034d699.png',6,1),(38,169,11,'','D001',NULL,2135.00,NULL,'Glossy Red','prod_1774071555_69be2f034dad6.png',7,1),(39,169,11,'','D001',NULL,2135.00,NULL,'Glossy Blue','prod_1774071555_69be2f034df23.png',8,1),(40,174,NULL,'varient1','po001',NULL,25000.00,NULL,'25mm','69f238003311a_1777481728.jpg',0,1),(41,174,NULL,'varient2','po002',NULL,25500.00,NULL,'35mm','69f2380033360_1777481728.jpg',1,1);
/*!40000 ALTER TABLE `catalogue_product_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalogue_products`
--

DROP TABLE IF EXISTS `catalogue_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalogue_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `series_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `hsn_code` varchar(15) DEFAULT NULL,
  `price` int(11) DEFAULT 0,
  `price_zone2` int(11) DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL,
  `specifications` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `variant_type` enum('none','size','color') DEFAULT 'none',
  `colour_label` enum('colour','finish') NOT NULL DEFAULT 'colour',
  `display_order` int(11) DEFAULT 0,
  `is_new_arrival` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_cat_prod_series_cascade` (`series_id`),
  CONSTRAINT `catalogue_products_ibfk_1` FOREIGN KEY (`series_id`) REFERENCES `catalogue_series` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cat_prod_series_cascade` FOREIGN KEY (`series_id`) REFERENCES `catalogue_series` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=175 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalogue_products`
--

LOCK TABLES `catalogue_products` WRITE;
/*!40000 ALTER TABLE `catalogue_products` DISABLE KEYS */;
INSERT INTO `catalogue_products` VALUES (93,23,'Thermostatic Shower Mixer With Body & Trim','TDS 60','69101000',100,NULL,'1850 x 1230 x 620 mm','<p>Thermostatic Shower Mixer With Body &amp; Trim&nbsp;</p>',1,'none','colour',1,1,'2026-02-19 10:32:41','2026-06-23 12:14:20'),(94,23,'Thermostatic Shower Mixer With Body & Trim','TDR 50','69101000',28555,NULL,'','<p>Thermostatic Shower Mixer With Body &amp; Trim</p>',1,'none','colour',2,1,'2026-02-19 10:33:26','2026-06-23 12:14:46'),(156,37,'Freestanding Bathtubs','EW6312',NULL,71150,80555,'1600 x 700 x 670 mm','Material : Acrylic<br>Features : All White Color and Silver Legs<br>Truly European Design <br>100% Quality Acrylic with fiberglass reinforced <br>Double layer, Seamless Design <br>Pop-up drainer<br>Chrome finished legs',1,'none','colour',1,0,'2026-03-13 14:40:34','2026-03-13 14:40:34'),(157,37,'Freestanding Bathtubs','6515',NULL,73595,83555,'1600 x 750 x 600 mm','Material : Acrylic <br>Features : Truly European Design<br>100% Quality Acrylic material<br>Seamless Design, high glossy both side<br>Stainless Steel frame undertub<br>Pop-up drainer with overflow',1,'none','colour',2,0,'2026-03-13 14:40:34','2026-03-13 14:40:34'),(158,37,'Freestanding Bathtubs','6809',NULL,73595,83555,'1600 x 750 x 580 mm','Material : Acrylic<br>Features :Truly European Design<br>100% Quality Acrylic material<br>Seamless Design, high glossy both side<br>Stainless Steel frame undertub<br>Pop-up drainer with overflow',1,'none','colour',3,0,'2026-03-13 14:40:34','2026-03-13 14:40:34'),(159,37,'Freestanding Bathtubs','6509',NULL,83405,94555,'1600 x 800 x 640 mm','Material : Acrylic <br>Features :<br>Truly European Design<br>100% Quality Acrylic material<br>Seamless Design, high glossy both side<br>Stainless Steel frame undertub<br>Pop-up drainer with overflow',1,'none','colour',4,0,'2026-03-13 14:40:34','2026-03-13 14:40:34'),(160,37,'Freestanding Bathtubs','6813B',NULL,83405,94555,'1600 x 800 x 600 mm','Material : Acrylic <br>Features :<br>Truly European Design<br>100% Quality Acrylic material<br>Seamless Design, high glossy both side<br>Stainless Steel frame undertub<br>Pop-up drainer with overflow',1,'none','colour',5,0,'2026-03-13 14:40:34','2026-03-13 14:40:34'),(161,37,'Freestanding Bathtubs','6829',NULL,76050,85955,'1700 x 700 x 570 mm','Material : Acrylic <br>Features :<br>Truly European Design<br>100% Quality Acrylic material<br>Seamless Design, high glossy both side<br>Stainless Steel frame undertub<br>Pop-up drainer with overflow',1,'none','colour',6,0,'2026-03-13 14:40:34','2026-03-13 14:40:34'),(162,39,'Freestanding Bathtub Facucets','DF-02019',NULL,32355,32355,'','',1,'none','colour',1,0,'2026-03-13 14:40:34','2026-03-13 14:40:34'),(163,39,'Freestanding Bathtub Facucets','DF-02017-2',NULL,32050,32050,'','',1,'none','colour',2,0,'2026-03-13 14:40:34','2026-03-13 14:40:34'),(164,39,'Freestanding Bathtub Facucets','DF - 02011',NULL,35000,37850,'','',1,'none','colour',3,0,'2026-03-13 14:40:34','2026-06-08 13:18:30'),(165,41,'Drop In Bathtub Kit','505510','69101000',2840,2840,'','',1,'none','colour',1,0,'2026-03-13 14:40:34','2026-05-20 07:51:55'),(169,42,'Ceramic Pop-up Waste Coupling',NULL,NULL,0,NULL,'','',1,'color','colour',1,1,'2026-03-21 05:39:15','2026-03-30 15:17:50'),(173,23,'new product name','TDR511','69101000',10000,NULL,'1850 x 1230 x 620 mm','<p>Thermostatic Shower Mixer With Body &amp; Trim&nbsp;</p>',1,'none','colour',3,0,'2026-04-22 13:30:30','2026-05-20 07:51:40'),(174,23,'Sample product',NULL,'69101000',0,NULL,NULL,'<p><span style=\"color: rgb(73, 80, 87);\">Technical SpecificationsTechnical SpecificationsTechnical Specifications</span></p>',1,'size','colour',7,1,'2026-04-29 16:55:28','2026-05-20 07:51:48');
/*!40000 ALTER TABLE `catalogue_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalogue_series`
--

DROP TABLE IF EXISTS `catalogue_series`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalogue_series` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `sub_category_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sub_category_id` (`sub_category_id`),
  KEY `fk_cat_series_cat_cascade` (`category_id`),
  CONSTRAINT `catalogue_series_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `catalogue_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `catalogue_series_ibfk_2` FOREIGN KEY (`sub_category_id`) REFERENCES `catalogue_sub_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_cat_series_cat_cascade` FOREIGN KEY (`category_id`) REFERENCES `catalogue_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalogue_series`
--

LOCK TABLES `catalogue_series` WRITE;
/*!40000 ALTER TABLE `catalogue_series` DISABLE KEYS */;
INSERT INTO `catalogue_series` VALUES (23,2,17,'3 Way Thermostatic Shower Mixer','6996e440093c1_1771496512.jpg','3 Way Thermostatic Shower Mixer.',1,1,'2026-02-19 10:21:52','2026-03-06 13:17:43'),(24,2,17,'HIgh Flow Body Jet','6996e45d77678_1771496541.jpg','HIgh Flow Body Jet',2,1,'2026-02-19 10:22:21','2026-02-19 10:22:21'),(25,2,17,'Project Series High Flow Body Jet','6996e477ba4ba_1771496567.jpg','Project Series High Flow Body Jet',3,1,'2026-02-19 10:22:47','2026-02-19 10:22:47'),(26,2,17,'Unomix Concealed Diverter Mixer','6996e4963e148_1771496598.jpg','Unomix Concealed Diverter Mixer',4,1,'2026-02-19 10:23:18','2026-02-19 10:23:18'),(27,2,17,'PPSU Controle Valve Collection','6996e4aa3fc48_1771496618.jpg','PPSU Controle Valve Collection',5,1,'2026-02-19 10:23:38','2026-02-19 10:23:38'),(28,2,17,'3-Inlet High Flow Diverter','6996e4cb21ea8_1771496651.jpg','3-Inlet High Flow Diverter',6,1,'2026-02-19 10:24:11','2026-02-19 10:24:11'),(29,2,17,'Body Jet (Brass)','6996e4e1c985e_1771496673.jpg','Body Jet (Brass)',7,1,'2026-02-19 10:24:33','2026-02-19 10:24:33'),(30,2,17,'Metropole & Flush Cock Collection','6996e504b2a21_1771496708.jpg','Metropole Flush Cock Collection',8,1,'2026-02-19 10:25:08','2026-02-21 08:55:01'),(31,2,17,'Brass Rain Shower Collection','6996e518019fe_1771496728.jpg','Brass Rain Shower Collection',9,1,'2026-02-19 10:25:28','2026-02-19 10:25:28'),(32,2,17,'Metropols','6996e530068d4_1771496752.jpg','Metropols',10,1,'2026-02-19 10:25:52','2026-02-19 10:25:52'),(33,2,17,'Exposed Part Kit','6996e5461c115_1771496774.jpg','Exposed Part Kit',11,1,'2026-02-19 10:26:14','2026-02-19 10:26:14'),(34,2,17,'Project Series High Flow Diverter','6996e557a3117_1771496791.jpg','Project Series High Flow Diverter',12,1,'2026-02-19 10:26:31','2026-02-19 10:26:31'),(35,2,17,'Project Series Spout Collection','6996e566977a7_1771496806.jpg','Project Series Spout Collection',13,1,'2026-02-19 10:26:46','2026-02-19 10:26:46'),(37,4,NULL,'Freestanding Bathtubs','6996fdf884056_1771503096.png','Freestanding Bathtubs',2,1,'2026-02-19 12:11:36','2026-03-07 07:50:10'),(38,4,NULL,'Drop in Bathtubs','6996fe0430af2_1771503108.png','Drop in Bathtubs',4,1,'2026-02-19 12:11:48','2026-03-07 07:50:22'),(39,4,NULL,'Freestanding Bathtub Facucets','6996fe12b465f_1771503122.png','Freestanding Bathtub Facucets',3,1,'2026-02-19 12:12:02','2026-03-07 07:50:17'),(40,4,NULL,'Whirlpools','699700b85f22a_1771503800.png','',1,1,'2026-02-19 12:23:20','2026-03-07 07:50:02'),(41,4,NULL,'Accessories',NULL,'Accessories',5,1,'2026-03-07 07:49:52','2026-03-07 07:49:52'),(42,2,22,'CERAMIC POP-UP WASTE COUPLING',NULL,'CERAMIC POP-UP WASTE COUPLING',14,1,'2026-03-20 13:48:48','2026-03-20 13:48:48');
/*!40000 ALTER TABLE `catalogue_series` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalogue_sub_categories`
--

DROP TABLE IF EXISTS `catalogue_sub_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalogue_sub_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_cat_sub_cat_cascade` (`category_id`),
  CONSTRAINT `catalogue_sub_categories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `catalogue_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cat_sub_cat_cascade` FOREIGN KEY (`category_id`) REFERENCES `catalogue_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalogue_sub_categories`
--

LOCK TABLES `catalogue_sub_categories` WRITE;
/*!40000 ALTER TABLE `catalogue_sub_categories` DISABLE KEYS */;
INSERT INTO `catalogue_sub_categories` VALUES (17,2,'DIVERTERS','6996e387a3f63_1771496327.jpg','DIVERTERS',1,1,'2026-02-19 10:18:47','2026-03-06 13:17:07'),(18,2,'LUXURY FAUCETS','6996e39e76ad0_1771496350.jpg','LUXURY FAUCETS',2,1,'2026-02-19 10:19:10','2026-02-19 10:19:10'),(19,2,'FAUCETS','6996e3b4c3af3_1771496372.jpg','FAUCETS',3,1,'2026-02-19 10:19:32','2026-02-19 10:19:32'),(20,2,'SHOWERS','6996e3ce16aba_1771496398.jpg','SHOWERS',4,1,'2026-02-19 10:19:58','2026-02-19 10:19:58'),(21,2,'DRAINER','6996e3e66cbba_1771496422.jpg','DRAINER',5,1,'2026-02-19 10:20:22','2026-02-19 10:20:22'),(22,2,'ACCESSORIES','6996e400b9e9d_1771496448.jpg','ACCESSORIES',6,1,'2026-02-19 10:20:48','2026-02-19 10:20:48'),(23,2,'BATH ACCESSORIES','6996e41712deb_1771496471.jpg','BATH ACCESSORIES',7,1,'2026-02-19 10:21:11','2026-02-19 10:21:11');
/*!40000 ALTER TABLE `catalogue_sub_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_history`
--

DROP TABLE IF EXISTS `chat_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_history` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mobile` varchar(20) NOT NULL,
  `direction` enum('incoming','outgoing') NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `type` enum('text','template','image','video','document') NOT NULL DEFAULT 'text',
  `body` text DEFAULT NULL,
  `media_url` text DEFAULT NULL,
  `status` enum('sent','delivered','read','failed') DEFAULT 'sent',
  `delivered_at` datetime DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `meta_message_id` varchar(100) DEFAULT NULL,
  `campaign_id` int(11) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_meta_message_id` (`meta_message_id`),
  KEY `idx_mobile` (`mobile`),
  KEY `idx_direction` (`direction`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_status` (`status`),
  KEY `idx_campaign_id` (`campaign_id`),
  KEY `idx_mobile_created` (`mobile`,`created_at`),
  KEY `idx_mobile_direction_read` (`mobile`,`direction`,`is_read`)
) ENGINE=InnoDB AUTO_INCREMENT=415 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_history`
--

LOCK TABLES `chat_history` WRITE;
/*!40000 ALTER TABLE `chat_history` DISABLE KEYS */;
INSERT INTO `chat_history` VALUES (329,'919033611456','outgoing',0,'template','Template: complaint_registered [BSW-00013, Sanitarywares]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDU2FQIAERgSM0ZBQTdFNUM5MThFMDFGMzdCAA==',NULL,'2026-02-27 10:35:01'),(330,'918460148508','outgoing',0,'template','Template: complaint_assigned_plumber [BSW-00013, Sanitarywares, gaurav Spartan, 919033611456, Rajkot, Gujarat, Normal]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE4NDYwMTQ4NTA4FQIAERgSNjExRThGMkVEMjQ1NTE1NkZGAA==',NULL,'2026-02-27 10:36:01'),(331,'919033611456','outgoing',0,'template','Template: complaint_assigned_customer [BSW-00013, anand gohel, 918460148508]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDU2FQIAERgSMzdDRTlDQjQxMDkyQUM5NUUyAA==',NULL,'2026-02-27 10:36:02'),(332,'919033611459','outgoing',0,'template','Template: complaint_registered_01 [BSW-00014, Bath Fittings]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDU5FQIAERgSRDVGRDEwNUM5NTYyMDdEQzRDAA==',NULL,'2026-03-09 13:29:48'),(333,'919033611444','outgoing',0,'template','Template: complaint_registered_01 [BSW-00015, Sanitarywares]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDQ0FQIAERgSM0Y3QUUxMTc2MDY5QUY2NkU0AA==',NULL,'2026-03-20 04:02:59'),(334,'919033611666','outgoing',0,'template','Template: complaint_registered_01 [BSW-00016, Sanitarywares]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNjY2FQIAERgSOTY4NTcxNkNFNUI1MzY1QkExAA==',NULL,'2026-03-20 04:14:44'),(335,'919033611256','outgoing',0,'template','Template: complaint_registered_01 [BSW-00017, Bath Fittings]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExMjU2FQIAERgSMDE3Q0MzRUFGMUQ1Mzc2NjQ1AA==',NULL,'2026-03-20 05:02:09'),(336,'919033611456','outgoing',0,'template','Template: complaint_registered_01 [BSW-00012, Bath Fittings]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDU2FQIAERgSQTFFMjY1REMwNEMxMDQ3NTY5AA==',NULL,'2026-03-20 06:54:50'),(337,'919033611453','outgoing',0,'template','Template: complaint_registered_01 [BSW-00013, Kitchen Sinks]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDUzFQIAERgSNjk4M0IxQjMyQ0M4QTIyRTRBAA==',NULL,'2026-03-20 10:53:24'),(338,'918460148508','outgoing',0,'template','Template: complaint_assigned_plumber_02 [BSW-00012, Bath Fittings, vivek patel, 919033611456, Morbi, Gujarat, Normal]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE4NDYwMTQ4NTA4FQIAERgSNDkxMEZCQ0QyM0Q4OTc5NUU5AA==',NULL,'2026-03-20 10:58:28'),(339,'919033611456','outgoing',0,'template','Template: complaint_assigned_customer_03 [BSW-00012, anand gohel, 918460148508]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDU2FQIAERgSM0NGMzkxNEJCMjY1QUIyNUZFAA==',NULL,'2026-03-20 10:58:30'),(340,'918460148508','outgoing',0,'template','Template: complaint_assigned_plumber_02 [BSW-00013, Kitchen Sinks, gyyhj vuvhv, 919033611453, rajkot, Gujarat, Normal]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE4NDYwMTQ4NTA4FQIAERgSQjM2NUFDNDhFMTJBMjgwRTU4AA==',NULL,'2026-03-24 11:03:31'),(341,'919033611453','outgoing',0,'template','Template: complaint_assigned_customer_03 [BSW-00013, anand gohel, 918460148508]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDUzFQIAERgSNTUwQTgwRDlEQkRDNEQ3ODExAA==',NULL,'2026-03-24 11:03:33'),(342,'919033611453','outgoing',0,'template','Template: complaint_accepted_05 [BSW-00013, anand gohel, 918460148508, 25 Mar, 04:38 PM]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDUzFQIAERgSNEIxODhERjhDMzQ3MzU5MkY4AA==',NULL,'2026-03-24 11:08:50'),(343,'919099910204','outgoing',0,'template','Template: bell_birthday_wish_v1 [aaa aaaa] [with media]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDk5OTEwMjA0FQIAERgSNjk4RDVDNkQ1OTk2RDMwNDVGAA==',110,'2026-03-30 14:39:45'),(344,'918460148508','outgoing',0,'template','Template: bell_birthday_wish_v1 [anand gohel] [with media]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE4NDYwMTQ4NTA4FQIAERgSNUI3REM0ODIyRTY1MTA3MDgxAA==',110,'2026-03-30 14:39:45'),(345,'919033611459','outgoing',0,'template','Template: bell_birthday_wish_v1 [anand Spartan] [with media]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDU5FQIAERgSNEI1MEQzNjI0OUNDQjg5OEJBAA==',110,'2026-03-30 14:39:46'),(346,'919033611453','outgoing',0,'template','Template: bell_birthday_wish_v1 [gyyhj vuvhv] [with media]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDUzFQIAERgSMDZCQUE4Nzk3RjZBNzBDNkUwAA==',110,'2026-03-30 14:39:47'),(347,'919033611256','outgoing',0,'template','Template: bell_birthday_wish_v1 [NEW Spartan] [with media]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExMjU2FQIAERgSNEExNzkzNDFCNENDMjdCRjFBAA==',110,'2026-03-30 14:39:48'),(348,'919911223344','outgoing',0,'template','Template: bell_birthday_wish_v1 [rishi rishi] [with media]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5OTExMjIzMzQ0FQIAERgSM0M1MDI3NTE1MDQzNTAyNEJFAA==',110,'2026-03-30 14:39:49'),(349,'919999999999','outgoing',0,'template','Template: bell_birthday_wish_v1 [Test Fresh] [with media]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5OTk5OTk5OTk5FQIAERgSQkIzMjM2RURGQUYwN0Y3NjI0AA==',110,'2026-03-30 14:39:49'),(350,'919033611456','outgoing',0,'template','Template: bell_birthday_wish_v1 [vivek patel] [with media]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDU2FQIAERgSMjZEMjExNkVEOEM5ODQxOUE1AA==',110,'2026-03-30 14:39:50'),(351,'919033611457','outgoing',0,'template','Template: bell_birthday_wish_v1 [vivek patel] [with media]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDU3FQIAERgSM0Y0Nzg2MEZEQkZCQjA5MDU0AA==',110,'2026-03-30 14:39:50'),(352,'919033611458','outgoing',0,'template','Template: bell_birthday_wish_v1 [vivek patel] [with media]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDU4FQIAERgUQ0FEQ0I1OTE0MTYxNDk5NTY5OUIA',110,'2026-03-30 14:39:51'),(353,'919033611444','outgoing',0,'template','Template: bell_birthday_wish_v1 [vivek Spartan] [with media]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDQ0FQIAERgSOEYzQkM3MDkyOTIwNTNBRDA1AA==',110,'2026-03-30 14:39:51'),(354,'919033611666','outgoing',0,'template','Template: bell_birthday_wish_v1 [vivek Spartan] [with media]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNjY2FQIAERgSQUI5QzU3NDIwN0NGMzg5RDY2AA==',110,'2026-03-30 14:39:52'),(355,'919033611488','outgoing',0,'template','Template: complaint_registered_01 [BSW-00014, Sanitarywares]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDg4FQIAERgSNUJCMDg4MjFCMDU0RTlFQzY4AA==',NULL,'2026-03-30 15:00:50'),(356,'918460148508','outgoing',0,'template','Template: complaint_assigned_plumber_02 [BSW-00014, Sanitarywares, vivek Spartan, 919033611488, Rajkot, Gujarat, Normal]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE4NDYwMTQ4NTA4FQIAERgSMTg2NjcxOTVERjYyNzlENEEzAA==',NULL,'2026-03-30 15:02:18'),(357,'919033611488','outgoing',0,'template','Template: complaint_assigned_customer_03 [BSW-00014, anand gohel, 918460148508]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDg4FQIAERgSMEFGNTFCQTVCMjFGMkUxRTRGAA==',NULL,'2026-03-30 15:02:19'),(358,'919033611488','outgoing',0,'template','Template: complaint_accepted_05 [BSW-00014, anand gohel, 918460148508, 30 Mar, 10:34 PM]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDg4FQIAERgSNzZERjNFNUM2RTg2REQzREU5AA==',NULL,'2026-03-30 15:04:33'),(359,'919033611488','outgoing',0,'template','Template: complaint_in_progress_06 [BSW-00014, anand gohel]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDg4FQIAERgSNzg3OTJBMDdGMTBEODdGRDREAA==',NULL,'2026-03-30 15:05:22'),(360,'919033611488','outgoing',0,'template','Template: complaint_solvedreference_07 [BSW-00014, 650, 650, 0, Company Warranty (Free), 944209]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDg4FQIAERgSMjRCQTA2NTIxOTJBQjU1NTNEAA==',NULL,'2026-03-30 15:05:57'),(361,'918877994455','outgoing',0,'template','Template: complaint_registered_01 [BSW-00015, Sanitarywares]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE4ODc3OTk0NDU1FQIAERgSQ0FDRkFFMzk0MjhBMzNENEU3AA==',NULL,'2026-04-22 12:54:06'),(362,'918460148508','outgoing',0,'template','Template: complaint_assigned_plumber_02 [BSW-00015, Sanitarywares, rajesh thakur, 918877994455, Rajkot, Gujarat, Critical]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE4NDYwMTQ4NTA4FQIAERgSRDQyQUQ0NEQyMEQzRjY0QzAwAA==',NULL,'2026-04-22 12:56:09'),(363,'918877994455','outgoing',0,'template','Template: complaint_assigned_customer_03 [BSW-00015, anand gohel, 918460148508]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE4ODc3OTk0NDU1FQIAERgSNjBGQzU1MDUxODVGMzgzNTRBAA==',NULL,'2026-04-22 12:56:10'),(364,'918877994455','outgoing',0,'template','Template: complaint_accepted_05 [BSW-00015, anand gohel, 918460148508, 23 Apr, 03:26 PM]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE4ODc3OTk0NDU1FQIAERgSODVDMjk1MjcxQzU5OEM3NTAzAA==',NULL,'2026-04-22 12:57:11'),(365,'918877994455','outgoing',0,'template','Template: complaint_in_progress_06 [BSW-00015, anand gohel]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE4ODc3OTk0NDU1FQIAERgSM0Q4MkU5QkIzQjc4MjMyNkU1AA==',NULL,'2026-04-22 12:58:05'),(366,'918877994455','outgoing',0,'template','Template: complaint_solvedreference_07 [BSW-00015, 650, 650, 0, Company Warranty (Free), 904363]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE4ODc3OTk0NDU1FQIAERgSNzY2MUEzMUI0NjQ4MTY2NEIyAA==',NULL,'2026-04-22 12:58:43'),(367,'919033611444','outgoing',0,'template','Template: complaint_registered_01 [BSW-00016, Kitchen Sinks]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDQ0FQIAERgSNDUwQzBBMEZBNDRBMjg1N0Q1AA==',NULL,'2026-05-11 06:09:15'),(368,'919033111222','outgoing',0,'template','Template: complaint_registered_01 [BSW-00017, Sanitarywares]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzMTExMjIyFQIAERgSNEJEMDk2NjRFQkVEMjEzN0M3AA==',NULL,'2026-05-11 06:14:28'),(369,'918460148508','outgoing',0,'template','Template: complaint_assigned_plumber_02 [BSW-00016, Kitchen Sinks, viveka Spartan, 919033611444, Rajkot, Gujarat, Normal]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE4NDYwMTQ4NTA4FQIAERgSRjA5QzhFOUJFMTVBQTY4MEUwAA==',NULL,'2026-05-11 06:17:58'),(370,'919033611444','outgoing',0,'template','Template: complaint_assigned_customer_03 [BSW-00016, anand gohel, 918460148508]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDQ0FQIAERgSNUZBODcxRDI1ODA2RTYzQkIyAA==',NULL,'2026-05-11 06:17:59'),(371,'919033611444','outgoing',0,'template','Template: complaint_accepted_05 [BSW-00016, anand gohel, 918460148508, 11 May, 11:48 AM]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDQ0FQIAERgSNEY0NzhBNkMyRDY1Q0RDMkUxAA==',NULL,'2026-05-11 06:18:19'),(372,'919033611444','outgoing',0,'template','Template: complaint_in_progress_06 [BSW-00016, anand gohel]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDQ0FQIAERgSNTU1OTYwOUM0RjRFN0M5MjFDAA==',NULL,'2026-05-11 06:18:28'),(373,'919033611444','outgoing',0,'template','Template: complaint_solvedreference_07 [BSW-00016, 0, 0, 0, Company Warranty (Free), 130220]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDQ0FQIAERgSRDVGQ0RCM0Y1MEI1RDlDODdEAA==',NULL,'2026-05-11 06:18:35'),(374,'919033611456','outgoing',0,'template','Template: complaint_registered_01 [BSW-00001, Sanitarywares]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDU2FQIAERgSRUU2MTQ5NzVBNkEwMUJBM0Y1AA==',NULL,'2026-06-08 11:54:15'),(375,'918140824102','outgoing',0,'template','Template: complaint_assigned_plumber_02 [BSW-00001, Sanitarywares, vivek patel, 919033611456, RAJKOT, Gujarat, Normal]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE4MTQwODI0MTAyFQIAERgSOTAyMEFFN0JCMkI4Rjk3NEYyAA==',NULL,'2026-06-08 11:55:55'),(376,'919033611456','outgoing',0,'template','Template: complaint_assigned_customer_03 [BSW-00001, anand gohel, 918140824102]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDU2FQIAERgSQTkwMUE0Nzg3Q0YyRjUxMkI1AA==',NULL,'2026-06-08 11:55:56'),(377,'918140824102','outgoing',0,'template','Template: complaint_assigned_plumber_02 [BSW-00001, Sanitarywares, vivek patel, 919033611456, RAJKOT, Gujarat, Normal]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE4MTQwODI0MTAyFQIAERgSNEU0RjlEQUEwQkFBNTVENUJGAA==',NULL,'2026-06-08 11:55:57'),(378,'919033611456','outgoing',0,'template','Template: complaint_assigned_customer_03 [BSW-00001, anand gohel, 918140824102]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDU2FQIAERgSNDk0MzlDRUUyMzZBM0VGNUE4AA==',NULL,'2026-06-08 11:55:58'),(379,'919033611456','outgoing',0,'template','Template: complaint_assigned_customer_03 [ELG-TEST-001, Vivek Patel, +91 88885 88333]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDU2FQIAERgSQ0UxNEZGMDQ5NDAwRkJBRkM4AA==',NULL,'2026-06-08 14:18:50'),(380,'919033611456','outgoing',0,'template','Template: complaint_registered_01 [ELG-PRE-VERIFY, Test Category]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDU2FQIAERgSMzEzMkQxOURFMjcxN0M3ODlDAA==',NULL,'2026-06-08 14:44:22'),(381,'919033611456','outgoing',0,'template','Template: complaint_registered_01 [CHK-201502, Bathware Test]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDU2FQIAERgSRjcxQTU0MjRFMEIwQjE1OEMzAA==',NULL,'2026-06-08 14:45:04'),(382,'919033611456','outgoing',0,'template','Template: complaint_registered_01 [POSTVERIFY-1101, Bathware Verified Test]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDU2FQIAERgSNzE5OEYxMzk0QzAxMkVGMjM1AA==',NULL,'2026-06-09 05:31:39'),(383,'919033611456','outgoing',0,'template','Template: complaint_registered_01 [WIN-123052, Service Window Test]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDU2FQIAERgSNTE2ODk1MUFEMUU5MjU0MDQyAA==',NULL,'2026-06-09 07:00:52'),(384,'919712875874','outgoing',0,'template','Template: complaint_registered_01 [BSW-00002, Kitchen Sinks]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5NzEyODc1ODc0FQIAERgSREZBRUFFMkZDNDg4QkYzMDRBAA==',NULL,'2026-06-09 07:14:51'),(385,'919712875874','outgoing',0,'template','Template: complaint_registered_01 [PAID-125409, Post-Payment Test]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5NzEyODc1ODc0FQIAERgSMUFCRUYxNzNENDdBMzJGOERBAA==',NULL,'2026-06-09 07:24:10'),(386,'919033611456','outgoing',0,'template','Template: complaint_registered_01 [WEBHK-162823, Webhook Test]',NULL,'read','2026-06-09 16:28:24','2026-06-09 16:29:16',NULL,'wamid.HBgMOTE5MDMzNjExNDU2FQIAERgSRTJBRDFCN0Y4QTVDMkU5QUYwAA==',NULL,'2026-06-09 10:58:25'),(387,'918460148508','outgoing',0,'template','Template: complaint_registered_01 [COLD-163059, Cold Number Test]',NULL,'failed',NULL,NULL,'[{\"code\":131042,\"title\":\"Business eligibility payment issue\",\"message\":\"Business eligibility payment issue\",\"error_data\":{\"details\":\"Message failed to send because there were one or more errors related to your payment method.\"},\"href\":\"https:\\/\\/developers.facebook.com\\/docs\\/whatsapp\\/cloud-api\\/support\\/error-codes\\/\"}]','wamid.HBgMOTE4NDYwMTQ4NTA4FQIAERgSOUQ5QUEwNjdDMkIxQzk3MDU4AA==',NULL,'2026-06-09 11:01:00'),(388,'917996101311','outgoing',0,'','🙏 Welcome to *Eleganza Bathware*!\n\nHow can we help you today? Please choose an option:',NULL,'failed',NULL,NULL,'[{\"code\":131026,\"title\":\"Message undeliverable\",\"message\":\"Message undeliverable\",\"error_data\":{\"details\":\"Message Undeliverable.\"},\"href\":\"https:\\/\\/developers.facebook.com\\/docs\\/whatsapp\\/cloud-api\\/support\\/error-codes\\/\"}]','wamid.HBgMOTE3OTk2MTAxMzExFQIAERgSOEEwNEU4M0Q3RkZDQTZFQTUwAA==',NULL,'2026-06-09 11:52:01'),(389,'917996101311','incoming',0,'','[Unsupported]',NULL,'delivered',NULL,NULL,NULL,'wamid.HBgMOTE3OTk2MTAxMzExFQIAEhgSQUFCREQwQjEzQ0FCRjlFMzgzAA==',NULL,'2026-06-09 11:52:01'),(390,'918460148508','outgoing',0,'template','Template: complaint_registered_01 [NOGST-173844, GST Removed Test]',NULL,'failed',NULL,NULL,'[{\"code\":131042,\"title\":\"Business eligibility payment issue\",\"message\":\"Business eligibility payment issue\",\"error_data\":{\"details\":\"Message failed to send because there were one or more errors related to your payment method.\"},\"href\":\"https:\\/\\/developers.facebook.com\\/docs\\/whatsapp\\/cloud-api\\/support\\/error-codes\\/\"}]','wamid.HBgMOTE4NDYwMTQ4NTA4FQIAERgSMEI4RTE3MUFDRTYwN0UwMzU2AA==',NULL,'2026-06-09 12:08:45'),(391,'918460148508','outgoing',0,'template','Template: complaint_registered_01 [RETRY-181145, Retry Test]',NULL,'failed',NULL,NULL,'[{\"code\":131042,\"title\":\"Business eligibility payment issue\",\"message\":\"Business eligibility payment issue\",\"error_data\":{\"details\":\"Message failed to send because there were one or more errors related to your payment method.\"},\"href\":\"https:\\/\\/developers.facebook.com\\/docs\\/whatsapp\\/cloud-api\\/support\\/error-codes\\/\"}]','wamid.HBgMOTE4NDYwMTQ4NTA4FQIAERgSQjU2RDU4OTE1MDY3MkQwQTk2AA==',NULL,'2026-06-09 12:41:46'),(392,'919737016642','outgoing',0,'template','Template: complaint_registered_01 [NEWCARD-193109, New Card Test]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5NzM3MDE2NjQyFQIAERgSOUQyNjgyRDhFQkJBRjVGQzM1AA==',NULL,'2026-06-11 14:01:10'),(393,'919737016642','outgoing',0,'template','Template: complaint_registered_01 [VERIFIED-115016, Tax Verified Test]',NULL,'failed',NULL,NULL,'[{\"code\":131042,\"title\":\"Business eligibility payment issue\",\"message\":\"Business eligibility payment issue\",\"error_data\":{\"details\":\"Message failed to send because there were one or more errors related to your payment method.\"},\"href\":\"https:\\/\\/developers.facebook.com\\/docs\\/whatsapp\\/cloud-api\\/support\\/error-codes\\/\"}]','wamid.HBgMOTE5NzM3MDE2NjQyFQIAERgSMjI2QTU2MzRENTgwNDM5MDc2AA==',NULL,'2026-06-16 06:20:17'),(394,'918140824102','outgoing',0,'template','Template: complaint_assigned_plumber_02 [BSW-00002, Kitchen Sinks, animesh sindhav, 919712875874, Rajkot, Gujarat, Normal]',NULL,'failed',NULL,NULL,'[{\"code\":131042,\"title\":\"Business eligibility payment issue\",\"message\":\"Business eligibility payment issue\",\"error_data\":{\"details\":\"Message failed to send because there were one or more errors related to your payment method.\"},\"href\":\"https:\\/\\/developers.facebook.com\\/docs\\/whatsapp\\/cloud-api\\/support\\/error-codes\\/\"}]','wamid.HBgMOTE4MTQwODI0MTAyFQIAERgSODI0NEI3MzA5Q0I5MzBGMjUyAA==',NULL,'2026-06-16 06:22:11'),(395,'919712875874','outgoing',0,'template','Template: complaint_assigned_customer_03 [BSW-00002, anand gohel, 918140824102]',NULL,'failed',NULL,NULL,'[{\"code\":131042,\"title\":\"Business eligibility payment issue\",\"message\":\"Business eligibility payment issue\",\"error_data\":{\"details\":\"Message failed to send because there were one or more errors related to your payment method.\"},\"href\":\"https:\\/\\/developers.facebook.com\\/docs\\/whatsapp\\/cloud-api\\/support\\/error-codes\\/\"}]','wamid.HBgMOTE5NzEyODc1ODc0FQIAERgSQUIwQzlEQzQ1QjZCNDM1RDZBAA==',NULL,'2026-06-16 06:22:12'),(396,'918758707750','outgoing',0,'template','Template: complaint_registered_01 [TEST2-120035, Verified Card Test]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE4NzU4NzA3NzUwFQIAERgSNzU5OEE2NkRCRERDRDVCQkM2AA==',NULL,'2026-06-16 06:30:36'),(397,'918460148508','outgoing',0,'template','Template: complaint_registered_01 [COLD-120440, Post-Verification Cold Test]',NULL,'failed',NULL,NULL,'[{\"code\":131042,\"title\":\"Business eligibility payment issue\",\"message\":\"Business eligibility payment issue\",\"error_data\":{\"details\":\"Message failed to send because there were one or more errors related to your payment method.\"},\"href\":\"https:\\/\\/developers.facebook.com\\/docs\\/whatsapp\\/cloud-api\\/support\\/error-codes\\/\"}]','wamid.HBgMOTE4NDYwMTQ4NTA4FQIAERgSMDM5QTY4RDU2Qjc1MzQ0QjgyAA==',NULL,'2026-06-16 06:34:41'),(398,'918460148508','outgoing',0,'template','Template: complaint_registered_01 [LOCAL-121823, Local Test After Tax]',NULL,'failed',NULL,NULL,'[{\"code\":131042,\"title\":\"Business eligibility payment issue\",\"message\":\"Business eligibility payment issue\",\"error_data\":{\"details\":\"Message failed to send because there were one or more errors related to your payment method.\"},\"href\":\"https:\\/\\/developers.facebook.com\\/docs\\/whatsapp\\/cloud-api\\/support\\/error-codes\\/\"}]','wamid.HBgMOTE4NDYwMTQ4NTA4FQIAERgSNTQ3N0I2NDYxOEEyMjgzQTY1AA==',NULL,'2026-06-16 06:48:23'),(399,'919654297000','incoming',0,'','[Unsupported]',NULL,'delivered',NULL,NULL,NULL,'wamid.HBgMOTE5NjU0Mjk3MDAwFQIAEhgSNzA5RDkzMDQ1QTA0NDVENDVFAA==',NULL,'2026-06-16 13:48:06'),(400,'919033611456','outgoing',0,'template','Template: complaint_registered_01 [EBPL-00001, Sanitarywares]',NULL,'failed',NULL,NULL,'[{\"code\":131042,\"title\":\"Business eligibility payment issue\",\"message\":\"Business eligibility payment issue\",\"error_data\":{\"details\":\"Message failed to send because there were one or more errors related to your payment method.\"},\"href\":\"https:\\/\\/developers.facebook.com\\/docs\\/whatsapp\\/cloud-api\\/support\\/error-codes\\/\"}]','wamid.HBgMOTE5MDMzNjExNDU2FQIAERgSNzQzRUY3Nzg1QTg2NzVGQzY2AA==',NULL,'2026-06-16 14:18:51'),(401,'918140824102','outgoing',0,'template','Template: complaint_assigned_plumber_02 [EBPL-00001, Sanitarywares, dasdf Spartan, 919033611456, Rajkot, Gujarat, Normal]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE4MTQwODI0MTAyFQIAERgSNjAxQkJCNkRDRDc5QzI4Q0M1AA==',NULL,'2026-06-23 11:12:06'),(402,'919033611456','outgoing',0,'template','Template: complaint_assigned_customer_03 [EBPL-00001, anand gohel, 918140824102]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDU2FQIAERgSMzQ1NjcxN0UzRDdFMEU1Qjg0AA==',NULL,'2026-06-23 11:12:07'),(403,'919033611456','outgoing',0,'template','Template: complaint_accepted_05 [EBPL-00001, anand gohel, 918140824102, 23 Jun, 04:45 PM]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDU2FQIAERgSNjExOTZGQTAyRkRFRDI0RDQyAA==',NULL,'2026-06-23 11:15:20'),(404,'919033611456','outgoing',0,'template','Template: complaint_in_progress_06 [EBPL-00001, anand gohel]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDU2FQIAERgSNkIyODFGMDJFQTU4MTdCQjBGAA==',NULL,'2026-06-23 11:15:24'),(405,'919033611456','outgoing',0,'template','Template: complaint_solvedreference_07 [EBPL-00001, 550, 450, 100, Company Warranty (Free), 689811]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5MDMzNjExNDU2FQIAERgSMEJDNzA0NkNEMUU0QUNDODU5AA==',NULL,'2026-06-23 11:15:37'),(406,'919033611456','outgoing',0,'template','Template: complaint_closed_success_08 [EBPL-00001]',NULL,'read','2026-06-25 16:11:31','2026-06-25 16:13:01',NULL,'wamid.HBgMOTE5MDMzNjExNDU2FQIAERgSRjE4QTQ1NTA1NjEzQ0Y2NEI3AA==',NULL,'2026-06-25 10:41:23'),(407,'918460148508','outgoing',0,'template','Template: complaint_closed_success_08 [EBPL-00001]',NULL,'read','2026-06-25 16:38:42','2026-06-25 16:38:48',NULL,'wamid.HBgMOTE4NDYwMTQ4NTA4FQIAERgSOTc2Qzc2RUFGOTE4OTZBQUM3AA==',NULL,'2026-06-25 11:08:33'),(408,'919712875874','outgoing',0,'template','Template: complaint_registered_01 [EBPL-00002, Sanitarywares]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5NzEyODc1ODc0FQIAERgSMjhEMTUwNzc2QkI1NzhDRTZEAA==',NULL,'2026-06-25 11:25:54'),(409,'918758707750','outgoing',0,'template','Template: complaint_registered_01 [EBPL-00003, Sanitarywares]',NULL,'read','2026-06-25 16:58:00','2026-06-25 16:58:03',NULL,'wamid.HBgMOTE4NzU4NzA3NzUwFQIAERgSNjk1QzU2OTJCRkJDOTkyQ0VGAA==',NULL,'2026-06-25 11:27:51'),(410,'918140824102','outgoing',0,'template','Template: complaint_assigned_plumber_02 [EBPL-00002, Sanitarywares, Animesh sindhav, 919712875874, Rajkot, Gujarat, Normal]',NULL,'read','2026-06-25 17:04:25','2026-06-25 17:24:39',NULL,'wamid.HBgMOTE4MTQwODI0MTAyFQIAERgSQzZGMTA3QTY4RTIyMDk2OTcxAA==',NULL,'2026-06-25 11:34:16'),(411,'919712875874','outgoing',0,'template','Template: complaint_assigned_customer_03 [EBPL-00002, anand gohel, 918140824102]',NULL,'sent',NULL,NULL,NULL,'wamid.HBgMOTE5NzEyODc1ODc0FQIAERgSNDYwMDg3REU5RUM5OEY0NDBCAA==',NULL,'2026-06-25 11:34:17'),(412,'918140824102','outgoing',0,'template','Template: complaint_assigned_plumber_02 [EBPL-00003, Sanitarywares, Animesh sindhav, 918758707750, Rajkot, Gujarat, Normal]',NULL,'read','2026-06-25 17:05:00','2026-06-25 17:24:39',NULL,'wamid.HBgMOTE4MTQwODI0MTAyFQIAERgSRkRBOTZCOURDRjEyRUI3NTI4AA==',NULL,'2026-06-25 11:34:53'),(413,'918758707750','outgoing',0,'template','Template: complaint_assigned_customer_03 [EBPL-00003, anand gohel, 918140824102]',NULL,'read','2026-06-25 17:05:00','2026-06-25 18:00:33',NULL,'wamid.HBgMOTE4NzU4NzA3NzUwFQIAERgSOEU1NDNFOUMwRTFCMTk0MzM2AA==',NULL,'2026-06-25 11:34:54'),(414,'918140824102','incoming',0,'text','Ye kya hai sir',NULL,'delivered',NULL,NULL,NULL,'wamid.HBgMOTE4MTQwODI0MTAyFQIAEhggQUM0NzRFNEQ3MDdFQTMxMTVGMThGQzdBRjlCNjY2QUMA',NULL,'2026-06-25 11:56:26');
/*!40000 ALTER TABLE `chat_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `gst_number` varchar(20) DEFAULT NULL,
  `bank_details` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `companies`
--

LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
INSERT INTO `companies` VALUES (1,'Eleganza Bathware','SHOP NO.305 & 306 BENCH MARK\r\nRAVISHANKAR MARG NASHIK\r\nUpnagar Sub Post Office\r\nNASHIK 422006\r\nMaharashtra','27AAJCE0475D1ZS','CANARA BANK - KHALILABAD\r\nA/c. No.: \r\nIFSC Code :',1,'2026-02-04 08:44:00');
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_banners`
--

DROP TABLE IF EXISTS `company_banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image_url` varchar(255) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_banners`
--

LOCK TABLES `company_banners` WRITE;
/*!40000 ALTER TABLE `company_banners` DISABLE KEYS */;
INSERT INTO `company_banners` VALUES (3,'banner_1772015866_0.webp',1,'2026-02-25 10:37:46'),(4,'banner_1772015866_1.webp',2,'2026-02-25 10:37:46'),(5,'banner_1772015866_2.webp',0,'2026-02-25 10:37:46'),(6,'banner_1772015866_3.webp',3,'2026-02-25 10:37:46'),(7,'banner_1772015866_4.webp',4,'2026-02-25 10:37:46');
/*!40000 ALTER TABLE `company_banners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `complaint_history`
--

DROP TABLE IF EXISTS `complaint_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `complaint_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `complaint_id` int(11) NOT NULL,
  `action_type` enum('created','assigned','reassigned','accepted','note_added','status_changed','tutorial_sent','resolved','otp_verified','closed','cancelled','settled') NOT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_customer_visible` tinyint(1) DEFAULT 0,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_complaint` (`complaint_id`),
  KEY `idx_action_type` (`action_type`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `complaint_history_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `complaint_history`
--

LOCK TABLES `complaint_history` WRITE;
/*!40000 ALTER TABLE `complaint_history` DISABLE KEYS */;
INSERT INTO `complaint_history` VALUES (6,3,'created',NULL,NULL,'Complaint registered via web form',0,1,'2026-06-16 14:18:50'),(7,3,'assigned',NULL,NULL,'Assigned to anand gohel - Service Type: Free (Warranty)',0,1,'2026-06-23 11:12:05'),(8,3,'accepted','ASSIGNED','ACCEPTED','Plumber accepted the complaint. Visit scheduled for: 23 Jun, 04:45 PM',0,60,'2026-06-23 11:15:19'),(9,3,'status_changed','ACCEPTED','IN_PROGRESS','Plumber started work',0,60,'2026-06-23 11:15:22'),(10,3,'',NULL,NULL,'Plumber marked as solved. Labor: 450, Parts: 100. Service Code generated.',0,0,'2026-06-23 11:15:36'),(11,3,'',NULL,NULL,'Admin bypassed OTP verification. Reason: adfasfd',0,1,'2026-06-23 11:15:46'),(12,3,'',NULL,NULL,'Admin reviewed and approved costs. done',0,1,'2026-06-23 11:15:51'),(13,3,'','UNPAID','PENDING','Added to Debit Note (Pending): DN-1006. Amount: ₹550',0,1,'2026-06-23 11:16:02'),(14,56,'created',NULL,NULL,'Complaint registered via web form',0,1,'2026-06-25 11:25:53'),(15,57,'created',NULL,NULL,'Complaint registered via web form',0,1,'2026-06-25 11:27:50'),(16,56,'assigned',NULL,NULL,'Assigned to anand gohel - Service Type: Free (Warranty)',0,1,'2026-06-25 11:34:15'),(17,57,'assigned',NULL,NULL,'Assigned to anand gohel - Service Type: Free (Warranty)',0,1,'2026-06-25 11:34:53');
/*!40000 ALTER TABLE `complaint_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `complaint_otps`
--

DROP TABLE IF EXISTS `complaint_otps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `complaint_otps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `complaint_id` int(11) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_complaint` (`complaint_id`),
  KEY `idx_otp` (`otp`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `complaint_otps`
--

LOCK TABLES `complaint_otps` WRITE;
/*!40000 ALTER TABLE `complaint_otps` DISABLE KEYS */;
INSERT INTO `complaint_otps` VALUES (37,55,'944209','2026-03-30 17:35:57',0,NULL,'2026-03-30 15:05:57'),(38,56,'904363','2026-04-22 18:58:41',0,NULL,'2026-04-22 12:58:41'),(39,57,'130220','2026-05-11 12:18:34',0,NULL,'2026-05-11 06:18:34'),(40,3,'689811','2026-06-23 17:15:36',0,NULL,'2026-06-23 11:15:36');
/*!40000 ALTER TABLE `complaint_otps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `complaints`
--

DROP TABLE IF EXISTS `complaints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `complaints` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_number` varchar(20) NOT NULL,
  `audience_id` int(10) unsigned NOT NULL,
  `category_id` int(11) NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `bill_photo` varchar(500) DEFAULT NULL,
  `warranty_status` enum('in_warranty','out_of_warranty','unknown') DEFAULT 'unknown',
  `warranty_end_date` date DEFAULT NULL,
  `issue_media` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`issue_media`)),
  `assigned_to` int(10) unsigned DEFAULT NULL,
  `status` enum('PENDING_ASSIGNMENT','ASSIGNED','ACCEPTED','IN_PROGRESS','SOLVED_PENDING_VERIFICATION','PENDING_ADMIN_REVIEW','CLOSED_SUCCESS','RESOLVED_WITHOUT_VISIT','CANCELLED') DEFAULT 'PENDING_ASSIGNMENT',
  `cancellation_reason` varchar(100) DEFAULT NULL,
  `cancellation_notes` text DEFAULT NULL,
  `priority` enum('normal','urgent','critical') DEFAULT 'normal',
  `service_type` enum('free','paid') DEFAULT NULL COMMENT 'Admin decision: free or paid',
  `diagnosis` text DEFAULT NULL,
  `resolution` text DEFAULT NULL,
  `labor_cost` decimal(10,2) DEFAULT 0.00 COMMENT 'Labor charges',
  `parts_cost` decimal(10,2) DEFAULT 0.00 COMMENT 'Parts charges',
  `total_cost` decimal(10,2) DEFAULT 0.00 COMMENT 'Total cost',
  `paid_by` enum('customer','company') DEFAULT NULL COMMENT 'Who pays',
  `payment_method_override` tinyint(1) DEFAULT 0 COMMENT 'Plumber override',
  `override_reason` text DEFAULT NULL COMMENT 'Override reason',
  `payment_verified` tinyint(1) DEFAULT 0 COMMENT 'OTP verified',
  `otp_code` varchar(6) DEFAULT NULL COMMENT 'OTP code',
  `otp_generated_at` datetime DEFAULT NULL,
  `otp_expires_at` datetime DEFAULT NULL,
  `otp_verified_at` datetime DEFAULT NULL,
  `manual_override` tinyint(1) DEFAULT 0,
  `manual_override_reason` text DEFAULT NULL,
  `manual_override_by` int(10) unsigned DEFAULT NULL,
  `manual_override_at` datetime DEFAULT NULL,
  `admin_reviewed_at` datetime DEFAULT NULL,
  `admin_reviewed_by` int(10) unsigned DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `original_labor_cost` decimal(10,2) DEFAULT NULL,
  `original_parts_cost` decimal(10,2) DEFAULT NULL,
  `admin_approved_labor_cost` decimal(10,2) DEFAULT NULL,
  `payment_status` enum('UNPAID_TO_PLUMBER','PAID_BY_CUSTOMER','SETTLED') DEFAULT 'UNPAID_TO_PLUMBER',
  `settlement_id` int(11) DEFAULT NULL,
  `settlement_amount` decimal(10,2) DEFAULT NULL,
  `source` enum('web_form','whatsapp_n8n','mobile_app','manual') DEFAULT 'web_form',
  `visit_scheduled_at` datetime DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `settled_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_number` (`ticket_number`),
  KEY `category_id` (`category_id`),
  KEY `idx_ticket` (`ticket_number`),
  KEY `idx_audience` (`audience_id`),
  KEY `idx_status` (`status`),
  KEY `idx_assigned` (`assigned_to`),
  KEY `idx_city_pincode` (`city`,`pincode`),
  KEY `idx_created` (`created_at`),
  KEY `idx_priority` (`priority`),
  KEY `idx_status_paid_by` (`status`,`paid_by`),
  KEY `idx_otp_expires` (`otp_expires_at`),
  KEY `idx_settlement` (`settlement_id`),
  CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`audience_id`) REFERENCES `audience` (`id`),
  CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`),
  CONSTRAINT `complaints_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `audience` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `complaints`
--

LOCK TABLES `complaints` WRITE;
/*!40000 ALTER TABLE `complaints` DISABLE KEYS */;
INSERT INTO `complaints` VALUES (3,'EBPL-00001',121,1,'adsf','asdf','1108/1109 RK Prime, Nana Mava Main Road, Rajkot 360005','Rajkot','Gujarat','Rajkot','360005','2026-06-03',NULL,'in_warranty','2031-06-03','{\"photos\":[\"issue_photo_1_6a315b4ab3b99.png\",\"issue_photo_2_6a315b4ab3cd7.png\"],\"video\":null}',60,'CLOSED_SUCCESS',NULL,NULL,'normal','free',NULL,'1 free part used',450.00,100.00,550.00,'company',0,NULL,0,'689811','2026-06-23 16:45:36','2026-06-23 17:15:36',NULL,1,'adfasfd',1,'2026-06-23 16:45:46','2026-06-23 16:45:51',1,'done',450.00,100.00,NULL,'SETTLED',20,550.00,'web_form','2026-06-23 16:45:00','2026-06-23 16:42:05','2026-06-23 16:45:51','2026-06-23 16:45:51','2026-06-23 17:03:46','2026-06-16 14:18:50','2026-06-23 11:33:46'),(56,'EBPL-00002',122,1,'adffasdfasdf','afasdfasdfasdfkjashdf aplsdkfjaslfkj','1108/1109 RK Prime, Nana Mava Main Road, Rajkot 360005','Rajkot','Gujarat','Rajkot','360005','2026-06-03',NULL,'in_warranty','2031-06-03','{\"photos\":[\"issue_photo_1_6a3d10417af1d.jpeg\"],\"video\":\"issue_video_6a3d10417b3bc.mp4\"}',60,'ASSIGNED',NULL,NULL,'normal','free',NULL,NULL,0.00,0.00,0.00,'company',0,NULL,0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'UNPAID_TO_PLUMBER',NULL,NULL,'web_form',NULL,'2026-06-25 17:04:15',NULL,NULL,NULL,'2026-06-25 11:25:53','2026-06-25 11:34:15'),(57,'EBPL-00003',176,1,'adffasdfasdf','afasdfasdfasdfkjashdf aplsdkfjaslfkj','1108/1109 RK Prime, Nana Mava Main Road, Rajkot 360005','Rajkot','Gujarat','Rajkot','360005','2026-06-03',NULL,'in_warranty','2031-06-03','{\"photos\":[\"issue_photo_1_6a3d10b6619cd.jpg\"],\"video\":\"issue_video_6a3d10b661f78.mp4\"}',60,'ASSIGNED',NULL,NULL,'normal','free',NULL,NULL,0.00,0.00,0.00,'company',0,NULL,0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'UNPAID_TO_PLUMBER',NULL,NULL,'web_form',NULL,'2026-06-25 17:04:53',NULL,NULL,NULL,'2026-06-25 11:27:50','2026-06-25 11:34:53');
/*!40000 ALTER TABLE `complaints` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `districts`
--

DROP TABLE IF EXISTS `districts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `districts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `state` varchar(100) NOT NULL,
  `district_name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_state_district` (`state`,`district_name`),
  KEY `idx_state` (`state`)
) ENGINE=InnoDB AUTO_INCREMENT=803 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `districts`
--

LOCK TABLES `districts` WRITE;
/*!40000 ALTER TABLE `districts` DISABLE KEYS */;
INSERT INTO `districts` VALUES (1,'Gujarat','Ahmedabad',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(2,'Gujarat','Amreli',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(3,'Gujarat','Anand',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(4,'Gujarat','Aravalli',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(5,'Gujarat','Banaskantha',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(6,'Gujarat','Bharuch',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(7,'Gujarat','Bhavnagar',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(8,'Gujarat','Botad',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(9,'Gujarat','Chhota Udaipur',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(10,'Gujarat','Dahod',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(11,'Gujarat','Dang',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(12,'Gujarat','Devbhoomi Dwarka',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(13,'Gujarat','Gandhinagar',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(14,'Gujarat','Gir Somnath',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(15,'Gujarat','Jamnagar',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(16,'Gujarat','Junagadh',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(17,'Gujarat','Kheda',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(18,'Gujarat','Kutch',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(19,'Gujarat','Mahisagar',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(20,'Gujarat','Mehsana',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(21,'Gujarat','Morbi',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(22,'Gujarat','Narmada',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(23,'Gujarat','Navsari',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(24,'Gujarat','Panchmahal',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(25,'Gujarat','Patan',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(26,'Gujarat','Porbandar',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(27,'Gujarat','Rajkot',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(28,'Gujarat','Sabarkantha',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(29,'Gujarat','Surat',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(30,'Gujarat','Surendranagar',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(31,'Gujarat','Tapi',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(32,'Gujarat','Vadodara',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(33,'Gujarat','Valsad',1,'2026-05-01 05:26:09','2026-05-01 05:26:09'),(34,'Andhra Pradesh','Alluri Sitharama Raju',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(35,'Andhra Pradesh','Anakapalli',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(36,'Andhra Pradesh','Anantapur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(37,'Andhra Pradesh','Annamayya',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(38,'Andhra Pradesh','Bapatla',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(39,'Andhra Pradesh','Chittoor',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(40,'Andhra Pradesh','East Godavari',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(41,'Andhra Pradesh','Eluru',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(42,'Andhra Pradesh','Guntur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(43,'Andhra Pradesh','Kakinada',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(44,'Andhra Pradesh','Konaseema',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(45,'Andhra Pradesh','Krishna',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(46,'Andhra Pradesh','Kurnool',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(47,'Andhra Pradesh','Nandyal',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(48,'Andhra Pradesh','NTR',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(49,'Andhra Pradesh','Palnadu',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(50,'Andhra Pradesh','Parvathipuram Manyam',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(51,'Andhra Pradesh','Prakasam',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(52,'Andhra Pradesh','Sri Potti Sriramulu Nellore',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(53,'Andhra Pradesh','Sri Sathya Sai',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(54,'Andhra Pradesh','Srikakulam',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(55,'Andhra Pradesh','Tirupati',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(56,'Andhra Pradesh','Visakhapatnam',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(57,'Andhra Pradesh','Vizianagaram',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(58,'Andhra Pradesh','West Godavari',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(59,'Andhra Pradesh','YSR Kadapa',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(60,'Arunachal Pradesh','Anjaw',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(61,'Arunachal Pradesh','Changlang',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(62,'Arunachal Pradesh','Dibang Valley',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(63,'Arunachal Pradesh','East Kameng',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(64,'Arunachal Pradesh','East Siang',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(65,'Arunachal Pradesh','Kamle',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(66,'Arunachal Pradesh','Kra Daadi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(67,'Arunachal Pradesh','Kurung Kumey',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(68,'Arunachal Pradesh','Lepa Rada',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(69,'Arunachal Pradesh','Lohit',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(70,'Arunachal Pradesh','Longding',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(71,'Arunachal Pradesh','Lower Dibang Valley',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(72,'Arunachal Pradesh','Lower Siang',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(73,'Arunachal Pradesh','Lower Subansiri',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(74,'Arunachal Pradesh','Namsai',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(75,'Arunachal Pradesh','Pakke-Kessang',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(76,'Arunachal Pradesh','Papum Pare',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(77,'Arunachal Pradesh','Shi Yomi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(78,'Arunachal Pradesh','Siang',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(79,'Arunachal Pradesh','Tawang',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(80,'Arunachal Pradesh','Tirap',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(81,'Arunachal Pradesh','Upper Siang',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(82,'Arunachal Pradesh','Upper Subansiri',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(83,'Arunachal Pradesh','West Kameng',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(84,'Arunachal Pradesh','West Siang',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(85,'Arunachal Pradesh','Itanagar Capital Complex',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(86,'Assam','Bajali',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(87,'Assam','Baksa',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(88,'Assam','Barpeta',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(89,'Assam','Biswanath',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(90,'Assam','Bongaigaon',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(91,'Assam','Cachar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(92,'Assam','Charaideo',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(93,'Assam','Chirang',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(94,'Assam','Darrang',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(95,'Assam','Dhemaji',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(96,'Assam','Dhubri',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(97,'Assam','Dibrugarh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(98,'Assam','Dima Hasao',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(99,'Assam','Goalpara',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(100,'Assam','Golaghat',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(101,'Assam','Hailakandi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(102,'Assam','Hojai',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(103,'Assam','Jorhat',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(104,'Assam','Kamrup',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(105,'Assam','Kamrup Metropolitan',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(106,'Assam','Karbi Anglong',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(107,'Assam','Karimganj',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(108,'Assam','Kokrajhar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(109,'Assam','Lakhimpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(110,'Assam','Majuli',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(111,'Assam','Morigaon',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(112,'Assam','Nagaon',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(113,'Assam','Nalbari',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(114,'Assam','Sivasagar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(115,'Assam','Sonitpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(116,'Assam','South Salmara-Mankachar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(117,'Assam','Tamulpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(118,'Assam','Tinsukia',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(119,'Assam','Udalguri',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(120,'Assam','West Karbi Anglong',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(121,'Bihar','Araria',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(122,'Bihar','Arwal',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(123,'Bihar','Aurangabad',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(124,'Bihar','Banka',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(125,'Bihar','Begusarai',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(126,'Bihar','Bhagalpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(127,'Bihar','Bhojpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(128,'Bihar','Buxar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(129,'Bihar','Darbhanga',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(130,'Bihar','East Champaran',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(131,'Bihar','Gaya',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(132,'Bihar','Gopalganj',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(133,'Bihar','Jamui',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(134,'Bihar','Jehanabad',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(135,'Bihar','Kaimur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(136,'Bihar','Katihar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(137,'Bihar','Khagaria',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(138,'Bihar','Kishanganj',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(139,'Bihar','Lakhisarai',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(140,'Bihar','Madhepura',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(141,'Bihar','Madhubani',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(142,'Bihar','Munger',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(143,'Bihar','Muzaffarpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(144,'Bihar','Nalanda',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(145,'Bihar','Nawada',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(146,'Bihar','Patna',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(147,'Bihar','Purnia',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(148,'Bihar','Rohtas',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(149,'Bihar','Saharsa',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(150,'Bihar','Samastipur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(151,'Bihar','Saran',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(152,'Bihar','Sheikhpura',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(153,'Bihar','Sheohar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(154,'Bihar','Sitamarhi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(155,'Bihar','Siwan',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(156,'Bihar','Supaul',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(157,'Bihar','Vaishali',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(158,'Bihar','West Champaran',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(159,'Chhattisgarh','Balod',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(160,'Chhattisgarh','Baloda Bazar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(161,'Chhattisgarh','Balrampur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(162,'Chhattisgarh','Bastar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(163,'Chhattisgarh','Bemetara',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(164,'Chhattisgarh','Bijapur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(165,'Chhattisgarh','Bilaspur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(166,'Chhattisgarh','Dakshin Bastar Dantewada',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(167,'Chhattisgarh','Dhamtari',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(168,'Chhattisgarh','Durg',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(169,'Chhattisgarh','Gariaband',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(170,'Chhattisgarh','Gaurela-Pendra-Marwahi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(171,'Chhattisgarh','Janjgir-Champa',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(172,'Chhattisgarh','Jashpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(173,'Chhattisgarh','Kabirdham',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(174,'Chhattisgarh','Kanker',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(175,'Chhattisgarh','Khairagarh-Chhuikhadan-Gandai',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(176,'Chhattisgarh','Kondagaon',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(177,'Chhattisgarh','Korba',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(178,'Chhattisgarh','Koriya',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(179,'Chhattisgarh','Mahasamund',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(180,'Chhattisgarh','Manendragarh-Chirmiri-Bharatpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(181,'Chhattisgarh','Mohla-Manpur-Ambagarh Chowki',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(182,'Chhattisgarh','Mungeli',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(183,'Chhattisgarh','Narayanpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(184,'Chhattisgarh','Raigarh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(185,'Chhattisgarh','Raipur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(186,'Chhattisgarh','Rajnandgaon',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(187,'Chhattisgarh','Sakti',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(188,'Chhattisgarh','Sarangarh-Bilaigarh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(189,'Chhattisgarh','Sukma',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(190,'Chhattisgarh','Surajpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(191,'Chhattisgarh','Surguja',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(192,'Goa','North Goa',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(193,'Goa','South Goa',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(227,'Haryana','Ambala',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(228,'Haryana','Bhiwani',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(229,'Haryana','Charkhi Dadri',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(230,'Haryana','Faridabad',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(231,'Haryana','Fatehabad',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(232,'Haryana','Gurugram',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(233,'Haryana','Hisar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(234,'Haryana','Jhajjar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(235,'Haryana','Jind',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(236,'Haryana','Kaithal',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(237,'Haryana','Karnal',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(238,'Haryana','Kurukshetra',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(239,'Haryana','Mahendragarh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(240,'Haryana','Nuh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(241,'Haryana','Palwal',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(242,'Haryana','Panchkula',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(243,'Haryana','Panipat',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(244,'Haryana','Rewari',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(245,'Haryana','Rohtak',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(246,'Haryana','Sirsa',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(247,'Haryana','Sonipat',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(248,'Haryana','Yamunanagar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(249,'Himachal Pradesh','Bilaspur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(250,'Himachal Pradesh','Chamba',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(251,'Himachal Pradesh','Hamirpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(252,'Himachal Pradesh','Kangra',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(253,'Himachal Pradesh','Kinnaur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(254,'Himachal Pradesh','Kullu',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(255,'Himachal Pradesh','Lahaul and Spiti',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(256,'Himachal Pradesh','Mandi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(257,'Himachal Pradesh','Shimla',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(258,'Himachal Pradesh','Sirmaur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(259,'Himachal Pradesh','Solan',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(260,'Himachal Pradesh','Una',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(261,'Jharkhand','Bokaro',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(262,'Jharkhand','Chatra',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(263,'Jharkhand','Deoghar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(264,'Jharkhand','Dhanbad',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(265,'Jharkhand','Dumka',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(266,'Jharkhand','East Singhbhum',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(267,'Jharkhand','Garhwa',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(268,'Jharkhand','Giridih',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(269,'Jharkhand','Godda',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(270,'Jharkhand','Gumla',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(271,'Jharkhand','Hazaribagh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(272,'Jharkhand','Jamtara',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(273,'Jharkhand','Khunti',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(274,'Jharkhand','Koderma',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(275,'Jharkhand','Latehar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(276,'Jharkhand','Lohardaga',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(277,'Jharkhand','Pakur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(278,'Jharkhand','Palamu',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(279,'Jharkhand','Ramgarh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(280,'Jharkhand','Ranchi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(281,'Jharkhand','Sahebganj',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(282,'Jharkhand','Seraikela Kharsawan',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(283,'Jharkhand','Simdega',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(284,'Jharkhand','West Singhbhum',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(285,'Karnataka','Bagalkot',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(286,'Karnataka','Ballari',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(287,'Karnataka','Belagavi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(288,'Karnataka','Bengaluru Rural',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(289,'Karnataka','Bengaluru Urban',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(290,'Karnataka','Bidar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(291,'Karnataka','Chamarajanagar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(292,'Karnataka','Chikballapur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(293,'Karnataka','Chikkamagaluru',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(294,'Karnataka','Chitradurga',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(295,'Karnataka','Dakshina Kannada',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(296,'Karnataka','Davanagere',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(297,'Karnataka','Dharwad',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(298,'Karnataka','Gadag',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(299,'Karnataka','Hassan',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(300,'Karnataka','Haveri',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(301,'Karnataka','Kalaburagi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(302,'Karnataka','Kodagu',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(303,'Karnataka','Kolar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(304,'Karnataka','Koppal',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(305,'Karnataka','Mandya',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(306,'Karnataka','Mysuru',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(307,'Karnataka','Raichur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(308,'Karnataka','Ramanagara',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(309,'Karnataka','Shivamogga',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(310,'Karnataka','Tumakuru',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(311,'Karnataka','Udupi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(312,'Karnataka','Uttara Kannada',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(313,'Karnataka','Vijayanagara',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(314,'Karnataka','Vijayapura',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(315,'Karnataka','Yadgir',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(316,'Kerala','Alappuzha',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(317,'Kerala','Ernakulam',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(318,'Kerala','Idukki',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(319,'Kerala','Kannur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(320,'Kerala','Kasaragod',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(321,'Kerala','Kollam',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(322,'Kerala','Kottayam',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(323,'Kerala','Kozhikode',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(324,'Kerala','Malappuram',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(325,'Kerala','Palakkad',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(326,'Kerala','Pathanamthitta',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(327,'Kerala','Thiruvananthapuram',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(328,'Kerala','Thrissur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(329,'Kerala','Wayanad',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(330,'Madhya Pradesh','Agar Malwa',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(331,'Madhya Pradesh','Alirajpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(332,'Madhya Pradesh','Anuppur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(333,'Madhya Pradesh','Ashoknagar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(334,'Madhya Pradesh','Balaghat',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(335,'Madhya Pradesh','Barwani',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(336,'Madhya Pradesh','Betul',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(337,'Madhya Pradesh','Bhind',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(338,'Madhya Pradesh','Bhopal',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(339,'Madhya Pradesh','Burhanpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(340,'Madhya Pradesh','Chhatarpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(341,'Madhya Pradesh','Chhindwara',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(342,'Madhya Pradesh','Damoh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(343,'Madhya Pradesh','Datia',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(344,'Madhya Pradesh','Dewas',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(345,'Madhya Pradesh','Dhar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(346,'Madhya Pradesh','Dindori',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(347,'Madhya Pradesh','Guna',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(348,'Madhya Pradesh','Gwalior',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(349,'Madhya Pradesh','Harda',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(350,'Madhya Pradesh','Indore',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(351,'Madhya Pradesh','Jabalpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(352,'Madhya Pradesh','Jhabua',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(353,'Madhya Pradesh','Katni',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(354,'Madhya Pradesh','Khandwa',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(355,'Madhya Pradesh','Khargone',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(356,'Madhya Pradesh','Maihar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(357,'Madhya Pradesh','Mandla',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(358,'Madhya Pradesh','Mandsaur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(359,'Madhya Pradesh','Mauganj',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(360,'Madhya Pradesh','Morena',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(361,'Madhya Pradesh','Narmadapuram',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(362,'Madhya Pradesh','Narsinghpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(363,'Madhya Pradesh','Neemuch',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(364,'Madhya Pradesh','Niwari',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(365,'Madhya Pradesh','Pandhurna',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(366,'Madhya Pradesh','Panna',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(367,'Madhya Pradesh','Raisen',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(368,'Madhya Pradesh','Rajgarh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(369,'Madhya Pradesh','Ratlam',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(370,'Madhya Pradesh','Rewa',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(371,'Madhya Pradesh','Sagar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(372,'Madhya Pradesh','Satna',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(373,'Madhya Pradesh','Sehore',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(374,'Madhya Pradesh','Seoni',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(375,'Madhya Pradesh','Shahdol',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(376,'Madhya Pradesh','Shajapur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(377,'Madhya Pradesh','Sheopur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(378,'Madhya Pradesh','Shivpuri',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(379,'Madhya Pradesh','Sidhi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(380,'Madhya Pradesh','Singrauli',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(381,'Madhya Pradesh','Tikamgarh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(382,'Madhya Pradesh','Ujjain',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(383,'Madhya Pradesh','Umaria',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(384,'Madhya Pradesh','Vidisha',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(385,'Maharashtra','Ahmednagar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(386,'Maharashtra','Akola',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(387,'Maharashtra','Amravati',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(388,'Maharashtra','Chhatrapati Sambhajinagar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(389,'Maharashtra','Beed',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(390,'Maharashtra','Bhandara',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(391,'Maharashtra','Buldhana',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(392,'Maharashtra','Chandrapur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(393,'Maharashtra','Dhule',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(394,'Maharashtra','Dharashiv',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(395,'Maharashtra','Gadchiroli',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(396,'Maharashtra','Gondia',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(397,'Maharashtra','Hingoli',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(398,'Maharashtra','Jalgaon',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(399,'Maharashtra','Jalna',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(400,'Maharashtra','Kolhapur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(401,'Maharashtra','Latur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(402,'Maharashtra','Mumbai City',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(403,'Maharashtra','Mumbai Suburban',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(404,'Maharashtra','Nagpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(405,'Maharashtra','Nanded',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(406,'Maharashtra','Nandurbar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(407,'Maharashtra','Nashik',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(408,'Maharashtra','Palghar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(409,'Maharashtra','Parbhani',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(410,'Maharashtra','Pune',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(411,'Maharashtra','Raigad',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(412,'Maharashtra','Ratnagiri',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(413,'Maharashtra','Sangli',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(414,'Maharashtra','Satara',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(415,'Maharashtra','Sindhudurg',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(416,'Maharashtra','Solapur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(417,'Maharashtra','Thane',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(418,'Maharashtra','Wardha',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(419,'Maharashtra','Washim',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(420,'Maharashtra','Yavatmal',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(421,'Manipur','Bishnupur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(422,'Manipur','Chandel',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(423,'Manipur','Churachandpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(424,'Manipur','Imphal East',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(425,'Manipur','Imphal West',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(426,'Manipur','Jiribam',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(427,'Manipur','Kakching',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(428,'Manipur','Kamjong',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(429,'Manipur','Kangpokpi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(430,'Manipur','Noney',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(431,'Manipur','Pherzawl',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(432,'Manipur','Senapati',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(433,'Manipur','Tamenglong',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(434,'Manipur','Tengnoupal',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(435,'Manipur','Thoubal',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(436,'Manipur','Ukhrul',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(437,'Meghalaya','East Garo Hills',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(438,'Meghalaya','East Jaintia Hills',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(439,'Meghalaya','East Khasi Hills',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(440,'Meghalaya','Eastern West Khasi Hills',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(441,'Meghalaya','North Garo Hills',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(442,'Meghalaya','Ri Bhoi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(443,'Meghalaya','South Garo Hills',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(444,'Meghalaya','South West Garo Hills',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(445,'Meghalaya','South West Khasi Hills',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(446,'Meghalaya','West Garo Hills',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(447,'Meghalaya','West Jaintia Hills',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(448,'Meghalaya','West Khasi Hills',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(449,'Mizoram','Aizawl',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(450,'Mizoram','Champhai',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(451,'Mizoram','Hnahthial',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(452,'Mizoram','Khawzawl',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(453,'Mizoram','Kolasib',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(454,'Mizoram','Lawngtlai',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(455,'Mizoram','Lunglei',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(456,'Mizoram','Mamit',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(457,'Mizoram','Saiha',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(458,'Mizoram','Saitual',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(459,'Mizoram','Serchhip',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(460,'Nagaland','Chumoukedima',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(461,'Nagaland','Dimapur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(462,'Nagaland','Kiphire',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(463,'Nagaland','Kohima',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(464,'Nagaland','Longleng',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(465,'Nagaland','Mokokchung',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(466,'Nagaland','Mon',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(467,'Nagaland','Niuland',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(468,'Nagaland','Noklak',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(469,'Nagaland','Peren',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(470,'Nagaland','Phek',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(471,'Nagaland','Shamator',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(472,'Nagaland','Tseminyu',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(473,'Nagaland','Tuensang',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(474,'Nagaland','Wokha',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(475,'Nagaland','Zunheboto',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(476,'Odisha','Angul',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(477,'Odisha','Balangir',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(478,'Odisha','Balasore',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(479,'Odisha','Bargarh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(480,'Odisha','Bhadrak',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(481,'Odisha','Boudh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(482,'Odisha','Cuttack',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(483,'Odisha','Deogarh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(484,'Odisha','Dhenkanal',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(485,'Odisha','Gajapati',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(486,'Odisha','Ganjam',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(487,'Odisha','Jagatsinghpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(488,'Odisha','Jajpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(489,'Odisha','Jharsuguda',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(490,'Odisha','Kalahandi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(491,'Odisha','Kandhamal',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(492,'Odisha','Kendrapara',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(493,'Odisha','Kendujhar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(494,'Odisha','Khordha',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(495,'Odisha','Koraput',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(496,'Odisha','Malkangiri',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(497,'Odisha','Mayurbhanj',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(498,'Odisha','Nabarangpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(499,'Odisha','Nayagarh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(500,'Odisha','Nuapada',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(501,'Odisha','Puri',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(502,'Odisha','Rayagada',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(503,'Odisha','Sambalpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(504,'Odisha','Subarnapur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(505,'Odisha','Sundargarh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(506,'Punjab','Amritsar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(507,'Punjab','Barnala',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(508,'Punjab','Bathinda',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(509,'Punjab','Faridkot',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(510,'Punjab','Fatehgarh Sahib',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(511,'Punjab','Fazilka',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(512,'Punjab','Ferozepur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(513,'Punjab','Gurdaspur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(514,'Punjab','Hoshiarpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(515,'Punjab','Jalandhar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(516,'Punjab','Kapurthala',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(517,'Punjab','Ludhiana',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(518,'Punjab','Malerkotla',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(519,'Punjab','Mansa',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(520,'Punjab','Moga',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(521,'Punjab','Sahibzada Ajit Singh Nagar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(522,'Punjab','Sri Muktsar Sahib',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(523,'Punjab','Pathankot',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(524,'Punjab','Patiala',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(525,'Punjab','Rupnagar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(526,'Punjab','Sangrur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(527,'Punjab','Shaheed Bhagat Singh Nagar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(528,'Punjab','Tarn Taran',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(529,'Rajasthan','Ajmer',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(530,'Rajasthan','Alwar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(531,'Rajasthan','Banswara',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(532,'Rajasthan','Baran',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(533,'Rajasthan','Barmer',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(534,'Rajasthan','Bharatpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(535,'Rajasthan','Bhilwara',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(536,'Rajasthan','Bikaner',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(537,'Rajasthan','Bundi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(538,'Rajasthan','Chittorgarh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(539,'Rajasthan','Churu',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(540,'Rajasthan','Dausa',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(541,'Rajasthan','Dholpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(542,'Rajasthan','Dungarpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(543,'Rajasthan','Hanumangarh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(544,'Rajasthan','Jaipur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(545,'Rajasthan','Jaisalmer',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(546,'Rajasthan','Jalore',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(547,'Rajasthan','Jhalawar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(548,'Rajasthan','Jhunjhunu',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(549,'Rajasthan','Jodhpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(550,'Rajasthan','Karauli',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(551,'Rajasthan','Kota',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(552,'Rajasthan','Nagaur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(553,'Rajasthan','Pali',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(554,'Rajasthan','Pratapgarh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(555,'Rajasthan','Rajsamand',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(556,'Rajasthan','Sawai Madhopur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(557,'Rajasthan','Sikar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(558,'Rajasthan','Sirohi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(559,'Rajasthan','Sri Ganganagar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(560,'Rajasthan','Tonk',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(561,'Rajasthan','Udaipur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(562,'Sikkim','Gangtok',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(563,'Sikkim','Geyzing',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(564,'Sikkim','Mangan',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(565,'Sikkim','Namchi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(566,'Sikkim','Pakyong',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(567,'Sikkim','Soreng',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(568,'Tamil Nadu','Ariyalur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(569,'Tamil Nadu','Chengalpattu',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(570,'Tamil Nadu','Chennai',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(571,'Tamil Nadu','Coimbatore',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(572,'Tamil Nadu','Cuddalore',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(573,'Tamil Nadu','Dharmapuri',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(574,'Tamil Nadu','Dindigul',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(575,'Tamil Nadu','Erode',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(576,'Tamil Nadu','Kallakurichi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(577,'Tamil Nadu','Kanchipuram',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(578,'Tamil Nadu','Kanyakumari',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(579,'Tamil Nadu','Karur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(580,'Tamil Nadu','Krishnagiri',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(581,'Tamil Nadu','Madurai',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(582,'Tamil Nadu','Mayiladuthurai',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(583,'Tamil Nadu','Nagapattinam',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(584,'Tamil Nadu','Namakkal',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(585,'Tamil Nadu','Nilgiris',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(586,'Tamil Nadu','Perambalur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(587,'Tamil Nadu','Pudukkottai',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(588,'Tamil Nadu','Ramanathapuram',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(589,'Tamil Nadu','Ranipet',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(590,'Tamil Nadu','Salem',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(591,'Tamil Nadu','Sivaganga',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(592,'Tamil Nadu','Tenkasi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(593,'Tamil Nadu','Thanjavur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(594,'Tamil Nadu','Theni',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(595,'Tamil Nadu','Thoothukudi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(596,'Tamil Nadu','Tiruchirappalli',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(597,'Tamil Nadu','Tirunelveli',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(598,'Tamil Nadu','Tirupathur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(599,'Tamil Nadu','Tiruppur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(600,'Tamil Nadu','Tiruvallur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(601,'Tamil Nadu','Tiruvannamalai',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(602,'Tamil Nadu','Tiruvarur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(603,'Tamil Nadu','Vellore',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(604,'Tamil Nadu','Viluppuram',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(605,'Tamil Nadu','Virudhunagar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(606,'Telangana','Adilabad',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(607,'Telangana','Bhadradri Kothagudem',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(608,'Telangana','Hanumakonda',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(609,'Telangana','Hyderabad',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(610,'Telangana','Jagtial',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(611,'Telangana','Jangaon',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(612,'Telangana','Jayashankar Bhupalpally',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(613,'Telangana','Jogulamba Gadwal',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(614,'Telangana','Kamareddy',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(615,'Telangana','Karimnagar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(616,'Telangana','Khammam',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(617,'Telangana','Komaram Bheem Asifabad',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(618,'Telangana','Mahabubabad',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(619,'Telangana','Mahabubnagar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(620,'Telangana','Mancherial',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(621,'Telangana','Medak',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(622,'Telangana','Medchal-Malkajgiri',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(623,'Telangana','Mulugu',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(624,'Telangana','Nagarkurnool',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(625,'Telangana','Nalgonda',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(626,'Telangana','Narayanpet',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(627,'Telangana','Nirmal',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(628,'Telangana','Nizamabad',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(629,'Telangana','Peddapalli',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(630,'Telangana','Rajanna Sircilla',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(631,'Telangana','Rangareddy',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(632,'Telangana','Sangareddy',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(633,'Telangana','Siddipet',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(634,'Telangana','Suryapet',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(635,'Telangana','Vikarabad',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(636,'Telangana','Wanaparthy',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(637,'Telangana','Warangal',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(638,'Telangana','Yadadri Bhuvanagiri',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(639,'Tripura','Dhalai',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(640,'Tripura','Gomati',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(641,'Tripura','Khowai',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(642,'Tripura','North Tripura',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(643,'Tripura','Sepahijala',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(644,'Tripura','South Tripura',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(645,'Tripura','Unakoti',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(646,'Tripura','West Tripura',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(647,'Uttar Pradesh','Agra',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(648,'Uttar Pradesh','Aligarh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(649,'Uttar Pradesh','Ambedkar Nagar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(650,'Uttar Pradesh','Amethi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(651,'Uttar Pradesh','Amroha',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(652,'Uttar Pradesh','Auraiya',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(653,'Uttar Pradesh','Ayodhya',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(654,'Uttar Pradesh','Azamgarh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(655,'Uttar Pradesh','Baghpat',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(656,'Uttar Pradesh','Bahraich',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(657,'Uttar Pradesh','Ballia',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(658,'Uttar Pradesh','Balrampur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(659,'Uttar Pradesh','Banda',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(660,'Uttar Pradesh','Barabanki',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(661,'Uttar Pradesh','Bareilly',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(662,'Uttar Pradesh','Basti',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(663,'Uttar Pradesh','Bhadohi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(664,'Uttar Pradesh','Bijnor',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(665,'Uttar Pradesh','Budaun',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(666,'Uttar Pradesh','Bulandshahr',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(667,'Uttar Pradesh','Chandauli',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(668,'Uttar Pradesh','Chitrakoot',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(669,'Uttar Pradesh','Deoria',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(670,'Uttar Pradesh','Etah',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(671,'Uttar Pradesh','Etawah',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(672,'Uttar Pradesh','Farrukhabad',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(673,'Uttar Pradesh','Fatehpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(674,'Uttar Pradesh','Firozabad',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(675,'Uttar Pradesh','Gautam Buddha Nagar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(676,'Uttar Pradesh','Ghaziabad',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(677,'Uttar Pradesh','Ghazipur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(678,'Uttar Pradesh','Gonda',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(679,'Uttar Pradesh','Gorakhpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(680,'Uttar Pradesh','Hamirpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(681,'Uttar Pradesh','Hapur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(682,'Uttar Pradesh','Hardoi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(683,'Uttar Pradesh','Hathras',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(684,'Uttar Pradesh','Jalaun',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(685,'Uttar Pradesh','Jaunpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(686,'Uttar Pradesh','Jhansi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(687,'Uttar Pradesh','Kannauj',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(688,'Uttar Pradesh','Kanpur Dehat',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(689,'Uttar Pradesh','Kanpur Nagar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(690,'Uttar Pradesh','Kasganj',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(691,'Uttar Pradesh','Kaushambi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(692,'Uttar Pradesh','Kheri',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(693,'Uttar Pradesh','Kushinagar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(694,'Uttar Pradesh','Lalitpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(695,'Uttar Pradesh','Lucknow',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(696,'Uttar Pradesh','Maharajganj',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(697,'Uttar Pradesh','Mahoba',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(698,'Uttar Pradesh','Mainpuri',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(699,'Uttar Pradesh','Mathura',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(700,'Uttar Pradesh','Mau',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(701,'Uttar Pradesh','Meerut',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(702,'Uttar Pradesh','Mirzapur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(703,'Uttar Pradesh','Moradabad',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(704,'Uttar Pradesh','Muzaffarnagar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(705,'Uttar Pradesh','Pilibhit',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(706,'Uttar Pradesh','Pratapgarh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(707,'Uttar Pradesh','Prayagraj',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(708,'Uttar Pradesh','Raebareli',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(709,'Uttar Pradesh','Rampur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(710,'Uttar Pradesh','Saharanpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(711,'Uttar Pradesh','Sambhal',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(712,'Uttar Pradesh','Sant Kabir Nagar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(713,'Uttar Pradesh','Shahjahanpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(714,'Uttar Pradesh','Shamli',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(715,'Uttar Pradesh','Shravasti',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(716,'Uttar Pradesh','Siddharthnagar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(717,'Uttar Pradesh','Sitapur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(718,'Uttar Pradesh','Sonbhadra',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(719,'Uttar Pradesh','Sultanpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(720,'Uttar Pradesh','Unnao',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(721,'Uttar Pradesh','Varanasi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(722,'Uttarakhand','Almora',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(723,'Uttarakhand','Bageshwar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(724,'Uttarakhand','Chamoli',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(725,'Uttarakhand','Champawat',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(726,'Uttarakhand','Dehradun',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(727,'Uttarakhand','Haridwar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(728,'Uttarakhand','Nainital',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(729,'Uttarakhand','Pauri Garhwal',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(730,'Uttarakhand','Pithoragarh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(731,'Uttarakhand','Rudraprayag',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(732,'Uttarakhand','Tehri Garhwal',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(733,'Uttarakhand','Udham Singh Nagar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(734,'Uttarakhand','Uttarkashi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(735,'West Bengal','Alipurduar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(736,'West Bengal','Bankura',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(737,'West Bengal','Birbhum',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(738,'West Bengal','Cooch Behar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(739,'West Bengal','Dakshin Dinajpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(740,'West Bengal','Darjeeling',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(741,'West Bengal','Hooghly',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(742,'West Bengal','Howrah',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(743,'West Bengal','Jalpaiguri',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(744,'West Bengal','Jhargram',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(745,'West Bengal','Kalimpong',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(746,'West Bengal','Kolkata',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(747,'West Bengal','Malda',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(748,'West Bengal','Murshidabad',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(749,'West Bengal','Nadia',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(750,'West Bengal','North 24 Parganas',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(751,'West Bengal','Paschim Bardhaman',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(752,'West Bengal','Paschim Medinipur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(753,'West Bengal','Purba Bardhaman',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(754,'West Bengal','Purba Medinipur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(755,'West Bengal','Purulia',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(756,'West Bengal','South 24 Parganas',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(757,'West Bengal','Uttar Dinajpur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(758,'Andaman and Nicobar Islands','Nicobar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(759,'Andaman and Nicobar Islands','North and Middle Andaman',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(760,'Andaman and Nicobar Islands','South Andaman',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(761,'Chandigarh','Chandigarh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(762,'Dadra and Nagar Haveli and Daman and Diu','Dadra and Nagar Haveli',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(763,'Dadra and Nagar Haveli and Daman and Diu','Daman',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(764,'Dadra and Nagar Haveli and Daman and Diu','Diu',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(765,'Delhi','Central Delhi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(766,'Delhi','East Delhi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(767,'Delhi','New Delhi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(768,'Delhi','North Delhi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(769,'Delhi','North East Delhi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(770,'Delhi','North West Delhi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(771,'Delhi','Shahdara',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(772,'Delhi','South Delhi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(773,'Delhi','South East Delhi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(774,'Delhi','South West Delhi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(775,'Delhi','West Delhi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(776,'Jammu and Kashmir','Anantnag',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(777,'Jammu and Kashmir','Bandipora',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(778,'Jammu and Kashmir','Baramulla',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(779,'Jammu and Kashmir','Budgam',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(780,'Jammu and Kashmir','Doda',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(781,'Jammu and Kashmir','Ganderbal',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(782,'Jammu and Kashmir','Jammu',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(783,'Jammu and Kashmir','Kathua',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(784,'Jammu and Kashmir','Kishtwar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(785,'Jammu and Kashmir','Kulgam',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(786,'Jammu and Kashmir','Kupwara',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(787,'Jammu and Kashmir','Poonch',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(788,'Jammu and Kashmir','Pulwama',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(789,'Jammu and Kashmir','Rajouri',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(790,'Jammu and Kashmir','Ramban',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(791,'Jammu and Kashmir','Reasi',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(792,'Jammu and Kashmir','Samba',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(793,'Jammu and Kashmir','Shopian',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(794,'Jammu and Kashmir','Srinagar',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(795,'Jammu and Kashmir','Udhampur',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(796,'Ladakh','Kargil',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(797,'Ladakh','Leh',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(798,'Lakshadweep','Lakshadweep',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(799,'Puducherry','Karaikal',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(800,'Puducherry','Mahe',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(801,'Puducherry','Puducherry',1,'2026-05-01 07:48:24','2026-05-01 07:48:24'),(802,'Puducherry','Yanam',1,'2026-05-01 07:48:24','2026-05-01 07:48:24');
/*!40000 ALTER TABLE `districts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `frames`
--

DROP TABLE IF EXISTS `frames`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `frames` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `transparent_x` int(11) DEFAULT 0,
  `transparent_y` int(11) DEFAULT 0,
  `transparent_width` int(11) DEFAULT 1080,
  `transparent_height` int(11) DEFAULT 1080,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `frames`
--

LOCK TABLES `frames` WRITE;
/*!40000 ALTER TABLE `frames` DISABLE KEYS */;
INSERT INTO `frames` VALUES (1,'Happy Customer','frame_6933bc960ec71_1764998294.png',342,319,346,348,'2025-12-06 05:18:14'),(2,'Happy Birthday','frame_6933c19734129_1764999575.png',370,387,282,278,'2025-12-06 05:39:35');
/*!40000 ALTER TABLE `frames` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` enum('business','field_staff','service') NOT NULL DEFAULT 'service',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (1,'Plumber','2025-12-31 13:54:52','service'),(2,'Dealer','2025-12-31 13:54:52','business'),(3,'Distributor','2025-12-31 13:54:52','business'),(4,'Customers','2025-12-31 13:54:52','service'),(5,'Visitors','2025-12-31 13:54:52','service'),(6,'Unknown Contacts','2025-12-31 13:54:52','service'),(7,'Salesman','2025-12-31 13:54:52','field_staff'),(9,'Retailer','2026-02-24 13:58:04','business'),(11,'Builder','2026-04-20 10:39:48','business'),(12,'Architect','2026-04-20 10:39:48','business');
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lead_followup_notes`
--

DROP TABLE IF EXISTS `lead_followup_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lead_followup_notes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` int(10) unsigned NOT NULL,
  `visit_id` int(10) unsigned DEFAULT NULL,
  `added_by` int(10) unsigned NOT NULL,
  `added_by_type` enum('TSM','Admin') DEFAULT 'TSM',
  `notes` text NOT NULL,
  `next_followup_date` date DEFAULT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_lead` (`lead_id`),
  KEY `idx_added_by` (`added_by`,`added_by_type`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lead_followup_notes`
--

LOCK TABLES `lead_followup_notes` WRITE;
/*!40000 ALTER TABLE `lead_followup_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `lead_followup_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leads`
--

DROP TABLE IF EXISTS `leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lead_number` varchar(20) DEFAULT NULL,
  `collected_by` int(10) unsigned NOT NULL,
  `tsm_name` varchar(100) DEFAULT NULL,
  `territory` varchar(100) DEFAULT NULL,
  `visit_id` int(10) unsigned DEFAULT NULL,
  `collected_date` date NOT NULL,
  `firm_name` varchar(255) NOT NULL,
  `firm_type` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `ordered_by` varchar(100) DEFAULT NULL,
  `mobile` varchar(20) NOT NULL,
  `alternate_mobile` varchar(20) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `area_location` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `outlet_type` enum('Retail','Wholesale','Both') NOT NULL DEFAULT 'Retail',
  `years_in_business` varchar(20) DEFAULT NULL,
  `current_brands` text DEFAULT NULL,
  `monthly_purchase_value` decimal(12,2) DEFAULT NULL,
  `has_gst` enum('Yes','No') DEFAULT 'No',
  `gst_number` varchar(20) DEFAULT NULL,
  `msme_udyam` varchar(50) DEFAULT NULL,
  `interested_in_eleganza` enum('Yes','No','Maybe') DEFAULT 'Maybe',
  `product_category_interest` varchar(255) DEFAULT NULL,
  `display_space_available` enum('Yes','No') DEFAULT 'No',
  `potential_rating` enum('High','Medium','Low') DEFAULT 'Medium',
  `remarks` text DEFAULT NULL,
  `next_followup_date` date DEFAULT NULL,
  `extra_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`extra_fields`)),
  `status` enum('New','Follow_Up','Interested','Not_Interested','Converted') DEFAULT 'New',
  `assigned_to` int(10) unsigned DEFAULT NULL,
  `converted_audience_id` int(10) unsigned DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `bank_name` varchar(255) DEFAULT NULL,
  `ifsc_code` varchar(20) DEFAULT NULL,
  `account_holder` varchar(255) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `pan` varchar(10) DEFAULT NULL,
  `interested_as` enum('Dealer','Distributor','Retailer') DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lead_number` (`lead_number`),
  KEY `idx_collected_by` (`collected_by`),
  KEY `idx_status` (`status`),
  KEY `idx_date` (`collected_date`),
  KEY `idx_city` (`city`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leads`
--

LOCK TABLES `leads` WRITE;
/*!40000 ALTER TABLE `leads` DISABLE KEYS */;
INSERT INTO `leads` VALUES (2,'LEAD-2026-0001',99,NULL,NULL,NULL,'2026-06-08','Bright Sanitaryware',NULL,'Pradeep',NULL,NULL,'919000000001',NULL,NULL,NULL,NULL,NULL,'Surat','Gujarat',NULL,NULL,NULL,NULL,'Retail',NULL,NULL,NULL,'No',NULL,NULL,'Maybe',NULL,'No','Medium','Dummy test lead',NULL,NULL,'New',NULL,NULL,NULL,'2026-06-08 08:00:25','2026-06-08 08:00:25',NULL,NULL,NULL,NULL,NULL,'Dealer'),(3,'LEAD-2026-0003',99,NULL,NULL,NULL,'2026-06-08','Modern Bath Studio',NULL,'Anil',NULL,NULL,'919000000002',NULL,NULL,NULL,NULL,NULL,'Ahmedabad','Gujarat',NULL,NULL,NULL,NULL,'Retail',NULL,NULL,NULL,'No',NULL,NULL,'Maybe',NULL,'No','Medium','Dummy test lead','2026-06-11',NULL,'Interested',NULL,NULL,NULL,'2026-06-08 08:00:25','2026-06-08 08:00:25',NULL,NULL,NULL,NULL,NULL,'Retailer'),(4,'LEAD-2026-0004',99,NULL,NULL,NULL,'2026-06-08','Royal Builders',NULL,'Sandeep',NULL,NULL,'919000000003',NULL,NULL,NULL,NULL,NULL,'Pune','Maharashtra',NULL,NULL,NULL,NULL,'Retail',NULL,NULL,NULL,'No',NULL,NULL,'Maybe',NULL,'No','Medium','Dummy test lead','2026-06-11',NULL,'Follow_Up',NULL,NULL,NULL,'2026-06-08 08:00:25','2026-06-08 08:00:25',NULL,NULL,NULL,NULL,NULL,NULL),(5,'LEAD-2026-0005',99,NULL,NULL,NULL,'2026-06-08','Elite Architects',NULL,'Manoj',NULL,NULL,'919000000004',NULL,NULL,NULL,NULL,NULL,'Mumbai','Maharashtra',NULL,NULL,NULL,NULL,'Retail',NULL,NULL,NULL,'No',NULL,NULL,'Maybe',NULL,'No','Medium','Dummy test lead','2026-06-11',NULL,'Interested',NULL,NULL,NULL,'2026-06-08 08:00:25','2026-06-08 08:00:25',NULL,NULL,NULL,NULL,NULL,NULL),(6,'LEAD-2026-0006',99,NULL,NULL,NULL,'2026-06-08','Krishna Distribution',NULL,'Bhavesh',NULL,NULL,'919000000005',NULL,NULL,NULL,NULL,NULL,'Vadodara','Gujarat',NULL,NULL,NULL,NULL,'Retail',NULL,NULL,NULL,'No',NULL,NULL,'Maybe',NULL,'No','Medium','Dummy test lead',NULL,NULL,'New',NULL,NULL,NULL,'2026-06-08 08:00:25','2026-06-08 08:00:25',NULL,NULL,NULL,NULL,NULL,'Distributor');
/*!40000 ALTER TABLE `leads` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_lead_number` BEFORE INSERT ON `leads` FOR EACH ROW BEGIN DECLARE next_id INT; SELECT IFNULL(MAX(id),0)+1 INTO next_id FROM leads; SET NEW.lead_number = CONCAT('LEAD-',YEAR(NOW()),'-',LPAD(next_id,4,'0')); END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `marketing_activity_log`
--

DROP TABLE IF EXISTS `marketing_activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marketing_activity_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `audience_id` int(10) unsigned NOT NULL,
  `attendance_id` int(10) unsigned NOT NULL,
  `visit_id` int(10) unsigned DEFAULT NULL,
  `activity_type` enum('day_start','check_in','lead_collected','order_taken','quotation_shared','followup_note','check_out','day_end') NOT NULL,
  `reference_id` int(10) unsigned DEFAULT NULL,
  `reference_label` varchar(255) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `geo_address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_att` (`attendance_id`),
  KEY `idx_tsm_date` (`audience_id`,`created_at`),
  KEY `idx_visit` (`visit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marketing_activity_log`
--

LOCK TABLES `marketing_activity_log` WRITE;
/*!40000 ALTER TABLE `marketing_activity_log` DISABLE KEYS */;
INSERT INTO `marketing_activity_log` VALUES (1,112,1,NULL,'day_start',NULL,NULL,NULL,22.27513900,70.77899130,NULL,NULL,'2026-05-18 12:06:36'),(2,112,1,1,'check_in',115,'Arjun Architech',NULL,22.27513450,70.77898500,NULL,NULL,'2026-05-18 12:07:40'),(3,112,1,1,'order_taken',5,'ORD-2026-0005',136350.00,NULL,NULL,NULL,'Order placed on behalf of party','2026-05-18 12:10:23'),(4,112,1,1,'check_out',NULL,NULL,NULL,22.27503310,70.77886370,NULL,'take order','2026-05-18 12:10:48'),(5,99,2,NULL,'day_start',NULL,NULL,NULL,22.27503300,70.77886100,NULL,NULL,'2026-05-18 12:14:17'),(6,99,2,2,'check_in',115,'Arjun Architech',NULL,22.27513290,70.77898240,NULL,NULL,'2026-05-18 12:14:30'),(7,99,2,2,'order_taken',6,'ORD-2026-0006',12681.90,NULL,NULL,NULL,'Order placed on behalf of party','2026-05-18 12:14:49'),(8,112,3,NULL,'day_start',NULL,NULL,NULL,22.27513420,70.77899150,NULL,NULL,'2026-05-20 20:27:14'),(9,112,3,3,'check_in',115,'Arjun Architech',NULL,22.27511540,70.77897050,NULL,NULL,'2026-05-20 20:27:34'),(10,112,3,3,'order_taken',10,'ORD-2026-0009',300.00,NULL,NULL,NULL,'Order placed on behalf of party','2026-05-20 20:27:57'),(11,112,3,3,'check_out',NULL,NULL,NULL,22.27513110,70.77898300,NULL,'order done','2026-05-20 20:28:38'),(12,112,4,NULL,'day_start',NULL,NULL,NULL,22.27513910,70.77899050,NULL,NULL,'2026-06-02 17:32:59'),(13,99,5,NULL,'day_start',NULL,NULL,NULL,37.42199830,-122.08400000,NULL,NULL,'2026-06-05 18:18:42'),(14,99,5,4,'check_in',115,'Arjun Architech',NULL,37.42199830,-122.08400000,NULL,NULL,'2026-06-05 18:19:05'),(15,99,5,4,'lead_collected',1,'Aaa enterprice | aaa',NULL,37.42199830,-122.08400000,NULL,'High potential','2026-06-05 18:56:52'),(16,99,6,NULL,'day_start',NULL,NULL,NULL,37.42199830,-122.08400000,NULL,NULL,'2026-06-08 18:31:35'),(17,99,6,5,'check_in',118,'Shah Bath Mart',NULL,37.42199830,-122.08400000,NULL,NULL,'2026-06-08 18:32:01'),(18,99,6,5,'order_taken',12,'ORD-2026-0012',88200.00,NULL,NULL,NULL,'Order placed on behalf of party','2026-06-08 18:32:54'),(19,99,6,5,'check_out',NULL,NULL,NULL,37.42199830,-122.08400000,NULL,'take order no 0012','2026-06-08 18:34:11'),(20,99,6,NULL,'day_end',NULL,NULL,NULL,37.42199830,-122.08400000,NULL,NULL,'2026-06-08 18:57:18');
/*!40000 ALTER TABLE `marketing_activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marketing_attendance`
--

DROP TABLE IF EXISTS `marketing_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marketing_attendance` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `audience_id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `day_start_at` datetime DEFAULT NULL,
  `day_start_lat` decimal(10,8) DEFAULT NULL,
  `day_start_lng` decimal(11,8) DEFAULT NULL,
  `day_end_at` datetime DEFAULT NULL,
  `day_end_lat` decimal(10,8) DEFAULT NULL,
  `day_end_lng` decimal(11,8) DEFAULT NULL,
  `total_shops_visited` int(11) DEFAULT 0,
  `total_leads` int(11) DEFAULT 0,
  `total_orders` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_att` (`audience_id`,`date`),
  KEY `idx_date` (`date`),
  KEY `idx_audience` (`audience_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marketing_attendance`
--

LOCK TABLES `marketing_attendance` WRITE;
/*!40000 ALTER TABLE `marketing_attendance` DISABLE KEYS */;
INSERT INTO `marketing_attendance` VALUES (1,112,'2026-05-18','2026-05-18 12:06:36',22.27513900,70.77899130,NULL,NULL,NULL,1,0,1,NULL,'2026-05-18 06:36:36','2026-05-18 06:40:23'),(2,99,'2026-05-18','2026-05-18 12:14:17',22.27503300,70.77886100,NULL,NULL,NULL,1,0,1,NULL,'2026-05-18 06:44:17','2026-05-18 06:44:49'),(3,112,'2026-05-20','2026-05-20 20:27:14',22.27513420,70.77899150,NULL,NULL,NULL,1,0,1,NULL,'2026-05-20 14:57:14','2026-05-20 14:57:57'),(4,112,'2026-06-02','2026-06-02 17:32:59',22.27513910,70.77899050,NULL,NULL,NULL,0,0,0,NULL,'2026-06-02 12:02:59','2026-06-02 12:02:59'),(5,99,'2026-06-05','2026-06-05 18:18:42',37.42199830,-122.08400000,NULL,NULL,NULL,1,1,0,NULL,'2026-06-05 12:48:42','2026-06-05 13:26:52'),(6,99,'2026-06-08','2026-06-08 18:31:35',37.42199830,-122.08400000,'2026-06-08 18:57:18',37.42199830,-122.08400000,1,0,1,NULL,'2026-06-08 13:01:35','2026-06-08 13:27:18');
/*!40000 ALTER TABLE `marketing_attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marketing_visits`
--

DROP TABLE IF EXISTS `marketing_visits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marketing_visits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `audience_id` int(10) unsigned NOT NULL,
  `attendance_id` int(10) unsigned NOT NULL,
  `visit_type` enum('New_Lead','Existing_Party') NOT NULL DEFAULT 'Existing_Party',
  `lead_id` int(10) unsigned DEFAULT NULL,
  `party_id` int(10) unsigned DEFAULT NULL,
  `shop_name` varchar(255) DEFAULT NULL,
  `check_in_at` datetime NOT NULL,
  `check_in_lat` decimal(10,8) NOT NULL,
  `check_in_lng` decimal(11,8) NOT NULL,
  `check_in_address` text DEFAULT NULL,
  `check_in_photo` varchar(255) DEFAULT NULL,
  `check_out_at` datetime DEFAULT NULL,
  `check_out_lat` decimal(10,8) DEFAULT NULL,
  `check_out_lng` decimal(11,8) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `next_followup_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_att` (`attendance_id`),
  KEY `idx_tsm` (`audience_id`),
  KEY `idx_party` (`party_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marketing_visits`
--

LOCK TABLES `marketing_visits` WRITE;
/*!40000 ALTER TABLE `marketing_visits` DISABLE KEYS */;
INSERT INTO `marketing_visits` VALUES (1,112,1,'Existing_Party',NULL,115,'Arjun Architech','2026-05-18 12:07:40',22.27513450,70.77898500,NULL,'checkin_6a0ab3b48e452_1779086260.jpg','2026-05-18 12:10:48',22.27503310,70.77886370,'take order',NULL,'2026-05-18 06:37:40','2026-05-18 06:40:48'),(2,99,2,'Existing_Party',NULL,115,'Arjun Architech','2026-05-18 12:14:30',22.27513290,70.77898240,NULL,'checkin_6a0ab54e8e0e4_1779086670.jpg',NULL,NULL,NULL,NULL,NULL,'2026-05-18 06:44:30','2026-05-18 06:44:30'),(3,112,3,'Existing_Party',NULL,115,'Arjun Architech','2026-05-20 20:27:34',22.27511540,70.77897050,NULL,'checkin_6a0dcbde6bde5_1779289054.jpg','2026-05-20 20:28:38',22.27513110,70.77898300,'order done',NULL,'2026-05-20 14:57:34','2026-05-20 14:58:38'),(4,99,5,'Existing_Party',NULL,115,'Arjun Architech','2026-06-05 18:19:05',37.42199830,-122.08400000,NULL,'checkin_6a22c5c15008f_1780663745.jpg',NULL,NULL,NULL,NULL,NULL,'2026-06-05 12:49:05','2026-06-05 14:41:39'),(5,99,6,'Existing_Party',NULL,118,'Shah Bath Mart','2026-06-08 18:32:01',37.42199830,-122.08400000,NULL,'checkin_6a26bd49510e8_1780923721.jpg','2026-06-08 18:34:11',37.42199830,-122.08400000,'take order no 0012','2026-06-30','2026-06-08 13:02:01','2026-06-08 13:04:11');
/*!40000 ALTER TABLE `marketing_visits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_dispatch_history`
--

DROP TABLE IF EXISTS `order_dispatch_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_dispatch_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dispatch_id` int(10) unsigned NOT NULL,
  `action` varchar(50) NOT NULL COMMENT 'created|updated|delivered|cancelled',
  `notes` text DEFAULT NULL,
  `done_by_type` enum('Party','TSM','Admin') DEFAULT NULL,
  `done_by_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_dh_dispatch` (`dispatch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_dispatch_history`
--

LOCK TABLES `order_dispatch_history` WRITE;
/*!40000 ALTER TABLE `order_dispatch_history` DISABLE KEYS */;
INSERT INTO `order_dispatch_history` VALUES (1,1,'created','Created with 10 units','Admin',1,'2026-05-22 07:56:14'),(2,1,'updated','Header fields updated','Admin',1,'2026-05-22 08:04:58'),(3,1,'updated','Header fields updated','Admin',1,'2026-05-22 08:06:41'),(4,1,'updated','Header fields updated','Admin',1,'2026-05-22 08:09:49'),(5,1,'updated','Billing status set to Paid','Admin',1,'2026-05-22 08:09:55'),(6,1,'delivered','Marked Delivered by Admin','Admin',1,'2026-05-22 08:10:05'),(7,2,'created','Created with 90 units','Admin',1,'2026-05-22 08:11:05'),(8,2,'delivered','Marked Delivered by Admin','Admin',1,'2026-05-22 08:14:43'),(9,3,'created','Created with 16 units','Admin',1,'2026-05-22 08:22:42'),(10,3,'updated','Billing status set to Paid','Admin',1,'2026-05-22 08:24:31'),(11,3,'delivered','Marked Delivered by Admin','Admin',1,'2026-05-22 08:24:33'),(12,3,'cancelled','Cancel Dispatch → enter reason → confirm misplaced parcel','Admin',1,'2026-05-22 08:30:05'),(13,4,'created','Created with 20 units','Admin',1,'2026-05-22 13:11:39'),(14,4,'updated','Header fields updated','Admin',1,'2026-05-22 13:12:03'),(15,4,'delivered','Marked Delivered by Admin','Admin',1,'2026-05-22 13:12:29'),(16,4,'updated','Billing status set to Paid','Admin',1,'2026-05-22 13:12:46'),(17,5,'created','Created with 20 units','Admin',1,'2026-05-22 13:15:05'),(18,5,'updated','Billing status set to Paid','Admin',1,'2026-05-22 13:15:12'),(19,5,'cancelled','accident','Admin',1,'2026-05-22 13:16:14'),(20,6,'created','Created with 20 units','Admin',1,'2026-05-22 13:17:13'),(21,6,'delivered','Marked Delivered by Admin','Admin',1,'2026-05-22 13:17:32'),(22,6,'updated','Billing status set to Paid','Admin',1,'2026-05-22 13:19:31'),(23,7,'created','Created with 10 units','Admin',1,'2026-06-02 11:31:31'),(24,7,'updated','Header fields updated','Admin',1,'2026-06-02 11:31:45'),(25,7,'delivered','Marked Delivered by Admin','Admin',1,'2026-06-02 11:31:48'),(26,8,'created','Created with 6 units','Admin',1,'2026-06-02 11:36:58'),(27,8,'delivered','Marked Delivered by Admin','Admin',1,'2026-06-02 11:37:05'),(28,8,'updated','Billing status set to Paid','Admin',1,'2026-06-02 11:37:14'),(29,9,'created','Created with 9 units','Admin',1,'2026-06-02 11:37:48'),(30,9,'delivered','Marked Delivered by Admin','Admin',1,'2026-06-02 11:37:52'),(31,9,'updated','Billing status set to Paid','Admin',1,'2026-06-02 11:38:09'),(32,10,'created','Created with 30 units','Admin',1,'2026-06-02 11:53:27'),(33,10,'updated','Billing status set to Paid','Admin',1,'2026-06-02 11:53:43'),(34,10,'delivered','Marked Delivered by Admin','Admin',1,'2026-06-02 11:54:02'),(35,11,'created','Created with 20 units','Admin',1,'2026-06-02 11:54:53'),(36,11,'delivered','Marked Delivered by Admin','Admin',1,'2026-06-02 11:55:25'),(37,11,'updated','Billing status set to Partial','Admin',1,'2026-06-02 11:55:29'),(38,11,'cancelled','fsghsfgh','Admin',1,'2026-06-02 11:56:26'),(39,12,'created','Created with 20 units','Admin',1,'2026-06-04 13:54:04'),(40,12,'updated','Header fields updated','Admin',1,'2026-06-04 13:54:29'),(41,12,'delivered','Marked Delivered by Admin','Admin',1,'2026-06-04 13:55:11'),(42,12,'cancelled','sdafasdf','Admin',1,'2026-06-04 13:55:28'),(43,13,'created','Created with 18 units','Admin',1,'2026-06-04 13:56:00'),(44,13,'delivered','Marked Delivered by Admin','Admin',1,'2026-06-04 13:56:02'),(45,2,'cancelled','sdgfadsg','Admin',1,'2026-06-04 14:00:12'),(46,13,'updated','Billing status set to Paid','Admin',1,'2026-06-04 14:01:10'),(47,13,'updated','Billing status set to Unpaid','Admin',1,'2026-06-06 14:40:21'),(48,13,'updated','Billing status set to Paid','Admin',1,'2026-06-06 14:40:47'),(49,14,'created','Created with 10 units','Admin',1,'2026-06-08 06:36:31'),(50,14,'delivered','Marked Delivered by Admin','Admin',1,'2026-06-08 06:38:48'),(51,15,'created','Created with 2 units','Admin',1,'2026-06-08 07:24:59'),(52,15,'delivered','Marked Delivered by Admin','Admin',1,'2026-06-08 07:25:19'),(53,16,'created','Created with 90 units','Admin',1,'2026-06-08 07:47:33'),(54,16,'delivered','Marked Delivered by Admin','Admin',1,'2026-06-08 07:47:54'),(55,17,'created','Created with 2 units','Admin',2,'2026-06-08 08:18:22'),(56,17,'updated','Header fields updated','Admin',2,'2026-06-08 11:44:22'),(57,17,'delivered','Marked Delivered by Admin','Admin',2,'2026-06-08 11:44:27'),(58,18,'created','Created with 20 units','Admin',1,'2026-06-08 12:39:40'),(59,18,'delivered','Marked Delivered by Party','Party',118,'2026-06-08 12:40:47'),(60,18,'updated','Billing status set to Paid','Admin',1,'2026-06-08 12:41:03'),(61,18,'updated','Billing status set to Unpaid','Admin',1,'2026-06-08 12:50:16'),(62,19,'created','Created with 30 units','Admin',1,'2026-06-08 12:50:57'),(63,19,'delivered','Marked Delivered by Admin','Admin',2,'2026-06-08 12:51:48'),(64,19,'updated','Billing status set to Paid','Admin',2,'2026-06-08 12:51:53'),(65,18,'updated','Billing status set to Paid','Admin',2,'2026-06-08 12:55:17'),(66,20,'created','Created with 95 units','Admin',3,'2026-06-08 13:05:17'),(67,20,'delivered','Marked Delivered by Admin','Admin',3,'2026-06-08 13:05:50'),(68,21,'created','Created with 54 units','Admin',3,'2026-06-08 13:06:21'),(69,21,'delivered','Marked Delivered by Admin','Admin',3,'2026-06-08 13:06:24'),(70,22,'created','Created with 1 units','Admin',1,'2026-06-08 13:08:52'),(71,22,'delivered','Marked Delivered by Party','Party',118,'2026-06-08 13:08:59'),(72,22,'updated','Billing status set to Paid','Admin',2,'2026-06-08 13:09:19'),(73,22,'updated','Billing status set to Paid','Admin',2,'2026-06-08 13:09:21'),(74,21,'updated','Billing status set to Paid','Admin',2,'2026-06-08 13:09:31'),(75,20,'updated','Billing status set to Paid','Admin',2,'2026-06-08 13:09:36'),(76,23,'created','Created with 1000 units','Admin',1,'2026-06-08 13:15:49'),(77,23,'updated','Billing status set to Paid','Admin',1,'2026-06-08 13:15:52'),(78,23,'delivered','Marked Delivered by Admin','Admin',1,'2026-06-08 13:15:55'),(83,25,'created','Created with 15 units','Admin',1,'2026-06-26 06:19:29'),(84,25,'delivered','Marked Delivered by Admin','Admin',1,'2026-06-26 06:20:59'),(85,25,'updated','Billing status set to Paid','Admin',1,'2026-06-26 06:21:20'),(86,26,'created','Created with 15 units','Admin',1,'2026-06-26 06:21:42'),(87,26,'delivered','Marked Delivered by Admin','Admin',1,'2026-06-26 06:21:45'),(88,26,'cancelled','asfasdf','Admin',1,'2026-06-26 06:21:56');
/*!40000 ALTER TABLE `order_dispatch_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_dispatch_items`
--

DROP TABLE IF EXISTS `order_dispatch_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_dispatch_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dispatch_id` int(10) unsigned NOT NULL,
  `order_item_id` int(10) unsigned NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dispatch_item` (`dispatch_id`,`order_item_id`),
  KEY `idx_di_order_item` (`order_item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_dispatch_items`
--

LOCK TABLES `order_dispatch_items` WRITE;
/*!40000 ALTER TABLE `order_dispatch_items` DISABLE KEYS */;
INSERT INTO `order_dispatch_items` VALUES (1,1,1,10,'2026-05-22 07:56:14'),(2,2,1,90,'2026-05-22 08:11:05'),(3,3,2,8,'2026-05-22 08:22:42'),(4,3,3,8,'2026-05-22 08:22:42'),(5,4,4,10,'2026-05-22 13:11:39'),(6,4,5,10,'2026-05-22 13:11:39'),(7,5,4,10,'2026-05-22 13:15:05'),(8,5,5,10,'2026-05-22 13:15:05'),(9,6,4,10,'2026-05-22 13:17:13'),(10,6,5,10,'2026-05-22 13:17:13'),(11,7,2,5,'2026-06-02 11:31:31'),(12,7,3,5,'2026-06-02 11:31:31'),(13,8,10,2,'2026-06-02 11:36:58'),(14,8,11,2,'2026-06-02 11:36:58'),(15,8,12,2,'2026-06-02 11:36:58'),(16,9,10,3,'2026-06-02 11:37:48'),(17,9,11,3,'2026-06-02 11:37:48'),(18,9,12,3,'2026-06-02 11:37:48'),(19,10,13,10,'2026-06-02 11:53:27'),(20,10,14,10,'2026-06-02 11:53:27'),(21,10,15,10,'2026-06-02 11:53:27'),(22,11,13,10,'2026-06-02 11:54:53'),(23,11,14,10,'2026-06-02 11:54:53'),(24,12,13,10,'2026-06-04 13:54:04'),(25,12,14,10,'2026-06-04 13:54:04'),(26,13,13,9,'2026-06-04 13:56:00'),(27,13,14,9,'2026-06-04 13:56:00'),(28,14,2,5,'2026-06-08 06:36:31'),(29,14,3,5,'2026-06-08 06:36:31'),(30,15,13,1,'2026-06-08 07:24:59'),(31,15,14,1,'2026-06-08 07:24:59'),(32,16,1,90,'2026-06-08 07:47:33'),(33,17,16,1,'2026-06-08 08:18:22'),(34,17,17,1,'2026-06-08 08:18:22'),(35,18,20,20,'2026-06-08 12:39:40'),(36,19,20,30,'2026-06-08 12:50:57'),(37,20,24,45,'2026-06-08 13:05:17'),(38,20,25,50,'2026-06-08 13:05:17'),(39,21,24,5,'2026-06-08 13:06:21'),(40,21,25,49,'2026-06-08 13:06:21'),(41,22,25,1,'2026-06-08 13:08:52'),(42,23,27,1000,'2026-06-08 13:15:49'),(44,25,32,5,'2026-06-26 06:19:29'),(45,25,33,5,'2026-06-26 06:19:29'),(46,25,34,5,'2026-06-26 06:19:29'),(47,26,32,5,'2026-06-26 06:21:42'),(48,26,33,5,'2026-06-26 06:21:42'),(49,26,34,5,'2026-06-26 06:21:42');
/*!40000 ALTER TABLE `order_dispatch_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_dispatches`
--

DROP TABLE IF EXISTS `order_dispatches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_dispatches` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `dispatch_number` varchar(30) NOT NULL COMMENT 'DSP-YYYY-#### (auto)',
  `invoice_no` varchar(30) NOT NULL COMMENT 'INV-YYYY-#### (auto)',
  `invoice_date` date DEFAULT NULL,
  `invoice_amount` decimal(12,2) DEFAULT NULL,
  `cartons` int(11) DEFAULT NULL COMMENT 'Total cartons in THIS shipment',
  `transport` varchar(100) DEFAULT NULL COMMENT 'Self Transport / carrier name',
  `lr_no` varchar(100) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL COMMENT 'Set when marked Delivered',
  `status` enum('In_Transit','Delivered','Cancelled') NOT NULL DEFAULT 'In_Transit',
  `billing_status` enum('Unpaid','Partial','Paid') NOT NULL DEFAULT 'Unpaid',
  `notes` text DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL COMMENT 'admin_users.id',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivered_by_type` enum('Party','TSM','Admin') DEFAULT NULL,
  `delivered_by_id` int(10) unsigned DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `cancelled_by` int(10) unsigned DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `cancelled_reason` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dispatch_number` (`dispatch_number`),
  UNIQUE KEY `uq_invoice_no` (`invoice_no`),
  KEY `idx_dispatch_order` (`order_id`),
  KEY `idx_dispatch_status` (`status`),
  CONSTRAINT `fk_dispatch_order` FOREIGN KEY (`order_id`) REFERENCES `sales_orders` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_dispatches`
--

LOCK TABLES `order_dispatches` WRITE;
/*!40000 ALTER TABLE `order_dispatches` DISABLE KEYS */;
INSERT INTO `order_dispatches` VALUES (1,1,'DSP-2026-0001','INV-2026-0001','2026-05-22',600.00,1,'Self Transport','LR001','2026-05-22','Delivered','Paid',NULL,1,'2026-05-22 07:56:14','Admin',1,'2026-05-22 13:40:05',NULL,NULL,NULL),(2,1,'DSP-2026-0002','INV-2026-0002','2026-05-22',5400.00,5,'Self Transport','LR002','2026-05-22','Cancelled','Unpaid',NULL,1,'2026-05-22 08:11:05','Admin',1,'2026-05-22 13:44:43',1,'2026-06-04 19:30:12','sdgfadsg'),(3,2,'DSP-2026-0003','INV-2026-0003','2026-05-22',50000.00,5,'Self Transport','100022246755','2026-05-22','Cancelled','Paid',NULL,1,'2026-05-22 08:22:42','Admin',1,'2026-05-22 13:54:33',1,'2026-05-22 14:00:05','Cancel Dispatch → enter reason → confirm misplaced parcel'),(4,3,'DSP-2026-0004','INV-2026-0004','2026-05-22',171930.00,20,'Self Transport','100022246755','2026-05-22','Delivered','Paid','send immidiatly',1,'2026-05-22 13:11:39','Admin',1,'2026-05-22 18:42:29',NULL,NULL,NULL),(5,3,'DSP-2026-0005','INV-2026-0005','2026-05-22',171930.00,11,'Self Transport','100022246755',NULL,'Cancelled','Paid','order done last dispatch',1,'2026-05-22 13:15:05',NULL,NULL,NULL,1,'2026-05-22 18:46:14','accident'),(6,3,'DSP-2026-0006','INV-2026-0006','2026-05-22',171930.00,15,'SafeXpress','KA52B5093','2026-05-22','Delivered','Paid','done',1,'2026-05-22 13:17:13','Admin',1,'2026-05-22 18:47:32',NULL,NULL,NULL),(7,2,'DSP-2026-0007','INV-2026-0007','2026-06-02',85965.00,5,'self','100022246755','2026-06-02','Delivered','Unpaid',NULL,1,'2026-06-02 11:31:31','Admin',1,'2026-06-02 17:01:48',NULL,NULL,NULL),(8,5,'DSP-2026-0008','INV-2026-0008','2026-06-02',137760.00,5,'self','100022246755','2026-06-02','Delivered','Paid',NULL,1,'2026-06-02 11:36:58','Admin',1,'2026-06-02 17:07:05',NULL,NULL,NULL),(9,5,'DSP-2026-0009','INV-2026-0009','2026-06-02',206640.00,6,'SafeXpress','100022246755','2026-06-02','Delivered','Paid',NULL,1,'2026-06-02 11:37:48','Admin',1,'2026-06-02 17:07:52',NULL,NULL,NULL),(10,6,'DSP-2026-0010','INV-2026-0010','2026-06-02',18240.00,6,'SafeXpress','100022246755','2026-06-02','Delivered','Paid',NULL,1,'2026-06-02 11:53:27','Admin',1,'2026-06-02 17:24:02',NULL,NULL,NULL),(11,6,'DSP-2026-0011','INV-2026-0011','2026-06-02',17640.00,6,'SELF','112233','2026-06-02','Cancelled','Partial',NULL,1,'2026-06-02 11:54:53','Admin',1,'2026-06-02 17:25:25',1,'2026-06-02 17:26:26','fsghsfgh'),(12,6,'DSP-2026-0012','INV-2026-0012','2026-06-04',17640.00,50,'SafeXpress.','100022246755','2026-06-04','Cancelled','Unpaid',NULL,1,'2026-06-04 13:54:04','Admin',1,'2026-06-04 19:25:11',1,'2026-06-04 19:25:28','sdafasdf'),(13,6,'DSP-2026-0013','INV-2026-0013','2026-06-04',15876.00,3,'SafeXpress',NULL,'2026-06-04','Delivered','Paid',NULL,1,'2026-06-04 13:56:00','Admin',1,'2026-06-04 19:26:02',NULL,NULL,NULL),(14,2,'DSP-2026-0014','INV-2026-0014','2026-06-08',85965.00,5,'asdfasdf','asdfasdf','2026-06-08','Delivered','Unpaid',NULL,1,'2026-06-08 06:36:31','Admin',1,'2026-06-08 12:08:48',NULL,NULL,NULL),(15,6,'DSP-2026-0015','INV-2026-0015','2026-06-08',1764.00,2,'SafeXpress','112233','2026-06-08','Delivered','Unpaid',NULL,1,'2026-06-08 07:24:59','Admin',1,'2026-06-08 12:55:19',NULL,NULL,NULL),(16,1,'DSP-2026-0016','INV-2026-0016','2026-06-08',5400.00,4,'dfg','1122334455','2026-06-08','Delivered','Unpaid',NULL,1,'2026-06-08 07:47:33','Admin',1,'2026-06-08 13:17:54',NULL,NULL,NULL),(17,7,'DSP-2026-0017','INV-2026-0017','2026-06-08',17193.00,5,'5','asdf','2026-06-08','Delivered','Unpaid',NULL,2,'2026-06-08 08:18:22','Admin',2,'2026-06-08 17:14:27',NULL,NULL,NULL),(18,9,'DSP-2026-0018','INV-2026-0018','2026-06-08',1200.00,5,'SafeXpress','1122334455','2026-06-08','Delivered','Paid',NULL,1,'2026-06-08 12:39:40','Party',118,'2026-06-08 18:10:47',NULL,NULL,NULL),(19,9,'DSP-2026-0019','INV-2026-0019','2026-06-08',1800.00,5,'Self Transport','556644','2026-06-08','Delivered','Paid',NULL,1,'2026-06-08 12:50:57','Admin',2,'2026-06-08 18:21:48',NULL,NULL,NULL),(20,12,'DSP-2026-0020','INV-2026-0020','2026-06-08',87900.00,50,'SafeXpress.','556678','2026-06-08','Delivered','Paid',NULL,3,'2026-06-08 13:05:17','Admin',3,'2026-06-08 18:35:50',NULL,NULL,NULL),(21,12,'DSP-2026-0021','INV-2026-0021','2026-06-08',83796.00,NULL,'asdfasdf','556678','2026-06-08','Delivered','Paid',NULL,3,'2026-06-08 13:06:21','Admin',3,'2026-06-08 18:36:24',NULL,NULL,NULL),(22,12,'DSP-2026-0022','INV-2026-0022','2026-06-08',1704.00,1,'Self Transport','1122334455','2026-06-08','Delivered','Paid',NULL,1,'2026-06-08 13:08:52','Party',118,'2026-06-08 18:38:59',NULL,NULL,NULL),(23,14,'DSP-2026-0023','INV-2026-0023','2026-06-08',19413000.00,500,'Self Transport','111','2026-06-08','Delivered','Paid',NULL,1,'2026-06-08 13:15:49','Admin',1,'2026-06-08 18:45:55',NULL,NULL,NULL),(25,17,'DSP-2026-0024','INV-2026-0024','2026-06-26',229605.00,5,'asdfasdf','Direct','2026-06-26','Delivered','Paid','asdfasdf adf',1,'2026-06-26 06:19:29','Admin',1,'2026-06-26 11:50:59',NULL,NULL,NULL),(26,17,'DSP-2026-0025','INV-2026-0025','2026-06-26',229605.00,NULL,NULL,NULL,'2026-06-26','Cancelled','Unpaid',NULL,1,'2026-06-26 06:21:42','Admin',1,'2026-06-26 11:51:45',1,'2026-06-26 11:51:56','asfasdf');
/*!40000 ALTER TABLE `order_dispatches` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_dispatch_number` BEFORE INSERT ON `order_dispatches` FOR EACH ROW BEGIN
          IF NEW.dispatch_number IS NULL OR NEW.dispatch_number = '' THEN
            SET NEW.dispatch_number = CONCAT(
              'DSP-', YEAR(NOW()), '-',
              LPAD(
                (SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(dispatch_number, '-', -1) AS UNSIGNED)), 0) + 1
                 FROM order_dispatches
                 WHERE YEAR(created_at) = YEAR(NOW())),
                4, '0')
            );
          END IF;
          IF NEW.invoice_no IS NULL OR NEW.invoice_no = '' THEN
            SET NEW.invoice_no = CONCAT(
              'INV-', YEAR(NOW()), '-',
              LPAD(
                (SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(invoice_no, '-', -1) AS UNSIGNED)), 0) + 1
                 FROM order_dispatches
                 WHERE YEAR(created_at) = YEAR(NOW())),
                4, '0')
            );
          END IF;
        END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `parts_inventory_master`
--

DROP TABLE IF EXISTS `parts_inventory_master`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parts_inventory_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `part_name` varchar(100) NOT NULL COMMENT 'Part name (e.g., Spindle, Washer)',
  `part_code` varchar(50) DEFAULT NULL COMMENT 'Unique part code/SKU',
  `category_id` int(11) DEFAULT NULL COMMENT 'Which product category this part belongs to',
  `unit_cost` decimal(10,2) DEFAULT 0.00 COMMENT 'Standard unit cost',
  `description` text DEFAULT NULL COMMENT 'Part description',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Active status',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `part_code` (`part_code`),
  KEY `idx_active` (`is_active`),
  KEY `idx_category` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master list of spare parts';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parts_inventory_master`
--

LOCK TABLES `parts_inventory_master` WRITE;
/*!40000 ALTER TABLE `parts_inventory_master` DISABLE KEYS */;
INSERT INTO `parts_inventory_master` VALUES (1,'Spindle','PART-SP-001',2,150.00,'Standard spindle for faucets',1,'2026-01-20 13:17:23'),(2,'Washer','PART-WS-001',5,20.00,'Rubber washer',1,'2026-01-20 13:17:23'),(3,'O-Ring','PART-OR-001',2,15.00,'O-ring seal',1,'2026-01-20 13:17:23'),(4,'Cartridge','PART-CR-001',2,350.00,'Ceramic cartridge',1,'2026-01-20 13:17:23'),(5,'Aerator','PART-AE-001',2,50.00,'Faucet aerator',1,'2026-01-20 13:17:23');
/*!40000 ALTER TABLE `parts_inventory_master` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `party_profiles`
--

DROP TABLE IF EXISTS `party_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `party_profiles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `audience_id` int(10) unsigned NOT NULL,
  `base_discount_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `extra_discount_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `volume_threshold_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `volume_period_days` int(11) NOT NULL DEFAULT 90,
  `assigned_tsm_id` int(10) unsigned DEFAULT NULL,
  `last_order_at` datetime DEFAULT NULL,
  `current_period_volume` decimal(12,2) DEFAULT 0.00,
  `volume_bonus_active` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `audience_id` (`audience_id`),
  KEY `idx_pp_tsm` (`assigned_tsm_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `party_profiles`
--

LOCK TABLES `party_profiles` WRITE;
/*!40000 ALTER TABLE `party_profiles` DISABLE KEYS */;
INSERT INTO `party_profiles` VALUES (1,115,40.00,6.00,1000000.00,90,NULL,NULL,805008.00,0,'2026-05-17 11:57:05','2026-06-26 06:21:56'),(3,116,40.00,6.00,1000000.00,90,NULL,NULL,343860.00,0,'2026-05-20 07:27:24','2026-05-22 13:17:13'),(10,117,40.00,6.00,1000000.00,30,NULL,NULL,0.00,0,'2026-06-08 07:59:46','2026-06-08 07:59:46'),(11,118,40.00,4.00,1000000.00,30,NULL,NULL,19589400.00,1,'2026-06-08 07:59:46','2026-06-23 10:52:36'),(12,119,40.00,6.00,1000000.00,30,NULL,NULL,0.00,0,'2026-06-08 07:59:46','2026-06-08 07:59:46'),(13,120,40.00,6.00,1000000.00,30,NULL,NULL,0.00,0,'2026-06-08 07:59:46','2026-06-08 07:59:46');
/*!40000 ALTER TABLE `party_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plumber_inventory_allocations`
--

DROP TABLE IF EXISTS `plumber_inventory_allocations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plumber_inventory_allocations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plumber_id` int(10) unsigned NOT NULL,
  `part_id` int(11) NOT NULL,
  `quantity_given` int(11) NOT NULL,
  `old_parts_received` int(11) DEFAULT 0,
  `allocation_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `allocated_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `allocated_by` (`allocated_by`),
  KEY `idx_plumber` (`plumber_id`),
  KEY `idx_part` (`part_id`),
  KEY `idx_date` (`allocation_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plumber_inventory_allocations`
--

LOCK TABLES `plumber_inventory_allocations` WRITE;
/*!40000 ALTER TABLE `plumber_inventory_allocations` DISABLE KEYS */;
/*!40000 ALTER TABLE `plumber_inventory_allocations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plumber_settlements`
--

DROP TABLE IF EXISTS `plumber_settlements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plumber_settlements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plumber_id` int(10) unsigned NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `total_complaints` int(11) DEFAULT 0,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('PENDING','PAID') DEFAULT 'PENDING',
  `transaction_ref` varchar(100) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `debit_note_number` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `settled_by` int(10) unsigned NOT NULL,
  `settled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `settled_by` (`settled_by`),
  KEY `idx_plumber` (`plumber_id`),
  KEY `idx_period` (`period_start`,`period_end`),
  KEY `idx_debit_note` (`debit_note_number`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plumber_settlements`
--

LOCK TABLES `plumber_settlements` WRITE;
/*!40000 ALTER TABLE `plumber_settlements` DISABLE KEYS */;
INSERT INTO `plumber_settlements` VALUES (14,60,1,'2026-02-06','2026-02-06',1,600.00,'PAID','11223344','uploads/payment_proofs/proof_14_1770386008.jpg','2026-02-07 00:00:00','DN-1000',NULL,1,'2026-02-06 13:51:11'),(15,60,1,'2026-02-16','2026-02-16',2,815.00,'PAID','11223344','uploads/payment_proofs/proof_15_1771331982.jpg','2026-02-17 00:00:00','DN-1001',NULL,1,'2026-02-16 06:56:41'),(16,60,1,'2026-02-24','2026-02-24',1,600.00,'PAID','11223344','uploads/payment_proofs/proof_16_1771917032.jpg','2026-02-24 00:00:00','DN-1002',NULL,1,'2026-02-24 07:09:40'),(17,60,1,'2026-02-26','2026-02-26',2,1250.00,'PAID','11223344','uploads/payment_proofs/proof_17_1772096925.jpg','2026-02-26 00:00:00','DN-1003',NULL,1,'2026-02-26 09:08:05'),(18,60,1,'2026-03-30','2026-03-30',1,600.00,'PAID','11223344','uploads/payment_proofs/proof_18_1774883577.jpeg','2026-03-30 00:00:00','DN-1004',NULL,1,'2026-03-30 15:11:53'),(19,60,1,'2026-04-22','2026-04-22',1,600.00,'PAID','11223344','uploads/payment_proofs/proof_19_1776863022.jpg','2026-04-22 00:00:00','DN-1005',NULL,1,'2026-04-22 13:02:27'),(20,60,1,'2026-06-23','2026-06-23',1,550.00,'PAID','11223344','uploads/payment_proofs/proof_20_1782214426.png','2026-06-23 00:00:00','DN-1006',NULL,1,'2026-06-23 11:16:02');
/*!40000 ALTER TABLE `plumber_settlements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plumber_skills`
--

DROP TABLE IF EXISTS `plumber_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plumber_skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plumber_id` int(10) unsigned NOT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_plumber_category` (`plumber_id`,`category_id`),
  KEY `idx_plumber` (`plumber_id`),
  KEY `idx_category` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plumber_skills`
--

LOCK TABLES `plumber_skills` WRITE;
/*!40000 ALTER TABLE `plumber_skills` DISABLE KEYS */;
INSERT INTO `plumber_skills` VALUES (19,60,2,'2026-01-23 12:11:48'),(20,60,3,'2026-01-23 12:11:48'),(21,60,5,'2026-01-23 12:11:48'),(22,60,1,'2026-01-23 12:11:48'),(23,60,4,'2026-01-23 12:11:48');
/*!40000 ALTER TABLE `plumber_skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_categories`
--

DROP TABLE IF EXISTS `product_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL COMMENT 'Product category name',
  `warranty_period_months` int(11) DEFAULT 12 COMMENT 'Warranty period in months',
  `description` text DEFAULT NULL COMMENT 'Category description',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Active status',
  `display_order` int(11) DEFAULT 0 COMMENT 'Display order in dropdowns',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_name` (`category_name`),
  KEY `idx_active` (`is_active`),
  KEY `idx_display_order` (`display_order`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Product categories with warranty periods';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_categories`
--

LOCK TABLES `product_categories` WRITE;
/*!40000 ALTER TABLE `product_categories` DISABLE KEYS */;
INSERT INTO `product_categories` VALUES (1,'Sanitarywares',60,'Toilets, washbasins, urinals - 5 year warranty',1,1,'2026-01-20 12:44:19'),(2,'Bath Fittings',120,'Faucets, showers, bath accessories - 10 year warranty',1,2,'2026-01-20 12:44:19'),(3,'Kitchen Sinks',60,'Kitchen sinks and accessories - 5 year warranty',1,3,'2026-01-20 12:44:19'),(4,'Wellness',120,'Jacuzzi, spa systems, wellness products - 10 year warranty',1,4,'2026-01-20 12:44:19'),(5,'PTMT Faucets',120,'PTMT faucet products - 10 year warranty',1,5,'2026-01-20 12:44:19');
/*!40000 ALTER TABLE `product_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_order_history`
--

DROP TABLE IF EXISTS `sales_order_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_order_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `action` varchar(100) NOT NULL COMMENT 'placed, approved, modified, cancelled, processing, dispatched, etc.',
  `from_status` varchar(50) DEFAULT NULL,
  `to_status` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `done_by_type` enum('Party','TSM','Sales_Team','Admin','System') DEFAULT NULL,
  `done_by_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_soh_order` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=175 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_order_history`
--

LOCK TABLES `sales_order_history` WRITE;
/*!40000 ALTER TABLE `sales_order_history` DISABLE KEYS */;
INSERT INTO `sales_order_history` VALUES (1,1,'placed',NULL,'Placed','Order placed by Admin','Admin',1,'2026-05-22 07:23:06'),(2,1,'approved','Placed','Approved',NULL,'Sales_Team',1,'2026-05-22 07:53:57'),(3,1,'status_change','Approved','Partial_Dispatch','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-05-22 07:56:14'),(4,1,'dispatch_created',NULL,NULL,'Dispatch created with 10 units','Admin',1,'2026-05-22 07:56:14'),(5,1,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0001 delivered','Admin',1,'2026-05-22 08:10:05'),(6,1,'status_change','Partial_Dispatch','Dispatched','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-05-22 08:11:05'),(7,1,'dispatch_created',NULL,NULL,'Dispatch created with 90 units','Admin',1,'2026-05-22 08:11:05'),(8,1,'status_change','Dispatched','Delivered','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-05-22 08:14:43'),(9,1,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0002 delivered','Admin',1,'2026-05-22 08:14:43'),(10,2,'placed',NULL,'Placed','Order placed by Admin','Admin',1,'2026-05-22 08:22:01'),(11,2,'approved','Placed','Approved',NULL,'Sales_Team',1,'2026-05-22 08:22:04'),(12,2,'status_change','Approved','Partial_Dispatch','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-05-22 08:22:42'),(13,2,'dispatch_created',NULL,NULL,'Dispatch created with 16 units','Admin',1,'2026-05-22 08:22:42'),(14,2,'short_closed',NULL,NULL,'Order short-closed (shortfall: 4 units). Reason: \"Stock unavailable\"','Admin',1,'2026-05-22 08:23:29'),(15,2,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0003 delivered','Admin',1,'2026-05-22 08:24:33'),(16,2,'status_change','Partial_Dispatch','Approved','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-05-22 08:30:05'),(17,2,'dispatch_cancelled',NULL,NULL,'Dispatch DSP-2026-0003 cancelled: Cancel Dispatch → enter reason → confirm misplaced parcel','Admin',1,'2026-05-22 08:30:05'),(18,3,'placed',NULL,'Placed','Order placed by Admin','Admin',1,'2026-05-22 13:09:02'),(19,3,'items_modified',NULL,NULL,'Thermostatic Shower Mixer With Body & Trim: 15 → 20 | Thermostatic Shower Mixer With Body & Trim: 15 → 20','Sales_Team',1,'2026-05-22 13:09:30'),(20,3,'approved','Placed','Approved',NULL,'Sales_Team',1,'2026-05-22 13:10:00'),(21,3,'status_change','Approved','Partial_Dispatch','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-05-22 13:11:39'),(22,3,'dispatch_created',NULL,NULL,'Dispatch created with 20 units','Admin',1,'2026-05-22 13:11:39'),(23,3,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0004 delivered','Admin',1,'2026-05-22 13:12:29'),(24,3,'status_change','Partial_Dispatch','Dispatched','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-05-22 13:15:05'),(25,3,'dispatch_created',NULL,NULL,'Dispatch created with 20 units','Admin',1,'2026-05-22 13:15:05'),(26,3,'status_change','Dispatched','Partial_Dispatch','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-05-22 13:16:14'),(27,3,'dispatch_cancelled',NULL,NULL,'Dispatch DSP-2026-0005 cancelled: accident','Admin',1,'2026-05-22 13:16:14'),(28,3,'status_change','Partial_Dispatch','Dispatched','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-05-22 13:17:13'),(29,3,'dispatch_created',NULL,NULL,'Dispatch created with 20 units','Admin',1,'2026-05-22 13:17:13'),(30,3,'status_change','Dispatched','Delivered','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-05-22 13:17:32'),(31,3,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0006 delivered','Admin',1,'2026-05-22 13:17:32'),(32,2,'short_close_reopened',NULL,NULL,'Short-close reversed — dispatches re-enabled','Admin',1,'2026-05-22 14:23:50'),(33,4,'placed',NULL,'Placed','Order placed by Admin','Admin',1,'2026-05-26 05:20:08'),(34,4,'items_modified',NULL,NULL,'Added: Freestanding Bathtubs × 15 @ ₹44,157','Sales_Team',1,'2026-05-26 05:22:21'),(35,4,'items_modified',NULL,NULL,'Added: Thermostatic Shower Mixer With Body & Trim × 10 @ ₹60','Sales_Team',1,'2026-05-26 05:29:38'),(36,4,'items_modified',NULL,NULL,'Removed: Thermostatic Shower Mixer With Body & Trim','Sales_Team',1,'2026-05-26 05:29:50'),(37,2,'status_change','Approved','Partial_Dispatch','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-02 11:31:31'),(38,2,'dispatch_created',NULL,NULL,'Dispatch created with 10 units','Admin',1,'2026-06-02 11:31:31'),(39,2,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0007 delivered','Admin',1,'2026-06-02 11:31:48'),(40,5,'placed',NULL,'Placed','Order placed by Admin','Admin',1,'2026-06-02 11:36:36'),(41,5,'approved','Placed','Approved',NULL,'Sales_Team',1,'2026-06-02 11:36:39'),(42,5,'status_change','Approved','Partial_Dispatch','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-02 11:36:58'),(43,5,'dispatch_created',NULL,NULL,'Dispatch created with 6 units','Admin',1,'2026-06-02 11:36:58'),(44,5,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0008 delivered','Admin',1,'2026-06-02 11:37:05'),(45,5,'status_change','Partial_Dispatch','Dispatched','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-02 11:37:48'),(46,5,'dispatch_created',NULL,NULL,'Dispatch created with 9 units','Admin',1,'2026-06-02 11:37:48'),(47,5,'status_change','Dispatched','Delivered','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-02 11:37:52'),(48,5,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0009 delivered','Admin',1,'2026-06-02 11:37:52'),(49,6,'placed',NULL,'Placed','Order placed by Admin','Admin',1,'2026-06-02 11:52:14'),(50,6,'items_modified',NULL,NULL,'Added: Thermostatic Shower Mixer With Body & Trim × 10 @ ₹60','Sales_Team',1,'2026-06-02 11:52:31'),(51,6,'approved','Placed','Approved',NULL,'Sales_Team',1,'2026-06-02 11:52:47'),(52,6,'status_change','Approved','Partial_Dispatch','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-02 11:53:27'),(53,6,'dispatch_created',NULL,NULL,'Dispatch created with 30 units','Admin',1,'2026-06-02 11:53:27'),(54,6,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0010 delivered','Admin',1,'2026-06-02 11:54:02'),(55,6,'status_change','Partial_Dispatch','Dispatched','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-02 11:54:53'),(56,6,'dispatch_created',NULL,NULL,'Dispatch created with 20 units','Admin',1,'2026-06-02 11:54:53'),(57,6,'status_change','Dispatched','Delivered','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-02 11:55:25'),(58,6,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0011 delivered','Admin',1,'2026-06-02 11:55:25'),(59,6,'status_change','Delivered','Partial_Dispatch','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-02 11:56:26'),(60,6,'dispatch_cancelled',NULL,NULL,'Dispatch DSP-2026-0011 cancelled: fsghsfgh','Admin',1,'2026-06-02 11:56:26'),(61,6,'status_change','Partial_Dispatch','Dispatched','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-04 13:54:04'),(62,6,'dispatch_created',NULL,NULL,'Dispatch created with 20 units','Admin',1,'2026-06-04 13:54:04'),(63,6,'status_change','Dispatched','Delivered','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-04 13:55:11'),(64,6,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0012 delivered','Admin',1,'2026-06-04 13:55:11'),(65,6,'status_change','Delivered','Partial_Dispatch','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-04 13:55:28'),(66,6,'dispatch_cancelled',NULL,NULL,'Dispatch DSP-2026-0012 cancelled: sdafasdf','Admin',1,'2026-06-04 13:55:28'),(67,6,'dispatch_created',NULL,NULL,'Dispatch created with 18 units','Admin',1,'2026-06-04 13:56:00'),(68,6,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0013 delivered','Admin',1,'2026-06-04 13:56:02'),(69,6,'short_closed',NULL,NULL,'Order short-closed (shortfall: 2 units). Reason: party ok with this','Admin',1,'2026-06-04 13:57:19'),(70,1,'status_change','Delivered','Partial_Dispatch','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-04 14:00:12'),(71,1,'dispatch_cancelled',NULL,NULL,'Dispatch DSP-2026-0002 cancelled: sdgfadsg','Admin',1,'2026-06-04 14:00:12'),(72,6,'short_close_reopened',NULL,NULL,'Short-close reversed — dispatches re-enabled','Admin',1,'2026-06-05 11:04:38'),(73,6,'short_closed',NULL,NULL,'Order short-closed (shortfall: 2 units). Reason: 2 dont want to send','Admin',1,'2026-06-06 14:37:48'),(74,6,'status_change','Partial_Dispatch','Delivered','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 05:54:37'),(75,6,'short_close_reopened',NULL,NULL,'Short-close reversed — dispatches re-enabled','Admin',1,'2026-06-08 06:31:22'),(76,6,'status_change','Delivered','Partial_Dispatch','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 06:34:14'),(77,2,'status_change','Partial_Dispatch','Dispatched','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 06:36:31'),(78,2,'dispatch_created',NULL,NULL,'Dispatch created with 10 units','Admin',1,'2026-06-08 06:36:31'),(79,2,'status_change','Dispatched','Delivered','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 06:38:48'),(80,2,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0014 delivered','Admin',1,'2026-06-08 06:38:48'),(81,6,'short_closed',NULL,NULL,'Order short-closed (shortfall: 2 units). Reason: done for now','Admin',1,'2026-06-08 06:39:27'),(82,6,'status_change','Partial_Dispatch','Delivered','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 06:42:53'),(83,6,'status_change','Delivered','Partial_Dispatch','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 07:24:34'),(84,6,'short_close_reopened',NULL,NULL,'Short-close reversed — dispatches re-enabled','Admin',1,'2026-06-08 07:24:34'),(85,6,'status_change','Partial_Dispatch','Dispatched','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 07:24:59'),(86,6,'dispatch_created',NULL,NULL,'Dispatch created with 2 units','Admin',1,'2026-06-08 07:24:59'),(87,6,'status_change','Dispatched','Delivered','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 07:25:19'),(88,6,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0015 delivered','Admin',1,'2026-06-08 07:25:19'),(89,1,'status_change','Partial_Dispatch','Delivered','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 07:46:57'),(90,1,'short_closed',NULL,NULL,'Order short-closed (shortfall: 90 units). Reason: dfgasdf','Admin',1,'2026-06-08 07:46:57'),(91,1,'status_change','Delivered','Partial_Dispatch','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 07:47:14'),(92,1,'short_close_reopened',NULL,NULL,'Short-close reversed — dispatches re-enabled','Admin',1,'2026-06-08 07:47:14'),(93,1,'status_change','Partial_Dispatch','Dispatched','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 07:47:33'),(94,1,'dispatch_created',NULL,NULL,'Dispatch created with 90 units','Admin',1,'2026-06-08 07:47:33'),(95,1,'status_change','Dispatched','Delivered','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 07:47:54'),(96,1,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0016 delivered','Admin',1,'2026-06-08 07:47:54'),(97,7,'placed',NULL,'Placed',NULL,'Admin',1,'2026-06-08 08:00:25'),(98,8,'placed',NULL,'Placed',NULL,'Admin',1,'2026-06-08 08:00:25'),(99,9,'placed',NULL,'Placed',NULL,'Admin',1,'2026-06-08 08:00:25'),(100,10,'placed',NULL,'Placed',NULL,'Admin',1,'2026-06-08 08:00:25'),(101,11,'placed',NULL,'Placed',NULL,'Admin',1,'2026-06-08 08:00:25'),(102,7,'approved','Placed','Approved',NULL,'Sales_Team',2,'2026-06-08 08:17:22'),(103,7,'status_change','Approved','Partial_Dispatch','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 08:18:22'),(104,7,'dispatch_created',NULL,NULL,'Dispatch created with 2 units','Admin',2,'2026-06-08 08:18:22'),(105,8,'cancelled','Placed','Cancelled','asdfasdf','Sales_Team',2,'2026-06-08 08:19:36'),(106,7,'status_change','Partial_Dispatch','Dispatched','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 08:20:14'),(107,7,'short_closed',NULL,NULL,'Order short-closed (shortfall: 10 units). Reason: sadfsadf','Admin',2,'2026-06-08 08:20:14'),(108,9,'approved','Placed','Approved',NULL,'Sales_Team',2,'2026-06-08 10:23:23'),(109,11,'approved','Placed','Approved',NULL,'Sales_Team',2,'2026-06-08 11:04:22'),(110,11,'items_modified',NULL,NULL,'Thermostatic Shower Mixer With Body & Trim: 15 → 20','Sales_Team',2,'2026-06-08 11:04:49'),(111,7,'status_change','Dispatched','Partial_Dispatch','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 11:09:35'),(112,7,'short_close_reopened',NULL,NULL,'Short-close reversed — dispatches re-enabled','Admin',2,'2026-06-08 11:09:35'),(113,7,'status_change','Partial_Dispatch','Dispatched','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 11:43:52'),(114,7,'short_closed',NULL,NULL,'Order short-closed (shortfall: 10 units). Reason: sdfasdf','Admin',2,'2026-06-08 11:43:52'),(115,7,'status_change','Dispatched','Delivered','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 11:44:27'),(116,7,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0017 delivered','Admin',2,'2026-06-08 11:44:27'),(117,10,'approved','Placed','Approved',NULL,'Sales_Team',1,'2026-06-08 11:51:04'),(118,9,'status_change','Approved','Partial_Dispatch','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 12:39:40'),(119,9,'dispatch_created',NULL,NULL,'Dispatch created with 20 units','Admin',1,'2026-06-08 12:39:40'),(120,9,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0018 delivered','Party',118,'2026-06-08 12:40:47'),(121,9,'status_change','Partial_Dispatch','Dispatched','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 12:50:57'),(122,9,'dispatch_created',NULL,NULL,'Dispatch created with 30 units','Admin',1,'2026-06-08 12:50:57'),(123,9,'status_change','Dispatched','Delivered','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 12:51:48'),(124,9,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0019 delivered','Admin',2,'2026-06-08 12:51:48'),(125,12,'placed',NULL,'Placed','Order placed by TSM','TSM',99,'2026-06-08 13:02:54'),(126,12,'items_modified',NULL,NULL,'Drop In Bathtub Kit: 50 → 100','TSM',99,'2026-06-08 13:03:04'),(127,12,'approved','Placed','Approved',NULL,'Sales_Team',2,'2026-06-08 13:03:24'),(128,12,'status_change','Approved','Partial_Dispatch','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 13:05:17'),(129,12,'dispatch_created',NULL,NULL,'Dispatch created with 95 units','Admin',3,'2026-06-08 13:05:17'),(130,12,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0020 delivered','Admin',3,'2026-06-08 13:05:50'),(131,12,'dispatch_created',NULL,NULL,'Dispatch created with 54 units','Admin',3,'2026-06-08 13:06:21'),(132,12,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0021 delivered','Admin',3,'2026-06-08 13:06:24'),(133,12,'status_change','Partial_Dispatch','Delivered','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 13:07:14'),(134,12,'short_closed',NULL,NULL,'Order short-closed (shortfall: 1 units). Reason: not required now','Admin',2,'2026-06-08 13:07:14'),(135,12,'status_change','Delivered','Partial_Dispatch','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 13:08:25'),(136,12,'short_close_reopened',NULL,NULL,'Short-close reversed — dispatches re-enabled','Admin',2,'2026-06-08 13:08:25'),(137,12,'status_change','Partial_Dispatch','Dispatched','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 13:08:52'),(138,12,'dispatch_created',NULL,NULL,'Dispatch created with 1 units','Admin',1,'2026-06-08 13:08:52'),(139,12,'status_change','Dispatched','Delivered','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 13:08:59'),(140,12,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0022 delivered','Party',118,'2026-06-08 13:08:59'),(141,13,'placed',NULL,'Placed','Order placed by Party','Party',118,'2026-06-08 13:11:31'),(142,13,'items_modified',NULL,NULL,'Thermostatic Shower Mixer With Body & Trim: 100 → 50','Sales_Team',2,'2026-06-08 13:12:16'),(143,14,'placed',NULL,'Placed','Order placed by TSM','TSM',99,'2026-06-08 13:13:28'),(144,15,'placed',NULL,'Placed','Order placed by Admin','Admin',2,'2026-06-08 13:14:09'),(145,15,'approved','Placed','Approved',NULL,'Sales_Team',2,'2026-06-08 13:14:12'),(146,16,'placed',NULL,'Placed','Order placed by Admin','Admin',2,'2026-06-08 13:14:44'),(147,14,'approved','Placed','Approved',NULL,'Sales_Team',2,'2026-06-08 13:15:02'),(148,14,'status_change','Approved','Dispatched','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 13:15:49'),(149,14,'dispatch_created',NULL,NULL,'Dispatch created with 1000 units','Admin',1,'2026-06-08 13:15:49'),(150,14,'status_change','Dispatched','Delivered','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 13:15:55'),(151,14,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0023 delivered','Admin',1,'2026-06-08 13:15:55'),(152,16,'items_modified',NULL,NULL,'Freestanding Bathtub Facucets: 2 → 3','Sales_Team',1,'2026-06-08 13:17:00'),(153,16,'items_modified',NULL,NULL,'Added: Freestanding Bathtub Facucets × 1 @ ₹21,312','Sales_Team',1,'2026-06-08 13:17:46'),(154,16,'items_modified',NULL,NULL,'Removed: Freestanding Bathtub Facucets','Sales_Team',1,'2026-06-08 13:17:56'),(155,16,'items_modified',NULL,NULL,'Freestanding Bathtub Facucets: 1 → 2','Party',118,'2026-06-08 13:18:40'),(156,16,'items_modified',NULL,NULL,'Added: Freestanding Bathtub Facucets × 2 @ ₹20,160 | Removed: Freestanding Bathtub Facucets','Party',118,'2026-06-08 13:19:00'),(157,16,'approved','Placed','Approved',NULL,'Sales_Team',1,'2026-06-08 13:19:31'),(158,16,'status_change','Approved','Dispatched','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 13:20:24'),(159,16,'dispatch_created',NULL,NULL,'Dispatch created with 2 units','Admin',1,'2026-06-08 13:20:24'),(160,16,'status_change','Dispatched','Delivered','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-08 13:20:40'),(161,16,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0024 delivered','Admin',1,'2026-06-08 13:20:40'),(162,16,'cancelled','Delivered','Cancelled','sdfasdf','Sales_Team',1,'2026-06-23 10:52:36'),(163,17,'placed',NULL,'Placed','Order placed by Admin','Admin',1,'2026-06-26 06:17:07'),(164,17,'items_modified',NULL,NULL,'Added: Freestanding Bathtubs × 10 @ ₹44,157','Sales_Team',1,'2026-06-26 06:17:39'),(165,17,'approved','Placed','Approved',NULL,'Sales_Team',1,'2026-06-26 06:18:21'),(166,17,'status_change','Approved','Partial_Dispatch','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-26 06:19:29'),(167,17,'dispatch_created',NULL,NULL,'Dispatch created with 15 units','Admin',1,'2026-06-26 06:19:29'),(168,17,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0024 delivered','Admin',1,'2026-06-26 06:20:59'),(169,17,'status_change','Partial_Dispatch','Dispatched','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-26 06:21:42'),(170,17,'dispatch_created',NULL,NULL,'Dispatch created with 15 units','Admin',1,'2026-06-26 06:21:42'),(171,17,'status_change','Dispatched','Delivered','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-26 06:21:45'),(172,17,'dispatch_delivered',NULL,NULL,'Dispatch DSP-2026-0025 delivered','Admin',1,'2026-06-26 06:21:45'),(173,17,'status_change','Delivered','Partial_Dispatch','Auto-flipped by dispatch lifecycle','Admin',NULL,'2026-06-26 06:21:56'),(174,17,'dispatch_cancelled',NULL,NULL,'Dispatch DSP-2026-0025 cancelled: asfasdf','Admin',1,'2026-06-26 06:21:56');
/*!40000 ALTER TABLE `sales_order_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_order_items`
--

DROP TABLE IF EXISTS `sales_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_order_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `variant_id` int(10) unsigned DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_code` varchar(100) DEFAULT NULL,
  `hsn_code` varchar(15) DEFAULT NULL,
  `variant_label` varchar(150) DEFAULT NULL COMMENT 'Colour or size variant label',
  `price_zone` enum('Z1','Z2') DEFAULT 'Z1' COMMENT 'Which zone price was used',
  `mrp` decimal(10,2) NOT NULL,
  `effective_discount_pct` decimal(5,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `dispatched_qty` int(11) DEFAULT 0,
  `line_total` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_soi_order` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_order_items`
--

LOCK TABLES `sales_order_items` WRITE;
/*!40000 ALTER TABLE `sales_order_items` DISABLE KEYS */;
INSERT INTO `sales_order_items` VALUES (1,1,93,NULL,'Thermostatic Shower Mixer With Body & Trim','TDS60','69101000',NULL,'Z1',100.00,40.00,60.00,100,100,6000.00,'2026-05-22 07:23:06'),(2,2,93,NULL,'Thermostatic Shower Mixer With Body & Trim','TDS60','69101000',NULL,'Z1',100.00,40.00,60.00,10,10,600.00,'2026-05-22 08:22:01'),(3,2,94,NULL,'Thermostatic Shower Mixer With Body & Trim','TDR50','69101000',NULL,'Z1',28555.00,40.00,17133.00,10,10,171330.00,'2026-05-22 08:22:01'),(4,3,93,NULL,'Thermostatic Shower Mixer With Body & Trim','TDS60','69101000',NULL,'Z1',100.00,40.00,60.00,20,20,1200.00,'2026-05-22 13:09:02'),(5,3,94,NULL,'Thermostatic Shower Mixer With Body & Trim','TDR50','69101000',NULL,'Z1',28555.00,40.00,17133.00,20,20,342660.00,'2026-05-22 13:09:02'),(6,4,93,NULL,'Thermostatic Shower Mixer With Body & Trim','TDS60','69101000',NULL,'Z1',100.00,40.00,60.00,1,0,60.00,'2026-05-26 05:20:08'),(7,4,94,NULL,'Thermostatic Shower Mixer With Body & Trim','TDR50','69101000',NULL,'Z1',28555.00,40.00,17133.00,1,0,17133.00,'2026-05-26 05:20:08'),(8,4,158,NULL,'Freestanding Bathtubs','6809',NULL,NULL,'Z1',73595.00,40.00,44157.00,15,0,662355.00,'2026-05-26 05:22:21'),(10,5,165,NULL,'Drop In Bathtub Kit','505510','69101000',NULL,'Z1',2840.00,40.00,1704.00,5,5,8520.00,'2026-06-02 11:36:36'),(11,5,159,NULL,'Freestanding Bathtubs','6509',NULL,NULL,'Z1',83405.00,40.00,50043.00,5,5,250215.00,'2026-06-02 11:36:36'),(12,5,94,NULL,'Thermostatic Shower Mixer With Body & Trim','TDR50','69101000',NULL,'Z1',28555.00,40.00,17133.00,5,5,85665.00,'2026-06-02 11:36:36'),(13,6,165,NULL,'Drop In Bathtub Kit','505510','69101000',NULL,'Z1',2840.00,40.00,1704.00,20,20,34080.00,'2026-06-02 11:52:14'),(14,6,93,NULL,'Thermostatic Shower Mixer With Body & Trim','TDS60','69101000',NULL,'Z1',100.00,40.00,60.00,20,20,1200.00,'2026-06-02 11:52:14'),(15,6,93,NULL,'Thermostatic Shower Mixer With Body & Trim','TDS60','69101000',NULL,'Z1',100.00,40.00,60.00,10,10,600.00,'2026-06-02 11:52:31'),(16,7,93,NULL,'Thermostatic Shower Mixer With Body & Trim','TDS60','69101000',NULL,'Z1',100.00,40.00,60.00,10,1,600.00,'2026-06-08 08:00:25'),(17,7,94,NULL,'Thermostatic Shower Mixer With Body & Trim','TDR50','69101000',NULL,'Z1',28555.00,40.00,17133.00,2,1,34266.00,'2026-06-08 08:00:25'),(18,8,93,NULL,'Thermostatic Shower Mixer With Body & Trim','TDS60','69101000',NULL,'Z1',100.00,40.00,60.00,20,0,1200.00,'2026-06-08 08:00:25'),(19,8,156,NULL,'Freestanding Bathtubs','EW6312','69101000',NULL,'Z1',71150.00,40.00,42690.00,1,0,42690.00,'2026-06-08 08:00:25'),(20,9,93,NULL,'Thermostatic Shower Mixer With Body & Trim','TDS60','69101000',NULL,'Z1',100.00,40.00,60.00,50,50,3000.00,'2026-06-08 08:00:25'),(21,10,94,NULL,'Thermostatic Shower Mixer With Body & Trim','TDR50','69101000',NULL,'Z1',28555.00,40.00,17133.00,5,0,85665.00,'2026-06-08 08:00:25'),(22,10,156,NULL,'Freestanding Bathtubs','EW6312','69101000',NULL,'Z1',71150.00,40.00,42690.00,2,0,85380.00,'2026-06-08 08:00:25'),(23,11,93,NULL,'Thermostatic Shower Mixer With Body & Trim','TDS60','69101000',NULL,'Z1',100.00,40.00,60.00,20,0,1200.00,'2026-06-08 08:00:25'),(24,12,93,NULL,'Thermostatic Shower Mixer With Body & Trim','TDS60','69101000',NULL,'Z1',100.00,40.00,60.00,50,50,3000.00,'2026-06-08 13:02:54'),(25,12,165,NULL,'Drop In Bathtub Kit','505510','69101000',NULL,'Z1',2840.00,40.00,1704.00,100,100,170400.00,'2026-06-08 13:02:54'),(26,13,93,NULL,'Thermostatic Shower Mixer With Body & Trim','TDS60','69101000',NULL,'Z1',100.00,40.00,60.00,50,0,3000.00,'2026-06-08 13:11:31'),(27,14,162,NULL,'Freestanding Bathtub Facucets','DF-02019',NULL,NULL,'Z1',32355.00,40.00,19413.00,1000,1000,19413000.00,'2026-06-08 13:13:28'),(28,15,163,NULL,'Freestanding Bathtub Facucets','DF-02017-2',NULL,NULL,'Z1',32050.00,40.00,19230.00,1,0,19230.00,'2026-06-08 13:14:09'),(32,17,93,NULL,'Thermostatic Shower Mixer With Body & Trim','TDS 60','69101000',NULL,'Z1',100.00,40.00,60.00,10,5,600.00,'2026-06-26 06:17:07'),(33,17,165,NULL,'Drop In Bathtub Kit','505510','69101000',NULL,'Z1',2840.00,40.00,1704.00,10,5,17040.00,'2026-06-26 06:17:07'),(34,17,158,NULL,'Freestanding Bathtubs','6809',NULL,NULL,'Z1',73595.00,40.00,44157.00,10,5,441570.00,'2026-06-26 06:17:39');
/*!40000 ALTER TABLE `sales_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_orders`
--

DROP TABLE IF EXISTS `sales_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(30) NOT NULL COMMENT 'ORD-YYYY-#### (auto-generated)',
  `party_id` int(10) unsigned NOT NULL,
  `party_group_name` varchar(50) DEFAULT NULL COMMENT 'Snapshot: Dealer/Distributor/Retailer/Builder/Architect',
  `placed_by_type` enum('Party','TSM','Sales_Team','Admin') NOT NULL,
  `placed_by_id` int(10) unsigned NOT NULL,
  `base_discount_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `extra_discount_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `volume_bonus_used` tinyint(1) DEFAULT 0 COMMENT 'Was extra discount applied at order time?',
  `total_mrp` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `net_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `admin_override` tinyint(1) DEFAULT 0,
  `admin_override_reason` text DEFAULT NULL,
  `admin_override_by` int(10) unsigned DEFAULT NULL,
  `status` enum('Placed','Approved','Partial_Dispatch','Dispatched','Delivered','Cancelled') NOT NULL DEFAULT 'Placed',
  `approved_by` int(10) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `cancelled_by_type` enum('Party','TSM','Sales_Team','Admin') DEFAULT NULL,
  `cancelled_by_id` int(10) unsigned DEFAULT NULL,
  `cancelled_reason` text DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `short_closed` tinyint(1) NOT NULL DEFAULT 0,
  `short_close_reason` text DEFAULT NULL,
  `short_closed_by` int(10) unsigned DEFAULT NULL,
  `short_closed_at` datetime DEFAULT NULL,
  `delivery_address` text DEFAULT NULL,
  `delivery_city` varchar(100) DEFAULT NULL,
  `delivery_state` varchar(100) DEFAULT NULL,
  `delivery_district` varchar(100) DEFAULT NULL,
  `delivery_pincode` varchar(10) DEFAULT NULL,
  `same_as_registered` tinyint(1) DEFAULT 1,
  `party_notes` text DEFAULT NULL COMMENT 'Notes from dealer/TSM at placement',
  `admin_notes` text DEFAULT NULL COMMENT 'Internal admin-only notes',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `idx_so_party` (`party_id`),
  KEY `idx_so_status` (`status`),
  KEY `idx_so_date` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_orders`
--

LOCK TABLES `sales_orders` WRITE;
/*!40000 ALTER TABLE `sales_orders` DISABLE KEYS */;
INSERT INTO `sales_orders` VALUES (1,'ORD-2026-0001',115,'Architect','Admin',1,40.00,6.00,0,10000.00,4000.00,6000.00,0,NULL,NULL,'Delivered',1,'2026-05-22 13:23:57',NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,'1108/1109 RK Prime, Nana Mava Main Road, Rajkot 360005','Jamnagar','Gujarat','Jamnagar','360006',1,'First Order','First Order Admin Note','2026-05-22 07:23:06','2026-06-08 07:47:54'),(2,'ORD-2026-0002',115,'Architect','Admin',1,40.00,6.00,0,286550.00,114620.00,171930.00,0,NULL,NULL,'Delivered',1,'2026-05-22 13:52:04',NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,'1108/1109 RK Prime, Nana Mava Main Road, Rajkot 360005','Jamnagar','Gujarat','Jamnagar','360006',1,NULL,NULL,'2026-05-22 08:22:01','2026-06-08 06:38:48'),(3,'ORD-2026-0003',116,'Builder','Admin',1,40.00,6.00,0,573100.00,229240.00,343860.00,0,NULL,NULL,'Delivered',1,'2026-05-22 18:40:00',NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,'1108/1109 RK Prime, Nana Mava Main Road, Rajkot 360005','Nashik','Maharashtra','Nashik','360044',1,'order done','admin note order','2026-05-22 13:09:02','2026-05-22 13:17:32'),(4,'ORD-2026-0004',115,'Architect','Admin',1,40.00,6.00,0,1132580.00,453032.00,679548.00,0,NULL,NULL,'Placed',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,'1108/1109 RK Prime, Nana Mava Main Road, Rajkot 360005','Jamnagar','Gujarat','Jamnagar','360006',1,NULL,NULL,'2026-05-26 05:20:08','2026-05-26 05:29:50'),(5,'ORD-2026-0005',115,'Architect','Admin',1,40.00,6.00,0,574000.00,229600.00,344400.00,0,NULL,NULL,'Delivered',1,'2026-06-02 17:06:39',NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,'1108/1109 RK Prime, Nana Mava Main Road, Rajkot 360005','Jamnagar','Gujarat','Jamnagar','360006',1,NULL,NULL,'2026-06-02 11:36:36','2026-06-02 11:37:52'),(6,'ORD-2026-0006',115,'Architect','Admin',1,40.00,6.00,0,59800.00,23920.00,35880.00,0,NULL,NULL,'Delivered',1,'2026-06-02 17:22:47',NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,'1108/1109 RK Prime, Nana Mava Main Road, Rajkot 360005','Jamnagar','Gujarat','Jamnagar','360006',1,NULL,NULL,'2026-06-02 11:52:14','2026-06-08 07:25:19'),(7,'ORD-2026-0007',115,'Architect','Admin',1,40.00,6.00,0,58110.00,23244.00,34866.00,0,NULL,NULL,'Delivered',2,'2026-06-08 13:47:22',NULL,NULL,NULL,NULL,1,'sdfasdf',2,'2026-06-08 17:13:52','1108/1109 RK Prime, Nana Mava Main Road, Rajkot 360005','Jamnagar','Gujarat',NULL,'360006',1,NULL,NULL,'2026-06-08 08:00:25','2026-06-08 11:44:27'),(8,'ORD-2026-0008',116,'Builder','Admin',1,40.00,6.00,0,73150.00,29260.00,43890.00,0,NULL,NULL,'Cancelled',NULL,NULL,'Sales_Team',2,'asdfasdf','2026-06-08 13:49:36',0,NULL,NULL,NULL,'1108/1109 RK Prime, Nana Mava Main Road, Rajkot 360005','Nashik','Maharashtra',NULL,'360044',1,NULL,NULL,'2026-06-08 08:00:25','2026-06-08 08:19:36'),(9,'ORD-2026-0009',118,'Dealer','Admin',1,40.00,6.00,0,5000.00,2000.00,3000.00,0,NULL,NULL,'Delivered',2,'2026-06-08 15:53:23',NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,'150 Ft Ring Road','Jamnagar','Gujarat',NULL,'365050',1,NULL,NULL,'2026-06-08 08:00:25','2026-06-08 12:51:48'),(10,'ORD-2026-0010',119,'Distributor','Admin',1,40.00,6.00,0,285075.00,114030.00,171045.00,0,NULL,NULL,'Approved',1,'2026-06-08 17:21:04',NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'Rajkot','Gujarat',NULL,NULL,1,NULL,NULL,'2026-06-08 08:00:25','2026-06-08 11:51:04'),(11,'ORD-2026-0011',120,'Retailer','Admin',1,40.00,6.00,0,2000.00,800.00,1200.00,0,NULL,NULL,'Approved',2,'2026-06-08 16:34:22',NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'Rajkot','Gujarat',NULL,NULL,1,NULL,NULL,'2026-06-08 08:00:25','2026-06-08 11:04:49'),(12,'ORD-2026-0012',118,'Dealer','TSM',99,40.00,6.00,0,289000.00,115600.00,173400.00,0,NULL,NULL,'Delivered',2,'2026-06-08 18:33:24',NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,'150 Ft Ring Road','Jamnagar','Gujarat',NULL,'365050',1,'take order on shah bhat mart',NULL,'2026-06-08 13:02:54','2026-06-08 13:08:59'),(13,'ORD-2026-0013',118,'Dealer','Party',118,40.00,6.00,0,5000.00,2000.00,3000.00,0,NULL,NULL,'Placed',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,'150 Ft Ring Road','Jamnagar','Gujarat',NULL,'365050',1,NULL,NULL,'2026-06-08 13:11:31','2026-06-08 13:12:16'),(14,'ORD-2026-0014',118,'Dealer','TSM',99,40.00,6.00,0,32355000.00,12942000.00,19413000.00,0,NULL,NULL,'Delivered',2,'2026-06-08 18:45:02',NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,'150 Ft Ring Road','Jamnagar','Gujarat',NULL,'365050',1,NULL,NULL,'2026-06-08 13:13:28','2026-06-08 13:15:55'),(15,'ORD-2026-0015',118,'Dealer','Admin',2,40.00,6.00,0,32050.00,12820.00,19230.00,0,NULL,NULL,'Approved',2,'2026-06-08 18:44:12',NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,'150 Ft Ring Road','Jamnagar','Gujarat',NULL,'365050',1,NULL,NULL,'2026-06-08 13:14:09','2026-06-08 13:14:12'),(17,'ORD-2026-0016',115,'Architect','Admin',1,40.00,6.00,0,765350.00,306140.00,459210.00,0,NULL,NULL,'Partial_Dispatch',1,'2026-06-26 11:48:21',NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,'1108/1109 RK Prime, Nana Mava Main Road, Rajkot 360005','Jamnagar','Gujarat','Jamnagar','360006',1,'asdfasfd','asdfasfd','2026-06-26 06:17:07','2026-06-26 06:21:56');
/*!40000 ALTER TABLE `sales_orders` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_order_number` BEFORE INSERT ON `sales_orders` FOR EACH ROW BEGIN
              IF NEW.order_number IS NULL OR NEW.order_number = '' THEN
                SET NEW.order_number = CONCAT(
                  'ORD-', YEAR(NOW()), '-',
                  LPAD(
                    (SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(order_number, '-', -1) AS UNSIGNED)), 0) + 1
                     FROM sales_orders
                     WHERE YEAR(created_at) = YEAR(NOW())),
                    4, '0')
                );
              END IF;
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('active_birthday_template_id','51','2025-12-12 07:07:07'),('api_version','v21.0','2025-12-05 12:51:00'),('company_about','<p><strong>X-Tral</strong> was founded with a singular belief ÔÇö that a bathroom is more than a functional space. It is a personal retreat where design, comfort, and reliability must come together seamlessly.</p><p><br></p><p>Every product we create is shaped by this philosophy, ensuring that our customers and partners experience meaningful value at every step.</p><p>Headquartered in Nashik, Maharashtra, our operations span manufacturing, assembling, and marketing ÔÇö delivering uncompromising quality with exceptional value.</p>','2026-07-03 12:30:13'),('company_address','<p>OFFICE NO. 101,</p><p>BENCHMARK COMPLEX,</p><p>RAVISHANKAR MARG ,</p><p>NASHIK - 422006 (MAHARASHTRA)</p>','2026-07-02 12:52:44'),('company_email','xtralcare@gmail.com','2026-07-03 12:30:13'),('company_gst_no','27AAJCE0475D1ZS','2026-06-08 13:24:59'),('company_name','X-Tral','2026-07-02 12:52:44'),('company_state','Maharashtra','2026-06-08 13:24:59'),('company_toll_free','+918888881898','2026-06-08 13:24:59'),('company_website','https://x-tral.com','2026-07-03 12:30:13'),('company_whatsapp','+918888881898','2026-06-08 13:24:59'),('wa_access_token','EAAdZCb8X2t1kBR3ZB4pkKSHw2MJSbVvqkv4rEXfTQE5eZBlO2yV9B6VykaQwnVo0BQdfoFZBxHgi5eyRB56cNVM659nmpSgXkVTxxlKmBytu1cTblHkGgUCZAO4sjtJKl0CNGj577NmmyStL8OcF0kiZAhiHCLwQPpexYCLNwiWLEm8hzPZCNXIS0ffi0FeNAZDZD','2026-06-25 10:36:04'),('wa_business_id','1699519941119342','2026-06-25 10:36:04'),('wa_phone_id','1097053800168040','2026-06-25 10:36:04'),('webhook_callback_url','https://unhubristic-ridable-melita.ngrok-free.dev/eleganzaadmin/webhook.php','2026-06-09 06:54:47'),('webhook_verify_token','eleganzaadmin_webhook_2026','2026-06-09 10:50:45');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `state_zones`
--

DROP TABLE IF EXISTS `state_zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `state_zones` (
  `state` varchar(100) NOT NULL,
  `zone_code` enum('Z1','Z2') NOT NULL DEFAULT 'Z1',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `state_zones`
--

LOCK TABLES `state_zones` WRITE;
/*!40000 ALTER TABLE `state_zones` DISABLE KEYS */;
INSERT INTO `state_zones` VALUES ('Andaman and Nicobar Islands','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Andhra Pradesh','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Arunachal Pradesh','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Assam','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Bihar','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Chandigarh','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Chhattisgarh','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Dadra and Nagar Haveli and Daman and Diu','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Delhi','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Goa','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Gujarat','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Haryana','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Himachal Pradesh','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Jammu and Kashmir','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Jharkhand','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Karnataka','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Kerala','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Ladakh','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Lakshadweep','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Madhya Pradesh','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Maharashtra','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Manipur','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Meghalaya','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Mizoram','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Nagaland','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Odisha','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Puducherry','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Punjab','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Rajasthan','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Sikkim','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Tamil Nadu','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Telangana','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Tripura','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Uttar Pradesh','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('Uttarakhand','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03'),('West Bengal','Z1','2026-04-30 13:44:03','2026-04-30 13:44:03');
/*!40000 ALTER TABLE `state_zones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `templates`
--

DROP TABLE IF EXISTS `templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `templates` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `language` varchar(10) DEFAULT 'en',
  `status` varchar(20) DEFAULT 'APPROVED',
  `category` varchar(50) DEFAULT 'MARKETING',
  `components` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`components`)),
  `synced_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=176 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `templates`
--

LOCK TABLES `templates` WRITE;
/*!40000 ALTER TABLE `templates` DISABLE KEYS */;
INSERT INTO `templates` VALUES (167,'complaint_closed_success_08','en','APPROVED','UTILITY','[{\"type\":\"BODY\",\"text\":\"\\u2705 Complaint Resolved Successfully\\n\\nTicket: {{1}}\\n\\nYour complaint has been closed. Thank you for choosing Eleganza Bathware!\\n\\nFor any new issues, you can register another complaint anytime.\\n\\nWe hope you had a great service experience.\",\"example\":{\"body_text\":[[\"ELG-2026-0001\"]]}},{\"type\":\"FOOTER\",\"text\":\"Eleganza Bathware\"}]','2026-06-25 10:40:23'),(168,'complaint_solvedreference_07','en','APPROVED','UTILITY','[{\"type\":\"BODY\",\"text\":\"Service completion details.\\nTicket: {{1}}\\nTotal cost: Rs.{{2}}\\nLabor: Rs.{{3}}\\nParts: Rs.{{4}}\\nPayment: {{5}}\\nReference number: {{6}}\\nShare this reference number with the technician to manually sign off on the work.\",\"example\":{\"body_text\":[[\"ELG-2026-0001\",\"500\",\"200\",\"300\",\"Cash\",\"600400\"]]}},{\"type\":\"FOOTER\",\"text\":\"Eleganza Bathware\"}]','2026-06-25 10:40:23'),(169,'complaint_in_progress_06','en','APPROVED','UTILITY','[{\"type\":\"BODY\",\"text\":\"\\ud83d\\udee0\\ufe0f Work Started\\n\\nTicket: {{1}}\\nTechnician: {{2}}\\n\\nOur technician has started working on your complaint. We will share the completion details shortly.\\n\\nThank you for your patience.\",\"example\":{\"body_text\":[[\"ELG-2026-0001\",\"Suresh Patil\"]]}},{\"type\":\"FOOTER\",\"text\":\"Eleganza Bathware\"}]','2026-06-25 10:40:23'),(170,'complaint_accepted_05','en','APPROVED','UTILITY','[{\"type\":\"BODY\",\"text\":\"\\u2705 Technician Accepted Your Complaint\\n\\nTicket: {{1}}\\nTechnician: {{2}}\\nMobile: {{3}}\\nVisit scheduled: {{4}}\\n\\nOur technician will reach you at the scheduled time. Please keep someone available at the location.\\n\\nThank you for choosing Eleganza Bathware!\",\"example\":{\"body_text\":[[\"ELG-2026-0001\",\"Suresh Patil\",\"+91 99887 76655\",\"15 Jun 2026, 10:30 AM\"]]}},{\"type\":\"FOOTER\",\"text\":\"Eleganza Bathware\"}]','2026-06-25 10:40:23'),(171,'complaint_cancelled_04','en','APPROVED','UTILITY','[{\"type\":\"BODY\",\"text\":\"Complaint Cancelled\\n\\nTicket: {{1}}\\nReason: {{2}}\\n\\nIf you still need assistance, please register a new complaint.\\n\\nThank you for choosing Eleganza Bathware!\",\"example\":{\"body_text\":[[\"ELG-2026-0001\",\"Customer resolved issue independently\"]]}},{\"type\":\"FOOTER\",\"text\":\"Eleganza Bathware\"}]','2026-06-25 10:40:23'),(172,'complaint_assigned_customer_03','en','APPROVED','UTILITY','[{\"type\":\"BODY\",\"text\":\"Your complaint has been assigned!\\n\\nTicket: {{1}}\\nTechnician: {{2}} ({{3}})\\n\\nOur technician will contact you within 24 hours to schedule a visit.\\n\\nThank you for choosing Eleganza Bathware!\",\"example\":{\"body_text\":[[\"ELG-2026-0001\",\"Suresh Patil\",\"+91 99887 76655\"]]}},{\"type\":\"FOOTER\",\"text\":\"Eleganza Bathware\"}]','2026-06-25 10:40:23'),(173,'complaint_assigned_plumber_02','en','APPROVED','UTILITY','[{\"type\":\"BODY\",\"text\":\"New Complaint Assigned!\\n\\nTicket: {{1}}\\nCategory: {{2}}\\nCustomer: {{3}} ({{4}})\\nLocation: {{5}}\\nPriority: {{6}}\\n\\nPlease contact the customer and schedule a visit.\",\"example\":{\"body_text\":[[\"ELG-2026-0001\",\"Bath Fittings Repair\",\"Rajesh Kumar\",\"+91 98765 43210\",\"Nashik, Maharashtra\",\"High\"]]}},{\"type\":\"FOOTER\",\"text\":\"Eleganza Bathware\"}]','2026-06-25 10:40:23'),(174,'complaint_registered_01','en','APPROVED','UTILITY','[{\"type\":\"BODY\",\"text\":\"\\u2705 Complaint Registered Successfully\\n\\nTicket: {{1}}\\nCategory: {{2}}\\n\\nWe will assign a technician soon and keep you updated.\\n\\nThank you for choosing Eleganza Bathware!\",\"example\":{\"body_text\":[[\"ELG-2026-0001\",\"Sanitaryware Repair\"]]}},{\"type\":\"FOOTER\",\"text\":\"Eleganza Bathware\"}]','2026-06-25 10:40:23'),(175,'hello_world','en_US','APPROVED','UTILITY','[{\"type\":\"HEADER\",\"format\":\"TEXT\",\"text\":\"Hello World\"},{\"type\":\"BODY\",\"text\":\"Welcome and congratulations!! This message demonstrates your ability to send a WhatsApp message notification from the Cloud API, hosted by Meta. Thank you for taking the time to test with us.\"},{\"type\":\"FOOTER\",\"text\":\"WhatsApp Business Platform sample message\"}]','2026-06-25 10:40:23');
/*!40000 ALTER TABLE `templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `whatsapp_catalogues`
--

DROP TABLE IF EXISTS `whatsapp_catalogues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `whatsapp_catalogues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'Displayed on bot list + website tile. Max 24 chars enforced in UI for WhatsApp list constraint.',
  `pdf_filename` varchar(255) NOT NULL COMMENT 'Filename only, no path. Lives at uploads/catalogue/pdfs/<pdf_filename>.',
  `thumb_filename` varchar(255) DEFAULT NULL COMMENT 'Filename only, no path. Lives at uploads/catalogue/pdfs/Thumb/<thumb_filename>. NULL ÔåÆ website shows placeholder.',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0 hides from both bot list and website. PDF file is NOT deleted from disk so existing WhatsApp message links keep working.',
  `display_order` int(11) NOT NULL DEFAULT 0 COMMENT 'Lower = first. Drag-reorder in admin UI.',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_active_order` (`is_active`,`display_order`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsapp_catalogues`
--

LOCK TABLES `whatsapp_catalogues` WRITE;
/*!40000 ALTER TABLE `whatsapp_catalogues` DISABLE KEYS */;
INSERT INTO `whatsapp_catalogues` VALUES (1,'Sanitarywares Catalogue','Sanitarywares Catalogue.pdf','Sanitarywares Catalogue.png',0,1,'2026-07-02 18:27:58','2026-07-03 14:36:01'),(2,'Bath Fittings Catalogue','Bell_bath_fittings.pdf',NULL,0,2,'2026-07-02 18:27:58','2026-07-03 14:36:03'),(3,'Kitchen Sinks Catalogue','Bell_kitchen_sinks.pdf',NULL,0,3,'2026-07-02 18:27:58','2026-07-03 14:36:02'),(4,'Wellness Catalogue','Bell_wellness.pdf',NULL,0,4,'2026-07-02 18:27:58','2026-07-03 14:36:04'),(5,'PTMT Faucets Catalogue','Bell_ptmt_faucets.pdf',NULL,0,5,'2026-07-02 18:27:58','2026-07-03 14:36:05'),(6,'X-Tral Catalogue','X-Tral Catalogue.pdf','X-Tral Catalogue.png',1,6,'2026-07-03 14:35:46','2026-07-03 18:02:32');
/*!40000 ALTER TABLE `whatsapp_catalogues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'xtral'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-04 15:11:15

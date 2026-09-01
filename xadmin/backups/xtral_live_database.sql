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
INSERT INTO `admin_users` VALUES (1,'admin','Admin','Admin@gmail.com',NULL,'super_admin',NULL,NULL,1,'2026-07-06 10:35:29','2026-07-06 05:05:29','$2y$10$vojT1bY0roAQ.4MDsG4Tq.2KeyBAe/xP3xs9ygwrRyoFuATBiNhlm','2025-12-05 12:51:00'),(2,'testsales','Test Sales','testsale@gmail.com','9033555666','super_admin',NULL,NULL,1,'2026-06-08 18:37:00','2026-07-04 12:19:14','$2y$10$Tc/9MqV0FjonzgySTu4Rd.WX4OGs9T1F0/iOl8nyflguq8xv8XL8K','2026-04-04 12:01:56'),(3,'dispatch','Test Dispatch Manager','dispatch@test.com',NULL,'super_admin',NULL,NULL,1,'2026-06-08 18:34:46','2026-07-04 12:19:14','$2y$10$RfFpxLOXAbRtv9CvCs8yMei6ZtiYghLn0wHDyl/JYrHBktTl.wmR6','2026-06-08 07:52:51'),(4,'mis','Test MIS Viewer','mis@test.com',NULL,'super_admin',NULL,NULL,1,'2026-06-08 13:40:31','2026-07-04 12:19:14','$2y$10$oCYs3hy9bMVZ/a9QJbPVy.iftA3c99jSS8vWvaIylWJUA8tAha9ya','2026-06-08 07:52:51'),(5,'support','Test Support Team','support@test.com',NULL,'super_admin',NULL,NULL,1,'2026-06-08 17:22:04','2026-07-04 12:19:14','$2y$10$5KlfGTfmDSkrUVy9kG4N/uEOwWUSPSpzZfOITnXo1G4d.AdR7uSwC','2026-06-08 08:30:40');
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=339 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalogue_product_images`
--

LOCK TABLES `catalogue_product_images` WRITE;
/*!40000 ALTER TABLE `catalogue_product_images` DISABLE KEYS */;
INSERT INTO `catalogue_product_images` VALUES (309,156,'prod_1773412834_69b421e227b5f.jpg',1,1,'2026-03-13 14:40:34'),(310,157,'prod_1773412834_69b421e2293fc.jpg',1,1,'2026-03-13 14:40:34'),(311,158,'prod_1773412834_69b421e22ab79.jpg',1,1,'2026-03-13 14:40:34'),(312,159,'prod_1773412834_69b421e22c330.jpg',1,1,'2026-03-13 14:40:34'),(313,160,'prod_1773412834_69b421e22dd85.jpg',1,1,'2026-03-13 14:40:34'),(314,161,'prod_1773412834_69b421e22f489.jpg',1,1,'2026-03-13 14:40:34'),(315,162,'prod_1773412834_69b421e230a57.jpg',1,1,'2026-03-13 14:40:34'),(316,163,'prod_1773412834_69b421e23233a.jpg',1,1,'2026-03-13 14:40:34'),(317,164,'prod_1773412834_69b421e233a9f.jpg',1,1,'2026-03-13 14:40:34'),(318,165,'prod_1773412834_69b421e234fab.jpg',1,1,'2026-03-13 14:40:34'),(322,169,'prod_1774071555_69be2f034aa0c.png',1,1,'2026-03-21 05:39:15'),(326,93,'6996e6c9b582d_1771497161.jpg',1,1,'2026-04-22 13:30:30'),(327,93,'prod_1771913763_699d4223dcc5e.png',0,2,'2026-04-22 13:30:30'),(328,94,'6996e6f701661_1771497207.jpg',1,1,'2026-04-22 13:30:30'),(329,173,'prod_1776864630_69e8cd768485a.jpg',1,1,'2026-04-22 13:30:30'),(330,174,'69f2380032e16_1777481728.jpg',1,0,'2026-04-29 16:55:28'),(331,175,'6a4b299ca2f8c_1783310748.png',1,0,'2026-07-06 04:05:48'),(332,175,'6a4b299ca3cf6_1783310748.png',0,1,'2026-07-06 04:05:48'),(333,175,'6a4b299ca4448_1783310748.png',0,2,'2026-07-06 04:05:48'),(334,175,'6a4b299ca4c11_1783310748.png',0,3,'2026-07-06 04:05:48'),(335,176,'6a4b2aa67d9c4_1783311014.png',1,0,'2026-07-06 04:10:14'),(336,176,'6a4b2aa67e2fc_1783311014.png',0,1,'2026-07-06 04:10:14'),(337,176,'6a4b2aa67eb0a_1783311014.png',0,2,'2026-07-06 04:10:14'),(338,176,'6a4b2aa67f581_1783311014.png',0,3,'2026-07-06 04:10:14');
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
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalogue_product_variants`
--

LOCK TABLES `catalogue_product_variants` WRITE;
/*!40000 ALTER TABLE `catalogue_product_variants` DISABLE KEYS */;
INSERT INTO `catalogue_product_variants` VALUES (31,169,2,'','D001',NULL,2135.00,NULL,'Matt Red','prod_1774071555_69be2f034b1a4.png',0,1),(32,169,11,'','D001',NULL,2135.00,NULL,'Matt Dark Blue','prod_1774071555_69be2f034b419.png',1,1),(33,169,11,'','D001',NULL,2135.00,NULL,'Matt Black','prod_1774071555_69be2f034bfe7.png',2,1),(34,169,11,'','D001',NULL,2135.00,NULL,'Matt Blue','prod_1774071555_69be2f034c55b.png',3,1),(35,169,11,'','D001',NULL,2135.00,NULL,'Matt Dark Red','prod_1774071555_69be2f034ca22.png',4,1),(36,169,11,'','D001',NULL,2135.00,NULL,'Matt Brown','prod_1774071555_69be2f034d0cd.png',5,1),(37,169,11,'','D001',NULL,2135.00,NULL,'Matt Orange','prod_1774071555_69be2f034d699.png',6,1),(38,169,11,'','D001',NULL,2135.00,NULL,'Glossy Red','prod_1774071555_69be2f034dad6.png',7,1),(39,169,11,'','D001',NULL,2135.00,NULL,'Glossy Blue','prod_1774071555_69be2f034df23.png',8,1),(40,174,NULL,'varient1','po001',NULL,25000.00,NULL,'25mm','69f238003311a_1777481728.jpg',0,1),(41,174,NULL,'varient2','po002',NULL,25500.00,NULL,'35mm','69f2380033360_1777481728.jpg',1,1),(42,175,NULL,'Demo1','DP-001',NULL,1000.00,NULL,'25x25x25MM','6a4b299ca5615_1783310748.png',0,1),(43,175,NULL,'Demo2','DP-002',NULL,2000.00,NULL,'20x20\"','6a4b299ca5e10_1783310748.png',1,1),(44,175,NULL,'Demo3','DP-003',NULL,5000.00,NULL,'20\"x20\"','6a4b299ca694c_1783310748.png',2,1),(45,176,2,'','001',NULL,1000.00,NULL,'Blue','6a4b2aa68060b_1783311014.jpg',0,1),(46,176,12,'','002',NULL,10000.00,NULL,'Burgundy','6a4b2aa6811a2_1783311014.jpg',1,1),(47,176,1,'','003',NULL,100.00,NULL,'Crystal White','6a4b2aa681c5b_1783311014.jpg',2,1),(48,176,8,'','004',NULL,200.00,NULL,'Peach Magenta','6a4b2aa68301a_1783311014.png',3,1),(49,176,6,'','005',NULL,30000.00,NULL,'Royal Grey','6a4b2aa6839eb_1783311014.png',4,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=177 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalogue_products`
--

LOCK TABLES `catalogue_products` WRITE;
/*!40000 ALTER TABLE `catalogue_products` DISABLE KEYS */;
INSERT INTO `catalogue_products` VALUES (93,23,'Thermostatic Shower Mixer With Body & Trim','TDS 60','69101000',100,NULL,'1850 x 1230 x 620 mm','<p>Thermostatic Shower Mixer With Body &amp; Trim&nbsp;</p>',1,'none','colour',1,1,'2026-02-19 10:32:41','2026-07-06 04:13:40'),(94,23,'Thermostatic Shower Mixer With Body & Trim','TDR 50','69101000',28555,NULL,'','<p>Thermostatic Shower Mixer With Body &amp; Trim</p>',1,'none','colour',2,1,'2026-02-19 10:33:26','2026-06-23 12:14:46'),(156,37,'Freestanding Bathtubs','EW6312',NULL,71150,80555,'1600 x 700 x 670 mm','Material : Acrylic<br>Features : All White Color and Silver Legs<br>Truly European Design <br>100% Quality Acrylic with fiberglass reinforced <br>Double layer, Seamless Design <br>Pop-up drainer<br>Chrome finished legs',1,'none','colour',1,0,'2026-03-13 14:40:34','2026-03-13 14:40:34'),(157,37,'Freestanding Bathtubs','6515',NULL,73595,83555,'1600 x 750 x 600 mm','Material : Acrylic <br>Features : Truly European Design<br>100% Quality Acrylic material<br>Seamless Design, high glossy both side<br>Stainless Steel frame undertub<br>Pop-up drainer with overflow',1,'none','colour',2,0,'2026-03-13 14:40:34','2026-03-13 14:40:34'),(158,37,'Freestanding Bathtubs','6809',NULL,73595,83555,'1600 x 750 x 580 mm','Material : Acrylic<br>Features :Truly European Design<br>100% Quality Acrylic material<br>Seamless Design, high glossy both side<br>Stainless Steel frame undertub<br>Pop-up drainer with overflow',1,'none','colour',3,0,'2026-03-13 14:40:34','2026-03-13 14:40:34'),(159,37,'Freestanding Bathtubs','6509',NULL,83405,94555,'1600 x 800 x 640 mm','Material : Acrylic <br>Features :<br>Truly European Design<br>100% Quality Acrylic material<br>Seamless Design, high glossy both side<br>Stainless Steel frame undertub<br>Pop-up drainer with overflow',1,'none','colour',4,0,'2026-03-13 14:40:34','2026-03-13 14:40:34'),(160,37,'Freestanding Bathtubs','6813B',NULL,83405,94555,'1600 x 800 x 600 mm','Material : Acrylic <br>Features :<br>Truly European Design<br>100% Quality Acrylic material<br>Seamless Design, high glossy both side<br>Stainless Steel frame undertub<br>Pop-up drainer with overflow',1,'none','colour',5,0,'2026-03-13 14:40:34','2026-03-13 14:40:34'),(161,37,'Freestanding Bathtubs','6829',NULL,76050,85955,'1700 x 700 x 570 mm','Material : Acrylic <br>Features :<br>Truly European Design<br>100% Quality Acrylic material<br>Seamless Design, high glossy both side<br>Stainless Steel frame undertub<br>Pop-up drainer with overflow',1,'none','colour',6,0,'2026-03-13 14:40:34','2026-03-13 14:40:34'),(162,39,'Freestanding Bathtub Facucets','DF-02019',NULL,32355,32355,'','',1,'none','colour',1,0,'2026-03-13 14:40:34','2026-03-13 14:40:34'),(163,39,'Freestanding Bathtub Facucets','DF-02017-2',NULL,32050,32050,'','',1,'none','colour',2,0,'2026-03-13 14:40:34','2026-03-13 14:40:34'),(164,39,'Freestanding Bathtub Facucets','DF - 02011',NULL,35000,37850,'','',1,'none','colour',3,0,'2026-03-13 14:40:34','2026-06-08 13:18:30'),(165,41,'Drop In Bathtub Kit','505510','69101000',2840,2840,'','',1,'none','colour',1,0,'2026-03-13 14:40:34','2026-05-20 07:51:55'),(169,42,'Ceramic Pop-up Waste Coupling',NULL,NULL,0,NULL,'','',1,'color','colour',1,1,'2026-03-21 05:39:15','2026-03-30 15:17:50'),(173,23,'new product name','TDR511','69101000',10000,NULL,'1850 x 1230 x 620 mm','<p>Thermostatic Shower Mixer With Body &amp; Trim&nbsp;</p>',1,'none','colour',3,0,'2026-04-22 13:30:30','2026-05-20 07:51:40'),(174,23,'Sample product',NULL,'69101000',0,NULL,NULL,'<p><span style=\"color: rgb(73, 80, 87);\">Technical SpecificationsTechnical SpecificationsTechnical Specifications</span></p>',1,'size','colour',7,1,'2026-04-29 16:55:28','2026-05-20 07:51:48'),(175,23,'Demo Products',NULL,'DP-001',0,NULL,NULL,'<ul><li>That is Demo product for a testing a size variation there add a three size,</li><li>That is Demo product for a testing a size variation there add a three size,That is Demo product for a testing a size variation there add a three size</li><li><u>That is Demo product for a testing a size variation there add a three size</u></li></ul><ol><li><em>That is Demo product for a testing a size variation there add a three size</em></li><li><strong>That is Demo product for a testing a size variation there add a three size</strong></li></ol>',1,'size','colour',8,0,'2026-07-06 04:05:48','2026-07-06 04:05:48'),(176,23,'Demo Colour',NULL,'DC-001',0,NULL,'15x15','<p>That is Demo product for a testing a size variation there add a three size</p><p>That is Demo product for a testing a size variation there add a three sizeThat is Demo product for a testing a size variation there add a three size</p><p>That is Demo product for a testing a size variation there add a three sizeThat is Demo product for a testing a size variation there add a three size</p><p>That is Demo product for a testing a size variation there add a three size</p><p>That is Demo product for a testing a size variation there add a three size</p><p>That is Demo product for a testing a size variation there add a three sizeThat is Demo product for a testing a size variation there add a three size</p>',1,'color','colour',9,0,'2026-07-06 04:10:14','2026-07-06 04:10:14');
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
INSERT INTO `settings` VALUES ('active_birthday_template_id','51','2025-12-12 07:07:07'),('api_version','v21.0','2025-12-05 12:51:00'),('company_address','<p>City,State,Pincode</p>','2026-07-06 04:01:14'),('company_email','xtralcare@gmail.com','2026-07-03 12:30:13'),('company_logo','logo_1783159399.png','2026-07-04 10:03:19'),('company_name','X-Tral','2026-07-02 12:52:44'),('company_toll_free','+917200536353','2026-07-06 04:37:45'),('company_website','','2026-07-06 04:37:45'),('company_whatsapp','+918+917200536353888881898','2026-07-06 04:37:45'),('wa_access_token','EAAdZCb8X2t1kBR3ZB4pkKSHw2MJSbVvqkv4rEXfTQE5eZBlO2yV9B6VykaQwnVo0BQdfoFZBxHgi5eyRB56cNVM659nmpSgXkVTxxlKmBytu1cTblHkGgUCZAO4sjtJKl0CNGj577NmmyStL8OcF0kiZAhiHCLwQPpexYCLNwiWLEm8hzPZCNXIS0ffi0FeNAZDZD','2026-06-25 10:36:04'),('wa_business_id','1699519941119342','2026-06-25 10:36:04'),('wa_phone_id','1097053800168040','2026-06-25 10:36:04'),('webhook_callback_url','https://unhubristic-ridable-melita.ngrok-free.dev/eleganzaadmin/webhook.php','2026-06-09 06:54:47'),('webhook_verify_token','eleganzaadmin_webhook_2026','2026-06-09 10:50:45');
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

-- Dump completed on 2026-07-06 10:36:19

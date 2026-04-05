-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: friedchicken_erp
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `building` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `floor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apartment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_user_id_foreign` (`user_id`),
  CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addresses`
--

LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `branches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branches`
--

LOCK TABLES `branches` WRITE;
/*!40000 ALTER TABLE `branches` DISABLE KEYS */;
INSERT INTO `branches` VALUES (1,'ميامي','01206628751','ميامي',NULL,NULL,1,'2026-03-30 21:05:42','2026-03-30 21:05:42');
/*!40000 ALTER TABLE `branches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('fried-chicken-erp-cache-oo.semo90@yahoo.com|172.70.108.50','i:1;',1775313609),('fried-chicken-erp-cache-oo.semo90@yahoo.com|172.70.108.50:timer','i:1775313609;',1775313609);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (14,'ركن الوجبات (The Meals)','rkn-alogbat-the-meals-69d055a9322dd',1,'2026-04-04 07:04:57','2026-04-04 07:04:57'),(15,'ركن السندوتشات (The Burgers)','rkn-alsndotshat-the-burgers-69d055af11a87',1,'2026-04-04 07:05:03','2026-04-04 07:05:03'),(16,'عائلات \"اللمة\" (Buckets)','aaaylat-allm-buckets-69d055b4cba5f',1,'2026-04-04 07:05:08','2026-04-04 07:05:08'),(17,'المقبلات والجانبيات (Sides)','almkblat-oalganbyat-sides-69d055bad73d4',1,'2026-04-04 07:05:14','2026-04-04 07:05:14');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `digital_menu_categories`
--

DROP TABLE IF EXISTS `digital_menu_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `digital_menu_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `digital_menu_setting_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `digital_menu_categories_digital_menu_setting_id_foreign` (`digital_menu_setting_id`),
  CONSTRAINT `digital_menu_categories_digital_menu_setting_id_foreign` FOREIGN KEY (`digital_menu_setting_id`) REFERENCES `digital_menu_settings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `digital_menu_categories`
--

LOCK TABLES `digital_menu_categories` WRITE;
/*!40000 ALTER TABLE `digital_menu_categories` DISABLE KEYS */;
INSERT INTO `digital_menu_categories` VALUES (1,1,'عروض التوفير','aarod-altofyr',0,1,'2026-03-31 21:07:42','2026-03-31 21:07:42');
/*!40000 ALTER TABLE `digital_menu_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `digital_menu_items`
--

DROP TABLE IF EXISTS `digital_menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `digital_menu_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `digital_menu_category_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `badge` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `digital_menu_items_digital_menu_category_id_foreign` (`digital_menu_category_id`),
  CONSTRAINT `digital_menu_items_digital_menu_category_id_foreign` FOREIGN KEY (`digital_menu_category_id`) REFERENCES `digital_menu_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `digital_menu_items`
--

LOCK TABLES `digital_menu_items` WRITE;
/*!40000 ALTER TABLE `digital_menu_items` DISABLE KEYS */;
INSERT INTO `digital_menu_items` VALUES (2,1,'برجر دبل','دبل برجر 2 برجر + بطاطس',150.00,'digital-menu/tLBJ8EIiuyoUw3SwKxuW90dhWNAmcQ4FOvZu4BvC.png','جديد',0,1,'2026-04-04 01:27:39','2026-04-04 01:27:39');
/*!40000 ALTER TABLE `digital_menu_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `digital_menu_settings`
--

DROP TABLE IF EXISTS `digital_menu_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `digital_menu_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Digital Menu',
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'main-menu',
  `subtitle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `show_prices` tinyint(1) NOT NULL DEFAULT '1',
  `show_descriptions` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `digital_menu_settings_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `digital_menu_settings`
--

LOCK TABLES `digital_menu_settings` WRITE;
/*!40000 ALTER TABLE `digital_menu_settings` DISABLE KEYS */;
INSERT INTO `digital_menu_settings` VALUES (1,'Digital Menu','main-menu','new',NULL,NULL,'01206628718',NULL,1,1,1,'2026-03-31 20:59:22','2026-04-04 01:31:17');
/*!40000 ALTER TABLE `digital_menu_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ltm_translations`
--

DROP TABLE IF EXISTS `ltm_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ltm_translations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `status` int NOT NULL DEFAULT '0',
  `locale` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `key` text COLLATE utf8mb4_bin NOT NULL,
  `value` text COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ltm_translations`
--

LOCK TABLES `ltm_translations` WRITE;
/*!40000 ALTER TABLE `ltm_translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `ltm_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_03_30_093113_create_categories_table',1),(5,'2026_03_30_093119_create_products_table',1),(6,'2026_03_30_093123_create_orders_table',1),(7,'2026_03_30_093128_create_order_items_table',1),(8,'2026_03_30_093133_create_settings_table',1),(9,'2026_03_30_101919_add_is_admin_to_users_table',2),(10,'2026_03_30_102358_add_user_id_and_location_fields_to_orders_table',3),(11,'2026_03_30_112748_add_order_number_and_eta_and_status_note_to_orders_table',4),(12,'2026_03_30_112851_create_user_addresses_table',5),(13,'2026_03_30_114128_create_notifications_table',6),(14,'2026_03_30_120306_create_product_option_groups_table',7),(15,'2026_03_30_120407_create_product_option_items_table',7),(16,'2026_03_30_121240_add_selected_options_to_order_items_table',8),(17,'2026_03_30_122910_add_is_seen_by_admin_to_orders_table',9),(18,'2026_03_30_125711_add_logo_and_banner_to_settings_table',10),(19,'2026_03_30_133745_create_branches_table',11),(20,'2026_03_30_133819_add_order_type_and_branch_id_to_orders_table',12),(21,'2026_03_31_081427_add_guest_token_to_orders_table',13),(22,'2026_03_31_000001_create_digital_menu_settings_table',14),(23,'2026_03_31_000002_create_digital_menu_categories_table',14),(24,'2026_03_31_000003_create_digital_menu_items_table',14),(25,'2026_04_01_000100_add_roles_permissions_to_users_table',15),(26,'2026_04_01_000101_add_user_type_to_users_table',16),(27,'2026_04_01_000102_make_role_nullable_in_users_table',16),(28,'2026_04_02_113724_create_popup_campaigns_table',17),(29,'2014_04_02_193005_create_translations_table',18),(30,'2026_04_04_111623_create_addresses_table',19);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES ('ce8f3076-a96c-4c51-9556-47e4cbf13d9c','App\\Notifications\\OrderStatusUpdated','App\\Models\\User',1,'{\"order_id\":39,\"order_number\":\"ORD-00039\",\"status\":\"delivered\",\"status_note\":null,\"message\":\"\\u062a\\u0645 \\u062a\\u062d\\u062f\\u064a\\u062b \\u062d\\u0627\\u0644\\u0629 \\u0627\\u0644\\u0637\\u0644\\u0628 ORD-00039\"}',NULL,'2026-04-01 19:21:56','2026-04-01 19:21:56');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `selected_options` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (57,44,24,'بطاطس بالجبنة',80.00,1,80.00,'[]','2026-04-04 18:05:07','2026-04-04 18:05:07'),(58,44,24,'بطاطس بالجبنة',80.00,1,80.00,'[]','2026-04-04 18:05:07','2026-04-04 18:05:07');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `guest_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_type` enum('delivery','pickup') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'delivery',
  `branch_id` bigint unsigned DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_line` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `area` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_method` enum('cash') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `status` enum('pending','confirmed','preparing','out_for_delivery','delivered','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `estimated_delivery_minutes` int DEFAULT NULL,
  `estimated_delivery_at` timestamp NULL DEFAULT NULL,
  `status_note` text COLLATE utf8mb4_unicode_ci,
  `is_seen_by_admin` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_branch_id_foreign` (`branch_id`),
  CONSTRAINT `orders_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (33,'ORD-00033',NULL,'mLPkzW0mUXsbMT3jfXLqk6Rt5sFmM3JNiRisgark','delivery',NULL,'ابراهيم محمود محمد ابراهيم محمد','01206628718','رشدي , كفر عبده شارع فكهاني النصر , مبني رقم 40 شركه سوليك للاستثمار',NULL,31.2001000,29.9187000,NULL,260.00,30.00,290.00,'cash','confirmed',45,'2026-03-31 20:50:33',NULL,1,'2026-03-31 19:57:16','2026-03-31 20:05:33'),(34,'ORD-00034',NULL,'jO9fnCWwD0R8Z2FbkwhHNhAkBdLWZmmOwfsdnQ2C','delivery',NULL,'hemaxxx mahmoud','120662717','alex',NULL,31.2001000,29.9187000,NULL,130.00,30.00,160.00,'cash','out_for_delivery',45,'2026-03-31 21:12:23',NULL,1,'2026-03-31 20:02:43','2026-03-31 20:27:23'),(35,'ORD-00035',NULL,'oa0Lf8NDPGzigzEeoiZniJtOiCffGRUNkSyDPu0Q','pickup',1,'Ibrah','1206628718','ميامي','ميامي',NULL,NULL,NULL,100.00,0.00,100.00,'cash','delivered',20,'2026-03-31 23:41:01',NULL,1,'2026-03-31 20:15:26','2026-03-31 23:21:01'),(36,'ORD-00036',1,NULL,'pickup',1,'ibrahim','1206628718','ميامي','ميامي',NULL,NULL,NULL,130.00,0.00,130.00,'cash','pending',20,'2026-03-31 22:41:24',NULL,1,'2026-03-31 22:21:24','2026-03-31 23:10:19'),(37,'ORD-00037',NULL,'p53Be9UnYPdG2rsJ7avh4YwJgGaazqCYmNfehXb4','pickup',1,'Finalsa','01206838272','ميامي','ميامي',NULL,NULL,NULL,280.00,0.00,280.00,'cash','confirmed',20,'2026-04-01 16:15:44',NULL,1,'2026-03-31 23:54:10','2026-04-01 15:55:44'),(38,'ORD-00038',1,NULL,'pickup',1,'ibrahim','01206628718','ميامي','ميامي',NULL,NULL,NULL,140.00,0.00,140.00,'cash','pending',20,'2026-04-01 19:40:09',NULL,1,'2026-04-01 19:20:09','2026-04-01 20:30:47'),(39,'ORD-00039',1,NULL,'delivery',NULL,'ibrahim mahmoud mohamed','01206628718','شارع كفر عبده',NULL,31.2001000,29.9187000,NULL,140.00,30.00,170.00,'cash','delivered',45,'2026-04-01 20:06:56',NULL,1,'2026-04-01 19:21:10','2026-04-01 19:21:56'),(40,'ORD-00040',1,NULL,'pickup',1,'ibrahim','120662717','ميامي','ميامي',NULL,NULL,NULL,140.00,0.00,140.00,'cash','pending',20,'2026-04-01 19:57:53',NULL,1,'2026-04-01 19:37:53','2026-04-01 20:23:52'),(41,'ORD-00041',NULL,'kxCGqNmMdZJt5eK4zsLcVSyJboYJ9BtLphrIt6Ah','delivery',NULL,'hemaxxx Soilk mahmoud','01206628718','alex',NULL,31.2001000,29.9187000,NULL,130.00,30.00,160.00,'cash','delivered',45,'2026-04-02 18:27:08',NULL,1,'2026-04-02 17:41:38','2026-04-02 17:42:08'),(42,'ORD-00042',NULL,'WTFBlkWTMwZ0hpjcJMHHRpd2ZS8pIzOK1QkbClEj','delivery',NULL,'ibrahim','01206628178','alexandria',NULL,31.2001000,29.9187000,NULL,280.00,30.00,310.00,'cash','pending',45,'2026-04-03 23:57:06',NULL,1,'2026-04-03 23:12:06','2026-04-04 02:34:26'),(43,'ORD-00043',NULL,'2xeuIb4qje4ZCxeygIjw90RbuJGnJFv4GjrwMYzB','delivery',NULL,'ibrahim','01206628718','8, شارع السيد عبد اللطيف, محمد طه, طنطا, الغربية, 31515, مصر','طنطا',30.7886000,31.0021000,NULL,140.00,30.00,170.00,'cash','confirmed',45,'2026-04-04 01:45:07',NULL,1,'2026-04-04 00:24:22','2026-04-04 01:00:07'),(44,'ORD-00044',NULL,'JxbZCJOrpyCG4KZRi8hD9hcGRGJLUaYmI9aZPKF4','pickup',1,'ibrahim','01206628718','ميامي','ميامي',NULL,NULL,NULL,160.00,0.00,160.00,'cash','pending',20,'2026-04-04 18:25:07',NULL,0,'2026-04-04 18:05:07','2026-04-04 18:05:07');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `popup_campaigns`
--

DROP TABLE IF EXISTS `popup_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `popup_campaigns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `show_once_per_user` tinyint(1) NOT NULL DEFAULT '1',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `popup_campaigns`
--

LOCK TABLES `popup_campaigns` WRITE;
/*!40000 ALTER TABLE `popup_campaigns` DISABLE KEYS */;
INSERT INTO `popup_campaigns` VALUES (1,1,'اعلان تجريبي','تقدر تعمل اعلان زي دا براحتك من لوحه الادمن','popup-campaigns/Bowr6SZWSu59vj4qrR4vqi47YZcbhmkOi80LBJ4I.png','مشاهده','sersa',0,'2026-04-02 20:44:00','2026-04-11 20:44:00','2026-04-02 18:42:33','2026-04-04 05:25:02');
/*!40000 ALTER TABLE `popup_campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_option_groups`
--

DROP TABLE IF EXISTS `product_option_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_option_groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('single','multiple') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'single',
  `is_required` tinyint(1) NOT NULL DEFAULT '0',
  `max_selection` int unsigned DEFAULT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_option_groups_product_id_foreign` (`product_id`),
  CONSTRAINT `product_option_groups_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_option_groups`
--

LOCK TABLES `product_option_groups` WRITE;
/*!40000 ALTER TABLE `product_option_groups` DISABLE KEYS */;
INSERT INTO `product_option_groups` VALUES (3,22,'الصوصات','single',1,1,0,1,'2026-04-04 07:11:17','2026-04-04 07:11:17'),(4,20,'الصوصات','multiple',0,NULL,0,1,'2026-04-04 07:11:40','2026-04-04 07:11:40');
/*!40000 ALTER TABLE `product_option_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_option_items`
--

DROP TABLE IF EXISTS `product_option_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_option_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_option_group_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_option_items_product_option_group_id_foreign` (`product_option_group_id`),
  CONSTRAINT `product_option_items_product_option_group_id_foreign` FOREIGN KEY (`product_option_group_id`) REFERENCES `product_option_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_option_items`
--

LOCK TABLES `product_option_items` WRITE;
/*!40000 ALTER TABLE `product_option_items` DISABLE KEYS */;
INSERT INTO `product_option_items` VALUES (2,4,'صوص الثومية',20.00,0,0,1,'2026-04-04 07:12:24','2026-04-04 07:12:24'),(3,4,'صوص الرانش',25.00,0,0,1,'2026-04-04 07:12:39','2026-04-04 07:12:39'),(4,4,'الصوص الحار',20.00,0,0,1,'2026-04-04 07:12:53','2026-04-04 07:12:53');
/*!40000 ALTER TABLE `product_option_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  KEY `products_category_id_foreign` (`category_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (15,14,'سوبر بوكس (قطعتين)','sobr-boks-ktaatyn-69d055df8085b','2 قطعة دجاج + بطاطس + كول سلو + خبز',150.00,'products/wlWaBAkysgPakpXhX6NHSuIvZsn5KejM4Em4Rcsn.jpg',1,'2026-04-04 07:05:51','2026-04-04 07:05:51'),(16,14,'ميجـا بوكس (3 قطع)','myga-boks-3-ktaa-69d05603cfff5','3 قطع دجاج + بطاطس + كول سلو + خبز',190.00,'products/1ADXcyvtHHb1ESykKe3pbuOZ4Kcr715VvwgZIfJi.jpg',1,'2026-04-04 07:06:27','2026-04-04 07:06:27'),(17,14,'وجبة الاستربس','ogb-alastrbs-69d0561c3ffd1','5 قطع أصابع دجاج مقرمشة + صوص + بطاطس',170.00,'products/TQNBAFZJnBBX0oH3TeSAjRzVyLlcn73JPJA1YbdA.jpg',1,'2026-04-04 07:06:52','2026-04-04 07:06:52'),(18,15,'سندوتش الكلاسيك','sndotsh-alklasyk-69d0563c6b4e0','دجاج مقرمش + خس + مايونيز',160.00,'products/TedYqU5ofr6tz9FviwfOvq8sN1SpOssGFADXbI2H.jpg',1,'2026-04-04 07:07:24','2026-04-04 07:07:24'),(19,15,'سندوتش بركان الجبن','sndotsh-brkan-algbn-69d0565590e27','دجاج مقرمش + صوص تشيدر سايح + هالبينو',200.00,'products/mN2ZNTvzkNBuDTyCEZSAFHgeFNZjxNA15IGCkmEk.jpg',1,'2026-04-04 07:07:49','2026-04-04 07:07:49'),(20,15,'سندوتش العملاق (Double)','sndotsh-alaamlak-double-69d0566d533a4','طبقتين دجاج + جبنة + صوص \"قرمشة\" الخاص',250.00,'products/p8CtWtYIeLYxMS21lfr6LEHHU6L7wLPX9SHfr1NN.jpg',1,'2026-04-04 07:08:13','2026-04-04 07:08:13'),(21,16,'باكت 9 قطع','bakt-9-ktaa-69d05684d2e89','9 قطع دجاج + بطاطس عائلي + لتر كولا',450.00,'products/txjTJKehc1yrqK0V8wXsuQTf88zey6EQOg0zsLPq.jpg',1,'2026-04-04 07:08:36','2026-04-04 07:08:36'),(22,16,'باكت 15 قطعة','bakt-15-ktaa-69d0569de581e','15 قطعة دجاج + بطاطس عائلي + كول سلو كبير + 5 خبز',550.00,'products/rdSLDCZLiFhO71XfJYVZD1BKJOhjlAFPFPnutq04.jpg',1,'2026-04-04 07:09:01','2026-04-04 07:09:01'),(23,17,'ريزو الدجاج','ryzo-aldgag-69d056da9e54d','أرز مبهر بقطع الاستربس وصوص الباربكيو',140.00,'products/SVYVD2N6fT9hOlpfJgOQpvNbOL5oWcP2C807VL3s.jpg',1,'2026-04-04 07:10:02','2026-04-04 07:10:02'),(24,17,'بطاطس بالجبنة','btats-balgbn-69d056f06d1bd','بطاطس مقلية غرقانة بصوص الشيدر',80.00,'products/krIt59XTopq1JESOG0Btibf1rs16q8cMwjG8OQya.jpg',1,'2026-04-04 07:10:24','2026-04-04 07:10:24');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('2W2c9d5bT7VUM2BCsP5eujXn7u2ufrkfqdrDRURW',NULL,'199.45.154.114','Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)','eyJfdG9rZW4iOiJMMndqOFJ1MHBPdWNlWnROandwY2Y2enVJNDNMZTRxVHFxTzhIalVmIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzUxLjM4LjExNS4xNzE6NDQzIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1775382341),('HiYpE1PGjQNTGh6VMtsu5ExXsIClTWnRtSENSNB9',NULL,'91.215.85.104','Mozilla/5.0 (Macintosh; U; Intel Mac OS X; ja-jp) AppleWebKit/523.12.2 (KHTML, like Gecko) Version/3.0.4 Safari/523.12.2','eyJfdG9rZW4iOiJWU0c3ZEJHV2g2SGQxeDV4RVlSTHhkaHNneVFCZG9CbDJGVTlCWFNrIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzUxLjM4LjExNS4xNzFcL2xvZ2luP3JlZGlyPSUyRm5nIiwicm91dGUiOiJsb2dpbiJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1775380861),('ivVj8lZZgY39lOSDiVL8fSDw8RY5muh7Rosz0QDN',NULL,'65.49.1.52','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJoNUpQR0dhak1kSXM5Z2ZrQTN5bURrY0JBZWkyR3loWklPeG81d21xIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzUxLjM4LjExNS4xNzEiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1775382529),('JN2dXfZfnugXB32q6MFvEUIbpBubqvrA03IvszNl',NULL,'134.209.53.95','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJDUmZjMTI1ZDZLZlBucTFRdG5xcGZRc3lzajdkR3RyNUdRTm9ObERuIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzUxLjM4LjExNS4xNzEiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1775381971),('LNcLFaQAVtClaYUYuFmkpXKfqOQeoIKuaHttw6MY',NULL,'172.71.182.168','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','eyJfdG9rZW4iOiIwNUNBakVjOTU5eTdXa0RZTnhvcHRvNGhHUkE1TGo4Nml1UDFLd0xiIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL3BsYXktY2FzeS5vbmxpbmUiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1775378024),('ly6zr2iQtUqZ9tplujKlUy2nQ21yuEdldcNi14JR',NULL,'2400:cb00:71:1000:8438:78d0:6371:9bce','Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','eyJfdG9rZW4iOiJKd3lXb1o3SzMwdU5DR0ZlQ2RyUEhpTm82ZWNkanhMR2J1OWh2MURBIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL3d3dy5kZXZpbGluc2lkZWJhbmQuY29tIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1775381298),('MdQmilbqCkZyKUuGtYJ8NS1V9nxajuPgcNX2tce3',NULL,'45.55.164.231','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJ0d01GaWRSeWQ1YTcxZGVSWFJIRXQ4eFZLWlZZcU1UcGZjU0w4cW9LIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzUxLjM4LjExNS4xNzEiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1775376074),('miHqd2CGNFY3hXsYeSJfFhYT78QFaufsYy0Y2Aug',NULL,'183.180.129.5','','eyJfdG9rZW4iOiI4eEVyRDBMWGFrZHpBamVtRElWNk00bG1OWVhaRENhT1FsUzZzOHlQIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzUxLjM4LjExNS4xNzEiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1775381360),('OcyFx4xYSjT9AntMeV7uD662f0cVVe3p94Qh0fJi',NULL,'104.22.17.193','Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','eyJfdG9rZW4iOiJwaUNtTkt3UmNJZ3FqUVdMQTFBblB6eVNDMlRGa2VkaEJocUpwVUduIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL3BsYXktY2FzeS5vbmxpbmUiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1775379628),('Pbdjmoxjuj7ku8cDrOpPG62NpPOX0Li8A4Y4zHS5',NULL,'199.45.154.114','Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)','eyJfdG9rZW4iOiJaZjR3NDJpZVA1aDdmTW1jYXlXcDhVRkl2bjRSMUhuc1d0ckxxdlpFIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzUxLjM4LjExNS4xNzE6NDQzIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1775382331),('pqbrDxir7DwZQr4EQhE778sKCMT9d668TYzrvvYm',NULL,'162.243.47.192','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJ5RWVGak1FdlB2UzhBcUVXR0VsbXYxMEVNbkdSY2ZBczQwbmxoMHRoIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzUxLjM4LjExNS4xNzE6NDQzIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1775381971),('PwdUIbKyjIAKz2KpCWf5QqHJB8kiMWkrzw2c6zA3',NULL,'104.23.170.99','User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0; 360Spider','eyJfdG9rZW4iOiJTc3R6SGl5eVJOQk95aHA0M1V2SEtiRVc1M0t6MnRVM0U3cW44WW45IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL3BsYXktY2FzeS5vbmxpbmUiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1775381339),('pXBNw9trHlogKQ1wqGyoD8XFw8T7KFNQnOQ6CPW9',NULL,'192.241.173.147','Mozilla/5.0 (compatible; zern.io/1.0; +https://zern.io/scan)','eyJfdG9rZW4iOiI3Y3F0YlJvQmlFZDQ2ZFhMYmVXSXRSY2lXVGRwWHpjcXltMzFIWWloIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzUxLjM4LjExNS4xNzEiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1775380097),('PYEEgkwjrA9PiiRaGKRUw61KtXYYbGNcLAX1zh5w',NULL,'192.241.173.147','Mozilla/5.0 (compatible; zern.io/1.0; +https://zern.io/scan)','eyJfdG9rZW4iOiJEeEVaOURhRjNQVURSTzhTTXNHU05WTm5tWTVObTN1eVhidjliUTloIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzUxLjM4LjExNS4xNzEiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1775380097),('S3YdoqK4bF29wlLaXWlX6PhaqCFwewx8hWuaecPu',NULL,'104.22.17.4','Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','eyJfdG9rZW4iOiI5dGhXU2d4SDc1b1FFeWh3TVlFeGRMamllekpMWFlJV2ZKeG5YaDVGIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL3d3dy5wbGF5LWNhc3kub25saW5lIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1775381876),('tzWL0Z7uD3GbfYKiCBPWCQ9M7IGTWEKJgqXVHha2',NULL,'185.177.72.61','l9tcpid/v1.1.0','eyJfdG9rZW4iOiI1c3dwU2ZkR1FHWmd1TVZHTW5VRjBMU0dFVU54ZEVVV3luZEdJeERNIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzUxLjM4LjExNS4xNzEiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1775380734),('UwQYcWOv7SfzwWiyOkjwxA34ld5GBU1FSKBGjuN1',1,'162.158.22.134','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0','eyJfdG9rZW4iOiJra2YxQlNvTjhoVGNobmV3NTJTSkNaT2YwR3pYNkhGTXpBT3JEeEhLIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL3BsYXktY2FzeS5vbmxpbmVcL2FkbWluXC9vcmRlcnMtZGVsaXZlcnkiLCJyb3V0ZSI6ImFkbWluLm9yZGVycy5kZWxpdmVyeSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxfQ==',1775381849),('Wjvsv9KQGFnx8Dv5uZmIPevI1c04iS4pnIjSEtPh',NULL,'149.86.227.60','Mozilla/5.0','eyJfdG9rZW4iOiJMcGxIVG40bmIxQW03YWgwUUN3QmIwWlRPY0lvMGFNVDhYNkQySXExIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzUxLjM4LjExNS4xNzEiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1775382294);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Fried Chicken',
  `restaurant_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `restaurant_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT '25.00',
  `is_open` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'زعتر وزتونه','01206628718','Alexandria, Egypt','settings/EjT0HffzaZ51XPY7bLPaIyViFu9MMyFybz2F4q2r.jpg','settings/3e8Ih058HP0aQQ5qI6Jqb83s8fwVFihNNn9PEmj0.png',30.00,1,'2026-03-30 16:36:38','2026-04-04 18:21:21');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_addresses`
--

DROP TABLE IF EXISTS `user_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'المنزل',
  `address_line` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `area` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_addresses_user_id_foreign` (`user_id`),
  CONSTRAINT `user_addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_addresses`
--

LOCK TABLES `user_addresses` WRITE;
/*!40000 ALTER TABLE `user_addresses` DISABLE KEYS */;
INSERT INTO `user_addresses` VALUES (1,2,'المنزل','1, شارع ميدان التحرير, المنشية الكبري, الإسكندرية, 21519, مصر','المنشية الكبري',31.1977000,29.8925000,1,'2026-03-30 18:35:32','2026-03-30 18:35:32'),(2,2,'Home','42, Kafr Abdo Street, Kafr Abdo, Alexandria, 21529, Egypt','Kafr Abdo',31.2268030,29.9519828,0,'2026-03-30 19:41:26','2026-03-30 19:41:26');
/*!40000 ALTER TABLE `user_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `permissions` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_branch_id_foreign` (`branch_id`),
  CONSTRAINT `users_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'ibrahim','admin@admin.com','staff','super_admin',1,'[\"view_orders\", \"update_order_status\", \"view_all_branches_orders\", \"manage_products\", \"manage_categories\", \"manage_branches\", \"manage_settings\", \"manage_digital_menu\", \"manage_staff\", \"view_reports\"]',1,1,NULL,'$2y$12$htUiZUwNlbAh0vGQo7Wgj.qA4vma6YZi37qLDRBUTbPz6U9g10Gei','ofZwogvkgFvfvonAHpJXbaK0BsYKXrECQ85mNXx4aNno6sFXRKHeXKyArxFA','2026-03-30 17:21:58','2026-03-30 17:22:38'),(2,'ibrahim','mse6322@gmail.com','staff','branch_staff',1,'[\"manage_digital_menu\"]',1,0,NULL,'$2y$12$3bOdhzj7gOiSDik6n8gVO.0kMzr5chbOrcnEi388hhmH7jrMqgLEC',NULL,'2026-03-30 17:33:54','2026-04-01 21:23:44'),(4,'semox','semoxa@gmail.com','customer',NULL,NULL,'[]',1,0,NULL,'$2y$12$cIMIDozuo.n7jD6rq0mOTuFulRs48RDZ1aoK8Z2ii3YiM4ZVvL8fe',NULL,'2026-04-01 21:16:24','2026-04-01 21:16:24'),(5,'محمد ابراهيم','Semo@gmail.com','staff','cashier',1,'[\"view_orders\", \"update_order_status\"]',1,0,NULL,'$2y$12$.1iPDrlPdcAzzN8ArDSvrui5xWqxKKtZL4yGhu67Vbbn7w16h7Aju',NULL,'2026-04-04 17:45:04','2026-04-04 17:45:04');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-05  3:12:06

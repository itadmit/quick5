-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: יולי 17, 2025 בזמן 04:16 PM
-- גרסת שרת: 5.7.39
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quickshop5`
--

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `analytics_pixels`
--

CREATE TABLE `analytics_pixels` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `pixel_type` enum('google_analytics','facebook_pixel','google_ads','tiktok_pixel','custom') COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pixel_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `api_keys`
--

CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `key_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permissions` json DEFAULT NULL,
  `rate_limit_per_hour` int(11) DEFAULT '1000',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `attributes`
--

CREATE TABLE `attributes` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('text','color','size','select') COLLATE utf8mb4_unicode_ci DEFAULT 'text',
  `sort_order` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- הוצאת מידע עבור טבלה `attributes`
--

INSERT INTO `attributes` (`id`, `name`, `display_name`, `type`, `sort_order`, `created_at`) VALUES
(1, 'color', 'צבע', 'color', 0, '2025-07-17 13:53:17'),
(2, 'size', 'מידה', 'size', 0, '2025-07-17 13:53:17');

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `attribute_values`
--

CREATE TABLE `attribute_values` (
  `id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color_hex` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `automations`
--

CREATE TABLE `automations` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trigger_event` enum('order_placed','order_paid','order_shipped','order_delivered','abandoned_cart','customer_registered') COLLATE utf8mb4_unicode_ci NOT NULL,
  `conditions` json DEFAULT NULL,
  `actions` json DEFAULT NULL,
  `delay_minutes` int(11) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `run_count` int(11) DEFAULT '0',
  `last_run_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `auto_suggestions`
--

CREATE TABLE `auto_suggestions` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trigger_type` enum('cart_value','product_category','product_specific','customer_behavior') COLLATE utf8mb4_unicode_ci NOT NULL,
  `trigger_condition` json DEFAULT NULL,
  `suggestion_type` enum('upsell','cross_sell','bundle') COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_products` json DEFAULT NULL,
  `discount_percentage` decimal(5,2) DEFAULT '0.00',
  `max_suggestions` int(11) DEFAULT '3',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- הוצאת מידע עבור טבלה `auto_suggestions`
--

INSERT INTO `auto_suggestions` (`id`, `store_id`, `name`, `trigger_type`, `trigger_condition`, `suggestion_type`, `target_products`, `discount_percentage`, `max_suggestions`, `is_active`, `created_at`, `updated_at`) VALUES
(9, 1, 'Auto Suggest by Category', 'product_category', '{}', 'cross_sell', '[]', '0.00', 3, 1, '2025-07-15 12:09:03', '2025-07-15 12:09:03'),
(10, 1, 'Auto Suggest by Price Range', 'cart_value', '{}', 'upsell', '[]', '0.00', 3, 1, '2025-07-15 12:09:03', '2025-07-15 12:09:03'),
(11, 1, 'Auto Suggest by Tags', 'product_specific', '{}', 'bundle', '[]', '0.00', 3, 1, '2025-07-15 12:09:03', '2025-07-15 12:09:03');

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `builder_pages`
--

CREATE TABLE `builder_pages` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `page_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'home',
  `page_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `page_data` json NOT NULL,
  `is_published` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `builder_templates`
--

CREATE TABLE `builder_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `description` text COLLATE utf8mb4_unicode_ci,
  `preview_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template_data` json NOT NULL,
  `is_premium` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `bundle_products`
--

CREATE TABLE `bundle_products` (
  `id` int(11) NOT NULL,
  `bundle_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT '1',
  `is_required` tinyint(1) DEFAULT '1',
  `discount_percentage` decimal(5,2) DEFAULT '0.00',
  `sort_order` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `seo_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seo_description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `commissions`
--

CREATE TABLE `commissions` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `total_sales` decimal(15,2) NOT NULL DEFAULT '0.00',
  `commission_rate` decimal(5,4) NOT NULL DEFAULT '0.0050',
  `commission_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','paid','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `custom_blocks`
--

CREATE TABLE `custom_blocks` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `page_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `block_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `block_settings` json DEFAULT NULL,
  `sort_order` int(11) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- הוצאת מידע עבור טבלה `custom_blocks`
--

INSERT INTO `custom_blocks` (`id`, `store_id`, `page_type`, `block_type`, `block_settings`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'home', 'hero', '{\"title\": \"ברוכים הבאים לניקול בוטיק\", \"overlay\": true, \"subtitle\": \"גלה את המוצרים הטובים ביותר במחירים הכי טובים\", \"text_color\": \"text-white\", \"button_link\": \"/category/all\", \"button_text\": \"קנה עכשיו\", \"overlay_opacity\": 40, \"background_color\": \"\"}', 0, 1, '2025-07-15 09:12:50', '2025-07-15 09:12:50'),
(2, 1, 'home', 'product-grid', '{\"title\": \"מוצרים מומלצים\", \"columns\": 4, \"subtitle\": \"הבחירה שלנו עבורך\", \"show_price\": true, \"filter_type\": \"featured\", \"products_count\": 8, \"show_add_to_cart\": true}', 1, 1, '2025-07-15 09:12:50', '2025-07-15 09:12:50');

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `custom_code`
--

CREATE TABLE `custom_code` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `code_type` enum('css','javascript','html') COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` enum('header','footer','before_body_end') COLLATE utf8mb4_unicode_ci DEFAULT 'header',
  `code_content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `custom_domains`
--

CREATE TABLE `custom_domains` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `domain_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ssl_certificate` text COLLATE utf8mb4_unicode_ci,
  `ssl_private_key` text COLLATE utf8mb4_unicode_ci,
  `verification_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','verified','active','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `dns_records` json DEFAULT NULL,
  `last_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `custom_field_types`
--

CREATE TABLE `custom_field_types` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_type` enum('text','textarea','number','email','phone','url','date','datetime','time','select','multi_select','radio','checkbox','file','color') COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `placeholder` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT '0',
  `validation_rules` json DEFAULT NULL,
  `options` json DEFAULT NULL,
  `help_text` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `custom_sections`
--

CREATE TABLE `custom_sections` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `page_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `section_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `section_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `custom_settings` json DEFAULT NULL,
  `section_order` int(11) DEFAULT '1',
  `is_visible` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- הוצאת מידע עבור טבלה `custom_sections`
--

INSERT INTO `custom_sections` (`id`, `store_id`, `page_type`, `section_id`, `section_type`, `custom_settings`, `section_order`, `is_visible`, `created_at`, `updated_at`) VALUES
(10, 1, 'home', 'header', 'header', '{\"spacing\": \"normal\", \"store_name\": \"ניקול בוטיק\", \"font_family\": \"Arial, sans-serif\", \"border_radius\": \"8px\", \"primary_color\": \"#1e40af\", \"secondary_color\": \"#f59e0b\"}', 1, 1, '2025-07-15 15:10:26', '2025-07-15 16:05:33'),
(12, 1, 'home', 'footer', 'footer', '{\"spacing\": \"normal\", \"copyright\": \"© 2024 ניקול בוטיק. כל הזכויות שמורות.\", \"font_family\": \"Arial, sans-serif\", \"border_radius\": \"8px\", \"primary_color\": \"#1e40af\", \"secondary_color\": \"#f59e0b\"}', 4, 1, '2025-07-15 15:10:26', '2025-07-15 16:05:33'),
(16, 1, 'home', 'featured-products-3', 'featured-products', '{\"columns\": 3, \"spacing\": \"normal\", \"font_family\": \"Arial, sans-serif\", \"border_radius\": \"8px\", \"primary_color\": \"#1e40af\", \"secondary_color\": \"#f59e0b\"}', 3, 1, '2025-07-15 15:15:52', '2025-07-15 16:05:33');

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `custom_templates`
--

CREATE TABLE `custom_templates` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `template_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `template_code` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `gdpr_settings`
--

CREATE TABLE `gdpr_settings` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `cookie_banner_enabled` tinyint(1) DEFAULT '1',
  `banner_text` text COLLATE utf8mb4_unicode_ci,
  `banner_button_text` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'אני מסכים',
  `privacy_policy_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `terms_of_service_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_retention_days` int(11) DEFAULT '365',
  `allow_analytics` tinyint(1) DEFAULT '1',
  `allow_marketing` tinyint(1) DEFAULT '0',
  `allow_functional` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `global_accordions`
--

CREATE TABLE `global_accordions` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_open_by_default` tinyint(1) DEFAULT '0',
  `apply_to_all_products` tinyint(1) DEFAULT '1',
  `category_ids` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `import_jobs`
--

CREATE TABLE `import_jobs` (
  `id` int(11) NOT NULL,
  `import_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `store_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `skip_existing` tinyint(1) DEFAULT '0',
  `download_images` tinyint(1) DEFAULT '1',
  `create_categories` tinyint(1) DEFAULT '1',
  `image_domain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `image_quality` enum('original','high','medium') COLLATE utf8mb4_unicode_ci DEFAULT 'high',
  `status` enum('pending','processing','completed','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `total_rows` int(11) DEFAULT '0',
  `processed_rows` int(11) DEFAULT '0',
  `imported_products` int(11) DEFAULT '0',
  `failed_products` int(11) DEFAULT '0',
  `progress_percent` decimal(5,2) DEFAULT '0.00',
  `current_step` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `error_log` text COLLATE utf8mb4_unicode_ci,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- הוצאת מידע עבור טבלה `import_jobs`
--

INSERT INTO `import_jobs` (`id`, `import_id`, `store_id`, `user_id`, `filename`, `file_path`, `skip_existing`, `download_images`, `create_categories`, `image_domain`, `image_quality`, `status`, `total_rows`, `processed_rows`, `imported_products`, `failed_products`, `progress_percent`, `current_step`, `error_log`, `started_at`, `completed_at`, `created_at`, `updated_at`) VALUES
(1, 'import_687917e1c09421.84392494', 1, 1, 'export_catalog_product_20250717_153436.csv', '../../uploads/imports/import_687917e1c09421.84392494.csv', 1, 1, 1, 'https://www.studiopasha.co.il/media/catalog/product', 'high', 'pending', 0, 0, 0, 0, '0.00', '', NULL, NULL, NULL, '2025-07-17 15:33:53', '2025-07-17 15:33:53');

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `notification_settings`
--

CREATE TABLE `notification_settings` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `notification_type` enum('new_order','low_stock','order_shipped','payment_received','customer_registered') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_enabled` tinyint(1) DEFAULT '1',
  `email_enabled` tinyint(1) DEFAULT '1',
  `sms_enabled` tinyint(1) DEFAULT '0',
  `slack_enabled` tinyint(1) DEFAULT '0',
  `webhook_enabled` tinyint(1) DEFAULT '0',
  `recipients` json DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `order_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_address` text COLLATE utf8mb4_unicode_ci,
  `billing_address` text COLLATE utf8mb4_unicode_ci,
  `shipping_address` text COLLATE utf8mb4_unicode_ci,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `shipping_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'ILS',
  `status` enum('pending','confirmed','processing','shipped','delivered','cancelled','refunded') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `order_status_id` int(11) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded','partial') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_status_id` int(11) DEFAULT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracking_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `internal_notes` text COLLATE utf8mb4_unicode_ci,
  `order_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `shipped_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- הוצאת מידע עבור טבלה `orders`
--

INSERT INTO `orders` (`id`, `store_id`, `order_number`, `customer_name`, `customer_email`, `customer_phone`, `customer_address`, `billing_address`, `shipping_address`, `subtotal`, `tax_amount`, `shipping_amount`, `discount_amount`, `total_amount`, `currency`, `status`, `order_status_id`, `payment_status`, `payment_status_id`, `payment_method`, `payment_id`, `shipping_method`, `tracking_number`, `notes`, `internal_notes`, `order_date`, `shipped_at`, `delivered_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'ORD-20250709-001', 'יוסי כהן', 'yossi.cohen@example.com', '050-1234567', NULL, NULL, 'יוסי כהן\nרחוב הרצל 123\nתל אביב, 6423456\nישראל', '800.00', '140.25', '25.00', '0.00', '965.25', 'ILS', 'delivered', NULL, 'paid', NULL, 'אשראי', NULL, 'דואר רשום', NULL, 'אנא השאירו ליד הדלת', NULL, '2025-07-02 16:46:07', '2025-07-03 16:46:07', '2025-07-04 16:46:07', '2025-07-09 16:46:07', '2025-07-09 16:46:07'),
(2, 1, 'ORD-20250709-002', 'שרה לוי', 'sara.levi@example.com', '052-9876543', NULL, NULL, 'שרה לוי\nרחוב דיזנגוף 45\nתל אביב, 6423789\nישראל', '200.00', '38.25', '25.00', '0.00', '263.25', 'ILS', 'shipped', NULL, 'paid', NULL, 'פייפל', NULL, 'שליח עד הבית', 'TRK123456789', NULL, NULL, '2025-07-06 16:46:07', '2025-07-07 16:46:07', NULL, '2025-07-09 16:46:07', '2025-07-09 16:46:07'),
(3, 1, 'ORD-20250709-003', 'דוד אברהם', 'david.abraham@example.com', '054-5555555', NULL, NULL, 'דוד אברהם\nרחוב בן יהודה 78\nירושלים, 9423123\nישראל', '600.00', '102.00', '0.00', '0.00', '702.00', 'ILS', 'processing', NULL, 'paid', NULL, 'העברה בנקאית', NULL, 'איסוף עצמי', NULL, NULL, 'לקוח VIP - טיפול מועדף', '2025-07-08 16:46:07', NULL, NULL, '2025-07-09 16:46:07', '2025-07-09 16:46:07'),
(4, 1, 'ORD-20250709-004', 'מיכל רוזנברג', 'michal.r@example.com', '053-7777777', NULL, NULL, 'מיכל רוזנברג\nרחוב אלנביי 234\nחיפה, 3423567\nישראל', '600.00', '106.25', '25.00', '0.00', '731.25', 'ILS', 'pending', NULL, 'pending', NULL, 'מזומן במשלוח', NULL, 'שליח עד הבית', NULL, NULL, NULL, '2025-07-09 16:46:07', NULL, NULL, '2025-07-09 16:46:07', '2025-07-09 16:46:07'),
(5, 1, 'ORD-20250709-005', 'אבי שמואלי', 'avi.shmueli@example.com', '050-9999999', NULL, NULL, 'אבי שמואלי\nרחוב העצמאות 56\nבאר שבע, 8423789\nישראל', '800.00', '140.25', '25.00', '0.00', '965.25', 'ILS', 'cancelled', NULL, 'refunded', NULL, 'אשראי', NULL, 'דואר רשום', NULL, NULL, 'הזמנה בוטלה על פי בקשת הלקוח - מוצר לא במלאי', '2025-07-04 16:46:07', NULL, NULL, '2025-07-09 16:46:07', '2025-07-09 16:46:07');

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_sku` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variant_attributes` text COLLATE utf8mb4_unicode_ci,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- הוצאת מידע עבור טבלה `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `variant_id`, `product_name`, `product_sku`, `variant_attributes`, `quantity`, `unit_price`, `total_price`, `created_at`) VALUES
(1, 1, 19, NULL, 'yogev avitan', 'SKU-19', NULL, 2, '200.00', '400.00', '2025-07-09 16:46:07'),
(2, 1, 19, NULL, 'yogev avitan', 'SKU-19', NULL, 2, '200.00', '400.00', '2025-07-09 16:46:07'),
(3, 2, 19, NULL, 'yogev avitan', 'SKU-19', NULL, 1, '200.00', '200.00', '2025-07-09 16:46:07'),
(4, 3, 19, NULL, 'yogev avitan', 'SKU-19', NULL, 1, '200.00', '200.00', '2025-07-09 16:46:07'),
(5, 3, 19, NULL, 'yogev avitan', 'SKU-19', NULL, 1, '200.00', '200.00', '2025-07-09 16:46:07'),
(6, 3, 19, NULL, 'yogev avitan', 'SKU-19', NULL, 1, '200.00', '200.00', '2025-07-09 16:46:07'),
(7, 4, 19, NULL, 'yogev avitan', 'SKU-19', NULL, 1, '200.00', '200.00', '2025-07-09 16:46:07'),
(8, 4, 19, NULL, 'yogev avitan', 'SKU-19', NULL, 1, '200.00', '200.00', '2025-07-09 16:46:07'),
(9, 4, 19, NULL, 'yogev avitan', 'SKU-19', NULL, 1, '200.00', '200.00', '2025-07-09 16:46:07'),
(10, 5, 19, NULL, 'yogev avitan', 'SKU-19', NULL, 1, '200.00', '200.00', '2025-07-09 16:46:07'),
(11, 5, 19, NULL, 'yogev avitan', 'SKU-19', NULL, 1, '200.00', '200.00', '2025-07-09 16:46:07'),
(12, 5, 19, NULL, 'yogev avitan', 'SKU-19', NULL, 2, '200.00', '400.00', '2025-07-09 16:46:07');

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `order_statuses`
--

CREATE TABLE `order_statuses` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#6B7280',
  `background_color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#F3F4F6',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'ri-circle-line',
  `is_default` tinyint(1) DEFAULT '0',
  `is_system` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int(11) DEFAULT '0',
  `allow_edit` tinyint(1) DEFAULT '1',
  `auto_complete_payment` tinyint(1) DEFAULT '0',
  `send_email_notification` tinyint(1) DEFAULT '1',
  `send_sms_notification` tinyint(1) DEFAULT '0',
  `reduce_stock` tinyint(1) DEFAULT '0',
  `release_stock` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- הוצאת מידע עבור טבלה `order_statuses`
--

INSERT INTO `order_statuses` (`id`, `store_id`, `name`, `slug`, `display_name`, `description`, `color`, `background_color`, `icon`, `is_default`, `is_system`, `is_active`, `sort_order`, `allow_edit`, `auto_complete_payment`, `send_email_notification`, `send_sms_notification`, `reduce_stock`, `release_stock`, `created_at`, `updated_at`) VALUES
(1, 1, 'pending', 'pending', 'ממתינה', 'הזמנה חדשה שממתינה לטיפול', '#F59E0B', '#FEF3C7', 'ri-time-line', 1, 1, 1, 1, 1, 0, 1, 0, 0, 0, '2025-07-09 16:55:56', '2025-07-09 16:55:56'),
(2, 1, 'confirmed', 'confirmed', 'אושרה', 'הזמנה אושרה ומוכנה לטיפול', '#3B82F6', '#DBEAFE', 'ri-check-line', 0, 1, 1, 2, 1, 0, 1, 0, 1, 0, '2025-07-09 16:55:56', '2025-07-09 16:55:56'),
(3, 1, 'processing', 'processing', 'בטיפול', 'הזמנה בתהליך הכנה ואריזה', '#8B5CF6', '#EDE9FE', 'ri-settings-3-line', 0, 1, 1, 3, 1, 0, 1, 0, 0, 0, '2025-07-09 16:55:56', '2025-07-09 16:55:56'),
(4, 1, 'shipped', 'shipped', 'נשלחה', 'הזמנה נשלחה ובדרך ללקוח', '#6366F1', '#E0E7FF', 'ri-truck-line', 0, 1, 1, 4, 0, 0, 1, 0, 0, 0, '2025-07-09 16:55:56', '2025-07-09 16:55:56'),
(5, 1, 'delivered', 'delivered', 'נמסרה', 'הזמנה נמסרה בהצלחה ללקוח', '#10B981', '#D1FAE5', 'ri-check-double-line', 0, 1, 1, 5, 0, 0, 1, 0, 0, 0, '2025-07-09 16:55:56', '2025-07-09 16:55:56'),
(6, 1, 'cancelled', 'cancelled', 'בוטלה', 'הזמנה בוטלה', '#EF4444', '#FEE2E2', 'ri-close-circle-line', 0, 1, 1, 6, 0, 0, 1, 0, 0, 1, '2025-07-09 16:55:56', '2025-07-09 16:55:56'),
(7, 1, 'refunded', 'refunded', 'הוחזרה', 'הזמנה הוחזרה ובוצע החזר כספי', '#6B7280', '#F3F4F6', 'ri-refund-line', 0, 1, 1, 7, 0, 0, 1, 0, 0, 1, '2025-07-09 16:55:56', '2025-07-09 16:55:56'),
(8, 1, 'urgent_processing', 'urgent_processing', 'טיפול דחוף', 'הזמנות הדורשות טיפול מיידי ודחוף', '#FF5722', '#FFEBEE', 'ri-alarm-warning-line', 0, 0, 1, 999, 1, 0, 1, 1, 1, 0, '2025-07-09 16:56:43', '2025-07-09 16:56:43');

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `old_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `changed_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- הוצאת מידע עבור טבלה `order_status_history`
--

INSERT INTO `order_status_history` (`id`, `order_id`, `old_status`, `new_status`, `notes`, `changed_by`, `changed_at`) VALUES
(1, 1, NULL, 'delivered', 'הזמנה נוצרה', 'מערכת', '2025-07-02 13:46:07'),
(2, 2, NULL, 'shipped', 'הזמנה נוצרה', 'מערכת', '2025-07-06 13:46:07'),
(3, 3, NULL, 'processing', 'הזמנה נוצרה', 'מערכת', '2025-07-08 13:46:07'),
(4, 4, NULL, 'pending', 'הזמנה נוצרה', 'מערכת', '2025-07-09 13:46:07'),
(5, 5, NULL, 'cancelled', 'הזמנה נוצרה', 'מערכת', '2025-07-04 13:46:07');

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `payment_providers`
--

CREATE TABLE `payment_providers` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `provider_name` enum('paypal','stripe','credit_guard','tranzila','bit','cash_on_delivery','bank_transfer') COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '0',
  `is_test_mode` tinyint(1) DEFAULT '1',
  `settings` json DEFAULT NULL,
  `sort_order` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `payment_statuses`
--

CREATE TABLE `payment_statuses` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#6B7280',
  `background_color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#F3F4F6',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'ri-money-dollar-circle-line',
  `is_default` tinyint(1) DEFAULT '0',
  `is_system` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int(11) DEFAULT '0',
  `is_paid` tinyint(1) DEFAULT '0',
  `allow_refund` tinyint(1) DEFAULT '0',
  `auto_fulfill` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- הוצאת מידע עבור טבלה `payment_statuses`
--

INSERT INTO `payment_statuses` (`id`, `store_id`, `name`, `slug`, `display_name`, `description`, `color`, `background_color`, `icon`, `is_default`, `is_system`, `is_active`, `sort_order`, `is_paid`, `allow_refund`, `auto_fulfill`, `created_at`, `updated_at`) VALUES
(1, 1, 'pending', 'pending', 'ממתין לתשלום', 'תשלום עדיין לא בוצע', '#F59E0B', '#FEF3C7', 'ri-time-line', 1, 1, 1, 1, 0, 0, 0, '2025-07-09 16:55:56', '2025-07-09 16:55:56'),
(2, 1, 'paid', 'paid', 'שולם', 'תשלום בוצע בהצלחה', '#10B981', '#D1FAE5', 'ri-check-line', 0, 1, 1, 2, 1, 1, 1, '2025-07-09 16:55:56', '2025-07-09 16:55:56'),
(3, 1, 'failed', 'failed', 'נכשל', 'תשלום נכשל', '#EF4444', '#FEE2E2', 'ri-close-line', 0, 1, 1, 3, 0, 0, 0, '2025-07-09 16:55:56', '2025-07-09 16:55:56'),
(4, 1, 'refunded', 'refunded', 'הוחזר', 'בוצע החזר כספי', '#6B7280', '#F3F4F6', 'ri-refund-line', 0, 1, 1, 4, 0, 0, 0, '2025-07-09 16:55:56', '2025-07-09 16:55:56'),
(5, 1, 'partial', 'partial', 'חלקי', 'תשלום חלקי בוצע', '#F97316', '#FED7AA', 'ri-funds-line', 0, 1, 1, 5, 0, 1, 0, '2025-07-09 16:55:56', '2025-07-09 16:55:56'),
(6, 1, 'installments', 'installments', 'תשלומים', 'תשלום בתשלומים עם כרטיס אשראי', '#FF9800', '#FFF3E0', 'ri-bank-card-line', 0, 0, 1, 999, 1, 1, 1, '2025-07-09 16:56:43', '2025-07-09 16:56:43');

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- הוצאת מידע עבור טבלה `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'manage_themes', 'ניהול תבניות ועיצוב', '2025-07-15 09:15:38', '2025-07-15 09:15:38'),
(2, 'customize_theme', 'התאמה אישית של תבנית', '2025-07-15 09:15:38', '2025-07-15 09:15:38'),
(3, 'edit_theme_code', 'עריכת קוד תבנית', '2025-07-15 09:15:38', '2025-07-15 09:15:38'),
(4, 'manage_products', 'ניהול מוצרים', '2025-07-15 09:15:38', '2025-07-15 09:15:38'),
(5, 'manage_orders', 'ניהול הזמנות', '2025-07-15 09:15:38', '2025-07-15 09:15:38'),
(6, 'manage_users', 'ניהול משתמשים', '2025-07-15 09:15:38', '2025-07-15 09:15:38'),
(7, 'view_analytics', 'צפייה בדוחות', '2025-07-15 09:15:38', '2025-07-15 09:15:38'),
(8, 'manage_settings', 'ניהול הגדרות כלליות', '2025-07-15 09:15:38', '2025-07-15 09:15:38');

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `short_description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sku` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barcode` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `compare_price` decimal(10,2) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `track_inventory` tinyint(1) DEFAULT '1',
  `inventory_quantity` int(11) DEFAULT '0',
  `allow_backorders` tinyint(1) DEFAULT '0',
  `weight` decimal(8,2) DEFAULT NULL,
  `weight_unit` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'kg',
  `requires_shipping` tinyint(1) DEFAULT '1',
  `is_digital` tinyint(1) DEFAULT '0',
  `vendor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` text COLLATE utf8mb4_unicode_ci,
  `gallery_attribute` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','active','archived') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `featured` tinyint(1) DEFAULT '0',
  `has_variants` tinyint(1) DEFAULT '0',
  `seo_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seo_description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seo_keywords` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- הוצאת מידע עבור טבלה `products`
--

INSERT INTO `products` (`id`, `store_id`, `name`, `slug`, `description`, `short_description`, `sku`, `barcode`, `price`, `compare_price`, `cost_price`, `track_inventory`, `inventory_quantity`, `allow_backorders`, `weight`, `weight_unit`, `requires_shipping`, `is_digital`, `vendor`, `product_type`, `tags`, `gallery_attribute`, `status`, `featured`, `has_variants`, `seo_title`, `seo_description`, `seo_keywords`, `created_at`, `updated_at`) VALUES
(19, 1, 'yogev avitan', 'yogev-avitan', 'תיאור מלא', '', '', '', '100.00', NULL, NULL, 1, 0, 0, NULL, 'kg', 1, 0, '', '', '', '', 'active', 0, 0, 'yogev avitan', '', '', '2025-07-09 16:23:27', '2025-07-13 08:40:07'),
(21, 1, 'שלמה סער', 'שלמה-סער', '', '', NULL, '', NULL, NULL, NULL, 1, NULL, 0, NULL, 'kg', 1, 0, '', '', '', 'צבע', 'active', 0, 1, 'שלמה סער', '', '', '2025-07-09 17:20:44', '2025-07-11 14:33:48'),
(23, 1, 'מוצר בדיקה', 'mvtzr-bdikh', 'תיאור מפורט', 'תיאור קצר', NULL, 'ברקוד', NULL, NULL, NULL, 1, NULL, 0, '5.00', 'kg', 1, 0, 'נייק', 'בגדים', 'חדש', '', 'active', 0, 1, 'כותרת seo', 'תיאור seo', 'מילת מפתח', '2025-07-13 08:48:40', '2025-07-15 08:23:45'),
(24, 1, 'חולצת פולו כחולה', 'polo-shirt-blue', '', '', '', '', '89.90', NULL, NULL, 1, 0, 0, NULL, 'kg', 1, 0, '', '', '', '', 'active', 0, 0, 'חולצת פולו כחולה', '', '', '2025-07-13 12:00:42', '2025-07-15 08:28:02'),
(25, 1, 'מכנסיים קלאסיים', 'classic-pants', NULL, NULL, NULL, NULL, '149.90', NULL, NULL, 1, 0, 0, NULL, 'kg', 1, 0, NULL, NULL, NULL, NULL, 'active', 0, 0, NULL, NULL, NULL, '2025-07-13 12:00:42', '2025-07-13 12:00:42'),
(26, 1, 'נעלי סניקרס לבנות', 'white-sneakers', '', '', '', '', '199.90', NULL, NULL, 1, 0, 0, NULL, 'kg', 1, 0, '', '', '', '', 'active', 0, 0, 'נעלי סניקרס לבנות', '', '', '2025-07-13 12:00:42', '2025-07-15 12:09:03');

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `product_accordions`
--

CREATE TABLE `product_accordions` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_open_by_default` tinyint(1) DEFAULT '0',
  `sort_order` int(11) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- הוצאת מידע עבור טבלה `product_accordions`
--

INSERT INTO `product_accordions` (`id`, `product_id`, `title`, `content`, `icon`, `is_open_by_default`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(33, 23, 'אקורדיון 1', 'תוכן', '', 0, 0, 1, '2025-07-15 08:23:45', '2025-07-15 08:23:45'),
(34, 23, 'אקורדיון 2', 'תוכן', '', 0, 0, 1, '2025-07-15 08:23:45', '2025-07-15 08:23:45');

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `product_attributes`
--

CREATE TABLE `product_attributes` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('text','color','image') COLLATE utf8mb4_unicode_ci DEFAULT 'text',
  `sort_order` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `product_badges`
--

CREATE TABLE `product_badges` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `text` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#EF4444',
  `background_color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#FEE2E2',
  `position` enum('top-left','top-right','bottom-left','bottom-right') COLLATE utf8mb4_unicode_ci DEFAULT 'top-right',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- הוצאת מידע עבור טבלה `product_badges`
--

INSERT INTO `product_badges` (`id`, `product_id`, `text`, `color`, `background_color`, `position`, `is_active`, `created_at`) VALUES
(1, 19, 'חדש', '#ff0000', '#7a7a7a', 'top-right', 1, '2025-07-13 08:40:07'),
(66, 23, 'חדש', '#ff0000', '#000000', 'top-right', 1, '2025-07-15 08:23:45'),
(67, 23, 'מבצע', '#49a82e', '#ffdd00', 'top-left', 1, '2025-07-15 08:23:45');

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `product_bundles`
--

CREATE TABLE `product_bundles` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `bundle_price` decimal(10,2) DEFAULT NULL,
  `discount_type` enum('fixed','percentage') COLLATE utf8mb4_unicode_ci DEFAULT 'percentage',
  `discount_value` decimal(10,2) DEFAULT '0.00',
  `min_items` int(11) DEFAULT '1',
  `max_items` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `product_categories`
--

CREATE TABLE `product_categories` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `product_collections`
--

CREATE TABLE `product_collections` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` enum('upsell','cross_sell','related','bundle','frequently_bought') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `product_collection_items`
--

CREATE TABLE `product_collection_items` (
  `id` int(11) NOT NULL,
  `collection_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `sort_order` int(11) DEFAULT '0',
  `discount_percentage` decimal(5,2) DEFAULT '0.00',
  `is_required` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `product_custom_fields`
--

CREATE TABLE `product_custom_fields` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `field_type_id` int(11) NOT NULL,
  `field_value` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `product_media`
--

CREATE TABLE `product_media` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `type` enum('image','video') COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumbnail_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gallery_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `sort_order` int(11) DEFAULT '0',
  `file_size` int(11) DEFAULT NULL,
  `dimensions` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `product_relationships`
--

CREATE TABLE `product_relationships` (
  `id` int(11) NOT NULL,
  `main_product_id` int(11) NOT NULL,
  `related_product_id` int(11) NOT NULL,
  `relationship_type` enum('upsell','cross_sell','related','alternative','frequently_bought','recommended','complete_look') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int(11) DEFAULT '0',
  `discount_percentage` decimal(5,2) DEFAULT '0.00',
  `is_automatic` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `sku` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `compare_price` decimal(10,2) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `inventory_quantity` int(11) DEFAULT '0',
  `weight` decimal(8,2) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `shipping_companies`
--

CREATE TABLE `shipping_companies` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `company_name` enum('israel_post','ups','fedex','dhl','aramex','get','wolt','custom') COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_credentials` json DEFAULT NULL,
  `default_service` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '0',
  `settings` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `shipping_methods`
--

CREATE TABLE `shipping_methods` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `zone_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `method_type` enum('flat_rate','free_shipping','local_pickup','calculated') COLLATE utf8mb4_unicode_ci DEFAULT 'flat_rate',
  `cost` decimal(10,2) DEFAULT '0.00',
  `min_order_amount` decimal(10,2) DEFAULT NULL,
  `max_weight` decimal(8,2) DEFAULT NULL,
  `estimated_delivery_days` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `shipping_zones`
--

CREATE TABLE `shipping_zones` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `countries` json DEFAULT NULL,
  `states` json DEFAULT NULL,
  `postcodes` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `sms_settings`
--

CREATE TABLE `sms_settings` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `provider` enum('twilio','nexmo','clicksend','infobip') COLLATE utf8mb4_unicode_ci DEFAULT 'twilio',
  `api_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `api_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender_name` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '0',
  `test_mode` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `stores`
--

CREATE TABLE `stores` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domain` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_domain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subdomain_enabled` tinyint(1) DEFAULT '1',
  `ssl_enabled` tinyint(1) DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `logo` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#3B82F6',
  `secondary_color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#1F2937',
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'ILS',
  `theme_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'quickshop-evening',
  `theme_settings` json DEFAULT NULL,
  `timezone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Asia/Jerusalem',
  `language` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT 'he',
  `status` enum('active','maintenance','suspended') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- הוצאת מידע עבור טבלה `stores`
--

INSERT INTO `stores` (`id`, `user_id`, `name`, `slug`, `domain`, `custom_domain`, `subdomain_enabled`, `ssl_enabled`, `description`, `logo`, `primary_color`, `secondary_color`, `currency`, `theme_name`, `theme_settings`, `timezone`, `language`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'ניקול בוטיק', 'yogev', NULL, NULL, 1, 0, 'החנות המקוונת שלי - נבנתה עם QuickShop5', NULL, '#3B82F6', '#1F2937', 'ILS', 'quickshop-evening', '{\"hero\": [], \"menu\": [], \"fonts\": [], \"colors\": [], \"footer\": [], \"gtm_id\": \"\", \"layout\": [], \"custom_js\": \"\", \"header_bg\": \"#ffffff\", \"max_width\": \"1400px\", \"minify_js\": true, \"show_cart\": true, \"show_logo\": true, \"custom_css\": \"\", \"enable_ssl\": true, \"hero_title\": \"ברוכים הבאים לחנות שלנו\", \"logo_width\": \"150\", \"minify_css\": true, \"site_title\": \"החנות שלי\", \"auto_backup\": true, \"blocked_ips\": \"\", \"button_size\": \"medium\", \"custom_head\": \"\", \"font_weight\": \"400\", \"show_search\": true, \"backup_count\": \"7\", \"button_color\": \"#3b82f6\", \"button_style\": \"rounded-lg\", \"lazy_loading\": true, \"primary_font\": \"Noto Sans Hebrew\", \"store_status\": \"open\", \"header_height\": \"80\", \"hero_subtitle\": \"גלה את המוצרים הטובים ביותר במחירים הכי טובים\", \"primary_color\": \"#3b82f6\", \"site_keywords\": \"\", \"base_font_size\": \"16\", \"cache_duration\": \"24\", \"disable_xmlrpc\": false, \"enable_caching\": true, \"facebook_pixel\": \"\", \"featured_count\": \"8\", \"featured_title\": \"המוצרים שלנו\", \"social_twitter\": \"\", \"social_youtube\": \"\", \"store_password\": \"\", \"tracking_codes\": \"\", \"animation_speed\": \"medium\", \"featured_layout\": \"grid\", \"hide_wp_version\": false, \"secondary_color\": \"#10b981\", \"show_navigation\": true, \"social_facebook\": \"\", \"background_color\": \"#ffffff\", \"footer_copyright\": \"© 2024 החנות שלי. כל הזכויות שמורות.\", \"google_analytics\": \"\", \"hero_button_link\": \"/products\", \"hero_button_text\": \"קנה עכשיו\", \"site_description\": \"החנות המובילה למוצרים איכותיים במחירים הטובים ביותר\", \"social_instagram\": \"\", \"container_padding\": \"20\", \"enable_animations\": true, \"show_social_links\": true, \"maintenance_message\": \"החנות נמצאת בתחזוקה. נחזור בקרוב!\", \"password_protection\": false, \"featured_description\": \"גלה את המוצרים הטובים ביותר שלנו\"}', 'Asia/Jerusalem', 'he', 'active', '2025-07-09 16:17:46', '2025-07-15 12:02:05');

--
-- Triggers `stores`
--
DELIMITER $$
CREATE TRIGGER `update_stores_timestamp` BEFORE UPDATE ON `stores` FOR EACH ROW BEGIN 
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `store_access`
--

CREATE TABLE `store_access` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','manager','viewer','editor') COLLATE utf8mb4_unicode_ci DEFAULT 'viewer',
  `permissions` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `invited_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `store_pages`
--

CREATE TABLE `store_pages` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `page_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_structure` json DEFAULT NULL,
  `global_settings` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- הוצאת מידע עבור טבלה `store_pages`
--

INSERT INTO `store_pages` (`id`, `store_id`, `page_type`, `page_structure`, `global_settings`, `created_at`, `updated_at`) VALUES
(7, 1, 'home', '[\"header\", \"hero\", \"featured-products\", \"footer\"]', '{\"spacing\": \"normal\", \"font_family\": \"Arial, sans-serif\", \"border_radius\": \"8px\", \"primary_color\": \"#1e40af\", \"secondary_color\": \"#f59e0b\"}', '2025-07-15 14:04:40', '2025-07-15 15:10:26');

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `store_settings`
--

CREATE TABLE `store_settings` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'basic',
  `amount` decimal(10,2) NOT NULL DEFAULT '399.00',
  `vat_amount` decimal(10,2) NOT NULL DEFAULT '71.82',
  `total_amount` decimal(10,2) NOT NULL DEFAULT '470.82',
  `status` enum('pending','paid','failed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `billing_date` date NOT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `system_billings`
--

CREATE TABLE `system_billings` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `billing_type` enum('subscription','commission','transaction_fee','addon') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `vat_amount` decimal(10,2) DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `billing_period_start` date DEFAULT NULL,
  `billing_period_end` date DEFAULT NULL,
  `status` enum('pending','paid','overdue','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `invoice_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `theme_customizations`
--

CREATE TABLE `theme_customizations` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `theme_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customization_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customization_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customization_value` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `theme_metadata`
--

CREATE TABLE `theme_metadata` (
  `id` int(11) NOT NULL,
  `theme_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `version` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '1.0.0',
  `author` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumbnail` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `features` json DEFAULT NULL,
  `requirements` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- הוצאת מידע עבור טבלה `theme_metadata`
--

INSERT INTO `theme_metadata` (`id`, `theme_name`, `display_name`, `description`, `version`, `author`, `thumbnail`, `features`, `requirements`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'quickshop-evening', 'QuickShop Evening', 'תבנית מתקדמת ומודרנית עם תמיכה מלאה בעברית ו-RTL', '1.0.0', 'QuickShop Team', NULL, '[\"responsive\", \"rtl\", \"dark_mode\", \"customizable\", \"blocks\", \"seo\"]', NULL, 1, '2025-07-15 09:16:34', '2025-07-15 09:16:34');

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `theme_sections`
--

CREATE TABLE `theme_sections` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `section_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `section_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `settings` json DEFAULT NULL,
  `section_order` int(11) DEFAULT '1',
  `is_visible` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- הוצאת מידע עבור טבלה `theme_sections`
--

INSERT INTO `theme_sections` (`id`, `store_id`, `section_id`, `section_type`, `settings`, `section_order`, `is_visible`, `created_at`, `updated_at`) VALUES
(20, 1, 'header', 'header', '{\"show_cart\": true, \"store_name\": \"ניקול בוטיק\", \"text_color\": \"#1f2937\", \"show_search\": true, \"background_color\": \"#ffffff\"}', 1, 1, '2025-07-15 13:29:33', '2025-07-15 13:29:33'),
(21, 1, 'hero-1752586173', 'hero', '{\"title\": \"ברוכים הבאים לחנות שלנו\", \"text_color\": \"#ffffff\", \"button_link\": \"/products\", \"button_text\": \"קנה עכשיו\", \"description\": \"גלה את המוצרים הטובים ביותר במחירים מעולים\", \"button_color\": \"#f59e0b\", \"background_color\": \"#1e40af\"}', 2, 1, '2025-07-15 13:29:33', '2025-07-15 13:29:33'),
(22, 1, 'featured-products-1752586173', 'featured-products', '{\"title\": \"מוצרים מומלצים\", \"columns\": 4, \"show_price\": true, \"products_count\": 4, \"show_add_to_cart\": true}', 3, 1, '2025-07-15 13:29:33', '2025-07-15 13:29:33'),
(23, 1, 'footer', 'footer', '{\"copyright\": \"© 2024 ניקול בוטיק. כל הזכויות שמורות.\", \"text_color\": \"#ffffff\", \"contact_email\": \"\", \"contact_phone\": \"\", \"facebook_link\": \"\", \"instagram_link\": \"\", \"background_color\": \"#1f2937\"}', 4, 1, '2025-07-15 13:29:33', '2025-07-15 13:29:33');

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `theme_versions`
--

CREATE TABLE `theme_versions` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `theme_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `theme_settings` json DEFAULT NULL,
  `blocks_data` json DEFAULT NULL,
  `templates_data` json DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `tracking_codes`
--

CREATE TABLE `tracking_codes` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_type` enum('header','footer','body_start','body_end','order_confirmation') COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','suspended','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `subscription_status` enum('trial','active','overdue','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'trial',
  `trial_ends_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- הוצאת מידע עבור טבלה `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `first_name`, `last_name`, `phone`, `status`, `subscription_status`, `trial_ends_at`, `created_at`, `updated_at`) VALUES
(1, 'itadmit@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$RWxWdWRXSThSSXAxbnkvUA$iw6nkmIy2YI9RJsuQ60K8bbIJTlRrn2hQCB0r2Qoc+s', 'יוגב', 'אביטן', '+972542284283', 'active', 'trial', '2025-07-23 16:17:46', '2025-07-09 16:17:46', '2025-07-09 16:17:46');

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `variant_attribute_values`
--

CREATE TABLE `variant_attribute_values` (
  `variant_id` int(11) NOT NULL,
  `attribute_value_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- אינדקסים לטבלה `analytics_pixels`
--
ALTER TABLE `analytics_pixels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_store_type` (`store_id`,`pixel_type`),
  ADD KEY `idx_active` (`is_active`);

--
-- אינדקסים לטבלה `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `api_key` (`api_key`),
  ADD KEY `idx_store_active` (`store_id`,`is_active`),
  ADD KEY `idx_api_key` (`api_key`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- אינדקסים לטבלה `attributes`
--
ALTER TABLE `attributes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`name`);

--
-- אינדקסים לטבלה `attribute_values`
--
ALTER TABLE `attribute_values`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_attribute` (`attribute_id`);

--
-- אינדקסים לטבלה `automations`
--
ALTER TABLE `automations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_store_trigger` (`store_id`,`trigger_event`),
  ADD KEY `idx_active` (`is_active`);

--
-- אינדקסים לטבלה `auto_suggestions`
--
ALTER TABLE `auto_suggestions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_store_trigger` (`store_id`,`trigger_type`),
  ADD KEY `idx_active` (`is_active`);

--
-- אינדקסים לטבלה `builder_pages`
--
ALTER TABLE `builder_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_store_page_type` (`store_id`,`page_type`),
  ADD KEY `idx_store_pages` (`store_id`,`page_type`),
  ADD KEY `idx_published_pages` (`store_id`,`is_published`);

--
-- אינדקסים לטבלה `builder_templates`
--
ALTER TABLE `builder_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_template_category` (`category`),
  ADD KEY `idx_template_premium` (`is_premium`);

--
-- אינדקסים לטבלה `bundle_products`
--
ALTER TABLE `bundle_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_bundle_product` (`bundle_id`,`product_id`),
  ADD KEY `idx_bundle` (`bundle_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- אינדקסים לטבלה `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_store_slug` (`store_id`,`slug`),
  ADD KEY `idx_store_active` (`store_id`,`is_active`),
  ADD KEY `idx_parent` (`parent_id`);

--
-- אינדקסים לטבלה `commissions`
--
ALTER TABLE `commissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_store_period` (`store_id`,`period_start`,`period_end`);

--
-- אינדקסים לטבלה `custom_blocks`
--
ALTER TABLE `custom_blocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_store_page` (`store_id`,`page_type`),
  ADD KEY `idx_sort_order` (`sort_order`);

--
-- אינדקסים לטבלה `custom_code`
--
ALTER TABLE `custom_code`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_store_type` (`store_id`,`code_type`),
  ADD KEY `idx_active` (`is_active`);

--
-- אינדקסים לטבלה `custom_domains`
--
ALTER TABLE `custom_domains`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `domain_name` (`domain_name`),
  ADD KEY `idx_store_status` (`store_id`,`status`),
  ADD KEY `idx_domain` (`domain_name`);

--
-- אינדקסים לטבלה `custom_field_types`
--
ALTER TABLE `custom_field_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_store_type` (`store_id`,`field_type`),
  ADD KEY `idx_active` (`is_active`);

--
-- אינדקסים לטבלה `custom_sections`
--
ALTER TABLE `custom_sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_section` (`store_id`,`page_type`,`section_id`);

--
-- אינדקסים לטבלה `custom_templates`
--
ALTER TABLE `custom_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_store_template` (`store_id`,`template_name`);

--
-- אינדקסים לטבלה `gdpr_settings`
--
ALTER TABLE `gdpr_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_store_gdpr` (`store_id`);

--
-- אינדקסים לטבלה `global_accordions`
--
ALTER TABLE `global_accordions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_store_active` (`store_id`,`is_active`);

--
-- אינדקסים לטבלה `import_jobs`
--
ALTER TABLE `import_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `import_id` (`import_id`),
  ADD KEY `store_id` (`store_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status`);

--
-- אינדקסים לטבלה `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_store_notification` (`store_id`,`notification_type`);

--
-- אינדקסים לטבלה `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_store_order_number` (`store_id`,`order_number`),
  ADD KEY `idx_store_status` (`store_id`,`status`),
  ADD KEY `idx_customer_email` (`customer_email`),
  ADD KEY `idx_order_date` (`order_date`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `payment_status_id` (`payment_status_id`),
  ADD KEY `idx_order_status` (`order_status_id`);

--
-- אינדקסים לטבלה `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_variant` (`variant_id`);

--
-- אינדקסים לטבלה `order_statuses`
--
ALTER TABLE `order_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_store_slug` (`store_id`,`slug`),
  ADD KEY `idx_store_active` (`store_id`,`is_active`),
  ADD KEY `idx_sort_order` (`sort_order`),
  ADD KEY `idx_system` (`is_system`);

--
-- אינדקסים לטבלה `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_changed_at` (`changed_at`);

--
-- אינדקסים לטבלה `payment_providers`
--
ALTER TABLE `payment_providers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_store_provider` (`store_id`,`provider_name`),
  ADD KEY `idx_store_active` (`store_id`,`is_active`);

--
-- אינדקסים לטבלה `payment_statuses`
--
ALTER TABLE `payment_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_store_slug` (`store_id`,`slug`),
  ADD KEY `idx_store_active` (`store_id`,`is_active`),
  ADD KEY `idx_sort_order` (`sort_order`),
  ADD KEY `idx_system` (`is_system`);

--
-- אינדקסים לטבלה `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- אינדקסים לטבלה `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_store_slug` (`store_id`,`slug`),
  ADD KEY `idx_store_status` (`store_id`,`status`),
  ADD KEY `idx_featured` (`featured`),
  ADD KEY `idx_sku` (`sku`),
  ADD KEY `idx_barcode` (`barcode`),
  ADD KEY `idx_vendor` (`vendor`),
  ADD KEY `idx_product_type` (`product_type`);

--
-- אינדקסים לטבלה `product_accordions`
--
ALTER TABLE `product_accordions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_active` (`product_id`,`is_active`),
  ADD KEY `idx_sort` (`sort_order`);

--
-- אינדקסים לטבלה `product_attributes`
--
ALTER TABLE `product_attributes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`);

--
-- אינדקסים לטבלה `product_badges`
--
ALTER TABLE `product_badges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_active` (`product_id`,`is_active`);

--
-- אינדקסים לטבלה `product_bundles`
--
ALTER TABLE `product_bundles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_store_active` (`store_id`,`is_active`),
  ADD KEY `idx_validity` (`valid_from`,`valid_until`);

--
-- אינדקסים לטבלה `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`product_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- אינדקסים לטבלה `product_collections`
--
ALTER TABLE `product_collections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_store_type` (`store_id`,`type`),
  ADD KEY `idx_active` (`is_active`);

--
-- אינדקסים לטבלה `product_collection_items`
--
ALTER TABLE `product_collection_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_collection_product` (`collection_id`,`product_id`),
  ADD KEY `idx_collection` (`collection_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- אינדקסים לטבלה `product_custom_fields`
--
ALTER TABLE `product_custom_fields`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_product_field` (`product_id`,`field_type_id`),
  ADD KEY `field_type_id` (`field_type_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- אינדקסים לטבלה `product_media`
--
ALTER TABLE `product_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_gallery` (`product_id`,`gallery_value`),
  ADD KEY `idx_primary` (`is_primary`);

--
-- אינדקסים לטבלה `product_relationships`
--
ALTER TABLE `product_relationships`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_product_relation` (`main_product_id`,`related_product_id`,`relationship_type`),
  ADD KEY `idx_main_product` (`main_product_id`,`relationship_type`),
  ADD KEY `idx_related_product` (`related_product_id`);

--
-- אינדקסים לטבלה `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_sku` (`sku`),
  ADD KEY `idx_active` (`is_active`);

--
-- אינדקסים לטבלה `shipping_companies`
--
ALTER TABLE `shipping_companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_store_company` (`store_id`,`company_name`),
  ADD KEY `idx_store_active` (`store_id`,`is_active`);

--
-- אינדקסים לטבלה `shipping_methods`
--
ALTER TABLE `shipping_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_id` (`store_id`),
  ADD KEY `idx_zone_active` (`zone_id`,`is_active`);

--
-- אינדקסים לטבלה `shipping_zones`
--
ALTER TABLE `shipping_zones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_store_active` (`store_id`,`is_active`);

--
-- אינדקסים לטבלה `sms_settings`
--
ALTER TABLE `sms_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_store_sms` (`store_id`);

--
-- אינדקסים לטבלה `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `domain` (`domain`),
  ADD UNIQUE KEY `unique_slug` (`slug`),
  ADD KEY `idx_domain` (`domain`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_stores_slug` (`slug`),
  ADD KEY `idx_stores_custom_domain` (`custom_domain`),
  ADD KEY `idx_stores_theme` (`theme_name`),
  ADD KEY `idx_stores_status` (`status`);

--
-- אינדקסים לטבלה `store_access`
--
ALTER TABLE `store_access`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_store_email` (`store_id`,`email`),
  ADD KEY `idx_store_role` (`store_id`,`role`),
  ADD KEY `idx_active` (`is_active`);

--
-- אינדקסים לטבלה `store_pages`
--
ALTER TABLE `store_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_store_page` (`store_id`,`page_type`);

--
-- אינדקסים לטבלה `store_settings`
--
ALTER TABLE `store_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_store_setting` (`store_id`,`setting_key`);

--
-- אינדקסים לטבלה `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_billing` (`user_id`,`billing_date`),
  ADD KEY `idx_status` (`status`);

--
-- אינדקסים לטבלה `system_billings`
--
ALTER TABLE `system_billings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_store_period` (`store_id`,`billing_period_start`,`billing_period_end`),
  ADD KEY `idx_status_due` (`status`,`due_date`),
  ADD KEY `idx_invoice` (`invoice_number`);

--
-- אינדקסים לטבלה `theme_customizations`
--
ALTER TABLE `theme_customizations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_store_customization` (`store_id`,`theme_name`,`customization_key`);

--
-- אינדקסים לטבלה `theme_metadata`
--
ALTER TABLE `theme_metadata`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_theme_name` (`theme_name`);

--
-- אינדקסים לטבלה `theme_sections`
--
ALTER TABLE `theme_sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_section_per_store` (`store_id`,`section_id`),
  ADD KEY `idx_store_order` (`store_id`,`section_order`),
  ADD KEY `idx_section_type` (`section_type`);

--
-- אינדקסים לטבלה `theme_versions`
--
ALTER TABLE `theme_versions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_store_theme` (`store_id`,`theme_name`);

--
-- אינדקסים לטבלה `tracking_codes`
--
ALTER TABLE `tracking_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_store_type` (`store_id`,`code_type`),
  ADD KEY `idx_active` (`is_active`);

--
-- אינדקסים לטבלה `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`);

--
-- אינדקסים לטבלה `variant_attribute_values`
--
ALTER TABLE `variant_attribute_values`
  ADD PRIMARY KEY (`variant_id`,`attribute_value_id`),
  ADD KEY `attribute_value_id` (`attribute_value_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `analytics_pixels`
--
ALTER TABLE `analytics_pixels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `api_keys`
--
ALTER TABLE `api_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attributes`
--
ALTER TABLE `attributes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `attribute_values`
--
ALTER TABLE `attribute_values`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `automations`
--
ALTER TABLE `automations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auto_suggestions`
--
ALTER TABLE `auto_suggestions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `builder_pages`
--
ALTER TABLE `builder_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `builder_templates`
--
ALTER TABLE `builder_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bundle_products`
--
ALTER TABLE `bundle_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commissions`
--
ALTER TABLE `commissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_blocks`
--
ALTER TABLE `custom_blocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `custom_code`
--
ALTER TABLE `custom_code`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_domains`
--
ALTER TABLE `custom_domains`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_field_types`
--
ALTER TABLE `custom_field_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_sections`
--
ALTER TABLE `custom_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `custom_templates`
--
ALTER TABLE `custom_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gdpr_settings`
--
ALTER TABLE `gdpr_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `global_accordions`
--
ALTER TABLE `global_accordions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `import_jobs`
--
ALTER TABLE `import_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notification_settings`
--
ALTER TABLE `notification_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `order_statuses`
--
ALTER TABLE `order_statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payment_providers`
--
ALTER TABLE `payment_providers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_statuses`
--
ALTER TABLE `payment_statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `product_accordions`
--
ALTER TABLE `product_accordions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `product_attributes`
--
ALTER TABLE `product_attributes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_badges`
--
ALTER TABLE `product_badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `product_bundles`
--
ALTER TABLE `product_bundles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_collections`
--
ALTER TABLE `product_collections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_collection_items`
--
ALTER TABLE `product_collection_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_custom_fields`
--
ALTER TABLE `product_custom_fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_media`
--
ALTER TABLE `product_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_relationships`
--
ALTER TABLE `product_relationships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shipping_companies`
--
ALTER TABLE `shipping_companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shipping_methods`
--
ALTER TABLE `shipping_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shipping_zones`
--
ALTER TABLE `shipping_zones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms_settings`
--
ALTER TABLE `sms_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `store_access`
--
ALTER TABLE `store_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `store_pages`
--
ALTER TABLE `store_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `store_settings`
--
ALTER TABLE `store_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_billings`
--
ALTER TABLE `system_billings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `theme_customizations`
--
ALTER TABLE `theme_customizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `theme_metadata`
--
ALTER TABLE `theme_metadata`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `theme_sections`
--
ALTER TABLE `theme_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `theme_versions`
--
ALTER TABLE `theme_versions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tracking_codes`
--
ALTER TABLE `tracking_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- הגבלות לטבלאות שהוצאו
--

--
-- הגבלות לטבלה `analytics_pixels`
--
ALTER TABLE `analytics_pixels`
  ADD CONSTRAINT `analytics_pixels_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `api_keys`
--
ALTER TABLE `api_keys`
  ADD CONSTRAINT `api_keys_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `attribute_values`
--
ALTER TABLE `attribute_values`
  ADD CONSTRAINT `attribute_values_ibfk_1` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `automations`
--
ALTER TABLE `automations`
  ADD CONSTRAINT `automations_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `auto_suggestions`
--
ALTER TABLE `auto_suggestions`
  ADD CONSTRAINT `auto_suggestions_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `bundle_products`
--
ALTER TABLE `bundle_products`
  ADD CONSTRAINT `bundle_products_ibfk_1` FOREIGN KEY (`bundle_id`) REFERENCES `product_bundles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bundle_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `categories_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- הגבלות לטבלה `commissions`
--
ALTER TABLE `commissions`
  ADD CONSTRAINT `commissions_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `custom_blocks`
--
ALTER TABLE `custom_blocks`
  ADD CONSTRAINT `custom_blocks_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `custom_code`
--
ALTER TABLE `custom_code`
  ADD CONSTRAINT `custom_code_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `custom_domains`
--
ALTER TABLE `custom_domains`
  ADD CONSTRAINT `custom_domains_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `custom_field_types`
--
ALTER TABLE `custom_field_types`
  ADD CONSTRAINT `custom_field_types_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `custom_templates`
--
ALTER TABLE `custom_templates`
  ADD CONSTRAINT `custom_templates_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `gdpr_settings`
--
ALTER TABLE `gdpr_settings`
  ADD CONSTRAINT `gdpr_settings_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `global_accordions`
--
ALTER TABLE `global_accordions`
  ADD CONSTRAINT `global_accordions_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `import_jobs`
--
ALTER TABLE `import_jobs`
  ADD CONSTRAINT `import_jobs_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `import_jobs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD CONSTRAINT `notification_settings_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`order_status_id`) REFERENCES `order_statuses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`payment_status_id`) REFERENCES `payment_statuses` (`id`) ON DELETE SET NULL;

--
-- הגבלות לטבלה `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- הגבלות לטבלה `order_statuses`
--
ALTER TABLE `order_statuses`
  ADD CONSTRAINT `order_statuses_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `payment_providers`
--
ALTER TABLE `payment_providers`
  ADD CONSTRAINT `payment_providers_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `payment_statuses`
--
ALTER TABLE `payment_statuses`
  ADD CONSTRAINT `payment_statuses_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `product_accordions`
--
ALTER TABLE `product_accordions`
  ADD CONSTRAINT `product_accordions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `product_attributes`
--
ALTER TABLE `product_attributes`
  ADD CONSTRAINT `product_attributes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `product_badges`
--
ALTER TABLE `product_badges`
  ADD CONSTRAINT `product_badges_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `product_bundles`
--
ALTER TABLE `product_bundles`
  ADD CONSTRAINT `product_bundles_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `product_categories`
--
ALTER TABLE `product_categories`
  ADD CONSTRAINT `product_categories_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `product_collections`
--
ALTER TABLE `product_collections`
  ADD CONSTRAINT `product_collections_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `product_collection_items`
--
ALTER TABLE `product_collection_items`
  ADD CONSTRAINT `product_collection_items_ibfk_1` FOREIGN KEY (`collection_id`) REFERENCES `product_collections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_collection_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `product_custom_fields`
--
ALTER TABLE `product_custom_fields`
  ADD CONSTRAINT `product_custom_fields_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_custom_fields_ibfk_2` FOREIGN KEY (`field_type_id`) REFERENCES `custom_field_types` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `product_media`
--
ALTER TABLE `product_media`
  ADD CONSTRAINT `product_media_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `product_relationships`
--
ALTER TABLE `product_relationships`
  ADD CONSTRAINT `product_relationships_ibfk_1` FOREIGN KEY (`main_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_relationships_ibfk_2` FOREIGN KEY (`related_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `shipping_companies`
--
ALTER TABLE `shipping_companies`
  ADD CONSTRAINT `shipping_companies_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `shipping_methods`
--
ALTER TABLE `shipping_methods`
  ADD CONSTRAINT `shipping_methods_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shipping_methods_ibfk_2` FOREIGN KEY (`zone_id`) REFERENCES `shipping_zones` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `shipping_zones`
--
ALTER TABLE `shipping_zones`
  ADD CONSTRAINT `shipping_zones_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `sms_settings`
--
ALTER TABLE `sms_settings`
  ADD CONSTRAINT `sms_settings_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `stores`
--
ALTER TABLE `stores`
  ADD CONSTRAINT `stores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `store_access`
--
ALTER TABLE `store_access`
  ADD CONSTRAINT `store_access_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `store_settings`
--
ALTER TABLE `store_settings`
  ADD CONSTRAINT `store_settings_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `system_billings`
--
ALTER TABLE `system_billings`
  ADD CONSTRAINT `system_billings_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `theme_customizations`
--
ALTER TABLE `theme_customizations`
  ADD CONSTRAINT `theme_customizations_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `theme_sections`
--
ALTER TABLE `theme_sections`
  ADD CONSTRAINT `theme_sections_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `theme_versions`
--
ALTER TABLE `theme_versions`
  ADD CONSTRAINT `theme_versions_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `tracking_codes`
--
ALTER TABLE `tracking_codes`
  ADD CONSTRAINT `tracking_codes_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- הגבלות לטבלה `variant_attribute_values`
--
ALTER TABLE `variant_attribute_values`
  ADD CONSTRAINT `variant_attribute_values_ibfk_1` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `variant_attribute_values_ibfk_2` FOREIGN KEY (`attribute_value_id`) REFERENCES `attribute_values` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 23, 2026 at 08:27 AM
-- Server version: 11.8.8-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u456280272_crm`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin') NOT NULL DEFAULT 'admin',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `phone`, `password`, `role`, `status`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Amit Super Admin', 'tamitkumar30@gmail.com', '6295726764', '$2y$10$GlKQ0lydP.biACudJyAP1OLZwghcnuu97/tRo5QN8dvctqNTeiY3i', 'super_admin', 'active', 'jSa5TugT0rZOe8Md4CZkPDoSYa8hoSkV6grSd5Yp59nQxBTIwSYlWKPDEeH1', NULL, NULL),
(2, 'Just Admin', 'admin@gmail.com', '+916295726764', '$2y$12$XrckMyQEqAFJ7IVRM.sE6OBP3IQyTvZ9tvI8JAS6sxhn9p.zEjfPO', 'admin', 'active', '1rJs1ifilMl2ITFy7peQWhaZl8YYXfoJyVoWiBh0AnIpHsyn0ldVih0nSfDF', '2026-07-04 07:07:20', '2026-07-09 11:10:30'),
(3, 'Sunita', 'negisunita@gmail.com', '9625855545', '$2y$12$nOSQw1Maz/NhBjJ.E.SYNeFF.BCUOZFJDlx/kBZEi4JqrvqK6yaHG', 'admin', 'active', 'JWaPukGbNJecNy1ooksYpvdhSxtXD6a1YS8Fna5l48L4cCK2Kf5NtBBZcYDo', '2026-07-17 13:33:55', '2026-07-17 13:39:52'),
(4, 'Balvindar Singh', 'balwinder@gmail.com', '+919115123929', '$2y$12$5RWVM1V4felYIcs84qkVnuUDLuZxp.kJDVFIXwnm4mes3YWABrEsC', 'super_admin', 'active', 'GkSiKbrpLZH2jUNzdwLoqsR2wy2Yzb9kTIYR6UABj6LPNH8U2WLXgbdV7Dc2', '2026-07-19 19:42:42', '2026-07-19 19:42:42');

-- --------------------------------------------------------

--
-- Table structure for table `banks`
--

CREATE TABLE `banks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `account_holder_name` varchar(255) NOT NULL,
  `account_number` varchar(255) NOT NULL,
  `ifsc_code` varchar(255) NOT NULL,
  `branch` varchar(255) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `banks`
--

INSERT INTO `banks` (`id`, `bank_name`, `account_holder_name`, `account_number`, `ifsc_code`, `branch`, `qr_code`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Kotak Mahindra Bank', 'Joy Enterprises', '9748287605', 'KKBK0000185', 'Burari Delhi', 'banks/aErpMaF4ySTYQ0DynTinqeZFJrNOHy1aE3peGkLm.jpg', 1, '2026-07-08 09:32:19', '2026-07-19 19:30:53');

-- --------------------------------------------------------

--
-- Table structure for table `billing_headers`
--

CREATE TABLE `billing_headers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `gstin` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `billing_headers`
--

INSERT INTO `billing_headers` (`id`, `company_name`, `logo`, `gstin`, `phone`, `address`, `is_default`, `created_by`, `created_at`, `updated_at`) VALUES
(3, 'JOY ENTERPRISES', 'billing-headers/rLOaCmzcs7CF7JkBrogqdkvYOy8xmLu2NN7C5t6h.jpg', '07CGCPS8670N1ZK', '9780096757', '2nd floor west sant nagar, chandan vihar, burari.', 0, 1, '2026-07-19 19:25:33', '2026-07-19 19:25:33');

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bill_number` varchar(255) NOT NULL,
  `bill_type` varchar(255) NOT NULL,
  `lead_id` bigint(20) UNSIGNED DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `gst_number` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `ship_same_as_billing` tinyint(1) NOT NULL DEFAULT 1,
  `billing_date` date NOT NULL,
  `valid_till` date DEFAULT NULL,
  `billing_header_id` bigint(20) UNSIGNED DEFAULT NULL,
  `bank_id` bigint(20) UNSIGNED DEFAULT NULL,
  `courier_name` varchar(255) DEFAULT NULL,
  `courier_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `courier_tax_type` varchar(255) DEFAULT NULL,
  `courier_tax_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `courier_tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_tax` decimal(12,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stock_deducted` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bills`
--

INSERT INTO `bills` (`id`, `bill_number`, `bill_type`, `lead_id`, `customer_name`, `company_name`, `gst_number`, `phone`, `email`, `billing_address`, `shipping_address`, `ship_same_as_billing`, `billing_date`, `valid_till`, `billing_header_id`, `bank_id`, `courier_name`, `courier_price`, `courier_tax_type`, `courier_tax_percent`, `courier_tax_amount`, `subtotal`, `total_tax`, `grand_total`, `stock_deducted`, `created_by`, `created_at`, `updated_at`) VALUES
(6, 'QUO-0001', 'quotation', NULL, 'Balvindar Singh', 'JOY ENTER', '1231232134143', '09115123929', NULL, 'Upkar colony\ndelhi burari', 'Upkar colony\ndelhi burari', 1, '2026-07-19', '2026-07-21', 3, 1, 'fEDEX', 120.00, NULL, 0.00, 0.00, 8570.00, 1014.00, 9584.00, 0, 1, '2026-07-19 19:36:48', '2026-07-19 19:36:48');

-- --------------------------------------------------------

--
-- Table structure for table `bill_items`
--

CREATE TABLE `bill_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bill_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `hsn_sku` varchar(255) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `quantity` decimal(12,2) NOT NULL,
  `taxable_amount` decimal(12,2) NOT NULL,
  `taxes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`taxes`)),
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bill_items`
--

INSERT INTO `bill_items` (`id`, `bill_id`, `product_id`, `product_name`, `hsn_sku`, `unit`, `price`, `quantity`, `taxable_amount`, `taxes`, `tax_amount`, `total`, `created_at`, `updated_at`) VALUES
(8, 6, 5, 'Ev Spare Part', 'HSK002', 'pcs', 8450.00, 1.00, 8450.00, '[{\"type\":\"CGST\",\"percent\":12,\"amount\":1014}]', 1014.00, 9464.00, '2026-07-19 19:36:48', '2026-07-19 19:36:48');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-query_normalizer.vocabulary', 'a:17:{s:7:\"lithium\";b:1;s:8:\"battries\";b:1;s:4:\"octa\";b:1;s:7:\"product\";b:1;s:7:\"catalog\";b:1;s:8:\"brochure\";b:1;s:7:\"battery\";b:1;s:8:\"warranty\";b:1;s:9:\"guarantee\";b:1;s:8:\"delivery\";b:1;s:7:\"payment\";b:1;s:11:\"installment\";b:1;s:3:\"emi\";b:1;s:5:\"stock\";b:1;s:8:\"showroom\";b:1;s:10:\"dealership\";b:1;s:7:\"charger\";b:1;}', 1783769845);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_intents`
--

CREATE TABLE `chatbot_intents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `intent` varchar(255) NOT NULL,
  `keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`keywords`)),
  `responses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`responses`)),
  `priority` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chatbot_intents`
--

INSERT INTO `chatbot_intents` (`id`, `intent`, `keywords`, `responses`, `priority`, `created_at`, `updated_at`) VALUES
(1, 'greeting', '[\"hi\",\"hello\",\"hii\",\"hey\",\"namaste\",\"good morning\",\"good evening\"]', '[\"Hello \\ud83d\\udc4b Welcome to Jojo EV. How may I help you today?\",\"Hi \\ud83d\\udc4b Thank you for contacting Jojo EV. How can I help you?\",\"Namaste \\ud83d\\ude4f Jojo EV mein aapka swagat hai. Main aapki kis tarah madad kar sakta hoon?\"]', 1, NULL, NULL),
(2, 'ad', '[\"ad\",\"advertisement\",\"facebook\",\"instagram\",\"youtube\",\"google\"]', '[\"Thank you for your interest \\ud83d\\ude0a Which EV product are you looking for?\",\"Glad you contacted us. Please tell us which product you want.\"]', 2, NULL, NULL),
(3, 'thanks', '[\"thanks\",\"thank you\",\"ok\",\"okay\"]', '[\"You\'re welcome \\ud83d\\ude0a\",\"Happy to help.\",\"Thank you.\"]', 3, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_settings`
--

CREATE TABLE `chatbot_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ai_globally_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `openai_api_key` text DEFAULT NULL,
  `openai_model` varchar(255) NOT NULL DEFAULT 'gpt-4o-mini',
  `openai_embedding_model` varchar(255) NOT NULL DEFAULT 'text-embedding-3-small',
  `confidence_threshold` tinyint(3) UNSIGNED NOT NULL DEFAULT 60,
  `system_prompt` text DEFAULT NULL,
  `fallback_message_en` varchar(255) NOT NULL DEFAULT 'Our executive will contact you.',
  `fallback_message_hi` varchar(255) NOT NULL DEFAULT 'हमारा प्रतिनिधि आपसे संपर्क करेगा।',
  `fallback_message_bn` varchar(255) NOT NULL DEFAULT 'আমাদের প্রতিনিধি আপনার সাথে যোগাযোগ করবেন।',
  `whatsapp_phone_number_id` varchar(255) DEFAULT NULL,
  `whatsapp_business_account_id` varchar(255) DEFAULT NULL,
  `whatsapp_access_token` text DEFAULT NULL,
  `whatsapp_webhook_verify_token` varchar(255) DEFAULT NULL,
  `whatsapp_api_version` varchar(255) NOT NULL DEFAULT 'v20.0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chatbot_settings`
--

INSERT INTO `chatbot_settings` (`id`, `ai_globally_enabled`, `openai_api_key`, `openai_model`, `openai_embedding_model`, `confidence_threshold`, `system_prompt`, `fallback_message_en`, `fallback_message_hi`, `fallback_message_bn`, `whatsapp_phone_number_id`, `whatsapp_business_account_id`, `whatsapp_access_token`, `whatsapp_webhook_verify_token`, `whatsapp_api_version`, `created_at`, `updated_at`) VALUES
(1, 1, 'eyJpdiI6Ijk2MXFSQUMxUTd0dElUYkRQa3c5WFE9PSIsInZhbHVlIjoiSTBTZXBQNHMrSDY1N2ZhSk81NllYM0tpY2ppQmkwUlB5MG5ickQ2OG94M2Fqa3YxbGkzazFoYUdaa0h3MkJBa3FkOXZQaVVCdGFuVzF0aU5aNVB3aHpYcFFjSEZTcTdpN2dqRnBPRUo3dEY0cnVzUWJUN3NtbW5aUnA5VUxMOXM1MUk2NVZzQ09hUGdDcHdoN01sY0IzRE44cXRRbWY0SW5rU05jMWFDdXB2TDgxR3FKR3JaSUtZdXVYOEVlcTg5SlprYS9GVW1rY0dJMVRxSGpyNmZ5RFNtL0N4dlgzMnEwWUhRQmlqaUtzcz0iLCJtYWMiOiJhYzM2ZjQ1MjhiNjE2MjY2MzU2ZjRhNzMyZmZkZmFlM2RlY2I3N2EwMWJlOTJiYTlmZDNjZDBkMDZjNWI2Zjk5IiwidGFnIjoiIn0=', 'gpt-5.4-mini', 'text-embedding-3-small', 60, NULL, 'Maaf kijiye, main abhi puri tarah confirm nahi kar pa raha. Hamara executive jaldi hi aapse contact karega.', 'हमारा प्रतिनिधि आपसे संपर्क करेगा।', 'আমাদের প্রতিনিধি আপনার সাথে যোগাযোগ করবেন।', '1246995381823770', '24938238129192627', 'eyJpdiI6IlZvNDR3MmJoeDk4T3JndSsyOUUzdWc9PSIsInZhbHVlIjoiTXVZMEcrT0l1bnpFN2hGQUZvWnVMcXU1YXVhNEFJVWdzU0ZpTjVyckVEZTYza3ZmczNMS09hRFhIRWlsQXYzWHFnSnRwOHN2Z2lGTDJFaGhtUG9qRGJDRCt6dkNGWEpzN0Q5ZDkySXRld3FmU3VONU5mNGFieTBKY0p2QTJWQU1zSGk1OW9hT295N1o4T2c4VG4rYS9XWjNVVmdzOHJkbmRhYTVaN2NGcUVJTDc4eHNFU0ZEeXcybzdXRzZQdktEQ0xLR0kxY3lIS0d2NnZha2M4b2F2MHZQUWM4MThlZlNPcjl5b0ZXMURabHBWNGV4eW5oV25iTHhtdXl2TWxNQUs5MXhDSFRkZlh0YVNIdWdTOE96bHc9PSIsIm1hYyI6ImUyMTJjYjljM2E0ZGE2NGE4NTgwYmQ0YWRhMzdkZGY0OTA5MDQ4MjNiNDA3YzYxNzBiYWYwMzMxMWY0NGY0YWQiLCJ0YWciOiIifQ==', '346cf76ac56fe58a655bf97276cc4ed9', 'v25.0', '2026-07-04 12:47:34', '2026-07-10 18:35:07');

-- --------------------------------------------------------

--
-- Table structure for table `chat_conversations`
--

CREATE TABLE `chat_conversations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `wa_phone_number` varchar(255) NOT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `language` varchar(2) DEFAULT NULL,
  `ai_active` tinyint(1) NOT NULL DEFAULT 1,
  `handling_admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `locked_by_admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `locked_at` timestamp NULL DEFAULT NULL,
  `escalated_at` timestamp NULL DEFAULT NULL,
  `lead_id` bigint(20) UNSIGNED DEFAULT NULL,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `last_message_preview` varchar(255) DEFAULT NULL,
  `unread_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `last_customer_message_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `state_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`state_data`)),
  `focus_product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pending_disambiguation` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`pending_disambiguation`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_conversations`
--

INSERT INTO `chat_conversations` (`id`, `wa_phone_number`, `contact_name`, `language`, `ai_active`, `handling_admin_id`, `assigned_admin_id`, `locked_by_admin_id`, `locked_at`, `escalated_at`, `lead_id`, `last_message_at`, `last_message_preview`, `unread_count`, `last_customer_message_at`, `created_at`, `updated_at`, `state`, `state_data`, `focus_product_id`, `pending_disambiguation`) VALUES
(4, '918805774834', 'Pratik Shrikrishna Padol', 'en', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-10 18:48:57', 'Warenty options..??', 0, '2026-07-10 18:48:57', '2026-07-10 18:47:26', '2026-07-11 10:28:20', NULL, NULL, NULL, NULL),
(6, '916295726764', 'amit', 'en', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-11 18:32:31', 'hii', 0, '2026-07-11 11:08:40', '2026-07-11 10:28:40', '2026-07-19 18:30:32', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `chat_intents`
--

CREATE TABLE `chat_intents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `intent` varchar(255) NOT NULL,
  `keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`keywords`)),
  `responses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`responses`)),
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `priority` int(11) NOT NULL DEFAULT 100,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_intents`
--

INSERT INTO `chat_intents` (`id`, `intent`, `keywords`, `responses`, `active`, `priority`, `created_at`, `updated_at`) VALUES
(1, 'greeting', '[\"hi\",\"hello\",\"hii\",\"hey\",\"namaste\",\"good morning\",\"good evening\"]', '[\"Hello \\ud83d\\udc4b Welcome to Jojo EV. How may I help you today?\",\"Hi \\ud83d\\udc4b Thank you for contacting Jojo EV. How can I help you?\",\"Namaste \\ud83d\\ude4f Jojo EV mein aapka swagat hai. Main aapki kis tarah madad kar sakta hoon?\"]', 1, 1, NULL, NULL),
(2, 'ad', '[\"ad\",\"advertisement\",\"facebook\",\"instagram\",\"youtube\",\"google\"]', '[\"Thank you for your interest \\ud83d\\ude0a Which EV product are you looking for?\",\"Glad you contacted us. Please tell us which product you want.\"]', 1, 2, NULL, NULL),
(3, 'thanks', '[\"thanks\",\"thank you\",\"ok\",\"okay\"]', '[\"You\'re welcome \\ud83d\\ude0a\",\"Happy to help.\",\"Thank you.\"]', 1, 3, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `conversation_id` bigint(20) UNSIGNED NOT NULL,
  `direction` enum('inbound','outbound') NOT NULL,
  `sender_type` enum('customer','ai','admin','system') NOT NULL,
  `sender_admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('text','system','image','audio','video','document') NOT NULL DEFAULT 'text',
  `body` text DEFAULT NULL,
  `media_url` varchar(255) DEFAULT NULL,
  `media_mime_type` varchar(255) DEFAULT NULL,
  `media_filename` varchar(255) DEFAULT NULL,
  `media_size` bigint(20) UNSIGNED DEFAULT NULL,
  `wa_message_id` varchar(255) DEFAULT NULL,
  `wa_status` varchar(255) DEFAULT NULL,
  `ai_confidence` tinyint(3) UNSIGNED DEFAULT NULL,
  `kb_article_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`kb_article_ids`)),
  `language` varchar(2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `conversation_id`, `direction`, `sender_type`, `sender_admin_id`, `type`, `body`, `media_url`, `media_mime_type`, `media_filename`, `media_size`, `wa_message_id`, `wa_status`, `ai_confidence`, `kb_article_ids`, `language`, `created_at`, `updated_at`) VALUES
(361, 4, 'outbound', 'system', NULL, 'system', 'Amit Super Admin resumed the AI for this chat.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-11 10:28:20', '2026-07-11 10:28:20'),
(362, 6, 'inbound', 'customer', NULL, 'text', 'cateloge hai', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAEhgWM0VCMEM1QUIzRTc4RjRDRjdGN0MzRAA=', 'received', NULL, NULL, 'en', '2026-07-11 10:28:40', '2026-07-11 10:28:40'),
(363, 6, 'outbound', 'ai', NULL, 'text', 'हमारा प्रतिनिधि आपसे संपर्क करेगा।', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAERgSOTQ3MTNBQ0FDMDY2RUVDMzZGAA==', 'read', 0, '[]', 'hi', '2026-07-11 10:28:46', '2026-07-11 10:28:58'),
(364, 6, 'outbound', 'system', NULL, 'system', 'Amit Super Admin resumed the AI for this chat.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-11 10:36:53', '2026-07-11 10:36:53'),
(365, 6, 'inbound', 'customer', NULL, 'text', 'hi', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAEhgWM0VCMDQxNThDQjc4QjdBQkREMjIxMQA=', 'received', NULL, NULL, 'en', '2026-07-11 10:37:06', '2026-07-11 10:37:06'),
(366, 6, 'outbound', 'ai', NULL, 'text', 'Namaste 🙏 Jojo EV mein aapka swagat hai. Main aapki kis tarah madad kar sakta hoon?', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAERgSMEQ3NzEyQUY1M0Q3ODAzMTQyAA==', 'read', 100, '[]', 'en', '2026-07-11 10:37:09', '2026-07-11 10:37:14'),
(367, 6, 'inbound', 'customer', NULL, 'text', 'product catelog hai', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAEhgWM0VCMDlCMjhDQzcxNjk2NUFENkIyMgA=', 'received', NULL, NULL, 'en', '2026-07-11 10:37:23', '2026-07-11 10:37:23'),
(368, 6, 'outbound', 'ai', NULL, 'text', 'Haan, hamara complete product catalog hai. Aap apni requirements share kar dijiye, hum aapko latest product catalog PDF format mein bhej denge.', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAERgSMDcwNDk4RjA4MDYzNTYwRTE1AA==', 'read', 68, '[20]', 'hi', '2026-07-11 10:37:48', '2026-07-11 10:37:52'),
(369, 6, 'inbound', 'customer', NULL, 'text', 'product catelog hai', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAEhgWM0VCMDlCMjhDQzcxNjk2NUFENkIyMgA=', 'received', NULL, NULL, 'en', '2026-07-11 10:37:57', '2026-07-11 10:37:57'),
(370, 6, 'outbound', 'ai', NULL, 'text', 'Haan, hamara complete product catalog hai. Aap apni requirements share kar dijiye, hum aapko latest product catalog PDF format mein bhej denge.', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAERgSMDkyRDYxNDRBRjVDMkVBRjMyAA==', 'sent', 68, '[20]', 'hi', '2026-07-11 10:38:23', '2026-07-11 10:46:28'),
(371, 6, 'inbound', 'customer', NULL, 'text', 'product catelog hai', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAEhgWM0VCMDlCMjhDQzcxNjk2NUFENkIyMgA=', 'received', NULL, NULL, 'en', '2026-07-11 10:38:32', '2026-07-11 10:38:32'),
(372, 6, 'outbound', 'ai', NULL, 'text', 'Haan, hamara complete product catalog hai. Aap apni requirements share kar dijiye, hum aapko latest product catalog PDF format mein bhej denge.', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAERgSRTA3RjVDOUI2RTBGNzZBQUQ3AA==', 'read', 68, '[20]', 'hi', '2026-07-11 10:39:01', '2026-07-11 11:25:31'),
(373, 6, 'inbound', 'customer', NULL, 'text', 'product catelog hai', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAEhgWM0VCMDlCMjhDQzcxNjk2NUFENkIyMgA=', 'received', NULL, NULL, 'en', '2026-07-11 10:39:25', '2026-07-11 10:39:25'),
(374, 6, 'outbound', 'ai', NULL, 'text', 'Haan, hamara complete product catalog hai. Aap apni requirements share kar dijiye, hum aapko latest product catalog PDF format mein bhej denge.', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAERgSREYyQUFDNTc5Q0ExMjgwQjQ0AA==', 'sent', 68, '[20]', 'hi', '2026-07-11 10:39:51', '2026-07-11 11:08:25'),
(375, 6, 'inbound', 'customer', NULL, 'text', 'product catelog hai', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAEhgWM0VCMDlCMjhDQzcxNjk2NUFENkIyMgA=', 'received', NULL, NULL, 'en', '2026-07-11 10:40:23', '2026-07-11 10:40:23'),
(376, 6, 'outbound', 'ai', NULL, 'text', 'Haan, hamara complete product catalog hai. Aap apni requirements share kar dijiye, hum aapko latest product catalog PDF format mein bhej denge.', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAERgSQkVDMzZDRDlFMTI4Q0UxQzQ0AA==', 'delivered', 68, '[20]', 'hi', '2026-07-11 10:40:49', '2026-07-11 11:03:51'),
(377, 6, 'inbound', 'customer', NULL, 'text', 'product catelog hai', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAEhgWM0VCMDlCMjhDQzcxNjk2NUFENkIyMgA=', 'received', NULL, NULL, 'en', '2026-07-11 10:43:01', '2026-07-11 10:43:01'),
(378, 6, 'outbound', 'ai', NULL, 'text', 'Haan, hamara complete product catalog hai. Aap apni requirements share kar dijiye, hum aapko latest product catalog PDF format mein bhej denge.', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAERgSQjdGRjREMUMzQzZDNThFQkQ0AA==', 'delivered', 68, '[20]', 'hi', '2026-07-11 10:43:28', '2026-07-11 10:47:41'),
(379, 6, 'inbound', 'customer', NULL, 'text', 'mujhe customer support chahaiye', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAEhgWM0VCMEIwMjBDNzUyNkEyMjA2NUEzRQA=', 'received', NULL, NULL, 'en', '2026-07-11 10:49:03', '2026-07-11 10:49:03'),
(380, 6, 'outbound', 'ai', NULL, 'text', 'Haan, aap apni query bhej dijiye — hamari customer support team aapki madad karegi.', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAERgSQTkwNUJCMDBEOEJFRUIyNUIyAA==', 'read', 68, '[21]', 'hi', '2026-07-11 10:49:18', '2026-07-11 10:49:47'),
(381, 6, 'inbound', 'customer', NULL, 'text', 'please ek baar phir se bhej do catelog', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAEhgWM0VCMDgyMzIzMDVENDhCOTAzRUYyMwA=', 'received', NULL, NULL, 'en', '2026-07-11 10:53:47', '2026-07-11 10:53:47'),
(382, 6, 'outbound', 'ai', NULL, 'text', 'Maaf kijiye, main abhi puri tarah confirm nahi kar pa raha. Hamara executive jaldi hi aapse contact karega.', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAERgSMTNENzJDMUExODBDNjVEODVFAA==', 'read', 0, '[]', 'en', '2026-07-11 10:53:53', '2026-07-11 10:54:18'),
(383, 6, 'outbound', 'system', NULL, 'system', 'Amit Super Admin resumed the AI for this chat.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-11 10:54:48', '2026-07-11 10:54:48'),
(384, 6, 'inbound', 'customer', NULL, 'text', 'hi', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAEhgWM0VCMDAzMTYyQTAzMDc2MDlGMkY5OAA=', 'received', NULL, NULL, 'en', '2026-07-11 10:55:15', '2026-07-11 10:55:15'),
(385, 6, 'outbound', 'ai', NULL, 'text', 'Hi 👋 Thank you for contacting Jojo EV. How can I help you?', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAERgSNzcwNzcxMDk2NTEzMzcxNDk5AA==', 'read', 100, '[]', 'en', '2026-07-11 10:55:20', '2026-07-11 10:55:31'),
(386, 6, 'inbound', 'customer', NULL, 'text', 'mujhe customer support se baat karti hai', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAEhgWM0VCMEQyQThCNUFFMzZCRUYzRDQ3NAA=', 'received', NULL, NULL, 'en', '2026-07-11 10:55:45', '2026-07-11 10:55:45'),
(387, 6, 'outbound', 'ai', NULL, 'text', 'Haan, aap apna query bhej dijiye — hamari customer support team aapki madad karegi.', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAERgSMTdCRjNDNzg3QUNEM0MyODlCAA==', 'read', 56, '[21]', 'hi', '2026-07-11 10:55:53', '2026-07-11 10:56:00'),
(388, 6, 'inbound', 'customer', NULL, 'text', 'mujhe catelog bhejo', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAEhgWM0VCMEFBQTMwNzExRkYzMzgyQjAzNwA=', 'received', NULL, NULL, 'en', '2026-07-11 10:56:12', '2026-07-11 10:56:12'),
(389, 6, 'outbound', 'ai', NULL, 'text', 'Maaf kijiye, main abhi puri tarah confirm nahi kar pa raha. Hamara executive jaldi hi aapse contact karega.', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAERgSRkFDRjUzNkM4MDM5OEIwQjJEAA==', 'read', 47, '[20]', 'en', '2026-07-11 10:56:16', '2026-07-11 10:56:23'),
(390, 6, 'inbound', 'customer', NULL, 'text', 'product catelog hai', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAEhgWM0VCMDlCMjhDQzcxNjk2NUFENkIyMgA=', 'received', NULL, NULL, 'en', '2026-07-11 11:08:40', '2026-07-11 11:08:40'),
(391, 6, 'outbound', 'system', NULL, 'system', 'Amit Super Admin took over this chat from the AI.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-11 18:15:13', '2026-07-11 18:15:13'),
(392, 6, 'outbound', 'system', NULL, 'system', 'Amit Super Admin took over this chat from the AI.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-11 18:15:18', '2026-07-11 18:15:18'),
(393, 6, 'outbound', 'system', NULL, 'system', 'Amit Super Admin took over this chat from the AI.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-11 18:15:22', '2026-07-11 18:15:22'),
(394, 6, 'outbound', 'system', NULL, 'system', 'Amit Super Admin resumed the AI for this chat.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-11 18:15:42', '2026-07-11 18:15:42'),
(395, 6, 'outbound', 'system', NULL, 'system', 'Amit Super Admin took over this chat from the AI.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-11 18:15:48', '2026-07-11 18:15:48'),
(396, 6, 'outbound', 'system', NULL, 'system', 'Amit Super Admin resumed the AI for this chat.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-11 18:15:53', '2026-07-11 18:15:53'),
(397, 6, 'outbound', 'system', NULL, 'system', 'Amit Super Admin took over this chat from the AI.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-11 18:16:07', '2026-07-11 18:16:07'),
(398, 6, 'outbound', 'admin', 1, 'text', 'hiii', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAERgSMjI3OUI2NjExMEIzQzg5ODk1AA==', 'sent', NULL, NULL, NULL, '2026-07-11 18:16:15', '2026-07-11 18:16:15'),
(399, 6, 'outbound', 'admin', 1, 'text', 'hii', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAERgSRDg2NzIwNUU0NDdDREE3NUJDAA==', 'sent', NULL, NULL, NULL, '2026-07-11 18:16:56', '2026-07-11 18:16:56'),
(400, 6, 'outbound', 'admin', 1, 'text', 'hello', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAERgSQzQ0NjdCRjJBNkZCRDQzQUVDAA==', 'sent', NULL, NULL, NULL, '2026-07-11 18:26:59', '2026-07-11 18:26:59'),
(401, 6, 'outbound', 'admin', 1, 'text', 'hii', NULL, NULL, NULL, NULL, 'wamid.HBgMOTE2Mjk1NzI2NzY0FQIAERgSMzg3MkZEMTEyRkJFRDA3QTMwAA==', 'sent', NULL, NULL, NULL, '2026-07-11 18:32:31', '2026-07-11 18:32:31'),
(402, 6, 'outbound', 'system', NULL, 'system', 'Amit Super Admin resumed the AI for this chat.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-13 00:16:54', '2026-07-13 00:16:54'),
(403, 6, 'outbound', 'system', NULL, 'system', 'Amit Super Admin took over this chat from the AI.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-19 18:27:52', '2026-07-19 18:27:52'),
(404, 6, 'outbound', 'system', NULL, 'system', 'Amit Super Admin resumed the AI for this chat.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-19 18:30:32', '2026-07-19 18:30:32');

-- --------------------------------------------------------

--
-- Table structure for table `chat_notes`
--

CREATE TABLE `chat_notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `conversation_id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `note` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_query_logs`
--

CREATE TABLE `chat_query_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `conversation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `question` text NOT NULL,
  `normalized_question` text DEFAULT NULL,
  `answer` text DEFAULT NULL,
  `final_score` double DEFAULT NULL,
  `confidence` tinyint(3) UNSIGNED DEFAULT NULL,
  `confidence_tier` varchar(10) DEFAULT NULL,
  `kb_article_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`kb_article_ids`)),
  `product_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`product_ids`)),
  `used_ai` tinyint(1) NOT NULL DEFAULT 0,
  `escalated` tinyint(1) NOT NULL DEFAULT 0,
  `language` varchar(5) DEFAULT NULL,
  `intent` varchar(40) DEFAULT NULL,
  `response_time_ms` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_query_logs`
--

INSERT INTO `chat_query_logs` (`id`, `conversation_id`, `question`, `normalized_question`, `answer`, `final_score`, `confidence`, `confidence_tier`, `kb_article_ids`, `product_ids`, `used_ai`, `escalated`, `language`, `intent`, `response_time_ms`, `created_at`, `updated_at`) VALUES
(1, NULL, 'kaun kaun sa products hai aapkepass', 'kaun kaun sa products hai aapkepass', NULL, 0, 0, 'low', '[]', '[]', 0, 1, 'hi', NULL, 4855, '2026-07-11 09:16:13', '2026-07-11 09:16:13'),
(2, NULL, 'product catelog hai ?', 'products catalog hai', NULL, 0, 0, 'low', '[]', '[]', 0, 1, 'hi', NULL, 4753, '2026-07-11 09:39:32', '2026-07-11 09:39:32'),
(3, NULL, 'product catelog hai ?', 'products catalog hai', NULL, 0, 0, 'low', '[]', '[]', 0, 1, 'hi', NULL, 4859, '2026-07-11 09:49:01', '2026-07-11 09:49:01'),
(4, NULL, 'Catalog', 'catalog', NULL, 0, 0, 'low', '[]', '[]', 0, 1, 'en', NULL, 4537, '2026-07-11 09:54:33', '2026-07-11 09:54:33'),
(5, NULL, 'catelog', 'catalog', NULL, 0.5272, 53, 'low', '[20]', '[]', 0, 1, 'en', NULL, 4460, '2026-07-11 10:16:04', '2026-07-11 10:16:04'),
(6, NULL, 'catelog hai kya ?', 'catalog hai kya', NULL, 0, 0, 'low', '[]', '[]', 0, 1, 'hi', NULL, 3791, '2026-07-11 10:25:03', '2026-07-11 10:25:03'),
(7, 6, 'cateloge hai', 'catalog hai', NULL, 0, 0, 'low', '[]', '[]', 0, 1, 'hi', NULL, 4847, '2026-07-11 10:28:46', '2026-07-11 10:28:46'),
(8, 6, 'product catelog hai', 'product catalog', 'Haan, hamara complete product catalog hai. Aap apni requirements share kar dijiye, hum aapko latest product catalog PDF format mein bhej denge.', 0.6829, 68, 'medium', '[20]', '[]', 1, 0, 'hi', NULL, 23470, '2026-07-11 10:37:48', '2026-07-11 10:37:48'),
(9, 6, 'product catelog hai', 'product catalog', 'Haan, hamara complete product catalog hai. Aap apni requirements share kar dijiye, hum aapko latest product catalog PDF format mein bhej denge.', 0.6829, 68, 'medium', '[20]', '[]', 1, 0, 'hi', NULL, 24789, '2026-07-11 10:38:23', '2026-07-11 10:38:23'),
(10, 6, 'product catelog hai', 'product catalog', 'Haan, hamara complete product catalog hai. Aap apni requirements share kar dijiye, hum aapko latest product catalog PDF format mein bhej denge.', 0.6829, 68, 'medium', '[20]', '[]', 1, 0, 'hi', NULL, 28846, '2026-07-11 10:39:02', '2026-07-11 10:39:02'),
(11, 6, 'product catelog hai', 'product catalog', 'Haan, hamara complete product catalog hai. Aap apni requirements share kar dijiye, hum aapko latest product catalog PDF format mein bhej denge.', 0.6829, 68, 'medium', '[20]', '[]', 1, 0, 'hi', NULL, 25464, '2026-07-11 10:39:51', '2026-07-11 10:39:51'),
(12, 6, 'product catelog hai', 'product catalog', 'Haan, hamara complete product catalog hai. Aap apni requirements share kar dijiye, hum aapko latest product catalog PDF format mein bhej denge.', 0.6829, 68, 'medium', '[20]', '[]', 1, 0, 'hi', NULL, 24960, '2026-07-11 10:40:50', '2026-07-11 10:40:50'),
(13, 6, 'product catelog hai', 'product catalog', 'Haan, hamara complete product catalog hai. Aap apni requirements share kar dijiye, hum aapko latest product catalog PDF format mein bhej denge.', 0.6829, 68, 'medium', '[20]', '[]', 1, 0, 'hi', NULL, 25408, '2026-07-11 10:43:28', '2026-07-11 10:43:28'),
(14, 6, 'mujhe customer support chahaiye', 'customer support chahaiye', 'Haan, aap apni query bhej dijiye — hamari customer support team aapki madad karegi.', 0.6833, 68, 'medium', '[21]', '[]', 1, 0, 'hi', NULL, 14452, '2026-07-11 10:49:19', '2026-07-11 10:49:19'),
(15, 6, 'please ek baar phir se bhej do catelog', 'ek baar phir bhej catalog', NULL, 0, 0, 'low', '[]', '[]', 0, 1, 'en', NULL, 4891, '2026-07-11 10:53:54', '2026-07-11 10:53:54'),
(16, 6, 'mujhe customer support se baat karti hai', 'customer support baat karti', 'Haan, aap apna query bhej dijiye — hamari customer support team aapki madad karegi.', 0.5604, 56, 'medium', '[21]', '[]', 1, 0, 'hi', NULL, 7033, '2026-07-11 10:55:53', '2026-07-11 10:55:53'),
(17, 6, 'mujhe catelog bhejo', 'catalog bhejo', NULL, 0.474, 47, 'low', '[20]', '[]', 0, 1, 'en', NULL, 3477, '2026-07-11 10:56:16', '2026-07-11 10:56:16');

-- --------------------------------------------------------

--
-- Table structure for table `dealers`
--

CREATE TABLE `dealers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dealers`
--

INSERT INTO `dealers` (`id`, `name`, `company_name`, `phone`, `email`, `address`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Ranmesh bhai', 'big', '+916295726764', NULL, 'Mamc subash pallyvk nagar', 1, '2026-07-04 07:33:59', '2026-07-04 07:33:59'),
(2, 'Rakesh', NULL, NULL, NULL, NULL, 1, '2026-07-09 11:35:23', '2026-07-09 11:35:23');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `failed_jobs`
--

INSERT INTO `failed_jobs` (`id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`) VALUES
(1, '901eab16-5c83-45d6-81aa-1228433c5cec', 'database', 'default', '{\"uuid\":\"901eab16-5c83-45d6-81aa-1228433c5cec\",\"displayName\":\"App\\\\Jobs\\\\ProcessIncomingWhatsAppMessage\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcessIncomingWhatsAppMessage\",\"command\":\"O:39:\\\"App\\\\Jobs\\\\ProcessIncomingWhatsAppMessage\\\":2:{s:9:\\\"waMessage\\\";a:6:{s:2:\\\"id\\\";s:11:\\\"ABGGFlA5Fpa\\\";s:9:\\\"timestamp\\\";s:10:\\\"1504902988\\\";s:4:\\\"from\\\";s:11:\\\"16315551181\\\";s:12:\\\"from_user_id\\\";s:23:\\\"US.13491208655302741918\\\";s:4:\\\"type\\\";s:4:\\\"text\\\";s:4:\\\"text\\\";a:1:{s:4:\\\"body\\\";s:22:\\\"this is a text message\\\";}}s:11:\\\"contactName\\\";s:14:\\\"test user name\\\";}\",\"batchId\":null},\"createdAt\":1783665025,\"delay\":null}', 'RuntimeException: OpenAI embedding request failed: {\n    \"error\": {\n        \"message\": \"Incorrect API key provided: Admin@1234. You can find your API key at https://platform.openai.com/account/api-keys.\",\n        \"type\": \"invalid_request_error\",\n        \"param\": null,\n        \"code\": \"invalid_api_key\"\n    }\n}\n in C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\app\\Services\\OpenAiService.php:43\nStack trace:\n#0 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\app\\Services\\KbEmbeddingService.php(66): App\\Services\\OpenAiService->embed(\'this is a text ...\')\n#1 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\app\\Services\\ChatbotReplyService.php(56): App\\Services\\KbEmbeddingService->topMatches(\'this is a text ...\', 5)\n#2 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\app\\Jobs\\ProcessIncomingWhatsAppMessage.php(74): App\\Services\\ChatbotReplyService->handleInboundMessage(Object(App\\Models\\ChatConversation), Object(App\\Models\\ChatMessage))\n#3 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\ProcessIncomingWhatsAppMessage->handle(Object(App\\Services\\ChatbotReplyService), Object(App\\Services\\WhatsAppService))\n#4 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#5 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#6 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#7 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#8 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#9 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\ProcessIncomingWhatsAppMessage))\n#10 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessIncomingWhatsAppMessage))\n#11 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#12 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\ProcessIncomingWhatsAppMessage), false)\n#13 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\ProcessIncomingWhatsAppMessage))\n#14 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessIncomingWhatsAppMessage))\n#15 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#16 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\ProcessIncomingWhatsAppMessage))\n#17 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#18 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(504): Illuminate\\Queue\\Jobs\\Job->fire()\n#19 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(454): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#20 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(212): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#21 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(149): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#22 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(132): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#23 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#24 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#25 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#26 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#27 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#28 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#29 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#30 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#31 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#32 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#34 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#35 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#36 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#37 {main}', '2026-07-10 06:37:12'),
(2, '9eef679f-e64e-4ba4-b111-76ed26008754', 'database', 'default', '{\"uuid\":\"9eef679f-e64e-4ba4-b111-76ed26008754\",\"displayName\":\"App\\\\Jobs\\\\ProcessIncomingWhatsAppMessage\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcessIncomingWhatsAppMessage\",\"command\":\"O:39:\\\"App\\\\Jobs\\\\ProcessIncomingWhatsAppMessage\\\":2:{s:9:\\\"waMessage\\\";a:6:{s:4:\\\"from\\\";s:12:\\\"916295726764\\\";s:12:\\\"from_user_id\\\";s:19:\\\"IN.1041606184998663\\\";s:2:\\\"id\\\";s:78:\\\"wamid.HBgMOTE2Mjk1NzI2NzY0FQIAEhggQTVGREFGMzIyNjNBOTk3OURENkNBMDU0Qzg2OUMyRDEA\\\";s:9:\\\"timestamp\\\";s:10:\\\"1783665141\\\";s:4:\\\"text\\\";a:1:{s:4:\\\"body\\\";s:3:\\\"Hii\\\";}s:4:\\\"type\\\";s:4:\\\"text\\\";}s:11:\\\"contactName\\\";s:4:\\\"amit\\\";}\",\"batchId\":null},\"createdAt\":1783665144,\"delay\":null}', 'RuntimeException: OpenAI embedding request failed: {\n    \"error\": {\n        \"message\": \"Incorrect API key provided: Admin@1234. You can find your API key at https://platform.openai.com/account/api-keys.\",\n        \"type\": \"invalid_request_error\",\n        \"param\": null,\n        \"code\": \"invalid_api_key\"\n    }\n}\n in C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\app\\Services\\OpenAiService.php:43\nStack trace:\n#0 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\app\\Services\\KbEmbeddingService.php(66): App\\Services\\OpenAiService->embed(\'Hii\')\n#1 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\app\\Services\\ChatbotReplyService.php(56): App\\Services\\KbEmbeddingService->topMatches(\'Hii\', 5)\n#2 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\app\\Jobs\\ProcessIncomingWhatsAppMessage.php(74): App\\Services\\ChatbotReplyService->handleInboundMessage(Object(App\\Models\\ChatConversation), Object(App\\Models\\ChatMessage))\n#3 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\ProcessIncomingWhatsAppMessage->handle(Object(App\\Services\\ChatbotReplyService), Object(App\\Services\\WhatsAppService))\n#4 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#5 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#6 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#7 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#8 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(129): Illuminate\\Container\\Container->call(Array)\n#9 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\ProcessIncomingWhatsAppMessage))\n#10 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessIncomingWhatsAppMessage))\n#11 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(133): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#12 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(136): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\ProcessIncomingWhatsAppMessage), false)\n#13 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\ProcessIncomingWhatsAppMessage))\n#14 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessIncomingWhatsAppMessage))\n#15 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#16 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\ProcessIncomingWhatsAppMessage))\n#17 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#18 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(504): Illuminate\\Queue\\Jobs\\Job->fire()\n#19 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(454): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#20 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(212): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#21 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(149): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#22 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(132): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#23 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#24 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#25 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#26 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#27 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#28 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#29 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#30 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#31 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#32 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#34 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#35 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#36 C:\\Users\\Amit IPS\\Desktop\\jojoev\\crm\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#37 {main}', '2026-07-10 06:37:14');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `dealer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `bill_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` decimal(12,2) NOT NULL,
  `type` enum('in','out') NOT NULL DEFAULT 'in',
  `unit` varchar(255) NOT NULL DEFAULT 'pcs',
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `entry_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_items`
--

INSERT INTO `inventory_items` (`id`, `product_id`, `dealer_id`, `bill_item_id`, `quantity`, `type`, `unit`, `price`, `entry_date`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(8, 4, NULL, NULL, 10.00, 'in', 'pcs', 18500.00, '2026-07-11', NULL, 1, '2026-07-11 22:42:49', '2026-07-11 22:42:49'),
(9, 5, NULL, NULL, 10.00, 'in', 'pcs', 8450.00, '2026-07-11', NULL, 1, '2026-07-11 22:45:29', '2026-07-11 22:45:29');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(7, 'default', '{\"uuid\":\"d723ef35-c425-4e08-8266-fd365d92c906\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"36830b99-7e4c-416c-b024-3f8024020785\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1783710251,\"delay\":null}', 0, NULL, 1783710252, 1783710252),
(8, 'default', '{\"uuid\":\"ccfaa7e2-184d-4709-be21-dfe5f44ff9d7\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"36830b99-7e4c-416c-b024-3f8024020785\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:9:\\\"broadcast\\\";}}\",\"batchId\":null},\"createdAt\":1783710252,\"delay\":null}', 0, NULL, 1783710252, 1783710252),
(9, 'default', '{\"uuid\":\"842c3f7d-8f06-45a0-a061-75c0a4086771\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"b77043fc-1637-4692-95e7-762de671aa06\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1783710544,\"delay\":null}', 0, NULL, 1783710544, 1783710544),
(10, 'default', '{\"uuid\":\"2f7c017a-311f-423d-936f-b1080796efc3\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"b77043fc-1637-4692-95e7-762de671aa06\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:9:\\\"broadcast\\\";}}\",\"batchId\":null},\"createdAt\":1783710544,\"delay\":null}', 0, NULL, 1783710544, 1783710544),
(11, 'default', '{\"uuid\":\"143cfb3d-9295-4c8f-9650-c1e9e1258a62\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"ea8cf5f8-fc69-4a36-a124-bc24258710be\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1783711414,\"delay\":null}', 0, NULL, 1783711414, 1783711414),
(12, 'default', '{\"uuid\":\"4c88730b-8be2-407f-bc46-490d46ab9e53\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"ea8cf5f8-fc69-4a36-a124-bc24258710be\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:9:\\\"broadcast\\\";}}\",\"batchId\":null},\"createdAt\":1783711414,\"delay\":null}', 0, NULL, 1783711414, 1783711414),
(13, 'default', '{\"uuid\":\"5b0aead4-5b4b-4165-ac29-eee2d284358e\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"669133c3-71a5-4410-8f6c-da824fe79058\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1783711505,\"delay\":null}', 0, NULL, 1783711505, 1783711505),
(14, 'default', '{\"uuid\":\"0982419b-d156-40f4-9a52-6e0bbadb298f\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"669133c3-71a5-4410-8f6c-da824fe79058\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:9:\\\"broadcast\\\";}}\",\"batchId\":null},\"createdAt\":1783711505,\"delay\":null}', 0, NULL, 1783711505, 1783711505),
(15, 'default', '{\"uuid\":\"20d8cd81-8aea-42c4-bda7-d64f51c8e0e1\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"a49a8f67-9b14-4cf2-a962-fdeffce02819\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1783711875,\"delay\":null}', 0, NULL, 1783711875, 1783711875),
(16, 'default', '{\"uuid\":\"ed079542-3e38-420c-aa61-b8541b90f55e\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"a49a8f67-9b14-4cf2-a962-fdeffce02819\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:9:\\\"broadcast\\\";}}\",\"batchId\":null},\"createdAt\":1783711875,\"delay\":null}', 0, NULL, 1783711875, 1783711875),
(17, 'default', '{\"uuid\":\"55a485c4-8ad6-4dfc-bcdb-1d6067efaaa8\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"fb84eab3-7b2c-4a09-8cc1-65146c139452\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1783711927,\"delay\":null}', 0, NULL, 1783711927, 1783711927),
(18, 'default', '{\"uuid\":\"81b5f6ed-320b-47db-a4a9-46b9a9285a07\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"fb84eab3-7b2c-4a09-8cc1-65146c139452\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:9:\\\"broadcast\\\";}}\",\"batchId\":null},\"createdAt\":1783711927,\"delay\":null}', 0, NULL, 1783711927, 1783711927),
(19, 'default', '{\"uuid\":\"5a22165a-cc18-4d74-987b-98ec4a285975\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"176ecc0d-c07b-4bb4-8a04-1d45c32a9523\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1783712335,\"delay\":null}', 0, NULL, 1783712335, 1783712335),
(20, 'default', '{\"uuid\":\"f15a60af-373f-439e-9d7a-160dc1f741e6\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"176ecc0d-c07b-4bb4-8a04-1d45c32a9523\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:9:\\\"broadcast\\\";}}\",\"batchId\":null},\"createdAt\":1783712336,\"delay\":null}', 0, NULL, 1783712336, 1783712336),
(21, 'default', '{\"uuid\":\"3c1dc46c-d706-42ba-8a2a-729b18e2de6b\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"6e36d54c-76e2-4c88-9b0b-4d92419cfb7e\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1783713394,\"delay\":null}', 0, NULL, 1783713394, 1783713394),
(22, 'default', '{\"uuid\":\"c4a844a5-92fd-4e33-8eb3-8d448233f2ae\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"6e36d54c-76e2-4c88-9b0b-4d92419cfb7e\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:9:\\\"broadcast\\\";}}\",\"batchId\":null},\"createdAt\":1783713394,\"delay\":null}', 0, NULL, 1783713394, 1783713394),
(23, 'default', '{\"uuid\":\"6e8ad27e-b06d-4631-b32f-458fe5266359\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"37711a04-2554-40c4-9522-ee3fdbf605a9\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1783713998,\"delay\":null}', 0, NULL, 1783713998, 1783713998),
(24, 'default', '{\"uuid\":\"6f2238e7-2829-4b23-9751-5ac914284700\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"37711a04-2554-40c4-9522-ee3fdbf605a9\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:9:\\\"broadcast\\\";}}\",\"batchId\":null},\"createdAt\":1783713998,\"delay\":null}', 0, NULL, 1783713998, 1783713998),
(25, 'default', '{\"uuid\":\"916f6b92-e291-49f4-b182-ad34fa0d8ced\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"682d5cc1-a824-4a0d-9faa-418df2c73a2d\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1783714294,\"delay\":null}', 0, NULL, 1783714294, 1783714294),
(26, 'default', '{\"uuid\":\"149ea326-161a-4bb8-aa3c-6d25f50c3c54\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"682d5cc1-a824-4a0d-9faa-418df2c73a2d\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:9:\\\"broadcast\\\";}}\",\"batchId\":null},\"createdAt\":1783714294,\"delay\":null}', 0, NULL, 1783714294, 1783714294),
(27, 'default', '{\"uuid\":\"ff301e2c-e5f5-4b9c-93dc-8576bba738c6\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"05295fcf-7b24-46e2-92ca-6d80cecffda7\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1783761028,\"delay\":null}', 0, NULL, 1783761028, 1783761028),
(28, 'default', '{\"uuid\":\"00c8f978-4d24-4f3f-877a-c083b8d93b98\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"05295fcf-7b24-46e2-92ca-6d80cecffda7\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:9:\\\"broadcast\\\";}}\",\"batchId\":null},\"createdAt\":1783761028,\"delay\":null}', 0, NULL, 1783761028, 1783761028),
(29, 'default', '{\"uuid\":\"a707944d-845f-4fed-bb2f-bb407646defc\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"1b1fd789-079d-467b-81e7-060fe5e48e0c\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1783761373,\"delay\":null}', 0, NULL, 1783761373, 1783761373),
(30, 'default', '{\"uuid\":\"a1fb5e2f-51cf-47d1-9ed0-3354021ffb15\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"1b1fd789-079d-467b-81e7-060fe5e48e0c\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:9:\\\"broadcast\\\";}}\",\"batchId\":null},\"createdAt\":1783761373,\"delay\":null}', 0, NULL, 1783761373, 1783761373),
(31, 'default', '{\"uuid\":\"d718c043-62cc-41f4-939f-80b3f77a2e76\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"b0adef29-c412-4763-85f5-e642e5bef7f7\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1783762772,\"delay\":null}', 0, NULL, 1783762772, 1783762772),
(32, 'default', '{\"uuid\":\"bd7142b2-fbc0-4da8-85fa-6edece134467\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"b0adef29-c412-4763-85f5-e642e5bef7f7\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:9:\\\"broadcast\\\";}}\",\"batchId\":null},\"createdAt\":1783762772,\"delay\":null}', 0, NULL, 1783762772, 1783762772),
(33, 'default', '{\"uuid\":\"85135ba6-51fe-43fc-abeb-9b1cd36f299e\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"958a520b-7a67-4957-9281-8b2e6febefac\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1783763341,\"delay\":null}', 0, NULL, 1783763341, 1783763341),
(34, 'default', '{\"uuid\":\"b42b5fc7-19bf-41cb-bd35-1b6e37dac672\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"958a520b-7a67-4957-9281-8b2e6febefac\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:9:\\\"broadcast\\\";}}\",\"batchId\":null},\"createdAt\":1783763341,\"delay\":null}', 0, NULL, 1783763341, 1783763341),
(35, 'default', '{\"uuid\":\"209f8481-b4cf-460d-8115-4d9ed7740648\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"39c39eff-7ab2-49f8-93a3-faf9ffff3dc7\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1783763673,\"delay\":null}', 0, NULL, 1783763673, 1783763673),
(36, 'default', '{\"uuid\":\"2c6d5436-967a-44a7-84f7-096115119ab6\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"39c39eff-7ab2-49f8-93a3-faf9ffff3dc7\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:9:\\\"broadcast\\\";}}\",\"batchId\":null},\"createdAt\":1783763673,\"delay\":null}', 0, NULL, 1783763673, 1783763673),
(37, 'default', '{\"uuid\":\"7b69980a-d00d-425d-b2aa-520b5910345d\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"fbfffdc9-616f-4ee8-9aaf-9beb874311c1\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1783764964,\"delay\":null}', 0, NULL, 1783764964, 1783764964),
(38, 'default', '{\"uuid\":\"d6416b43-d13f-448a-a912-b13147061292\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"fbfffdc9-616f-4ee8-9aaf-9beb874311c1\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:9:\\\"broadcast\\\";}}\",\"batchId\":null},\"createdAt\":1783764964,\"delay\":null}', 0, NULL, 1783764964, 1783764964),
(39, 'default', '{\"uuid\":\"398c6b5a-bdad-4eca-8284-f4e67c308a83\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"8ea0e99b-d769-41b7-ada3-3c9e4180211f\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1783765503,\"delay\":null}', 0, NULL, 1783765503, 1783765503),
(40, 'default', '{\"uuid\":\"8953c205-131e-461a-83c0-7c052a9a4036\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"8ea0e99b-d769-41b7-ada3-3c9e4180211f\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:9:\\\"broadcast\\\";}}\",\"batchId\":null},\"createdAt\":1783765503,\"delay\":null}', 0, NULL, 1783765503, 1783765503),
(41, 'default', '{\"uuid\":\"173c1db8-5eb3-4b32-b20c-cdb12e965d3d\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:6;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"98d804cc-0401-41f5-a723-fff518e5d9eb\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1783765726,\"delay\":null}', 0, NULL, 1783765726, 1783765726);
INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(42, 'default', '{\"uuid\":\"f8975332-97c0-43b9-8ad7-a1293f054395\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:6;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"98d804cc-0401-41f5-a723-fff518e5d9eb\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:9:\\\"broadcast\\\";}}\",\"batchId\":null},\"createdAt\":1783765726,\"delay\":null}', 0, NULL, 1783765726, 1783765726),
(43, 'default', '{\"uuid\":\"dd40b32d-8f93-485c-b85a-c6a52aa7255f\",\"displayName\":\"App\\\\Jobs\\\\ProcessIncomingWhatsAppMessage\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcessIncomingWhatsAppMessage\",\"command\":\"O:39:\\\"App\\\\Jobs\\\\ProcessIncomingWhatsAppMessage\\\":2:{s:9:\\\"waMessage\\\";a:6:{s:4:\\\"from\\\";s:12:\\\"916295726764\\\";s:12:\\\"from_user_id\\\";s:19:\\\"IN.1041606184998663\\\";s:2:\\\"id\\\";s:66:\\\"wamid.HBgMOTE2Mjk1NzI2NzY0FQIAEhgWM0VCMDk3QjZCMkJCOEJDQTBDNkZEMAA=\\\";s:9:\\\"timestamp\\\";s:10:\\\"1783767069\\\";s:4:\\\"text\\\";a:1:{s:4:\\\"body\\\";s:30:\\\"ek bar catelog phir se bhejoge\\\";}s:4:\\\"type\\\";s:4:\\\"text\\\";}s:11:\\\"contactName\\\";s:4:\\\"amit\\\";}\",\"batchId\":null},\"createdAt\":1783767072,\"delay\":null}', 0, NULL, 1783767072, 1783767072),
(44, 'default', '{\"uuid\":\"7bd4fac3-2bd2-4839-b1cc-218890a46a04\",\"displayName\":\"App\\\\Jobs\\\\ProcessIncomingWhatsAppMessage\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcessIncomingWhatsAppMessage\",\"command\":\"O:39:\\\"App\\\\Jobs\\\\ProcessIncomingWhatsAppMessage\\\":2:{s:9:\\\"waMessage\\\";a:6:{s:4:\\\"from\\\";s:12:\\\"916295726764\\\";s:12:\\\"from_user_id\\\";s:19:\\\"IN.1041606184998663\\\";s:2:\\\"id\\\";s:66:\\\"wamid.HBgMOTE2Mjk1NzI2NzY0FQIAEhgWM0VCMDhFN0E0OEUxRDExNzVBQUEzQwA=\\\";s:9:\\\"timestamp\\\";s:10:\\\"1783767165\\\";s:4:\\\"text\\\";a:1:{s:4:\\\"body\\\";s:9:\\\"kya hua ?\\\";}s:4:\\\"type\\\";s:4:\\\"text\\\";}s:11:\\\"contactName\\\";s:4:\\\"amit\\\";}\",\"batchId\":null},\"createdAt\":1783767167,\"delay\":null}', 0, NULL, 1783767167, 1783767167),
(45, 'default', '{\"uuid\":\"2c036611-7699-4442-b29a-eb2be58c7d3d\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:6;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"d2febb5f-921c-4c74-8374-2354456d55e6\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1783767234,\"delay\":null}', 0, NULL, 1783767234, 1783767234),
(46, 'default', '{\"uuid\":\"065682fd-9baa-42ae-9a74-2ba94b4be91f\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:6;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"d2febb5f-921c-4c74-8374-2354456d55e6\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:9:\\\"broadcast\\\";}}\",\"batchId\":null},\"createdAt\":1783767234,\"delay\":null}', 0, NULL, 1783767234, 1783767234),
(47, 'default', '{\"uuid\":\"cac75179-cbc0-4464-b5c7-3299f64ade88\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:6;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"f2dbf30b-b047-45f4-b636-834f3ebb1580\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1783767376,\"delay\":null}', 0, NULL, 1783767376, 1783767376),
(48, 'default', '{\"uuid\":\"071afc5e-56cb-41cc-a798-9a6427013b3f\",\"displayName\":\"App\\\\Notifications\\\\ChatConversationNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Admin\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:46:\\\"App\\\\Notifications\\\\ChatConversationNotification\\\":3:{s:12:\\\"conversation\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:27:\\\"App\\\\Models\\\\ChatConversation\\\";s:2:\\\"id\\\";i:6;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"escalated\\\";s:2:\\\"id\\\";s:36:\\\"f2dbf30b-b047-45f4-b636-834f3ebb1580\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:9:\\\"broadcast\\\";}}\",\"batchId\":null},\"createdAt\":1783767376,\"delay\":null}', 0, NULL, 1783767376, 1783767376);

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kb_articles`
--

CREATE TABLE `kb_articles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('faq','policy','pricing','product','service') NOT NULL DEFAULT 'faq',
  `title` varchar(255) NOT NULL,
  `keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`keywords`)),
  `search_tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `question` text DEFAULT NULL,
  `answer` longtext NOT NULL,
  `detailed_description` text DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `price_override` decimal(12,2) DEFAULT NULL,
  `status` enum('draft','pending_approval','published','archived') NOT NULL DEFAULT 'draft',
  `version` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `ai_ready` tinyint(1) NOT NULL DEFAULT 1,
  `embedding` longtext DEFAULT NULL,
  `embedding_hash` varchar(32) DEFAULT NULL,
  `embedding_updated_at` timestamp NULL DEFAULT NULL,
  `priority` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kb_articles`
--

INSERT INTO `kb_articles` (`id`, `category_id`, `type`, `title`, `keywords`, `search_tags`, `question`, `answer`, `detailed_description`, `product_id`, `price_override`, `status`, `version`, `ai_ready`, `embedding`, `embedding_hash`, `embedding_updated_at`, `priority`, `created_by`, `approved_by`, `approved_at`, `published_at`, `created_at`, `updated_at`) VALUES
(20, 4, 'faq', 'Product Catalog', NULL, NULL, 'Do you have a product catalog? Can you send your catalog? Can I get your brochure? Product catalog hai? Catalog bhejo. Brochure bhejo. Product list bhejo. Catelog hai?', 'Yes, we have a complete product catalog. Please share your requirements, and we will send you our latest product catalog in PDF format.', NULL, NULL, NULL, 'published', 1, 1, '[-0.02386474609375,-0.0240936279296875,-0.033477783203125,0.0166168212890625,0.0035247802734375,-0.0039043426513671875,-0.022857666015625,0.02056884765625,0.001689910888671875,0.0213470458984375,0.0203094482421875,-0.07623291015625,0.013916015625,-0.02642822265625,0.03631591796875,0.00027179718017578125,0.0169677734375,0.0282440185546875,-0.00624847412109375,0.011932373046875,0.048370361328125,0.01214599609375,-0.01242828369140625,-0.00653839111328125,-0.00714874267578125,0.0140533447265625,-0.006866455078125,-0.01218414306640625,0.051483154296875,0.00881195068359375,-0.00664520263671875,-0.02288818359375,-0.034637451171875,0.061187744140625,-0.047149658203125,0.0679931640625,0.0284881591796875,-0.02056884765625,0.016754150390625,0.001316070556640625,0.0002332925796508789,-0.03155517578125,0.047149658203125,-0.0251312255859375,0.01450347900390625,0.025604248046875,0.005695343017578125,-0.0169219970703125,0.01467132568359375,0.0031299591064453125,-0.028961181640625,-0.0118408203125,0.0233306884765625,0.046661376953125,-0.051177978515625,-0.044036865234375,0.01617431640625,-0.00859832763671875,-0.03900146484375,-0.042449951171875,-0.02545166015625,-0.0273590087890625,0.01357269287109375,0.00695037841796875,-0.007904052734375,-0.01140594482421875,-0.054290771484375,0.034515380859375,-0.03375244140625,-0.01568603515625,0.021270751953125,0.07086181640625,0.004619598388671875,0.0146636962890625,-0.010162353515625,0.0017156600952148438,-0.043487548828125,0.028411865234375,0.048095703125,-0.03607177734375,0.0007343292236328125,0.0238494873046875,-0.00536346435546875,-0.00592803955078125,-0.0252532958984375,-0.008331298828125,0.00131988525390625,0.0465087890625,-0.01678466796875,-0.0290985107421875,0.034576416015625,0.01012420654296875,-0.018829345703125,0.033477783203125,0.019744873046875,-0.0281982421875,0.001556396484375,0.021697998046875,0.005809783935546875,0.024078369140625,0.048828125,0.0194244384765625,-0.0164642333984375,0.00872802734375,0.0479736328125,0.013336181640625,0.024932861328125,-0.0147552490234375,-0.00605010986328125,-0.023956298828125,-0.0921630859375,-0.044647216796875,0.0214385986328125,0.06744384765625,0.050506591796875,0.0341796875,0.0904541015625,-0.030029296875,-0.00423431396484375,-0.0303192138671875,-0.06005859375,0.0172576904296875,0.0241546630859375,-0.01690673828125,0.017913818359375,0.0304412841796875,-0.0099945068359375,-0.0164794921875,-0.0115203857421875,-0.043670654296875,0.00826263427734375,-0.019683837890625,0.0202178955078125,0.00107574462890625,-0.00464630126953125,-0.00315093994140625,0.00689697265625,-0.0089111328125,-0.03302001953125,0.019805908203125,0.011199951171875,-0.0131988525390625,-0.01000213623046875,0.0019378662109375,-0.0262451171875,-0.0121917724609375,0.010498046875,-0.0280303955078125,0.0015411376953125,-0.0241241455078125,-0.01568603515625,-0.0189361572265625,-0.0119781494140625,-0.0306854248046875,0.01422882080078125,-0.00727081298828125,-0.01406097412109375,-0.0011949539184570312,-0.03277587890625,0.0014715194702148438,0.01160430908203125,-0.0355224609375,-0.04095458984375,-0.01300048828125,-0.0241546630859375,-0.05303955078125,0.0005731582641601562,0.0120391845703125,-0.0297393798828125,-0.0144500732421875,0.0015201568603515625,-0.0167083740234375,-7.76052474975586e-5,-0.0188446044921875,0.0419921875,0.02685546875,0.01358795166015625,-0.0228271484375,-0.003414154052734375,-0.0269012451171875,-0.02545166015625,0.046875,-0.0037841796875,-0.064208984375,-4.309415817260742e-5,-0.0259552001953125,0.0167694091796875,0.006763458251953125,-0.049713134765625,0.0081329345703125,-0.020172119140625,0.019683837890625,-0.033050537109375,-0.045867919921875,-0.0066680908203125,-0.0633544921875,0.00453948974609375,0.016265869140625,-0.021453857421875,-0.046417236328125,-0.0008053779602050781,0.02630615234375,0.0020618438720703125,0.05780029296875,-0.0290374755859375,-0.0369873046875,-0.0207366943359375,-0.0020465850830078125,-0.031494140625,-0.02008056640625,0.058837890625,-0.015838623046875,-0.0196380615234375,0.05780029296875,-0.00678253173828125,-0.011932373046875,-0.055206298828125,0.0361328125,0.0146484375,0.00496673583984375,-0.0138397216796875,-0.0066986083984375,0.019683837890625,-0.002948760986328125,-0.007965087890625,0.054351806640625,-0.0103759765625,-0.019073486328125,0.060882568359375,-0.07342529296875,0.0423583984375,-0.00542449951171875,0.052032470703125,-0.00850677490234375,0.019439697265625,-0.034820556640625,0.022186279296875,0.03466796875,0.001552581787109375,-0.07611083984375,-0.00748443603515625,-0.01006317138671875,-0.000919342041015625,0.060272216796875,0.0207061767578125,-0.0159149169921875,-0.02239990234375,-0.0892333984375,0.038970947265625,0.010284423828125,0.066162109375,0.01471710205078125,0.0146484375,-0.0762939453125,0.0229339599609375,-0.03253173828125,-0.044036865234375,-0.00310516357421875,0.0265350341796875,-0.0105438232421875,-0.0280609130859375,0.0074462890625,0.01561737060546875,0.010833740234375,-0.012298583984375,0.039276123046875,0.01357269287109375,0.04486083984375,0.004459381103515625,0.050323486328125,0.006572723388671875,0.0058746337890625,0.0225372314453125,0.0115509033203125,-0.051727294921875,0.0390625,-0.00908660888671875,-0.03021240234375,0.02923583984375,-0.0002416372299194336,-0.013946533203125,0.03594970703125,-0.050506591796875,-0.014312744140625,0.00905609130859375,0.027984619140625,0.0533447265625,0.0234222412109375,-0.043060302734375,0.0159912109375,0.040069580078125,-0.04254150390625,-0.00817108154296875,-0.024078369140625,-0.0194091796875,-0.01361846923828125,0.024932861328125,0.0024433135986328125,0.015899658203125,-0.00925445556640625,-0.0007276535034179688,-0.0122833251953125,0.0400390625,0.0504150390625,-0.05322265625,0.0160675048828125,0.03143310546875,-0.03338623046875,-0.0022106170654296875,-0.033477783203125,0.0294189453125,0.05780029296875,0.042083740234375,-0.0283966064453125,-0.024322509765625,-0.04034423828125,-0.0155029296875,-0.0472412109375,0.03656005859375,0.007556915283203125,0.0286712646484375,0.0188446044921875,0.0233001708984375,-0.01415252685546875,-0.039581298828125,0.0008988380432128906,-0.0252227783203125,0.03875732421875,0.0181884765625,-0.021575927734375,0.01128387451171875,0.01381683349609375,0.03961181640625,-0.0013036727905273438,-8.13603401184082e-5,0.02728271484375,0.025115966796875,0.03009033203125,-0.044464111328125,-0.0203857421875,-0.0073699951171875,-0.03717041015625,-0.0133514404296875,0.039306640625,0.0377197265625,-0.029998779296875,0.004276275634765625,0.08343505859375,0.0157012939453125,-0.0047454833984375,0.007808685302734375,0.041015625,0.0019063949584960938,0.043243408203125,0.0179595947265625,-0.0185546875,0.024383544921875,-0.0012493133544921875,-0.0293731689453125,0.0059051513671875,0.0112457275390625,0.001743316650390625,0.033447265625,0.034637451171875,-0.035491943359375,0.0533447265625,-0.058013916015625,-0.044647216796875,0.0382080078125,0.00798797607421875,0.061676025390625,-0.0286865234375,0.01079559326171875,-0.0355224609375,-0.02630615234375,-0.0155487060546875,-0.020965576171875,0.0205841064453125,0.03570556640625,-0.009429931640625,0.042999267578125,0.01311492919921875,-0.01593017578125,0.01561737060546875,0.036468505859375,-0.01136016845703125,-0.01214599609375,0.044097900390625,-0.0198516845703125,-0.0190277099609375,0.008392333984375,-0.024505615234375,0.0204620361328125,-0.0292816162109375,0.054351806640625,0.00457000732421875,0.021209716796875,0.022430419921875,-0.00617218017578125,-0.0458984375,0.01433563232421875,-0.022735595703125,0.025360107421875,0.021942138671875,-0.00803375244140625,-0.0007600784301757812,0.015625,0.0228118896484375,0.03497314453125,-0.0190277099609375,0.04290771484375,-0.044189453125,0.015106201171875,0.0119781494140625,0.04376220703125,-0.01503753662109375,-0.072509765625,0.03466796875,0.01284027099609375,0.004364013671875,-0.017181396484375,-0.0013990402221679688,-0.0211944580078125,0.0015382766723632812,-0.04754638671875,0.00024580955505371094,0.03826904296875,-0.0184173583984375,-0.01019287109375,-0.0750732421875,-0.01129150390625,-0.0089263916015625,-0.0132904052734375,0.019195556640625,-0.0218353271484375,0.00998687744140625,0.006969451904296875,0.012939453125,-0.01166534423828125,-0.047088623046875,0.034454345703125,0.041412353515625,-0.0233001708984375,0.0202178955078125,-0.042266845703125,-0.029388427734375,0.014434814453125,0.0260467529296875,-0.0167999267578125,0.005786895751953125,-0.033905029296875,-0.048858642578125,-0.0181884765625,0.018280029296875,-0.0693359375,0.054931640625,0.0009403228759765625,-0.0300445556640625,0.0186767578125,-0.0389404296875,0.0248565673828125,0.0312042236328125,0.0107574462890625,0.0163726806640625,-0.02398681640625,-0.0186309814453125,-0.0261383056640625,-0.024932861328125,0.0208587646484375,-0.037841796875,0.00014078617095947266,-0.003696441650390625,-0.041473388671875,-0.05120849609375,-0.01052093505859375,0.033355712890625,0.00679779052734375,-0.01091766357421875,-0.00496673583984375,-0.0009489059448242188,0.076904296875,-0.0089111328125,-0.06988525390625,0.0025310516357421875,-0.025390625,0.0460205078125,-0.01206207275390625,0.0016460418701171875,0.0244598388671875,-0.0024051666259765625,-0.00811004638671875,0.0136566162109375,0.032073974609375,-0.00933074951171875,-0.0032749176025390625,0.003215789794921875,-0.00800323486328125,-0.0289459228515625,0.0009737014770507812,0.004146575927734375,-0.047454833984375,0.019927978515625,-0.007259368896484375,-0.02032470703125,0.0413818359375,-0.01354217529296875,-0.007598876953125,-0.049713134765625,0.023956298828125,0.0135650634765625,0.0035915374755859375,0.062469482421875,-0.024200439453125,0.02166748046875,0.040863037109375,0.00623321533203125,-0.00311279296875,0.0177001953125,-0.060150146484375,-0.03521728515625,-0.0223388671875,-0.007259368896484375,-0.005115509033203125,0.00727081298828125,-0.045623779296875,0.00885009765625,0.00951385498046875,-0.043365478515625,-0.0210418701171875,-0.0103607177734375,0.038909912109375,-0.0166168212890625,-0.0318603515625,0.0338134765625,0.033416748046875,-0.01678466796875,0.0341796875,-0.0172271728515625,-0.043914794921875,-0.031097412109375,-0.00545501708984375,0.01065826416015625,-0.0133514404296875,0.00392913818359375,0.004383087158203125,-0.01422119140625,0.00846099853515625,-0.0051422119140625,-0.0200958251953125,0.0025634765625,0.02886962890625,0.03228759765625,-0.007659912109375,-0.0086822509765625,0.02886962890625,-0.00887298583984375,-0.01503753662109375,-0.01995849609375,0.0166778564453125,-0.028900146484375,0.0023403167724609375,0.00388336181640625,-0.0004563331604003906,0.004657745361328125,0.034881591796875,0.045318603515625,-0.00888824462890625,0.005855560302734375,0.01522064208984375,-0.0180206298828125,0.009796142578125,-0.014129638671875,-0.041656494140625,-0.0290985107421875,0.041778564453125,0.0042877197265625,-0.020782470703125,-0.022674560546875,0.01384735107421875,0.00728607177734375,0.00963592529296875,0.0024356842041015625,-0.01239013671875,0.00588226318359375,-0.0184173583984375,-0.0015649795532226562,0.09503173828125,-0.000946044921875,0.011932373046875,0.01436614990234375,-0.043243408203125,-0.039306640625,0.0030117034912109375,-0.006664276123046875,-0.02166748046875,-0.027099609375,0.00213623046875,-0.002239227294921875,-0.0275115966796875,-0.00991058349609375,0.01021575927734375,0.001598358154296875,-0.01064300537109375,0.01471710205078125,-0.0491943359375,0.00623321533203125,0.01398468017578125,0.007568359375,-0.02520751953125,-0.0273284912109375,-0.0194549560546875,0.0191497802734375,-0.0189361572265625,-0.019683837890625,0.0029811859130859375,0.035247802734375,0.0126800537109375,0.0210418701171875,0.007389068603515625,0.02099609375,0.00647735595703125,0.03466796875,0.0224761962890625,-0.0051727294921875,0.0129547119140625,0.0181884765625,0.033203125,-0.01529693603515625,-0.05682373046875,0.0182647705078125,0.0196380615234375,-0.02490234375,0.025390625,-0.004070281982421875,0.0189361572265625,0.017913818359375,-0.0002281665802001953,-0.0066680908203125,0.0139007568359375,-0.01019287109375,0.0002853870391845703,0.01242828369140625,0.033905029296875,-0.03387451171875,0.031402587890625,-0.008087158203125,0.0199432373046875,-0.0009860992431640625,-0.0095062255859375,0.01021575927734375,0.032501220703125,-0.0296630859375,-0.007320404052734375,-0.0266265869140625,0.03485107421875,0.030792236328125,-0.0271759033203125,0.0213470458984375,-0.05950927734375,0.0294189453125,-0.0162811279296875,-0.0013294219970703125,0.0638427734375,-0.02142333984375,-0.0181884765625,0.040679931640625,0.00286102294921875,0.045440673828125,-0.00605010986328125,0.01560211181640625,-0.07647705078125,0.0189056396484375,0.0028133392333984375,-0.0255279541015625,-0.036285400390625,-0.0028839111328125,0.004558563232421875,-0.0027256011962890625,-0.039581298828125,-0.01531982421875,-0.01482391357421875,0.03277587890625,-0.027984619140625,0.005870819091796875,-0.0213165283203125,-0.0173187255859375,0.02191162109375,0.0308837890625,-0.0020999908447265625,0.00421142578125,-0.0085906982421875,0.0281219482421875,-0.02099609375,-0.016815185546875,1.150369644165039e-5,0.002559661865234375,0.003215789794921875,-0.003971099853515625,-0.01019287109375,-0.10009765625,0.0256805419921875,-0.0195770263671875,-0.002399444580078125,0.0067596435546875,0.01349639892578125,-0.03204345703125,-0.06402587890625,-0.0281982421875,0.0170440673828125,0.03753662109375,-0.0005025863647460938,0.034271240234375,-0.003284454345703125,0.020843505859375,0.0013551712036132812,0.01358795166015625,-0.019561767578125,0.0140380859375,0.01216888427734375,0.01548004150390625,0.001842498779296875,0.0070037841796875,-0.01422119140625,-0.00518798828125,-0.0160369873046875,0.0236663818359375,-0.008331298828125,0.00693511962890625,-0.0282135009765625,-0.02789306640625,0.021484375,0.02606201171875,-0.005218505859375,0.01512908935546875,-0.00962066650390625,-0.0185394287109375,-0.0204010009765625,-0.001861572265625,0.007598876953125,0.0087432861328125,-0.01511383056640625,-0.0189971923828125,0.002132415771484375,0.0667724609375,-0.0195465087890625,-0.00848388671875,-0.027496337890625,0.029937744140625,-0.0199127197265625,-0.0016031265258789062,0.0033512115478515625,0.0066680908203125,-0.05255126953125,-0.006061553955078125,0.0545654296875,-0.045318603515625,0.028167724609375,-0.00444793701171875,0.00742340087890625,0.004138946533203125,0.01403045654296875,0.044921875,-0.0134429931640625,-0.02703857421875,-0.052032470703125,-0.0380859375,0.00012445449829101562,0.035247802734375,0.036285400390625,-0.0126495361328125,0.0191650390625,-0.0265655517578125,-0.006488800048828125,-0.032684326171875,0.037017822265625,-0.0128631591796875,-0.01332855224609375,0.01427459716796875,0.006160736083984375,-0.0005941390991210938,0.01186370849609375,-0.0226593017578125,0.044708251953125,-0.0193634033203125,0.0217742919921875,-0.0171356201171875,-0.02789306640625,-0.0185699462890625,-0.0072479248046875,-0.0257720947265625,0.0027713775634765625,-0.01221466064453125,-0.0144500732421875,-0.003665924072265625,-0.006221771240234375,-0.0106048583984375,-3.534555435180664e-5,-0.01371002197265625,0.003772735595703125,0.0211334228515625,-0.0269012451171875,0.046295166015625,-0.0259552001953125,0.01739501953125,0.042877197265625,0.0158233642578125,-0.00274658203125,0.042877197265625,-0.01381683349609375,0.024383544921875,-0.0260162353515625,-0.01557159423828125,0.022674560546875,0.01140594482421875,-0.024566650390625,0.002796173095703125,0.007518768310546875,-0.0124969482421875,-0.030303955078125,0.0058746337890625,0.01398468017578125,0.004405975341796875,-0.02386474609375,0.0176239013671875,0.01081085205078125,0.049774169921875,0.0251617431640625,-0.053955078125,0.0235443115234375,-0.043487548828125,0.0032863616943359375,-0.01018524169921875,-0.027679443359375,0.0003159046173095703,0.004161834716796875,0.007122039794921875,0.007404327392578125,0.001964569091796875,0.006679534912109375,-0.019775390625,0.007183074951171875,0.023590087890625,0.01377105712890625,-0.023529052734375,0.025482177734375,0.0267181396484375,0.0031795501708984375,0.007625579833984375,-0.003314971923828125,-0.004245758056640625,0.014739990234375,-0.0049896240234375,-0.0172271728515625,-0.06329345703125,-0.05621337890625,0.0175628662109375,-0.001819610595703125,-0.0272674560546875,-0.0008921623229980469,-0.01107025146484375,-0.0236663818359375,0.01142120361328125,-0.0252685546875,-0.049041748046875,0.0108184814453125,-0.0411376953125,0.0582275390625,0.019500732421875,0.036224365234375,-0.0061187744140625,-0.002872467041015625,-0.003345489501953125,-0.00946044921875,0.01149749755859375,-0.0149688720703125,-0.008514404296875,0.02337646484375,0.01276397705078125,0.0010671615600585938,-0.02630615234375,0.0244293212890625,-0.057830810546875,0.032501220703125,0.03717041015625,0.00528717041015625,-0.00762176513671875,0.0225982666015625,0.01959228515625,0.0172119140625,0.00811004638671875,-0.0005269050598144531,-0.036712646484375,0.04376220703125,-0.01995849609375,-0.0150146484375,0.00555419921875,-0.033599853515625,-0.00031304359436035156,0.03302001953125,-0.0006709098815917969,0.00572967529296875,-0.01776123046875,-0.0006656646728515625,0.02667236328125,0.0177154541015625,0.0105438232421875,0.0189971923828125,-0.00376129150390625,0.00937652587890625,0.0013189315795898438,-0.028594970703125,-0.01363372802734375,-0.0043792724609375,0.018890380859375,0.02960205078125,0.0182952880859375,-0.0248260498046875,0.019683837890625,0.046173095703125,-0.0189056396484375,-0.0035037994384765625,-0.01079559326171875,0.017578125,-0.0166015625,-0.0161895751953125,-0.008270263671875,-0.014801025390625,-0.0211944580078125,0.023284912109375,-0.00926971435546875,0.01922607421875,0.05828857421875,0.03936767578125,0.03790283203125,0.029937744140625,-0.0236358642578125,-0.0051116943359375,0.009124755859375,-0.0268096923828125,-0.010284423828125,0.043365478515625,0.01220703125,-0.0264434814453125,-0.03399658203125,0.052764892578125,-0.057464599609375,-0.004383087158203125,0.01104736328125,0.0146484375,-0.0296173095703125,0.042083740234375,-0.011077880859375,0.040557861328125,-0.0408935546875,0.0018301010131835938,-0.0094146728515625,0.00592803955078125,-0.047882080078125,0.0268096923828125,-0.01055145263671875,0.01494598388671875,0.00754547119140625,-0.015411376953125,-0.015594482421875,0.014617919921875,0.0122528076171875,0.034881591796875,0.0175323486328125,0.05120849609375,0.006336212158203125,0.0194549560546875,0.0080413818359375,-0.0214996337890625,0.0208282470703125,0.006328582763671875,0.0214691162109375,-0.022125244140625,-0.01352691650390625,0.02606201171875,-0.02166748046875,0.0280609130859375,0.0003249645233154297,0.0064239501953125,0.0283355712890625,-0.0005593299865722656,0.02386474609375,-0.00019979476928710938,0.03399658203125,-0.0109405517578125,0.0178070068359375,-0.0282135009765625,0.01947021484375,0.0185699462890625,0.00955963134765625,-0.0224761962890625,0.0005698204040527344,0.00495147705078125,-0.01192474365234375,0.01108551025390625,-0.00150299072265625,0.005645751953125,-0.03912353515625,-0.0119476318359375,-0.032012939453125,0.0165863037109375,0.00101470947265625,0.06024169921875,0.0192413330078125,-0.0226287841796875,-0.02178955078125,-0.0025081634521484375,-0.014556884765625,-0.0232086181640625,-0.004932403564453125,0.038818359375,0.00313568115234375,0.0031452178955078125,-0.0323486328125,-0.00848388671875,-0.055084228515625,0.008392333984375,-0.006946563720703125,-0.004669189453125,0.0241241455078125,-0.0284576416015625,-0.0173797607421875,0.02618408203125,0.00501251220703125,0.0484619140625,0.0196533203125,0.012298583984375,-0.0028209686279296875,-0.03155517578125,-0.004810333251953125,-0.0105438232421875,-0.00330352783203125,-0.019775390625,-0.015106201171875,0.005512237548828125,0.022796630859375,0.01039886474609375,-0.026947021484375,-0.0247955322265625,0.01398468017578125,-0.0100250244140625,-0.0034732818603515625,-0.03009033203125,0.0017404556274414062,0.038909912109375,-0.00978851318359375,0.004108428955078125,0.0261993408203125,-0.0078582763671875,0.0108489990234375,-0.01136016845703125,0.02447509765625,-0.0110931396484375,-0.0237274169921875,-0.00785064697265625,0.026519775390625,-0.0217437744140625,0.043212890625,0.0216217041015625,0.0036182403564453125,-0.01010894775390625,-0.0207977294921875,-0.00865936279296875,-0.005962371826171875,0.00321197509765625,0.0248870849609375,0.00867462158203125,0.00905609130859375,0.0296173095703125,-0.0239410400390625,0.045928955078125,-0.028717041015625,-0.014007568359375,-0.0026798248291015625,0.0178680419921875,-0.001811981201171875,0.00231170654296875,0.03375244140625,-0.0176239013671875,0.0238037109375,-0.0068206787109375,0.0013666152954101562,0.0011425018310546875,0.04254150390625,0.006664276123046875,-0.015869140625,-0.0201263427734375,0.004680633544921875,0.00727081298828125,-0.0229644775390625,0.0170440673828125,0.034576416015625,0.01442718505859375,-0.004241943359375,-0.036468505859375,0.018585205078125,-0.0161285400390625,0.0170135498046875,0.0168609619140625,-0.021087646484375,-0.037841796875,0.008636474609375,0.0094451904296875,0.0145721435546875,-0.0048828125,0.0178375244140625,-0.01763916015625,0.0013370513916015625,-0.00856781005859375,-0.043060302734375,0.033477783203125,0.002803802490234375,-0.00739288330078125,0.016143798828125,-0.0010356903076171875,0.0189056396484375,-0.0296630859375,0.00439453125,-0.009552001953125,-0.0140838623046875,0.01123046875,0.0047454833984375,-0.0105133056640625,0.0240478515625,0.0010805130004882812,0.0017366409301757812,-0.0079498291015625,-0.0167999267578125,0.012298583984375,-0.028076171875,0.02386474609375,-0.00823974609375,-0.037750244140625,-0.032928466796875,0.0227508544921875,-0.0174407958984375,0.0280609130859375,0.0019121170043945312,-0.007228851318359375,-0.0018835067749023438,-0.0172271728515625,0.0026264190673828125,0.0014715194702148438,0.05755615234375,-0.004962921142578125,0.0131988525390625,-0.004535675048828125,-0.00836944580078125,-0.003696441650390625,0.020233154296875,-0.0009284019470214844,-0.032684326171875,-0.019805908203125,-0.01424407958984375,-0.033721923828125,0.0055694580078125,0.025909423828125,0.0011644363403320312,-0.022064208984375,-0.036773681640625,-0.0391845703125,-0.033935546875,-0.0701904296875,0.00954437255859375,0.00024580955505371094,0.01251220703125,0.0014553070068359375,-0.0186309814453125,-0.059051513671875,0.010833740234375,0.0017871856689453125,0.01108551025390625,0.027801513671875,0.04193115234375,0.041473388671875,0.0310211181640625,-0.020416259765625,-0.008270263671875,0.00266265869140625,-0.0200347900390625,-0.0266571044921875,0.0014619827270507812,0.00537872314453125,0.0091705322265625,0.00394439697265625,0.0061492919921875,0.0233917236328125,0.046478271484375,0.0016431808471679688,-0.03466796875,0.0184173583984375,0.003910064697265625,-0.03179931640625,-0.01922607421875,0.0017080307006835938,-0.03204345703125,-0.0228424072265625,0.0017299652099609375,-0.019287109375,0.043609619140625,0.00774383544921875,-0.0179290771484375,0.01366424560546875,-0.0011091232299804688,0.0117950439453125,0.01029205322265625,-0.01497650146484375,0.052886962890625,0.0032501220703125,-0.019927978515625,-0.01137542724609375,0.0252227783203125,0.00981903076171875,0.045135498046875,0.01296234130859375,-0.005405426025390625,0.00838470458984375,-0.01678466796875,0.046051025390625,0.0118560791015625,-0.003871917724609375,-0.007648468017578125,-0.02215576171875,-0.005153656005859375,-0.01511383056640625,0.01026153564453125,0.0020809173583984375,-0.00504302978515625,0.0065460205078125,0.0040283203125,0.00687408447265625,0.005237579345703125,0.0028228759765625,0.021728515625,0.0101470947265625,0.042449951171875,0.01029205322265625,-0.0209197998046875,0.00955963134765625,0.0117950439453125,0.02880859375,-0.01146697998046875,0.00926971435546875,0.01047515869140625,-0.02325439453125,-0.01276397705078125,0.038330078125,-0.0113677978515625,0.00424957275390625,-0.005157470703125,-0.00373077392578125,-0.0019054412841796875,-0.0374755859375,0.0201263427734375,-0.0038890838623046875,-0.00896453857421875,-0.00994110107421875,-0.01727294921875,0.01047515869140625,0.0161590576171875,0.01096343994140625,0.01422119140625,-0.0184173583984375,-0.003650665283203125,0.028076171875,-0.054443359375,0.0189361572265625,0.0003523826599121094,0.0011949539184570312,-0.00446319580078125,-0.0269775390625,0.002685546875,0.011810302734375,0.016571044921875,0.0161590576171875,0.039031982421875,0.0217742919921875,0.00995635986328125,-0.0005254745483398438,-0.04010009765625,0.007610321044921875,0.015533447265625,-0.0208740234375,-0.059906005859375,0.03125,-0.005802154541015625,0.004123687744140625,0.0264434814453125,0.006969451904296875,-0.05035400390625,-0.020294189453125,0.0030879974365234375,0.004154205322265625,0.0157928466796875,0.01068878173828125,0.002338409423828125,-0.0005331039428710938,0.0174407958984375,0.004802703857421875,0.011566162109375,-0.01494598388671875,-0.006000518798828125,-0.02447509765625,-0.00858306884765625,-0.0061187744140625,-0.0689697265625,-0.028533935546875,0.0186004638671875,-0.018585205078125,0.0214996337890625,0.0100860595703125,-0.0071258544921875,-0.0005354881286621094,-0.01605224609375,0.0252685546875,0.0011606216430664062,0.0099945068359375,0.007320404052734375,-0.01108551025390625,-0.007843017578125,0.0232086181640625,-0.020599365234375,-0.0276947021484375,-0.0070648193359375,-0.0007443428039550781,-0.02862548828125,-0.006885528564453125,-0.00749969482421875,0.0104217529296875,-0.00010985136032104492,-0.0289154052734375,0.00894927978515625,-0.0130767822265625,0.017364501953125,0.00803375244140625,-0.026947021484375,-0.0030364990234375,0.01445770263671875,0.048583984375,0.0008058547973632812,0.01221466064453125,0.01236724853515625,-0.01120758056640625,-0.0011577606201171875,0.0106353759765625,-0.0239105224609375,0.01483154296875,0.005748748779296875,0.00713348388671875,-0.005535125732421875,-0.0005450248718261719,-0.0014591217041015625,-0.019378662109375,0.04058837890625,-0.0577392578125,-0.037200927734375,0.015655517578125,-0.0111083984375,-0.0010156631469726562,0.0125579833984375,0.033050537109375,-0.0032634735107421875,0.01947021484375,0.019134521484375,0.004596710205078125,0.014007568359375,-0.00616455078125,-0.0015411376953125,0.02056884765625,0.042266845703125,-0.0355224609375,0.006137847900390625,-0.01433563232421875,0.016082763671875,-0.005008697509765625,0.00992584228515625,0.00629425048828125,-0.004161834716796875,-0.005641937255859375,-0.048736572265625,0.044769287109375,0.017120361328125,-0.00774383544921875,-0.0171966552734375,-0.050506591796875,0.00727081298828125,0.00801849365234375,-0.005168914794921875,-0.0166015625,0.0166168212890625,-0.0218353271484375,-0.05120849609375,-0.0313720703125,-0.024658203125,-0.0006842613220214844,0.01166534423828125,-0.00501251220703125,-0.012298583984375,-0.01187896728515625,0.0305328369140625,-0.009307861328125,0.01471710205078125,0.0308685302734375,-0.022796630859375,-0.025390625,-0.006259918212890625,-0.01416778564453125,0.037506103515625,0.00496673583984375,-0.021575927734375,-0.036651611328125,-0.0087432861328125,-0.005306243896484375,-0.009033203125,-9.173154830932617e-5,0.0262908935546875,-0.017181396484375,-0.01293182373046875,-0.0230255126953125,-0.01424407958984375,0.0164031982421875,0.0309600830078125,0.0158538818359375,-0.0127410888671875,0.02581787109375,0.0238037109375,-0.004192352294921875,0.00896453857421875,0.00753021240234375,0.006622314453125,0.0526123046875,0.008331298828125,-0.007320404052734375,0.0227508544921875,0.0141448974609375,0.0011491775512695312,0.047515869140625,0.04437255859375,0.0261383056640625,0.0068206787109375,-0.0011720657348632812,-0.027801513671875,-0.01300811767578125,-0.041473388671875,-0.012115478515625,0.029937744140625,-0.0335693359375,0.01525115966796875,0.042999267578125,-0.0008697509765625,-0.001102447509765625,0.0200347900390625,0.0173187255859375,-0.002368927001953125,0.0140533447265625,-0.006744384765625,-0.0168914794921875,0.010589599609375,0.01473236083984375,0.01103973388671875,0.00899505615234375,0.005115509033203125,0.01548004150390625,0.030792236328125,0.00659942626953125,0.003826141357421875,0.0055084228515625,0.0194244384765625,0.0216064453125,0.005321502685546875,-0.00592803955078125,0.01125335693359375,-0.00402069091796875,0.0145721435546875,-0.00800323486328125,0.0433349609375,0.033477783203125,0.0328369140625,-0.0249481201171875,-0.0020923614501953125,0.0296173095703125,-0.0020809173583984375,-0.0117645263671875,-0.0018367767333984375,0.0048675537109375,0.0296173095703125,-0.024322509765625,0.0013589859008789062,0.028778076171875,0.007457733154296875,-0.025604248046875,0.026336669921875,-0.012451171875,-0.0253143310546875,-0.00572967529296875,-0.0039215087890625,0.0029811859130859375,-0.008880615234375,0.016693115234375,-0.00099945068359375,-0.0092926025390625,0.0160980224609375,0.01467132568359375,-0.017486572265625,0.0335693359375,-0.0231170654296875,0.00885772705078125,0.060272216796875,-0.003997802734375,-0.039093017578125,0.01239013671875,0.0142669677734375,0.01190948486328125,-0.01258087158203125,-0.00726318359375,0.04296875,-0.006504058837890625,0.00582122802734375,-0.0195159912109375,-0.0049591064453125,0.006290435791015625,0.01580810546875,-0.04083251953125,0.004459381103515625,0.00592803955078125,-0.01502227783203125,-0.0177154541015625,0.03851318359375,-0.0216217041015625,0.005863189697265625,0.043975830078125,-0.01204681396484375]', '54e8f75fb8d5a4549f1ae01f60b7e9a1', '2026-07-11 09:54:05', 100, 1, 1, '2026-07-11 09:54:02', '2026-07-11 09:54:02', '2026-07-11 09:53:31', '2026-07-11 09:54:02');
INSERT INTO `kb_articles` (`id`, `category_id`, `type`, `title`, `keywords`, `search_tags`, `question`, `answer`, `detailed_description`, `product_id`, `price_override`, `status`, `version`, `ai_ready`, `embedding`, `embedding_hash`, `embedding_updated_at`, `priority`, `created_by`, `approved_by`, `approved_at`, `published_at`, `created_at`, `updated_at`) VALUES
(21, NULL, 'faq', 'Customer Support', NULL, NULL, 'Customer support Support chahiye Help chahiye Mujhe help karo Customer care Support team Need assistance Help me', 'We\'re happy to help you. Please let us know your query, and our customer support team will assist you as soon as possible.', NULL, NULL, NULL, 'published', 1, 1, '[0.0616455078125,-0.036712646484375,0.0093994140625,0.03704833984375,-0.0264739990234375,-0.03704833984375,-0.0263214111328125,0.015350341796875,-0.015289306640625,0.0170440673828125,0.01593017578125,-0.0255889892578125,-0.032867431640625,0.0055999755859375,0.0235595703125,0.0196685791015625,0.002552032470703125,0.01629638671875,-0.01194000244140625,0.0130462646484375,0.050201416015625,0.05181884765625,0.02392578125,0.022857666015625,0.037200927734375,0.039031982421875,-0.0131072998046875,0.00823974609375,-0.0003414154052734375,-0.02191162109375,0.01922607421875,-0.02642822265625,-0.00858306884765625,0.00846099853515625,-0.0018911361694335938,0.01331329345703125,-0.0440673828125,-0.01494598388671875,0.008026123046875,0.0298614501953125,0.01309967041015625,0.0084075927734375,-0.00579071044921875,0.0355224609375,-0.015594482421875,0.0049285888671875,-0.01134490966796875,0.0290374755859375,0.0017633438110351562,0.04248046875,-0.042449951171875,-0.0046234130859375,0.0321044921875,0.01800537109375,0.0014257431030273438,-0.0282745361328125,0.033477783203125,0.0171966552734375,-0.00333404541015625,-0.01708984375,-0.0283203125,-0.03436279296875,-0.0341796875,-0.030517578125,-0.0005235671997070312,0.01303863525390625,-0.009246826171875,0.008514404296875,-0.0245513916015625,0.004055023193359375,-0.0162200927734375,0.06292724609375,0.02581787109375,-0.007785797119140625,-0.038238525390625,-0.01076507568359375,0.0181884765625,-0.02105712890625,0.02069091796875,-0.029327392578125,-0.040863037109375,-0.0231781005859375,-0.07464599609375,0.0272216796875,0.00024199485778808594,-0.0206298828125,-0.05908203125,0.0137939453125,-0.0316162109375,-0.01013946533203125,0.026031494140625,0.036895751953125,-0.032440185546875,0.015869140625,0.032928466796875,-0.0103759765625,0.01194000244140625,-0.01030731201171875,-0.0155792236328125,0.049530029296875,0.028961181640625,0.0066986083984375,-0.0190582275390625,0.00626373291015625,0.0631103515625,-0.0249481201171875,0.006961822509765625,-0.0300445556640625,-0.024017333984375,-0.004535675048828125,-0.10693359375,-0.0293731689453125,0.0271148681640625,-0.00862884521484375,0.017974853515625,0.01369476318359375,0.016754150390625,-0.080078125,-0.032989501953125,-0.0628662109375,-0.007747650146484375,0.0014791488647460938,0.050750732421875,-0.0594482421875,-0.0185699462890625,-0.002170562744140625,-0.0030364990234375,0.00372314453125,-0.055999755859375,-0.0184326171875,-0.0087890625,-0.01666259765625,0.0034046173095703125,0.035736083984375,-0.013916015625,-0.0004642009735107422,-0.0014209747314453125,-0.0330810546875,-0.07275390625,-0.00960540771484375,-0.0280609130859375,-0.036468505859375,-0.0283203125,0.0197906494140625,0.014801025390625,-0.0299224853515625,-0.007251739501953125,-0.025054931640625,-0.0119171142578125,-0.01271820068359375,-0.0217132568359375,-0.01763916015625,-0.0457763671875,0.0027065277099609375,0.0028018951416015625,-0.0013408660888671875,0.0268707275390625,0.007045745849609375,-0.055816650390625,-0.01444244384765625,-0.0293426513671875,-0.0538330078125,-0.057220458984375,-0.037200927734375,0.007053375244140625,-0.0672607421875,-0.017303466796875,-0.00937652587890625,-0.0230255126953125,-0.021270751953125,-0.01468658447265625,0.00872802734375,-0.08856201171875,-0.018951416015625,0.032470703125,-0.00041961669921875,0.0216827392578125,0.017730712890625,-0.031280517578125,-0.0255889892578125,-0.005970001220703125,0.0404052734375,0.007965087890625,-0.01450347900390625,0.01873779296875,0.0230560302734375,0.007610321044921875,0.033203125,-0.012939453125,0.01070404052734375,-0.0775146484375,-0.011505126953125,-0.0252532958984375,-0.0650634765625,-0.05792236328125,-0.036224365234375,0.031982421875,-0.0189666748046875,-0.0293426513671875,-0.00878143310546875,0.0268402099609375,-0.0225067138671875,0.0292205810546875,0.04803466796875,-0.016265869140625,-0.02703857421875,-0.04437255859375,0.0015659332275390625,-0.01273345947265625,-0.00927734375,0.053619384765625,-0.036773681640625,-0.030303955078125,0.0196075439453125,-0.0266876220703125,-0.035797119140625,-0.0246734619140625,0.0270843505859375,0.00762176513671875,-0.035064697265625,-0.03643798828125,0.0216522216796875,0.03350830078125,0.033599853515625,-0.0016021728515625,0.0259857177734375,0.046661376953125,-0.01611328125,0.047454833984375,-0.039947509765625,-0.0125274658203125,-0.01094818115234375,0.029510498046875,0.0230865478515625,0.02520751953125,-0.06109619140625,0.06610107421875,0.029144287109375,0.0194549560546875,-0.07781982421875,0.0070953369140625,0.006328582763671875,0.01480865478515625,0.015777587890625,0.020660400390625,-0.019256591796875,0.0166015625,-0.018157958984375,0.03887939453125,-0.0298614501953125,0.01480865478515625,0.0186004638671875,0.007137298583984375,-0.083740234375,0.0164031982421875,-0.0286865234375,0.032562255859375,0.05938720703125,0.0264892578125,-0.0244903564453125,-0.008880615234375,0.0110626220703125,-0.0223388671875,-0.0170745849609375,-0.0312347412109375,0.032745361328125,-0.0141448974609375,0.002429962158203125,-0.057098388671875,0.015411376953125,-0.00811004638671875,-0.0033855438232421875,0.0701904296875,0.018798828125,-0.0298919677734375,0.0211029052734375,-0.0020885467529296875,0.0295867919921875,0.040618896484375,-0.00885009765625,-0.0275115966796875,0.0090179443359375,0.0010099411010742188,0.005504608154296875,-0.01134490966796875,0.00890350341796875,0.0021572113037109375,-0.0325927734375,-0.042205810546875,0.061431884765625,-0.0083160400390625,-0.05291748046875,-0.039764404296875,0.01739501953125,-0.029388427734375,0.0013055801391601562,0.048431396484375,0.00738525390625,0.0036640167236328125,0.01227569580078125,-0.0144195556640625,-0.011566162109375,0.0133819580078125,-0.004520416259765625,-0.0208587646484375,-0.0183563232421875,0.0164031982421875,-0.023284912109375,0.0572509765625,-0.036224365234375,0.026153564453125,0.0039520263671875,0.0706787109375,0.013214111328125,-0.062255859375,-0.0721435546875,0.020355224609375,-0.0310821533203125,0.0196990966796875,0.01165008544921875,-0.0196533203125,0.0025653839111328125,0.00983428955078125,-0.040130615234375,0.02862548828125,-0.02862548828125,0.0068511962890625,0.0264129638671875,-0.0234375,-0.01543426513671875,-0.024810791015625,-0.0345458984375,0.00943756103515625,-0.03717041015625,-0.0100555419921875,0.04302978515625,0.01513671875,0.0032520294189453125,-0.007129669189453125,-0.01522064208984375,-0.01029205322265625,-0.04522705078125,-0.059173583984375,-0.0175323486328125,-0.00710296630859375,-0.049957275390625,0.034912109375,0.033660888671875,0.03265380859375,0.03802490234375,0.00811004638671875,0.028076171875,-0.0015621185302734375,0.016326904296875,0.01041412353515625,-0.005588531494140625,0.0028076171875,-0.020751953125,0.0233306884765625,-0.0003056526184082031,-0.0259246826171875,0.00820159912109375,-0.0158538818359375,0.00862884521484375,0.0240631103515625,0.054351806640625,-0.035186767578125,-0.01708984375,0.047454833984375,0.0228118896484375,0.0162811279296875,-0.062408447265625,0.00864410400390625,0.0223541259765625,-0.065185546875,-0.0196075439453125,0.006229400634765625,0.00910186767578125,-0.02618408203125,0.004573822021484375,0.038482666015625,-0.06597900390625,-0.0223388671875,-0.0161285400390625,0.036163330078125,0.005550384521484375,-0.059783935546875,0.0172576904296875,0.0013399124145507812,-0.01763916015625,0.044281005859375,0.014556884765625,0.03863525390625,-0.0020751953125,0.04193115234375,0.047119140625,0.03436279296875,0.065673828125,0.003269195556640625,-0.053497314453125,0.05206298828125,-0.0633544921875,0.007495880126953125,0.0557861328125,-0.02044677734375,0.027557373046875,-0.0305938720703125,-0.0291595458984375,-0.0032596588134765625,-0.030303955078125,0.056121826171875,-0.005451202392578125,0.0183868408203125,0.050994873046875,-0.0280609130859375,-0.0194091796875,-0.01177978515625,0.05535888671875,0.0209808349609375,0.0003647804260253906,-0.02593994140625,-0.0309295654296875,0.0299835205078125,0.00890350341796875,-0.02191162109375,0.0311431884765625,0.03924560546875,-0.013671875,0.035888671875,-0.01025390625,-0.0236053466796875,-0.005413055419921875,-0.006923675537109375,-0.029205322265625,-0.03546142578125,0.0031490325927734375,-0.00458526611328125,-0.032501220703125,0.0014171600341796875,-0.045440673828125,-0.004711151123046875,0.0002605915069580078,-0.00855255126953125,0.060272216796875,0.0310211181640625,0.0043182373046875,-0.0206451416015625,0.017303466796875,-0.00972747802734375,0.0087127685546875,0.00836944580078125,0.0227203369140625,0.04876708984375,0.022857666015625,-0.08746337890625,0.0021228790283203125,0.013671875,0.01090240478515625,0.044891357421875,0.058746337890625,0.0024280548095703125,0.031494140625,0.0181732177734375,0.023773193359375,-0.01849365234375,-0.034271240234375,-0.0197601318359375,-0.024993896484375,-0.0022735595703125,-0.0215911865234375,-0.0179595947265625,0.049957275390625,-0.0004627704620361328,-0.0186309814453125,-0.0523681640625,-0.044342041015625,0.017059326171875,-0.011199951171875,0.00021886825561523438,-0.0335693359375,0.0284881591796875,-0.0181121826171875,-0.07257080078125,0.0238494873046875,-0.0175323486328125,-0.0181427001953125,0.028839111328125,-0.0205230712890625,-0.00022912025451660156,0.01241302490234375,0.018310546875,0.01082611083984375,0.0202178955078125,-0.00675201416015625,-0.0207366943359375,-0.00696563720703125,-0.0148468017578125,0.003093719482421875,-0.0151214599609375,-0.0158843994140625,-0.05853271484375,-0.0126800537109375,-0.016357421875,-0.01331329345703125,0.01071929931640625,0.029693603515625,0.03692626953125,-0.006160736083984375,-0.0167694091796875,6.490945816040039e-5,0.007564544677734375,0.049346923828125,0.001613616943359375,0.0056610107421875,-0.02532958984375,-0.0233917236328125,0.03326416015625,0.0034809112548828125,-0.026641845703125,-0.0122833251953125,-0.00377655029296875,-0.005748748779296875,0.026519775390625,-0.00748443603515625,-0.017059326171875,-0.027862548828125,-0.00632476806640625,-0.017303466796875,0.013214111328125,-0.011749267578125,0.02587890625,-0.0012178421020507812,-0.0186767578125,-0.00012302398681640625,-0.004245758056640625,-0.02685546875,0.02667236328125,0.0158843994140625,-0.0132904052734375,-0.034027099609375,0.01296234130859375,0.006153106689453125,0.0217132568359375,0.01214599609375,0.04339599609375,0.020477294921875,0.0095367431640625,0.013519287109375,-0.0146636962890625,0.0256500244140625,0.011016845703125,-0.00792694091796875,0.00435638427734375,0.007205963134765625,0.04193115234375,-0.02587890625,-0.00865936279296875,-0.039459228515625,-0.004150390625,0.03033447265625,-0.007266998291015625,0.009307861328125,0.0034313201904296875,0.01323699951171875,0.0064239501953125,0.05712890625,0.0059814453125,0.006992340087890625,-0.040008544921875,-0.01849365234375,0.003734588623046875,0.03118896484375,-0.032958984375,-0.06573486328125,0.08056640625,-0.0008826255798339844,-0.063232421875,0.007465362548828125,0.01611328125,0.039398193359375,-0.0012998580932617188,-0.007030487060546875,0.02020263671875,0.0273590087890625,-0.026458740234375,-0.0251007080078125,0.016265869140625,-0.0194854736328125,0.007251739501953125,-0.0022335052490234375,-0.0318603515625,-0.020721435546875,0.04864501953125,0.027587890625,0.0004911422729492188,-0.026702880859375,-0.032073974609375,0.005168914794921875,0.01033782958984375,-0.012786865234375,0.0012826919555664062,-0.01396942138671875,-0.02044677734375,-0.01690673828125,-0.012298583984375,0.00014495849609375,0.01104736328125,0.03704833984375,0.025543212890625,0.01012420654296875,0.000957489013671875,-0.01373291015625,-0.005359649658203125,-0.0104522705078125,-0.0216217041015625,0.0078887939453125,-0.019744873046875,0.0022716522216796875,-0.0216064453125,0.0211029052734375,-0.007534027099609375,0.0077056884765625,0.048248291015625,0.010528564453125,0.0159454345703125,0.01288604736328125,-0.006649017333984375,0.00012743473052978516,-0.006931304931640625,0.00997161865234375,-0.01309967041015625,0.004329681396484375,0.052093505859375,-0.03472900390625,0.0024127960205078125,-0.01207733154296875,0.00283050537109375,0.021697998046875,-0.0175933837890625,0.00490570068359375,0.0102691650390625,-0.012786865234375,-0.01216888427734375,-0.01052093505859375,0.04315185546875,0.01433563232421875,-0.0229949951171875,-0.0032958984375,0.01245880126953125,0.0115966796875,0.00476837158203125,-0.025909423828125,0.0211029052734375,-0.054046630859375,0.022308349609375,-0.010711669921875,-0.0306549072265625,0.048797607421875,0.006603240966796875,0.061920166015625,-0.03485107421875,0.01247406005859375,0.0030498504638671875,-0.0164642333984375,-0.007137298583984375,-0.0111236572265625,0.004497528076171875,-0.007457733154296875,0.080322265625,-0.0092315673828125,-0.0265960693359375,0.0023632049560546875,0.033294677734375,-0.0189971923828125,-0.042572021484375,-0.0096893310546875,0.0244293212890625,-0.0144805908203125,0.0082855224609375,0.0177001953125,-0.03839111328125,0.01465606689453125,-0.032989501953125,-0.0081939697265625,0.054168701171875,-0.00832366943359375,0.05218505859375,0.006313323974609375,-0.0028057098388671875,0.01763916015625,0.019989013671875,-0.00811004638671875,-0.031768798828125,0.0017499923706054688,0.0211029052734375,0.0012292861938476562,0.0279083251953125,0.01392364501953125,0.00334930419921875,-0.0938720703125,-0.01505279541015625,-0.06048583984375,-0.006549835205078125,-0.0006732940673828125,0.0041046142578125,-0.0138092041015625,-0.018646240234375,-0.00959014892578125,0.038818359375,0.0102386474609375,-0.038848876953125,0.0208892822265625,0.00957489013671875,0.0226898193359375,-0.01861572265625,0.0259857177734375,-0.034271240234375,-0.028839111328125,0.0251922607421875,0.012603759765625,-0.005130767822265625,0.01097869873046875,0.006580352783203125,0.0070037841796875,0.002216339111328125,0.0006947517395019531,-0.0061798095703125,0.0261383056640625,0.0093536376953125,0.00260162353515625,0.0218505859375,0.032989501953125,0.0010480880737304688,-0.005275726318359375,0.0192108154296875,0.01139068603515625,-0.053466796875,-0.0034656524658203125,-0.031585693359375,-0.03509521484375,0.00974273681640625,-0.048980712890625,0.0030193328857421875,0.0249481201171875,-0.0065460205078125,0.00945281982421875,-0.0151519775390625,0.033477783203125,-0.03271484375,0.01125335693359375,0.0178070068359375,0.00449371337890625,0.00748443603515625,0.0225830078125,0.040557861328125,-0.0673828125,-0.005584716796875,-0.0253753662109375,-0.03814697265625,0.0219573974609375,-0.044281005859375,0.05279541015625,0.0214080810546875,0.00894927978515625,-0.0250244140625,0.0106201171875,-0.0249481201171875,-0.017608642578125,0.01849365234375,-0.0100250244140625,-0.0233612060546875,-0.0181121826171875,-0.0208892822265625,-0.01261138916015625,-0.00795745849609375,-0.0275726318359375,-0.01233673095703125,0.0352783203125,-0.00623321533203125,-0.0290374755859375,0.00751495361328125,0.0031871795654296875,0.00024509429931640625,-0.0202789306640625,-0.01904296875,0.007732391357421875,0.005588531494140625,-0.042083740234375,-0.01439666748046875,-0.047576904296875,-0.00856781005859375,-0.018035888671875,-0.0124053955078125,-0.0006399154663085938,-0.01111602783203125,0.0199432373046875,0.01245880126953125,0.01177215576171875,0.021697998046875,-0.0236053466796875,0.00252532958984375,0.0350341796875,-0.01410675048828125,0.020263671875,0.031158447265625,0.023773193359375,-0.0180816650390625,-0.001018524169921875,0.005672454833984375,0.002208709716796875,-0.00662994384765625,-0.025665283203125,0.0170440673828125,0.01312255859375,-0.0038299560546875,0.0009303092956542969,0.01389312744140625,-0.0003542900085449219,-0.0631103515625,-0.0167999267578125,0.07025146484375,-8.732080459594727e-5,-0.00669097900390625,-0.0094757080078125,-0.0027713775634765625,0.01204681396484375,0.0084228515625,-0.0151214599609375,-0.0272216796875,-0.018280029296875,0.047088623046875,-0.0279693603515625,-0.003681182861328125,0.004085540771484375,0.00907135009765625,-0.0007677078247070312,0.0027332305908203125,-0.0289459228515625,0.006603240966796875,0.009429931640625,-2.0802021026611328e-5,0.03985595703125,0.03521728515625,0.02569580078125,0.0198974609375,0.01313018798828125,0.016021728515625,-0.0226898193359375,0.0271453857421875,0.02679443359375,0.0249481201171875,0.021759033203125,0.022796630859375,-0.0452880859375,-0.036163330078125,0.03155517578125,-0.04241943359375,-0.0007905960083007812,-0.00750732421875,-0.006195068359375,0.0007023811340332031,0.005157470703125,-0.003749847412109375,-0.060943603515625,0.036224365234375,-0.006633758544921875,0.049896240234375,-0.003551483154296875,0.021453857421875,-0.02349853515625,0.023468017578125,-0.003173828125,8.016824722290039e-5,-0.016815185546875,-0.0016002655029296875,-0.012359619140625,0.00905609130859375,-0.041351318359375,-0.00969696044921875,-0.0173797607421875,0.0406494140625,-0.0241546630859375,-0.0226287841796875,0.010009765625,-0.0136260986328125,0.00762176513671875,-0.01068115234375,0.036102294921875,0.0211334228515625,-0.0013513565063476562,0.017181396484375,0.005107879638671875,0.0206146240234375,-0.020751953125,-0.033843994140625,0.033233642578125,-0.0225372314453125,0.038330078125,0.0028705596923828125,0.031341552734375,-0.007152557373046875,-0.0008225440979003906,-0.0228424072265625,0.0144805908203125,-0.0015125274658203125,0.0007205009460449219,0.006439208984375,-0.015655517578125,-0.003978729248046875,0.03680419921875,-0.0164642333984375,-0.0274505615234375,0.0164337158203125,0.00836181640625,0.0192413330078125,-0.005947113037109375,-0.0306854248046875,0.00870513916015625,0.04034423828125,0.0181884765625,-0.015777587890625,-0.02069091796875,0.007328033447265625,-0.01526641845703125,-0.00449371337890625,0.0118865966796875,0.013427734375,0.0125732421875,0.011474609375,-0.0028705596923828125,0.031982421875,0.043792724609375,0.011566162109375,0.0017070770263671875,-0.0175933837890625,-0.0230255126953125,0.0103912353515625,0.00983428955078125,-0.003688812255859375,6.198883056640625e-6,0.0200958251953125,0.005947113037109375,-0.047210693359375,0.005970001220703125,0.053924560546875,0.0003845691680908203,0.011993408203125,-0.058441162109375,0.004123687744140625,-0.0008597373962402344,0.06707763671875,-0.0022907257080078125,0.025299072265625,0.0019121170043945312,-0.013397216796875,0.00035762786865234375,-0.0271148681640625,-0.0312042236328125,0.0036773681640625,0.0196990966796875,0.023193359375,-0.042205810546875,0.0004425048828125,-0.00982666015625,0.02410888671875,0.033294677734375,-0.004695892333984375,0.004550933837890625,0.054718017578125,0.0102081298828125,-0.01947021484375,0.014801025390625,0.0386962890625,0.0252685546875,-0.0151214599609375,-0.0076446533203125,-0.008209228515625,-0.013702392578125,0.01232147216796875,-0.00392913818359375,0.036590576171875,-0.005550384521484375,0.0212249755859375,0.024169921875,-0.0027179718017578125,-0.0038089752197265625,0.00262451171875,0.010467529296875,-0.028076171875,0.011962890625,-0.013946533203125,0.0172271728515625,0.026641845703125,0.020477294921875,0.0298919677734375,-0.0186004638671875,0.0027484893798828125,-0.03472900390625,0.0174102783203125,0.0150299072265625,0.04052734375,-0.0271759033203125,-0.0321044921875,-0.01451873779296875,0.0071563720703125,-0.0482177734375,0.0089874267578125,-0.00849151611328125,-0.0192108154296875,-0.0284576416015625,-0.00492095947265625,-0.015411376953125,-0.0204010009765625,-0.01019287109375,-0.0034542083740234375,0.036651611328125,-0.00841522216796875,-0.034515380859375,-0.00730133056640625,0.0009007453918457031,-0.0064849853515625,0.0116119384765625,-0.0230560302734375,0.007328033447265625,-0.026397705078125,0.004558563232421875,-0.0006914138793945312,0.00305938720703125,0.0274200439453125,0.0159912109375,0.00927734375,-0.01384735107421875,-0.00238800048828125,-0.0022258758544921875,-0.03607177734375,0.014984130859375,-0.01323699951171875,0.01123809814453125,2.384185791015625e-7,-0.0133209228515625,0.026947021484375,0.0125732421875,-0.023773193359375,0.0372314453125,-0.0020465850830078125,-0.004932403564453125,0.0036163330078125,-0.023162841796875,0.01529693603515625,0.0130157470703125,-0.010162353515625,-0.00377655029296875,-0.0180511474609375,0.00760650634765625,0.0023288726806640625,-0.0187225341796875,0.0034732818603515625,0.0003662109375,0.00885772705078125,0.0306854248046875,-0.0159912109375,0.01461029052734375,0.00905609130859375,-0.026519775390625,0.00469207763671875,-0.013153076171875,-0.037445068359375,-0.021240234375,0.006755828857421875,-0.0005478858947753906,0.0172271728515625,0.0277862548828125,0.0115814208984375,-0.00970458984375,0.039794921875,-0.0286712646484375,-0.013092041015625,-0.0278472900390625,0.0096893310546875,0.030426025390625,-0.00897216796875,0.019378662109375,0.00771331787109375,-0.00756072998046875,0.0018301010131835938,-0.03277587890625,-0.01169586181640625,0.01131439208984375,-0.0241546630859375,0.005420684814453125,-0.0025997161865234375,0.0345458984375,0.01499176025390625,0.030517578125,-0.01526641845703125,0.011474609375,-0.015777587890625,0.0284423828125,0.0230255126953125,-0.0067596435546875,0.0533447265625,0.045257568359375,0.016021728515625,-0.0167388916015625,-0.007183074951171875,0.0194549560546875,-0.0124664306640625,0.02239990234375,0.017669677734375,0.00302886962890625,0.01235198974609375,0.0034389495849609375,-0.00818634033203125,0.0108795166015625,0.028656005859375,-0.004486083984375,0.0259857177734375,-0.0247650146484375,0.01995849609375,0.0310211181640625,-0.058349609375,0.02093505859375,0.0126495361328125,-0.0001481771469116211,-0.0158538818359375,0.003021240234375,-0.00830078125,0.017425537109375,-0.0287933349609375,0.0198974609375,0.0019321441650390625,0.0283203125,-0.0126495361328125,-0.040283203125,0.00899505615234375,-0.01010894775390625,-0.0031585693359375,0.005336761474609375,-0.017333984375,-0.0269622802734375,0.00408172607421875,-0.0086822509765625,-0.0184173583984375,0.01172637939453125,-0.024322509765625,0.0016927719116210938,0.0117340087890625,0.06622314453125,-0.003528594970703125,-0.031890869140625,-0.006988525390625,0.02789306640625,-0.00730133056640625,-0.0313720703125,-0.006839752197265625,-0.01290130615234375,-0.0307464599609375,-0.003437042236328125,0.01509857177734375,0.0018749237060546875,0.054931640625,0.00612640380859375,-0.01320648193359375,-0.009521484375,0.012939453125,-0.0224609375,-0.023651123046875,0.011016845703125,0.005313873291015625,0.022857666015625,-0.0012950897216796875,-0.0110626220703125,-0.0109100341796875,0.0175628662109375,-0.00539398193359375,0.028045654296875,0.01690673828125,0.01163482666015625,0.02740478515625,0.00939178466796875,-0.0229034423828125,-0.0010700225830078125,0.0013017654418945312,-0.00014495849609375,-0.002101898193359375,0.024505615234375,0.0240631103515625,0.007251739501953125,-0.0224609375,-0.0007605552673339844,0.0237884521484375,-0.01177215576171875,-0.01739501953125,-0.0308685302734375,-0.006450653076171875,0.0288848876953125,-0.01200103759765625,0.00789642333984375,0.005931854248046875,0.01004791259765625,-0.0174713134765625,-0.038604736328125,0.009124755859375,0.004230499267578125,0.01016998291015625,-0.00939178466796875,0.03070068359375,-0.0162811279296875,0.00846099853515625,0.0313720703125,0.0207061767578125,0.002956390380859375,-0.00794219970703125,-0.041717529296875,0.0197601318359375,-0.0291595458984375,-0.00865936279296875,0.0197906494140625,0.00016486644744873047,0.0023860931396484375,-0.01216888427734375,0.0169525146484375,0.054046630859375,0.0307159423828125,0.00897216796875,-0.03857421875,-0.037933349609375,0.0203094482421875,-0.011199951171875,-0.01021575927734375,0.0214385986328125,0.0293426513671875,-0.012054443359375,-0.0113067626953125,0.054779052734375,0.01491546630859375,3.546476364135742e-5,-0.0088958740234375,0.00647735595703125,0.0032806396484375,0.0004367828369140625,0.024444580078125,0.036956787109375,-0.01529693603515625,0.021331787109375,0.0195770263671875,-0.010772705078125,0.0042266845703125,-0.001575469970703125,-5.990266799926758e-5,0.01515960693359375,-0.037322998046875,-0.0290374755859375,0.0267486572265625,-0.051177978515625,0.004032135009765625,-0.028045654296875,0.0095062255859375,-0.007717132568359375,-0.0023040771484375,0.03363037109375,0.019561767578125,0.01116943359375,-0.01029205322265625,-0.01299285888671875,-0.006427764892578125,0.0004761219024658203,0.0170135498046875,0.01316070556640625,-0.033477783203125,0.032928466796875,0.01488494873046875,-0.00628662109375,0.00628662109375,-0.01309967041015625,0.0460205078125,0.046539306640625,-0.023895263671875,0.03857421875,0.0141143798828125,0.0178680419921875,-0.00664520263671875,0.0265350341796875,-0.0369873046875,-0.01554107666015625,0.0267333984375,0.00801849365234375,-0.0667724609375,0.0091705322265625,-0.0170440673828125,0.0081329345703125,0.0162506103515625,0.0181121826171875,-0.0318603515625,0.016845703125,0.0428466796875,0.0051116943359375,0.00862884521484375,0.004871368408203125,-0.00020051002502441406,0.01727294921875,0.0012416839599609375,-0.0129547119140625,0.0165557861328125,-0.01535797119140625,0.0007467269897460938,0.0086669921875,-0.020355224609375,0.0005211830139160156,-0.044464111328125,-0.03436279296875,-0.0217132568359375,-0.008331298828125,0.032135009765625,0.0083465576171875,0.018798828125,-0.01373291015625,-0.0105133056640625,0.0217132568359375,-0.01045989990234375,-0.006069183349609375,-0.00786590576171875,-0.0005116462707519531,-0.02783203125,0.050933837890625,0.01520538330078125,-0.03997802734375,0.00988006591796875,0.018218994140625,-0.02191162109375,-0.01617431640625,-0.0162353515625,0.0482177734375,-0.021484375,-0.0192413330078125,-0.0306549072265625,0.0029163360595703125,-0.0033416748046875,-0.0015850067138671875,-0.0240631103515625,0.006191253662109375,-0.01922607421875,-0.00667572021484375,0.0152587890625,-0.018798828125,0.028289794921875,-0.004467010498046875,0.056427001953125,-0.004543304443359375,-0.034881591796875,-0.01107025146484375,-0.0142822265625,-0.01605224609375,0.0082244873046875,-0.00604248046875,-0.0032825469970703125,-0.00815582275390625,0.03887939453125,-0.0102386474609375,-0.050994873046875,0.00946044921875,-0.03143310546875,0.0293121337890625,0.03179931640625,0.00957489013671875,-0.0084075927734375,-0.0214385986328125,-0.00722503662109375,-0.001739501953125,0.0081939697265625,0.007354736328125,0.0240478515625,0.024658203125,-0.025848388671875,-0.046600341796875,-0.006122589111328125,0.003032684326171875,0.05120849609375,-0.011962890625,0.0020351409912109375,0.006137847900390625,-0.03997802734375,0.01094818115234375,-0.009674072265625,0.01410675048828125,-0.00858306884765625,0.005222320556640625,-0.0006895065307617188,0.00493621826171875,-0.0474853515625,-0.0216217041015625,-0.0206146240234375,-0.021575927734375,0.0078277587890625,-0.00431060791015625,-0.047088623046875,-0.00862884521484375,0.013519287109375,0.003978729248046875,-0.019989013671875,-0.002010345458984375,0.0002498626708984375,0.0273895263671875,0.0167694091796875,0.01271820068359375,-0.001705169677734375,-0.00969696044921875,0.00408172607421875,0.02484130859375,0.0216827392578125,-0.0231781005859375,0.0019817352294921875,-0.0032405853271484375,-0.0296173095703125,-0.022613525390625,0.0016155242919921875,0.0235595703125,-0.0093841552734375,0.005313873291015625,0.06475830078125,-0.0290985107421875,-0.001277923583984375,0.007617950439453125,0.017669677734375,0.002353668212890625,0.035125732421875,-0.0157623291015625,-0.04638671875,0.04571533203125,-0.0027313232421875,-0.0264892578125,-0.0224456787109375,-0.025665283203125,0.01739501953125,0.039520263671875,0.0178680419921875,-0.036376953125,0.028167724609375,0.01739501953125,0.00598907470703125,0.0279083251953125,-0.00984954833984375,0.0102386474609375,0.005138397216796875,-0.0037555694580078125,0.00751495361328125,0.01230621337890625,-0.003803253173828125,-0.0192413330078125,0.0130157470703125,-0.0256500244140625,0.0268707275390625,0.0335693359375,0.0247039794921875,-0.0025653839111328125,0.00693511962890625,0.0246124267578125,0.032867431640625,0.0160369873046875,-0.0196533203125,-0.00695037841796875,0.040252685546875,0.01107025146484375,0.032257080078125,-0.017547607421875,0.01482391357421875,0.0196380615234375,0.004306793212890625,0.0232696533203125,-0.0046234130859375,-0.012542724609375,0.00743865966796875,-0.03863525390625,-0.02850341796875,-0.02691650390625,0.0186767578125,-0.023345947265625,0.01331329345703125,-0.00621795654296875,3.653764724731445e-5,0.0224151611328125,0.03643798828125,0.0289306640625,0.014312744140625,-0.021270751953125,0.008026123046875,-0.0018329620361328125,0.0025577545166015625,0.04644775390625,-0.0015039443969726562,0.001407623291015625,0.00984954833984375,0.027496337890625,0.01197052001953125,0.01515960693359375,0.031494140625,-0.01168060302734375,-0.0225372314453125,4.661083221435547e-5,0.0240936279296875,0.0168609619140625,-0.0178680419921875,0.00545501708984375,0.00872039794921875,-0.02557373046875,0.00505828857421875,0.0183258056640625,-0.014007568359375,0.002079010009765625,0.0019359588623046875,0.0009245872497558594,0.0026226043701171875,0.01227569580078125,-0.05712890625,0.01122283935546875,0.0282440185546875,-0.01245880126953125,-0.00396728515625,0.0267486572265625,0.0081329345703125,-0.033111572265625,0.0347900390625,0.0009021759033203125,0.0005898475646972656,0.026397705078125,0.01381683349609375,-0.0279693603515625,0.0155181884765625,-0.01207733154296875,0.01210784912109375,-0.0233917236328125,0.0026073455810546875,0.0159454345703125,0.00800323486328125,0.026641845703125,0.0180511474609375]', '8dadb0f4dafe782daac3b3d4976b0bc1', '2026-07-11 10:48:48', 100, 1, 1, '2026-07-11 10:48:46', '2026-07-11 10:48:46', '2026-07-11 10:48:37', '2026-07-11 10:48:46'),
(22, 4, 'faq', 'Warranty', NULL, NULL, 'Kitna warrnty milega ?', 'This is answers', NULL, NULL, NULL, 'published', 1, 1, NULL, NULL, NULL, 100, 1, 1, '2026-07-19 18:27:00', '2026-07-19 18:27:00', '2026-07-19 18:21:52', '2026-07-19 18:27:00'),
(26, NULL, 'faq', 'Our Product Categories', NULL, NULL, 'What products do you have? / Aapke paas kaun kaun se products hain?', 'Hum ye products offer karte hain:\n\n- Ev Spare Part (pcs)\n- Hybrid scooter Kit (pcs)\n- Lithium ion battries (pcs)\n- Lithium ion battries ISO (set)\n- Octa fx 2 (pcs)\n\nKisi specific product ke baare mein poochne ke liye uska naam bataiye — warranty, price ya stock, sab bata denge.', NULL, NULL, NULL, 'published', 1, 1, NULL, NULL, NULL, 100, 1, 1, '2026-07-19 18:24:15', '2026-07-19 18:24:15', '2026-07-19 18:24:15', '2026-07-19 18:24:15');

-- --------------------------------------------------------

--
-- Table structure for table `kb_article_tag`
--

CREATE TABLE `kb_article_tag` (
  `kb_article_id` bigint(20) UNSIGNED NOT NULL,
  `kb_tag_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kb_article_tag`
--

INSERT INTO `kb_article_tag` (`kb_article_id`, `kb_tag_id`) VALUES
(20, 11),
(20, 23),
(20, 24),
(20, 25),
(20, 26),
(20, 27),
(20, 28),
(21, 29),
(21, 30),
(21, 31);

-- --------------------------------------------------------

--
-- Table structure for table `kb_article_versions`
--

CREATE TABLE `kb_article_versions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `article_id` bigint(20) UNSIGNED NOT NULL,
  `version_number` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `question` text DEFAULT NULL,
  `answer` longtext NOT NULL,
  `status_at_time` varchar(255) DEFAULT NULL,
  `edited_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kb_article_versions`
--

INSERT INTO `kb_article_versions` (`id`, `article_id`, `version_number`, `title`, `question`, `answer`, `status_at_time`, `edited_by`, `created_at`) VALUES
(19, 20, 1, 'Product Catalog', 'Do you have a product catalog? Can you send your catalog? Can I get your brochure? Product catalog hai? Catalog bhejo. Brochure bhejo. Product list bhejo. Catelog hai?', 'Yes, we have a complete product catalog. Please share your requirements, and we will send you our latest product catalog in PDF format.', 'draft', 1, '2026-07-11 09:53:31'),
(20, 21, 1, 'Customer Support', 'Customer support Support chahiye Help chahiye Mujhe help karo Customer care Support team Need assistance Help me', 'We\'re happy to help you. Please let us know your query, and our customer support team will assist you as soon as possible.', 'draft', 1, '2026-07-11 10:48:37');

-- --------------------------------------------------------

--
-- Table structure for table `kb_categories`
--

CREATE TABLE `kb_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kb_categories`
--

INSERT INTO `kb_categories` (`id`, `name`, `slug`, `parent_id`, `icon`, `order`, `status`, `created_at`, `updated_at`) VALUES
(4, 'Product', 'product', NULL, NULL, 0, 'active', '2026-07-10 07:42:33', '2026-07-10 07:42:33');

-- --------------------------------------------------------

--
-- Table structure for table `kb_media`
--

CREATE TABLE `kb_media` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `article_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('image','pdf') NOT NULL,
  `purpose` varchar(255) NOT NULL DEFAULT 'general',
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED DEFAULT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kb_media`
--

INSERT INTO `kb_media` (`id`, `article_id`, `type`, `purpose`, `file_name`, `file_path`, `mime_type`, `file_size`, `uploaded_by`, `created_at`, `updated_at`) VALUES
(16, 20, 'pdf', 'catalog', 'QUO-0002 (2).pdf', 'kb-media/YOLCzB34BzPn1FXGZyo43zmGzkUNtt6wpOL7GvXg.pdf', 'application/pdf', 1811802, 1, '2026-07-11 09:53:50', '2026-07-11 09:53:50'),
(17, 20, 'pdf', 'general', 'QUO-0002 (2).pdf', 'kb-media/I7KWiqDGPD2wOurbephL4xlcGGriI3M501appaTs.pdf', 'application/pdf', 1811802, 1, '2026-07-11 09:53:52', '2026-07-11 09:53:52');

-- --------------------------------------------------------

--
-- Table structure for table `kb_tags`
--

CREATE TABLE `kb_tags` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kb_tags`
--

INSERT INTO `kb_tags` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'EYGFIYERF', 'eygfiyerf', '2026-07-09 12:14:01', '2026-07-09 12:14:01'),
(2, '3RU3R', '3ru3r', '2026-07-09 12:14:01', '2026-07-09 12:14:01'),
(3, '34RU34', '34ru34', '2026-07-09 12:14:01', '2026-07-09 12:14:01'),
(4, 'elctric scooter', 'elctric-scooter', '2026-07-10 08:11:38', '2026-07-10 08:11:38'),
(5, 'evbike', 'evbike', '2026-07-10 08:11:38', '2026-07-10 08:11:38'),
(6, 'bikeconversion', 'bikeconversion', '2026-07-10 08:32:31', '2026-07-10 08:32:31'),
(7, 'bike conversion', 'bike-conversion', '2026-07-10 08:32:31', '2026-07-10 08:32:31'),
(8, 'electric bike conversion', 'electric-bike-conversion', '2026-07-10 08:32:32', '2026-07-10 08:32:32'),
(9, 'Warranty', 'warranty', '2026-07-10 17:48:22', '2026-07-10 17:48:22'),
(10, 'kabtak chalega', 'kabtak-chalega', '2026-07-10 17:48:22', '2026-07-10 17:48:22'),
(11, 'catelog', 'catelog', '2026-07-10 19:03:36', '2026-07-10 19:03:36'),
(12, 'catelouge', 'catelouge', '2026-07-10 19:03:36', '2026-07-10 19:03:36'),
(13, 'cateloge', 'cateloge', '2026-07-10 19:03:36', '2026-07-10 19:03:36'),
(14, 'brocher', 'brocher', '2026-07-10 19:03:36', '2026-07-10 19:03:36'),
(15, 'brochers', 'brochers', '2026-07-10 19:03:36', '2026-07-10 19:03:36'),
(16, 'product images', 'product-images', '2026-07-10 20:07:29', '2026-07-10 20:07:29'),
(17, 'product img', 'product-img', '2026-07-10 20:07:29', '2026-07-10 20:07:29'),
(18, 'images', 'images', '2026-07-10 20:07:29', '2026-07-10 20:07:29'),
(19, 'image', 'image', '2026-07-10 20:07:29', '2026-07-10 20:07:29'),
(20, 'photo', 'photo', '2026-07-10 20:10:50', '2026-07-10 20:10:50'),
(21, 'product image', 'product-image', '2026-07-10 20:10:50', '2026-07-10 20:10:50'),
(22, 'gallery', 'gallery', '2026-07-10 20:10:50', '2026-07-10 20:10:50'),
(23, 'catalog', 'catalog', '2026-07-11 09:53:28', '2026-07-11 09:53:28'),
(24, 'brochure', 'brochure', '2026-07-11 09:53:29', '2026-07-11 09:53:29'),
(25, 'product catalog', 'product-catalog', '2026-07-11 09:53:29', '2026-07-11 09:53:29'),
(26, 'product list', 'product-list', '2026-07-11 09:53:29', '2026-07-11 09:53:29'),
(27, 'catalog pdf', 'catalog-pdf', '2026-07-11 09:53:29', '2026-07-11 09:53:29'),
(28, 'company profile', 'company-profile', '2026-07-11 09:53:29', '2026-07-11 09:53:29'),
(29, 'customer support', 'customer-support', '2026-07-11 10:48:37', '2026-07-11 10:48:37'),
(30, 'help', 'help', '2026-07-11 10:48:37', '2026-07-11 10:48:37'),
(31, 'customer carre', 'customer-carre', '2026-07-11 10:48:37', '2026-07-11 10:48:37');

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `gst_number` varchar(255) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `whatsapp` varchar(255) DEFAULT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `source` varchar(255) NOT NULL DEFAULT 'Website',
  `social_platform` varchar(255) DEFAULT NULL,
  `lead_type` enum('hot','cold','warm','converted','dealer') NOT NULL DEFAULT 'warm',
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `customer_name`, `company_name`, `gst_number`, `product_name`, `whatsapp`, `phone`, `email`, `billing_address`, `shipping_address`, `source`, `social_platform`, `lead_type`, `is_pinned`, `created_by`, `created_at`, `updated_at`) VALUES
(15, 'hybrid scooter kit', NULL, NULL, 'scooter kit', '8059509175', '8059509175', NULL, NULL, NULL, 'Other', NULL, 'cold', 1, 3, '2026-07-17 14:58:11', '2026-07-21 15:50:37'),
(18, '18 inch hub motor kit', NULL, NULL, 'hub motor', '7699452029', '7699452029', NULL, NULL, NULL, 'Other', NULL, 'warm', 1, 3, '2026-07-17 15:01:19', '2026-07-21 15:58:35'),
(22, '16 inch hub motor', NULL, NULL, 'hub motor', '9423598829', '9423598829', NULL, NULL, NULL, 'Other', NULL, 'hot', 0, 3, '2026-07-17 15:06:34', '2026-07-17 15:06:34'),
(30, '17 inch 2000w hub motor kit', NULL, NULL, 'hub motor', '7888720508', '7888720508', NULL, NULL, NULL, 'Other', NULL, 'hot', 0, 3, '2026-07-17 15:17:37', '2026-07-17 15:17:37'),
(32, '17 inch 2000w hub motor kit', NULL, NULL, 'hub motor', '8104363502', '8104363502', NULL, NULL, NULL, 'Other', NULL, 'cold', 0, 3, '2026-07-17 15:19:45', '2026-07-17 15:19:45'),
(47, 'Test customer', NULL, NULL, 'Bike kit', '0000000000', '000000000', NULL, NULL, NULL, 'Social Media', 'Facebook', 'hot', 1, 3, '2026-07-19 17:53:23', '2026-07-19 17:58:26'),
(48, 'Test customer', NULL, NULL, 'Bike kit', '0000000087', '0000000987', NULL, NULL, NULL, 'Website', NULL, 'hot', 0, 1, '2026-07-19 18:24:40', '2026-07-19 18:24:40'),
(49, 'Demo lead', NULL, NULL, 'CRM subscription', '6295726764', '1234567899', NULL, NULL, NULL, 'Website', NULL, 'hot', 0, 1, '2026-07-19 19:08:20', '2026-07-19 19:08:20'),
(53, '17 inch 2000w hub motor controller kit', NULL, NULL, 'hub motor', '9983554698', '9983554698', NULL, NULL, NULL, 'Other', NULL, 'hot', 0, 3, '2026-07-20 11:43:46', '2026-07-20 11:43:46'),
(59, 'hybrid scooter kit', NULL, NULL, 'scooter kit', '8630802050', '8630802050', NULL, NULL, NULL, 'Other', NULL, 'hot', 0, 3, '2026-07-20 17:29:21', '2026-07-20 17:29:21'),
(61, '18 inch hub motor kit', NULL, NULL, 'hub motor', '8909536412', '8909536412', NULL, NULL, NULL, 'Other', NULL, 'hot', 0, 4, '2026-07-21 12:01:05', '2026-07-21 12:01:05'),
(62, 'hybrid scooter kit', NULL, NULL, 'hub motor', '7718918980', '7718918980', NULL, NULL, NULL, 'Other', NULL, 'hot', 0, 4, '2026-07-21 14:28:03', '2026-07-21 14:28:03'),
(63, '350w 36v cycle kit', NULL, NULL, 'cycle kit', '8010788698', '8010788698', NULL, NULL, NULL, 'Other', NULL, 'hot', 0, 4, '2026-07-21 14:46:35', '2026-07-21 14:46:35'),
(64, 'hybrid scooter kit', NULL, NULL, 'hub motor', '7972636368', '7972636368', NULL, NULL, NULL, 'Other', NULL, 'hot', 0, 4, '2026-07-21 14:56:19', '2026-07-21 14:56:19'),
(65, '12 inch motor, 17 inch hub motor', NULL, NULL, 'hub motor', '9634008990', '9634008990', NULL, NULL, NULL, 'Other', NULL, 'hot', 0, 4, '2026-07-22 10:52:09', '2026-07-22 10:52:09'),
(66, 'hybrid scooter kit', NULL, NULL, 'scooter kit', '9412561200', '9412561200', NULL, NULL, NULL, 'Other', NULL, 'hot', 0, 4, '2026-07-22 11:16:19', '2026-07-22 11:16:19'),
(67, '10 inch hub motor', NULL, NULL, 'motor', '8168175748', '8168175748', NULL, NULL, NULL, 'Other', NULL, 'hot', 0, 4, '2026-07-22 11:33:02', '2026-07-22 11:33:02'),
(68, '350w 36v cycle kit', NULL, NULL, 'cycle kit', '9891776497', '9891776497', NULL, NULL, NULL, 'Other', NULL, 'hot', 0, 4, '2026-07-22 12:08:01', '2026-07-22 12:08:01'),
(69, '18 inch hub motor kit', NULL, NULL, 'hub motor', '8866836239', '8866836239', NULL, NULL, NULL, 'Other', NULL, 'hot', 1, 4, '2026-07-22 12:37:19', '2026-07-22 12:37:27'),
(70, '18 inch hub motor kit', NULL, NULL, 'hub motor', '9909821047', '9909821047', NULL, NULL, NULL, 'Other', NULL, 'hot', 1, 4, '2026-07-22 12:59:06', '2026-07-22 13:00:54'),
(71, '10inch hub motor', NULL, NULL, 'motor', '9901045727', '9901045727', NULL, NULL, NULL, 'Website', NULL, 'hot', 1, 4, '2026-07-22 13:44:37', '2026-07-22 15:53:47'),
(72, 'hybrid scooter kit', NULL, NULL, 'scooter kit', '8769500619', '876500619', NULL, NULL, NULL, 'Other', NULL, 'hot', 1, 4, '2026-07-22 13:50:23', '2026-07-22 18:13:17'),
(73, '10 inch hub motor', NULL, NULL, 'motor', '9358596042', '9358596042', NULL, NULL, NULL, 'Other', NULL, 'hot', 0, 4, '2026-07-22 17:42:11', '2026-07-22 17:42:11'),
(74, '10 inch hub motor', NULL, NULL, 'motor', '8780784841', '8780784841', NULL, NULL, NULL, 'Other', NULL, 'hot', 0, 4, '2026-07-23 10:17:51', '2026-07-23 10:17:51'),
(75, '10 inch hub motor', NULL, NULL, 'motor', '9834352331', '9834352331', NULL, NULL, NULL, 'Other', NULL, 'hot', 1, 4, '2026-07-23 11:37:52', '2026-07-23 11:37:55'),
(76, 'hybrid scooter kit', NULL, NULL, 'hub motor', '8770367386', '8770367386', NULL, NULL, NULL, 'Other', NULL, 'hot', 0, 4, '2026-07-23 12:06:47', '2026-07-23 12:06:47'),
(77, '12v 32ah battery', NULL, NULL, '12v 32ah battery', '9991166568', '9991166568', NULL, NULL, NULL, 'Other', NULL, 'hot', 1, 4, '2026-07-23 12:26:02', '2026-07-23 12:26:17'),
(78, '12 inch hub motor', NULL, NULL, 'hub motor', '7015512946', '7015512946', NULL, NULL, NULL, 'Other', NULL, 'hot', 1, 4, '2026-07-23 12:43:14', '2026-07-23 12:43:26');

-- --------------------------------------------------------

--
-- Table structure for table `lead_admin`
--

CREATE TABLE `lead_admin` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lead_admin`
--

INSERT INTO `lead_admin` (`id`, `lead_id`, `admin_id`, `created_at`, `updated_at`) VALUES
(11, 15, 3, '2026-07-17 14:58:11', '2026-07-17 14:58:11'),
(14, 18, 3, '2026-07-17 15:01:19', '2026-07-17 15:01:19'),
(18, 22, 3, '2026-07-17 15:06:34', '2026-07-17 15:06:34'),
(26, 30, 3, '2026-07-17 15:17:37', '2026-07-17 15:17:37'),
(28, 32, 3, '2026-07-17 15:19:45', '2026-07-17 15:19:45'),
(43, 48, 2, '2026-07-19 18:24:40', '2026-07-19 18:24:40'),
(44, 49, 2, '2026-07-19 19:08:20', '2026-07-19 19:08:20'),
(48, 53, 4, '2026-07-20 11:43:46', '2026-07-20 11:43:46'),
(54, 59, 4, '2026-07-20 17:29:21', '2026-07-20 17:29:21'),
(56, 61, 4, '2026-07-21 12:01:05', '2026-07-21 12:01:05'),
(57, 62, 4, '2026-07-21 14:28:03', '2026-07-21 14:28:03'),
(58, 63, 4, '2026-07-21 14:46:35', '2026-07-21 14:46:35'),
(59, 64, 4, '2026-07-21 14:56:19', '2026-07-21 14:56:19'),
(60, 65, 4, '2026-07-22 10:52:09', '2026-07-22 10:52:09'),
(61, 66, 4, '2026-07-22 11:16:19', '2026-07-22 11:16:19'),
(62, 67, 4, '2026-07-22 11:33:02', '2026-07-22 11:33:02'),
(63, 68, 4, '2026-07-22 12:08:01', '2026-07-22 12:08:01'),
(64, 69, 4, '2026-07-22 12:37:19', '2026-07-22 12:37:19'),
(65, 70, 4, '2026-07-22 12:59:06', '2026-07-22 12:59:06'),
(66, 71, 4, '2026-07-22 13:44:37', '2026-07-22 13:44:37'),
(67, 72, 4, '2026-07-22 13:50:23', '2026-07-22 13:50:23'),
(68, 73, 4, '2026-07-22 17:42:11', '2026-07-22 17:42:11'),
(69, 74, 4, '2026-07-23 10:17:51', '2026-07-23 10:17:51'),
(70, 75, 4, '2026-07-23 11:37:52', '2026-07-23 11:37:52'),
(71, 76, 4, '2026-07-23 12:06:47', '2026-07-23 12:06:47'),
(72, 77, 4, '2026-07-23 12:26:02', '2026-07-23 12:26:02'),
(73, 78, 4, '2026-07-23 12:43:14', '2026-07-23 12:43:14');

-- --------------------------------------------------------

--
-- Table structure for table `lead_attachments`
--

CREATE TABLE `lead_attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED DEFAULT NULL,
  `uploaded_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lead_attachments`
--

INSERT INTO `lead_attachments` (`id`, `lead_id`, `file_name`, `file_path`, `mime_type`, `file_size`, `uploaded_by`, `created_at`, `updated_at`) VALUES
(11, 47, 'adhar card_1.pdf', 'lead-attachments/h20DDidWK0UA9ilinkTVTbS591a2Aaie46bTS819.pdf', 'application/pdf', 199831, 3, '2026-07-19 17:55:39', '2026-07-19 17:55:39');

-- --------------------------------------------------------

--
-- Table structure for table `lead_reminders`
--

CREATE TABLE `lead_reminders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `note` text NOT NULL,
  `reminder_date` date DEFAULT NULL,
  `reminder_time` time DEFAULT NULL,
  `appointment_type` enum('whatsapp','meeting') DEFAULT NULL,
  `status` enum('scheduled','missed','silent','completed') NOT NULL DEFAULT 'scheduled',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lead_reminders`
--

INSERT INTO `lead_reminders` (`id`, `lead_id`, `note`, `reminder_date`, `reminder_time`, `appointment_type`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(15, 47, 'TEST REMINDER', '2026-07-19', '17:57:00', NULL, 'silent', 3, '2026-07-19 17:56:59', '2026-07-19 17:57:07'),
(16, 49, 'Remindeer', '2026-07-19', '19:09:00', NULL, 'silent', 1, '2026-07-19 19:08:43', '2026-07-19 19:09:05'),
(17, 47, 'aaj baat karni hai', '2026-07-19', '19:49:00', NULL, 'scheduled', 4, '2026-07-19 19:47:13', '2026-07-19 19:47:13');

-- --------------------------------------------------------

--
-- Table structure for table `meta_leads`
--

CREATE TABLE `meta_leads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `leadgen_id` varchar(255) NOT NULL,
  `page_id` varchar(255) DEFAULT NULL,
  `form_id` varchar(255) DEFAULT NULL,
  `form_name` varchar(255) DEFAULT NULL,
  `ad_id` varchar(255) DEFAULT NULL,
  `ad_name` varchar(255) DEFAULT NULL,
  `adset_id` varchar(255) DEFAULT NULL,
  `adset_name` varchar(255) DEFAULT NULL,
  `campaign_id` varchar(255) DEFAULT NULL,
  `campaign_name` varchar(255) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `whatsapp` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `raw_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_payload`)),
  `status` enum('new','converted','discarded') NOT NULL DEFAULT 'new',
  `converted_lead_id` bigint(20) UNSIGNED DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_07_02_121148_myapp', 1),
(5, '2026_07_02_121808_create_admin_table', 1),
(6, '2026_07_02_131611_add_remember_token_to_admins_table', 1),
(7, '2026_07_03_055451_create_leads_table', 1),
(8, '2026_07_03_055615_create_lead_admin_table', 1),
(9, '2026_07_03_055713_create_lead_reminders_table', 1),
(10, '2026_07_03_061900_update_status_enum_in_lead_reminders_table', 1),
(11, '2026_07_03_141824_create_inventory_management_table', 1),
(12, '2026_07_03_143157_add_image_to_products_table', 1),
(13, '2026_07_03_161430_create_banks_table', 1),
(14, '2026_07_03_161524_create_billing_headers_table', 1),
(15, '2026_07_03_161739_add_billing_fields_to_leads_table', 1),
(16, '2026_07_03_161942_add_hsn_to_products_table', 1),
(17, '2026_07_03_162044_create_bills_table', 1),
(18, '2026_07_03_165325_create_bill_items_table', 1),
(19, '2026_07_03_165540_add_bill_item_id_to_inventory_items_table', 1),
(20, '2026_07_03_183220_create_meta_leads_table', 1),
(21, '2026_07_03_212847_create_product_image_table', 1),
(22, '2026_07_04_082824_add_type_to_inventory_item_table', 1),
(23, '2026_07_04_101420_create_kb_categories_table', 1),
(24, '2026_07_04_101514_create_kb_tags_table', 1),
(25, '2026_07_04_101608_create_kb_articles_table', 1),
(26, '2026_07_04_101702_create_kb_article_tag_table', 1),
(27, '2026_07_04_101808_create_kb_article_versions_table', 1),
(28, '2026_07_04_101858_create_kb_media_table', 1),
(29, '2026_07_04_110600_create_tasks_table', 1),
(30, '2026_07_04_110700_create_task_activity_logs_table', 1),
(31, '2026_07_04_110732_create_task_attachments_table', 1),
(32, '2026_07_04_110806_create_task_comments_table', 1),
(33, '2026_07_04_110917_create_notifications_table', 1),
(34, '2026_07_04_110918_create_notifications_table', 2),
(35, '2026_07_04_170814_create_embedding_to_kb_articles_table', 3),
(36, '2026_07_04_171002_create_chatbot_settings_table', 3),
(37, '2026_07_04_171046_create_chatbot_settings_table', 3),
(38, '2026_07_04_171149_create_chat_messages_table', 3),
(39, '2026_07_04_171237_create_chat_notes_table', 3),
(40, '2026_07_08_123901_create_lead_attachments_table', 4),
(41, '2026_07_10_163128_create_chat_intents_table', 5),
(42, '2026_07_10_164745_create_chatbot_intents_table', 6),
(43, '2026_07_10_173331_add_conversation_state_to_chat_conversations', 7),
(44, '2026_07_10_203335_add_media_details_to_chat_messages_table', 8),
(45, '2026_07_10_224914_add_product_kb_fields', 9),
(46, '2026_07_11_142822_add_hybrid_search_fields_to_kb_articles_table', 10),
(47, '2026_07_11_143141_add_memory_feild_to_chat_conversations_table', 10),
(48, '2026_07_11_143428_create_chat_query_logs_table', 10),
(49, '2026_07_11_150734_rename_tags_to_search_tags_in_kb_articles', 11);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('1938bced-3cea-4599-bfef-16ea6ad0da9f', 'App\\Notifications\\TaskStatusUpdatedNotification', 'App\\Models\\Admin', 1, '{\"type\":\"task_status_updated\",\"task_id\":6,\"task_name\":\"Ye main important hai\",\"status\":\"in_progress\",\"actor\":\"Just Admin\",\"message\":\"Just Admin marked \\\"Ye main important hai\\\" as In Progress\"}', '2026-07-04 07:16:56', '2026-07-04 07:10:27', '2026-07-04 07:16:56'),
('22b6a220-30bd-447f-a7c6-c9c6dd65b048', 'App\\Notifications\\TaskStatusUpdatedNotification', 'App\\Models\\Admin', 3, '{\"type\":\"task_status_updated\",\"task_id\":8,\"task_name\":\"LAKAHAN SE BAAT KARNI AI\",\"status\":\"in_progress\",\"actor\":\"Balvindar Singh\",\"message\":\"Balvindar Singh marked \\\"LAKAHAN SE BAAT KARNI AI\\\" as In Progress\"}', NULL, '2026-07-19 19:46:21', '2026-07-19 19:46:21'),
('2c742d50-8d15-43c3-8bed-bc25d3dd942d', 'App\\Notifications\\TaskStatusUpdatedNotification', 'App\\Models\\Admin', 3, '{\"type\":\"task_status_updated\",\"task_id\":8,\"task_name\":\"LAKAHAN SE BAAT KARNI AI\",\"status\":\"completed\",\"actor\":\"Balvindar Singh\",\"message\":\"Balvindar Singh marked \\\"LAKAHAN SE BAAT KARNI AI\\\" as Completed\"}', NULL, '2026-07-19 19:46:07', '2026-07-19 19:46:07'),
('3f20eb5a-89c9-4eb4-acae-16236cd19c65', 'App\\Notifications\\TaskAssignedNotification', 'App\\Models\\Admin', 1, '{\"type\":\"task_assigned\",\"task_id\":1,\"task_name\":\"Ye task karna hai\",\"priority\":\"medium\",\"due_date\":\"2026-07-16\",\"assigned_by\":\"Amit Super Admin\",\"message\":\"You\'ve been assigned a new task: \\\"Ye task karna hai\\\"\"}', '2026-07-04 06:32:05', '2026-07-04 06:30:16', '2026-07-04 06:32:05'),
('5ccbe390-4186-4ea6-806f-6fbd5eb6669a', 'App\\Notifications\\TaskStatusUpdatedNotification', 'App\\Models\\Admin', 3, '{\"type\":\"task_status_updated\",\"task_id\":8,\"task_name\":\"LAKAHAN SE BAAT KARNI AI\",\"status\":\"in_progress\",\"actor\":\"Balvindar Singh\",\"message\":\"Balvindar Singh marked \\\"LAKAHAN SE BAAT KARNI AI\\\" as In Progress\"}', NULL, '2026-07-19 19:45:55', '2026-07-19 19:45:55'),
('5fc134da-1bea-4ab6-845a-68ed8a2edcf8', 'App\\Notifications\\TaskStatusUpdatedNotification', 'App\\Models\\Admin', 1, '{\"type\":\"task_status_updated\",\"task_id\":6,\"task_name\":\"Ye main important hai\",\"status\":\"completed\",\"actor\":\"Just Admin\",\"message\":\"Just Admin marked \\\"Ye main important hai\\\" as Completed\"}', '2026-07-04 07:16:55', '2026-07-04 07:11:01', '2026-07-04 07:16:55'),
('704651ef-22be-4955-afa6-87a5897ecd31', 'App\\Notifications\\TaskAssignedNotification', 'App\\Models\\Admin', 3, '{\"type\":\"task_assigned\",\"task_id\":8,\"task_name\":\"LAKAHAN SE BAAT KARNI AI\",\"priority\":\"urgent\",\"due_date\":\"2026-07-21\",\"assigned_by\":\"Balvindar Singh\",\"message\":\"You\'ve been assigned a new task: \\\"LAKAHAN SE BAAT KARNI AI\\\"\"}', NULL, '2026-07-19 19:44:52', '2026-07-19 19:44:52'),
('72ce1530-07ff-44c3-bbd3-2271621eb38b', 'App\\Notifications\\TaskAssignedNotification', 'App\\Models\\Admin', 2, '{\"type\":\"task_assigned\",\"task_id\":6,\"task_name\":\"Ye main important hai\",\"priority\":\"medium\",\"due_date\":\"2026-07-31\",\"assigned_by\":\"Amit Super Admin\",\"message\":\"You\'ve been assigned a new task: \\\"Ye main important hai\\\"\"}', '2026-07-04 07:09:59', '2026-07-04 07:09:02', '2026-07-04 07:09:59'),
('78556e8b-02b4-4e99-9bb0-9404a242449b', 'App\\Notifications\\TaskStatusUpdatedNotification', 'App\\Models\\Admin', 1, '{\"type\":\"task_status_updated\",\"task_id\":6,\"task_name\":\"Ye main important hai\",\"status\":\"reverted\",\"actor\":\"Just Admin\",\"message\":\"Just Admin marked \\\"Ye main important hai\\\" as Reverted\"}', '2026-07-04 07:17:00', '2026-07-04 07:10:49', '2026-07-04 07:17:00'),
('c7ee0dd9-ef3d-4333-ae0d-55410770b12b', 'App\\Notifications\\TaskStatusUpdatedNotification', 'App\\Models\\Admin', 3, '{\"type\":\"task_status_updated\",\"task_id\":8,\"task_name\":\"LAKAHAN SE BAAT KARNI AI\",\"status\":\"pending\",\"actor\":\"Balvindar Singh\",\"message\":\"Balvindar Singh marked \\\"LAKAHAN SE BAAT KARNI AI\\\" as Pending\"}', NULL, '2026-07-19 19:46:09', '2026-07-19 19:46:09'),
('ca4a37c4-15ec-4538-974f-e6d0db7d0ca9', 'App\\Notifications\\TaskCommentedNotification', 'App\\Models\\Admin', 3, '{\"type\":\"task_commented\",\"task_id\":8,\"task_name\":\"LAKAHAN SE BAAT KARNI AI\",\"actor\":\"Balvindar Singh\",\"comment\":\"BHAI HUA YA NAHI\",\"message\":\"Balvindar Singh commented on \\\"LAKAHAN SE BAAT KARNI AI\\\"\"}', NULL, '2026-07-19 19:45:32', '2026-07-19 19:45:32');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(255) NOT NULL,
  `hsn` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `unit` varchar(255) NOT NULL DEFAULT 'pcs',
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `sku`, `hsn`, `model`, `unit`, `description`, `image`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Lithium ion battries', '2342342', NULL, NULL, 'pcs', NULL, NULL, 1, '2026-07-04 07:33:50', '2026-07-04 07:33:50'),
(2, 'Octa fx 2', '13213', NULL, NULL, 'pcs', 'eqwe', NULL, 1, '2026-07-04 07:36:29', '2026-07-04 07:36:29'),
(3, 'Lithium ion battries ISO', '823482740827', NULL, NULL, 'set', 'This this prduct', NULL, 1, '2026-07-09 11:34:42', '2026-07-09 11:38:03'),
(4, 'Hybrid scooter Kit', 'HSK001', NULL, NULL, 'pcs', '1500w Motor,Controller ,Thum Throttle with key and volt display,converter,', NULL, 1, '2026-07-11 22:40:46', '2026-07-11 22:40:46'),
(5, 'Ev Spare Part', 'HSK002', NULL, NULL, 'pcs', NULL, NULL, 1, '2026-07-11 22:45:12', '2026-07-11 22:45:12');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'products/ICBiLPbtvk0ubaOWp0NxLN0jRwWggINjmbb89lAi.jpg', 1, '2026-07-04 07:33:51', '2026-07-04 07:33:51'),
(2, 2, 'products/shy3gjJA31MtTl9UDl4sn64wxXiL1sr0ehfXCXz7.png', 1, '2026-07-04 07:36:29', '2026-07-04 07:36:29'),
(3, 3, 'products/ABxMKe9vuQ2fQN6U79M8IoaO5G5Qu4PEiHeNT8v3.jpg', 1, '2026-07-09 11:34:42', '2026-07-09 11:34:42'),
(4, 3, 'products/PIeCs54WsP4POhUX7s58oFtWvSJY2gOV0t8YSIWw.jpg', 2, '2026-07-09 11:34:42', '2026-07-09 11:34:42'),
(5, 3, 'products/hiPA1Yn4AP1rh3BxMiXzzQAMJgVEQyoViXWBn9r0.png', 3, '2026-07-09 11:34:42', '2026-07-09 11:34:42'),
(6, 4, 'products/Cl1eZCkt8Bds6RG2gjsmkvheDdxnWsKDa5pwroE9.jpg', 1, '2026-07-11 22:40:46', '2026-07-11 22:40:46');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('e6KrDZks1Rd5K27TfBXuKbFvV9BYdT5iIiEqQO57', 4, '27.100.14.80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiS0xBUHhDcldvTFo2YWZMeHQ0cVUzaU1KSldlbmNGdGxHdlQ5Y2psayI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vY3JtLmpvam9ldi5pbi9hZG1pbi9sZWFkcyI7czo1OiJyb3V0ZSI7czoxMToibGVhZHMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUyOiJsb2dpbl9hZG1pbl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ7fQ==', 1784795270),
('qQq8mNlJs0H65c1vnUnjR28LjUIij2tEYNblHHag', 4, '27.100.14.80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicU9ua1FPYWFocEJvZ3AyRnpRMFBSVDk0MFJtMlhKQ2tKU2l3STNPZCI7czo1MjoibG9naW5fYWRtaW5fNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo0O3M6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1784792143);

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `task_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `due_date` date DEFAULT NULL,
  `status` enum('pending','in_progress','completed','reverted','on_hold') NOT NULL DEFAULT 'pending',
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `task_name`, `description`, `priority`, `due_date`, `status`, `assigned_to`, `assigned_by`, `completed_at`, `created_at`, `updated_at`) VALUES
(8, 'LAKAHAN SE BAAT KARNI AI', 'THIS IS DESCRIPTION', 'urgent', '2026-07-21', 'in_progress', 3, 4, NULL, '2026-07-19 19:44:52', '2026-07-19 19:46:21');

-- --------------------------------------------------------

--
-- Table structure for table `task_activity_logs`
--

CREATE TABLE `task_activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `task_id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_activity_logs`
--

INSERT INTO `task_activity_logs` (`id`, `task_id`, `admin_id`, `action`, `description`, `created_at`, `updated_at`) VALUES
(28, 8, 4, 'created', 'Balvindar Singh created this task.', '2026-07-19 19:44:52', '2026-07-19 19:44:52'),
(29, 8, 4, 'assigned', 'Balvindar Singh assigned this task to Sunita.', '2026-07-19 19:44:52', '2026-07-19 19:44:52'),
(30, 8, 4, 'commented', 'Balvindar Singh added a comment.', '2026-07-19 19:45:32', '2026-07-19 19:45:32'),
(31, 8, 4, 'status_changed', 'Balvindar Singh changed the status to In Progress.', '2026-07-19 19:45:55', '2026-07-19 19:45:55'),
(32, 8, 4, 'status_changed', 'Balvindar Singh changed the status to Completed.', '2026-07-19 19:46:07', '2026-07-19 19:46:07'),
(33, 8, 4, 'status_changed', 'Balvindar Singh changed the status to Pending.', '2026-07-19 19:46:09', '2026-07-19 19:46:09'),
(34, 8, 4, 'status_changed', 'Balvindar Singh changed the status to In Progress.', '2026-07-19 19:46:21', '2026-07-19 19:46:21');

-- --------------------------------------------------------

--
-- Table structure for table `task_attachments`
--

CREATE TABLE `task_attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `task_id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `mime_type` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_comments`
--

CREATE TABLE `task_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `task_id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_comments`
--

INSERT INTO `task_comments` (`id`, `task_id`, `admin_id`, `comment`, `created_at`, `updated_at`) VALUES
(4, 8, 4, 'BHAI HUA YA NAHI', '2026-07-19 19:45:32', '2026-07-19 19:45:32');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admins_email_unique` (`email`);

--
-- Indexes for table `banks`
--
ALTER TABLE `banks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `banks_created_by_foreign` (`created_by`);

--
-- Indexes for table `billing_headers`
--
ALTER TABLE `billing_headers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `billing_headers_created_by_foreign` (`created_by`);

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bills_bill_number_unique` (`bill_number`),
  ADD KEY `bills_lead_id_foreign` (`lead_id`),
  ADD KEY `bills_billing_header_id_foreign` (`billing_header_id`),
  ADD KEY `bills_bank_id_foreign` (`bank_id`),
  ADD KEY `bills_created_by_foreign` (`created_by`);

--
-- Indexes for table `bill_items`
--
ALTER TABLE `bill_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bill_items_bill_id_foreign` (`bill_id`),
  ADD KEY `bill_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `chatbot_intents`
--
ALTER TABLE `chatbot_intents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chatbot_settings`
--
ALTER TABLE `chatbot_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chat_conversations_wa_phone_number_unique` (`wa_phone_number`),
  ADD KEY `chat_conversations_handling_admin_id_foreign` (`handling_admin_id`),
  ADD KEY `chat_conversations_locked_by_admin_id_foreign` (`locked_by_admin_id`),
  ADD KEY `chat_conversations_lead_id_foreign` (`lead_id`),
  ADD KEY `chat_conversations_ai_active_escalated_at_index` (`ai_active`,`escalated_at`),
  ADD KEY `chat_conversations_assigned_admin_id_index` (`assigned_admin_id`),
  ADD KEY `chat_conversations_focus_product_id_foreign` (`focus_product_id`);

--
-- Indexes for table `chat_intents`
--
ALTER TABLE `chat_intents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chat_intents_intent_unique` (`intent`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_messages_conversation_id_foreign` (`conversation_id`),
  ADD KEY `chat_messages_sender_admin_id_foreign` (`sender_admin_id`),
  ADD KEY `chat_messages_wa_message_id_index` (`wa_message_id`);

--
-- Indexes for table `chat_notes`
--
ALTER TABLE `chat_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_notes_conversation_id_foreign` (`conversation_id`),
  ADD KEY `chat_notes_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `chat_query_logs`
--
ALTER TABLE `chat_query_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_query_logs_conversation_id_foreign` (`conversation_id`),
  ADD KEY `chat_query_logs_created_at_index` (`created_at`),
  ADD KEY `chat_query_logs_escalated_index` (`escalated`),
  ADD KEY `chat_query_logs_intent_index` (`intent`),
  ADD KEY `chat_query_logs_confidence_tier_index` (`confidence_tier`);

--
-- Indexes for table `dealers`
--
ALTER TABLE `dealers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dealers_created_by_foreign` (`created_by`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_items_product_id_foreign` (`product_id`),
  ADD KEY `inventory_items_dealer_id_foreign` (`dealer_id`),
  ADD KEY `inventory_items_created_by_foreign` (`created_by`),
  ADD KEY `inventory_items_bill_item_id_foreign` (`bill_item_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kb_articles`
--
ALTER TABLE `kb_articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kb_articles_category_id_foreign` (`category_id`),
  ADD KEY `kb_articles_product_id_foreign` (`product_id`),
  ADD KEY `kb_articles_created_by_foreign` (`created_by`),
  ADD KEY `kb_articles_approved_by_foreign` (`approved_by`),
  ADD KEY `kb_articles_type_status_index` (`type`,`status`),
  ADD KEY `kb_articles_status_ai_ready_index` (`status`,`ai_ready`);

--
-- Indexes for table `kb_article_tag`
--
ALTER TABLE `kb_article_tag`
  ADD PRIMARY KEY (`kb_article_id`,`kb_tag_id`),
  ADD KEY `kb_article_tag_kb_tag_id_foreign` (`kb_tag_id`);

--
-- Indexes for table `kb_article_versions`
--
ALTER TABLE `kb_article_versions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kb_article_versions_article_id_foreign` (`article_id`),
  ADD KEY `kb_article_versions_edited_by_foreign` (`edited_by`);

--
-- Indexes for table `kb_categories`
--
ALTER TABLE `kb_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kb_categories_slug_unique` (`slug`),
  ADD KEY `kb_categories_parent_id_foreign` (`parent_id`);

--
-- Indexes for table `kb_media`
--
ALTER TABLE `kb_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kb_media_article_id_foreign` (`article_id`),
  ADD KEY `kb_media_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `kb_tags`
--
ALTER TABLE `kb_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kb_tags_slug_unique` (`slug`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leads_created_by_foreign` (`created_by`),
  ADD KEY `leads_lead_type_source_index` (`lead_type`,`source`),
  ADD KEY `leads_is_pinned_index` (`is_pinned`);

--
-- Indexes for table `lead_admin`
--
ALTER TABLE `lead_admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lead_admin_lead_id_admin_id_unique` (`lead_id`,`admin_id`),
  ADD KEY `lead_admin_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `lead_attachments`
--
ALTER TABLE `lead_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_attachments_lead_id_foreign` (`lead_id`),
  ADD KEY `lead_attachments_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `lead_reminders`
--
ALTER TABLE `lead_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_reminders_lead_id_foreign` (`lead_id`),
  ADD KEY `lead_reminders_created_by_foreign` (`created_by`),
  ADD KEY `lead_reminders_reminder_date_reminder_time_status_index` (`reminder_date`,`reminder_time`,`status`);

--
-- Indexes for table `meta_leads`
--
ALTER TABLE `meta_leads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `meta_leads_leadgen_id_unique` (`leadgen_id`),
  ADD KEY `meta_leads_converted_lead_id_foreign` (`converted_lead_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_sku_unique` (`sku`),
  ADD KEY `products_created_by_foreign` (`created_by`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_images_product_id_foreign` (`product_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tasks_assigned_by_foreign` (`assigned_by`),
  ADD KEY `tasks_assigned_to_status_index` (`assigned_to`,`status`),
  ADD KEY `tasks_status_due_date_index` (`status`,`due_date`);

--
-- Indexes for table `task_activity_logs`
--
ALTER TABLE `task_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_activity_logs_task_id_foreign` (`task_id`),
  ADD KEY `task_activity_logs_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `task_attachments`
--
ALTER TABLE `task_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_attachments_task_id_foreign` (`task_id`),
  ADD KEY `task_attachments_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `task_comments`
--
ALTER TABLE `task_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_comments_task_id_foreign` (`task_id`),
  ADD KEY `task_comments_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `banks`
--
ALTER TABLE `banks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `billing_headers`
--
ALTER TABLE `billing_headers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `bill_items`
--
ALTER TABLE `bill_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `chatbot_intents`
--
ALTER TABLE `chatbot_intents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `chatbot_settings`
--
ALTER TABLE `chatbot_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `chat_intents`
--
ALTER TABLE `chat_intents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=405;

--
-- AUTO_INCREMENT for table `chat_notes`
--
ALTER TABLE `chat_notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `chat_query_logs`
--
ALTER TABLE `chat_query_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `dealers`
--
ALTER TABLE `dealers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `kb_articles`
--
ALTER TABLE `kb_articles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `kb_article_versions`
--
ALTER TABLE `kb_article_versions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `kb_categories`
--
ALTER TABLE `kb_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kb_media`
--
ALTER TABLE `kb_media`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `kb_tags`
--
ALTER TABLE `kb_tags`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `lead_admin`
--
ALTER TABLE `lead_admin`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `lead_attachments`
--
ALTER TABLE `lead_attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `lead_reminders`
--
ALTER TABLE `lead_reminders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `meta_leads`
--
ALTER TABLE `meta_leads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `task_activity_logs`
--
ALTER TABLE `task_activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `task_attachments`
--
ALTER TABLE `task_attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `task_comments`
--
ALTER TABLE `task_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `banks`
--
ALTER TABLE `banks`
  ADD CONSTRAINT `banks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `billing_headers`
--
ALTER TABLE `billing_headers`
  ADD CONSTRAINT `billing_headers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bills`
--
ALTER TABLE `bills`
  ADD CONSTRAINT `bills_bank_id_foreign` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bills_billing_header_id_foreign` FOREIGN KEY (`billing_header_id`) REFERENCES `billing_headers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bills_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bills_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bill_items`
--
ALTER TABLE `bill_items`
  ADD CONSTRAINT `bill_items_bill_id_foreign` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bill_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD CONSTRAINT `chat_conversations_assigned_admin_id_foreign` FOREIGN KEY (`assigned_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `chat_conversations_focus_product_id_foreign` FOREIGN KEY (`focus_product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `chat_conversations_handling_admin_id_foreign` FOREIGN KEY (`handling_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `chat_conversations_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `chat_conversations_locked_by_admin_id_foreign` FOREIGN KEY (`locked_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_messages_sender_admin_id_foreign` FOREIGN KEY (`sender_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `chat_notes`
--
ALTER TABLE `chat_notes`
  ADD CONSTRAINT `chat_notes_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_notes_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_query_logs`
--
ALTER TABLE `chat_query_logs`
  ADD CONSTRAINT `chat_query_logs_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `dealers`
--
ALTER TABLE `dealers`
  ADD CONSTRAINT `dealers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD CONSTRAINT `inventory_items_bill_item_id_foreign` FOREIGN KEY (`bill_item_id`) REFERENCES `bill_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_items_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_items_dealer_id_foreign` FOREIGN KEY (`dealer_id`) REFERENCES `dealers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kb_articles`
--
ALTER TABLE `kb_articles`
  ADD CONSTRAINT `kb_articles_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `kb_articles_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `kb_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `kb_articles_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `kb_articles_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `kb_article_tag`
--
ALTER TABLE `kb_article_tag`
  ADD CONSTRAINT `kb_article_tag_kb_article_id_foreign` FOREIGN KEY (`kb_article_id`) REFERENCES `kb_articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kb_article_tag_kb_tag_id_foreign` FOREIGN KEY (`kb_tag_id`) REFERENCES `kb_tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kb_article_versions`
--
ALTER TABLE `kb_article_versions`
  ADD CONSTRAINT `kb_article_versions_article_id_foreign` FOREIGN KEY (`article_id`) REFERENCES `kb_articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kb_article_versions_edited_by_foreign` FOREIGN KEY (`edited_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `kb_categories`
--
ALTER TABLE `kb_categories`
  ADD CONSTRAINT `kb_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `kb_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `kb_media`
--
ALTER TABLE `kb_media`
  ADD CONSTRAINT `kb_media_article_id_foreign` FOREIGN KEY (`article_id`) REFERENCES `kb_articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kb_media_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `leads_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lead_admin`
--
ALTER TABLE `lead_admin`
  ADD CONSTRAINT `lead_admin_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lead_admin_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lead_attachments`
--
ALTER TABLE `lead_attachments`
  ADD CONSTRAINT `lead_attachments_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lead_attachments_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lead_reminders`
--
ALTER TABLE `lead_reminders`
  ADD CONSTRAINT `lead_reminders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lead_reminders_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `meta_leads`
--
ALTER TABLE `meta_leads`
  ADD CONSTRAINT `meta_leads_converted_lead_id_foreign` FOREIGN KEY (`converted_lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tasks_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `task_activity_logs`
--
ALTER TABLE `task_activity_logs`
  ADD CONSTRAINT `task_activity_logs_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `task_activity_logs_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_attachments`
--
ALTER TABLE `task_attachments`
  ADD CONSTRAINT `task_attachments_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `task_attachments_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_comments`
--
ALTER TABLE `task_comments`
  ADD CONSTRAINT `task_comments_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `task_comments_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

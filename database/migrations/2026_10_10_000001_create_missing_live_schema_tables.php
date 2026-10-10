<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * These 31 tables exist in the live/dev database (imported from a SQL
 * dump) but had no migration at all - a fresh migrate could never
 * reproduce the real schema, and the test suite's RefreshDatabase
 * couldn't create them either. Each CREATE TABLE below is copied
 * verbatim from `SHOW CREATE TABLE` against the live dev database, not
 * hand-translated, so the schema this produces is byte-for-byte what
 * already exists in production/dev. Schema::hasTable() guards make this
 * a no-op against a database (like the real dev one) that already has
 * these tables from the original import.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('activities')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `activity_type_id` bigint(20) unsigned DEFAULT NULL,
  `number` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `mission` text NOT NULL,
  `special` text DEFAULT NULL,
  `shop` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `min_zeds` decimal(10,2) NOT NULL,
  `max_zeds` decimal(10,2) NOT NULL,
  `min_items` int(11) DEFAULT NULL,
  `max_items` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `activity_type_id` (`activity_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('activitiestype')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `activitiestype` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('beneficiaries')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `beneficiaries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `sid` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `bank` varchar(255) NOT NULL,
  `account_number` varchar(255) NOT NULL,
  `sort_code` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `beneficiaries_user_id_foreign` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('city_mall_items')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `city_mall_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `store_name` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `bank_statement_tag` varchar(255) DEFAULT NULL,
  `item_type` enum('needs_fixed','needs_variable','wants') DEFAULT 'wants',
  `due_date` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('direct_deposite')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `direct_deposite` (
  `id` bigint(20) unsigned NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `sid` varchar(50) DEFAULT NULL,
  `empname` varchar(191) NOT NULL,
  `empphone` varchar(20) DEFAULT NULL,
  `empemail` varchar(191) DEFAULT NULL,
  `empid` varchar(100) DEFAULT NULL,
  `bankname` varchar(191) DEFAULT NULL,
  `bankaccountiban` varchar(100) DEFAULT NULL,
  `banknumber` varchar(191) DEFAULT NULL,
  `bankdeposite` tinyint(1) DEFAULT 0,
  `empsignature` varchar(191) DEFAULT NULL,
  `empdate` date DEFAULT NULL,
  `empreceived` varchar(191) DEFAULT NULL,
  `empreceiveddate` date DEFAULT NULL,
  `empeffectivedate` date DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `students_student_id_unique` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('donations')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `donations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `npo_name` varchar(255) NOT NULL,
  `account_no` varchar(100) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `transaction_ref` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('durable_goods_catalog')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `durable_goods_catalog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `category` varchar(32) NOT NULL COMMENT 'tech | transport | home | clothing | tools | other',
  `icon` varchar(8) NOT NULL COMMENT 'emoji character',
  `price_zeds` int(11) NOT NULL,
  `lifespan_years` tinyint(3) unsigned NOT NULL CHECK (`lifespan_years` >= 3),
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_catalog_category` (`category`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('email_templates')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `email_templates` (
  `id` int(10) unsigned NOT NULL,
  `sid` varchar(50) DEFAULT NULL,
  `store_name` varchar(255) NOT NULL,
  `subTitle` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL
            );
        }

        if (!Schema::hasTable('finhero_activity_registry')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `finhero_activity_registry` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sid` int(11) DEFAULT NULL,
  `cid` int(11) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `activity_key` varchar(255) DEFAULT NULL,
  `activity_name` varchar(255) NOT NULL,
  `max_points` tinyint(4) DEFAULT 1,
  `position` enum('required','optional','free') DEFAULT 'optional',
  `is_active` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `salary` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_activity_type` (`activity_key`,`type`,`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('finhero_badge_records')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `finhero_badge_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `month` tinyint(4) NOT NULL,
  `year` smallint(6) NOT NULL,
  `academic_year` varchar(9) NOT NULL,
  `quiz_pts` tinyint(4) DEFAULT 0,
  `library_pts` tinyint(4) DEFAULT 0,
  `activity_pts` tinyint(4) DEFAULT 0,
  `total_earned` tinyint(4) DEFAULT 0,
  `total_available` tinyint(4) DEFAULT 0,
  `monthly_pct` decimal(5,2) DEFAULT 0.00,
  `monthly_badge` enum('LEGEND','CHAMPION','FINHERO','ROOKIE','NONE') DEFAULT 'NONE',
  `monthly_badge_points` tinyint(4) DEFAULT 0,
  `accumulated_points` tinyint(4) DEFAULT 0,
  `accumulated_badge` enum('LEGEND','CHAMPION','FINHERO','ROOKIE','NONE') DEFAULT 'NONE',
  `is_overridden` tinyint(1) DEFAULT 0,
  `override_badge` enum('LEGEND','CHAMPION','FINHERO','ROOKIE','NONE') DEFAULT NULL,
  `overridden_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_month_year` (`student_id`,`month`,`year`),
  KEY `idx_academic_year` (`academic_year`),
  KEY `idx_student_academic_year` (`student_id`,`academic_year`),
  CONSTRAINT `fk_badge_records_user` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('finhero_monthly_settings')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `finhero_monthly_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) unsigned DEFAULT NULL,
  `month` tinyint(4) NOT NULL,
  `year` smallint(6) NOT NULL,
  `academic_year` varchar(9) NOT NULL,
  `active_library_module_id` bigint(20) unsigned DEFAULT NULL,
  `badge_active` tinyint(1) DEFAULT 1,
  `threshold_legend` tinyint(4) DEFAULT 90,
  `threshold_champion` tinyint(4) DEFAULT 70,
  `threshold_finhero` tinyint(4) DEFAULT 50,
  `threshold_rookie` tinyint(4) DEFAULT 25,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_school_month_year` (`school_id`,`month`,`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('finhero_student_points')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `finhero_student_points` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `month` tinyint(4) NOT NULL,
  `year` smallint(6) NOT NULL,
  `academic_year` varchar(9) NOT NULL,
  `source_type` enum('quiz','library','activity','task') NOT NULL,
  `source_key` varchar(255) NOT NULL,
  `points_earned` tinyint(4) DEFAULT 1,
  `earned_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_student_month_year` (`student_id`,`month`,`year`),
  KEY `idx_source` (`source_type`,`source_key`),
  CONSTRAINT `fk_student_points_user` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('groceriesomnivore')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `groceriesomnivore` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `category` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('groceriespecastarian')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `groceriespecastarian` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `category` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('groceriesvegan')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `groceriesvegan` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `category` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('groceriesvegetarian')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `groceriesvegetarian` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `category` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('grocery_costs')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `grocery_costs` (
  `diet_type` varchar(50) NOT NULL,
  `sid` varchar(50) DEFAULT NULL,
  `monthly_cost` decimal(10,2) NOT NULL,
  `bank_statement_tag` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  UNIQUE KEY `diet_type` (`diet_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('mba_position')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `mba_position` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `activity_key` varchar(255) DEFAULT NULL,
  `sid` varchar(50) DEFAULT NULL,
  `cid` int(11) DEFAULT NULL,
  `required` tinyint(1) DEFAULT 0,
  `optional` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('npos')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `npos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(100) NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(160) NOT NULL,
  `content` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `bank_name` varchar(150) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('orders')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `sid` varchar(50) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'paid',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `orders_user_id_index` (`user_id`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('order_items')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `sid` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `qty` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_index` (`order_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('pay_bills')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `pay_bills` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `sid` varchar(50) DEFAULT NULL,
  `account_id` varchar(191) NOT NULL,
  `biller_id` varchar(191) NOT NULL,
  `account_number` varchar(191) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_type` enum('now','schedule','recurring') NOT NULL,
  `schedule_date` date DEFAULT NULL,
  `frequency` enum('weekly','monthly','yearly') DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('penalty_wants_items')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `penalty_wants_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('products')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `goods_type` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('room_poster')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `room_poster` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `sid` varchar(50) DEFAULT NULL,
  `poster_name` varchar(255) DEFAULT NULL,
  `poster_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('school_month_settings')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `school_month_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `start_month` tinyint(2) NOT NULL COMMENT '1=Jan, 2=Feb ... 12=Dec',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `school_unique` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('stores')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `stores` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) DEFAULT NULL,
  `store_name` varchar(255) NOT NULL,
  `store_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('student_closet')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `student_closet` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` varchar(64) NOT NULL,
  `catalog_item_id` int(10) unsigned NOT NULL,
  `purchased_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `purchase_date` date NOT NULL DEFAULT curdate(),
  `price_paid_zeds` int(11) NOT NULL COMMENT 'actual price at time of purchase',
  PRIMARY KEY (`id`),
  KEY `idx_closet_student` (`student_id`),
  KEY `idx_closet_student_dt` (`student_id`,`purchase_date`),
  KEY `fk_closet_catalog` (`catalog_item_id`),
  CONSTRAINT `fk_closet_catalog` FOREIGN KEY (`catalog_item_id`) REFERENCES `durable_goods_catalog` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('supermarketname')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `supermarketname` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('user_mba')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `user_mba` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `sid` varchar(50) DEFAULT NULL,
  `budget` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`budget`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_mba_user_id_index` (`user_id`),
  CONSTRAINT `user_mba_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

        if (!Schema::hasTable('wellbeing_posts')) {
            DB::unprepared(<<<'SQL'
CREATE TABLE `wellbeing_posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `short_description` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `type` enum('article','video') NOT NULL DEFAULT 'article',
  `category` enum('financial','stress','lifestyle','general') DEFAULT 'general',
  `youtube_url` varchar(500) DEFAULT NULL,
  `read_time` varchar(20) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `published_at` datetime DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('wellbeing_posts');
        Schema::dropIfExists('user_mba');
        Schema::dropIfExists('supermarketname');
        Schema::dropIfExists('student_closet');
        Schema::dropIfExists('stores');
        Schema::dropIfExists('school_month_settings');
        Schema::dropIfExists('room_poster');
        Schema::dropIfExists('products');
        Schema::dropIfExists('penalty_wants_items');
        Schema::dropIfExists('pay_bills');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('npos');
        Schema::dropIfExists('mba_position');
        Schema::dropIfExists('grocery_costs');
        Schema::dropIfExists('groceriesvegetarian');
        Schema::dropIfExists('groceriesvegan');
        Schema::dropIfExists('groceriespecastarian');
        Schema::dropIfExists('groceriesomnivore');
        Schema::dropIfExists('finhero_student_points');
        Schema::dropIfExists('finhero_monthly_settings');
        Schema::dropIfExists('finhero_badge_records');
        Schema::dropIfExists('finhero_activity_registry');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('durable_goods_catalog');
        Schema::dropIfExists('donations');
        Schema::dropIfExists('direct_deposite');
        Schema::dropIfExists('city_mall_items');
        Schema::dropIfExists('beneficiaries');
        Schema::dropIfExists('activitiestype');
        Schema::dropIfExists('activities');
    }
};
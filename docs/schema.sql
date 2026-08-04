-- =============================================================================
-- PayIn Platform — Esquema relacional normalizado (MariaDB/MySQL)
--
-- Generado con un dump del esquema producido por las migraciones de Laravel:
--   docker compose exec db mariadb-dump --no-data payin <tablas> -upayin -psecret
-- Fuente de verdad: backend/src/*/Infrastructure/Persistence/Migrations.
-- =============================================================================

CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` uuid NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `accounts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` uuid NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `account_number` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounts_uuid_unique` (`uuid`),
  KEY `accounts_customer_id_foreign` (`customer_id`),
  CONSTRAINT `accounts_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `payment_methods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` uuid NOT NULL,
  `account_id` bigint(20) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_methods_uuid_unique` (`uuid`),
  KEY `payment_methods_account_id_foreign` (`account_id`),
  CONSTRAINT `payment_methods_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `payment_providers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_providers_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `pay_ins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` uuid NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `account_id` bigint(20) unsigned NOT NULL,
  `payment_method_id` bigint(20) unsigned NOT NULL,
  `payment_provider_id` bigint(20) unsigned NOT NULL,
  `amount` bigint(20) unsigned NOT NULL,
  `currency` varchar(3) NOT NULL,
  `status` varchar(255) NOT NULL,
  `provider_request` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provider_request`)),
  `provider_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provider_response`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pay_ins_uuid_unique` (`uuid`),
  KEY `pay_ins_customer_id_foreign` (`customer_id`),
  KEY `pay_ins_account_id_foreign` (`account_id`),
  KEY `pay_ins_payment_method_id_foreign` (`payment_method_id`),
  KEY `pay_ins_payment_provider_id_foreign` (`payment_provider_id`),
  KEY `pay_ins_status_index` (`status`),
  CONSTRAINT `pay_ins_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),
  CONSTRAINT `pay_ins_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `pay_ins_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`),
  CONSTRAINT `pay_ins_payment_provider_id_foreign` FOREIGN KEY (`payment_provider_id`) REFERENCES `payment_providers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `pay_in_status_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pay_in_id` bigint(20) unsigned NOT NULL,
  `previous_status` varchar(255) DEFAULT NULL,
  `current_status` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pay_in_status_history_pay_in_id_foreign` (`pay_in_id`),
  CONSTRAINT `pay_in_status_history_pay_in_id_foreign` FOREIGN KEY (`pay_in_id`) REFERENCES `pay_ins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

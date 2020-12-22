-- Add new configuration value for AutoNumberingResetMonth
set @query = if ((select count(*) from `tblconfiguration` where `setting` = 'AutoNumberingResetMonth') = 0, "INSERT INTO `tblconfiguration` (`setting`, `value`, `created_at`, `updated_at`) VALUES ('AutoNumberingResetMonth', CONCAT_WS('-', YEAR(CURRENT_DATE()), MONTH(CURRENT_DATE())), now(), now());",'DO 0;');
prepare statement from @query;
execute statement;
deallocate prepare statement;

-- Add new configuration value for AutoPaidNumberingResetMonth
set @query = if ((select count(*) from `tblconfiguration` where `setting` = 'AutoPaidNumberingResetMonth') = 0, "INSERT INTO `tblconfiguration` (`setting`, `value`, `created_at`, `updated_at`) VALUES ('AutoPaidNumberingResetMonth', CONCAT_WS('-', YEAR(CURRENT_DATE()), MONTH(CURRENT_DATE())), now(), now());",'DO 0;');
prepare statement from @query;
execute statement;
deallocate prepare statement;

-- Add new payment gateway product mapping table
CREATE TABLE IF NOT EXISTS `tblpaymentgateways_product_mapping` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gateway` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `account_identifier` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `product_identifier` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `remote_identifier` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Add new field to tblinvoices for date_refunded
set @query = if ((select count(*) from information_schema.columns where table_schema=database() and table_name='tblinvoices' and column_name='date_refunded') = 0, 'alter table `tblinvoices` add `date_refunded` timestamp NOT NULL DEFAULT \'0000-00-00 00:00:00\' AFTER `last_capture_attempt`', 'DO 0');
prepare statement from @query;
execute statement;
deallocate prepare statement;

-- Add new field to tblinvoices for date_cancelled
set @query = if ((select count(*) from information_schema.columns where table_schema=database() and table_name='tblinvoices' and column_name='date_cancelled') = 0, 'alter table `tblinvoices` add `date_cancelled` timestamp NOT NULL DEFAULT \'0000-00-00 00:00:00\' AFTER `date_refunded`', 'DO 0');
prepare statement from @query;
execute statement;
deallocate prepare statement;

-- Add new field to tblinvoices for created_at
set @query = if ((select count(*) from information_schema.columns where table_schema=database() and table_name='tblinvoices' and column_name='created_at') = 0, 'alter table `tblinvoices` add `created_at` timestamp NOT NULL DEFAULT \'0000-00-00 00:00:00\' AFTER `notes`', 'DO 0');
prepare statement from @query;
execute statement;
deallocate prepare statement;

-- Add new field to tblinvoices for updated_at
set @query = if ((select count(*) from information_schema.columns where table_schema=database() and table_name='tblinvoices' and column_name='updated_at') = 0, 'alter table `tblinvoices` add `updated_at` timestamp NOT NULL DEFAULT \'0000-00-00 00:00:00\' AFTER `created_at`', 'DO 0');
prepare statement from @query;
execute statement;
deallocate prepare statement;

-- update type of gateways to BankAccount
UPDATE `tblpaymentgateways` SET `value` = 'Bank' WHERE `setting` = 'type' AND `gateway` IN ('authorizeecheck', 'bluepayecheck', 'directdebit');

-- Update pricing enum type to include usage billing values
set @query = if ((select count(*) from information_schema.columns where table_schema=database() and table_name='tblpricing' and column_name='type' and column_type='enum("product","addon","configoptions","domainregister","domaintransfer","domainrenew","domainaddons")') = 0, 'ALTER TABLE `tblpricing` CHANGE `type` `type` enum("product","addon","configoptions","domainregister","domaintransfer","domainrenew","domainaddons","usage") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL','DO 0;');
prepare statement from @query;
execute statement;
deallocate prepare statement;

CREATE TABLE IF NOT EXISTS `tblpricing_bracket` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `floor` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `ceiling` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `rel_type` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `rel_id` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `schema_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'flat',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbltenant_stats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL DEFAULT '0',
  `metric` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `value` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `measured_at` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `invoice_id` int(11) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `tblserver_tenants` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `server_id` int(11) NOT NULL DEFAULT '0',
  `tenant` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `server_tenant` (`tenant`,`server_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `tblusage_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rel_type` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `rel_id` int(11) NOT NULL DEFAULT '0',
  `module_type` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `module` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `metric` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `included` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `is_hidden` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tblusage_items_rel_type_id` (`rel_type`,`rel_id`),
  KEY `tblusage_items_module_type` (`module_type`),
  KEY `tblusage_items_module` (`module`),
  KEY `tblusage_items_metric` (`metric`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

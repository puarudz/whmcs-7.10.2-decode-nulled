-- Modify the datatype of the namespace_value column of tbllog_register
ALTER table `tbllog_register` modify `namespace_value` mediumtext COLLATE utf8_unicode_ci NOT NULL;

-- Add new field to tblemailmarketer for created_at
set @query = if ((select count(*) from information_schema.columns where table_schema=database() and table_name='tblemailmarketer' and column_name='created_at') = 0, 'alter table `tblemailmarketer` add `created_at` timestamp NOT NULL DEFAULT \'0000-00-00 00:00:00\' AFTER `marketing`', 'DO 0');
prepare statement from @query;
execute statement;
deallocate prepare statement;

-- Add new field to tblemailmarketer for updated_at
set @query = if ((select count(*) from information_schema.columns where table_schema=database() and table_name='tblemailmarketer' and column_name='updated_at') = 0, 'alter table `tblemailmarketer` add `updated_at` timestamp NOT NULL DEFAULT \'0000-00-00 00:00:00\' AFTER `created_at`', 'DO 0');
prepare statement from @query;
execute statement;
deallocate prepare statement;

-- add email_preferences to tblclients
set @query = if ((select count(*) from information_schema.columns where table_schema=database() and table_name='tblclients' and column_name='email_preferences') = 0, 'alter table `tblclients` add `email_preferences` TEXT COLLATE utf8_unicode_ci DEFAULT NULL AFTER `email_verified`', 'DO 0');
prepare statement from @query;
execute statement;
deallocate prepare statement;

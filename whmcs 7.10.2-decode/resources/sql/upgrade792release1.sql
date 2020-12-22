-- Add new field to tblhostingaddons for subscriptionid
set @query = if ((select count(*) from information_schema.columns where table_schema=database() and table_name='tblhostingaddons' and column_name='subscriptionid') = 0, 'alter table `tblhostingaddons` add `subscriptionid` VARCHAR(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT \'\' AFTER `notes`', 'DO 0');
prepare statement from @query;
execute statement;
deallocate prepare statement;

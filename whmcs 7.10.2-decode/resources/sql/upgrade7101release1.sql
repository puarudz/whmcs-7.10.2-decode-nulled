-- Add new field to tblhosting for promocount
set @query = if ((select count(*) from information_schema.columns where table_schema=database() and table_name='tblhosting' and column_name='promocount') = 0, 'alter table `tblhosting` add `promocount` INT(10) NULL DEFAULT \'0\' AFTER `promoid`', 'DO 0');
prepare statement from @query;
execute statement;
deallocate prepare statement;

-- Update promocount for existing services and set to null
UPDATE `tblhosting` SET `promocount` = null;
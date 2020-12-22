-- Add AcceptedCardTypes setting to tblconfiguration
set @query = if ((select count(*) from `tblconfiguration` where `setting` = 'AcceptedCardTypes') = 0, "INSERT INTO `tblconfiguration` (`setting`, `value`, `created_at`, `updated_at`) VALUES ('AcceptedCardTypes', 'Visa,MasterCard,Discover,American Express,JCB,Diners Club', now(), now());",'DO 0;');
prepare statement from @query;
execute statement;
deallocate prepare statement;

<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Updater\Version;

class Version533 extends IncrementalVersion
{
    protected $runUpdateCodeBeforeDatabase = true;
    protected function runUpdateCode()
    {
        $query = "ALTER TABLE  `tblsslorders` ADD  `provisiondate` DATE NOT NULL AFTER  `configdata`";
        mysql_query($query);
        return $this;
    }
}

?>
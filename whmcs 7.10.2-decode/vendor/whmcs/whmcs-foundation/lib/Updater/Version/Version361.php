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

class Version361 extends IncrementalVersion
{
    protected function runUpdateCode()
    {
        include_once ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "functions.php";
        $query = "SELECT id,value FROM tblregistrars";
        $result = mysql_query($query);
        while ($row = mysql_fetch_array($result)) {
            $id = $row["id"];
            $value = $row["value"];
            $value = encrypt($value);
            $query2 = "UPDATE tblregistrars SET value='" . $value . "' WHERE id='" . $id . "'";
            $result2 = mysql_query($query2);
        }
        return $this;
    }
}

?>
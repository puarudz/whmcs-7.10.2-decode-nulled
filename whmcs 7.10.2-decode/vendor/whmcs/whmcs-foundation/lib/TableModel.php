<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS;

abstract class TableModel extends TableQuery
{
    protected $pageObj = NULL;
    protected $queryObj = NULL;
    public function __construct(Pagination $obj = NULL)
    {
        $this->pageObj = $obj;
        $numrecords = Config\Setting::getValue("NumRecordstoDisplay");
        $this->setRecordLimit($numrecords);
        return $this;
    }
    public abstract function _execute($implementationData);
    public function setPageObj(Pagination $pageObj)
    {
        $this->pageObj = $pageObj;
    }
    public function getPageObj()
    {
        return $this->pageObj;
    }
    public function execute($implementationData = NULL)
    {
        $results = $this->_execute($implementationData);
        $this->getPageObj()->setData($results);
        return $this;
    }
}

?>
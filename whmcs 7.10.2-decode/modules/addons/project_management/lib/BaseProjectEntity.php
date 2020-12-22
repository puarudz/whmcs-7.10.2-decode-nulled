<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module\Addon\ProjectManagement;

abstract class BaseProjectEntity
{
    public $project = NULL;
    public function __construct(Project $project)
    {
        $this->project = $project;
    }
    public function project()
    {
        return $this->project;
    }
}

?>
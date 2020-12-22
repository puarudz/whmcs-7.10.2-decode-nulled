<?php

use WHMCS\Cron;
use WHMCS\Exception\Fatal;
use WHMCS\Terminus;

/**
 * admin/cron.php
 *
 * This file is deprecated and here for backwards compatibility.
 *
 * The distributed version of WHMCS provides the main application cron in
 * crons/cron.php
 *
 * The crons folder may be moved to any place above or below the docroot.
 *
 * For more information please see https://docs.whmcs.com/Custom_Crons_Directory
 */
/** @var WHMCS\Application $whmcs */

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'init.php';

define('PROXY_FILE', true);

try {
    $path = Cron::getCronsPath(basename(__FILE__));
    require_once $path;
} catch (Fatal $e) {
    echo Cron::formatOutput(Cron::getCronRootDirErrorMessage());
    Terminus::getInstance()->doExit(1);
} catch (\Exception $e) {
    echo Cron::formatOutput(Cron::getCronPathErrorMessage());
    Terminus::getInstance()->doExit(1);
}

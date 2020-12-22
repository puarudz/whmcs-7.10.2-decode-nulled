<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\Search\Controller;

abstract class AbstractSearchController implements \WHMCS\Search\ApplicationSupport\Controller\SearchInterface, \WHMCS\Search\SearchInterface
{
    public abstract function getSearchTerm(\WHMCS\Http\Message\ServerRequest $request);
    public abstract function getSearchable();
    public function searchRequest(\WHMCS\Http\Message\ServerRequest $request)
    {
        try {
            $data = $this->getSearchable()->search($this->getSearchTerm($request));
        } catch (\WHMCS\Exception\Information $e) {
            $data = array("warning" => $e->getMessage());
        } catch (\Exception $e) {
            $data = array("error" => $e->getMessage());
        }
        return new \WHMCS\Http\Message\JsonResponse($data);
    }
}

?>
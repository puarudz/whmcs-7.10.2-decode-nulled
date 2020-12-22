<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Knowledgebase;

class KnowledgebaseServiceProvider implements \WHMCS\Route\Contracts\DeferredProviderInterface
{
    use \WHMCS\Route\ProviderTrait;
    protected function getRoutes()
    {
        return array("/knowledgebase" => array(array("name" => "knowledgebase-article-view", "method" => array("GET", "POST"), "path" => "/{id:\\d+}[/{slug}.html]", "handle" => array("WHMCS\\Knowledgebase\\Controller\\Article", "view")), array("name" => "knowledgebase-category-view", "method" => "GET", "path" => "/{categoryId:\\d+}/{categoryName}", "handle" => array("WHMCS\\Knowledgebase\\Controller\\Category", "view")), array("name" => "knowledgebase-tag-view", "method" => "GET", "path" => "/tag/{tag}", "handle" => array("WHMCS\\Knowledgebase\\Controller\\Knowledgebase", "viewTag")), array("name" => "knowledgebase-search", "method" => array("GET", "POST"), "path" => "/search[/{search}]", "handle" => array("WHMCS\\Knowledgebase\\Controller\\Knowledgebase", "search")), array("name" => "knowledgebase-index", "method" => "GET", "path" => "", "handle" => array("WHMCS\\Knowledgebase\\Controller\\Knowledgebase", "index"))));
    }
    public function getDeferredRoutePathNameAttribute()
    {
        return "knowledgebase-";
    }
}

?>
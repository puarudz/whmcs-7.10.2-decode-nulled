<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\View\Menu\Factory;

class WhmcsExtension implements \Knp\Menu\Factory\ExtensionInterface
{
    public function buildOptions(array $options)
    {
        return array_merge(array("uri" => null, "badge" => null, "order" => null, "icon" => null, "headingHtml" => null, "bodyHtml" => null, "footerHtml" => null, "disabled" => false), $options);
    }
    public function buildItem(\Knp\Menu\ItemInterface $item, array $options)
    {
        $item->setUri($options["uri"])->setBadge($options["badge"])->setOrder($options["order"])->setIcon($options["icon"])->setHeadingHtml($options["headingHtml"])->setBodyHtml($options["bodyHtml"])->setFooterHtml($options["footerHtml"]);
        if ($options["disabled"]) {
            $item->disable();
        }
    }
}

?>
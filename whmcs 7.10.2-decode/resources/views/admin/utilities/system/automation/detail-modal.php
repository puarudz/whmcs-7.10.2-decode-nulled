<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

$assetHelper = DI::make("asset");
$tableClasses = array("automation-data-table", "table", "table-responsive", "table-striped", "table-condensed", "table-fixed-head");
echo WHMCS\View\Asset::cssInclude("tabdrop.css");
echo WHMCS\View\Asset::jsInclude("bootstrap-tabdrop.js");
if (count($tabTitles) === 0) {
    echo "    <div class=\"alert alert-info\">\n        There is no automation data available for the specified period\n    </div>\n";
} else {
    echo "    <div class=\"admin-tabs-v2\">\n        <ul class=\"nav nav-tabs admin-tabs detail-tabs\" role=\"tablist\">\n            ";
    $i = 1;
    foreach ($tabTitles as $tabTitle) {
        echo "                <li class=\"";
        echo $i == $selectedTab ? "active" : "";
        echo "\" role=\"presentation\">\n                    <a id=\"tab";
        echo ucfirst($tabTitle);
        echo ">\"\n                       data-toggle=\"tab\"\n                       href=\"#content";
        echo ucfirst($tabTitle);
        echo "\"\n                       role=\"tab\"\n                    >\n                        ";
        echo AdminLang::trans("utilities.automationStatusDetail." . $namespace . "." . $tabTitle . ".detail");
        echo "                    </a>\n                </li>\n                ";
        $i++;
    }
    echo "        </ul>\n        <div class=\"tab-content\">\n            ";
    $i = 1;
    foreach ($tabTitles as $tabTitle) {
        echo "                <div class=\"tab-pane";
        echo $i == $selectedTab ? " active" : "";
        echo "\"\n                     id=\"content";
        echo ucfirst($tabTitle);
        echo "\"\n                >\n                    ";
        if (!array_key_exists($tabTitle, $tabContent) || !$tabContent[$tabTitle]) {
            $noData = AdminLang::trans("utilities.automationStatusDetail." . $namespace . ".no" . ucfirst($tabTitle));
            echo "        <div class=\"alert alert-info no-margin clearfix\" role=\"alert\">\n            <i class=\"fas fa-info-circle fa-2x pull-left fa-fw\"></i>\n            <div style=\"margin-left: 40px;\">" . $noData . "</div>\n        </div>\n    </div>";
            $i++;
            continue;
        }
        echo "                    <table class=\"";
        echo implode(" ", $tableClasses);
        echo "\">\n                        <thead>\n                            <tr>\n                                <th class=\"id text-center\">";
        echo AdminLang::trans($idTitle);
        echo "</th>\n                                <th class=\"name text-center\">";
        echo AdminLang::trans("fields.clientname");
        echo "</th>\n                                ";
        if (!$isClient) {
            echo "                                    <th class=\"product text-center\">";
            echo AdminLang::trans("fields.product");
            echo "</th>\n                                ";
        }
        echo "                                <th class=\"status text-center\">";
        echo AdminLang::trans($statusTitle);
        echo "</th>\n                                ";
        if ($isInvoice) {
            echo "                                    <th class=\"due-date text-center\">";
            echo AdminLang::trans("fields.duedate");
            echo "</th>\n                                    <th class=\"total text-center\">";
            echo AdminLang::trans("fields.total");
            echo "</th>\n                                ";
        }
        echo "                                ";
        if ($isDomain) {
            echo "                                    <th class=\"expiry-date text-center\">";
            echo AdminLang::trans("fields.expirydate");
            echo "</th>\n                                ";
        }
        echo "                                ";
        if ($errorsOutput) {
            echo "                                    <th class=\"error text-center\">";
            echo AdminLang::trans("fields.errorMessage");
            echo "</th>\n                                ";
        }
        echo "                                <th class=\"edit-link text-center\"></th>\n                                ";
        if ($errorsOutput && !$isClient) {
            echo "                                    <th class=\"queue-link text-center\"></th>\n                                ";
        }
        echo "                            </tr>\n                        </thead>\n                        <tbody>\n                            ";
        $tabContents = $tabContent[$tabTitle];
        foreach ($tabContents as $content) {
            $errorMessage = $content["error"];
            $item = $content["item"];
            echo "                                <tr>\n                                    <td class=\"id text-center\">\n                                        <a class=\"autoLinked\"\n                                           href=\"";
            echo $assetHelper->getWebRoot() . "/" . $item->getLink();
            echo "\"\n                                        >\n                                            ";
            echo $item->id;
            echo "                                        </a>\n                                    </td>\n                                    <td class=\"name text-center\">\n                                        ";
            if ($item instanceof WHMCS\User\Client) {
                echo "<a href=\"" . $assetHelper->getWebRoot() . "/" . $item->getLink() . "\" class=\"autoLinked\">";
                echo $item->fullName;
                echo "</a>";
            } else {
                if ($item->client) {
                    echo "<a href=\"" . $assetHelper->getWebRoot() . "/" . $item->getLink() . "\" class=\"autoLinked\">";
                    echo $item->client->fullName;
                    echo "</a>";
                } else {
                    if ($item instanceof WHMCS\Support\Ticket) {
                        echo $item->name;
                    } else {
                        echo "-";
                    }
                }
            }
            echo "                                    </td>\n                                    ";
            if (!$isClient) {
                echo "                                        <td class=\"product text-center\">\n                                            ";
                if ($item instanceof WHMCS\Service\Addon) {
                    echo "<a href=\"" . $assetHelper->getWebRoot() . "/" . $item->getLink() . "\" class=\"autoLinked\">";
                    echo $item->name ?: $item->productAddon->name;
                    echo "</a>";
                } else {
                    if ($item instanceof WHMCS\Domain\Domain) {
                        echo "<a href=\"" . $assetHelper->getWebRoot() . "/" . $item->getLink() . "\" class=\"autoLinked\">";
                        echo $item->client->fullName;
                        echo "</a>";
                    } else {
                        if ($item instanceof WHMCS\Service\Service) {
                            echo "<a href=\"" . $assetHelper->getWebRoot() . "/" . $item->getLink() . "\" class=\"autoLinked\">";
                            echo $item->product->productGroup->name . " - ";
                            echo $item->product->name;
                            if ($item->domain) {
                                echo " - " . $item->domain;
                            }
                            echo "</a>";
                        }
                    }
                }
                echo "                                        </td>\n                                    ";
            }
            echo "                                    <td class=\"status text-center\">\n                                        ";
            if ($item instanceof WHMCS\Service\Addon || $item instanceof WHMCS\User\Client || $item instanceof WHMCS\Domain\Domain || $item instanceof WHMCS\Billing\Invoice || $item instanceof WHMCS\Support\Ticket) {
                echo $item->status;
            } else {
                if ($item instanceof WHMCS\Service\Service) {
                    echo $item->domainStatus;
                }
            }
            echo "                                    </td>\n                                    ";
            if ($item instanceof WHMCS\Billing\Invoice && $isInvoice) {
                echo "                                        <td class=\"due-date text-center\">\n                                            ";
                echo fromMySQLDate($item->dateDue);
                echo "                                        </td>\n                                        <td class=\"total text-center\">\n                                            ";
                echo formatCurrency($item->total, $item->client->currencyId);
                echo "                                        </td>\n                                    ";
            }
            echo "                                    ";
            if ($item instanceof WHMCS\Domain\Domain && $isDomain) {
                echo "                                        <td class=\"expiry-date text-center\">\n                                            ";
                echo fromMySQLDate($item->expiryDate);
                echo "                                        </td>\n                                    ";
            }
            echo "                                    ";
            if ($errorsOutput) {
                echo "                                        <td class=\"error text-center\">";
                echo $errorMessage;
                echo "</td>\n                                    ";
            }
            echo "                                    <td class=\"edit-link text-center\">\n                                        <a class=\"autoLinked\"\n                                           href=\"";
            echo $assetHelper->getWebRoot() . "/" . $item->getLink();
            echo "\"\n                                        >\n                                            <i class=\"fal fa-pencil-alt\"\n                                               data-toggle=\"tooltip\"\n                                               title=\"";
            echo AdminLang::trans("global.edit");
            echo "\"\n                                            ></i>\n                                        </a>\n                                    </td>\n                                    ";
            if ($errorsOutput && !$isClient) {
                echo "                                        <td class=\"queue-link text-center\">\n                                            ";
                if ($item->failedActions()->incomplete()->count() === 0) {
                    echo "                                                <i class=\"fal fa-arrow-to-right\"\n                                                   style=\"color: lightgrey;\"\n                                                   data-toggle=\"tooltip\"\n                                                   title=\"";
                    echo AdminLang::trans("utilities.automationStatusDetail.noModuleQueue");
                    echo "\"\n                                                ></i>\n                                            ";
                } else {
                    echo "                                                <a class=\"autoLinked\"\n                                                   href=\"";
                    echo $queueUri;
                    echo "?serviceType=";
                    echo $item instanceof WHMCS\Service\Addon ? "addon" : "service";
                    echo "&relatedId=";
                    echo $item->id;
                    echo "\"\n                                                >\n                                                    <i class=\"fal fa-arrow-to-right\"\n                                                       data-toggle=\"tooltip\"\n                                                       title=\"";
                    echo AdminLang::trans("utilities.automationStatusDetail.viewModuleQueue");
                    echo "\"\n                                                    ></i>\n                                                </a>\n                                            ";
                }
                echo "                                        </td>\n                                    ";
            }
            echo "                                </tr>\n                                ";
        }
        $i++;
        echo "                        </tbody>\n                    </table>\n                </div>\n            ";
    }
    echo "        </div>\n    </div>\n    <script>\n        jQuery(document).ready(function() {\n            jQuery(\".detail-tabs\").tabdrop();\n            jQuery(window).resize();\n            jQuery('[data-toggle=\"tooltip\"]').tooltip();\n        });\n        jQuery(window).resize(function() {\n            jQuery(\".detail-tabs\").tabdrop();\n        })\n    </script>\n";
}

?>
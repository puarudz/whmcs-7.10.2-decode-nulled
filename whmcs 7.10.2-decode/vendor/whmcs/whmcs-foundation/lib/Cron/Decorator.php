<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Cron;

class Decorator
{
    protected $item = NULL;
    public function __construct(DecoratorItemInterface $item)
    {
        $this->item = $item;
    }
    public function render($data, $isDisabled)
    {
        if ($this->item->isBooleanStatusItem()) {
            return $this->renderBooleanItem($data, $isDisabled);
        }
        return $this->renderStatisticalItem($data, $isDisabled);
    }
    public function renderStatisticalItem($data, $isDisabled)
    {
        $date = \App::getFromRequest("date");
        if (!$date) {
            $date = \WHMCS\Carbon::today()->toDateString();
        }
        $date = \WHMCS\Carbon::createFromFormat("Y-m-d", $date)->toAdminDateFormat();
        $detailUrl = $this->item->getDetailUrl();
        $modalTitle = "";
        if (is_array($this->item->getSuccessCountIdentifier())) {
            $primarySuccessCount = 0;
            foreach ($this->item->getSuccessCountIdentifier() as $identifier) {
                $primarySuccessCount += (int) $data[$identifier];
            }
        } else {
            $primarySuccessCount = (int) $data[$this->item->getSuccessCountIdentifier()];
        }
        if ($detailUrl) {
            $modalTitle = (string) $this->item->getName() . " - " . $date;
            $primarySuccessCount = "<a href=\"" . $detailUrl . "\"\n   class=\"open-modal\"\n   data-modal-size=\"modal-lg\"\n   data-modal-title=\"" . $modalTitle . "\"\n></a>" . $primarySuccessCount;
        }
        $successKeyword = $this->item->getSuccessKeyword();
        if ($this->item->getFailureCountIdentifier()) {
            $failedCountIdentifier = (int) $data[$this->item->getFailureCountIdentifier()];
            if ($detailUrl) {
                $failedLink = "<a href=\"" . $detailUrl . "/tab2\"\n   class=\"failed open-modal\"\n   data-modal-size=\"modal-lg\"\n   data-modal-title=\"" . $modalTitle . "\"\n>\n    " . $failedCountIdentifier . " " . $this->item->getFailureKeyword() . "\n</a>";
            } else {
                $failedLink = (string) $failedCountIdentifier . " " . $this->item->getFailureKeyword();
            }
        } else {
            $failedLink = "";
        }
        $widgetClass = "";
        if ($detailUrl) {
            $widgetClass = " automation-clickable-widget";
        }
        $disabled = "";
        if ($isDisabled && $primarySuccessCount == 0) {
            $primarySuccessCount = "-";
            $successKeyword = "";
            $failedLink = "";
            $widgetClass = "";
            $disabled = "<small>Disabled</small>";
        }
        return "<div class=\"widget" . $widgetClass . "\">\n    <div class=\"info-container\">\n        <div class=\"pull-right\">\n            <i class=\"" . $this->item->getIcon() . " fa-2x\"></i>\n        </div>\n        <p class=\"intro\">\n            " . $this->item->getName() . "\n        </p>\n        <h3 class=\"title\">\n            <span class=\"figure\">\n                " . $primarySuccessCount . "\n            </span>\n            <span class=\"note\">\n                " . $successKeyword . "\n            </span>\n        </h3>\n        " . $failedLink . $disabled . "\n    </div>\n</div>";
    }
    public function renderBooleanItem($data, $isDisabled)
    {
        $primarySuccessCount = (bool) $data[$this->item->getSuccessCountIdentifier()];
        $name = $this->item->getName();
        if ($name == "WHMCS Updates" && array_key_exists("update.available", $data) && $data["update.available"] == 1) {
            $name = "<a href=\"update.php\">An Update is Available</a>";
            if (array_key_exists("update.version", $data) && $data["update.version"]) {
                $name = "<a href=\"update.php\">" . $data["update.version"] . " is Available</a>";
            }
        } else {
            if ($name == "WHMCS Updates") {
                $name = "No Update Available";
            }
        }
        $icon = $primarySuccessCount ? "fas fa-check" : "fas fa-times";
        $disabled = "";
        if ($isDisabled && !$primarySuccessCount) {
            $disabled = "<small>Disabled</small>";
        }
        return "<div class=\"widget\">\n            <div class=\"info-container info-container-boolean\">\n                <div class=\"pull-right\"><i class=\"" . $this->item->getIcon() . " fa-2x\"></i></div>\n                <p class=\"intro\">\n                    <span class=\"status\"><i class=\"" . $icon . "\"></i></span>&nbsp;\n                    " . $name . "\n                </p>\n                " . $disabled . "\n            </div>\n        </div>";
    }
}

?>
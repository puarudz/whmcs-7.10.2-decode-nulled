<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Domain\TopLevel;

class Categories
{
    protected $data = NULL;
    protected $loaded = false;
    const DIRECTORY = NULL;
    const FILENAME = "categories.json";
    const REMOVE_KEYWORD = "REMOVE";
    protected function getDirectory()
    {
        return ROOTDIR . DIRECTORY_SEPARATOR . self::DIRECTORY . DIRECTORY_SEPARATOR;
    }
    protected function load()
    {
        if (!$this->loaded) {
            $distFilename = $this->getDirectory() . "dist." . self::FILENAME;
            if (file_exists($distFilename)) {
                $data = file_get_contents($distFilename);
                $this->setData(json_decode($data, true));
            }
            $userFilename = $this->getDirectory() . self::FILENAME;
            if (file_exists($userFilename)) {
                $data = file_get_contents($userFilename);
                $this->applyUserCustomisations(json_decode($data, true));
            }
            $this->loaded = true;
        }
        return $this;
    }
    protected function setData($data)
    {
        if (is_array($data)) {
            $this->data = collect($data);
        }
        return $this;
    }
    protected function applyUserCustomisations($userData)
    {
        if (is_array($userData)) {
            $data = $this->data->toArray();
            if (isset($userData[self::REMOVE_KEYWORD]) && is_array($userData[self::REMOVE_KEYWORD])) {
                foreach ($userData[self::REMOVE_KEYWORD] as $category => $tldsToRemove) {
                    if (array_key_exists($category, $data)) {
                        $data[$category] = array_diff($data[$category], $tldsToRemove);
                    }
                }
                unset($userData[self::REMOVE_KEYWORD]);
            }
            foreach ($userData as $category => $tlds) {
                if (is_array($tlds)) {
                    foreach ($tlds as $tld) {
                        $data[$category][] = $tld;
                    }
                }
            }
            $this->data = collect($data);
        }
        return $this;
    }
    public function getAllTlds()
    {
        $this->load();
        $all = array();
        foreach ($this->data as $category => $tlds) {
            $all = array_merge($all, $tlds);
        }
        return $all;
    }
    public function hasTld($tld)
    {
        return in_array($tld, $this->getAllTlds());
    }
    public function getCategoriesByTld($tld, $allowOther = true)
    {
        $this->load();
        $tld = $this->formatTld($tld);
        $categories = array();
        foreach ($this->data as $category => $tlds) {
            if (in_array($tld, $tlds)) {
                $categories[] = $category;
            }
        }
        if (count($categories) == 0 && $allowOther) {
            $categories[] = "Other";
        }
        return $categories;
    }
    public function getCategoriesByTlds(array $tlds)
    {
        $categories = array();
        $nonCategorised = array();
        foreach ($tlds as $tld) {
            $tldCategories = $this->getCategoriesByTld($tld, false);
            if (0 < count($tldCategories)) {
                foreach ($tldCategories as $category) {
                    $categories[$category][] = $tld;
                }
            } else {
                $nonCategorised[] = $tld;
            }
        }
        $categoriesToReturn = array();
        foreach ($this->data as $category => $tlds) {
            if (array_key_exists($category, $categories)) {
                $categoriesToReturn[$category] = $categories[$category];
            }
        }
        if (0 < count($nonCategorised)) {
            $categoriesToReturn["Other"] = $nonCategorised;
        }
        return $categoriesToReturn;
    }
    protected function formatTld($tld)
    {
        return "." . ltrim($tld, ".");
    }
}

?>
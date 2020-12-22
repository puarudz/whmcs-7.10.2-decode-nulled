<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Product;

class Products
{
    public function getProducts($groupId = NULL)
    {
        $where = array();
        if ($groupId) {
            $where["tblproducts.gid"] = (int) $groupId;
        }
        $products = array();
        $result = select_query("tblproducts", "tblproducts.id,tblproducts.gid,tblproducts.retired,tblproducts.name,tblproductgroups.name AS groupname", $where, "tblproductgroups`.`order` ASC, `tblproducts`.`order` ASC, `name", "ASC", "", "tblproductgroups ON tblproducts.gid=tblproductgroups.id");
        while ($data = mysql_fetch_assoc($result)) {
            $products[] = $data;
        }
        return $products;
    }
}

?>
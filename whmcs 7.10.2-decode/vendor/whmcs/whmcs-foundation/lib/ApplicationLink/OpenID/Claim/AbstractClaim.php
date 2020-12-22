<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\ApplicationLink\OpenID\Claim;

abstract class AbstractClaim
{
    protected $user = NULL;
    protected $claimName = NULL;
    public function __construct(\WHMCS\User\UserInterface $user, $claimName = NULL)
    {
        $this->setUser($user);
        if ($claimName) {
            $this->setClaimName($claimName);
        }
        $this->hydrate();
    }
    public function getUser()
    {
        return $this->user;
    }
    public function setUser(\WHMCS\User\UserInterface $user)
    {
        $this->user = $user;
        return $this;
    }
    public function getClaimName()
    {
        return $this->claimName;
    }
    public function setClaimName($claimName)
    {
        $this->claimName = $claimName;
        return $this;
    }
    public function toArray()
    {
        $data = array();
        $properties = get_object_vars($this);
        foreach ($properties as $propName => $propValue) {
            if ($propName == "user" || $propName == "claimName") {
                continue;
            }
            $data[$propName] = $propValue;
        }
        return $data;
    }
    protected abstract function hydrate();
}

?>
<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\User;

abstract class AbstractUser extends \WHMCS\Model\AbstractModel
{
    public abstract function isAllowedToAuthenticate();
    public static function findUuid($uuid)
    {
        if (!$uuid) {
            return null;
        }
        return static::where("uuid", "=", $uuid)->first();
    }
    public static function boot()
    {
        parent::boot();
        static::creating(function (AbstractUser $model) {
            if (!$model->uuid) {
                $uuid = \Ramsey\Uuid\Uuid::uuid4();
                $model->uuid = $uuid->toString();
            }
        });
        static::saving(function (AbstractUser $model) {
            if (!$model->uuid) {
                $uuid = \Ramsey\Uuid\Uuid::uuid4();
                $model->uuid = $uuid->toString();
            }
        });
    }
}

?>
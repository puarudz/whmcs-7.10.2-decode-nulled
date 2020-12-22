<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\Controller;

class ImageController
{
    public function uploadImage(\WHMCS\Http\Message\ServerRequest $request)
    {
        if (!function_exists("checkPermission")) {
            require_once ROOTDIR . "includes" . ROOTDIR . "adminfunctions.php";
        }
        $type = $request->get("type");
        try {
            switch ($type) {
                case \WHMCS\Utility\Image::IMAGE_KNOWLEDGEBASE:
                    $permission = "Manage Knowledgebase";
                    $class = "WHMCS\\Knowledgebase\\Image";
                    $classMethod = "storeAsKbImage";
                    break;
                case \WHMCS\Utility\Image::IMAGE_EMAIL:
                    $permission = "Create/Edit Email Templates";
                    $class = "WHMCS\\Mail\\Image";
                    $classMethod = "storeAsEmailImage";
                    break;
                default:
                    throw new \WHMCS\Exception("Invalid Type for Upload");
            }
            checkPermission($permission);
            $fileUpload = new \WHMCS\File\Upload("file");
            $fileExtension = strtolower($fileUpload->getExtension());
            $fileValidationFunctions = array(".png" => "imagecreatefrompng", ".jpg" => "imagecreatefromjpeg", ".jpeg" => "imagecreatefromjpeg", ".jpe" => "imagecreatefromjpeg", ".gif" => "imagecreatefromgif");
            $fileVerified = false;
            if (isset($fileValidationFunctions[$fileExtension])) {
                $validationFunction = $fileValidationFunctions[$fileExtension];
                if (function_exists($validationFunction)) {
                    $hImage = $validationFunction($fileUpload->getFileTmpName());
                    $fileVerified = is_resource($hImage);
                    imagedestroy($hImage);
                }
            }
            if ($fileVerified) {
                list($fileUpload) = \WHMCS\File\Upload::getUploadedFiles("file");
                $image = $class::create(array("filename" => $fileUpload->{$classMethod}(), "original_name" => $fileUpload->getFileName()));
                return new \WHMCS\Http\Message\JsonResponse(array("location" => fqdnRoutePath("image-display", $type, $image->id, foreignChrReplace($image->originalName))));
            }
            throw new \WHMCS\Exception(\AdminLang::trans("support.invalidFilename"));
        } catch (\Exception $e) {
            return new \WHMCS\Http\Message\JsonResponse(array("body" => $e->getMessage()), 500);
        }
    }
    public function recentlyUploaded(\WHMCS\Http\Message\ServerRequest $request)
    {
        if (!function_exists("checkPermission")) {
            require_once ROOTDIR . "includes" . ROOTDIR . "adminfunctions.php";
        }
        $type = $request->get("type");
        switch ($type) {
            case \WHMCS\Utility\Image::IMAGE_KNOWLEDGEBASE:
                $permission = "Manage Knowledgebase";
                $class = "WHMCS\\Knowledgebase\\Image";
                break;
            case \WHMCS\Utility\Image::IMAGE_EMAIL:
                $permission = "Create/Edit Email Templates";
                $class = "WHMCS\\Mail\\Image";
                break;
            default:
                return new \WHMCS\Http\Message\JsonResponse(array());
        }
        checkPermission($permission);
        $imageList = array();
        $images = $class::orderBy("id", "desc")->limit(25)->get();
        foreach ($images as $image) {
            $imageUrl = fqdnRoutePath("image-display", $type, $image->id, foreignChrReplace($image->originalName));
            $imageList[] = array("title" => $image->originalName, "value" => $imageUrl);
        }
        return new \WHMCS\Http\Message\JsonResponse($imageList);
    }
}

?>
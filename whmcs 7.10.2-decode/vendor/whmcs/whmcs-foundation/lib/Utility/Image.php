<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Utility;

class Image
{
    const IMAGE_EMAIL = "em";
    const IMAGE_KNOWLEDGEBASE = "kb";
    public function displayKbImage($id)
    {
        $this->displayImage($id);
    }
    public function displayEMailImage($id)
    {
        $this->displayImage($id, self::IMAGE_EMAIL);
    }
    protected function displayImage($id, $type = self::IMAGE_KNOWLEDGEBASE)
    {
        switch ($type) {
            case self::IMAGE_EMAIL:
                $storage = \Storage::emailImages();
                $class = "WHMCS\\Mail\\Image";
                break;
            case self::IMAGE_KNOWLEDGEBASE:
            default:
                $storage = \Storage::kbImages();
                $class = "WHMCS\\Knowledgebase\\Image";
                try {
                } catch (\Exception $e) {
                    $this->displayDefaultImageUnavailable();
                }
        }
        $file = $class::findOrFail($id);
        $fileName = $file->filename;
        $fileParts = explode(".", $fileName, 2);
        $fileSize = $storage->getSizeStrict($fileName);
        $loweredFileExtension = strtolower($fileParts[1]);
        switch ($loweredFileExtension) {
            case "gif":
            case "png":
            case "jpeg":
                $contentType = "image/" . $loweredFileExtension;
                break;
            case "jpe":
            case "jpg":
                $contentType = "image/jpeg";
                break;
            default:
                throw new \WHMCS\Exception("Invalid Access Attempt");
        }
        header("Content-Length: " . $fileSize);
        header("Content-Type: " . $contentType);
        $stream = $storage->readStream($fileName);
        echo stream_get_contents($stream);
        fclose($stream);
        \WHMCS\Terminus::getInstance()->doExit();
    }
    protected function displayDefaultImageUnavailable()
    {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Content-type: image/png");
        $image = imagecreate(640, 480);
        $textBackground = imagecolorallocate($image, 0, 0, 0);
        $textColour = imagecolorallocate($image, 255, 255, 255);
        $imageText = \Lang::trans("imageUnavailable");
        $xVal = 9 * strlen($imageText) / 2;
        imagestring($image, 5, 320 - $xVal, 200, $imageText, $textColour);
        imagepng($image);
        imagedestroy($image);
    }
}

?>
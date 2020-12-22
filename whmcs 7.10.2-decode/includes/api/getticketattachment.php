<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

$relatedId = App::getFromRequest("relatedid");
$type = App::getFromRequest("type");
$index = App::getFromRequest("index");
if (!$relatedId) {
    $apiresults = array("result" => "error", "message" => "Related ID Required");
} else {
    if (!in_array($type, array("ticket", "reply", "note"))) {
        $apiresults = array("result" => "error", "message" => "Invalid Type. Must be one of ticket, reply, note");
    } else {
        if (!App::isInRequest("index")) {
            $apiresults = array("result" => "error", "message" => "Attachment Index Required");
        } else {
            $field = "attachment";
            switch ($type) {
                case "reply":
                    $table = "tblticketreplies";
                    break;
                case "note":
                    $table = "tblticketnotes";
                    $field = "attachments";
                    break;
                default:
                    $table = "tbltickets";
            }
            $relatedData = WHMCS\Database\Capsule::table($table)->find($relatedId, array($field, "attachments_removed"));
            if (!$relatedData) {
                $apiresults = array("result" => "error", "message" => "Related ID Not Found");
            } else {
                if (!$relatedData->{$field}) {
                    $apiresults = array("result" => "error", "message" => "No Attachments Found");
                } else {
                    if ($relatedData->attachments_removed) {
                        $apiresults = array("result" => "error", "message" => "Attachments Deleted");
                    } else {
                        $attachments = explode("|", $relatedData->{$field});
                        if (!array_key_exists($index, $attachments)) {
                            $apiresults = array("result" => "error", "message" => "Invalid Attachment Index");
                        } else {
                            $file = $attachments[$index];
                            $fileName = substr($file, 7);
                            $storage = Storage::ticketAttachments();
                            try {
                                $stream = $storage->readStream($file);
                                $data = base64_encode(stream_get_contents($stream));
                                fclose($stream);
                            } catch (Exception $e) {
                                $apiresults = array("result" => "error", "message" => $e->getMessage());
                                return NULL;
                            }
                            $apiresults = array("result" => "success", "filename" => $fileName, "data" => $data);
                        }
                    }
                }
            }
        }
    }
}

?>
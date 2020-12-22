<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\ApplicationLink;

class AccessToken extends \WHMCS\Model\AbstractModel
{
    protected $table = "tbloauthserver_access_tokens";
    protected $primaryKey = "id";
    protected $scopePivotTable = "tbloauthserver_access_token_scopes";
    protected $scopePivotId = "access_token_id";
    protected $commaSeparated = array("grantTypes");
    protected $dates = array("expires");
    public function createTable($drop = false)
    {
        $schemaBuilder = \Illuminate\Database\Capsule\Manager::schema();
        if ($drop) {
            $schemaBuilder->dropIfExists($this->getTable());
            $schemaBuilder->dropIfExists($this->scopePivotTable);
        }
        if (!$schemaBuilder->hasTable($this->getTable())) {
            $schemaBuilder->create($this->getTable(), function ($table) {
                $table->increments("id");
                $table->string("access_token", 80)->unique();
                $table->string("client_id", 80)->default("");
                $table->string("user_id", 255)->default("");
                $table->string("redirect_uri", 2000)->default("");
                $table->timestamp("expires")->default("0000-00-00 00:00:00");
                $table->timestamp("created_at")->default("0000-00-00 00:00:00");
                $table->timestamp("updated_at")->default("0000-00-00 00:00:00");
            });
        }
        if (!$schemaBuilder->hasTable($this->scopePivotTable)) {
            $self = $this;
            $schemaBuilder->create($this->scopePivotTable, function ($table) use($self) {
                $table->integer($self->scopePivotId, false, true)->default(0);
                $table->integer("scope_id", false, true)->default(0);
                $table->index(array($self->scopePivotId, "scope_id"), (string) $self->scopePivotTable . "_scope_id_index");
            });
        }
        $scope = new Scope();
        $scope->createTable();
    }
    public function scopes()
    {
        return $this->belongsToMany("\\WHMCS\\ApplicationLink\\Scope", $this->scopePivotTable, $this->scopePivotId, "scope_id");
    }
    protected function getFormattedScopes()
    {
        $scopes = $this->scopes()->get();
        $spaceDelimitedScopes = "";
        foreach ($scopes as $scope) {
            $spaceDelimitedScopes .= " " . $scope->scope;
        }
        return trim($spaceDelimitedScopes);
    }
    public function getScopeAttribute()
    {
        return $this->getFormattedScopes();
    }
    public function getUserAttribute()
    {
        $uuid = $this->getRawAttribute("user_id");
        $delimiter = strpos($uuid, ":");
        $model = null;
        if ($delimiter !== false) {
            $id = substr($uuid, $delimiter + 1);
            $model = \WHMCS\User\Client\Contact::find($id);
        } else {
            $model = \WHMCS\User\Client::findUuid($uuid);
        }
        return $model;
    }
    public function client()
    {
        return $this->belongsTo("\\WHMCS\\ApplicationLink\\Client", "client_id", "identifier");
    }
    public function toArray()
    {
        $data = parent::toArray();
        $data["expires"] = $this->expires->timestamp;
        $data["scope"] = $this->scope;
        return $data;
    }
    public static function deleteExpired(\WHMCS\Carbon $datetime = NULL)
    {
        if (!$datetime) {
            $datetime = \WHMCS\Carbon::now();
        }
        $tokens = self::where("expires", "<", $datetime->toDateTimeString())->get();
        foreach ($tokens as $token) {
            $token->delete();
        }
    }
}

?>
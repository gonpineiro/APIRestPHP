<?php

namespace App\Models;

use ErrorException;

/**
 * This is the model class for table "wapUsuario".
 *
 * @property int $personaId
 * @property int $usuario
 * @property string $clave
 * 
 */
class WapUsuario extends BaseModel
{
    protected $table = 'wapUsuarios';
    protected $logPath = 'v1/wapUsuarios';
    protected $identity = 'ReferenciaID';

    protected $fillable = ['PersonaID', 'Usuario', 'Clave'];

    public function wapPersona()
    {
        return $this->hasOne(WapPersona::class, 'PersonaID',  'ReferenciaID');
    }

    public function aplicaciones()
    {
        $sql =
            "SELECT 
                app.REFERENCIA,
                app.APLICACION,
                app.TITULO,
                app.URL
            FROM dbo.wapUsuariosPerfiles usu_per
                LEFT JOIN dbo.wapUsuarios wap_usu ON usu_per.ReferenciaID = wap_usu.ReferenciaID 
                LEFT JOIN dbo.wapPersonas wap_per ON wap_usu.PersonaID  = wap_per.ReferenciaID
                LEFT JOIN dbo.wlAplicaciones app ON usu_per.AppID = app.REFERENCIA 
            WHERE wap_usu.ReferenciaID = ";

        if (!is_array(array_values($this->value)[0])) {
            $id = $this->value['ReferenciaID'];
            $result = $this->executeSqlQuery($sql . $id, false);

            if ($result instanceof ErrorException) {
                logFileEE($this->logPath, $result, get_class($this), __FUNCTION__);
            }

            $this->value['aplicaciones'] = $result;
        } else {
            foreach ($this->value as $key => $value) {
                $id = $value['ReferenciaID'];
                $result = $this->executeSqlQuery($sql . $id, false);

                if ($result instanceof ErrorException) {
                    logFileEE($this->logPath, $result, get_class($this), __FUNCTION__);
                }

                $this->value[$key]['aplicaciones'] = $result;
            }
        }
    }
}

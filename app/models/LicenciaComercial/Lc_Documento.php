<?php

namespace App\Models\LicenciaComercial;

use App\Connections\BaseDatos;
use App\Models\BaseModel;
use ErrorException;

class Lc_Documento extends BaseModel
{
    protected $table = 'lc_documentos';
    protected $logPath = 'v1/licencia_comercial';
    protected $identity = 'id';

    protected $fillable = [
        'id_solicitud',
        'constancia_afip',
        'impuesto',
        'constancia_convenio_dpr',
        'contrato_social',
        'certificado_vigencia',
        'obra_actualizado',
        'escritura',
        'reglamento_copropiedad',
        'reglamento_copropiedad',
        'recibo_retributaria',
    ];

    public $filesUrl = FILE_PATH . 'licencia_comercial/solicitud/';
}

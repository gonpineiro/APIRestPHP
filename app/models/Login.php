<?php

namespace App\Models;

use App\Connections\BaseDatos;
use ErrorException;

class Login
{
    public function getUserData($user, $pass)
    {
        try {
            $postData = [
                "action" => 3,
                "credentials" => [
                    "userName" => $user,
                    "userPass" => $pass
                ]
            ];

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => WEBLOGIN2,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($postData),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json']
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            if ($response) {
                return json_decode($response);
            } else {
                return new ErrorException('Problema con el inicio de sesion');
            }
        } catch (\Throwable $th) {
            return $th;
        }
    }

    function viewFetch($referenciaId, $doc)
    {
        $sql =
            "SELECT 
            (
                SELECT
                TOP 1
                    sol.id as id
                FROM wapUsuarios wu
                    LEFT JOIN wapPersonas per ON per.ReferenciaID = wu.PersonaID
                    LEFT JOIN libretas_usuarios usu ON usu.id_wappersonas = per.ReferenciaID
                    LEFT JOIN libretas_solicitudes sol ON sol.id_usuario_solicitante = usu.id
                WHERE wu.ReferenciaID = $referenciaId ORDER BY id DESC
            ) AS libreta,
            (
                SELECT 
                    insumo
                FROM licLicencias
                    WHERE Licencia = $doc
            ) as licencia,
            (
            SELECT 
                a.PATENTE as patente
            FROM dbo.wapUsuarios wu
                LEFT JOIN AC_ACARREO a ON a.ID_PERSONA = wu.PersonaID
            WHERE wu.ReferenciaID = $referenciaId and a.BORRADO_LOGICO = 'NO'
            ) as patente";

        try {
            $conn = new BaseDatos();
            $query =  $conn->query($sql);
            $result = $conn->fetch_assoc($query);
            return $result;
        } catch (\Throwable $th) {
            return $th;
        }
    }
}

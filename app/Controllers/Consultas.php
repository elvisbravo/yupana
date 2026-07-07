<?php

namespace App\Controllers;

class Consultas extends BaseController
{
    public function api_dni_ruc($tipo, $numero)
    {
        $db = \Config\Database::connect();
        $token = "facturalaya_erickpeso_05jFE7sAOudi8j0";

        $bloquear_busquedas = false;
        if ($bloquear_busquedas) {
            $resp['respuesta'] = 'error';
            $resp['titulo'] = 'Error';
            $resp['mensaje'] = 'Tenemos Problemas en los Servidores de SUNAT y RENIEC, ingresa los datos manualmente por favor...';
            return $this->response->setJSON($resp);
        }

        if ($tipo == 'dni') {
            $ruta = "https://facturalahoy.com/api/persona/" . $numero . '/' . $token . '/completa';
        } elseif ($tipo == 'ruc') {
            $ruta = "https://facturalahoy.com/api/empresa/" . $numero . '/' . $token . '/completa';
        } else {
            $resp['respuesta'] = 'error';
            $resp['titulo'] = 'Error';
            $resp['mensaje'] = 'Tipo de Documento Desconocido';
            return $this->response->setJSON($resp);
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $ruta,
            CURLOPT_USERAGENT => 'Consulta Datos',
            CURLOPT_CONNECTTIMEOUT => 0,
            CURLOPT_TIMEOUT => 400,
            CURLOPT_FAILONERROR => true
        ));

        $data = curl_exec($curl);
        if (curl_error($curl)) {
            $error_msg = curl_error($curl);
        }

        curl_close($curl);

        if (isset($error_msg)) {
            $resp['respuesta'] = 'error';
            $resp['titulo'] = 'Error';
            $resp['data'] = $data;
            $resp['encontrado'] = false;
            $resp['mensaje'] = 'Error en Api de Búsqueda';
            $resp['errores_curl'] = $error_msg;
            return $this->response->setJSON($resp);
        }

        $data_resp = json_decode($data);
        if (!isset($data_resp->respuesta) || $data_resp->respuesta == 'error') {
            $resp['respuesta'] = 'error';
            $resp['titulo'] = 'Error';
            $resp['encontrado'] = false;
            $resp['data_resp'] = $data_resp;
            return $this->response->setJSON($resp);
        }

        $apiData = json_decode($data);

        // Look up ubigeo if available
        $ubigeoId = null;
        $codigoUbigeo = '';
        foreach (['codigo_ubigeo', 'ubigeo', 'cod_ubigeo'] as $key) {
            if (!empty($apiData->$key)) {
                $codigoUbigeo = preg_replace('/[^0-9]/', '', $apiData->$key);
                if (strlen($codigoUbigeo) >= 4) break;
            }
        }
        if (strlen($codigoUbigeo) === 6) {
            $row = $db->query("SELECT id FROM ubigeos WHERE codigo = ?", [$codigoUbigeo])->getRow();
            if ($row) $ubigeoId = $row->id;
        }

        $resp['respuesta'] = 'ok';
        $resp['encontrado'] = true;
        $resp['api'] = true;
        $resp['data'] = $apiData;
        $resp['ubigeo_id'] = $ubigeoId;

        return $this->response->setJSON($resp);
    }
}

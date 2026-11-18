<?php
namespace app\components;
use Yii;

class LibService implements InterfaceLib
{






    public function obtenerPaises()
    {
        // URL de la API de CountriesNow
        $url = 'https://countriesnow.space/api/v0.1/countries';

        // Realizar la solicitud desde el servidor
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Evitar problemas con SSL

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['error' => 'No se pudo obtener la lista de países. Error: ' . $error];
        }
        curl_close($ch);

        // Verificar si la respuesta es válida
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Error al decodificar JSON: ' . json_last_error_msg()];
        }

        return $data['data'];
    }


    public function obtenerZonaHoraria($ciudad, $pais = null)
    {
        if (empty($ciudad)) {
            return ['success' => false, 'zona_horaria' => ''];
        }

        // ✅ Cargar el diccionario externo
        $mapaZonas = require(__DIR__ . '/../helpers/zonaHorariaHelper.php');

        // Buscar coincidencia exacta en el array
        if (isset($mapaZonas[$ciudad])) {
            return [
                'success' => true,
                'zona_horaria' => $mapaZonas[$ciudad]
            ];
        }

        // Fallback por coincidencia parcial
        $ciudadLower = strtolower(str_replace(' ', '_', trim($ciudad)));
        $paisLower = strtolower(str_replace(' ', '_', trim($pais ?? '')));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://worldtimeapi.org/api/timezone');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);

        $zonas = json_decode($result, true);
        if (!is_array($zonas)) {
            return ['success' => false, 'zona_horaria' => ''];
        }

        // Coincidencia por ciudad
        foreach ($zonas as $zona) {
            if (str_contains(strtolower($zona), $ciudadLower)) {
                return ['success' => true, 'zona_horaria' => $zona];
            }
        }

        // Coincidencia por país
        foreach ($zonas as $zona) {
            if (str_contains(strtolower($zona), $paisLower)) {
                return ['success' => true, 'zona_horaria' => $zona];
            }
        }

        return ['success' => false, 'zona_horaria' => ''];
    }




}
<?php

namespace app\components;

use app\models\Inversor;
use Yii;
use yii\helpers\FileHelper;

/**
 * Borra las solicitudes de la web que nunca confirmaron el correo (spam, correos inventados o gente que no siguió):
 * la cuenta, sus datos, su historial y su documento de identidad. Solo las "sin verificar" de origen web,
 * con más de DIAS días. Nunca toca solicitudes confirmadas, cargadas desde el panel ni inversores aprobados.
 *
 * Se ejecuta sola, como mucho una vez por hora, cuando alguien se registra (no hace falta cron).
 * También a mano o por cron:  php yii inversores/limpiar
 */
final class LimpiezaSolicitudes
{
    const DIAS = 7;
    const CLAVE_CACHE = 'limpieza-solicitudes';

    /** La corre si no se corrió en la última hora. */
    public static function siCorresponde(): void
    {
        if (Yii::$app->cache->get(self::CLAVE_CACHE)) {
            return;
        }
        Yii::$app->cache->set(self::CLAVE_CACHE, 1, 3600);
        try {
            self::ejecutar();
        } catch (\Throwable $e) { // la limpieza nunca debe romper un registro
            Yii::error('No se pudo limpiar las solicitudes sin confirmar: ' . $e->getMessage(), __METHOD__);
        }
    }

    /** @return int cuántas solicitudes borró */
    public static function ejecutar(int $dias = self::DIAS): int
    {
        $limite = date('Y-m-d H:i:s', time() - $dias * 86400);
        $ids = (new \yii\db\Query())->select('i.idusuario')->from(['i' => 'inversor'])
            ->innerJoin(['u' => 'usuarios'], 'u.idusuario = i.idusuario')
            ->where(['i.estado_solicitud' => 'sin_verificar', 'i.origen' => 'web', 'i.email_verificado' => null])
            ->andWhere(['<', 'i.fecha_registro', $limite])
            ->andWhere(['not exists', (new \yii\db\Query())->from('inversion_pago p')->where('p.idusuario = i.idusuario')])
            ->column();
        $db = Yii::$app->db;
        foreach ($ids as $id) {
            $tx = $db->beginTransaction();
            try {
                $db->createCommand()->delete('inversor_historial', ['idusuario' => (int) $id])->execute();
                $db->createCommand()->delete('historial_usuarios', ['idusuario_afectado' => (int) $id])->execute();
                $db->createCommand()->delete('usuarios', ['idusuario' => (int) $id])->execute(); // inversor, intereses y áreas: en cascada
                $tx->commit();
            } catch (\Throwable $e) {
                $tx->rollBack();
                Yii::error("No se pudo borrar la solicitud sin confirmar $id: " . $e->getMessage(), __METHOD__);
                continue;
            }
            $carpeta = Inversor::carpetaDe($id);
            if (is_dir($carpeta)) {
                FileHelper::removeDirectory($carpeta); // el documento de identidad
            }
        }
        if ($ids) {
            Yii::info('Se borraron ' . count($ids) . " solicitudes que no confirmaron el correo en $dias días.", __METHOD__);
        }
        return count($ids);
    }
}

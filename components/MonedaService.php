<?php

namespace app\components;

use app\models\ConfiguracionHistorial;
use app\models\Moneda;
use app\models\Proyecto;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * Configuración → Monedas (las que se eligen en cada proyecto).
 * Los cambios quedan en el historial de configuración (Datos generales), con el prefijo "Monedas:".
 */
class MonedaService implements InterfaceMonedas
{
    public function listar()
    {
        return [
            'dataProvider' => new ActiveDataProvider([
                'query' => Moneda::find()->orderBy(['orden' => SORT_ASC, 'codigo' => SORT_ASC]),
                'sort' => false,
                'pagination' => false,
            ]),
            'usadas' => array_map('intval', Proyecto::find()->select(['COUNT(*)', 'idmoneda'])->where(['not', ['idmoneda' => null]])
                ->groupBy('idmoneda')->indexBy('idmoneda')->column()),
        ];
    }

    public function buscar($id): Moneda
    {
        return Moneda::findOne((int) $id) ?? throw new NotFoundHttpException('La moneda no existe.');
    }

    public function guardar(Moneda $m, array $post)
    {
        $nueva = $m->isNewRecord;
        $antes = $m->getAttributes();
        $m->setAttributes(array_intersect_key($post['Moneda'] ?? [], array_flip(['codigo', 'simbolo', 'nombre'])), false);
        if (!$m->validate()) {
            return ['exito' => false, 'mensaje' => 'Revisá los campos marcados.'];
        }
        if ($nueva) {
            $m->estado = '1';
            $m->orden = (int) Moneda::find()->max('orden') + 1;
        }
        $m->save(false);
        if ($nueva) {
            ConfiguracionHistorial::registrar('Monedas: agregada ' . $m->codigo . ' (' . $m->simbolo . ', ' . $m->nombre . ')');
        } else {
            $cambios = [];
            foreach (['codigo', 'simbolo', 'nombre'] as $c) {
                if ((string) $antes[$c] !== (string) $m->$c) {
                    $cambios[] = $m->getAttributeLabel($c) . ': ' . $antes[$c] . ' → ' . $m->$c;
                }
            }
            if ($cambios) {
                ConfiguracionHistorial::registrar('Monedas: ' . $antes['codigo'] . "\n" . implode("\n", $cambios));
            }
        }
        return ['exito' => true, 'mensaje' => $nueva ? 'Moneda agregada.' : 'Cambios guardados.'];
    }

    public function cambiarEstado($id)
    {
        $m = $this->buscar($id);
        $activar = $m->estado !== '1';
        $m->updateAttributes(['estado' => $activar ? '1' : '0']);
        ConfiguracionHistorial::registrar('Monedas: ' . $m->codigo . ($activar ? ' activada' : ' desactivada (ya no se ofrece en proyectos nuevos)'));
        return ['exito' => true, 'mensaje' => $activar ? 'Moneda activada.' : 'Moneda desactivada.'];
    }

    public function eliminar($id)
    {
        $m = $this->buscar($id);
        $n = Proyecto::find()->where(['idmoneda' => $m->idmoneda])->count();
        if ($n > 0) {
            return ['exito' => false, 'mensaje' => "No se puede eliminar: la usan {$n} proyecto(s). Desactivala para que no se ofrezca más."];
        }
        $codigo = $m->codigo;
        $m->delete();
        ConfiguracionHistorial::registrar('Monedas: eliminada ' . $codigo);
        return ['exito' => true, 'mensaje' => 'Moneda eliminada.'];
    }
}

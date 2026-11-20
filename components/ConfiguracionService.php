<?php

namespace app\components;

use app\models\Configuracion;
use app\models\ConfiguracionHistorial;
use app\models\DatosGeneralesForm;
use yii\data\ActiveDataProvider;

/**
 * Datos generales de la página: una sola fila en la tabla configuracion.
 * Se guarda con updateAttributes() para no depender de las reglas viejas del modelo Configuracion.
 */
class ConfiguracionService implements InterfaceConfiguracion
{
    private function fila(): Configuracion
    {
        $conf = Configuracion::find()->orderBy('idconfig')->one();
        if ($conf === null) {
            throw new \yii\web\ServerErrorHttpException('Falta la fila de configuración en la base de datos.');
        }
        return $conf;
    }

    public function formulario(): DatosGeneralesForm
    {
        $conf = $this->fila();
        $form = new DatosGeneralesForm();
        foreach (DatosGeneralesForm::COLUMNAS as $atributo => $columna) {
            $form->$atributo = (string) $conf->getAttribute($columna);
        }
        return $form;
    }

    public function guardar(DatosGeneralesForm $form, array $post): bool
    {
        if (!$form->load($post) || !$form->validate()) {
            return false;
        }
        $conf = $this->fila();
        $cambios = [];
        $detalle = [];
        foreach (DatosGeneralesForm::COLUMNAS as $atributo => $columna) {
            $antes = (string) $conf->getAttribute($columna);
            $despues = (string) $form->$atributo;
            if ($antes === $despues) {
                continue;
            }
            $cambios[$columna] = $despues;
            $etiqueta = $form->getAttributeLabel($atributo);
            if ($atributo === 'mapa') {
                $detalle[] = $etiqueta . ': ' . ($despues === '' ? 'quitado' : ($antes === '' ? 'agregado' : 'actualizado'));
            } else {
                $detalle[] = $etiqueta . ': ' . ($antes === '' ? '(vacío)' : $antes) . ' → ' . ($despues === '' ? '(vacío)' : $despues);
            }
        }
        if ($cambios) {
            $conf->updateAttributes($cambios);
            ConfiguracionHistorial::registrar(implode("\n", $detalle));
        }
        return true;
    }

    public function historial()
    {
        return new ActiveDataProvider([
            'query' => ConfiguracionHistorial::find()->with('usuarioActor')->orderBy(['idhistorial' => SORT_DESC]),
            'pagination' => ['pageSize' => 10, 'pageParam' => 'hpage'],
        ]);
    }

    /** Íconos de Font Awesome de cada red => columna de la tabla configuracion. */
    const REDES = [
        'fa-facebook-f'  => 'facebook',
        'fa-instagram'   => 'instagram',
        'fa-linkedin-in' => 'LinkedIn',
        'fa-x-twitter'   => 'twitter',
        'fa-tiktok'      => 'tiktok',
    ];

    public function paraWeb(): array
    {
        $fila = Configuracion::find()->orderBy('idconfig')->asArray()->one() ?? [];
        $datos = array_map(static fn ($v) => trim((string) $v), $fila);
        $redes = [];
        foreach (self::REDES as $icono => $columna) {
            if (($datos[$columna] ?? '') !== '') {
                $redes[$icono] = $datos[$columna];
            }
        }
        return ['datos' => $datos, 'redes' => $redes];
    }
}

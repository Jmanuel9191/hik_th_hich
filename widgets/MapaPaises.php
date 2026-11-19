<?php

namespace app\widgets;

use yii\base\Widget;
use yii\helpers\Html;

/**
 * Mapa de Sudamérica dibujado (SVG en línea). Los países marcados se pintan con la clase "is-activo";
 * los colores los pone el CSS de quien lo use (panel: .huk-mapa, sección 18 de huk-admin.css).
 *
 * Uso:  MapaPaises::widget(['activos' => ['BO' => 'Resources & Development', 'AR' => '…']])
 *       (código => texto que aparece al pasar el mouse)
 */
class MapaPaises extends Widget
{
    /** Código ISO => nombre, para los selectores. */
    const PAISES = [
        'AR' => 'Argentina',
        'BO' => 'Bolivia',
        'BR' => 'Brasil',
        'CL' => 'Chile',
        'CO' => 'Colombia',
        'EC' => 'Ecuador',
        'GF' => 'Guayana Francesa',
        'GY' => 'Guyana',
        'PY' => 'Paraguay',
        'PE' => 'Perú',
        'SR' => 'Surinam',
        'UY' => 'Uruguay',
        'VE' => 'Venezuela',
    ];

    /** @var array código => texto (los países pintados) */
    public $activos = [];
    /** @var array atributos extra del <svg> */
    public $options = [];

    public static function nombre($codigo)
    {
        return self::PAISES[$codigo] ?? $codigo;
    }

    public function run()
    {
        $mapa = require __DIR__ . '/mapa/sudamerica.php';
        $paths = '';
        foreach ($mapa['paises'] as $codigo => $d) {
            $activo = array_key_exists($codigo, $this->activos);
            $titulo = self::nombre($codigo) . ($activo && $this->activos[$codigo] !== '' ? ' · ' . $this->activos[$codigo] : '');
            $paths .= Html::tag('path', Html::tag('title', Html::encode($titulo)), [
                'd' => $d,
                'class' => 'huk-mapa-pais' . ($activo ? ' is-activo' : ''),
                'data-pais' => $codigo,
            ]);
        }
        return Html::tag('svg', $paths, array_merge([
            'xmlns' => 'http://www.w3.org/2000/svg',
            'viewBox' => $mapa['viewBox'],
            'class' => 'huk-mapa',
            'role' => 'img',
            'aria-label' => 'Mapa de Sudamérica',
        ], $this->options));
    }
}

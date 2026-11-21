<?php

namespace app\widgets;

use yii\helpers\Html;

/**
 * Zona para subir archivos con el formato HUK (el mismo look que CampoImagen): recuadro punteado
 * "Arrastre el archivo aquí / o haga clic para elegirlo", la ayuda (formatos y peso), lo elegido con vista previa
 * (miniatura si es imagen, ícono si es PDF, video u otro) y los botones "Elegir archivo" / "Quitar".
 * Envuelve el <input type=file> de siempre (queda encima de la zona, invisible): el formulario, la validación
 * y los JS que ya leían ese input siguen funcionando igual. Comportamiento: assets_b/js/huk-zona.js.
 *
 * Con ActiveForm:   $form->field($model, 'comprobante', ['template' => ZonaArchivo::plantilla('PDF o foto · hasta 10 MB')])
 *                       ->fileInput(['accept' => '.pdf,.jpg'])
 * Con un input suelto:  ZonaArchivo::envolver(Html::fileInput('documento', null, [...]), 'PDF o foto · hasta 10 MB')
 *
 * Opciones: 'titulo' (texto grande), 'compacta' (más baja, para cajas chicas), 'lista' => false (si la pantalla
 * ya muestra su propia lista de archivos, como Avances), 'multiple' (textos en plural), 'botones' => false (sin
 * Elegir / Quitar: cuando la pantalla ya tiene los suyos, como el editor de la página del proyecto).
 */
class ZonaArchivo
{
    public static function plantilla(string $ayuda, array $opciones = []): string
    {
        return "{label}\n" . self::abrir($ayuda, $opciones) . '{input}' . self::cerrar($ayuda, $opciones) . "\n{hint}\n{error}";
    }

    public static function envolver(string $input, string $ayuda, array $opciones = []): string
    {
        return self::abrir($ayuda, $opciones) . $input . self::cerrar($ayuda, $opciones);
    }

    private static function abrir(string $ayuda, array $o): string
    {
        \app\assets_b\ZonaArchivoAsset::register(\Yii::$app->view);
        $clases = 'huk-zona' . (!empty($o['compacta']) ? ' is-compacta' : '');
        return Html::beginTag('div', ['class' => $clases, 'data-huk-zona' => true, 'data-lista' => ($o['lista'] ?? true) ? 1 : 0])
            . Html::beginTag('div', ['class' => 'huk-zona-caja']);
    }

    private static function cerrar(string $ayuda, array $o): string
    {
        $varios = !empty($o['multiple']);
        $titulo = $o['titulo'] ?? ($varios ? 'Arrastre los archivos aquí' : 'Arrastre el archivo aquí');
        $texto = Html::tag('div',
            '<i class="fa fa-cloud-upload" aria-hidden="true"></i>'
            . Html::tag('strong', Html::encode($titulo))
            . Html::tag('span', $varios ? 'o haga clic para elegirlos' : 'o haga clic para elegirlo')
            . ($ayuda !== '' ? Html::tag('small', Html::encode($ayuda)) : ''),
            ['class' => 'huk-zona-texto']);
        $botones = Html::tag('div',
            Html::button('<i class="fa fa-folder-open"></i> <span>' . ($varios ? 'Elegir archivos' : 'Elegir archivo') . '</span>',
                ['class' => 'btn btn-default', 'data-huk-zona-elegir' => true, 'data-texto' => $varios ? 'Elegir archivos' : 'Elegir archivo',
                    'data-texto-cambiar' => $varios ? 'Elegir otros' : 'Cambiar archivo'])
            . Html::button('<i class="fa fa-trash"></i> Quitar', ['class' => 'btn btn-default huk-zona-quitar', 'data-huk-zona-quitar' => true]),
            ['class' => 'huk-zona-botones']);
        return $texto . Html::tag('div', '', ['class' => 'huk-zona-elegidos', 'data-huk-zona-lista' => true, 'aria-live' => 'polite'])
            . Html::endTag('div') . (($o['botones'] ?? true) ? $botones : '') . Html::endTag('div');
    }
}

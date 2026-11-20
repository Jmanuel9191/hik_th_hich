<?php

namespace app\components;

use yii\helpers\HtmlPurifier;

/**
 * Limpia el HTML que llega del editor (TinyMCE): deja formato, tablas y colores,
 * y saca scripts, eventos y cualquier cosa peligrosa. Se usa en Ecosistemas y Equipo.
 */
class HtmlEditor
{
    public static function limpiar($html)
    {
        return HtmlPurifier::process((string) $html, function ($config) {
            $config->set('HTML.Allowed',
                'p[style],br,strong,b,em,i,u,s,sub,sup,span[style],div[style],h2[style],h3[style],h4[style],'
                . 'ul[style],ol[style],li[style],blockquote,hr,a[href|title|target],'
                . 'table[style|border|cellpadding|cellspacing|width],caption,thead,tbody,tfoot,tr[style],'
                . 'th[style|colspan|rowspan|width],td[style|colspan|rowspan|width],colgroup,col[style|span|width]');
            $config->set('CSS.AllowedProperties', [
                'font-size', 'font-weight', 'font-style', 'font-family', 'text-decoration', 'color', 'background-color',
                'text-align', 'vertical-align', 'white-space', 'width', 'height', 'padding', 'padding-left',
                'padding-right', 'padding-top', 'padding-bottom', 'margin-left', 'border', 'border-collapse',
                'border-top', 'border-bottom', 'border-left', 'border-right', 'border-color', 'border-width', 'border-style',
            ]);
            $config->set('Attr.AllowedFrameTargets', ['_blank']);
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
            $config->set('AutoFormat.RemoveEmpty', false);
        });
    }

    /**
     * Versión corta para los bloques de la página de proyecto: solo párrafos, subtítulos, negrita, cursiva,
     * listas y enlaces (sin estilos ni tablas: el diseño lo pone la web). Un texto vacío devuelve ''.
     */
    public static function limpiarSimple($html)
    {
        $limpio = HtmlPurifier::process((string) $html, function ($config) {
            $config->set('HTML.Allowed', 'p,br,strong,b,em,i,u,h3,h4,ul,ol,li,a[href|title|target]');
            $config->set('Attr.AllowedFrameTargets', ['_blank']);
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
            $config->set('AutoFormat.RemoveEmpty', true);
        });
        $plano = trim(html_entity_decode(strip_tags($limpio), ENT_QUOTES | ENT_HTML5, 'UTF-8'), " \t\n\r\0\x0B\xC2\xA0");
        return $plano === '' ? '' : trim($limpio);
    }
}

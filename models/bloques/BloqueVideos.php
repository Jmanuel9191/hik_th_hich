<?php

namespace app\models\bloques;

use app\models\ProyectoVideo;

/**
 * Videos: los de la pestaña "Videos" (todos o los elegidos) y/o enlaces de YouTube o Vimeo pegados acá.
 * El primero se ve grande; con varios, miniaturas abajo para cambiar. Los archivos se ven pero no se descargan.
 */
class BloqueVideos extends Bloque
{
    const TIPO = 'videos';
    const NOMBRE = 'Videos';
    const DESCRIPCION = 'Videos de la pestaña Videos y/o enlaces de YouTube o Vimeo.';
    const GRUPO = 'proyecto';
    const ICONO = 'film';
    const AUTOMATICO = true;

    public static function campos(): array
    {
        return [
            'titulo' => ['tipo' => 'texto', 'etiqueta' => 'Título', 'max' => 80, 'defecto' => 'Videos'],
            'mostrar' => ['tipo' => 'opciones', 'etiqueta' => 'Qué videos de la pestaña', 'opciones' => [
                'todos' => 'Todos (también los que se suban después)', 'elegidos' => 'Solo los que marco', 'ninguno' => 'Ninguno (solo los enlaces de abajo)',
            ]],
            'videos' => ['tipo' => 'elegir', 'etiqueta' => 'Videos', 'fuente' => 'videos', 'si' => 'mostrar:elegidos'],
            'enlaces' => ['tipo' => 'lista', 'etiqueta' => 'Enlaces de YouTube o Vimeo (opcional)', 'item' => 'Enlace', 'maxItems' => 8, 'campos' => [
                'titulo' => ['tipo' => 'texto', 'etiqueta' => 'Título', 'requerido' => true, 'max' => 100],
                'url' => ['tipo' => 'enlace', 'etiqueta' => 'Enlace', 'requerido' => true, 'max' => 300, 'placeholder' => 'https://youtu.be/…',
                    'ayuda' => 'Si el video es confidencial, en YouTube tiene que estar como «No listado».'],
            ]],
        ];
    }

    /**
     * Lo que se reproduce, en orden: los videos de la pestaña (todos o los marcados) y después los enlaces de acá.
     * Cada uno: ['titulo', 'archivo' => ProyectoVideo|null (archivo subido), 'embed' => URL de YouTube/Vimeo|null, 'miniatura' => URL|null].
     * @param ProyectoVideo[] $todos
     */
    public function lista(array $todos): array
    {
        $lista = [];
        $deLaPestana = $this->mostrar === 'ninguno' ? [] : $this->elegidosDe($todos, 'videos', 'idvideo');
        foreach ($deLaPestana as $v) {
            if ($v->tipo === 'archivo') {
                $lista[] = ['titulo' => $v->titulo, 'archivo' => $v, 'embed' => null, 'miniatura' => null];
            } elseif (($embed = ProyectoVideo::embed($v->url)) !== null) {
                $lista[] = ['titulo' => $v->titulo, 'archivo' => null, 'embed' => $embed, 'miniatura' => $v->miniaturaEnlace()];
            }
        }
        foreach ($this->enlaces as $e) {
            if (($embed = ProyectoVideo::embed($e['url'])) !== null) {
                $tmp = new ProyectoVideo(['tipo' => 'enlace', 'url' => $e['url']]);
                $lista[] = ['titulo' => $e['titulo'], 'archivo' => null, 'embed' => $embed, 'miniatura' => $tmp->miniaturaEnlace()];
            }
        }
        return $lista;
    }

    protected function reglas(): array
    {
        return [[['enlaces'], function ($a) {
            if ($this->mostrar === 'ninguno' && !$this->enlaces) {
                $this->addError($a, 'Pegue al menos un enlace, o elija mostrar los videos de la pestaña.');
            }
            foreach ($this->enlaces as $i => $e) {
                if (ProyectoVideo::embed($e['url']) === null) {
                    $this->addError($a, 'Enlace ' . ($i + 1) . ': tiene que ser de YouTube o Vimeo (ej. https://youtu.be/… o https://vimeo.com/…).');
                }
            }
        }]];
    }
}

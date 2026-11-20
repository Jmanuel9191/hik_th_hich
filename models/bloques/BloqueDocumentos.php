<?php

namespace app\models\bloques;

/**
 * Documentos para descargar: los de la pestaña "Documentos" marcados "lo ven los inversores" (todos o los elegidos).
 * Los PDF y las fotos se abren en el navegador; Word, Excel y los demás se descargan. Cada descarga queda registrada.
 */
class BloqueDocumentos extends Bloque
{
    const TIPO = 'documentos';
    const NOMBRE = 'Documentos para descargar';
    const DESCRIPCION = 'Los documentos visibles para el inversor (todos o los que elija).';
    const GRUPO = 'proyecto';
    const ICONO = 'download';
    const AUTOMATICO = true;

    public static function campos(): array
    {
        return [
            'titulo' => ['tipo' => 'texto', 'etiqueta' => 'Título', 'max' => 80, 'defecto' => 'Documentos del proyecto'],
            'texto' => ['tipo' => 'parrafo', 'etiqueta' => 'Texto (opcional)', 'max' => 400],
            'mostrar' => ['tipo' => 'opciones', 'etiqueta' => 'Qué documentos', 'opciones' => [
                'todos' => 'Todos los visibles (también los que se suban después)', 'elegidos' => 'Solo los que marco',
            ]],
            'documentos' => ['tipo' => 'elegir', 'etiqueta' => 'Documentos', 'fuente' => 'documentos', 'si' => 'mostrar:elegidos'],
        ];
    }

    /** @param \app\models\ProyectoDocumento[] $todos  @return \app\models\ProyectoDocumento[] */
    public function documentosDe(array $todos): array
    {
        return $this->elegidosDe($todos, 'documentos', 'iddocumento');
    }
}

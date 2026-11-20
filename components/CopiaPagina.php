<?php

namespace app\components;

use app\models\bloques\Catalogo;
use app\models\ProyectoBloque;
use app\models\ProyectoSeccion;
use Yii;
use yii\helpers\FileHelper;

/**
 * Copia secciones y bloques (con sus fotos, con nombre nuevo) entre proyectos y plantillas.
 * La usan PaginaProyectoService (duplicar, aplicar plantilla, guardar como plantilla) y PlantillaService (duplicar).
 */
final class CopiaPagina
{
    const CAMPOS_SECCION = ['orden', 'antetitulo', 'titulo', 'menu', 'ancla', 'fondo', 'diseno', 'en_menu', 'numerada', 'visible'];

    /**
     * @param array $destino ['idproyecto' => …] o ['idplantilla' => …]
     * @param array $cambios lo que cambia en la copia (ej. orden, ancla)
     */
    public static function seccion(ProyectoSeccion $s, array $destino, array $cambios, string $carpetaOrigen, string $carpetaDestino): ProyectoSeccion
    {
        $copia = new ProyectoSeccion($destino + $cambios + array_intersect_key($s->getAttributes(), array_flip(self::CAMPOS_SECCION)));
        $copia->fecha_registro = date('Y-m-d H:i:s');
        $copia->save(false);
        foreach ($s->bloques as $j => $b) {
            self::bloque($b, $copia->idseccion, $j + 1, $carpetaOrigen, $carpetaDestino);
        }
        return $copia;
    }

    public static function bloque(ProyectoBloque $b, int $idseccion, int $orden, string $carpetaOrigen, string $carpetaDestino): ProyectoBloque
    {
        $copia = new ProyectoBloque([
            'idseccion' => $idseccion, 'orden' => $orden, 'tipo' => $b->tipo, 'ancho' => $b->ancho,
            'datos' => $b->datos, 'visible' => $b->visible, 'fecha_registro' => date('Y-m-d H:i:s'),
        ]);
        $contenido = $b->contenido();
        if ($contenido && ($fotos = $contenido->imagenes())) {
            $mapa = [];
            foreach ($fotos as $f) {
                if (($nueva = self::foto($carpetaOrigen, $carpetaDestino, $f)) !== null) {
                    $mapa[$f] = $nueva;
                }
            }
            $nuevo = Catalogo::crear($b->tipo, $contenido->datos());
            $nuevo->renombrarImagenes($mapa);
            $copia->setContenido($nuevo);
        }
        $copia->save(false);
        return $copia;
    }

    /** Copia una foto con nombre nuevo. null si la original ya no está. */
    public static function foto(string $origen, string $destino, string $archivo): ?string
    {
        if (basename($archivo) !== $archivo || !is_file($origen . '/' . $archivo)) {
            return null;
        }
        FileHelper::createDirectory($destino, 0755);
        $nuevo = Yii::$app->security->generateRandomString(24) . '.' . strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
        return copy($origen . '/' . $archivo, $destino . '/' . $nuevo) ? $nuevo : null;
    }
}

<?php

namespace app\models\bloques;

use yii\base\InvalidArgumentException;

/**
 * Catálogo de bloques de la página de proyecto. Para sumar un tipo:
 *  1. clase en models/bloques (extiende Bloque, declara campos()),
 *  2. vista en views/proyectos/bloques/<tipo>.php,
 *  3. estilos en assets_b/css/huk-proyecto.css y miniatura en modules/admin/views/paginas-proyecto/_miniaturas.php,
 *  4. agregarlo acá.
 */
final class Catalogo
{
    const GRUPOS = [
        'basicos' => 'Básicos',
        'datos' => 'Datos',
        'tarjetas' => 'Tarjetas',
        'pasos' => 'Pasos y listas',
        'proyecto' => 'De las pestañas del proyecto',
    ];

    const TIPOS = [
        // Básicos
        'texto' => BloqueTexto::class,
        'introduccion' => BloqueIntroduccion::class,
        'recuadro' => BloqueRecuadro::class,
        'imagen' => BloqueImagen::class,
        'galeria' => BloqueGaleria::class,
        'nota' => BloqueNota::class,
        'aviso' => BloqueAviso::class,
        'botones' => BloqueBotones::class,
        'fuentes' => BloqueFuentes::class,
        // Datos
        'datos' => BloqueDatos::class,
        'cifras' => BloqueCifras::class,
        'grafico' => BloqueGrafico::class,
        'tabla' => BloqueTabla::class,
        'etiquetas' => BloqueEtiquetas::class,
        // Tarjetas
        'tarjetas' => BloqueTarjetas::class,
        'tarjetas_foto' => BloqueTarjetasFoto::class,
        'minerales' => BloqueMinerales::class,
        'ficha_destacada' => BloqueFichaDestacada::class,
        // Pasos y listas
        'lista_numerada' => BloqueListaNumerada::class,
        'linea_tiempo' => BloqueLineaTiempo::class,
        'proceso' => BloqueProceso::class,
        'dos_aportes' => BloqueDosAportes::class,
        'pasos_finales' => BloquePasosFinales::class,
        'hitos' => BloqueHitos::class,
        'sello' => BloqueSello::class,
        // De las pestañas del proyecto
        'galeria_proyecto' => BloqueGaleriaProyecto::class,
        'videos' => BloqueVideos::class,
        'documentos' => BloqueDocumentos::class,
    ];

    public static function crear(string $tipo, array $datos = []): Bloque
    {
        if (!isset(self::TIPOS[$tipo])) {
            throw new InvalidArgumentException('Tipo de bloque desconocido: ' . $tipo);
        }
        $clase = self::TIPOS[$tipo];
        return new $clase($datos);
    }

    public static function nombre(string $tipo): string
    {
        return isset(self::TIPOS[$tipo]) ? (self::TIPOS[$tipo])::NOMBRE : $tipo;
    }

    /** grupo => [tipo => clase], en el orden de GRUPOS. */
    public static function porGrupo(): array
    {
        $grupos = array_fill_keys(array_keys(self::GRUPOS), []);
        foreach (self::TIPOS as $tipo => $clase) {
            $grupos[$clase::GRUPO][$tipo] = $clase;
        }
        return array_filter($grupos);
    }
}

<?php

namespace app\components;

use app\models\Noticia;

/**
 * Blog: noticias (panel y web). Las categorías: InterfaceNoticiaCategorias.
 */
interface InterfaceNoticias
{
    /* ---------- Panel ---------- */

    /** @return array ['searchModel', 'dataProvider', 'categorias' => id => nombre] */
    public function listar($params);

    /** Noticia o 404. */
    public function buscar($id): Noticia;

    /** Crea o edita (portada, imagen de contenido y texto incluidos). @return array ['exito' => bool, 'mensaje' => string] */
    public function guardar(Noticia $model, array $post);

    public function cambiarEstado($id);

    /** Marca o quita "Destacada" (carrusel del Inicio). */
    public function cambiarDestacado($id);

    /** Borra la noticia con sus imágenes y su galería. */
    public function eliminar($id);

    /** Historial de una noticia. @return \yii\data\ActiveDataProvider */
    public function historial($id);

    /** Kartik FileInput: [] = ok, ['error' => '...'] = falló. */
    public function subirGaleria($id);

    public function borrarImagenGaleria($idimagen);

    /* ---------- Web ---------- */

    /**
     * Listado público: publicadas, 12 por página, con búsqueda y filtro de categoría.
     * @return array ['dataProvider', 'categoria' => NoticiaCategoria|null, 'busqueda' => string]
     */
    public function paraWeb(?string $busqueda, ?string $categoria): array;

    /** Noticia publicada por su dirección, con categoría y galería, o 404. */
    public function porSlug(string $slug): Noticia;

    /** Barra lateral: ['categorias' => [[NoticiaCategoria, cantidad]], 'recientes' => Noticia[]] */
    public function barraLateral(): array;

    /** Destacadas publicadas (carrusel del Inicio); si no hay ninguna marcada, las últimas publicadas. @return Noticia[] */
    public function destacadas(int $limite = 9): array;
}

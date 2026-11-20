<?php

namespace app\components;

use app\models\BlogHistorial;
use app\models\Noticia;
use app\models\NoticiaCategoria;
use app\models\NoticiaImagen;
use app\models\NoticiaSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * Blog: noticias. Los permisos los revisa Admin\NoticiasController.
 * Imágenes en imagenes/noticias/<id>/ (SubidaImagen); descripción limpia con HtmlEditor.
 */
class NoticiaService implements InterfaceNoticias
{
    const CAMPOS = ['titulo', 'slug', 'resumen', 'fecha', 'idcategoria'];
    const POR_PAGINA = 12;

    /* ================= Panel ================= */

    public function listar($params)
    {
        $search = new NoticiaSearch();
        return ['searchModel' => $search, 'dataProvider' => $search->search($params), 'categorias' => NoticiaCategoria::lista()];
    }

    public function buscar($id): Noticia
    {
        $model = Noticia::findOne((int) $id);
        if ($model === null) {
            throw new NotFoundHttpException('Noticia no encontrada.');
        }
        return $model;
    }

    public function guardar(Noticia $model, array $post)
    {
        $nuevo = $model->isNewRecord;
        $antes = $model->getAttributes();
        $datos = $post['Noticia'] ?? [];
        $model->setAttributes(array_intersect_key($datos, array_flip(self::CAMPOS)), false);
        if (array_key_exists('descripcion', $datos)) {
            $html = HtmlEditor::limpiar($datos['descripcion']);
            $texto = trim(preg_replace('/[\s\x{00A0}]+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            $model->descripcion = $texto === '' && stripos($html, '<img') === false ? '' : $html; // un editor "vacío" deja <p>&nbsp;</p>
        }

        // Imágenes: nueva, quitar o dejar como está
        $nuevas = [];
        foreach (array_keys(Noticia::IMAGENES) as $campo) {
            $f = UploadedFile::getInstance($model, $campo);
            $nuevas[$campo] = $f !== null && $f->error !== UPLOAD_ERR_NO_FILE ? $f : null;
        }
        $quitar = array_keys(array_filter($datos['quitar'] ?? []));
        $valido = $model->validate();
        if ($nuevas['imagen_portada'] === null && (!$model->imagen_portada || in_array('imagen_portada', $quitar, true))) {
            $model->addError('imagen_portada', 'La portada es obligatoria: sale en el listado y al compartir la noticia.');
            $valido = false;
        }
        if (!$valido) {
            return ['exito' => false, 'mensaje' => 'Revise los campos marcados.'];
        }

        if ((string) $model->fecha === '') {
            $model->fecha = $nuevo ? date('Y-m-d') : date('Y-m-d', strtotime($model->fecha_registro));
        }
        $tx = Yii::$app->db->beginTransaction();
        if ($nuevo) {
            $model->estado = '1';
            $model->destacado = '0';
            $model->imagen_portada = (string) $model->imagen_portada; // se completa al subir
            $model->idusuario_registro = Yii::$app->user->id;
            $model->fecha_registro = date('Y-m-d H:i:s');
        } else {
            $model->fecha_actualizacion = date('Y-m-d H:i:s');
        }
        $model->save(false); // ya tiene id: las imágenes van a su carpeta

        $subidas = [];
        $borrar = [];
        $cambiosImagen = [];
        foreach (Noticia::IMAGENES as $campo => [$etiqueta]) {
            if ($nuevas[$campo] !== null) {
                $r = SubidaImagen::guardar($nuevas[$campo], $model->carpeta(), 8);
                if (!$r['ok']) {
                    $tx->rollBack();
                    foreach ($subidas as $archivo) {
                        SubidaImagen::borrar($model->carpeta(), $archivo);
                    }
                    if ($nuevo) {
                        $model->setIsNewRecord(true);
                        $model->idnoticia = null;
                    }
                    $model->addError($campo, $r['error']);
                    return ['exito' => false, 'mensaje' => 'Revise los campos marcados.'];
                }
                $subidas[] = $r['archivo'];
                if ($model->$campo) {
                    $borrar[] = $model->$campo;
                }
                $cambiosImagen[] = $etiqueta . ': ' . ($model->$campo ? 'reemplazada' : 'subida');
                $model->$campo = $r['archivo'];
            } elseif (in_array($campo, $quitar, true) && $model->$campo) {
                $borrar[] = $model->$campo;
                $cambiosImagen[] = $etiqueta . ': quitada';
                $model->$campo = null;
            }
        }
        $model->updateAttributes(['imagen_portada' => $model->imagen_portada, 'imagen_contenido' => $model->imagen_contenido]);
        $tx->commit();
        foreach ($borrar as $archivo) {
            SubidaImagen::borrar($model->carpeta(), $archivo);
        }

        if ($nuevo) {
            BlogHistorial::registrar(BlogHistorial::NOTICIA, $model->idnoticia, 'Creación',
                'Noticia creada: ' . $model->titulo . "\nCategoría: " . ($model->categoria->nombre ?? '') . "\nDirección: /noticias/" . $model->slug);
        } else {
            $cambios = [];
            foreach (self::CAMPOS as $campo) {
                $a = (string) $antes[$campo];
                $d = (string) $model->$campo;
                if ($a === $d) {
                    continue;
                }
                if ($campo === 'idcategoria') {
                    $a = NoticiaCategoria::findOne((int) $a)->nombre ?? $a;
                    $d = $model->categoria->nombre ?? $d;
                }
                $cambios[] = $model->getAttributeLabel($campo) . ': ' . ($a === '' ? '(vacío)' : $a) . ' → ' . ($d === '' ? '(vacío)' : $d);
            }
            if ((string) $antes['descripcion'] !== (string) $model->descripcion) {
                $cambios[] = 'Descripción completa: modificada';
            }
            $cambios = array_merge($cambios, $cambiosImagen);
            if ($cambios) {
                BlogHistorial::registrar(BlogHistorial::NOTICIA, $model->idnoticia, 'Edición', implode("\n", $cambios));
            }
        }
        return ['exito' => true, 'mensaje' => $nuevo ? 'Noticia creada y publicada.' : 'Cambios guardados.'];
    }

    public function cambiarEstado($id)
    {
        $model = $this->buscar($id);
        $activar = $model->estado !== '1';
        $model->updateAttributes(['estado' => $activar ? '1' : '0', 'fecha_actualizacion' => date('Y-m-d H:i:s')]);
        BlogHistorial::registrar(BlogHistorial::NOTICIA, $model->idnoticia, $activar ? 'Publicación' : 'Ocultada',
            $activar ? 'Estado: Oculta → Publicada' : 'Estado: Publicada → Oculta (no se muestra en la web)');
        return ['exito' => true, 'mensaje' => $activar ? 'Noticia publicada.' : 'Noticia oculta.'];
    }

    public function cambiarDestacado($id)
    {
        $model = $this->buscar($id);
        $destacar = $model->destacado !== '1';
        $model->updateAttributes(['destacado' => $destacar ? '1' : '0', 'fecha_actualizacion' => date('Y-m-d H:i:s')]);
        BlogHistorial::registrar(BlogHistorial::NOTICIA, $model->idnoticia, 'Destacada',
            $destacar ? 'Marcada como destacada (carrusel del Inicio)' : 'Ya no es destacada');
        return ['exito' => true, 'mensaje' => $destacar ? 'Marcada como destacada.' : 'Ya no es destacada.'];
    }

    public function eliminar($id)
    {
        $model = $this->buscar($id);
        $carpeta = $model->carpeta();
        $titulo = $model->titulo;
        $model->delete(); // la galería se borra sola (clave foránea en cascada)
        FileHelper::removeDirectory($carpeta);
        BlogHistorial::registrar(BlogHistorial::NOTICIA, (int) $id, 'Eliminación', 'Noticia eliminada: ' . $titulo);
        return ['exito' => true, 'mensaje' => 'Noticia eliminada.'];
    }

    public function historial($id)
    {
        return new ActiveDataProvider([
            'query' => BlogHistorial::find()->with('usuarioActor')
                ->where(['tipo' => BlogHistorial::NOTICIA, 'idregistro' => (int) $id])
                ->orderBy(['idhistorial' => SORT_DESC]),
            'pagination' => ['pageSize' => 10, 'pageParam' => 'hpage'],
            'sort' => false,
        ]);
    }

    public function subirGaleria($id)
    {
        $model = $this->buscar($id);
        $f = UploadedFile::getInstanceByName('imagenes') ?? (UploadedFile::getInstancesByName('imagenes')[0] ?? null);
        if ($f === null) {
            return ['error' => 'No llegó ninguna imagen.'];
        }
        $r = SubidaImagen::guardar($f, $model->carpeta() . '/galeria', 8);
        if (!$r['ok']) {
            return ['error' => $r['error']];
        }
        $img = new NoticiaImagen([
            'idnoticia' => $model->idnoticia,
            'archivo' => $r['archivo'],
            'nombre_original' => mb_substr($f->name, 0, 255),
            'orden' => (int) NoticiaImagen::find()->where(['idnoticia' => $model->idnoticia])->max('orden') + 1,
            'idusuario' => Yii::$app->user->id,
            'fecha_registro' => date('Y-m-d H:i:s'),
        ]);
        $img->save(false);
        BlogHistorial::registrar(BlogHistorial::NOTICIA, $model->idnoticia, 'Galería', 'Foto agregada: ' . $img->nombre_original);
        return [];
    }

    public function borrarImagenGaleria($idimagen)
    {
        $img = NoticiaImagen::findOne((int) $idimagen);
        if ($img === null) {
            return ['error' => 'La foto ya no existe.'];
        }
        $model = $this->buscar($img->idnoticia);
        SubidaImagen::borrar($model->carpeta() . '/galeria', $img->archivo);
        $img->delete();
        BlogHistorial::registrar(BlogHistorial::NOTICIA, $model->idnoticia, 'Galería', 'Foto eliminada: ' . $img->nombre_original);
        return [];
    }

    /* ================= Web ================= */

    public function paraWeb(?string $busqueda, ?string $categoria): array
    {
        $query = Noticia::publicadas()->with('categoria');
        $cat = null;
        if ($categoria !== null && $categoria !== '') {
            $cat = NoticiaCategoria::findOne(['slug' => $categoria, 'estado' => '1']);
            if ($cat === null) {
                throw new NotFoundHttpException('Categoría no encontrada.');
            }
            $query->andWhere(['n.idcategoria' => $cat->idcategoria]);
        }
        $busqueda = trim(mb_substr((string) $busqueda, 0, 100));
        if ($busqueda !== '') {
            $query->andWhere(['or', ['like', 'n.titulo', $busqueda], ['like', 'n.resumen', $busqueda]]);
        }
        return [
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'sort' => false,
                'pagination' => ['pageSize' => self::POR_PAGINA, 'forcePageParam' => false, 'pageSizeParam' => false],
            ]),
            'categoria' => $cat,
            'busqueda' => $busqueda,
        ];
    }

    public function porSlug(string $slug): Noticia
    {
        $model = Noticia::publicadas()->with(['categoria', 'imagenes'])->andWhere(['n.slug' => $slug])->one();
        if ($model === null) {
            throw new NotFoundHttpException('Noticia no encontrada.');
        }
        return $model;
    }

    public function barraLateral(): array
    {
        $cantidades = Noticia::find()->select(['COUNT(*)', 'idcategoria'])->where(['estado' => '1'])->groupBy('idcategoria')->indexBy('idcategoria')->column();
        $categorias = [];
        foreach (NoticiaCategoria::find()->where(['estado' => '1'])->orderBy(['orden' => SORT_ASC, 'nombre' => SORT_ASC])->all() as $c) {
            if (!empty($cantidades[$c->idcategoria])) {
                $categorias[] = [$c, (int) $cantidades[$c->idcategoria]];
            }
        }
        return ['categorias' => $categorias, 'recientes' => Noticia::publicadas()->limit(5)->all()];
    }

    public function destacadas(int $limite = 9): array
    {
        $destacadas = Noticia::publicadas()->with('categoria')->andWhere(['n.destacado' => '1'])->limit($limite)->all();
        return $destacadas ?: Noticia::publicadas()->with('categoria')->limit(min($limite, 6))->all(); // si no marcó ninguna: las últimas
    }
}

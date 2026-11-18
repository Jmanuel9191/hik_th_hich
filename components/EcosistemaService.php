<?php

namespace app\components;

use app\models\Ecosistema;
use app\models\EcosistemaHistorial;
use app\models\EcosistemaImagen;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * Ecosistemas: listado, alta/edición con imágenes, estado, galería e historial.
 *
 * Quién puede qué (además de los permisos de la tabla rol_permiso):
 *  - Superadmin y Editor de contenido: todos los ecosistemas.
 *  - Gerente de área: solo los ecosistemas que tiene asignados.
 */
class EcosistemaService implements InterfaceEcosistemas
{
    private $permisos;

    public function __construct(InterfacePermisosService $permisos)
    {
        $this->permisos = $permisos;
    }

    /* ---------------- Acceso ---------------- */

    /** IDs de ecosistemas asignados al usuario logueado (solo aplica a gerentes). */
    private function idsAsignados()
    {
        return (new \yii\db\Query())->select('idecosistema')->from('usuario_ecosistema')
            ->where(['idusuario' => Yii::$app->user->id])->column();
    }

    private function esGerente()
    {
        return (int) (Yii::$app->user->identity->idrol ?? 0) === Rol::GERENTE;
    }

    public function puedeGestionar($ecosistema)
    {
        if (!$this->permisos->tienePermiso('ecosistemas_editar')) {
            return false;
        }
        return !$this->esGerente() || in_array((string) $ecosistema->idecosistema, array_map('strval', $this->idsAsignados()), true);
    }

    public function buscar($id, $permiso = 'ecosistemas_ver')
    {
        $this->permisos->verificarPermiso($permiso);
        $model = Ecosistema::findOne((int) $id);
        if ($model === null) {
            throw new NotFoundHttpException('El ecosistema no existe.');
        }
        if ($this->esGerente() && !in_array((string) $model->idecosistema, array_map('strval', $this->idsAsignados()), true)) {
            throw new ForbiddenHttpException('Este ecosistema no está asignado a tu usuario.');
        }
        return $model;
    }

    /* ---------------- Listado ---------------- */

    public function listar($params)
    {
        $this->permisos->verificarPermiso('ecosistemas_listar');
        $query = Ecosistema::find()->with('imagenes');
        if ($this->esGerente()) {
            $query->andWhere(['idecosistema' => $this->idsAsignados() ?: [0]]);
        }
        $filtro = $params['f'] ?? [];
        $query->andFilterWhere(['like', 'nombre', trim((string) ($filtro['nombre'] ?? ''))]);
        if (isset($filtro['estado']) && in_array($filtro['estado'], ['0', '1'], true)) {
            $query->andWhere(['estado' => $filtro['estado']]);
        }
        return new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['numero' => SORT_ASC], 'attributes' => ['numero', 'nombre', 'fecha_registro']],
            'pagination' => ['pageSize' => 20],
        ]);
    }

    /* ---------------- Crear / Editar ---------------- */

    /** HTML del editor: deja formato y tablas (Excel), saca scripts y todo lo peligroso. */
    /** HTML del editor, limpio (ver HtmlEditor::limpiar). */
    public static function limpiarHtml($html)
    {
        return HtmlEditor::limpiar($html);
    }

    /**
     * Guarda el ecosistema con sus imágenes. $post es Yii::$app->request->post().
     * Imágenes: input file "Ecosistema[campo]"; para quitar una: Ecosistema[quitar][campo] = 1.
     *
     * @return array ['exito' => bool, 'mensaje' => string]
     */
    public function guardar($model, $post)
    {
        $nuevo = $model->isNewRecord;
        $this->permisos->verificarPermiso($nuevo ? 'ecosistemas_crear' : 'ecosistemas_editar');
        if (!$nuevo && !$this->puedeGestionar($model)) {
            throw new ForbiddenHttpException('Este ecosistema no está asignado a tu usuario.');
        }

        $antes = $model->getAttributes();
        $datos = $post['Ecosistema'] ?? [];
        $model->setAttributes([
            'numero' => $datos['numero'] ?? $model->numero,
            'nombre' => trim((string) ($datos['nombre'] ?? $model->nombre)),
            'resumen' => trim((string) ($datos['resumen'] ?? $model->resumen)),
            'descripcion' => self::limpiarHtml($datos['descripcion'] ?? $model->descripcion),
        ], false);

        // Archivos: validar antes de tocar nada
        $archivos = [];
        foreach (array_keys(Ecosistema::IMAGENES) as $campo) {
            $f = UploadedFile::getInstance($model, $campo);
            if ($f !== null && $f->error !== UPLOAD_ERR_NO_FILE) {
                $archivos[$campo] = $f;
            }
        }

        if (!$model->validate()) {
            return ['exito' => false, 'mensaje' => 'Revisá los campos marcados.'];
        }

        if ($nuevo) {
            $model->estado = '1';
            $model->fecha_registro = date('Y-m-d H:i:s');
            $model->idusuario_registro = Yii::$app->user->id;
        } else {
            $model->fecha_actualizacion = date('Y-m-d H:i:s');
        }
        $model->save(false); // necesitamos el id para la carpeta de imágenes

        // Imágenes sueltas: quitar y/o reemplazar
        $cambiosImg = [];
        $quitar = $datos['quitar'] ?? [];
        foreach (Ecosistema::IMAGENES as $campo => $etiqueta) {
            $anterior = $model->$campo;
            if (isset($archivos[$campo])) {
                $r = SubidaImagen::guardar($archivos[$campo], $model->carpeta(), $campo === 'icono' ? 1 : 5, $campo === 'icono');
                if (!$r['ok']) {
                    $model->addError($campo, $r['error']);
                    continue;
                }
                $model->$campo = $r['archivo'];
                SubidaImagen::borrar($model->carpeta(), $anterior);
                $cambiosImg[] = $etiqueta . ': ' . ($anterior ? 'reemplazada' : 'subida');
            } elseif (!empty($quitar[$campo]) && $anterior) {
                $model->$campo = null;
                SubidaImagen::borrar($model->carpeta(), $anterior);
                $cambiosImg[] = $etiqueta . ': eliminada';
            }
        }
        $model->save(false);

        // Historial
        if ($nuevo) {
            EcosistemaHistorial::registrar($model->idecosistema, 'Creación',
                'Ecosistema creado: ' . $model->nombre . ($cambiosImg ? "\n" . implode("\n", $cambiosImg) : ''));
        } else {
            $cambios = [];
            foreach (['numero', 'nombre', 'resumen'] as $campo) {
                if ((string) $antes[$campo] !== (string) $model->$campo) {
                    $cambios[] = $model->getAttributeLabel($campo) . ': ' . $antes[$campo] . ' → ' . $model->$campo;
                }
            }
            if ((string) $antes['descripcion'] !== (string) $model->descripcion) {
                $cambios[] = 'Descripción: modificada';
            }
            $cambios = array_merge($cambios, $cambiosImg);
            if ($cambios) {
                EcosistemaHistorial::registrar($model->idecosistema, 'Edición', implode("\n", $cambios));
            }
        }

        if ($model->hasErrors()) {
            return ['exito' => false, 'mensaje' => 'Se guardaron los datos, pero alguna imagen no se pudo subir. Revisá los avisos.'];
        }
        return ['exito' => true, 'mensaje' => $nuevo ? 'Ecosistema creado.' : 'Cambios guardados.'];
    }

    /* ---------------- Estado ---------------- */

    public function cambiarEstado($id)
    {
        $model = $this->buscar($id, 'ecosistemas_estado');
        $activar = $model->estado !== '1';
        $model->updateAttributes(['estado' => $activar ? '1' : '0', 'fecha_actualizacion' => date('Y-m-d H:i:s')]);
        EcosistemaHistorial::registrar($model->idecosistema, $activar ? 'Activación' : 'Desactivación',
            $activar ? 'Estado: Inactivo → Activo (se muestra en la web)' : 'Estado: Activo → Inactivo (no se muestra en la web)');
        return ['exito' => true, 'mensaje' => $activar ? 'Ecosistema activado.' : 'Ecosistema desactivado.'];
    }

    /* ---------------- Galería ---------------- */

    /** Para Kartik FileInput: [] = ok, ['error' => '...'] = falló. */
    public function subirGaleria($id)
    {
        $model = $this->buscar($id, 'ecosistemas_galeria');
        $f = UploadedFile::getInstanceByName('imagenes') ?? (UploadedFile::getInstancesByName('imagenes')[0] ?? null);
        if ($f === null) {
            return ['error' => 'No llegó ninguna imagen.'];
        }
        $r = SubidaImagen::guardar($f, $model->carpeta() . '/galeria', 8);
        if (!$r['ok']) {
            return ['error' => $r['error']];
        }
        $img = new EcosistemaImagen([
            'idecosistema' => $model->idecosistema,
            'archivo' => $r['archivo'],
            'nombre_original' => mb_substr($f->name, 0, 255),
            'orden' => (int) EcosistemaImagen::find()->where(['idecosistema' => $model->idecosistema])->max('orden') + 1,
            'fecha_registro' => date('Y-m-d H:i:s'),
            'idusuario' => Yii::$app->user->id,
        ]);
        $img->save(false);
        EcosistemaHistorial::registrar($model->idecosistema, 'Galería', 'Imagen agregada: ' . $img->nombre_original);
        return [];
    }

    /* ---------------- Eliminar ---------------- */

    /**
     * Proyectos asignados al ecosistema. Hoy siempre 0: el módulo Proyectos todavía no existe.
     * Cuando se cree la tabla de proyectos (con su columna idecosistema), se cuenta acá y
     * eliminar() deja de permitir borrar ecosistemas con proyectos.
     */
    public function proyectosAsignados($id)
    {
        return 0;
    }

    public function eliminar($id)
    {
        $model = $this->buscar($id, 'ecosistemas_eliminar');
        $n = $this->proyectosAsignados($model->idecosistema);
        if ($n > 0) {
            return ['exito' => false, 'mensaje' => "No se puede eliminar: tiene {$n} proyecto(s) asignado(s). Desactivalo para ocultarlo de la web."];
        }
        $gerentes = (new \yii\db\Query())->from('usuario_ecosistema')->where(['idecosistema' => $model->idecosistema])->count();
        $tx = Yii::$app->db->beginTransaction();
        EcosistemaImagen::deleteAll(['idecosistema' => $model->idecosistema]);
        Yii::$app->db->createCommand()->delete('usuario_ecosistema', ['idecosistema' => $model->idecosistema])->execute();
        EcosistemaHistorial::registrar($model->idecosistema, 'Eliminación', 'Ecosistema eliminado: ' . $model->nombre
            . ($gerentes ? "\nSe quitó de {$gerentes} gerente(s) que lo tenían asignado." : ''));
        $carpeta = $model->carpeta();
        $model->delete();
        $tx->commit();
        \yii\helpers\FileHelper::removeDirectory($carpeta); // imágenes y galería
        return ['exito' => true, 'mensaje' => 'Ecosistema eliminado.'];
    }

    public function borrarImagenGaleria($idimagen)
    {
        $img = EcosistemaImagen::findOne((int) $idimagen);
        if ($img === null) {
            return ['error' => 'La imagen ya no existe.'];
        }
        $model = $this->buscar($img->idecosistema, 'ecosistemas_galeria');
        SubidaImagen::borrar($model->carpeta() . '/galeria', $img->archivo);
        $img->delete();
        EcosistemaHistorial::registrar($model->idecosistema, 'Galería', 'Imagen eliminada: ' . $img->nombre_original);
        return [];
    }

    /* ---------------- Historial ---------------- */

    public function historial($id)
    {
        if (!$this->permisos->tienePermiso('ecosistemas_historial')) {
            return null;
        }
        return new ActiveDataProvider([
            'query' => EcosistemaHistorial::find()->with('usuarioActor')
                ->where(['idecosistema' => (int) $id])
                ->orderBy(['fecha' => SORT_DESC, 'idhistorial' => SORT_DESC]),
            'pagination' => ['pageSize' => 15, 'pageParam' => 'hpage'],
            'sort' => false,
        ]);
    }
}

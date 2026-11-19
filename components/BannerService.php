<?php

namespace app\components;

use app\models\BannerHistorial;
use app\models\Banners;
use app\models\BannersSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * Banners de la portada. Los permisos los revisa BannersController.
 * Imágenes en imagenes/banners/ con nombre aleatorio (SubidaImagen).
 */
class BannerService implements InterfaceBanners
{
    const CAMPOS_TEXTO = ['antetitulo', 'titulo', 'texto', 'boton_texto', 'boton_url', 'fecha_inicio', 'fecha_fin'];

    public function listar($params)
    {
        $search = new BannersSearch();
        return [
            'searchModel' => $search,
            'dataProvider' => $search->search($params),
            // Para no mostrar ↑ en el primero ni ↓ en el último
            'extremos' => [(int) Banners::find()->min('orden'), (int) Banners::find()->max('orden')],
        ];
    }

    public function paraWeb()
    {
        return Banners::vigentes()->all();
    }

    public function buscar($id): Banners
    {
        $model = Banners::findOne((int) $id);
        if ($model === null) {
            throw new NotFoundHttpException('Banner no encontrado.');
        }
        return $model;
    }

    public function guardar(Banners $model, array $post)
    {
        $nuevo = $model->isNewRecord;
        $antes = $model->getAttributes();
        $datos = $post['Banners'] ?? [];
        $model->setAttributes(array_intersect_key($datos, array_flip(self::CAMPOS_TEXTO)), false);

        // Archivos: se validan antes de guardar nada
        $archivos = [];
        foreach (array_keys(Banners::IMAGENES) as $campo) {
            $f = UploadedFile::getInstance($model, $campo);
            if ($f !== null && $f->error !== UPLOAD_ERR_NO_FILE) {
                $archivos[$campo] = $f;
            }
        }
        $quitar = (array) ($datos['quitar'] ?? []);
        $valido = $model->validate();
        if (!isset($archivos['imagen']) && ($nuevo || !empty($quitar['imagen']))) {
            $model->addError('imagen', $nuevo ? 'Subí la imagen del banner.' : 'Subí otra imagen: la de computadora es obligatoria.');
            $valido = false;
        }
        if (!$valido) {
            return ['exito' => false, 'mensaje' => 'Revisá los campos marcados.'];
        }

        // Subir primero; si alguna falla no se guarda nada
        $subidas = [];
        foreach ($archivos as $campo => $f) {
            $r = SubidaImagen::guardar($f, Banners::carpeta(), 5);
            if (!$r['ok']) {
                $model->addError($campo, $r['error']);
                foreach ($subidas as $archivo) {
                    SubidaImagen::borrar(Banners::carpeta(), $archivo);
                }
                return ['exito' => false, 'mensaje' => 'Revisá los campos marcados.'];
            }
            $subidas[$campo] = $r['archivo'];
        }

        $cambiosImg = [];
        $borrar = [];
        foreach (Banners::IMAGENES as $campo => [$etiqueta]) {
            $anterior = $model->$campo;
            if (isset($subidas[$campo])) {
                $model->$campo = $subidas[$campo];
                if ($anterior) {
                    $borrar[] = $anterior;
                }
                $cambiosImg[] = $etiqueta . ': ' . ($anterior ? 'reemplazada' : 'subida');
            } elseif (!empty($quitar[$campo]) && $anterior) { // la obligatoria ya se validó arriba
                $model->$campo = null;
                $borrar[] = $anterior;
                $cambiosImg[] = $etiqueta . ': quitada';
            }
        }

        if ($nuevo) {
            $model->estado = '1';
            $model->orden = (int) Banners::find()->max('orden') + 1;
            $model->idusuario_registro = Yii::$app->user->id;
            $model->fecha_registro = date('Y-m-d H:i:s');
        } else {
            $model->fecha_actualizacion = date('Y-m-d H:i:s');
        }
        $model->save(false);
        foreach ($borrar as $archivo) {
            SubidaImagen::borrar(Banners::carpeta(), $archivo);
        }

        if ($nuevo) {
            BannerHistorial::registrar($model->idbanner, 'Creación', 'Banner creado: ' . $model->nombre());
        } else {
            $cambios = [];
            foreach (self::CAMPOS_TEXTO as $campo) {
                $a = (string) $antes[$campo];
                $d = (string) $model->$campo;
                if ($a !== $d) {
                    $cambios[] = $model->getAttributeLabel($campo) . ': ' . ($a === '' ? '(vacío)' : $a) . ' → ' . ($d === '' ? '(vacío)' : $d);
                }
            }
            $cambios = array_merge($cambios, $cambiosImg);
            if ($cambios) {
                BannerHistorial::registrar($model->idbanner, 'Edición', implode("\n", $cambios));
            }
        }
        return ['exito' => true, 'mensaje' => $nuevo ? 'Banner creado.' : 'Cambios guardados.'];
    }

    public function cambiarEstado($id)
    {
        $model = $this->buscar($id);
        $activar = $model->estado !== '1';
        $model->updateAttributes(['estado' => $activar ? '1' : '0', 'fecha_actualizacion' => date('Y-m-d H:i:s')]);
        BannerHistorial::registrar($model->idbanner, $activar ? 'Activación' : 'Desactivación',
            $activar ? 'Estado: Inactivo → Activo' : 'Estado: Activo → Inactivo (no se muestra en la web)');
        return ['exito' => true, 'mensaje' => $activar ? 'Banner activado.' : 'Banner desactivado.'];
    }

    public function mover($id, $direccion)
    {
        $model = $this->buscar($id);
        $subir = (int) $direccion < 0;
        $vecino = Banners::find()
            ->where([$subir ? '<' : '>', 'orden', $model->orden])
            ->orderBy(['orden' => $subir ? SORT_DESC : SORT_ASC])
            ->one();
        if ($vecino === null) {
            return ['exito' => false, 'mensaje' => $subir ? 'Ya es el primero.' : 'Ya es el último.'];
        }
        $tx = Yii::$app->db->beginTransaction();
        $orden = $model->orden;
        $model->updateAttributes(['orden' => $vecino->orden]);
        $vecino->updateAttributes(['orden' => $orden]);
        $tx->commit();
        BannerHistorial::registrar($model->idbanner, 'Orden', 'Posición: ' . ($subir ? 'subió' : 'bajó') . ' un lugar.');
        return ['exito' => true, 'mensaje' => ''];
    }

    public function eliminar($id)
    {
        $model = $this->buscar($id);
        $nombre = $model->nombre();
        $archivos = [$model->imagen, $model->imagen_movil];
        $model->delete();
        foreach ($archivos as $archivo) {
            SubidaImagen::borrar(Banners::carpeta(), $archivo);
        }
        BannerHistorial::registrar((int) $id, 'Eliminación', 'Banner eliminado: ' . $nombre);
        return ['exito' => true, 'mensaje' => 'Banner eliminado.'];
    }

    public function historial($id)
    {
        return new ActiveDataProvider([
            'query' => BannerHistorial::find()->with('usuarioActor')->where(['idbanner' => (int) $id])->orderBy(['idhistorial' => SORT_DESC]),
            'pagination' => ['pageSize' => 10, 'pageParam' => 'hpage'],
        ]);
    }
}

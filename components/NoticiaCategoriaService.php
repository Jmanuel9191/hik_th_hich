<?php

namespace app\components;

use app\models\BlogHistorial;
use app\models\Noticia;
use app\models\NoticiaCategoria;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * Blog: categorías. Los permisos los revisa Admin\NoticiasCategoriasController (noticias_categorias).
 */
class NoticiaCategoriaService implements InterfaceNoticiaCategorias
{
    public function listar()
    {
        return [
            'dataProvider' => new ActiveDataProvider([
                'query' => NoticiaCategoria::find()->orderBy(['orden' => SORT_ASC, 'idcategoria' => SORT_ASC]),
                'sort' => false,
                'pagination' => ['pageSize' => 50],
            ]),
            'cantidades' => Noticia::find()->select(['COUNT(*)', 'idcategoria'])->groupBy('idcategoria')->indexBy('idcategoria')->column(),
            'extremos' => [(int) NoticiaCategoria::find()->min('orden'), (int) NoticiaCategoria::find()->max('orden')],
        ];
    }

    public function buscar($id): NoticiaCategoria
    {
        $model = NoticiaCategoria::findOne((int) $id);
        if ($model === null) {
            throw new NotFoundHttpException('Categoría no encontrada.');
        }
        return $model;
    }

    public function guardar(NoticiaCategoria $model, array $post)
    {
        $nuevo = $model->isNewRecord;
        $antes = $model->nombre;
        $model->nombre = $post['NoticiaCategoria']['nombre'] ?? '';
        if (!$model->validate()) {
            return ['exito' => false, 'mensaje' => 'Revise los campos marcados.'];
        }
        if ($nuevo) {
            $model->estado = '1';
            $model->orden = (int) NoticiaCategoria::find()->max('orden') + 1;
            $model->idusuario_registro = Yii::$app->user->id;
            $model->fecha_registro = date('Y-m-d H:i:s');
        } else {
            $model->fecha_actualizacion = date('Y-m-d H:i:s');
        }
        $model->save(false);
        if ($nuevo) {
            BlogHistorial::registrar(BlogHistorial::CATEGORIA, $model->idcategoria, 'Creación', 'Categoría creada: ' . $model->nombre);
        } elseif ($antes !== $model->nombre) {
            BlogHistorial::registrar(BlogHistorial::CATEGORIA, $model->idcategoria, 'Edición', 'Nombre: ' . $antes . ' → ' . $model->nombre . "\nDirección: /noticias/categoria/" . $model->slug);
        }
        return ['exito' => true, 'mensaje' => $nuevo ? 'Categoría creada.' : 'Cambios guardados.'];
    }

    public function cambiarEstado($id)
    {
        $model = $this->buscar($id);
        $activar = $model->estado !== '1';
        $model->updateAttributes(['estado' => $activar ? '1' : '0', 'fecha_actualizacion' => date('Y-m-d H:i:s')]);
        BlogHistorial::registrar(BlogHistorial::CATEGORIA, $model->idcategoria, $activar ? 'Activación' : 'Desactivación',
            $model->nombre . ': ' . ($activar ? 'vuelve a salir en la barra lateral' : 'ya no sale en la barra lateral (sus noticias siguen publicadas)'));
        return ['exito' => true, 'mensaje' => $activar ? 'Categoría activada.' : 'Categoría desactivada.'];
    }

    public function mover($id, $direccion)
    {
        $model = $this->buscar($id);
        $subir = (int) $direccion < 0;
        $vecino = NoticiaCategoria::find()->where([$subir ? '<' : '>', 'orden', $model->orden])
            ->orderBy(['orden' => $subir ? SORT_DESC : SORT_ASC])->one();
        if ($vecino === null) {
            return ['exito' => false, 'mensaje' => $subir ? 'Ya es la primera.' : 'Ya es la última.'];
        }
        $tx = Yii::$app->db->beginTransaction();
        $orden = $model->orden;
        $model->updateAttributes(['orden' => $vecino->orden]);
        $vecino->updateAttributes(['orden' => $orden]);
        $tx->commit();
        BlogHistorial::registrar(BlogHistorial::CATEGORIA, $model->idcategoria, 'Orden', $model->nombre . ': ' . ($subir ? 'subió' : 'bajó') . ' un lugar.');
        return ['exito' => true, 'mensaje' => ''];
    }

    public function eliminar($id)
    {
        $model = $this->buscar($id);
        $n = (int) Noticia::find()->where(['idcategoria' => $model->idcategoria])->count();
        if ($n > 0) {
            return ['exito' => false, 'mensaje' => "No se puede eliminar: tiene {$n} noticia(s). Páselas a otra categoría o desactívela."];
        }
        $nombre = $model->nombre;
        $model->delete();
        BlogHistorial::registrar(BlogHistorial::CATEGORIA, (int) $id, 'Eliminación', 'Categoría eliminada: ' . $nombre);
        return ['exito' => true, 'mensaje' => 'Categoría eliminada.'];
    }

    public function historial()
    {
        return new ActiveDataProvider([
            'query' => BlogHistorial::find()->with('usuarioActor')->where(['tipo' => BlogHistorial::CATEGORIA])->orderBy(['idhistorial' => SORT_DESC]),
            'pagination' => ['pageSize' => 10, 'pageParam' => 'hpage'],
            'sort' => false,
        ]);
    }
}

<?php
namespace app\components;

use app\models\Audiencias;
use Yii;
use app\models\ProyectoSucursal;
use app\models\ProyectoSector;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

class CatalogoService implements InterfaceCatalogoService
{
    public function guardarSucursalesProyecto($idproyecto, array $idsucursales)
    {
        // Eliminar relaciones anteriores si existieran
        Yii::$app->db->createCommand()->delete('proyecto_sucursal', ['idproyecto' => $idproyecto])->execute();

        foreach ($idsucursales as $idsucursal) {
            Yii::$app->db->createCommand()->insert('proyecto_sucursal', [
                'idproyecto' => $idproyecto,
                'idsucursal' => $idsucursal,
            ])->execute();
        }
    }

    public function guardarSectoresProyecto($idproyecto, array $idsectores)
    {
        // Eliminar relaciones anteriores si existieran
        Yii::$app->db->createCommand()->delete('proyecto_sector', ['idproyecto' => $idproyecto])->execute();

        foreach ($idsectores as $idsector) {
            Yii::$app->db->createCommand()->insert('proyecto_sector', [
                'idproyecto' => $idproyecto,
                'idsector' => $idsector,
            ])->execute();
        }
    }







    public function guardarSucursalesCampania($idcampania, array $idsucursales)
    {
        // Eliminar relaciones anteriores si existieran
        Yii::$app->db->createCommand()->delete('campania_sucursal', ['idcampania' => $idcampania])->execute();

        foreach ($idsucursales as $idsucursal) {
            Yii::$app->db->createCommand()->insert('campania_sucursal', [
                'idcampania' => $idcampania,
                'idsucursal' => $idsucursal,
            ])->execute();
        }
    }

    public function guardarSectoresCampania($idcampania, array $idsectores)
    {
        // Eliminar relaciones anteriores si existieran
        Yii::$app->db->createCommand()->delete('campania_sector', ['idcampania' => $idcampania])->execute();

        foreach ($idsectores as $idsector) {
            Yii::$app->db->createCommand()->insert('campania_sector', [
                'idcampania' => $idcampania,
                'idsector' => $idsector,
            ])->execute();
        }
    }


    public function guardarAudienciasCampania($idcampania, $audiencias)
    {
        // Eliminar relaciones anteriores si existieran
        Yii::$app->db->createCommand()->delete('campanias_audiencia', ['idcampania' => $idcampania])->execute();

        foreach ($audiencias as $idaudiencia) {
            Yii::$app->db->createCommand()->insert('campanias_audiencia', [
                'idcampania' => $idcampania,
                'idaudiencia' => $idaudiencia,
            ])->execute();
        }
    }

    public function obtenerNombresSucursales(array $ids): array
    {
        return \app\models\Sucursal::find()
            ->select('nombre')
            ->where(['idsucursal' => $ids])
            ->column();
    }

    public function obtenerNombresSectores(array $ids): array
    {
        return \app\models\Sector::find()
            ->select('nombre')
            ->where(['idsector' => $ids])
            ->column();
    }



    public function obtenerNombresAudiencias($ids): array
    {
        return Audiencias::find()
            ->select('nombre')
            ->where(['idaudiencia' => $ids])
            ->column();
    }





    private function getModelClassByTipo($tipo)
    {
        return match ($tipo) {
            'sucursal' => \app\models\Sucursal::class,
            'sector' => \app\models\Sector::class,
            'categoria_servicio' => \app\models\CategoriasServicio::class,
            'audiencia' => \app\models\Audiencias::class,
            'canal_marketing' => \app\models\CanalesMarketing::class,
            'tipo_tarea' => \app\models\TipoTareas::class,
            'rol' => \app\models\Roles::class,
            default => throw new NotFoundHttpException('Catálogo no válido'),
        };
    }

    public function listar($tipo, $queryParams)
    {
        $modelClass = $this->getModelClassByTipo($tipo);
        $searchModelClass = $modelClass . 'Search';
        $searchModel = class_exists($searchModelClass) ? new $searchModelClass() : null;

        $dataProvider = $searchModel
            ? $searchModel->search($queryParams)
            : new ActiveDataProvider(['query' => $modelClass::find()]);

        return [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ];
    }

    public function crear($tipo, $post)
    {
        $modelClass = $this->getModelClassByTipo($tipo);
        $model = new $modelClass();

        if ($model->load($post) && $model->validate() && $model->save()) {
            // Solo si el tipo es rol => guardar los permisos seleccionados
            if ($tipo == 'rol' && !empty($model->permisos)) {
                foreach ($model->permisos as $idpermiso) {
                    Yii::$app->db->createCommand()->insert('rol_permiso', [
                        'idrol' => $model->idrol,
                        'idpermiso' => $idpermiso,
                    ])->execute();
                }
            }

            Yii::$app->session->setFlash('success', 'Registro creado correctamente.');
            return ['exito' => true];
        }

        return ['exito' => false, 'model' => $model];
    }


    public function obtener($tipo, $id)
    {
        $modelClass = $this->getModelClassByTipo($tipo);
        $model = $modelClass::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException('Elemento no encontrado.');
        }

        return $model;
    }

    public function actualizar($tipo, $id, $post)
    {
        $modelClass = $this->getModelClassByTipo($tipo);
        $model = $modelClass::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException('Elemento no encontrado.');
        }

        if ($model->load($post) && $model->validate() && $model->save()) {
            // Solo si el tipo es rol => actualizar permisos
            if ($tipo == 'rol') {
                Yii::$app->db->createCommand()->delete('rol_permiso', ['idrol' => $model->idrol])->execute();

                if (!empty($model->permisos)) {
                    foreach ($model->permisos as $idpermiso) {
                        Yii::$app->db->createCommand()->insert('rol_permiso', [
                            'idrol' => $model->idrol,
                            'idpermiso' => $idpermiso,
                        ])->execute();
                    }
                }
            }

            Yii::$app->session->setFlash('success', 'Registro actualizado correctamente.');
            return ['exito' => true];
        }

        return ['exito' => false, 'model' => $model];
    }


    public function eliminar($tipo, $id)
    {
        $modelClass = $this->getModelClassByTipo($tipo);
        $model = $modelClass::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException('Elemento no encontrado.');
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Registro eliminado correctamente.');
    }


    public function limpiarFlashes()
    {
        Yii::$app->session->removeFlash('flash'); // <-- este es el importante
        Yii::$app->session->removeFlash('success');
        Yii::$app->session->removeFlash('error');
        Yii::$app->session->removeFlash('warning');
        Yii::$app->session->removeFlash('info');
    }





}

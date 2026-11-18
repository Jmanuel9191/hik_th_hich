<?php
namespace app\components;

use app\models\HistorialTareasSearch;
use app\models\Tareas;
use app\models\TareasSearch;
use app\models\Usuarios;
use Yii;
use yii\base\ExitException;
use yii\web\NotFoundHttpException;

class TareasService implements InterfaceTarea
{
    private $alertService;


    public function __construct(InterfaceAlert $alertService)
    {
        $this->alertService = $alertService;
    }

     public function obtenerUsuarioSesion()
    {
        $user = Yii::$app->user->identity;
        if (empty($user)) {
            return null;
        }
        $usuario = Usuarios::findOne(['idusuario' => $user['id']]);
        if ($usuario !== null && in_array($usuario->idrol, ['1', '2'])) {
            return $usuario;
        }
        return null;
    }

    /**
     * @throws ExitException
     */
    public function verificarAccesoAdmin()
    {
        $usuario = $this->obtenerUsuarioSesion();
        if ($usuario === null) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para acceder a esta sección.');
            Yii::$app->controller->redirect(Yii::$app->request->referrer ?: ['site/login']);
            Yii::$app->end();
        }
        return $usuario;
    }


    public function listTareas($queryParams)
    {
        $searchModel = new TareasSearch();
        $dataProvider = $searchModel->search($queryParams);
        $dataProvider->setSort([
            'defaultOrder' => ['idtarea' => SORT_DESC]
        ]);
        return [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ];

    }


    public function listHistorial($queryParams, $idTarea = null)
    {
        $searchModel = new HistorialTareasSearch();

        // Filtra por ID de tarea si se proporciona
        if ($idTarea !== null) {
            $queryParams['HistorialTareasSearch']['idtarea'] = $idTarea; // Asegúrate de que coincida con el nombre del campo en tu modelo
        }

        $dataProvider = $searchModel->search($queryParams);

        return [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ];

    }


    public function nuevaTarea($queryParams)
    {

        $model = new Tareas();
        if ($model->load($queryParams) && $model->validate()) {
            if ($model->save()) {
                    $this->alertService->agregarMensajeExito('La tarea ha sido creado correctamente.');
                    return ['exito' => true];

                } else {
                    $this->alertService->agregarMensajeError('Error al crear tarea, inténtelo más tarde.');
                    return ['exito' => false, 'model' => $model];
                }
            }
        return ['exito' => false, 'model' => $model];


       }



    public function actualizarTarea($dates, $id)
    {
        $model = $this->findModel($id);
        $tituloOriginal = $model->titulo;
        $descripcionOriginal = $model->descripcion;
        $moduloOriginal = $model->modulo;

        // Cargamos nombres legibles por si se cambia el módulo
        $moduloNombreOriginal = \app\models\Modulos::findOne($moduloOriginal)->nombre ?? $moduloOriginal;

        if ($model->load($dates) && $model->validate()) {
            if ($model->save(false)) {
                $model->refresh(); // <- esencial para que modulos->nombre esté disponible

                $cambios = [];

                if ($model->titulo !== $tituloOriginal) {
                    $cambios[] = "Título cambiado de '$tituloOriginal' a '$model->titulo'";
                }

                if (trim(strip_tags($model->descripcion)) !== trim(strip_tags($descripcionOriginal))) {
                    $cambios[] = "Descripción modificada.";
                }

                if ((int)$model->modulo !== (int)$moduloOriginal) {
                    $nombreNuevo = $model->modulos->nombre ?? $model->modulo;
                    $cambios[] = "Módulo cambiado de '$moduloNombreOriginal' a '$nombreNuevo'";
                }

                if (!empty($cambios)) {
                    $descripcion = implode("\n", $cambios);
                    $this->registrarHistorial($model->idtarea, 'Edición', $descripcion);
                }

                $this->alertService->agregarMensajeExito('La Tarea ha sido actualizada correctamente.');
                return ['exito' => true];
            } else {
                $this->alertService->agregarMensajeError('Error al actualizar tarea, inténtelo más tarde.');
                return ['exito' => false, 'model' => $model];
            }
        }

        return ['exito' => false, 'model' => $model];
    }





    public function limpiarFlashes()
    {
        Yii::$app->session->removeFlash('flash'); // <-- este es el importante
        Yii::$app->session->removeFlash('success');
        Yii::$app->session->removeFlash('error');
        Yii::$app->session->removeFlash('warning');
        Yii::$app->session->removeFlash('info');
    }



    public function cambiarEstadoTarea($id, $motivo = null)
    {
        $tarea = Tareas::findOne($id);
        if (!$tarea) {
            return [
                'exito' => false,
                'mensaje' => 'Tarea no encontrada.',
            ];
        }


        $estadoAnterior = $tarea->estado;
        $nuevoEstado = $estadoAnterior == '1' ? '0' : '1'; // Alternar estado
        $tarea->estado = $nuevoEstado;
        $tarea->motivobloqueo = $motivo;


        //$estadoAnterior = $tarea->estado;
        //$tarea->estado = $estadoAnterior == '1' ? '0' : '1';
        //$tarea->motivobloqueo = $motivo;

        if ($tarea->save(false)) {
            $mensaje = $tarea->estado == '1'
                ? 'Tarea desbloqueada correctamente.'
                : 'Tarea bloqueada con éxito.';
            return ['exito' => true, 'mensaje' => $mensaje];
        }

        return ['exito' => false, 'mensaje' => 'No se pudo actualizar el estado de la tarea.'];
    }


    public function registrarHistorial($idtarea, $accion, $descripcion = null)
    {
        $historial = new \app\models\HistorialTareas();
        $historial->idtarea = $idtarea;
        $historial->idusuario = Yii::$app->user->identity->idusuario ?? null;
        $historial->accion = $accion;
        $historial->descripcion = $descripcion;
        $historial->save(false);
    }




    /**
     * @throws NotFoundHttpException
     */
    public function findModel($id)
    {
        if (($model = Tareas::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
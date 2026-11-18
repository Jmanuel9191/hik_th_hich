<?php
namespace app\components;

use app\models\DoctoresSearch;
use app\models\HistorialTareasSearch;
use app\models\Doctores;
use app\models\TareasSearch;
use app\models\Usuarios;
use PhpParser\Comment\Doc;
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



    /**
     * @throws ExitException
     */



    public function listTareas($queryParams)
    {
        $searchModel = new DoctoresSearch();
        $dataProvider = $searchModel->search($queryParams);
        $dataProvider->setSort([
            'defaultOrder' => ['iddoctor' => SORT_DESC]
        ]);
        return [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ];

    }


    public function listHistorial($queryParams, $idDoctor = null)
    {
        $searchModel = new HistorialTareasSearch();

        // Filtra por ID de tarea si se proporciona
        if ($idDoctor !== null) {
            $queryParams['HistorialTareasSearch']['iddoctor'] = $idDoctor; // Asegúrate de que coincida con el nombre del campo en tu modelo
        }

        $dataProvider = $searchModel->search($queryParams);

        return [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ];

    }


    public function nuevaTarea($queryParams)
    {

        $model = new Doctores();
        if ($model->load($queryParams) && $model->validate()) {
            $model->fecha_registro = date('Y-m-d H:i:s');
            if ($model->save()) {
                    $this->alertService->agregarMensajeExito('Doctor creado correctamente.');
                    return ['exito' => true];

                } else {
                    $this->alertService->agregarMensajeError('Error al registrar, inténtelo más tarde.');
                    return ['exito' => false, 'model' => $model];
                }
            }
        return ['exito' => false, 'model' => $model];


       }



    public function actualizarTarea($dates, $id)
    {
        $model = $this->findModel($id);
        $tituloOriginal = $model->nombre;
        $descripcionOriginal = $model->descripcion;
        // Cargamos nombres legibles por si se cambia el módulo
        if ($model->load($dates) && $model->validate()) {
            if ($model->save(false)) {
                $model->refresh(); // <- esencial para que modulos->nombre esté disponible

                $this->alertService->agregarMensajeExito('Se ha sido actualizada correctamente.');
                return ['exito' => true];
            } else {
                $this->alertService->agregarMensajeError('Error al actualizar, inténtelo más tarde.');
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
        $tarea = Doctores::findOne($id);
        if (!$tarea) {
            return [
                'exito' => false,
                'mensaje' => 'Doctor no encontrada.',
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
            // Registrar en el historial
            $accion = $nuevoEstado == '1' ? 'Desbloqueo' : 'Bloqueo';
            $descripcion = "Estado cambiado de '" . ($estadoAnterior == '1' ? 'Activo' : 'Bloqueado') .
                "' a '" . ($nuevoEstado == '1' ? 'Activo' : 'Bloqueado') . "'";

            if (!empty($motivo)) {
                $descripcion .= "\nMotivo: " . $motivo;
            }

            $this->registrarHistorial($tarea->iddoctor, $accion, $descripcion);

            $mensaje = $nuevoEstado == '1'
                ? 'Doctor desbloqueado correctamente.'
                : 'Doctor bloqueado con éxito.';

            return ['exito' => true, 'mensaje' => $mensaje];
        }

        return ['exito' => false, 'mensaje' => 'No se pudo actualizar el estado.'];
    }


    public function registrarHistorial($iddoctor, $accion, $descripcion = null)
    {
        $historial = new \app\models\HistorialTareas();
        $historial->iddoctor = $iddoctor;
        $historial->idusuario = Yii::$app->user->identity->idusuario ?? null;
        $historial->accion = $accion;
        $historial->descripcion = $descripcion;
        $historial->fecha = date('Y-m-d H:i:s');
        $historial->save(false);
    }




    /**
     * @throws NotFoundHttpException
     */
    public function findModel($id)
    {
        if (($model = Doctores::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
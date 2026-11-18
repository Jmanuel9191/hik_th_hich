<?php
namespace app\components;

use app\helpers\PermisosGeneralesHelper;
use app\models\Asignacion;
use app\models\Campanias;
use app\models\HistoriaRecursos;
use app\models\Proyectos;
use app\models\Recursos;
use app\models\RecursoSearch;
use Yii;
use yii\base\ExitException;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class RecursosService implements InterfaceRecursos
{
    private $correoService;
    private $notiService;
    public function __construct(InterfaceCorreos $correoService, InterfaceNoti $notiService)
    {
        $this->correoService = $correoService;
        $this->notiService = $notiService;
    }


    public function obtenerRecurso($idrecurso)
    {
        return $this->findModel($idrecurso);
    }

    public function nuevoRecurso($dates, $idasignartarea)
    {
        $model = new Recursos();
        $model->idasignartarea = $idasignartarea;
        $model->idusuario = Yii::$app->user->identity->idusuario;
        $model->fecha_registro = date('Y-m-d H:i:s');

        $asignacion = Asignacion::find()
            ->with(['proyecto', 'campania']) // fuerza la carga de ambas relaciones
            ->where(['idasignartarea' => $idasignartarea])
            ->one();

        if (!PermisosGeneralesHelper::puedeCrearRecurso(Yii::$app->user->identity, $asignacion)) {
            $this->notiService->agregarMensajeError('No tiene permiso para crear un recurso en esta tarea.');
            return ['exito' => false];
        }
        if ($model->load($dates) && $model->validate()) {
            if ($model->save()) {
                // Obtener cliente del proyecto
                $asignacion = $model->asignacion;

                $cliente = null;
                $gestor = null;

                if ($asignacion->proyecto) {
                    $cliente = $asignacion->proyecto->cliente ?? null;
                    $gestor = $asignacion->proyecto->gestor ?? null;
                } elseif ($asignacion->campania) {
                    $cliente = $asignacion->campania->cliente ?? null;
                    $gestor = $asignacion->campania->gestor ?? null;
                }

                $correoCliente = $cliente->email ?? null;
                $correoGestor = $gestor->email ?? null;



                if ($correoCliente) {
                    $this->correoService->enviarCorreoNuevoRecurso($model, $correoCliente);
                }

                if ($correoGestor) {
                    $this->correoService->enviarCorreoNuevoRecursoGestor($model, $correoGestor);
                }

                $this->notiService->agregarMensajeExito('Recurso creado correctamente.');
                return ['exito' => true];
            }

            // Error al guardar
            $this->notiService->agregarMensajeError('Error al guardar el recurso.');
        } else {
            // Error al validar
            $this->notiService->agregarMensajeError('Error al validar los datos del recurso.');
        }

        // Siempre retornar el modelo
        return ['exito' => false, 'model' => $model];
    }




    public function editarRecurso($id, $data)
    {
        $model = Recursos::findOne($id);

        if (!$model) {
            $this->notiService->agregarMensajeError('Recurso no encontrado.');
            return ['exito' => false];
        }
        $asignacion = $model->asignacion;

// Obtener cliente y gestor desde proyecto o campaña
        $cliente = null;
        $gestor = null;

        if ($asignacion->proyecto) {
            $cliente = $asignacion->proyecto->cliente ?? null;
            $gestor = $asignacion->proyecto->gestor ?? null;
        } elseif ($asignacion->campania) {
            $cliente = $asignacion->campania->cliente ?? null;
            $gestor = $asignacion->campania->gestor ?? null;
        }

        if (!PermisosGeneralesHelper::puedeCrearRecurso(Yii::$app->user->identity, $asignacion)) {
            $this->notiService->agregarMensajeError('No tiene permiso para editar un recurso en esta tarea.');
            return ['exito' => false];
        }

        $tieneHistorial = HistoriaRecursos::find()
            ->where(['idrecurso' => $model->idrecurso])
            ->exists();

        if ($tieneHistorial) {
            return ['exito' => false, 'mensaje' => 'No se puede editar un recurso con historial.'];
        }



        if ($model->load($data) && $model->validate()) {
            if ($model->save()) {
                // Obtener el correo del cliente asociado al proyecto
                $asignacion = $model->asignacion;
                $correoCliente = $cliente->email ?? null;
                $correoGestor = $gestor->email ?? null;
                if ($correoCliente) {
                    $this->correoService->enviarCorreoEdicionRecurso($model, $correoCliente);
                }

                if ($correoGestor) {
                    $this->correoService->enviarCorreoEditarRecursoGestor($model, $correoGestor);
                }

                $this->notiService->agregarMensajeExito('Recurso editado correctamente.');
                return ['exito' => true];
            }

            $this->notiService->agregarMensajeError('No se pudo guardar el recurso.');
        } else {
            $this->notiService->agregarMensajeError('Error al validar los datos del recurso.');
        }

        return ['exito' => false, 'model' => $model];
    }



    public function eliminarRecurso($id)
    {
        $model = Recursos::find()
            ->with([
                'asignacion.campania.cliente',
                'asignacion.campania.gestor',
                'asignacion.proyecto.cliente',
                'asignacion.proyecto.gestor'
            ])
            ->where(['idrecurso' => $id])
            ->one();


        if (!$model) {
            return ['exito' => false, 'mensaje' => 'Recurso no encontrado.'];
        }

        $asignacion = $model->asignacion;
        $cliente = null;
        $gestor = null;

        if ($asignacion->proyecto) {
            $cliente = $asignacion->proyecto->cliente ?? null;
            $gestor = $asignacion->proyecto->gestor ?? null;
        } elseif ($asignacion->campania) {
            $cliente = $asignacion->campania->cliente ?? null;
            $gestor = $asignacion->campania->gestor ?? null;
        }

        if (!PermisosGeneralesHelper::puedeCrearRecurso(Yii::$app->user->identity, $asignacion)) {
            $this->notiService->agregarMensajeError('No tiene permiso para eliminar un recurso en esta tarea.');
            return ['exito' => false];
        }

        $tieneHistorial = HistoriaRecursos::find()
            ->where(['idrecurso' => $model->idrecurso])
            ->exists();

        if ($tieneHistorial) {
            return ['exito' => false, 'mensaje' => 'No se puede eliminar un recurso con historial.'];
        }

        // Capturar correos antes del delete
        $correoCliente = $cliente->email ?? null;
        $correoGestor = $gestor->email ?? null;

        // Enviar correos antes de eliminar
        if ($correoCliente) {
            $this->correoService->enviarCorreoEliminacionRecurso($model, $correoCliente);
        }

        if ($correoGestor) {
            $this->correoService->enviarCorreoEliminarRecursoGestor($model, $correoGestor);
        }

        if ($model->delete()) {
            return ['exito' => true, 'mensaje' => 'Recurso eliminado correctamente.'];
        }

        return ['exito' => false, 'mensaje' => 'No se pudo eliminar el recurso.'];
    }






    public function esClienteAsignado($recurso, $usuarioId): bool
    {
        $asignacion = Asignacion::findOne($recurso->idasignartarea);

        if (!$asignacion) {
            return false;
        }

        if ($asignacion->idproyecto !== null) {
            $proyecto = Proyectos::findOne($asignacion->idproyecto);
            return $proyecto && $proyecto->idcliente == $usuarioId;
        }

        if ($asignacion->idcampania !== null) {
            $campania = Campanias::findOne($asignacion->idcampania);
            return $campania && $campania->idcliente == $usuarioId;
        }

        return false;
    }




    public function esClienteDelRecurso($recurso): bool
    {
        $user =  Yii::$app->user->identity;
        if (!$user) {
            return false;
        }

        $proyecto = $recurso->asignacion->proyecto ?? null;

        return $proyecto && $proyecto->idcliente == $user['id'];
    }




    public function rechazarRecursoConComentario($post)
    {
        $usuario = Yii::$app->user->identity;
        $idrecurso = $post['idrecurso'] ?? null;
        $comentario = trim($post['comentario'] ?? '');

        if (!$idrecurso || !$comentario) {
            return ['exito' => false, 'mensaje' => 'Datos incompletos.'];
        }

        $recurso = Recursos::findOne($idrecurso);
        if (!$recurso) {
            return ['exito' => false, 'mensaje' => 'Recurso no encontrado.'];
        }

        // Guardar comentario en historial
        $historial = new HistoriaRecursos();
        $historial->idrecurso = $idrecurso;
        $historial->idusuario = $usuario->idusuario;
        $historial->comentario = $comentario;
        $historial->tipomensaje = 3; // Rechazo
        $historial->tipo = 2; // Tipo cliente
        $historial->fecha_registro = date('Y-m-d H:i:s');
        $historial->save(false);

        // Cambiar estado del recurso a rechazado
        $recurso->estado = 3; // 3 = Rechazado
        $recurso->save(false);

        // Obtener destinatarios
        $asignacion = $recurso->asignacion;
        $destinatarios = [];

        $correoConsultor = $asignacion->consultor->email ?? null;
        if ($correoConsultor) {
            $destinatarios[] = $correoConsultor;
        }

        $correoGestor = null;
        if ($asignacion->proyecto) {
            $correoGestor = $asignacion->proyecto->gestor->email ?? null;
        } elseif ($asignacion->campania) {
            $correoGestor = $asignacion->campania->gestor->email ?? null;
        }

        if ($correoGestor && $correoGestor !== $correoConsultor) {
            $destinatarios[] = $correoGestor;
        }

        // Enviar correo (si hay destinatarios válidos)
        if (!empty($destinatarios)) {
            $this->correoService->enviarCorreoRechazoRecurso($recurso, $comentario, $destinatarios);
            Yii::info("Correo de rechazo enviado a: " . implode(', ', $destinatarios), 'correo');
        } else {
            Yii::info("NO SE PUDO ENVIAR CORREO, sin destinatarios válidos", 'correo');
        }

        return ['exito' => true, 'mensaje' => 'Recurso rechazado con éxito.'];
    }




    public function aprobarRecursoConComentario($post)
    {
        $usuario = Yii::$app->user->identity;
        $idrecurso = $post['idrecurso'] ?? null;
        $comentario = trim($post['comentario'] ?? '');

        if (!$idrecurso || !$comentario) {
            return ['exito' => false, 'mensaje' => 'Datos incompletos.'];
        }

        $recurso = Recursos::findOne($idrecurso);
        if (!$recurso) {
            return ['exito' => false, 'mensaje' => 'Recurso no encontrado.'];
        }

        // Guardar comentario en historial
        $historial = new HistoriaRecursos();
        $historial->idrecurso = $idrecurso;
        $historial->idusuario = $usuario->idusuario;
        $historial->comentario = $comentario;
        $historial->tipomensaje = 2;
        $historial->tipo = 2; // tipo cliente
        $historial->fecha_registro = date('Y-m-d H:i:s');
        $historial->save(false);

        // Cambiar estado del recurso
        $recurso->estado = 2; // aprobado
        $recurso->save(false);

        // Obtener destinatarios (consultor y gestor)
        $asignacion = $recurso->asignacion;
        $destinatarios = [];

        if (!empty($asignacion->consultor->email)) {
            $destinatarios[] = $asignacion->consultor->email;
        }

        $gestor = $asignacion->proyecto->gestor ?? $asignacion->campania->gestor ?? null;
        if (!empty($gestor->email)) {
            $destinatarios[] = $gestor->email;
        }

        if (!empty($destinatarios)) {
            $this->correoService->enviarCorreoAprobacionRecurso($recurso, $comentario, $destinatarios);
            Yii::info("Correo de aprobación enviado a: " . implode(', ', $destinatarios), 'correo');
        } else {
            Yii::info("NO SE PUDO ENVIAR CORREO, destinatarios vacíos", 'correo');
        }

        return ['exito' => true, 'mensaje' => 'Recurso aprobado con éxito.'];
    }


    public function todosLosRecursosAprobados($idasignacion)
    {
        $total = Recursos::find()->where(['idasignartarea' => $idasignacion])->count();
        $aprobados = Recursos::find()->where(['idasignartarea' => $idasignacion, 'estado' => 2])->count();
        return $total > 0 && $total === $aprobados;
    }


    /**
     * @throws NotFoundHttpException
     */
    public function findModel($id)
    {
        if (($model = RecursoSearch::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }




}
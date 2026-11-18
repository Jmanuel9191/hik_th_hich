<?php
namespace app\components;

use app\models\Asignacion;
use app\models\AsignacionSearch;
use app\models\Campanias;
use app\models\HistorialAsignacionSearch;
use app\models\HistorialProyectosSearch;
use app\models\Proyectos;
use app\models\ProyectosSearch;
use app\models\Recursos;
use app\models\Tareas;
use app\models\TareasSearch;
use app\models\Usuarios;
use Yii;
use yii\web\NotFoundHttpException;

class AsignacionService implements InterfaceAsignacion
{
    private $notiService;
    private $correoService;

    public function __construct(InterfaceCorreos $correoService, InterfaceNoti $notiService)
    {
        $this->correoService = $correoService;
        $this->notiService = $notiService;
    }

    public function obtenerUsuarioSesion(): ?Usuarios
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


    public function prepararModeloAsignacion($id, $esProyecto = true)
    {
        if ($esProyecto) {
            $proyecto = Proyectos::findOne(['idproyecto' => $id]);
            if (!$proyecto) {
                return null;
            }
        } else {
            $campania = Campanias::findOne(['idcampania' => $id]);
            if (!$campania) {
                return null;
            }
        }

        $model = new Asignacion();
        $model->scenario = 'create';

        if ($esProyecto) {
            $model->idproyecto = $id;
        } else {
            $model->idcampania = $id;
        }

        return $model;
    }


    public function prepararModeloAsignacioncamp($idcampania)
    {
        $proyecto = Campanias::findOne(['idcampania' => $idcampania]);
        if (!$proyecto) {
            return null;
        }
        $model = new Asignacion();
        $model->idcampania = $idcampania;
        $model->scenario = 'create';
        return $model;
    }

    public function procesarAsignacionTarea($model, $datosPost)
    {
        if ($model->load($datosPost)) {
            if (!$model->validate()) {
                $this->notiService->agregarMensajeError('Error de validación. Revisa los campos del formulario.');
                return ['exito' => false, 'model' => $model];
            }

            if ($model->save()) {

                $esProyecto = !empty($model->idproyecto);
                $esCampania = !empty($model->idcampania);

                $comentario = $esProyecto
                    ? 'Se asignó la tarea al consultor desde el proyecto.'
                    : ($esCampania
                        ? 'Se asignó la tarea al consultor desde la campaña.'
                        : 'Asignación registrada.');

                $this->registrarHistorial(
                    $model->idasignartarea,
                    $model->idproyecto,
                    $model->idtarea ?? null,
                    $model->idcampania,
                    $model->idconsultor,
                    'Asignación creada',
                    $comentario
                );

                $correoEnviado = true;
                $correoEnviadogestor = true;

                // Correo al consultor
                $consultor = $model->consultor;
                if ($consultor !== null) {
                    $correoConsultor = $consultor->email;
                    $correoEnviado = $this->correoService->enviarCorreodeAsignacionproyecto($model, $correoConsultor);
                    if (!$correoEnviado) {
                        $this->notiService->agregarMensajeError('Error al enviar el correo al consultor.');
                    }
                }

                // Correo al gestor (proyecto o campaña)
                $gestor = null;
                if ($esProyecto && $model->proyecto && $model->proyecto->gestor) {
                    $gestor = $model->proyecto->gestor;
                } elseif ($esCampania && $model->campania && $model->campania->gestor) {
                    $gestor = $model->campania->gestor;
                }

                if ($gestor !== null && !empty($gestor->email)) {
                    $correoGestor = $gestor->email;
                    $correoEnviadogestor = $this->correoService->enviarCorreoAGestorSobreAsignacion($model, $correoGestor);
                    if (!$correoEnviadogestor) {
                        $this->notiService->agregarMensajeError('Error al enviar el correo al gestor.');
                    }
                }

                // Resultado final
                if ($correoEnviado && $correoEnviadogestor) {
                    $this->notiService->agregarMensajeExito('Se asignó correctamente.');
                    return ['exito' => true];
                } else {
                    return ['exito' => false, 'model' => $model];
                }
            } else {
                $this->notiService->agregarMensajeError('Error al guardar la asignación.');
                return ['exito' => false, 'model' => $model];
            }
        }

        $this->notiService->agregarMensajeError('No se pudo procesar la solicitud.');
        return ['exito' => false, 'model' => $model];
    }




    public function procesarEdicionAsignacion($id, $datosPost)
    {
        $model = Asignacion::findOne($id);
        $consultorAnterior = $model->idconsultor;

        if ($model->estado != 1) {
            $this->notiService->agregarMensajeError('No se puede editar una asignación finalizada.');
            return ['exito' => false, 'model' => $model];
        }


        if ($model->load($datosPost)) {

            // Asegurar que el comentario fue escrito solo al editar
            if (empty(trim($model->comentario))) {
                $model->addError('comentario', 'El comentario es obligatorio.');
            }

            if ($model->validate()) {
                try {
                    if ($model->save()) {

                        $esProyecto = !empty($model->idproyecto);
                        $esCampania = !empty($model->idcampania);

                        $nombreAnterior = Usuarios::findOne($consultorAnterior)?->nombrecompleto;
                        $nombreNuevo = $model->consultor->nombrecompleto ?? 'N/A';
                        $textoCambioConsultor = ($consultorAnterior != $model->idconsultor)
                            ? " Cambio de consultor: de {$nombreAnterior} a {$nombreNuevo}."
                            : '';
                        $comentarioHistorial = $esProyecto
                            ? 'Asignación editada desde el proyecto. Comentario: ' . $model->comentario . $textoCambioConsultor
                            : ($esCampania
                                ? 'Asignación editada desde la campaña. Comentario: ' . $model->comentario . $textoCambioConsultor
                                : 'Asignación editada. Comentario: ' . $model->comentario . $textoCambioConsultor);



                        $this->registrarHistorial(
                            $model->idasignartarea,
                            $model->idproyecto,
                            $model->idtarea ?? null,
                            $model->idcampania,
                            $model->idconsultor,
                            'Asignación editada',
                            $comentarioHistorial
                        );




                        $cliente = null;
                        if (!empty($model->idproyecto) && $model->proyecto && $model->proyecto->cliente) {
                            $cliente = $model->proyecto->cliente;
                        } elseif (!empty($model->idcampania) && $model->campania && $model->campania->cliente) {
                            $cliente = $model->campania->cliente;
                        }

                        if ($cliente && $cliente->email) {
                            $this->correoService->enviarCorreoModificacionAsignacionCliente($model, $cliente->email);
                        }

// Enviar correo al gestor (proyecto o campaña)
                        $gestor = null;
                        if (!empty($model->idproyecto) && $model->proyecto && $model->proyecto->gestor) {
                            $gestor = $model->proyecto->gestor;
                        } elseif (!empty($model->idcampania) && $model->campania && $model->campania->gestor) {
                            $gestor = $model->campania->gestor;
                        }

                        if ($gestor && $gestor->email) {
                            $this->correoService->enviarCorreoModificacionAsignacionGestor($model, $gestor->email);
                        }

                        $this->notiService->agregarMensajeExito('La asignación se actualizó correctamente.');
                        return ['exito' => true];
                    } else {
                        $this->notiService->agregarMensajeError('Error al actualizar la asignación. Inténtelo más tarde.');
                    }
                } catch (\yii\db\IntegrityException $e) {
                    $model->addError('idconsultor', 'Esta combinación de tarea y consultor ya existe.');
                    $model->addError('idtarea', 'Esta combinación de tarea y consultor ya existe.');
                }
            }
        }

        $this->notiService->agregarMensajeError('Error al editar la asignación, revise los campos.');
        return ['exito' => false, 'model' => $model];
    }



    public function obtenerAsignacionPorId($id)
    {
        return Asignacion::findOne($id);
    }

    public function aceptarTarea($id)
    {
        $asignacion = Asignacion::findOne($id);
        if (!$asignacion) {
            return [
                'exito' => false,
                'mensaje' => 'Asignación no encontrada.',
            ];
        }

        $asignacion->estado = '2';

        if ($asignacion->save(false)) {
            $this->registrarHistorial(
                $asignacion->idasignartarea,
                $asignacion->idproyecto,
                $asignacion->idtarea ?? null,
                $asignacion->idcampania,
                $asignacion->idconsultor,
                'Estado',
                'El consultor ' . Yii::$app->user->identity->nombrecompleto . ' comenzó la tarea.'
            );

            // Buscar gestor del proyecto o campaña
            $tituloEntidad = '(sin título)';
            $tipoEntidad = 'proyecto'; // Valor por defecto
            $gestor = null;

            if (!empty($asignacion->idproyecto)) {
                $proyecto = Proyectos::findOne($asignacion->idproyecto);
                if ($proyecto && $proyecto->gestor) {
                    $gestor = $proyecto->gestor;
                    $tituloEntidad = $proyecto->titulo;
                    $tipoEntidad = 'proyecto';
                }
            } elseif (!empty($asignacion->idcampania)) {
                $campania = \app\models\Campanias::findOne($asignacion->idcampania);
                if ($campania && $campania->gestor) {
                    $gestor = $campania->gestor;
                    $tituloEntidad = $campania->titulo;
                    $tipoEntidad = 'campania';
                }
            }

            // Enviar correo al gestor si corresponde
            if ($gestor && $gestor->email) {
                $this->correoService->enviarCorreoAvisoAceptaciondetarea($gestor->email, [
                    'consultor' => Yii::$app->user->identity->nombrecompleto,
                    'tituloTarea' => $asignacion->tarea->titulo ?? '(tarea eliminada)',
                    'proyecto' => $tituloEntidad,
                    'tipo' => $tipoEntidad, // ✅ acá indicás si es campaña o proyecto
                ]);
            }

            return [
                'exito' => true,
                'mensaje' => 'Se cambió el estado de la tarea a EN PROGRESO.',
            ];
        }

        return [
            'exito' => false,
            'mensaje' => 'No se pudo actualizar el estado de la tarea.',
        ];
    }



    public function eliminarAsignacionTarea($idasignacion)
    {
        $model = Asignacion::findOne($idasignacion);

        if (!$model) {
            return ['exito' => false, 'mensaje' => 'Asignación no encontrada.'];
        }

        if ($model->estado != 1) {
            return ['exito' => false, 'mensaje' => 'No se puede eliminar una asignación finalizada.'];
        }

        // Guardar info antes de eliminar
        $idproyecto = $model->idproyecto;
        $idcampania = $model->idcampania;
        $idtarea = $model->idtarea;
        $idconsultor = $model->idconsultor;
        $idasignacion = $model->idasignartarea;

        $esProyecto = !empty($idproyecto);
        $esCampania = !empty($idcampania);

        $descripcion = $esProyecto
            ? 'Asignación eliminada desde el proyecto.'
            : ($esCampania
                ? 'Asignación eliminada desde la campaña.'
                : 'Asignación eliminada.');

        $eliminado = $model->delete();

        if ($eliminado) {
            $this->registrarHistorial(
                $idasignacion,
                $idproyecto,
                $idtarea,
                $idcampania,
                $idconsultor,
                'Asignación eliminada',
                $descripcion
            );

            // Correo al consultor
            $consultor = Usuarios::findOne($idconsultor);
            if ($consultor && $consultor->email) {
                $this->correoService->enviarCorreoAvisoEliminacionAsignacionConsultor(
                    $consultor->email, $idtarea, $descripcion
                );
            }

            // Cliente (proyecto o campaña)
            $cliente = null;
            if ($esProyecto) {
                $proyecto = \app\models\Proyectos::findOne($idproyecto);
                if ($proyecto && $proyecto->cliente && $proyecto->cliente->email) {
                    $cliente = $proyecto->cliente;
                }
            } elseif ($esCampania) {
                $campania = \app\models\Campanias::findOne($idcampania);
                if ($campania && $campania->cliente && $campania->cliente->email) {
                    $cliente = $campania->cliente;
                }
            }

            if ($cliente) {
                $this->correoService->enviarCorreoAvisoEliminacionAsignacionConsultor(
                    $cliente->email, $idtarea, $descripcion
                );
            }

            // Gestor (proyecto o campaña)
            $gestor = null;
            if ($esProyecto && isset($proyecto)) {
                $gestor = $proyecto->gestor ?? null;
            } elseif ($esCampania && isset($campania)) {
                $gestor = $campania->gestor ?? null;
            }

            if ($gestor && $gestor->email) {
                $this->correoService->enviarCorreoAvisoEliminacionAsignacionGestor(
                    $gestor->email, $idtarea, $descripcion
                );
            }
        }

        return ['exito' => $eliminado];
    }





    public function revertirFinalizacionTarea($post)
    {
        $id = $post['idasignacion'] ?? null;
        $comentario = trim($post['comentario'] ?? '');

        if (!$id || !$comentario) {
            return ['exito' => false, 'mensaje' => 'Datos incompletos.'];
        }

        $asignacion = Asignacion::findOne($id);
        if (!$asignacion) {
            return ['exito' => false, 'mensaje' => 'Asignación no encontrada.'];
        }

        // Cambiar estado y limpiar fecha
        $asignacion->estado = 2; // estado anterior (en proceso)
        $asignacion->fechaestadofinal = null;
        $asignacion->comentario = $comentario;

        if ($asignacion->save(false)) {

            // Obtener cliente y gestor según proyecto o campaña
            $cliente = null;
            $gestor = null;

            if ($asignacion->proyecto) {
                $cliente = $asignacion->proyecto->cliente ?? null;
                $gestor = $asignacion->proyecto->gestor ?? null;
            } elseif ($asignacion->campania) {
                $cliente = $asignacion->campania->cliente ?? null;
                $gestor = $asignacion->campania->gestor ?? null;
            }

            $destinatarios = [];
            if ($cliente && $cliente->email) {
                $destinatarios[] = $cliente->email;
            }
            if ($gestor && $gestor->email) {
                $destinatarios[] = $gestor->email;
            }

            if (!empty($destinatarios)) {
                $this->correoService->enviarCorreoReversionTarea($asignacion, $destinatarios);
            }

            return ['exito' => true, 'mensaje' => 'Tarea devuelta al estado anterior.'];
        }

        return ['exito' => false, 'mensaje' => 'No se pudo devolver la tarea.'];
    }


    public function finalizarTarea($post)
    {
        $id = $post['idasignacion'] ?? null;
        $comentario = trim($post['comentario'] ?? '');

        if (!$id || !$comentario) {
            return ['exito' => false, 'mensaje' => 'Datos incompletos.'];
        }

        $asignacion = Asignacion::findOne($id);
        if (!$asignacion) {
            return ['exito' => false, 'mensaje' => 'Asignación no encontrada.'];
        }

        // Verificar que todos los recursos estén aprobados
        $recursos = Recursos::find()->where(['idasignartarea' => $id])->all();
        foreach ($recursos as $r) {
            if ($r->estado != 2) {
                return ['exito' => false, 'mensaje' => 'No todos los recursos están aprobados.'];
            }
        }

        $asignacion->estado = 3; // finalizado
        $asignacion->fechaestadofinal = date('Y-m-d');
        $asignacion->comentario = $comentario;

        if ($asignacion->save(false)) {
            // Detectar si la asignación pertenece a proyecto o campaña
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
                $this->correoService->enviarCorreoFinalizacionTarea($asignacion, $correoCliente);
            }

            if ($correoGestor) {
                $this->correoService->enviarCorreoFinalizacionTarea($asignacion, $correoGestor);
            }

            return ['exito' => true, 'mensaje' => 'Tarea finalizada correctamente.'];
        }

        return ['exito' => false, 'mensaje' => 'No se pudo finalizar la tarea.'];
    }


    public function solicitudfinalizarCamp($post)
    {
        $id = $post['idasignacion'] ?? null;
        $comentario = trim($post['comentario'] ?? '');

        if (!$id || !$comentario) {
            return ['exito' => false, 'mensaje' => 'Datos incompletos.'];
        }

        $asignacion = Asignacion::findOne($id);
        if (!$asignacion) {
            return ['exito' => false, 'mensaje' => 'Asignación no encontrada.'];
        }

        $usuario = Yii::$app->user->identity;

        // Validar que sea cliente
        if ($usuario->rol->alias !== 'cliente') {
            return ['exito' => false, 'mensaje' => 'Solo el cliente puede solicitar la finalización.'];
        }

        // Validar que sea cliente del proyecto o campaña correspondiente
        $esClientePropietario = false;
        $gestor = null;

        if ($asignacion->proyecto && $asignacion->proyecto->idcliente === $usuario->idusuario) {
            $esClientePropietario = true;
            $gestor = $asignacion->proyecto->gestor ?? null;
        } elseif ($asignacion->campania && $asignacion->campania->idcliente === $usuario->idusuario) {
            $esClientePropietario = true;
            $gestor = $asignacion->campania->gestor ?? null;
        }

        if (!$esClientePropietario) {
            return ['exito' => false, 'mensaje' => 'No tiene permiso para finalizar esta tarea.'];
        }

        // Marcar como finalizado
        $asignacion->estado = 3;
        $asignacion->fechaestadofinal = date('Y-m-d');
        $asignacion->comentario = $comentario;

        if ($asignacion->save(false)) {
            $correoGestor = $gestor->email ?? null;

            if ($correoGestor) {
                $this->correoService->enviarCorreoSolicitudFin($asignacion, $correoGestor);
            }

            return ['exito' => true, 'mensaje' => 'La empresa se comunicara con usted en un momento.'];
        }

        return ['exito' => false, 'mensaje' => 'No se pudo finalizar la tarea.'];
    }




    public function obtenerAsignacionesFinalizables($id)
    {
        $asignaciones = Asignacion::find()->where(['idproyecto' => $id])->all();
        $finalizables = [];

        foreach ($asignaciones as $asignacion) {
            if ($this->puedeFinalizarAsignacion($asignacion->idasignartarea)) {
                $finalizables[$asignacion->idasignartarea] = true;
            }
        }

        return $finalizables;
    }


    public function obtenerAsignacionesFinalizablescamp($id)
    {
        $asignaciones = Asignacion::find()->where(['idcampania' => $id])->all();
        $finalizables = [];

        foreach ($asignaciones as $asignacion) {
            if ($this->puedeFinalizarAsignacion($asignacion->idasignartarea)) {
                $finalizables[$asignacion->idasignartarea] = true;
            }
        }

        return $finalizables;
    }


    public function puedeFinalizarAsignacion($idasignacion)
    {
        $asignacion = Asignacion::findOne($idasignacion);
        if (!$asignacion) return false;

        foreach ($asignacion->recursos as $recurso) {
            if ($recurso->estado != 2) {
                return false;
            }
        }

        return true;
    }





    public function listHistorialAsignacion($queryParams, $idproyecto = null)
    {
        $searchModel = new HistorialAsignacionSearch();

        if ($idproyecto !== null) {
            $queryParams['HistorialAsignacionSearch']['idproyecto'] = $idproyecto;
        }

        $dataProvider = $searchModel->search($queryParams);

        return [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ];
    }


    public function registrarHistorial($idasignartarea, $idproyecto = null, $idtarea = null, $idcampania = null, $idconsultor = null, $accion, $descripcion = null)
    {
        $historial = new \app\models\HistorialAsignaciones();
        $historial->idasignartarea = $idasignartarea;
        $historial->idproyecto = $idproyecto;
        $historial->idtarea = $idtarea;
        $historial->idcampania = $idcampania;
        $historial->idconsultor = $idconsultor;
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
        if (($model = AsignacionSearch::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
<?php
namespace app\components;

use app\models\Asignacion;
use app\models\AsignacionSearch;
use app\models\Campanias;
use app\models\CampaniasSearch;
use app\models\HistorialCampaniasSearch;
use app\models\HistorialProyectosSearch;
use app\models\HistorialTareasSearch;
use app\models\Proyectos;
use app\models\ProyectosSearch;
use app\models\Tareas;
use app\models\Usuarios;
use Yii;
use yii\base\ExitException;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class CampaniasService implements InterfaceCampanias
{
    private $correoService;
    private $notiService;
    public function __construct(InterfaceCorreos $correoService, InterfaceNoti $notiService)
    {
        $this->correoService = $correoService;
        $this->notiService = $notiService;
    }


    public function obtenerUsuarioSesionadmingestor()
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

    public function obtenerUsuarioSesionadmingestorconsultor()
    {
        $user = Yii::$app->user->identity;
        if (empty($user)) {
            return null;
        }
        $usuario = Usuarios::findOne(['idusuario' => $user['id']]);
        if ($usuario !== null && in_array($usuario->idrol, ['1', '2', '3'])) {
            return $usuario;
        }
        return null;
    }

    public function verificarUsuarioLogueado()
    {
        $usuario = Yii::$app->user->identity;
        if ($usuario === null) {
            Yii::$app->session->setFlash('error', 'Debes iniciar sesión para acceder a esta sección.');
            Yii::$app->controller->redirect(['site/login']);
            Yii::$app->end();
        }
        return $usuario;
    }



    public function obtenerUsuarioSesioncons()
    {
        $user = Yii::$app->user->identity;;
        if (empty($user)) {
            return null;
        }
        $usuario = Usuarios::findOne(['idusuario' => $user['id']]);
        if ($usuario !== null && in_array($usuario->idrol, ['3', '4'])) {
            return $usuario;
        }
        return null;
    }




    /**
     * @throws ExitException
     */
    public function verificarAccesoAdmingestoryadmin()
    {
        $usuario = $this->obtenerUsuarioSesionadmingestor();
        if ($usuario === null) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para acceder a esta sección.');
            Yii::$app->controller->redirect(Yii::$app->request->referrer ?: ['site/login']);
            Yii::$app->end();
        }
        return $usuario;
    }



    public function verificarAccesoAdmingestoradminyconsultor()
    {
        $usuario = $this->obtenerUsuarioSesionadmingestorconsultor();
        if ($usuario === null) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para acceder a esta sección.');
            Yii::$app->controller->redirect(Yii::$app->request->referrer ?: ['site/login']);
            Yii::$app->end();
        }
        return $usuario;
    }


    public function verificarAccesoCons()
    {
        $usuario = $this->obtenerUsuarioSesioncons();
        if ($usuario === null) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para acceder a esta sección.');
            Yii::$app->controller->redirect(Yii::$app->request->referrer ?: ['site/login']);
            Yii::$app->end();
        }
        return $usuario;
    }

    public function verificarAccesoConsycliente()
    {
        $usuario = $this->obtenerUsuarioSesioncons();
        if ($usuario === null) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para acceder a esta sección.');
            Yii::$app->controller->redirect(Yii::$app->request->referrer ?: ['site/login']);
            Yii::$app->end();
        }
        return $usuario;
    }



    public function listarCampanias($queryParams)
    {
        $searchModel = new CampaniasSearch();
        $dataProvider = $searchModel->search($queryParams);
        $dataProvider->setSort([
            'defaultOrder' => ['idcampania' => SORT_DESC]
        ]);
        return [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ];

    }
    public function obtenerCampania($id)
    {
        return $this->findModel($id);
    }



    public function obtenerCampaniaasignado($id)
    {
        $userId = $userId = Yii::$app->user->identity->id;
        $usuario = Usuarios::findOne($userId);
        $tipo = $usuario->idrol;
        $proyecto = Campanias::findOne($id);
        if ($proyecto == null) {
            throw new NotFoundHttpException('La campañana solicitado no existe.');
        }

        if ($tipo == 3) { // Consultor
            $asignacion = Asignacion::find()
                ->where(['idcampania' => $proyecto->idcampania, 'idconsultor' => $userId])
                ->one();

            if ($asignacion === null) {
                throw new ForbiddenHttpException('No tiene permiso para acceder a este proyecto.');
            }

        } elseif ($tipo == 4) { // Cliente
            if ($proyecto->idcliente != $userId) {
                throw new ForbiddenHttpException('No tiene permiso para acceder a este proyecto.');
            }

        } else {
            // Otros roles no autorizados
            throw new ForbiddenHttpException('No tiene permiso para acceder a este proyecto.');
        }

        return $proyecto;
    }





    public function nuevoCampania($queryParams, InterfaceCatalogoService $catalogoService)
    {

        $model = new Campanias();
        $user = Yii::$app->user->identity;
        $usuario = Usuarios::findOne(['idusuario' => $user['id']]);

        // Si es administrador y se seleccionó manualmente un gestor
        if (isset($queryParams['Campanias']['idgestor']) && $usuario->idrol == 1) {
            $model->idgestor = $queryParams['Campanias']['idgestor'];
        } else {
            $model->idgestor = $usuario->idusuario;
        }
        $model->fecha_registro = date('Y-m-d H:i:s');
        $sucursales = $queryParams['Campanias']['sucursales'] ?? [];
        $sectores = $queryParams['Campanias']['sectores'] ?? [];
        $audiencias = $queryParams['Campanias']['audiencias'] ?? [];


        if ($model->load($queryParams) && $model->validate()) {
            if ($model->save()) {

                // Guardar relaciones múltiples con el catálogo
                $catalogoService->guardarSucursalesCampania($model->idcampania, $sucursales);
                $catalogoService->guardarSectoresCampania($model->idcampania, $sectores);
                $catalogoService->guardarAudienciasCampania($model->idcampania, $audiencias);


                $correoEnviado = false;
                $correoEnviadogestor = false;
                $cliente = $model->cliente;
                $gestor = $model->gestor;


                if ($cliente !== null && !empty($cliente->email)) {
                    $correoCliente = $cliente->email;
                    $correoEnviado = $this->correoService->enviarCorreodeCreacioncampania($model, $correoCliente);
                }

                if ($gestor !== null && !empty($gestor->email)) {
                    $correoGestor = $gestor->email;
                    $correoEnviadogestor = $this->correoService->enviarCorreodeAsignacionGestorcampania($model, $correoGestor);
                }



                if ($correoEnviado && $correoEnviadogestor) {
                    $this->notiService->agregarMensajeExito('Se creó la campaña correctamente y se notificó al cliente y al gestor.');
                } elseif ($correoEnviado) {
                    $this->notiService->agregarMensajeAdvertencia('Se notificó al cliente, pero no se pudo enviar correo al gestor.');
                } elseif ($correoEnviadogestor) {
                    $this->notiService->agregarMensajeAdvertencia('Se notificó al gestor, pero no se pudo enviar correo al cliente.');
                } else {
                    $this->notiService->agregarMensajeAdvertencia('La campaña fue creada, pero no se pudieron enviar los correos.');
                }

                return ['exito' => true, 'model' => $model];

            } else {
                $this->notiService->agregarMensajeError('Error al crear la campaña. Inténtelo más tarde.');
                return ['exito' => false, 'model' => $model];
            }
        }
        return ['exito' => false, 'model' => $model];
    }





    public function obtenerCampaniaPorId($campaniaId)
    {
        return Campanias::findOne(['idcampania' => $campaniaId]);
    }




    public function actualizarCampania($dates, $id, InterfaceCatalogoService $catalogoService)
    {
        $model = $this->findModel($id);
        $clienteOriginal = $model->getOldAttribute('idcliente');
        $tituloOriginal = $model->titulo;
        $descripcionOriginal = $model->descripcion;
        $FechainicioOriginal = $model->fecha_inicio;
        $FechafinOriginal = $model->fecha_fin;
        $PrioridadOriginal = $model->prioridad;
        $CategoriaOriginal = $model->idcategoria_servicio;

        // Arrays auxiliares para comparar relaciones múltiples
        $sucursalesAnteriores = $model->getSucursalesIds();
        $sectoresAnteriores = $model->getSectoresIds();
        $audienciasAnteriores = $model->getAudienciasIds();

        if ($model->load($dates)) {
            if (!$model->validate()) {
                $this->notiService->agregarMensajeError('Error de validación. Revisa los campos del formulario.');
                return ['exito' => false, 'model' => $model];
            }

            if ($model->idcliente != $clienteOriginal) {
                $this->notiService->agregarMensajeError('El cliente no puede ser cambiado durante la actualización de la campaña.');
                return ['exito' => false, 'model' => $model];
            }

            if ($model->save()) {
                $cambios = [];
                $model->refresh();
                // Comparar campos directos
                if ($model->titulo !== $tituloOriginal) {
                    $cambios[] = "Título cambiado de '$tituloOriginal' a '$model->titulo'";
                }

                if ($model->prioridad !== $PrioridadOriginal) {
                    $cambios[] = "Prioridad cambiada de '$PrioridadOriginal' a '$model->prioridad'";
                }

                if ($model->fecha_inicio !== $FechainicioOriginal) {
                    $cambios[] = "Fecha de inicio cambiada de '$FechainicioOriginal' a '$model->fecha_inicio'";
                }

                if ($model->fecha_fin !== $FechafinOriginal) {
                    $cambios[] = "Fecha de fin cambiada de '$FechafinOriginal' a '$model->fecha_fin'";
                }

                if ($model->idcategoria_servicio != $CategoriaOriginal) {
                    $nombreAnterior = $model->getCategoriaServicioNombre($CategoriaOriginal);
                    $nombreNuevo = $model->categoriaServicio->nombre ?? '(sin nombre)';
                    $cambios[] = "Categoría de servicio cambiada de '$nombreAnterior' a '$nombreNuevo'";
                }


                if (trim(strip_tags($model->descripcion)) !== trim(strip_tags($descripcionOriginal))) {
                    $cambios[] = "Descripción modificada.";
                }

                // Comparar relaciones múltiples
                $sucursalesNuevas = $dates['Campanias']['sucursales'] ?? [];
                $sectoresNuevos = $dates['Campanias']['sectores'] ?? [];
                $audienciasNuevos = $dates['Campanias']['audiencias'] ?? [];

                if (array_diff($sucursalesNuevas, $sucursalesAnteriores) || array_diff($sucursalesAnteriores, $sucursalesNuevas)) {
                    $nombresAnteriores = implode(', ', $catalogoService->obtenerNombresSucursales($sucursalesAnteriores));
                    $nombresNuevos = implode(', ', $catalogoService->obtenerNombresSucursales($sucursalesNuevas));
                    $cambios[] = "Sucursales cambiadas de: [$nombresAnteriores] a: [$nombresNuevos]";
                    $catalogoService->guardarSucursalesCampania($model->idcampania, $sucursalesNuevas);
                }


                if (array_diff($sectoresNuevos, $sectoresAnteriores) || array_diff($sectoresAnteriores, $sectoresNuevos)) {
                    $nombresAnteriores = implode(', ', $catalogoService->obtenerNombresSectores($sectoresAnteriores));
                    $nombresNuevos = implode(', ', $catalogoService->obtenerNombresSectores($sectoresNuevos));
                    $cambios[] = "Sectores cambiados de: [$nombresAnteriores] a: [$nombresNuevos]";
                    $catalogoService->guardarSectoresCampania($model->idcampania, $sectoresNuevos);
                }



                if (array_diff($audienciasNuevos, $audienciasAnteriores) || array_diff($audienciasAnteriores, $audienciasNuevos)) {
                    $nombresAnteriores = implode(', ', $catalogoService->obtenerNombresAudiencias($audienciasAnteriores));
                    $nombresNuevos = implode(', ', $catalogoService->obtenerNombresAudiencias($audienciasNuevos));
                    $cambios[] = "Audiencias cambiadas de: [$nombresAnteriores] a: [$nombresNuevos]";
                    $catalogoService->guardarAudienciasCampania($model->idcampania, $audienciasNuevos); // ✅ CORRECTO
                }



                // Registrar historial si hubo cambios
                if (!empty($cambios)) {
                    $descripcion = implode("\n", $cambios);
                    $this->registrarHistorial($model->idcampania, 'Edición', $descripcion);
                }



                $cliente = $model->cliente;
                $gestor = $model->gestor;
                $correoEnviado = true;
                $correoEnviadoGestor = true;


                if ($cliente && !empty($cliente->email)) {
                    $correoCliente = $cliente->email;
                    $correoEnviado = $this->correoService->enviarCorreodeEdicioncampania($model, $correoCliente, $cambios);
                }

                if ($gestor && !empty($gestor->email)) {
                    $correoGestor = $gestor->email;
                    $correoEnviadoGestor = $this->correoService->enviarCorreoActualizacionCampaniaGestor($model, $correoGestor, $cambios);
                }

                // Mensajes flash según resultado
                if ($correoEnviado && $correoEnviadoGestor) {
                    $this->notiService->agregarMensajeExito('La campaña fue actualizado y se notificó al cliente y al gestor.');
                } elseif ($correoEnviado) {
                    $this->notiService->agregarMensajeAdvertencia('Se notificó al cliente, pero no se pudo enviar correo al gestor.');
                } elseif ($correoEnviadoGestor) {
                    $this->notiService->agregarMensajeAdvertencia('Se notificó al gestor, pero no se pudo enviar correo al cliente.');
                } else {
                    $this->notiService->agregarMensajeAdvertencia('La campaña fue actualizada, pero no se pudieron enviar los correos.');
                }

                return ['exito' => true];
            } else {
                $this->notiService->agregarMensajeError('Error al guardar la campaña.');
            }
        }

        return ['exito' => false, 'model' => $model];
    }










    public function registrarHistorial($idcampania, $accion, $descripcion = null )
    {
        $historial = new \app\models\HistorialCampanias();
        $historial->idcampania = $idcampania;
        $historial->idusuario = Yii::$app->user->identity->idusuario ?? null;
        $historial->accion = $accion;
        $historial->descripcion = $descripcion;
        $historial->save(false);
    }








    public function cambiarEstadocampania($id, $motivo)
    {
        $model = $this->findModel($id);
        if ($model === null) {
            return ['exito' => false, 'mensaje' => 'Campaña no encontrado.'];
        }

        if (empty(trim($motivo))) {
            return ['exito' => false, 'mensaje' => 'Colocar el motivo del bloqueo.'];
        }

        $model->scenario = 'cambio_estado';

        $estadoAnterior = $model->estado;
        $nuevoEstado = $estadoAnterior == '1' ? '0' : '1'; // Alternar estado
        $model->estado = $nuevoEstado;
        $model->motivobloqueo = $motivo;


        if ($model->save()) {
            $cliente = $model->cliente;
            $correoEnviado = true;
            $accion = $nuevoEstado == '1' ? 'Desbloqueo' : 'Bloqueo';
            $descripcion = "Estado cambiado de '" . ($estadoAnterior == '1' ? 'Activo' : 'Bloqueado') .
                "' a '" . ($nuevoEstado == '1' ? 'Activo' : 'Bloqueado') . "'";
            if (!empty($motivo)) {
                $descripcion .= "\nMotivo: " . $motivo;
            }

            $this->registrarHistorial($model->idcampania, $accion, $descripcion);
            $mensaje = ($estadoAnterior == '1')
                ? 'La campaña ha sido bloqueada.'
                : 'La campaña ha sido desbloqueada.';

            if ($cliente !== null) {
                $correoCliente = $cliente->email;

                if ($estadoAnterior == '1') {
                    $correoEnviado = $this->correoService
                        ->enviarCorreoDeCampaniaBloqueo($model, $correoCliente, $motivo);
                } else {
                    $correoEnviado = $this->correoService
                        ->enviarCorreoDeCampaniaDesBloqueo($model, $correoCliente, $motivo);
                }

                if (!$correoEnviado) {
                    return ['exito' => false, 'mensaje' => 'Error al enviar el correo al cliente.'];
                }
            }

            return ['exito' => true, 'mensaje' => $mensaje];
        }

        Yii::error("Error al guardar el modelo Campaña ID {$id}: " . json_encode($model->getErrors()), __METHOD__);

        return ['exito' => false, 'mensaje' => 'Hubo un error al actualizar el estado.'];
    }






    public function limpiarFlashes()
    {
        Yii::$app->session->removeFlash('flash'); // <-- este es el importante
        Yii::$app->session->removeFlash('success');
        Yii::$app->session->removeFlash('error');
        Yii::$app->session->removeFlash('warning');
        Yii::$app->session->removeFlash('info');
    }


    public function listHistorial($queryParams, $idcampania = null)
    {
        $searchModel = new HistorialCampaniasSearch();

        // Filtra por ID de tarea si se proporciona
        if ($idcampania !== null) {
            $queryParams['HistorialCampaniasSearch']['idcampania'] = $idcampania; // Asegúrate de que coincida con el nombre del campo en tu modelo
        }

        $dataProvider = $searchModel->search($queryParams);

        return [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ];

    }


    /*public function listarmisProyectos($params)
    {

        $consultorId = Yii::$app->session->get('user')['id'];
        $asignaciones = Asignacion::find()
            ->select(['idproyecto'])
            ->where(['idconsultor' => $consultorId])
            ->distinct()
            ->column();

        $query = Proyectos::find()->where(['idproyecto' => $asignaciones]);
        $searchModel = new ProyectosSearch();
        $dataProvider = $searchModel->search($params, $query);
        $dataProvider->setSort([
            'defaultOrder' => ['idproyecto' => SORT_DESC]
        ]);
        return [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ];

    }*/


    public function listarmisCampanias($params)
    {

        $userId = $userId = Yii::$app->user->identity->id;
        $usuario = Usuarios::findOne($userId);
        $tipo = $usuario->idrol;
        if (!$userId) {
            throw new \yii\web\ForbiddenHttpException("No tienes acceso a esta sección.");
        }

        if ($tipo == '3') {
            $query = $this->buscarProyectosAsignadosAConsultor($userId);
        } elseif ($tipo == '4') {
            $query = $this->buscarProyectosDelCliente($userId);
        } else {
            $query = Campanias::find()->where('0=1'); // no muestra nada
        }
        $searchModel = new CampaniasSearch();
        $dataProvider = $searchModel->search($params, $query);
        $dataProvider->setSort([
            'defaultOrder' => ['idcampania' => SORT_DESC]
        ]);

        return [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ];
    }






    private function buscarProyectosAsignadosAConsultor($consultorId)
    {
        $asignaciones = Asignacion::find()
            ->select(['idcampania'])
            ->where(['idconsultor' => $consultorId])
            ->distinct()
            ->column();

        return Campanias::find()
            ->where(['idcampania' => $asignaciones, 'estado' => 1]);
    }

    private function buscarProyectosDelCliente($clienteId)
    {
        return Campanias::find()
            ->where(['idcliente' => $clienteId, 'estado' => 1]);
    }



    public function puedeFinalizarCampania($idcampania)
    {
        $asignaciones = Asignacion::find()->where(['idcampania' => $idcampania])->all();
        if (empty($asignaciones)) return false;

        foreach ($asignaciones as $asignacion) {
            if ($asignacion->estado != 3) {
                return false;
            }
        }

        return true;
    }









    public function finalizarCampania($post)
    {
        $idcampania = $post['idcampania'] ?? null;
        $comentario = trim($post['comentario'] ?? '');

        if (!$idcampania || !$comentario) {
            return ['exito' => false, 'mensaje' => 'Datos incompletos.'];
        }

        $campania = Campanias::findOne($idcampania);
        if (!$campania) {
            return ['exito' => false, 'mensaje' => 'Campaña no encontrado.'];
        }

        $campania->finalizado = 2; // Estado finalizado
        $campania->motivodefinal = $comentario;
        $campania->fechafinalizacion = date('Y-m-d H:i:s');
        $campania->save(false);
        $this->registrarHistorial($campania->idcampania, 'Finalización', "Campaña finalizada. Motivo: $comentario");

        // Correo al cliente
        $correoCliente = $campania->cliente->email ?? null;
        if ($correoCliente) {
            $this->correoService->enviarCorreoFinalizacionCampania($campania, $correoCliente);
            Yii::info("Correo enviado a cliente: $correoCliente", 'correo');
        } else {
            Yii::info("NO SE PUDO ENVIAR CORREO al cliente, email vacío", 'correo');
        }

        // Correo al gestor
        $correoGestor = $campania->gestor->email ?? null;
        if ($correoGestor) {
            $this->correoService->enviarCorreoFinalizacionCampania($campania, $correoGestor);
            Yii::info("Correo enviado al gestor: $correoGestor", 'correo');
        } else {
            Yii::info("NO SE PUDO ENVIAR CORREO al gestor, email vacío", 'correo');
        }

        return ['exito' => true, 'mensaje' => 'Campaña finalizada exitosamente.'];
    }



    /**
     * @throws NotFoundHttpException
     */
    public function findModel($id)
    {
        if (($model = Campanias::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }




}
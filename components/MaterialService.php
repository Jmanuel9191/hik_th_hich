<?php
namespace app\components;

use app\models\Asignacion;
use app\models\Campanias;
use app\models\HistoriaRecursos;
use app\models\Proyectos;
use app\models\Recursos;
use app\models\RecursoSearch;
use app\models\Usuarios;
use Yii;
use yii\base\ExitException;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class MaterialService implements InterfaceMaterial
{
    private $correoService;
    private $notiService;
    public function __construct(InterfaceCorreos $correoService, InterfaceNoti $notiService)
    {
        $this->correoService = $correoService;
        $this->notiService = $notiService;
    }

    public function verificarAccesoCliente()  {
        $usuario = $this->obtenerUsuarioSesion();
        if ($usuario === null) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para acceder a esta sección.');
            Yii::$app->controller->redirect(Yii::$app->request->referrer ?: ['site/login']);
            Yii::$app->end(); // Termina la ejecución del script, ya que la redirección se realizará aquí
        }
        return $usuario;
    }


    public function obtenerUsuarioSesion()
    {
        $user = Yii::$app->user->identity;
        if (empty($user)) {
            return null;
        }
        $usuario = Usuarios::findOne(['idusuario' => $user['id']]);
        // Verificar si el usuario existe y si su tipo de usuario es '1'
        if ($usuario !== null && $usuario->idrol == '4') {
            return $usuario;
        }
        return null;
    }
    public function verificarUsuarioLogueado()
    {
        $usuario = Yii::$app->user->identity;;
        if ($usuario === null) {
            Yii::$app->session->setFlash('error', 'Debes iniciar sesión para acceder a esta sección.');
            Yii::$app->controller->redirect(['site/login']);
            Yii::$app->end();
        }
        return $usuario;
    }



    public function verificarAccesoCompletoAlHistorialcamp($idrecurso)
    {
        $this->verificarUsuarioLogueado();

        $userId =  Yii::$app->user->identity->id;
        $usuario = Usuarios::findOne($userId);
        $tipo = $usuario->idrol;

        $recurso = Recursos::findOne($idrecurso);
        if (!$recurso) {
            throw new NotFoundHttpException('El recurso no existe.');
        }

        // Obtenemos la asignación desde el recurso
        $asignacion = Asignacion::findOne($recurso->idasignartarea);
        if (!$asignacion) {
            throw new NotFoundHttpException('La asignación relacionada no existe.');
        }

        // Obtenemos el proyecto desde la asignación
        $campania = Campanias::findOne($asignacion->idcampania);
        if (!$campania) {
            throw new NotFoundHttpException('La campaña asociada al recurso no existe.');
        }

        if ($tipo == 3) { // Consultor
            $asignado = Asignacion::find()
                ->where(['idcampania' => $campania->idcampania, 'idconsultor' => $userId])
                ->exists();

            if (!$asignado) {
                throw new ForbiddenHttpException('No tiene permiso para acceder a este historial.');
            }

        } elseif ($tipo == 4) { // Cliente
            if ($campania->idcliente != $userId) {
                throw new ForbiddenHttpException('No tiene permiso para acceder a este historial.');
            }

        }
    }



    public function verificarAccesoCompletoAlHistorial($idrecurso)
    {
        $this->verificarUsuarioLogueado();

        $userId = Yii::$app->user->identity->id;
        $usuario = Usuarios::findOne($userId);
        $rolNombre = $usuario->rol->nombre ?? null;

        $recurso = Recursos::findOne($idrecurso);
        if (!$recurso) {
            throw new NotFoundHttpException('El recurso no existe.');
        }

        $asignacion = $recurso->asignacion;
        if (!$asignacion) {
            throw new NotFoundHttpException('La asignación relacionada no existe.');
        }

        $proyecto = $asignacion->proyecto;
        $campania = $asignacion->campania;

        if (!$proyecto && !$campania) {
            throw new NotFoundHttpException('El recurso no está vinculado a ningún proyecto o campaña.');
        }

        if ($rolNombre === 'consultor') {
            $asignado = Asignacion::find()
                ->where([
                    'idconsultor' => $userId,
                    'idasignartarea' => $asignacion->idasignartarea
                ])
                ->exists();

            if (!$asignado) {
                throw new ForbiddenHttpException('No tiene permiso para acceder a este historial.');
            }

        } elseif ($rolNombre === 'cliente') {
            $idClienteAsignado = $proyecto->idcliente ?? $campania->idcliente;

            if ($idClienteAsignado != $userId) {
                throw new ForbiddenHttpException('No tiene permiso para acceder a este historial.');
            }
        }

        // Si es administrador o gestor, no se restringe el acceso
    }



    public function listarHistorial($params, $idrecurso)
    {
        $searchModel = new \app\models\HistorialRecursoSearch();
        $usuario = Yii::$app->user->identity;

        // Filtros según tipo de usuario


        // Filtro obligatorio por idrecurso
        $params['HistorialRecursoSearch']['idrecurso'] = $idrecurso;
        $dataProvider = $searchModel->search($params);
        // Cargamos el recurso para mostrar sus datos arriba en la vista
        $recurso = \app\models\Recursos::findOne($idrecurso);

        return [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'recurso' => $recurso,
        ];
    }



    public function procesarMensajeConArchivo($datosPost, $datosArchivo)
    {
        $usuario = Yii::$app->user->identity;
        $comentario = trim($datosPost['HistoriaRecursos']['comentario'] ?? '');

        $modelCarga = new \app\models\HistoriaRecursos();
        $archivos = \yii\web\UploadedFile::getInstances($modelCarga, 'archivo');

        if (empty($comentario) && empty($archivos)) {
            return ['exito' => false, 'mensaje' => 'Debe ingresar un comentario o adjuntar un archivo.'];
        }

        $exito = false;
        $html = '';
        $ultimoId = null;
        $seEnvioComentario = false;
        $seEnvioArchivo = false;

        $idRecurso = $datosPost['HistoriaRecursos']['idrecurso'] ?? null;
        $recurso = \app\models\Recursos::findOne($idRecurso);

        // GUARDAR COMENTARIO
        if (!empty($comentario)) {
            $modelComentario = new \app\models\HistoriaRecursos();
            $modelComentario->idrecurso = $idRecurso;
            $modelComentario->comentario = $comentario;
            $modelComentario->idusuario = $usuario->idusuario;
            $modelComentario->tipo = $usuario->esConsultor() ? 1 : 2;

            if ($modelComentario->save(false)) {
                $exito = true;
                $html .= $this->generarHtmlDelMensaje($modelComentario);
                $ultimoId = $modelComentario->idhistorial;
                $seEnvioComentario = true;
            }
        }

        // GUARDAR ARCHIVOS
        foreach ($archivos as $archivo) {
            $modelArchivo = new \app\models\HistoriaRecursos();
            $modelArchivo->idrecurso = $idRecurso;
            $modelArchivo->idusuario = $usuario->idusuario;
            $modelArchivo->tipo = $usuario->esConsultor() ? 1 : 2;

            $ruta = $modelArchivo->subirArchivo($archivo, $recurso);

            if ($ruta) {
                $modelArchivo->archivo = $ruta;
                if ($modelArchivo->save(false)) {
                    $exito = true;
                    $html .= $this->generarHtmlDelMensaje($modelArchivo);
                    $ultimoId = $modelArchivo->idhistorial;
                    $seEnvioArchivo = true;
                }
            }
        }

        // Enviar notificaciones si se hizo algo
        if ($exito) {
            try {
                $this->correoService->enviarCorreosNotificacion(
                    $usuario,
                    $recurso,
                    $seEnvioComentario,
                    $seEnvioArchivo
                );
            } catch (\Throwable $e) {
                Yii::error('Error al enviar notificación de correo: ' . $e->getMessage(), __METHOD__);
            }
        }

        return $exito
            ? [
                'exito' => true,
                'mensaje' => 'Mensaje enviado correctamente.',
                'html' => $html ?? null,
                'ultimoId' => $ultimoId ?? null
            ]
            : [
                'exito' => false,
                'mensaje' => 'Error al guardar el mensaje.'
            ];
    }


    public function obtenerMensajesNuevos($idrecurso, $ultimoId)
    {
        $mensajes = HistoriaRecursos::find()
            ->where(['idrecurso' => $idrecurso])
            ->andWhere(['>', 'idhistorial', $ultimoId])
            ->orderBy(['idhistorial' => SORT_ASC])
            ->all();

        if (empty($mensajes)) {
            return ['exito' => true, 'html' => '', 'ultimoId' => $ultimoId];
        }

        $html = '';
        $maxId = $ultimoId;

        foreach ($mensajes as $mensaje) {
            $html .= \Yii::$app->controller->renderPartial('widgets/_mensaje_historial', [
                'model' => $mensaje
            ]);
            $maxId = max($maxId, $mensaje->idhistorial);
        }

        return [
            'exito' => true,
            'html' => $html,
            'ultimoId' => $maxId
        ];
    }





    private function generarHtmlDelMensaje($model)
    {
        return \Yii::$app->controller->renderPartial('widgets/_mensaje_historial', [

            'model' => $model
        ]);
    }


    public function obtenerUltimoIdMensaje($idrecurso)
    {
        return HistoriaRecursos::find()
            ->where(['idrecurso' => $idrecurso])
            ->max('idhistorial');
    }


    /**
     * @throws NotFoundHttpException
     */
    public function findModel($id)
    {
        if (($model = HistoriaRecursos::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }




}
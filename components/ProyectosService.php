<?php
namespace app\components;

use app\models\Asignacion;
use app\models\AsignacionSearch;
use app\models\HistorialProyectosSearch;
use app\models\HistorialTareasSearch;
use app\models\Parametros;
use app\models\ProductoParametro;
use app\models\Productos;
use app\models\ProductosSearch;
use app\models\Tareas;
use app\models\Usuarios;
use Yii;
use yii\base\ExitException;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class ProyectosService implements InterfaceProyectos
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



    public function listarProyectos($queryParams)
    {
        $searchModel = new ProductosSearch();
        $dataProvider = $searchModel->search($queryParams);
        $dataProvider->setSort([
            'defaultOrder' => ['idproducto' => SORT_DESC]
        ]);
        return [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ];

    }
    public function obtenerProyecto($id)
    {
        return Productos::find()
            ->with(['sucursales', 'sectores']) // ← añade las relaciones aquí
            ->where(['idproducto' => $id])
            ->one();
    }




    public function obtenerProyectoasignado($id)
    {
        $userId = $userId = Yii::$app->user->identity->id;
        $usuario = Usuarios::findOne($userId);
        $tipo = $usuario->idrol;
        $proyecto = Proyectos::findOne($id);
        if ($proyecto == null) {
            throw new NotFoundHttpException('El proyecto solicitado no existe.');
        }

        if ($tipo == 3) { // Consultor
            $asignacion = Asignacion::find()
                ->where(['idproyecto' => $proyecto->idproyecto, 'idconsultor' => $userId])
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




    public function nuevoProyecto($queryParams, InterfaceCatalogoService $catalogoService)
    {
        $model = new Productos();
        $user = Yii::$app->user->identity;
        $usuario = Usuarios::findOne(['idusuario' => $user['id']]);
        $model->fecha_registro = date('Y-m-d H:i:s');
        $model->estado = '1';
        // Extraer sucursales y sectores (múltiples)


        if ($model->load($queryParams) && $model->validate()) {
            if ($model->save()) {
   
                    $this->notiService->agregarMensajeExito('Se creó el productos correctamente.');

                return ['exito' => true, 'model' => $model];
            } else {
                $this->notiService->agregarMensajeError('Error al guardar el proyecto. Inténtelo más tarde.');
            }
        } else {
            $this->notiService->agregarMensajeError('Error al procesar los datos del proyecto.');
        }

        return ['exito' => false, 'model' => $model];
    }



    public function obtenerProyectoPorId($proyectoId)
    {
        return Productos::findOne(['idproyecto' => $proyectoId]);
    }



public function obtenerParametro($idparametro)
{
    $p = \app\models\Parametros::findOne($idparametro);
    if (!$p) return null;
    return [
        'idparametro'      => $p->idparametro,
        'nombre'           => $p->nombre,
        'unidad'           => $p->unidad ?? '',
        'es_negrita'       => $p->es_negrita ?? 0,
        'es_subtitulo'     => $p->es_subtitulo ?? 0,
        'referencia_texto' => $p->referencia_texto ?? '',
        'tipo_campo'       => $p->tipo_campo ?? 'texto',
        'opciones'         => $p->opciones ?? '',
        'salto_linea'      => $p->salto_linea ?? 1,   // <-- NUEVO
        'valor_default' => $p->valor_default ?? '',
    ];
}

    public function obtenerParametrosDeProducto($idproducto)
{
    return \app\models\ProductoParametro::find()
        ->where(['idproducto' => $idproducto])
        ->orderBy('orden')
        ->with('parametro')
        ->all();
}

public function agregarFilasParametros($idproducto, $filas)
{
    foreach ((array)$filas as $fila) {
        $titulo = trim($fila['titulo'] ?? '');
        $orden = (int)($fila['orden'] ?? 0);
        if ($titulo === '') continue;

        // Busca si ya existe un parámetro con ese nombre exacto en el catálogo
        $parametro = \app\models\Parametros::find()->where(['nombre' => $titulo])->one();
        if (!$parametro) {
            $parametro = new \app\models\Parametros();
            $parametro->nombre = $titulo; // unidad y referencia quedan en blanco, se completan después
            if (!$parametro->save()) continue;
        }

        $vinculo = \app\models\ProductoParametro::findOne(['idproducto' => $idproducto, 'idparametro' => $parametro->idparametro]);
        if ($vinculo) {
            // ya estaba vinculado -> solo actualiza el orden, no duplica
            $vinculo->orden = $orden;
            $vinculo->save();
            continue;
        }

        $pp = new \app\models\ProductoParametro();
        $pp->idproducto = $idproducto;
        $pp->idparametro = $parametro->idparametro;
        $pp->orden = $orden;
        $pp->save();
    }

    $this->notiService->agregarMensajeExito('Parámetros guardados correctamente.');
    return ['exito' => true];
}





/*

public function crearYVincularParametro($idproducto, $post)
{
    $param = new Parametros();
    $param->nombre           = trim($post['nombre'] ?? '');
    $param->unidad           = trim($post['unidad'] ?? '') ?: null;
    $param->es_negrita       = isset($post['es_negrita']) ? 1 : 0;
    $param->es_subtitulo     = isset($post['es_subtitulo']) ? 1 : 0;
    $param->referencia_texto = $post['referencia_texto'] ?? null;

    if (!$param->save()) {
        return false;
    }

    $vinculo = new ProductoParametro();
    $vinculo->idproducto  = (int)$idproducto;
    $vinculo->idparametro = $param->idparametro;
    $vinculo->orden       = (int)($post['orden'] ?? 1);

    return $vinculo->save();
}
*/


public function crearYVincularParametro($idproducto, $post)
{
    $param = new Parametros();
    $param->nombre           = trim($post['nombre'] ?? '');
    $param->unidad           = trim($post['unidad'] ?? '') ?: null;
    $param->es_negrita       = isset($post['es_negrita']) ? 1 : 0;
    $param->es_subtitulo     = isset($post['es_subtitulo']) ? 1 : 0;
    $param->referencia_texto = $post['referencia_texto'] ?? null;

    $tipoCampo = $post['tipo_campo'] ?? 'texto';
    if (!in_array($tipoCampo, ['texto', 'select', 'select_texto'])) {
        $tipoCampo = 'texto';
    }
    $param->tipo_campo  = $tipoCampo;
    $param->opciones    = in_array($tipoCampo, ['select', 'select_texto']) ? trim($post['opciones'] ?? '') : null;
    $param->salto_linea = isset($post['salto_linea']) ? 1 : 0;   // <-- NUEVO
    $param->valor_default = trim($post['valor_default'] ?? '') ?: null;

    if (!$param->save()) {
        return false;
    }

    $vinculo = new ProductoParametro();
    $vinculo->idproducto  = (int)$idproducto;
    $vinculo->idparametro = $param->idparametro;
    $vinculo->orden       = (int)($post['orden'] ?? 1);

    return $vinculo->save();
}



public function quitarParametroDeProducto($idproducto, $idparametro)
{
    $vinculo = ProductoParametro::findOne([
        'idproducto'  => (int)$idproducto,
        'idparametro' => (int)$idparametro,
    ]);

    if (!$vinculo) return false;

    $vinculo->delete();
    return true;
}

public function agregarParametros($idproducto, $parametros)
{
    foreach ($parametros as $fila) {
        $titulo = trim($fila['titulo'] ?? '');
        if (empty($titulo)) continue;

        $param = \app\models\Parametros::findOne(['nombre' => $titulo]);

        if (!$param) {
            $param = new \app\models\Parametros();
            $param->nombre = $titulo;
            if (!$param->save()) continue;
        }

        $yaVinculado = \app\models\ProductoParametro::findOne([
            'idproducto'  => $idproducto,
            'idparametro' => $param->idparametro,
        ]);

        if ($yaVinculado) continue;

        $vinculo = new \app\models\ProductoParametro();
        $vinculo->idproducto  = $idproducto;
        $vinculo->idparametro = $param->idparametro;
        $vinculo->orden       = (int)($fila['orden'] ?? 1);
        $vinculo->save();
    }
}



/*
public function editarParametro($idparametro, $post)
{
    $param = \app\models\Parametros::findOne($idparametro);
    if (!$param) return false;

    $param->nombre           = trim($post['nombre'] ?? '');
    $param->unidad           = trim($post['unidad'] ?? '') ?: null;
    $param->es_negrita       = isset($post['es_negrita']) ? 1 : 0;
    $param->es_subtitulo     = isset($post['es_subtitulo']) ? 1 : 0;
    $param->referencia_texto = $post['referencia_texto'] ?? null;

    return $param->save(false);
}
*/



public function editarParametro($idparametro, $post)
{
    $param = \app\models\Parametros::findOne($idparametro);
    if (!$param) return false;

    $param->nombre           = trim($post['nombre'] ?? '');
    $param->unidad           = trim($post['unidad'] ?? '') ?: null;
    $param->es_negrita       = isset($post['es_negrita']) ? 1 : 0;
    $param->es_subtitulo     = isset($post['es_subtitulo']) ? 1 : 0;
    $param->referencia_texto = $post['referencia_texto'] ?? null;

    $tipoCampo = $post['tipo_campo'] ?? 'texto';
    if (!in_array($tipoCampo, ['texto', 'select', 'select_texto'])) {
        $tipoCampo = 'texto';
    }
    $param->tipo_campo  = $tipoCampo;
    $param->opciones    = in_array($tipoCampo, ['select', 'select_texto']) ? trim($post['opciones'] ?? '') : null;
    $param->salto_linea = isset($post['salto_linea']) ? 1 : 0;   // <-- NUEVO
       $param->valor_default = trim($post['valor_default'] ?? '') ?: null;

    return $param->save(false);
}

    public function actualizarProyecto($dates, $id, InterfaceCatalogoService $catalogoService)
    {
        $model = $this->findModel($id);
       

        // Arrays auxiliares para comparar relaciones múltiples
      

        if ($model->load($dates)) {
            if (!$model->validate()) {
                $this->notiService->agregarMensajeError('Error de validación. Revisa los campos del formulario.');
                return ['exito' => false, 'model' => $model];
            }

         

            if ($model->save()) {
               
             $this->notiService->agregarMensajeExito('Actualizado y con exito.');

                return ['exito' => true];
            } else {
                $this->notiService->agregarMensajeError('Error al guardar el proyecto.');
            }
        }

        return ['exito' => false, 'model' => $model];
    }




public function actualizarOrdenExistentes($idproducto, $ordenData)
{
    foreach ($ordenData as $idparametro => $orden) {
        \app\models\ProductoParametro::updateAll(
            ['orden' => (int)$orden],
            ['idproducto' => (int)$idproducto, 'idparametro' => (int)$idparametro]
        );
    }
}







    public function registrarHistorial($idproyecto, $accion, $descripcion = null )
    {
        $historial = new \app\models\HistorialProyectos();
        $historial->idproyecto = $idproyecto;
        $historial->idusuario = Yii::$app->user->identity->idusuario ?? null;
        $historial->accion = $accion;
        $historial->descripcion = $descripcion;
        $historial->fecha = date('Y-m-d H:i:s');
        $historial->save(false);
    }




    public function cambiarEstadoproyecto($id, $motivo)
    {
        $model = $this->findModel($id);
        if ($model === null) {
            return ['exito' => false, 'mensaje' => 'Proyecto no encontrado.'];
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

            $this->registrarHistorial($model->idproyecto, $accion, $descripcion);
            $mensaje = ($estadoAnterior == '1')
                ? 'El proyecto ha sido bloqueado.'
                : 'El proyecto ha sido desbloqueado.';

            if ($cliente !== null) {
                $correoCliente = $cliente->email;

                if ($estadoAnterior == '1') {
                    $correoEnviado = $this->correoService
                        ->enviarCorreoDeProyectoBloqueo($model, $correoCliente, $motivo);
                } else {
                    $correoEnviado = $this->correoService
                        ->enviarCorreoDeProyectoDesbloqueo($model, $correoCliente, $motivo);
                }

                if (!$correoEnviado) {
                    return ['exito' => false, 'mensaje' => 'Error al enviar el correo al cliente.'];
                }
            }

            return ['exito' => true, 'mensaje' => $mensaje];
        }

        Yii::error("Error al guardar el modelo Proyecto ID {$id}: " . json_encode($model->getErrors()), __METHOD__);

        return ['exito' => false, 'mensaje' => 'Hubo un error al actualizar el estado.'];
    }



    public function finalizarProyecto($post)
    {
        $idproyecto = $post['idproyecto'] ?? null;
        $comentario = trim($post['comentario'] ?? '');

        if (!$idproyecto || !$comentario) {
            return ['exito' => false, 'mensaje' => 'Datos incompletos.'];
        }

        $proyecto = Proyectos::findOne($idproyecto);
        if (!$proyecto) {
            return ['exito' => false, 'mensaje' => 'Proyecto no encontrado.'];
        }

        $proyecto->finalizado = 2; // Estado finalizado
        $proyecto->motivodefinal = $comentario;
        $proyecto->fechafinalizacion = date('Y-m-d H:i:s');
        $proyecto->save(false);
        $this->registrarHistorial($proyecto->idproyecto, 'Finalización', "Proyecto finalizado. Motivo: $comentario");

        // Correo al cliente
        $correoCliente = $proyecto->cliente->email ?? null;
        if ($correoCliente) {
            $this->correoService->enviarCorreoFinalizacionProyecto($proyecto, $correoCliente);
            Yii::info("Correo enviado a cliente: $correoCliente", 'correo');
        } else {
            Yii::info("NO SE PUDO ENVIAR CORREO al cliente, email vacío", 'correo');
        }

        // Correo al gestor
        $correoGestor = $proyecto->gestor->email ?? null;
        if ($correoGestor) {
            $this->correoService->enviarCorreoFinalizacionProyecto($proyecto, $correoGestor);
            Yii::info("Correo enviado al gestor: $correoGestor", 'correo');
        } else {
            Yii::info("NO SE PUDO ENVIAR CORREO al gestor, email vacío", 'correo');
        }

        return ['exito' => true, 'mensaje' => 'Proyecto finalizado exitosamente.'];
    }






    public function limpiarFlashes()
    {
        Yii::$app->session->removeFlash('flash'); // <-- este es el importante
        Yii::$app->session->removeFlash('success');
        Yii::$app->session->removeFlash('error');
        Yii::$app->session->removeFlash('warning');
        Yii::$app->session->removeFlash('info');
    }


    public function listHistorial($queryParams, $idproyecto = null)
    {
        $searchModel = new HistorialProyectosSearch();

        // Filtra por ID de tarea si se proporciona
        if ($idproyecto !== null) {
            $queryParams['HistorialProyectosSearch']['idproyecto'] = $idproyecto; // Asegúrate de que coincida con el nombre del campo en tu modelo
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


    public function listarmisProyectos($params)
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
            $query = Proyectos::find()->where('0=1'); // no muestra nada
        }
        $searchModel = new ProyectosSearch();
        $dataProvider = $searchModel->search($params, $query);
        $dataProvider->setSort([
            'defaultOrder' => ['idproyecto' => SORT_DESC]
        ]);

        return [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ];
    }






    private function buscarProyectosAsignadosAConsultor($consultorId)
    {
        $asignaciones = Asignacion::find()
            ->select(['idproyecto'])
            ->where(['idconsultor' => $consultorId])
            ->distinct()
            ->column();

        return Proyectos::find()
            ->where(['idproyecto' => $asignaciones, 'estado' => 1]);
    }

    private function buscarProyectosDelCliente($clienteId)
    {
        return Proyectos::find()
            ->where(['idcliente' => $clienteId, 'estado' => 1]);
    }



    public function puedeFinalizarProyecto($idproyecto)
    {
        $asignaciones = Asignacion::find()->where(['idproyecto' => $idproyecto])->all();
        if (empty($asignaciones)) return false;

        foreach ($asignaciones as $asignacion) {
            if ($asignacion->estado != 3) {
                return false;
            }
        }

        return true;
    }





    /**
     * @throws NotFoundHttpException
     */
    public function findModel($id)
    {
        if (($model = Productos::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }




}
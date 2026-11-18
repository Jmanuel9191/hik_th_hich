<?php

namespace app\components;

use app\models\HistorialProyectosSearch;
use app\models\Ecosistema;
use app\models\HistorialUsuarios;
use app\models\HistorialUsuariosSearch;
use app\models\Roles;
use app\models\Tareas;
use app\models\TareasSearch;
use app\models\Usuarios;
use app\models\UsuariosSearch;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class AdminService implements InterfaceAdmin
{

    private $correoService;
    private $alertService;

    public function __construct(InterfaceCorreos $correoService, InterfaceAlert $alertService)
    {
        $this->correoService = $correoService;
        $this->alertService = $alertService;
    }

    public function obtenerUsuarioSesion()
    {
        $usuario = Yii::$app->user->identity;

        if ($usuario !== null && $usuario->idrol == '1') {
            return $usuario;
        }
        return null;

    }

    public function verificarAccesoAdmin()
    {
        $usuario = $this->obtenerUsuarioSesion();
        if ($usuario === null) {
               Yii::$app->response->redirect(['admin/default/login'])->send();
            Yii::$app->end();
        }
        return $usuario;
    }

    public function listUsuarios($queryParams)
    {
        $searchModel = new UsuariosSearch();
        $dataProvider = $searchModel->search($this->conPestana((array) $queryParams));
        $dataProvider->setSort([
            'defaultOrder' => ['idusuario' => SORT_DESC],
            'attributes' => ['nombrecompleto', 'usuario', 'email', 'movil', 'idrol', 'estado', 'idusuario', 'ultimo_acceso'],
        ]);
        return [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'conteo' => $this->conteoPorPestana(),
        ];

    }

    /**
     * Pestañas del listado: "Activos" (por defecto) y "Bloqueados" (UsuariosSearch[estado] = 0).
     * Los bloqueados por intentos siguen en Activos (es temporal) con su etiqueta.
     */
    private function conPestana(array $params): array
    {
        $params['UsuariosSearch']['estado'] = ($params['UsuariosSearch']['estado'] ?? '') === '0' ? '0' : '1';
        return $params;
    }

    /** Cantidad de usuarios del panel en cada pestaña. */
    public function conteoPorPestana(): array
    {
        $base = Usuarios::find()->where(['idrol' => Rol::PANEL]);
        return [
            'activos' => (int) (clone $base)->andWhere(['estado' => '1'])->count(),
            'bloqueados' => (int) (clone $base)->andWhere(['estado' => ['0', '']])->count(),
        ];
    }

    public function obtenerUsuario($id)
    {
        return $this->findModel($id);
    }

    public function nuevoUsuario($postData)
    {
        $model = new Usuarios();

        if ($model->load($postData)) {
            // Campos que NO se toman del formulario
            $model->estado = '1';
            $model->intentos_fallidos = 0;
            $model->bloqueado_hasta = null;

            if (!Rol::entraAlPanel($model->idrol)) {
                $model->addError('idrol', 'Elegí un tipo de usuario válido.');
                return ['exito' => false, 'model' => $model];
            }

            if ($model->validate()) {
                $model->fecha_registro = date('Y-m-d H:i:s');
                $password = $postData['Usuarios']['contrasena'] ?? '';
                $model->contrasena = Yii::$app->security->generatePasswordHash($password);
                if ($model->save(false)) {
                    $ecos = $this->sincronizarEcosistemas($model);
                    HistorialUsuarios::registrar($model->idusuario, 'Creación',
                        'Usuario creado como "' . ($model->rol->nombre ?? $model->idrol) . '".'
                        . ($ecos ? "\nEcosistemas: " . implode(', ', $ecos) : ''), Yii::$app->user->id);
                    try {
                        $this->correoService->enviarCorreodeBienvenida($model);
                    } catch (\Throwable $e) {
                        Yii::error('Correo de bienvenida: ' . $e->getMessage(), __METHOD__); // el usuario igual queda creado
                    }
                    $this->alertService->agregarMensajeExito('El usuario ha sido creado correctamente.');
                    return ['exito' => true];
                }
                $this->alertService->agregarMensajeError('Error al crear usuario. Inténtelo más tarde.');
            }
        }

        return ['exito' => false, 'model' => $model];
    }


    /** Campos que la edición del usuario NUNCA cambia (tienen su propia acción o son internos). */
    const CAMPOS_PROTEGIDOS = ['contrasena', 'estado', 'intentos_fallidos', 'bloqueado_hasta', 'auth_key',
        'google_token', 'reset_token_hash', 'reset_token_expira', 'fecha_registro', 'requiere_cambio_contrasena',
        'ultimo_acceso', 'ultimo_acceso_ip'];

    public function actualizarUsuario($datos, $id)
    {
        $model = $this->findModel($id);
        $anterior = $model->getAttributes();
        $model->ecosistemasIds = array_map('intval', ArrayHelper::getColumn($model->ecosistemas, 'idecosistema'));
        $ecosAntes = $this->nombresEcosistemas($model->ecosistemasIds);

        if (!$model->load($datos)) {
            return ['exito' => false, 'model' => $model];
        }

        foreach (self::CAMPOS_PROTEGIDOS as $campo) {
            if ($model->hasAttribute($campo)) {
                $model->setAttribute($campo, $anterior[$campo]);
            }
        }

        if (!Rol::entraAlPanel($model->idrol)) {
            $model->addError('idrol', 'Elegí un tipo de usuario válido.');
        } elseif ((int) $id === (int) Yii::$app->user->id && (int) $model->idrol !== (int) $anterior['idrol']) {
            $model->addError('idrol', 'No podés cambiar tu propio tipo de usuario.');
        }

        // No se validan los campos protegidos (ej. contrasena ya viene como hash).
        $aValidar = array_diff($model->activeAttributes(), self::CAMPOS_PROTEGIDOS);
        if ($model->hasErrors() || !$model->validate($aValidar, false)) {
            $this->alertService->agregarMensajeError('Error de validación. Revisa los campos.');
            return ['exito' => false, 'model' => $model];
        }

        if (!$model->save(false)) {
            $this->alertService->agregarMensajeError('Error al guardar los cambios. Inténtelo más tarde.');
            return ['exito' => false, 'model' => $model];
        }

        $cambios = $this->describirCambios($model, $anterior);
        $ecosAhora = $this->sincronizarEcosistemas($model);
        if ($ecosAntes !== $ecosAhora) {
            $cambios[] = 'Ecosistemas: ' . ($ecosAntes ? implode(', ', $ecosAntes) : '(ninguno)')
                . ' → ' . ($ecosAhora ? implode(', ', $ecosAhora) : '(ninguno)');
        }
        if ($cambios !== []) {
            HistorialUsuarios::registrar($model->idusuario, 'Edición', implode("\n", $cambios), Yii::$app->user->id);
        }

        $this->alertService->agregarMensajeExito('El usuario ha sido actualizado correctamente.');
        return ['exito' => true];
    }

    /**
     * Guarda los ecosistemas del usuario. Solo el Gerente de área tiene; a los demás se les quitan.
     * @return string[] nombres de los ecosistemas que quedaron asignados
     */
    private function sincronizarEcosistemas(Usuarios $model)
    {
        $ids = [];
        if ((int) $model->idrol === Rol::GERENTE) {
            $ids = Ecosistema::find()->select('idecosistema')
                ->where(['idecosistema' => array_map('intval', (array) $model->ecosistemasIds)])->column();
        }
        $db = Yii::$app->db;
        $db->createCommand()->delete('usuario_ecosistema', ['idusuario' => $model->idusuario])->execute();
        if ($ids) {
            $db->createCommand()->batchInsert('usuario_ecosistema', ['idusuario', 'idecosistema'],
                array_map(fn($id) => [$model->idusuario, $id], $ids))->execute();
        }
        $model->ecosistemasIds = array_map('intval', $ids);
        return $this->nombresEcosistemas($ids);
    }

    private function nombresEcosistemas(array $ids)
    {
        return $ids ? Ecosistema::find()->select('nombre')->where(['idecosistema' => $ids])
            ->orderBy(['numero' => SORT_ASC])->column() : [];
    }

    /** "Teléfono: 7630 → 7650" por cada campo que cambió (sin campos protegidos). */
    private function describirCambios(Usuarios $model, array $anterior)
    {
        $cambios = [];
        foreach ($model->getAttributes() as $campo => $nuevo) {
            if (in_array($campo, self::CAMPOS_PROTEGIDOS, true)) {
                continue;
            }
            $viejo = $anterior[$campo] ?? null;
            if ((string) $viejo === (string) $nuevo) {
                continue;
            }
            if ($campo === 'idrol') {
                $viejo = Roles::findOne($viejo)->nombre ?? $viejo;
                $nuevo = Roles::findOne($nuevo)->nombre ?? $nuevo;
            }
            $vacio = '(vacío)';
            $cambios[] = $model->getAttributeLabel($campo) . ': '
                . ((string) $viejo === '' ? $vacio : $viejo) . ' → ' . ((string) $nuevo === '' ? $vacio : $nuevo);
        }
        return $cambios;
    }


    /** Bloquear o activar con motivo obligatorio. Estado: '1' activo, '0' bloqueado. */
    public function cambiarEstadoUsuario($id, $motivo = '')
    {
        $motivo = trim((string) $motivo);
        if ($motivo === '') {
            return ['exito' => false, 'mensaje' => 'Tenés que escribir el motivo.'];
        }
        if ((int) $id === (int) Yii::$app->user->id) {
            return ['exito' => false, 'mensaje' => 'No podés bloquear tu propia cuenta.'];
        }

        $model = Usuarios::findOne($id);
        if ($model === null || !Rol::entraAlPanel($model->idrol)) {
            return ['exito' => false, 'mensaje' => 'Usuario no encontrado.'];
        }

        $bloquear = $model->estado === '1';
        $model->updateAttributes(['estado' => $bloquear ? '0' : '1']);

        HistorialUsuarios::registrar(
            $model->idusuario,
            $bloquear ? 'Bloqueo' : 'Activación',
            ($bloquear ? "Estado: Activo → Bloqueado" : "Estado: Bloqueado → Activo") . "\nMotivo: " . $motivo,
            Yii::$app->user->id
        );

        // Si falla el correo, el cambio de estado igual queda hecho.
        try {
            $bloquear
                ? $this->correoService->enviarCorreoDeBloqueo($model, $motivo)
                : $this->correoService->enviarCorreoDedesBloqueo($model, $motivo);
        } catch (\Throwable $e) {
            Yii::error('No se pudo enviar el aviso de estado: ' . $e->getMessage(), __METHOD__);
        }

        return ['exito' => true, 'mensaje' => $bloquear ? 'El usuario fue bloqueado.' : 'El usuario fue activado.'];
    }

    /** Eliminar: solo si no es uno mismo. Queda registrado en el log de la app. */
    public function eliminarUsuario($id)
    {
        if ((int) $id === (int) Yii::$app->user->id) {
            return ['exito' => false, 'mensaje' => 'No podés eliminar tu propia cuenta.'];
        }
        $model = Usuarios::findOne($id);
        if ($model === null || !Rol::entraAlPanel($model->idrol)) {
            return ['exito' => false, 'mensaje' => 'Usuario no encontrado.'];
        }
        // Si ya usó el panel, se bloquea (así se conserva quién hizo qué).
        if ($model->ultimo_acceso !== null) {
            return ['exito' => false, 'mensaje' => 'Este usuario ya ingresó al panel: no se elimina, bloquealo.'];
        }
        $model->delete();
        Yii::info('Usuario #' . $id . ' (' . $model->usuario . ') eliminado por #' . Yii::$app->user->id, 'usuarios');
        return ['exito' => true, 'mensaje' => 'Usuario eliminado.'];
    }

    /**
     * Datos para exportar el listado de usuarios con los MISMOS filtros que tiene el index.
     * @return array ['titulo', 'columnas' => [encabezado => fn], 'modelos', 'filtros' => string[]]
     */
    public function exportarUsuarios($params)
    {
        $search = new UsuariosSearch();
        $dp = $search->search($this->conPestana((array) $params));
        $modelos = $dp->query->with(['rol', 'ecosistemas'])->orderBy(['nombrecompleto' => SORT_ASC])->all();

        $fecha = fn($v) => $v ? date('d/m/Y', strtotime($v)) : '';
        $estado = function ($m) {
            if ($m->estado !== '1') {
                return 'Bloqueado';
            }
            if ($m->bloqueado_hasta !== null && strtotime($m->bloqueado_hasta) > time()) {
                return 'Bloqueado por intentos (hasta ' . date('d/m/Y H:i', strtotime($m->bloqueado_hasta)) . ')';
            }
            return 'Activo';
        };
        $columnas = [
            'Nombre completo' => fn($m) => $m->nombrecompleto,
            'Usuario' => fn($m) => $m->usuario,
            'Email' => fn($m) => $m->email,
            'Tipo de usuario' => fn($m) => $m->rol->nombre ?? '',
            'Ecosistemas' => fn($m) => implode(', ', array_map(fn($e) => $e->nombre, $m->ecosistemas)),
            'Estado' => $estado,
            'País' => fn($m) => $m->pais_operacion,
            'Ciudad' => fn($m) => $m->ciudad,
            'Teléfono' => fn($m) => $m->telefono,
            'Celular / WhatsApp' => fn($m) => $m->movil,
            'Puesto de trabajo' => fn($m) => $m->cargo,
            'Dirección' => fn($m) => $m->direccion,
            'Fecha de nacimiento' => fn($m) => $fecha($m->fecha_nacimiento),
            'Fecha de registro' => fn($m) => $m->fecha_registro ? date('d/m/Y H:i', strtotime($m->fecha_registro)) : '',
            'Último acceso' => fn($m) => $m->ultimo_acceso ? date('d/m/Y H:i', strtotime($m->ultimo_acceso)) : 'Nunca',
        ];

        // Texto de los filtros aplicados (para el PDF)
        $filtros = [];
        foreach (['nombrecompleto' => 'Nombre', 'usuario' => 'Usuario', 'email' => 'Email', 'movil' => 'Celular'] as $campo => $etiqueta) {
            if ((string) $search->$campo !== '') {
                $filtros[] = $etiqueta . ': ' . $search->$campo;
            }
        }
        if ($search->idrol) {
            $filtros[] = 'Tipo: ' . (Roles::findOne($search->idrol)->nombre ?? $search->idrol);
        }
        if ($search->idecosistema) {
            $filtros[] = 'Ecosistema: ' . (Ecosistema::findOne($search->idecosistema)->nombre ?? $search->idecosistema);
        }
        if ((string) $search->estado !== '') {
            $filtros[] = $search->estado === '1' ? 'Activos' : 'Bloqueados';
        }

        return ['titulo' => 'Usuarios', 'columnas' => $columnas, 'modelos' => $modelos, 'filtros' => $filtros];
    }

    /** Historial de un usuario, del más nuevo al más viejo. */
    public function historialUsuario($id)
    {
        return new \yii\data\ActiveDataProvider([
            'query' => HistorialUsuarios::find()
                ->with('usuarioActor')
                ->where(['idusuario_afectado' => $id])
                ->orderBy(['fecha' => SORT_DESC, 'idhistorial' => SORT_DESC]),
            'pagination' => ['pageSize' => 15, 'pageParam' => 'hpage'],
            'sort' => false,
        ]);
    }


    public function cambiarEstadoInactividad($id, $motivo = '')
    {
        $model = $this->findModel($id);
        if ($model === null) {
            return ['exito' => false, 'mensaje' => 'Usuario no encontrado.'];
        }

        $estadoAnterior = $model->estado;
        $model->estado = $estadoAnterior == '2' ? '1' : '2'; // 2 = Inactivo

        if ($model->save(false)) {
            $accion = '';
            $descripcion = '';

            if ($model->estado == '2') {
                $accion = 'Marcado como inactivo';
                $descripcion = 'El usuario fue marcado como inactivo. Motivo: ' . $motivo;
                $this->correoService->enviarCorreoDeInactividad($model, $motivo);
            } else {
                $accion = 'Reactivado desde estado inactivo';
                $descripcion = 'El usuario fue reactivado. Motivo: ' . $motivo;
                $this->correoService->enviarCorreoDedesBloqueo($model, $motivo); // Podés crear uno específico si querés
            }

            // Registrar en historial
            $historial = new \app\models\HistorialUsuarios();
            $historial->idusuario_afectado = $model->idusuario;
            $historial->idusuario_actor = Yii::$app->user->id;
            $historial->accion = $accion;
            $historial->descripcion = $descripcion;
            $historial->fecha = date('Y-m-d H:i:s');
            $historial->save(false);

            return ['exito' => true, 'mensaje' => $descripcion];
        }

        return ['exito' => false, 'mensaje' => 'No se pudo cambiar el estado del usuario.'];
    }


    public function findModel($id)
    {
        if (($model = Usuarios::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('La página solicitada no existe.');
        }
    }

    public function listHistorial($queryParams, $idusuario = null)
    {
        $searchModel = new HistorialUsuariosSearch();

        // Filtra por ID de tarea si se proporciona
        if ($idusuario !== null) {
            $queryParams['HistorialUsuariosSearch']['idusuario_afectado'] = $idusuario; // Asegúrate de que coincida con el nombre del campo en tu modelo
        }

        $dataProvider = $searchModel->search($queryParams);

        return [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ];

    }

    /** Roles para el formulario de usuarios: solo los del panel (el inversor tiene su módulo). */
    public function obtenerRolesActivos()
    {
        return ArrayHelper::map(
            Roles::find()->where(['estado' => 1, 'idrol' => Rol::PANEL])->orderBy('idrol')->all(),
            'idrol',
            'nombre'
        );
    }
}
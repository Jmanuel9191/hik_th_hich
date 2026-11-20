<?php

namespace app\components;

use app\models\Configuracion;
use app\models\Ecosistema;
use app\models\Inversor;
use app\models\InversorHistorial;
use app\models\InversorSearch;
use app\models\SolicitudInversorForm;
use app\models\Usuarios;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * Inversores (rol 5). La cuenta va en `usuarios`; la solicitud en `inversor`.
 *  Web:   registro → confirma el correo (24 h) → pendiente → (panel) aprobado / rechazado → NDA → proyectos de sus áreas.
 *  Panel: se carga la solicitud (sin contraseña) → correo "Activá tu cuenta" (72 h) para que la cree.
 * Tokens: solo se guarda el hash sha256; el token viaja en el correo. Documento de identidad en privado/inversores/<id>/.
 */
class InversorService implements InterfaceInversores
{
    const HORAS_VERIFICACION = 24;
    const HORAS_ACTIVACION = 72;
    const MINUTOS_RECUPERACION = 30;
    /** Registros por IP por hora (freno a robots). */
    /** Registros por hora desde una misma conexión (IP). Una oficina o familia no llega; un robot sí. */
    const REGISTROS_POR_HORA = 20;
    /** Pedidos de "olvidé mi contraseña" por hora desde una misma conexión. */
    const RECUPERACIONES_POR_HORA = 10;

    private $permisos;
    private $correos;

    public function __construct(InterfacePermisosService $permisos, InterfaceCorreos $correos)
    {
        $this->permisos = $permisos;
        $this->correos = $correos;
    }

    /* ================= Panel ================= */

    private function esGerente()
    {
        return (int) (Yii::$app->user->identity->idrol ?? 0) === Rol::GERENTE;
    }

    /** null = todos; gerente: sus ecosistemas. */
    private function alcance()
    {
        if (!$this->esGerente()) {
            return null;
        }
        return array_map('intval', (new \yii\db\Query())->select('idecosistema')->from('usuario_ecosistema')
            ->where(['idusuario' => Yii::$app->user->id])->column());
    }

    public function listar($params, $pestana)
    {
        $this->permisos->verificarPermiso('inversores_listar');
        $alcance = $this->alcance();
        $search = new InversorSearch();
        $conteos = [];
        foreach (['todos', 'pendiente', 'aprobado', 'rechazado', 'sin_verificar', 'bloqueado'] as $p) {
            $conteos[$p] = (int) (new InversorSearch())->search([], $p, $alcance, Yii::$app->user->id)->query->count();
        }
        $ecosistemas = Ecosistema::lista(false);
        return [
            'searchModel' => $search,
            'dataProvider' => $search->search($params, $pestana, $alcance, Yii::$app->user->id),
            'conteos' => $conteos,
            'ecosistemas' => $alcance === null ? $ecosistemas : array_intersect_key($ecosistemas, array_flip($alcance)),
        ];
    }

    public function buscar($id, $permiso = 'inversores_ver'): Inversor
    {
        $this->permisos->verificarPermiso($permiso);
        $inv = Inversor::find()->with(['usuario', 'intereses', 'areas'])->where(['idusuario' => (int) $id])->one();
        if ($inv === null) {
            throw new NotFoundHttpException('El inversor no existe.');
        }
        $alcance = $this->alcance();
        if ($alcance !== null) {
            $ids = array_merge(array_column($inv->intereses, 'idecosistema'), array_column($inv->areas, 'idecosistema'));
            $suyo = (int) $inv->idusuario_registro === (int) Yii::$app->user->id;
            if (!$suyo && !array_intersect(array_map('intval', $ids), $alcance)) {
                throw new ForbiddenHttpException('Este inversor no está relacionado con sus áreas.');
            }
        }
        return $inv;
    }

    public function crearDesdePanel(SolicitudInversorForm $f, array $post)
    {
        $f->load($post);
        if (!$f->validate()) {
            return ['exito' => false, 'mensaje' => 'Revise los campos marcados.'];
        }
        $alcance = $this->alcance(); // el gerente carga inversores para sus áreas
        if ($alcance !== null && !array_intersect(array_map('intval', $f->intereses), $alcance)) {
            $f->addError('intereses', 'Elija al menos una de sus áreas.');
            return ['exito' => false, 'mensaje' => 'Revise los campos marcados.'];
        }
        $aprobar = $f->aprobar_ahora && $this->permisos->tienePermiso('inversores_aprobar');

        $tx = Yii::$app->db->beginTransaction();
        $id = $this->crearCuenta($f, Yii::$app->security->generatePasswordHash(Yii::$app->security->generateRandomString(32)));
        $inv = $this->crearSolicitud($id, $f, 'admin');
        $inv->estado_solicitud = 'pendiente';
        $inv->idusuario_registro = Yii::$app->user->id;
        $inv->save(false);
        if ($f->documento) {
            $r = SubidaArchivo::guardar($f->documento, Inversor::carpetaDe($id), Inversor::FORMATOS_DOCUMENTO, Inversor::MAX_DOCUMENTO_MB);
            if (!$r['ok']) {
                $tx->rollBack();
                $f->addError('documento', $r['error']);
                return ['exito' => false, 'mensaje' => 'Revise los campos marcados.'];
            }
            $inv->updateAttributes(['documento_archivo' => $r['archivo'], 'documento_nombre' => mb_substr($f->documento->name, 0, 255)]);
        }
        $tx->commit();

        InversorHistorial::registrar($id, 'Creación', 'Solicitud cargada desde el panel.' . "\n" . $this->resumen($inv));
        if ($aprobar) {
            $this->aprobar($id, $f->intereses);
        }
        $enviado = $this->mandarActivacion(Usuarios::findOne($id));
        return ['exito' => true, 'id' => $id, 'mensaje' => 'Inversor cargado' . ($aprobar ? ' y aprobado' : ' (pendiente de aprobación)') . '. '
            . ($enviado ? 'Le enviamos un correo para que cree su contraseña.' : 'No se pudo enviar el correo de activación: reintente desde su ficha.')];
    }

    public function editar(Inversor $inv, SolicitudInversorForm $f, array $post)
    {
        $this->permisos->verificarPermiso('inversores_editar');
        $antesU = $inv->usuario->getAttributes();
        $antesI = $inv->getAttributes();
        $antesInt = array_column($inv->intereses, 'nombre');
        $f->load($post);
        if (!$f->validate()) {
            return ['exito' => false, 'mensaje' => 'Revise los campos marcados.'];
        }
        $tx = Yii::$app->db->beginTransaction();
        Yii::$app->db->createCommand()->update('usuarios', $this->datosCuenta($f), ['idusuario' => $inv->idusuario])->execute();
        $inv->setAttributes($this->datosSolicitud($f), false);
        $inv->fecha_actualizacion = date('Y-m-d H:i:s');
        $inv->save(false);
        $this->guardarRelacion('inversor_interes', $inv->idusuario, $f->intereses);
        $tx->commit();

        $u = Usuarios::findOne($inv->idusuario);
        $inv = Inversor::find()->with(['intereses'])->where(['idusuario' => $inv->idusuario])->one();
        $cambios = [];
        $etiquetas = (new SolicitudInversorForm(['tipo' => $inv->tipo]))->attributeLabels();
        foreach (['nombrecompleto' => 'nombre', 'email' => 'email', 'movil' => 'celular', 'pais' => 'pais', 'ciudad' => 'ciudad', 'cargo' => 'cargo'] as $col => $campo) {
            if ((string) $antesU[$col] !== (string) $u->$col) {
                $cambios[] = $etiquetas[$campo] . ': ' . ($antesU[$col] ?: '(vacío)') . ' → ' . ($u->$col ?: '(vacío)');
            }
        }
        $codigos = \app\models\Moneda::find()->select('codigo')->indexBy('idmoneda')->column();
        $cod = fn($id) => $codigos[$id] ?? 'USD';
        $plata = fn($v, $id) => $cod($id) . ' ' . number_format((float) $v, 0, ',', '.');
        foreach (['monto_minimo', 'monto_maximo'] as $c) {
            if ((float) $antesI[$c] !== (float) $inv->$c || (int) $antesI['idmoneda'] !== (int) $inv->idmoneda) {
                $cambios[] = $etiquetas[$c] . ': ' . $plata($antesI[$c], $antesI['idmoneda']) . ' → ' . $plata($inv->$c, $inv->idmoneda);
            }
        }
        $listas = ['tipo' => Inversor::TIPOS, 'documento_tipo' => Inversor::DOCUMENTOS,
            'plazo_preferido' => Inversor::PLAZOS, 'participacion_preferida' => Inversor::PARTICIPACIONES];
        foreach (['tipo', 'documento_tipo', 'documento_numero', 'razon_social', 'nit', 'sitio_web', 'plazo_preferido',
            'participacion_preferida', 'como_nos_conocio', 'mensaje'] as $c) {
            if ((string) $antesI[$c] !== (string) $inv->$c) {
                $a = $listas[$c][$antesI[$c]] ?? $antesI[$c];
                $d = $listas[$c][$inv->$c] ?? $inv->$c;
                $cambios[] = ($etiquetas[$c] ?? $c) . ': ' . ($a ?: '(vacío)') . ' → ' . ($d ?: '(vacío)');
            }
        }
        $despuesInt = array_column($inv->intereses, 'nombre');
        if ($antesInt != $despuesInt) {
            $cambios[] = 'Áreas de interés: ' . (implode(', ', $antesInt) ?: '(ninguna)') . ' → ' . implode(', ', $despuesInt);
        }
        if ($cambios) {
            InversorHistorial::registrar($inv->idusuario, 'Edición', implode("\n", $cambios));
        }
        return ['exito' => true, 'mensaje' => $cambios ? 'Cambios guardados.' : 'No había cambios para guardar.'];
    }

    public function aprobar($id, array $areas)
    {
        $inv = $this->buscar($id, 'inversores_aprobar');
        $areas = $this->areasValidas($areas);
        if (!$areas) {
            return ['exito' => false, 'mensaje' => 'Elija al menos un área para aprobarlo.'];
        }
        if ($inv->estado_solicitud === 'sin_verificar' && $inv->origen === 'web') {
            return ['exito' => false, 'mensaje' => 'Todavía no confirmó su correo. Puede reenviarle el correo de confirmación.'];
        }
        $this->guardarRelacion('inversor_area', $inv->idusuario, $areas);
        $inv->updateAttributes([
            'estado_solicitud' => 'aprobado', 'motivo_rechazo' => null,
            'fecha_aprobacion' => date('Y-m-d H:i:s'), 'idusuario_aprobacion' => Yii::$app->user->id,
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
        ]);
        $nombres = Ecosistema::find()->select('nombre')->where(['idecosistema' => $areas])->orderBy('numero')->column();
        InversorHistorial::registrar($inv->idusuario, 'Aprobación', 'Solicitud aprobada.' . "\nÁreas: " . implode(', ', $nombres));
        // las que pidió y no se le asignaron: el correo le dice que por ahora no fue seleccionado/a para esas
        $noIncluidas = array_values(array_diff(array_column($inv->intereses, 'nombre'), $nombres));
        $this->intentar(fn() => $this->correos->enviarCorreoInversorAprobado($inv->usuario, $nombres, $noIncluidas));
        $this->avisarGerentes($inv, $areas);
        return ['exito' => true, 'mensaje' => 'Inversor aprobado. Le avisamos por correo (y a los gerentes de sus áreas).'];
    }

    public function guardarAreas($id, array $areas)
    {
        $inv = $this->buscar($id, 'inversores_aprobar');
        if ($inv->estado_solicitud !== 'aprobado') {
            return ['exito' => false, 'mensaje' => 'Solo se cambian las áreas de un inversor aprobado.'];
        }
        $areas = $this->areasValidas($areas);
        if (!$areas) {
            return ['exito' => false, 'mensaje' => 'Tiene que quedar al menos un área. Si no debe ver nada, bloquee la cuenta.'];
        }
        $antes = array_column($inv->areas, 'nombre');
        $idsAntes = array_map('intval', array_column($inv->areas, 'idecosistema'));
        $pedidos = array_diff(Inversor::idsPedidos($inv->idusuario), $idsAntes); // pedidos de Mi cuenta sin decidir
        $this->guardarRelacion('inversor_area', $inv->idusuario, $areas);
        $despues = Ecosistema::find()->select('nombre')->where(['idecosistema' => $areas])->orderBy('numero')->column();
        // Al guardar, los pedidos quedan decididos: los asignados dejan de estar pendientes; los no asignados se borran.
        $negados = array_values(array_diff($pedidos, $areas));
        if ($pedidos) {
            Yii::$app->db->createCommand()->update('inversor_interes', ['pedido_fecha' => null],
                ['idusuario' => $inv->idusuario, 'idecosistema' => array_values(array_intersect($pedidos, $areas))])->execute();
            Yii::$app->db->createCommand()->delete('inversor_interes', ['idusuario' => $inv->idusuario, 'idecosistema' => $negados ?: [0]])->execute();
        }
        $noAprobadas = $negados ? Ecosistema::find()->select('nombre')->where(['idecosistema' => $negados])->orderBy('numero')->column() : [];
        if ($antes == $despues && !$noAprobadas) {
            return ['exito' => true, 'mensaje' => 'No había cambios en las áreas.'];
        }
        InversorHistorial::registrar($inv->idusuario, 'Áreas', 'Áreas: ' . (implode(', ', $antes) ?: '(ninguna)') . ' → ' . implode(', ', $despues)
            . ($noAprobadas ? "\nPedido no aprobado: " . implode(', ', $noAprobadas) : ''));
        // al inversor: qué se sumó, qué se sacó, qué pidió y no se aprobó, y cómo queda · a los gerentes: solo las áreas nuevas
        $nuevas = array_values(array_diff($despues, $antes));
        $quitadas = array_values(array_diff($antes, $despues));
        $this->intentar(fn() => $this->correos->enviarCorreoAreasActualizadas($inv->usuario, $despues, $nuevas, $quitadas, $noAprobadas));
        $this->avisarGerentes($inv, array_diff(array_map('intval', $areas), $idsAntes));
        return ['exito' => true, 'mensaje' => 'Áreas guardadas. Le avisamos al inversor por correo.'];
    }

    public function rechazar($id, $motivo)
    {
        $inv = $this->buscar($id, 'inversores_aprobar');
        $motivo = trim((string) $motivo);
        if (mb_strlen($motivo) < 5 || mb_strlen($motivo) > 500) {
            return ['exito' => false, 'mensaje' => 'Escriba el motivo (entre 5 y 500 caracteres). Se le envía al inversor.'];
        }
        $antes = $inv->estadoNombre();
        Yii::$app->db->createCommand()->delete('inversor_area', ['idusuario' => $inv->idusuario])->execute();
        $inv->updateAttributes(['estado_solicitud' => 'rechazado', 'motivo_rechazo' => $motivo, 'fecha_actualizacion' => date('Y-m-d H:i:s')]);
        InversorHistorial::registrar($inv->idusuario, 'Rechazo', 'Estado: ' . $antes . ' → Rechazado' . "\nMotivo: " . $motivo);
        $pedidas = array_column($inv->intereses, 'nombre');
        $this->intentar(fn() => $this->correos->enviarCorreoInversorRechazado($inv->usuario, $motivo, $pedidas));
        return ['exito' => true, 'mensaje' => 'Solicitud rechazada. Le avisamos por correo.'];
    }

    public function cambiarEstadoCuenta($id, $motivo)
    {
        $inv = $this->buscar($id, 'inversores_estado');
        $motivo = trim((string) $motivo);
        if (mb_strlen($motivo) < 5 || mb_strlen($motivo) > 500) {
            return ['exito' => false, 'mensaje' => 'Escriba el motivo (entre 5 y 500 caracteres).'];
        }
        $u = $inv->usuario;
        $bloquear = $u->estado === '1';
        // auth_key nuevo: si estaba conectado, se le cierra la sesión en el próximo clic
        Yii::$app->db->createCommand()->update('usuarios', ['estado' => $bloquear ? '0' : '1', 'auth_key' => Yii::$app->security->generateRandomString(32)],
            ['idusuario' => $u->idusuario])->execute();
        InversorHistorial::registrar($inv->idusuario, $bloquear ? 'Bloqueo' : 'Activación',
            'Cuenta: ' . ($bloquear ? 'Activa → Bloqueada' : 'Bloqueada → Activa') . "\nMotivo: " . $motivo);
        $this->intentar(fn() => $this->correos->enviarCorreoCuentaInversor($u, $bloquear, $motivo));
        return ['exito' => true, 'mensaje' => $bloquear ? 'Cuenta bloqueada.' : 'Cuenta reactivada.'];
    }

    public function desbloquearIntentos($id)
    {
        $inv = $this->buscar($id, 'inversores_desbloquear'); // el gerente, solo inversores de sus áreas
        $u = $inv->usuario;
        if (!$u->bloqueado_hasta && !(int) $u->intentos_fallidos) {
            return ['exito' => false, 'mensaje' => 'La cuenta no tiene intentos fallidos ni bloqueo.'];
        }
        $bloqueado = $u->bloqueado_hasta && strtotime($u->bloqueado_hasta) > time();
        Yii::$app->db->createCommand()->update(Usuarios::tableName(), ['intentos_fallidos' => 0, 'bloqueado_hasta' => null],
            ['idusuario' => $u->idusuario])->execute();
        $quien = Yii::$app->user->identity->nombrecompleto ?? 'el equipo';
        InversorHistorial::registrar($u->idusuario, 'Cuenta', ($bloqueado ? 'Desbloqueo por intentos fallidos' : 'Reinicio de intentos fallidos') . ' (por ' . $quien . ').');
        $h = new \app\models\HistorialUsuarios();
        $h->idusuario_afectado = $u->idusuario;
        $h->idusuario_actor = Yii::$app->user->id;
        $h->accion = 'Desbloqueo por intentos';
        $h->descripcion = 'Se quitó el bloqueo por intentos fallidos desde la ficha del inversor.';
        $h->fecha = date('Y-m-d H:i:s');
        $h->save(false);
        if ($bloqueado) {
            $this->intentar(fn() => $this->correos->enviarCorreoDesbloqueoPorIntentos($u));
        }
        return ['exito' => true, 'mensaje' => $bloqueado ? 'Cuenta desbloqueada. Le avisamos por correo.' : 'Intentos fallidos reiniciados.'];
    }

    public function reenviarCorreo($id)
    {
        $inv = $this->buscar($id, 'inversores_ver');
        $u = $inv->usuario;
        if ($inv->origen === 'web' && $inv->estado_solicitud === 'sin_verificar') {
            $ok = $this->mandarVerificacion($u, $inv);
            $que = 'confirmación de correo';
        } elseif ($inv->origen === 'admin' && !$inv->email_verificado) {
            $ok = $this->mandarActivacion($u);
            $que = 'activación de cuenta';
        } else {
            return ['exito' => false, 'mensaje' => 'Ya confirmó su correo: no hay nada que reenviar. Si olvidó la contraseña, puede recuperarla desde la web.'];
        }
        InversorHistorial::registrar($inv->idusuario, 'Correo', 'Se reenvió el correo de ' . $que . '.');
        return ['exito' => $ok, 'mensaje' => $ok ? 'Correo reenviado.' : 'No se pudo enviar el correo. Revise la configuración del correo del servidor.'];
    }

    public function subirDocumento($id)
    {
        $inv = $this->buscar($id, 'inversores_editar');
        $f = UploadedFile::getInstanceByName('documento');
        if ($f === null || $f->error === UPLOAD_ERR_NO_FILE) {
            return ['exito' => false, 'mensaje' => 'Elija el archivo.'];
        }
        $r = SubidaArchivo::guardar($f, Inversor::carpetaDe($inv->idusuario), Inversor::FORMATOS_DOCUMENTO, Inversor::MAX_DOCUMENTO_MB);
        if (!$r['ok']) {
            return ['exito' => false, 'mensaje' => $r['error']];
        }
        $anterior = $inv->documento_archivo;
        $inv->updateAttributes(['documento_archivo' => $r['archivo'], 'documento_nombre' => mb_substr($f->name, 0, 255), 'fecha_actualizacion' => date('Y-m-d H:i:s')]);
        if ($anterior) {
            SubidaArchivo::borrar(Inversor::carpetaDe($inv->idusuario), $anterior);
        }
        InversorHistorial::registrar($inv->idusuario, 'Documento', 'Documento de identidad ' . ($anterior ? 'reemplazado' : 'subido') . ' desde el panel.');
        return ['exito' => true, 'mensaje' => 'Documento guardado.'];
    }

    public function archivoDocumento($id): array
    {
        $inv = $this->buscar($id);
        $ruta = Inversor::carpetaDe($inv->idusuario) . '/' . basename((string) $inv->documento_archivo);
        if (!$inv->documento_archivo || !is_file($ruta)) {
            throw new NotFoundHttpException('No tiene documento cargado.');
        }
        InversorHistorial::registrar($inv->idusuario, 'Documento', 'Documento de identidad visto/descargado.');
        $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
        return ['ruta' => $ruta, 'nombre' => $inv->documento_nombre ?: 'documento.' . $ext, 'mime' => Inversor::FORMATOS_DOCUMENTO[$ext][0] ?? null];
    }

    public function historial($id)
    {
        return new ActiveDataProvider([
            'query' => InversorHistorial::find()->with('usuarioActor')->where(['idusuario' => (int) $id])->orderBy(['idhistorial' => SORT_DESC]),
            'pagination' => ['pageSize' => 15, 'pageParam' => 'hpage'],
        ]);
    }

    /* ================= Web ================= */

    public function registrar(SolicitudInversorForm $f)
    {
        if (trim((string) $f->sitio_hp) !== '') {
            return ['exito' => true, 'mensaje' => 'ok']; // robot: se le responde igual y no se guarda nada
        }
        $clave = 'registro-inversor-' . Yii::$app->request->userIP;
        $n = (int) Yii::$app->cache->get($clave);
        if ($n >= self::REGISTROS_POR_HORA) {
            return ['exito' => false, 'mensaje' => 'Hubo demasiados registros desde su conexión. Pruebe de nuevo en una hora.'];
        }
        LimpiezaSolicitudes::siCorresponde(); // de paso, borra las solicitudes que no confirmaron el correo en 7 días
        if (!$f->validate()) {
            return ['exito' => false, 'mensaje' => 'Revise los campos marcados.'];
        }
        $tx = Yii::$app->db->beginTransaction();
        $id = $this->crearCuenta($f, Yii::$app->security->generatePasswordHash($f->contrasena));
        $inv = $this->crearSolicitud($id, $f, 'web');
        $inv->terminos_fecha = date('Y-m-d H:i:s');
        $inv->terminos_ip = Yii::$app->request->userIP;
        $inv->terminos_version = \app\models\TerminosVersion::vigente()->numero ?? null; // qué versión aceptó
        $inv->save(false);
        $r = SubidaArchivo::guardar($f->documento, Inversor::carpetaDe($id), Inversor::FORMATOS_DOCUMENTO, Inversor::MAX_DOCUMENTO_MB);
        if (!$r['ok']) {
            $tx->rollBack();
            $f->addError('documento', $r['error']);
            return ['exito' => false, 'mensaje' => 'Revise los campos marcados.'];
        }
        $inv->updateAttributes(['documento_archivo' => $r['archivo'], 'documento_nombre' => mb_substr($f->documento->name, 0, 255)]);
        $tx->commit();
        Yii::$app->cache->set($clave, $n + 1, 3600);

        InversorHistorial::registrar($id, 'Registro', 'Se registró desde la web (aceptó términos y privacidad' . ($inv->terminos_version ? ' v' . $inv->terminos_version : '') . ', IP ' . $inv->terminos_ip . ').' . "\n" . $this->resumen($inv));
        $this->mandarVerificacion(Usuarios::findOne($id), $inv);
        return ['exito' => true, 'mensaje' => 'ok'];
    }

    public function verificarCorreo($token)
    {
        if (!is_string($token) || !preg_match('/^[A-Za-z0-9_-]{48}$/', $token)) {
            return null;
        }
        $inv = Inversor::findOne(['verificacion_hash' => hash('sha256', $token)]);
        if ($inv === null || !$inv->verificacion_expira || strtotime($inv->verificacion_expira) < time()) {
            return null;
        }
        $inv->updateAttributes([
            'email_verificado' => date('Y-m-d H:i:s'), 'verificacion_hash' => null, 'verificacion_expira' => null,
            'estado_solicitud' => $inv->estado_solicitud === 'sin_verificar' ? 'pendiente' : $inv->estado_solicitud,
        ]);
        InversorHistorial::registrar($inv->idusuario, 'Correo confirmado', 'Confirmó su correo. La solicitud pasa a pendiente de revisión.');
        // el aviso al equipo nunca debe romper la confirmación del inversor
        $this->intentar(function () use ($inv) { $this->avisarDistribuidores($inv); return true; });
        return $inv->usuario;
    }

    public function reenviarVerificacion($email)
    {
        $u = Usuarios::findOne(['email' => mb_strtolower(trim((string) $email)), 'idrol' => Rol::INVERSOR, 'estado' => '1']);
        $inv = $u ? Inversor::findOne($u->idusuario) : null;
        if ($inv && $inv->estado_solicitud === 'sin_verificar') {
            $clave = 'reenvio-verif-' . $u->idusuario;
            if (!Yii::$app->cache->get($clave)) { // uno cada 2 minutos
                Yii::$app->cache->set($clave, 1, 120);
                $this->mandarVerificacion($u, $inv);
            }
        }
        return 'Si el correo está registrado y falta confirmarlo, le enviamos un enlace nuevo. Revise también la carpeta de spam.';
    }

    public function pedirRecuperacion($email)
    {
        $respuesta = 'Si el correo está registrado, le enviamos un enlace para crear una contraseña nueva. Vence en ' . self::MINUTOS_RECUPERACION . ' minutos.';
        // Solo se envía a cuentas de inversor que existen; a cualquier otro correo no se manda nada, pero la respuesta
        // es la misma (así nadie puede averiguar qué correos están registrados).
        // Antiabuso: hasta RECUPERACIONES_POR_HORA pedidos por conexión y un correo cada 2 minutos por cuenta.
        $claveIp = 'recuperacion-ip-' . Yii::$app->request->userIP;
        $n = (int) Yii::$app->cache->get($claveIp);
        if ($n >= self::RECUPERACIONES_POR_HORA) {
            return $respuesta;
        }
        Yii::$app->cache->set($claveIp, $n + 1, 3600);
        $u = Usuarios::findOne(['email' => mb_strtolower(trim((string) $email)), 'idrol' => Rol::INVERSOR, 'estado' => '1']);
        if ($u && !Yii::$app->cache->get('recuperacion-cuenta-' . $u->idusuario)) {
            Yii::$app->cache->set('recuperacion-cuenta-' . $u->idusuario, 1, 120);
            $token = Yii::$app->security->generateRandomString(48);
            Yii::$app->db->createCommand()->update('usuarios', [
                'reset_token_hash' => hash('sha256', $token),
                'reset_token_expira' => date('Y-m-d H:i:s', time() + self::MINUTOS_RECUPERACION * 60),
            ], ['idusuario' => $u->idusuario])->execute();
            $this->intentar(fn() => $this->correos->enviarCorreoRecuperacionInversor($u, Url::to(['/cuenta/acceso/restablecer', 'token' => $token], true)));
            InversorHistorial::registrar($u->idusuario, 'Contraseña', 'Pidió recuperar la contraseña (IP ' . Yii::$app->request->userIP . ').');
        }
        return $respuesta;
    }

    public function usuarioPorToken($token)
    {
        if (!is_string($token) || !preg_match('/^[A-Za-z0-9_-]{48}$/', $token)) {
            return null;
        }
        $u = Usuarios::findOne(['reset_token_hash' => hash('sha256', $token), 'idrol' => Rol::INVERSOR, 'estado' => '1']);
        if ($u === null || !$u->reset_token_expira || strtotime($u->reset_token_expira) < time()) {
            return null;
        }
        return $u;
    }

    public function guardarContrasena(Usuarios $u, $contrasena)
    {
        Yii::$app->db->createCommand()->update('usuarios', [
            'contrasena' => Yii::$app->security->generatePasswordHash($contrasena),
            'reset_token_hash' => null, 'reset_token_expira' => null,
            'intentos_fallidos' => 0, 'bloqueado_hasta' => null,
            'auth_key' => Yii::$app->security->generateRandomString(32),
        ], ['idusuario' => $u->idusuario])->execute();
        $inv = Inversor::findOne($u->idusuario);
        $activacion = $inv && !$inv->email_verificado;
        if ($activacion) {
            $inv->updateAttributes(['email_verificado' => date('Y-m-d H:i:s')]); // el enlace llegó a su correo: queda confirmado
        }
        InversorHistorial::registrar($u->idusuario, 'Contraseña', $activacion ? 'Activó su cuenta y creó su contraseña.' : 'Cambió su contraseña con el enlace de recuperación.');
        return true;
    }

    public function correoConfirmado($email): bool
    {
        if (!is_string($email) || $email === '') {
            return false;
        }
        return Inversor::find()->alias('i')->innerJoin(['u' => Usuarios::tableName()], 'u.idusuario = i.idusuario')
            ->where(['u.email' => $email])->andWhere(['not', ['i.email_verificado' => null]])->exists();
    }

    public function motivoSinAcceso(Usuarios $u)
    {
        $inv = Inversor::findOne($u->idusuario);
        if ($inv === null) {
            return 'Su cuenta no tiene una solicitud de inversor.';
        }
        if (!$inv->email_verificado) {
            return $inv->origen === 'web'
                ? 'Todavía no confirmó su correo. Busque el mensaje que le enviamos (revise spam) o pida uno nuevo.'
                : 'Todavía no activó su cuenta. Use el enlace del correo "Active su cuenta" o pida uno nuevo con "¿Olvidó su contraseña?".';
        }
        // Solo entra el aprobado: en revisión o rechazado ve una pantalla con su estado (sin iniciar sesión).
        if ($inv->estado_solicitud === 'rechazado') {
            return 'Su solicitud fue rechazada.';
        }
        if ($inv->estado_solicitud !== 'aprobado') {
            return 'Su solicitud todavía está en revisión.';
        }
        return null;
    }

    public function estadoSinAcceso(Usuarios $u): ?array
    {
        $inv = Inversor::findOne($u->idusuario);
        if ($inv === null || !$inv->email_verificado || $inv->estado_solicitud === 'aprobado') {
            return null;
        }
        return ['estado' => $inv->estado_solicitud === 'rechazado' ? 'rechazado' : 'revision',
            'nombre' => $u->nombrecompleto, 'motivo' => $inv->estado_solicitud === 'rechazado' ? $inv->motivo_rechazo : null];
    }

    public function perfil($idusuario): ?Inversor
    {
        return Inversor::find()->with(['usuario', 'intereses', 'areas'])->where(['idusuario' => (int) $idusuario])->one();
    }

    /* ================= Ayudas ================= */

    private function datosCuenta(SolicitudInversorForm $f)
    {
        return [
            'nombrecompleto' => $f->nombre,
            'usuario' => $f->email,
            'email' => $f->email,
            'movil' => $f->celular,
            'telefono' => $f->celular,
            'pais' => $f->pais,
            'ciudad' => $f->ciudad,
            'empresa' => $f->tipo === 'empresa' ? (string) $f->razon_social : '',
            'cargo' => $f->tipo === 'empresa' ? (string) $f->cargo : '',
        ];
    }

    private function datosSolicitud(SolicitudInversorForm $f)
    {
        $empresa = $f->tipo === 'empresa';
        return [
            'tipo' => $f->tipo,
            'documento_tipo' => $f->documento_tipo,
            'documento_numero' => $f->documento_numero,
            'razon_social' => $empresa ? $f->razon_social : null,
            'nit' => $empresa ? $f->nit : null,
            'sitio_web' => $f->sitio_web ?: null,
            'monto_minimo' => $f->monto_minimo,
            'monto_maximo' => $f->monto_maximo,
            'idmoneda' => (int) $f->idmoneda ?: null,
            'plazo_preferido' => $f->plazo_preferido ?: null,
            'participacion_preferida' => $f->participacion_preferida ?: null,
            'como_nos_conocio' => $f->como_nos_conocio ?: null,
            'mensaje' => $f->mensaje ?: null,
        ];
    }

    /** Inserta la cuenta (sin pasar por las reglas viejas del modelo Usuarios). */
    private function crearCuenta(SolicitudInversorForm $f, $hash)
    {
        Yii::$app->db->createCommand()->insert('usuarios', $this->datosCuenta($f) + [
            'contrasena' => $hash,
            'idrol' => Rol::INVERSOR,
            'estado' => '1',
            'auth_key' => Yii::$app->security->generateRandomString(32),
            'fecha_registro' => date('Y-m-d H:i:s'),
        ])->execute();
        return (int) Yii::$app->db->getLastInsertID();
    }

    private function crearSolicitud($id, SolicitudInversorForm $f, $origen)
    {
        $inv = new Inversor(['idusuario' => $id, 'origen' => $origen, 'estado_solicitud' => 'sin_verificar', 'fecha_registro' => date('Y-m-d H:i:s')]);
        $inv->setAttributes($this->datosSolicitud($f), false);
        $inv->save(false);
        $this->guardarRelacion('inversor_interes', $id, $f->intereses);
        return $inv;
    }

    private function guardarRelacion($tabla, $idusuario, array $ids)
    {
        $db = Yii::$app->db;
        $db->createCommand()->delete($tabla, ['idusuario' => (int) $idusuario])->execute();
        $filas = array_map(fn($e) => [(int) $idusuario, (int) $e], array_unique(array_map('intval', $ids)));
        if ($filas) {
            $db->createCommand()->batchInsert($tabla, ['idusuario', 'idecosistema'], $filas)->execute();
        }
    }

    private function areasValidas(array $areas)
    {
        $ids = array_unique(array_filter(array_map('intval', $areas)));
        return $ids ? array_map('intval', Ecosistema::find()->select('idecosistema')->where(['idecosistema' => $ids])->column()) : [];
    }

    private function resumen(Inversor $inv)
    {
        $inv = Inversor::find()->with('intereses')->where(['idusuario' => $inv->idusuario])->one();
        return 'Tipo: ' . (Inversor::TIPOS[$inv->tipo] ?? $inv->tipo)
            . "\nInterés: " . implode(', ', array_column($inv->intereses, 'nombre'))
            . "\nMonto: " . $inv->montoTexto();
    }

    private function mandarVerificacion(Usuarios $u, Inversor $inv)
    {
        $token = Yii::$app->security->generateRandomString(48);
        $inv->updateAttributes([
            'verificacion_hash' => hash('sha256', $token),
            'verificacion_expira' => date('Y-m-d H:i:s', time() + self::HORAS_VERIFICACION * 3600),
        ]);
        return $this->intentar(fn() => $this->correos->enviarCorreoVerificacionInversor($u, Url::to(['/cuenta/acceso/verificar', 'token' => $token], true)));
    }

    private function mandarActivacion(Usuarios $u)
    {
        $token = Yii::$app->security->generateRandomString(48);
        Yii::$app->db->createCommand()->update('usuarios', [
            'reset_token_hash' => hash('sha256', $token),
            'reset_token_expira' => date('Y-m-d H:i:s', time() + self::HORAS_ACTIVACION * 3600),
        ], ['idusuario' => $u->idusuario])->execute();
        return $this->intentar(fn() => $this->correos->enviarCorreoActivacionInversor($u, Url::to(['/cuenta/acceso/restablecer', 'token' => $token, 'activar' => 1], true)));
    }

    /**
     * Solicitud nueva (confirmó su correo): avisa al correo del sistema (Panel → Configuración), a los Superadmin
     * y a todos los distribuidores activos, con un resumen. A los gerentes no: todavía no tiene áreas asignadas.
     */
    private function avisarDistribuidores(Inversor $inv)
    {
        $emails = Usuarios::find()->select('email')->where(['idrol' => [Rol::SUPERADMIN, Rol::DISTRIBUIDOR], 'estado' => '1'])->column();
        $corporativo = Configuracion::find()->select('email')->scalar();
        $emails = array_values(array_unique(array_filter(array_map('trim', array_merge([(string) $corporativo], $emails)))));
        if (!$emails) {
            return;
        }
        $inv = Inversor::find()->with(['intereses', 'usuario'])->where(['idusuario' => $inv->idusuario])->one();
        $resumen = [
            'Nombre' => $inv->usuario->nombrecompleto,
            'Invierte como' => (Inversor::TIPOS[$inv->tipo] ?? $inv->tipo) . ($inv->tipo === 'empresa' && $inv->razon_social ? ' · ' . $inv->razon_social : ''),
            'País / ciudad' => trim($inv->usuario->pais . ' · ' . $inv->usuario->ciudad, ' ·'),
            'Correo' => $inv->usuario->email,
            'Celular' => $inv->usuario->movil ?: $inv->usuario->telefono,
            'Áreas de interés' => implode(', ', array_column($inv->intereses, 'nombre')),
            'Monto' => $inv->montoTexto(),
            'Plazo' => Inversor::PLAZOS[$inv->plazo_preferido] ?? null,
        ];
        $enlace = Url::to(['/admin/inversores/view', 'id' => $inv->idusuario], true);
        $this->intentar(fn() => $this->correos->enviarCorreoNuevaSolicitudInversor($emails, $inv->usuario, $enlace, $resumen));
    }

    /**
     * A los gerentes activos de las áreas $idsAreas (Panel → Usuarios → ecosistemas del gerente): un inversor fue
     * habilitado ahí. Cada gerente recibe un solo correo, con las áreas suyas.
     */
    private function avisarGerentes(Inversor $inv, array $idsAreas)
    {
        $idsAreas = array_values(array_unique(array_map('intval', $idsAreas)));
        if (!$idsAreas) {
            return;
        }
        $filas = (new \yii\db\Query())->select(['ue.idusuario', 'e.nombre'])->from(['ue' => 'usuario_ecosistema'])
            ->innerJoin(['u' => 'usuarios'], 'u.idusuario = ue.idusuario')
            ->innerJoin(['e' => 'ecosistema'], 'e.idecosistema = ue.idecosistema')
            ->where(['ue.idecosistema' => $idsAreas, 'u.idrol' => Rol::GERENTE, 'u.estado' => '1'])
            ->orderBy(['e.numero' => SORT_ASC])->all();
        $porGerente = [];
        foreach ($filas as $f) {
            $porGerente[(int) $f['idusuario']][] = $f['nombre'];
        }
        if (!$porGerente) {
            return;
        }
        $enlace = Url::to(['/admin/inversores/view', 'id' => $inv->idusuario], true);
        foreach (Usuarios::findAll(array_keys($porGerente)) as $g) {
            if (trim((string) $g->email) !== '') {
                $this->intentar(fn() => $this->correos->enviarCorreoGerenteNuevoInversor($g, $inv->usuario, $porGerente[(int) $g->idusuario], $enlace));
            }
        }
    }

    /** Un correo que falla no debe romper la operación: queda en el log. */
    private function intentar(callable $enviar)
    {
        try {
            return (bool) $enviar();
        } catch (\Throwable $e) {
            Yii::error('No se pudo enviar un correo de inversores: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /* ================= Exportar ================= */

    public function exportar($params, $pestana)
    {
        $this->permisos->verificarPermiso('inversores_exportar');
        $search = new InversorSearch();
        $modelos = $search->search($params, $pestana, $this->alcance(), Yii::$app->user->id)->query
            ->with(['usuario', 'intereses', 'areas', 'moneda'])->all();
        $vigente = \app\models\NdaVersion::vigente();
        $aceptaron = $vigente ? array_flip(\app\models\NdaAceptacion::find()->select('idusuario')->where(['idversion' => $vigente->idversion])->column()) : [];
        $fecha = fn($v) => $v ? date('d/m/Y H:i', strtotime($v)) : '';
        $columnas = [
            'Inversor' => fn(Inversor $m) => $m->nombreVisible(),
            'Tipo' => fn($m) => Inversor::TIPOS[$m->tipo] ?? $m->tipo,
            'Razón social' => fn($m) => $m->razon_social,
            'NIT' => fn($m) => $m->nit,
            'Representante / nombre' => fn($m) => $m->usuario->nombrecompleto,
            'Cargo' => fn($m) => $m->tipo === 'empresa' ? $m->usuario->cargo : '',
            'Documento' => fn($m) => (Inversor::DOCUMENTOS[$m->documento_tipo] ?? $m->documento_tipo) . ' ' . $m->documento_numero,
            'Email' => fn($m) => $m->usuario->email,
            'Celular / WhatsApp' => fn($m) => $m->usuario->movil,
            'País' => fn($m) => $m->usuario->pais,
            'Ciudad' => fn($m) => $m->usuario->ciudad,
            'Áreas de interés' => fn($m) => implode(', ', array_column($m->intereses, 'nombre')),
            'Áreas aprobadas' => fn($m) => implode(', ', array_column($m->areas, 'nombre')),
            'Moneda' => fn($m) => $m->codigoMoneda(),
            'Monto mínimo' => fn($m) => number_format((float) $m->monto_minimo, 2, '.', ''),
            'Monto máximo' => fn($m) => number_format((float) $m->monto_maximo, 2, '.', ''),
            'Plazo' => fn($m) => Inversor::PLAZOS[$m->plazo_preferido] ?? '',
            'Participación' => fn($m) => Inversor::PARTICIPACIONES[$m->participacion_preferida] ?? '',
            'Estado de la solicitud' => fn($m) => $m->estadoNombre(),
            'Motivo de rechazo' => fn($m) => $m->motivo_rechazo,
            'Cuenta' => fn($m) => $m->usuario->estado === '1' ? 'Activa' : 'Bloqueada',
            'Acuerdo vigente' => fn($m) => $vigente === null ? '' : (isset($aceptaron[$m->idusuario]) ? 'Aceptado (v' . $vigente->numero . ')' : 'Falta'),
            'Invertido (vigente)' => fn($m) => \app\models\InversionPago::totalesTexto(\app\models\InversionPago::totalesPorMoneda(['idusuario' => $m->idusuario])),
            'Origen' => fn($m) => $m->origen === 'web' ? 'Web' : 'Panel',
            'Fecha de registro' => fn($m) => $fecha($m->fecha_registro),
            'Fecha de aprobación' => fn($m) => $fecha($m->fecha_aprobacion),
            '¿Cómo nos conoció?' => fn($m) => $m->como_nos_conocio,
        ];
        $nombres = ['todos' => 'Todos', 'pendiente' => 'Pendientes', 'aprobado' => 'Aprobados', 'rechazado' => 'Rechazados', 'sin_verificar' => 'Sin confirmar correo', 'bloqueado' => 'Bloqueados'];
        $filtros = ['Pestaña: ' . ($nombres[$pestana] ?? $pestana)];
        if ((string) $search->texto !== '') {
            $filtros[] = 'Búsqueda: ' . $search->texto;
        }
        if ($search->tipo) {
            $filtros[] = 'Tipo: ' . Inversor::TIPOS[$search->tipo];
        }
        if ($search->interes && ($e = Ecosistema::findOne((int) $search->interes))) {
            $filtros[] = 'Área: ' . $e->nombre;
        }
        if ((string) $search->monto_desde !== '' || (string) $search->monto_hasta !== '') {
            $filtros[] = 'Monto (USD): ' . ($search->monto_desde ?: '…') . ' a ' . ($search->monto_hasta ?: '…');
        }
        if ($search->nda) {
            $filtros[] = 'Acuerdo: ' . ($search->nda === 'si' ? 'aceptado' : 'falta');
        }
        return ['titulo' => 'Inversores', 'columnas' => $columnas, 'modelos' => $modelos, 'filtros' => $filtros];
    }

}

<?php

namespace app\components;

use app\models\Configuracion;
use app\models\ContrasenaInversorForm;
use app\models\Ecosistema;
use app\models\InversionPago;
use app\models\Inversor;
use app\models\InversorHistorial;
use app\models\NdaAceptacion;
use app\models\PerfilInversorForm;
use app\models\Proyecto;
use app\models\TerminosVersion;
use app\models\Usuarios;
use Yii;
use yii\base\DynamicModel;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * Mi cuenta del inversor (ver InterfacePerfilInversor).
 * Los campos que puede cambiar están fijos en PerfilInversorForm (escenarios) y acá se copian uno por uno:
 * aunque alguien agregue campos al formulario desde la consola del navegador, no se guardan.
 */
class PerfilInversorService implements InterfacePerfilInversor
{
    const MAX_FOTO_MB = 8;
    const LADO_FOTO = 480;          // px de la foto guardada (cuadrada)
    const LADO_MAXIMO_ORIGINAL = 9000;
    const FORMATOS_FOTO = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    private $correos;

    public function __construct(InterfaceCorreos $correos)
    {
        $this->correos = $correos;
    }

    /* ================= Lectura ================= */

    public function resumen(int $idusuario): array
    {
        $inv = Inversor::find()->with(['usuario', 'intereses', 'areas', 'moneda'])->where(['idusuario' => $idusuario])->one();
        if ($inv === null) {
            throw new NotFoundHttpException('No encontramos su perfil de inversor.');
        }
        $aprobado = $inv->estado_solicitud === 'aprobado';
        $idsAreas = array_map('intval', array_column($inv->areas, 'idecosistema'));

        $proyectos = [];
        foreach ($aprobado ? $inv->areas : [] as $area) {
            $proyectos[$area->idecosistema] = (int) Proyecto::publicados([$area->idecosistema])->count();
        }
        // Pedidas desde Mi cuenta y todavía sin decidir (las marcadas al registrarse ya se decidieron al aprobar).
        $idsPedidos = $aprobado ? array_diff(Inversor::idsPedidos($idusuario), $idsAreas) : [];
        $pendientes = array_values(array_filter($inv->intereses, fn($e) => in_array((int) $e->idecosistema, $idsPedidos, true)));
        $tomadas = array_merge($idsAreas, $idsPedidos);
        $disponibles = $aprobado ? array_diff_key(Ecosistema::lista(true), array_flip($tomadas)) : [];

        return [
            'inversor' => $inv,
            'nda' => NdaAceptacion::find()->with('version')->where(['idusuario' => $idusuario])->orderBy(['fecha' => SORT_DESC])->one(),
            'terminos' => $inv->terminos_version ? TerminosVersion::findOne(['numero' => $inv->terminos_version]) : null,
            'proyectos' => $proyectos,
            'pendientes' => $pendientes,
            'disponibles' => $disponibles,
            'inversiones' => $aprobado ? $this->inversiones($idusuario, $idsAreas) : [],
            'tieneFoto' => $this->archivoFoto($idusuario) !== null,
        ];
    }

    /**
     * Sus pagos vigentes agrupados por proyecto (los anulados no se muestran).
     * @return array [['proyecto' => Proyecto, 'enlace' => bool, 'totales' => string, 'pagos' => InversionPago[]], ...]
     */
    private function inversiones(int $idusuario, array $idsAreas): array
    {
        $pagos = InversionPago::find()->with(['proyecto.ecosistema', 'moneda', 'monedaProyecto'])
            ->where(['idusuario' => $idusuario, 'estado' => 'vigente'])
            ->orderBy(['fecha_pago' => SORT_DESC, 'idpago' => SORT_DESC])->all();
        $res = [];
        foreach ($pagos as $pago) {
            $id = (int) $pago->idproyecto;
            if (!isset($res[$id])) {
                $p = $pago->proyecto;
                $res[$id] = [
                    'proyecto' => $p,
                    'enlace' => $p && $p->esVisibleEnWeb() && in_array((int) $p->idecosistema, $idsAreas, true),
                    'totales' => InversionPago::totalesTexto(InversionPago::totalesPorMoneda(['idproyecto' => $id, 'idusuario' => $idusuario])),
                    'pagos' => [],
                ];
            }
            $res[$id]['pagos'][] = $pago;
        }
        return array_values($res);
    }

    public function archivoFoto(int $idusuario): ?string
    {
        $nombre = Usuarios::find()->select('foto_perfil')->where(['idusuario' => $idusuario])->scalar();
        if (!$nombre) {
            return null;
        }
        $ruta = Inversor::carpetaDe($idusuario) . '/' . basename((string) $nombre);
        return is_file($ruta) ? $ruta : null;
    }

    public function archivoComprobante(int $idusuario, int $idpago): array
    {
        $pago = InversionPago::findOne(['idpago' => $idpago, 'idusuario' => $idusuario, 'estado' => 'vigente']);
        $ruta = $pago && $pago->comprobante_archivo ? InversionPago::carpetaDe($pago->idproyecto) . '/' . basename($pago->comprobante_archivo) : null;
        if ($ruta === null || !is_file($ruta)) {
            throw new NotFoundHttpException('Este pago no tiene comprobante.');
        }
        $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
        InversorHistorial::registrar($idusuario, 'Inversión', 'Abrió el comprobante del pago del ' . date('d/m/Y', strtotime($pago->fecha_pago)) . ' (' . $pago->montoTexto() . ').');
        return ['ruta' => $ruta, 'nombre' => $pago->comprobante_nombre ?: 'comprobante.' . $ext, 'mime' => InversionPago::FORMATOS_COMPROBANTE[$ext][0] ?? null];
    }

    /* ================= Cambios ================= */

    public function guardarDatos(Inversor $inv, PerfilInversorForm $f, array $post): array
    {
        if (!$f->load($post) || !$f->validate()) {
            return ['exito' => false, 'mensaje' => 'Revise los campos marcados.'];
        }
        $u = $inv->usuario;
        $antes = ['Celular' => $u->movil, 'País' => $u->pais, 'Ciudad' => $u->ciudad];
        $usuario = ['movil' => $f->celular, 'telefono' => $f->celular, 'pais' => $f->pais, 'ciudad' => $f->ciudad];
        $despues = ['Celular' => $f->celular, 'País' => $f->pais, 'Ciudad' => $f->ciudad];
        $inversor = [];
        if ($f->scenario === PerfilInversorForm::SCENARIO_EMPRESA) {
            $antes += ['Razón social' => $inv->razon_social, 'NIT' => $inv->nit, 'Cargo' => $u->cargo, 'Sitio web' => $inv->sitio_web];
            $usuario['cargo'] = $f->cargo;
            $inversor = ['razon_social' => $f->razon_social, 'nit' => $f->nit, 'sitio_web' => $f->sitio_web];
            $despues += ['Razón social' => $f->razon_social, 'NIT' => $f->nit, 'Cargo' => $f->cargo, 'Sitio web' => $f->sitio_web];
        }
        $cambios = array_keys(array_filter($despues, fn($v, $k) => (string) $v !== (string) ($antes[$k] ?? ''), ARRAY_FILTER_USE_BOTH));
        if (!$cambios) {
            return ['exito' => true, 'mensaje' => 'No había cambios para guardar.'];
        }
        $tx = Yii::$app->db->beginTransaction();
        try {
            Yii::$app->db->createCommand()->update(Usuarios::tableName(), $usuario, ['idusuario' => $u->idusuario])->execute();
            if ($inversor) {
                Yii::$app->db->createCommand()->update(Inversor::tableName(), $inversor + ['fecha_actualizacion' => date('Y-m-d H:i:s')],
                    ['idusuario' => $inv->idusuario])->execute();
            }
            InversorHistorial::registrar($inv->idusuario, 'Perfil', 'Actualizó desde Mi cuenta: ' . implode(', ', $cambios) . '.');
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error('No se pudo guardar el perfil del inversor ' . $inv->idusuario . ': ' . $e->getMessage(), __METHOD__);
            return ['exito' => false, 'mensaje' => 'No se pudieron guardar los cambios. Intente de nuevo.'];
        }
        return ['exito' => true, 'mensaje' => 'Sus datos se guardaron correctamente.'];
    }

    public function cambiarContrasena(Inversor $inv, ContrasenaInversorForm $f, array $post): array
    {
        if (!$f->load($post) || !$f->validate()) {
            return ['exito' => false, 'mensaje' => 'Revise los campos marcados.'];
        }
        $u = $inv->usuario;
        if (!Yii::$app->security->validatePassword((string) $f->actual, (string) $u->contrasena)) {
            $f->addError('actual', 'La contraseña actual no es correcta.');
            InversorHistorial::registrar($inv->idusuario, 'Contraseña', 'Intentó cambiar la contraseña desde Mi cuenta con una contraseña actual incorrecta.');
            return ['exito' => false, 'mensaje' => 'Revise los campos marcados.'];
        }
        $authKey = Yii::$app->security->generateRandomString(32);
        Yii::$app->db->createCommand()->update(Usuarios::tableName(), [
            'contrasena' => Yii::$app->security->generatePasswordHash($f->nueva),
            'reset_token_hash' => null, 'reset_token_expira' => null,
            'auth_key' => $authKey, // cierra las sesiones "recordarme" de otros equipos
        ], ['idusuario' => $u->idusuario])->execute();
        $u->auth_key = $authKey;
        InversorHistorial::registrar($inv->idusuario, 'Contraseña', 'Cambió su contraseña desde Mi cuenta.');
        $this->intentar(fn() => $this->correos->enviarCorreoContrasenaCambiadaInversor($u));
        return ['exito' => true, 'mensaje' => 'Su contraseña se cambió correctamente. Le enviamos un aviso por correo.'];
    }

    public function subirFoto(Inversor $inv): array
    {
        $archivo = UploadedFile::getInstanceByName('foto');
        $v = DynamicModel::validateData(['foto' => $archivo], [
            [['foto'], 'required', 'message' => 'Elija una foto.'],
            [['foto'], 'file', 'extensions' => ['jpg', 'jpeg', 'png', 'webp'], 'checkExtensionByMimeType' => true,
                'mimeTypes' => array_keys(self::FORMATOS_FOTO), 'maxSize' => self::MAX_FOTO_MB * 1024 * 1024,
                'tooBig' => 'La foto pesa más de ' . self::MAX_FOTO_MB . ' MB.', 'wrongExtension' => 'Formato no permitido: JPG, PNG o WEBP.',
                'wrongMimeType' => 'Formato no permitido: JPG, PNG o WEBP.'],
        ]);
        if ($v->hasErrors()) {
            return ['exito' => false, 'mensaje' => $v->getFirstError('foto')];
        }
        $imagen = $this->imagenCuadrada($archivo->tempName);
        if ($imagen === null) {
            return ['exito' => false, 'mensaje' => 'No pudimos leer esa imagen. Pruebe con otra foto (JPG, PNG o WEBP).'];
        }
        $carpeta = Inversor::carpetaDe($inv->idusuario);
        if (!is_dir($carpeta) && !@mkdir($carpeta, 0750, true)) {
            imagedestroy($imagen);
            return ['exito' => false, 'mensaje' => 'No se pudo guardar la foto. Intente de nuevo.'];
        }
        $nombre = 'foto-' . Yii::$app->security->generateRandomString(16) . '.webp';
        $ok = imagewebp($imagen, $carpeta . '/' . $nombre, 82);
        imagedestroy($imagen);
        if (!$ok) {
            return ['exito' => false, 'mensaje' => 'No se pudo guardar la foto. Intente de nuevo.'];
        }
        $anterior = $this->archivoFoto($inv->idusuario);
        Yii::$app->db->createCommand()->update(Usuarios::tableName(), ['foto_perfil' => $nombre], ['idusuario' => $inv->idusuario])->execute();
        if ($anterior) {
            @unlink($anterior);
        }
        InversorHistorial::registrar($inv->idusuario, 'Perfil', $anterior ? 'Cambió su foto de perfil.' : 'Agregó su foto de perfil.');
        return ['exito' => true, 'mensaje' => 'Su foto de perfil se actualizó.'];
    }

    public function quitarFoto(Inversor $inv): array
    {
        $ruta = $this->archivoFoto($inv->idusuario);
        Yii::$app->db->createCommand()->update(Usuarios::tableName(), ['foto_perfil' => null], ['idusuario' => $inv->idusuario])->execute();
        if ($ruta) {
            @unlink($ruta);
            InversorHistorial::registrar($inv->idusuario, 'Perfil', 'Quitó su foto de perfil.');
        }
        return ['exito' => true, 'mensaje' => 'Se quitó su foto de perfil.'];
    }

    public function pedirAreas(Inversor $inv, array $ids): array
    {
        if ($inv->estado_solicitud !== 'aprobado') {
            return ['exito' => false, 'mensaje' => 'Podrá pedir más áreas cuando su solicitud esté aprobada.'];
        }
        $resumen = $this->resumen((int) $inv->idusuario);
        $ids = array_values(array_intersect(array_map('intval', $ids), array_map('intval', array_keys($resumen['disponibles']))));
        if (!$ids) {
            return ['exito' => false, 'mensaje' => 'Elija al menos un área de la lista.'];
        }
        // Si ya la había marcado al registrarse, el pedido nuevo la reemplaza (con fecha = pendiente).
        $ahora = date('Y-m-d H:i:s');
        Yii::$app->db->createCommand()->delete('inversor_interes', ['idusuario' => (int) $inv->idusuario, 'idecosistema' => $ids])->execute();
        Yii::$app->db->createCommand()->batchInsert('inversor_interes', ['idusuario', 'idecosistema', 'pedido_fecha'],
            array_map(fn($id) => [(int) $inv->idusuario, $id, $ahora], $ids))->execute();
        $nombres = array_values(array_intersect_key($resumen['disponibles'], array_flip($ids)));
        InversorHistorial::registrar($inv->idusuario, 'Áreas', 'Pidió acceso desde Mi cuenta a: ' . implode(', ', $nombres) . '.');

        $emails = Usuarios::find()->select('email')->where(['idrol' => [Rol::SUPERADMIN, Rol::DISTRIBUIDOR], 'estado' => '1'])->column();
        $emails = array_values(array_unique(array_filter(array_map('trim', array_merge([(string) Configuracion::find()->select('email')->scalar()], $emails)))));
        $enlace = Url::to(['/admin/inversores/view', 'id' => $inv->idusuario], true);
        $this->intentar(fn() => $this->correos->enviarCorreoPedidoAreas($emails, $inv->usuario, $nombres, $enlace));
        return ['exito' => true, 'mensaje' => 'Recibimos su pedido. Le avisaremos por correo cuando se habiliten las áreas.'];
    }

    /* ================= Ayudas ================= */

    /** Abre la imagen, la endereza (EXIF de celulares), la recorta al centro y la deja de LADO_FOTO px. */
    private function imagenCuadrada(string $ruta)
    {
        $info = @getimagesize($ruta);
        if (!$info || !isset(self::FORMATOS_FOTO[$info['mime']]) || $info[0] < 1 || $info[1] < 1
            || $info[0] > self::LADO_MAXIMO_ORIGINAL || $info[1] > self::LADO_MAXIMO_ORIGINAL) {
            return null;
        }
        $src = @imagecreatefromstring((string) file_get_contents($ruta));
        if (!$src) {
            return null;
        }
        if ($info['mime'] === 'image/jpeg' && function_exists('exif_read_data')) {
            $giro = [3 => 180, 6 => -90, 8 => 90][(int) (@exif_read_data($ruta)['Orientation'] ?? 1)] ?? 0;
            if ($giro && ($rotada = imagerotate($src, $giro, 0))) {
                imagedestroy($src);
                $src = $rotada;
            }
        }
        $w = imagesx($src);
        $h = imagesy($src);
        $lado = min($w, $h);
        $destino = imagecreatetruecolor(self::LADO_FOTO, self::LADO_FOTO);
        imagefill($destino, 0, 0, imagecolorallocate($destino, 255, 255, 255)); // PNG con transparencia: fondo blanco
        imagecopyresampled($destino, $src, 0, 0, (int) (($w - $lado) / 2), (int) (($h - $lado) / 2), self::LADO_FOTO, self::LADO_FOTO, $lado, $lado);
        imagedestroy($src);
        return $destino;
    }

    private function intentar(callable $enviar)
    {
        try {
            return (bool) $enviar();
        } catch (\Throwable $e) {
            Yii::error('No se pudo enviar un correo de Mi cuenta: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }
}

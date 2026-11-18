<?php



namespace app\components;

use app\models\CambioPasswordObligatorioForm;
use app\models\ChangePasswordForm;
use app\models\Forget;
use app\models\Usuarios;
use Yii;
use yii\helpers\Url;

class CuentaService implements InterfaceCuenta
{
    private $notiService;
    private $correoService;

    public function __construct(InterfaceNoti $notiService, InterfaceCorreos $correoService)
    {
        $this->notiService = $notiService;
        $this->correoService = $correoService;
    }

    public function actualizarUsuario($model, $datosPost)
    {
        $modelAnterior = clone $model;
        $loaded = $model->load($datosPost);
        // Desde "Mi perfil" no se cambia contraseña, estado, rol ni campos internos.
        foreach (array_merge(AdminService::CAMPOS_PROTEGIDOS, ['idrol']) as $campo) {
            if ($model->hasAttribute($campo)) {
                $model->setAttribute($campo, $modelAnterior->getAttribute($campo));
            }
        }
        if ($loaded && $model->validate(array_diff($model->activeAttributes(), AdminService::CAMPOS_PROTEGIDOS, ['idrol', 'ecosistemasIds']))) {

            if ($model->save(false)) {
                // Actualiza los datos en la sesión actual manualmente (sin romper sesión)
                Yii::$app->user->identity->nombrecompleto = $model->nombrecompleto;
                Yii::$app->user->identity->email = $model->email;
                // Agregá más si querés reflejar más datos actualizados

                $cambios = [];
                foreach ($model->attributes as $atributo => $nuevoValor) {
                    $anterior = $modelAnterior->{$atributo};
                    if (in_array($atributo, AdminService::CAMPOS_PROTEGIDOS, true) || (string) $nuevoValor === (string) $anterior) {
                        continue;
                    }
                    $cambios[] = $model->getAttributeLabel($atributo) . ': '
                        . ((string) $anterior === '' ? '(vacío)' : $anterior) . ' → '
                        . ((string) $nuevoValor === '' ? '(vacío)' : $nuevoValor);
                }

                if (!empty($cambios)) {
                    $historial = new \app\models\HistorialUsuarios();
                    $historial->idusuario_afectado = $model->idusuario;
                    $historial->idusuario_actor = Yii::$app->user->id;
                    $historial->accion = 'Edición personal';
                    $historial->descripcion = implode("\n", $cambios);
                    $historial->fecha = date('Y-m-d H:i:s');
                    $historial->save(false);
                }


                Yii::$app->session->setFlash('flash', [
                    'type' => 'success',
                    'message' => 'Datos actualizados correctamente.'
                ]);

                return ['exito' => true];
            }
        }

        Yii::$app->session->setFlash('flash', [
            'type' => 'error',
            'message' => 'Error al guardar los datos.'
        ]);

        return ['exito' => false, 'model' => $model];
    }




    public function desbloquearintentosFallidos($id)
    {
        $usuario = Usuarios::findOne($id);
        if (!$usuario) {
            return ['exito' => false, 'mensaje' => 'Usuario no encontrado.'];
        }

        $usuario->updateAttributes(['intentos_fallidos' => 0, 'bloqueado_hasta' => null]);

        // Enviar correo (si falla, el desbloqueo igual queda hecho)
        try {
            $this->correoService->enviarCorreoDesbloqueoPorIntentos($usuario);
        } catch (\Throwable $e) {
            Yii::error('No se pudo enviar el aviso de desbloqueo: ' . $e->getMessage(), __METHOD__);
        }

        // Registrar en historial
        $historial = new \app\models\HistorialUsuarios();
        $historial->idusuario_afectado = $usuario->idusuario;
        $historial->idusuario_actor = Yii::$app->user->id;
        $historial->accion = 'Desbloqueo por intentos';
        $historial->descripcion = 'Cuenta desbloqueada manualmente tras bloqueo por intentos fallidos.';
        $historial->fecha = date('Y-m-d H:i:s');
        $historial->save(false);

        return ['exito' => true, 'mensaje' => 'Usuario desbloqueado correctamente.'];
    }


    /** Minutos que dura el enlace de recuperación. */
    const MINUTOS_TOKEN_RECUPERACION = 30;

    /**
     * Paso 1: el usuario pone su correo. Si existe y está activo, se le manda un
     * enlace de un solo uso que vence en 30 minutos. La contraseña NO cambia aquí.
     * Siempre devuelve el mismo mensaje para no revelar qué correos existen.
     */
    public function recuperacionCuenta($email)
    {
        $mensaje = 'Si el correo está registrado, te enviamos un enlace para crear una nueva contraseña. '
            . 'Vence en ' . self::MINUTOS_TOKEN_RECUPERACION . ' minutos. Revisá también la carpeta de spam.';

        $usuario = Usuarios::findOne(['email' => trim((string) $email), 'estado' => '1']);
        if (!$usuario || !Rol::entraAlPanel($usuario->idrol)) {
            return ['exito' => true, 'mensaje' => $mensaje];
        }

        // En la BD solo se guarda el hash del token; el token real viaja únicamente en el correo.
        $token = Yii::$app->security->generateRandomString(48);
        $usuario->updateAttributes([
            'reset_token_hash' => hash('sha256', $token),
            'reset_token_expira' => date('Y-m-d H:i:s', time() + self::MINUTOS_TOKEN_RECUPERACION * 60),
        ]);

        $historial = new \app\models\HistorialUsuarios();
        $historial->idusuario_afectado = $usuario->idusuario;
        $historial->idusuario_actor = null; // Acción iniciada desde el login, sin sesión.
        $historial->accion = 'Solicitud de recuperación de contraseña';
        $historial->descripcion = 'Se envió al correo registrado un enlace para restablecer la contraseña (vence en '
            . self::MINUTOS_TOKEN_RECUPERACION . ' min). IP: ' . (Yii::$app->request->userIP ?? '-');
        $historial->fecha = date('Y-m-d H:i:s');
        $historial->save(false);

        $enlace = Url::to(['/admin/cuenta/restablecer', 'token' => $token], true);
        if (!$this->correoService->enviarCorreoRecuperacion($usuario->idusuario, $enlace)) {
            Yii::error('No se pudo enviar el correo de recuperación al usuario #' . $usuario->idusuario, __METHOD__);
        }

        return ['exito' => true, 'mensaje' => $mensaje];
    }

    /**
     * Devuelve el usuario dueño del token si el token es válido, no venció y la cuenta está activa.
     * Si no, devuelve null.
     */
    public function buscarUsuarioPorTokenRecuperacion($token)
    {
        if (!is_string($token) || !preg_match('/^[A-Za-z0-9_-]{48}$/', $token)) {
            return null;
        }

        $usuario = Usuarios::findOne(['reset_token_hash' => hash('sha256', $token), 'estado' => '1']);
        if (!$usuario || !Rol::entraAlPanel($usuario->idrol) || empty($usuario->reset_token_expira) || strtotime($usuario->reset_token_expira) < time()) {
            return null;
        }

        return $usuario;
    }

    /**
     * Paso 2: guarda la nueva contraseña y anula el enlace (no se puede volver a usar).
     */
    public function restablecerContrasena($usuario, $nuevaContrasena)
    {
        $usuario->updateAttributes([
            'contrasena' => Yii::$app->security->generatePasswordHash($nuevaContrasena),
            'requiere_cambio_contrasena' => 0,
            'reset_token_hash' => null,
            'reset_token_expira' => null,
            // Cierra las sesiones "recordarme" que pudieran estar abiertas con la clave anterior.
            'auth_key' => Yii::$app->security->generateRandomString(),
        ]);

        $historial = new \app\models\HistorialUsuarios();
        $historial->idusuario_afectado = $usuario->idusuario;
        $historial->idusuario_actor = null;
        $historial->accion = 'Restablecimiento de contraseña';
        $historial->descripcion = 'El usuario creó una nueva contraseña desde el enlace enviado a su correo. IP: '
            . (Yii::$app->request->userIP ?? '-');
        $historial->fecha = date('Y-m-d H:i:s');
        $historial->save(false);

        return true;
    }

    public function generarContrasenaAleatoria()
    {
        return Yii::$app->security->generateRandomString(8); // Genera una contraseña de 8 caracteres.
    }


    public function cambiarPassword($usuario, $newPassword)
    {

        $usuario->contrasena = Yii::$app->security->generatePasswordHash($newPassword);
        if ($usuario->save()) {

            $historial = new \app\models\HistorialUsuarios();
            $historial->idusuario_afectado = $usuario->idusuario;
            $historial->idusuario_actor = Yii::$app->user->id ?? null; // puede ser null si no está logueado
            $historial->accion = 'Cambio de contraseña';
            $historial->descripcion = 'El usuario actualizó su contraseña desde su perfil.';
            $historial->fecha = date('Y-m-d H:i:s');
            $historial->save(false); // puedes validar si quieres


            Yii::$app->session->set('user', $usuario);
            $this->notiService->agregarMensajeExito('Contraseña cambiada correctamente!');
            return ['exito' => true];
        } else  {
        $this->notiService->agregarMensajeError('Error al validar el formulario. Verifica los datos ingresados');
        return ['exito' => false];
    }

    }







    public function procesarFormularioCambioPassword($usuario, $datosPost)
    {
        $changePasswordForm = new ChangePasswordForm();

        if ($changePasswordForm->load($datosPost) && $changePasswordForm->validate()) {
            return $this->cambiarPassword($usuario, $changePasswordForm->newPassword);
        }

        // Si hay errores, devolver también el formModel
        return [
            'exito' => false,
            'formModel' => $changePasswordForm
        ];
    }


    public function cambioPasswordObligatorio($usuario, $nuevaContrasena)
    {


        $usuario->contrasena = Yii::$app->security->generatePasswordHash($nuevaContrasena);
        $usuario->requiere_cambio_contrasena = 0;

        if ($usuario->save(false)) {
            Yii::$app->session->setFlash('success', 'Tu contraseña ha sido actualizada correctamente.');
            return ['exito' => true];
        } else {
            Yii::$app->session->setFlash('error', 'No se pudo guardar la nueva contraseña.');
            return ['exito' => false];
        }
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



    public function limpiarFlashes()
    {
        Yii::$app->session->removeFlash('flash'); // <-- este es el importante
        Yii::$app->session->removeFlash('success');
        Yii::$app->session->removeFlash('error');
        Yii::$app->session->removeFlash('warning');
        Yii::$app->session->removeFlash('info');
    }






}
<?php

namespace app\components;

use app\models\Configuracion;
use app\models\Tareas;
use app\models\Usuarios;
use Yii;
use yii\base\Component;
use yii\helpers\Html;
use yii\helpers\Url;

class CorreoService implements InterfaceCorreos
{
/* ===================== CORREOS DE CUENTA (estilo HUK) ===================== */

    /** Enlace al ingreso del panel. */
    private function urlPanel(): string
    {
        return Url::to(['/admin/default/login'], true);
    }

    /**
     * Cuerpo del correo: título dorado, párrafos y botón opcional.
     * Los párrafos llegan YA escapados (lo que viene de la BD pasa por Html::encode antes).
     */
    private function cuerpoHuk(string $titulo, array $parrafos, ?array $boton = null): string
    {
        $html = Html::tag('h2', Html::encode($titulo), [
            'style' => 'color: #e6c27a; font-family: Arimo, Arial, sans-serif; font-size: 22px; margin: 0 0 18px;',
        ]);
        foreach ($parrafos as $p) {
            $html .= Html::tag('p', $p, ['style' => 'color: #ffffff; margin: 0 0 14px;']);
        }
        if ($boton !== null) {
            [$texto, $url] = $boton;
            $html .= Html::tag('p', Html::a(Html::encode($texto), $url, [
                'target' => '_blank',
                'style' => 'display: inline-block; background-color: #bd944f; color: #ffffff; padding: 12px 26px; border-radius: 6px; text-decoration: none; font-weight: bold;',
            ]), ['style' => 'text-align: center; margin: 26px 0 18px;']);
        }
        return $html;
    }

    /** Recuadro con borde dorado (motivo, datos de acceso). $html ya escapado. */
    private function recuadroHuk(string $html): string
    {
        return '<span style="display: block; background-color: #1a1610; border-left: 3px solid #bd944f; border-radius: 4px; padding: 10px 14px; color: #ffffff;">'
            . $html . '</span>';
    }

    private function enviarHuk(string $email, string $asunto, string $cuerpo): bool
    {
        $conf = Configuracion::find()->one();
        $ok = Yii::$app->mailer->compose('layouts/template2', ['content' => $cuerpo, 'config' => $conf])
            ->setTo($email)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - ' . $asunto)
            ->send();
        if (!$ok) {
            Yii::error("No se pudo enviar el correo \"$asunto\" a $email.", __METHOD__);
        }
        return (bool) $ok;
    }

    /** Al crear un usuario del panel. No lleva la contraseña: si no la sabe, usa "¿Olvidaste tu contraseña?". */
    public function enviarCorreodeBienvenida($model)
    {
        $nombre = Html::encode($model->nombrecompleto);
        $rol = Html::encode($model->rol->nombre ?? '');
        $cuerpo = $this->cuerpoHuk('¡Bienvenido/a a HUK-THE-ICHI!', [
            "Hola <strong>$nombre</strong>, se creó tu cuenta en el panel de HUK-THE-ICHI GLOBAL GROUP"
                . ($rol !== '' ? " como <strong>$rol</strong>." : '.'),
            $this->recuadroHuk('Usuario: <strong>' . Html::encode($model->usuario) . '</strong><br>Email: <strong>' . Html::encode($model->email) . '</strong>'),
            'Podés ingresar con tu usuario o con tu email.',
            'Si no conocés tu contraseña, en la pantalla de ingreso tocá <strong>«¿Olvidaste tu contraseña?»</strong> y te llegará un enlace para crearla.',
        ], ['Ingresar al panel', $this->urlPanel()]);

        return $this->enviarHuk($model->email, 'Bienvenido/a', $cuerpo);
    }


    public function enviarCorreodeEditar($model)
    {
        $conf = Configuracion::find()->one();
        $nombre = Html::encode($model->nombrecompleto);
        $loginUrl = Url::to(['site/login'], true);

        $mensaje = Html::tag('h2', "Hola $nombre,", ['style' => 'color: #ffffff;']);
        $mensaje .= Html::tag('p', 'Te informamos que tu información personal ha sido actualizada en el sistema.', ['style' => 'color: #ffffff;']);
        $mensaje .= Html::tag('p', 'Si tú realizaste esta modificación, no necesitas hacer nada más.', ['style' => 'color: #ffffff;']);
        $mensaje .= Html::tag('p', 'Si no reconoces este cambio, por favor comunícate con el equipo de soporte lo antes posible.', ['style' => 'color: #ffffff;']);
        $mensaje .= Html::tag('p', "Puedes acceder al sistema desde <a href=\"$loginUrl\" target=\"_blank\" style=\"color: #ffffff; text-decoration: underline;\">aquí</a>.", ['style' => 'color: #ffffff;']);

        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($model->email)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Edición de cuenta')
            ->send();
    }





    public function enviarCorreoDeInactividad($model, $motivo)
    {
        $conf = Configuracion::find()->one();

        $mensaje = Html::tag('h1', '¡Estimado: ' . $model->nombrecompleto . '!', ['style' => 'color: #ffffff;']);
        $mensaje .= Html::tag(
            'p',
            'Comentarle que su cuenta ha sido suspendida.<br><strong style="color: #ffffff;">Motivo:</strong> <span style="color: #ffffff;">' . Html::encode($motivo) . '</span>',
            ['style' => 'color: #ffffff;']
        );

        $result = Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($model->email)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Cuenta suspendida')
            ->send();

        if ($result) {
            return true;
        } else {
            Yii::error("El correo no pudo ser enviado por razones desconocidas.", __METHOD__);
            return false;
        }
    }




    public function enviarCorreoDeCampaniaBloqueo($model, $correoCliente, $motivo)
    {
        $conf = Configuracion::find()->one();
        $adminEmail = $conf->email ?? 'manueljustinia@gmail.com';

        $mensaje = Html::tag('h2', 'Estimado/a: ' . $model->cliente->nombrecompleto, [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);
        $mensaje .= Html::tag('p', 'Tu campaña ha sido bloqueada por el siguiente motivo:', [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);
        $mensaje .= Html::tag('p', '<strong>Motivo:</strong> ' . $motivo, [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);
        $mensaje .= Html::tag('p', 'Para mayor información, por favor comunícate con el área de soporte.', [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);

        $result = Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoCliente)
            ->setBcc($adminEmail)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Bloqueo de campaña')
            ->send();

        if ($result) {
            return true; // Retorna true si el envío fue exitoso.
        } else {
            Yii::error("El correo no pudo ser enviado por razones desconocidas.", __METHOD__);
            return false;
        }
    }







    public function enviarCorreoDeProyectoBloqueo($model, $correoCliente, $motivo)
    {
        $conf = Configuracion::find()->one();
        $adminEmail = $conf->email ?? 'manueljustinia@gmail.com';

        $mensaje = Html::tag('h2', 'Estimado/a: ' . $model->cliente->nombrecompleto, [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);
        $mensaje .= Html::tag('p', 'Tu proyecto ha sido bloqueado por el siguiente motivo:', [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);
        $mensaje .= Html::tag('p', '<strong>Motivo:</strong> ' . $motivo, [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);
        $mensaje .= Html::tag('p', 'Para mayor información, por favor comunícate con el área de soporte.', [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);

        $result = Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoCliente)
            ->setBcc($adminEmail)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Bloqueo de proyecto')
            ->send();

        if ($result) {
            return true; // Retorna true si el envío fue exitoso.
        } else {
            Yii::error("El correo no pudo ser enviado por razones desconocidas.", __METHOD__);
            return false;
        }
    }






    public function enviarCorreoDeCampaniaDesBloqueo($model, $correoCliente, $motivo)
    {
        $conf = Configuracion::find()->one();
        $adminEmail = $conf->email ?? 'manueljustinia@gmail.com';

        $mensaje = Html::tag('h2', 'Estimado/a: ' . $model->cliente->nombrecompleto, [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);
        $mensaje .= Html::tag('p', 'Tu campaña ha sido reactivada.', [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);
        $mensaje .= Html::tag('p', '<strong>Motivo:</strong> ' . $motivo, [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);
        $mensaje .= Html::tag('p', 'Si tienes dudas, puedes comunicarte con soporte.', [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);

        $result = Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoCliente)
            ->setBcc($adminEmail)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Desbloqueo de campaña')
            ->send();

        if ($result) {
            return true; // Retorna true si el envío fue exitoso.
        } else {
            Yii::error("El correo no pudo ser enviado por razones desconocidas.", __METHOD__);
            return false;
        }
    }



    public function enviarCorreoDeProyectoDesBloqueo($model, $correoCliente, $motivo)
    {
        $conf = Configuracion::find()->one();
        $adminEmail = $conf->email ?? 'manueljustinia@gmail.com';

        $mensaje = Html::tag('h2', 'Estimado/a: ' . $model->cliente->nombrecompleto, [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);
        $mensaje .= Html::tag('p', 'Tu proyecto ha sido reactivado.', [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);
        $mensaje .= Html::tag('p', '<strong>Motivo:</strong> ' . $motivo, [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);
        $mensaje .= Html::tag('p', 'Si tienes dudas, puedes comunicarte con soporte.', [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);

        $result = Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoCliente)
            ->setBcc($adminEmail)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Desbloqueo de proyecto')
            ->send();

        if ($result) {
            return true; // Retorna true si el envío fue exitoso.
        } else {
            Yii::error("El correo no pudo ser enviado por razones desconocidas.", __METHOD__);
            return false;
        }

    }



    /** Bloqueo hecho por un administrador (con motivo). */
    public function enviarCorreoDeBloqueo($model, $motivo)
    {
        $cuerpo = $this->cuerpoHuk('Tu cuenta fue bloqueada', [
            'Hola <strong>' . Html::encode($model->nombrecompleto) . '</strong>, un administrador bloqueó tu acceso al panel por este motivo:',
            $this->recuadroHuk(nl2br(Html::encode($motivo))),
            'Si creés que es un error, comunicate con el administrador.',
        ]);
        return $this->enviarHuk($model->email, 'Cuenta bloqueada', $cuerpo);
    }

    /** Activación hecha por un administrador (con motivo). */
    public function enviarCorreoDedesBloqueo($model, $motivo)
    {
        $cuerpo = $this->cuerpoHuk('Tu cuenta fue activada', [
            'Hola <strong>' . Html::encode($model->nombrecompleto) . '</strong>, ya podés volver a ingresar al panel. Motivo:',
            $this->recuadroHuk(nl2br(Html::encode($motivo))),
        ], ['Ingresar al panel', $this->urlPanel()]);
        return $this->enviarHuk($model->email, 'Cuenta activada', $cuerpo);
    }

    /** Enlace de 30 minutos para crear una contraseña nueva. */
    public function enviarCorreoRecuperacion($id, $enlace)
    {
        $usuario = Usuarios::findOne(['idusuario' => $id]);
        $cuerpo = $this->cuerpoHuk('Recuperación de contraseña', [
            'Hola <strong>' . Html::encode($usuario->nombrecompleto) . '</strong>, recibimos una solicitud para restablecer tu contraseña.',
            'Hacé clic en el botón para crear una nueva. El enlace vence en <strong>30 minutos</strong> y sirve una sola vez.',
        ], ['Crear nueva contraseña', $enlace])
            . Html::tag('p', 'Si el botón no funciona, copiá este enlace en tu navegador:', ['style' => 'color: #ffffff; margin: 0 0 6px;'])
            . Html::tag('p', Html::encode($enlace), ['style' => 'color: #e6c27a; word-break: break-all; font-size: 13px; margin: 0 0 14px;'])
            . Html::tag('p', 'Si no pediste este cambio, ignorá este mensaje: tu contraseña actual sigue funcionando.', ['style' => 'color: #ffffff; margin: 0 0 14px;']);
        return $this->enviarHuk($usuario->email, 'Recuperación de contraseña', $cuerpo);
    }

    /* ===================== INVERSORES (web) ===================== */

    private function urlCuentaInversor(): string
    {
        return Url::to(['/cuenta/acceso/ingresar'], true);
    }

    /** Enlace para copiar si el botón no anda. */
    private function enlaceTexto(string $enlace): string
    {
        return Html::tag('p', 'Si el botón no funciona, copiá este enlace en tu navegador:', ['style' => 'color: #ffffff; margin: 0 0 6px;'])
            . Html::tag('p', Html::encode($enlace), ['style' => 'color: #e6c27a; word-break: break-all; font-size: 13px; margin: 0 0 14px;']);
    }

    /** Registro desde la web: confirmar el correo. */
    public function enviarCorreoVerificacionInversor($usuario, $enlace)
    {
        $cuerpo = $this->cuerpoHuk('Confirmá tu correo', [
            'Hola <strong>' . Html::encode($usuario->nombrecompleto) . '</strong>, gracias por tu interés en invertir con nosotros.',
            'Para enviar tu solicitud, confirmá tu correo con el botón. El enlace vence en <strong>24 horas</strong>.',
        ], ['Confirmar mi correo', $enlace]) . $this->enlaceTexto($enlace)
            . Html::tag('p', 'Si no hiciste este registro, ignorá este mensaje.', ['style' => 'color: #ffffff; margin: 0 0 14px;']);
        return $this->enviarHuk($usuario->email, 'Confirmá tu correo', $cuerpo);
    }

    /** Alta desde el panel: el inversor crea su contraseña. */
    public function enviarCorreoActivacionInversor($usuario, $enlace)
    {
        $cuerpo = $this->cuerpoHuk('Activá tu cuenta de inversor', [
            'Hola <strong>' . Html::encode($usuario->nombrecompleto) . '</strong>, nuestro equipo registró tu solicitud de inversor.',
            'Para entrar a la plataforma, creá tu contraseña con el botón. El enlace vence en <strong>72 horas</strong> y sirve una sola vez.',
        ], ['Crear mi contraseña', $enlace]) . $this->enlaceTexto($enlace);
        return $this->enviarHuk($usuario->email, 'Activá tu cuenta de inversor', $cuerpo);
    }

    public function enviarCorreoRecuperacionInversor($usuario, $enlace)
    {
        $cuerpo = $this->cuerpoHuk('Recuperación de contraseña', [
            'Hola <strong>' . Html::encode($usuario->nombrecompleto) . '</strong>, recibimos una solicitud para restablecer tu contraseña.',
            'Hacé clic en el botón para crear una nueva. El enlace vence en <strong>30 minutos</strong> y sirve una sola vez.',
        ], ['Crear nueva contraseña', $enlace]) . $this->enlaceTexto($enlace)
            . Html::tag('p', 'Si no pediste este cambio, ignorá este mensaje: tu contraseña actual sigue funcionando.', ['style' => 'color: #ffffff; margin: 0 0 14px;']);
        return $this->enviarHuk($usuario->email, 'Recuperación de contraseña', $cuerpo);
    }

    /** Aviso al distribuidor: hay una solicitud nueva para revisar. */
    public function enviarCorreoNuevaSolicitudInversor(array $emails, $usuario, $enlacePanel)
    {
        $cuerpo = $this->cuerpoHuk('Nueva solicitud de inversor', [
            '<strong>' . Html::encode($usuario->nombrecompleto) . '</strong> (' . Html::encode($usuario->email) . ') confirmó su correo y su solicitud está <strong>pendiente de revisión</strong>.',
            'Revisala en el panel para coordinar con la persona, asignarle áreas y aprobarla o rechazarla.',
        ], ['Ver la solicitud', $enlacePanel]);
        $ok = true;
        foreach (array_unique($emails) as $email) {
            $ok = $this->enviarHuk($email, 'Nueva solicitud de inversor', $cuerpo) && $ok;
        }
        return $ok;
    }

    public function enviarCorreoInversorAprobado($usuario, array $areas)
    {
        $lista = implode('<br>', array_map(fn($a) => '• ' . Html::encode($a), $areas));
        $cuerpo = $this->cuerpoHuk('¡Tu solicitud fue aprobada!', array_values(array_filter([
            'Hola <strong>' . Html::encode($usuario->nombrecompleto) . '</strong>, ya podés ver los proyectos de estas áreas:',
            $lista !== '' ? $this->recuadroHuk($lista) : null,
            'Al entrar por primera vez vas a leer y aceptar el acuerdo de confidencialidad.',
        ])), ['Entrar a mi cuenta', $this->urlCuentaInversor()]);
        return $this->enviarHuk($usuario->email, 'Tu solicitud fue aprobada', $cuerpo);
    }

    public function enviarCorreoInversorRechazado($usuario, $motivo)
    {
        $cuerpo = $this->cuerpoHuk('Sobre tu solicitud de inversor', [
            'Hola <strong>' . Html::encode($usuario->nombrecompleto) . '</strong>, gracias por tu interés. Por ahora no pudimos aprobar tu solicitud.',
            $this->recuadroHuk('<strong>Motivo:</strong> ' . Html::encode($motivo)),
            'Si querés más información, respondé este correo o escribinos por los medios de contacto de la web.',
        ]);
        return $this->enviarHuk($usuario->email, 'Sobre tu solicitud de inversor', $cuerpo);
    }

    public function enviarCorreoCuentaInversor($usuario, $bloqueada, $motivo)
    {
        $cuerpo = $this->cuerpoHuk($bloqueada ? 'Tu cuenta fue suspendida' : 'Tu cuenta fue reactivada', [
            'Hola <strong>' . Html::encode($usuario->nombrecompleto) . '</strong>, ' . ($bloqueada
                ? 'suspendimos el acceso a tu cuenta de inversor.' : 'tu cuenta de inversor vuelve a estar activa.'),
            $this->recuadroHuk('<strong>Motivo:</strong> ' . Html::encode($motivo)),
        ], $bloqueada ? null : ['Entrar a mi cuenta', $this->urlCuentaInversor()]);
        return $this->enviarHuk($usuario->email, $bloqueada ? 'Tu cuenta fue suspendida' : 'Tu cuenta fue reactivada', $cuerpo);
    }

    /** Bloqueo automático por intentos fallidos. */
    public function enviarCorreoDeBloqueoporintentos($id)
    {
        $usuario = Usuarios::findOne(['idusuario' => $id]);
        $cuerpo = $this->cuerpoHuk('Cuenta bloqueada temporalmente', [
            'Hola <strong>' . Html::encode($usuario->nombrecompleto) . '</strong>, hubo <strong>' . AuthService::MAX_INTENTOS
                . ' intentos fallidos</strong> de ingreso con tu cuenta, así que la bloqueamos por <strong>'
                . AuthService::MINUTOS_BLOQUEO . ' minutos</strong> por seguridad.',
            'Si fuiste vos y no recordás tu contraseña, podés crear una nueva con «¿Olvidaste tu contraseña?».',
            'Si <strong>no</strong> fuiste vos, cambiá tu contraseña apenas vuelvas a ingresar.',
        ], ['¿Olvidaste tu contraseña?', Url::to(['/admin/cuenta/forget'], true)]);
        return $this->enviarHuk($usuario->email, 'Cuenta bloqueada temporalmente', $cuerpo);
    }

    /** Un administrador quitó el bloqueo por intentos. */
    public function enviarCorreoDesbloqueoPorIntentos($usuario)
    {
        $cuerpo = $this->cuerpoHuk('Cuenta desbloqueada', [
            'Hola <strong>' . Html::encode($usuario->nombrecompleto) . '</strong>, un administrador desbloqueó tu cuenta, que estaba bloqueada por intentos fallidos. Ya podés ingresar.',
            'Si no reconocés esos intentos, cambiá tu contraseña desde <strong>Mi perfil</strong>.',
        ], ['Ingresar al panel', $this->urlPanel()]);
        return $this->enviarHuk($usuario->email, 'Cuenta desbloqueada', $cuerpo);
    }


    public function enviarCorreodeCreacioncampania($model, $correoCliente)
    {
        $conf = Configuracion::find()->one();
        $nombreCliente = $model->cliente->nombrecompleto ?? 'cliente';
        $nombreGestor = $model->gestor->nombrecompleto ?? 'nuestro equipo';
        $nombreProyecto = $model->titulo;
        $fechaInicio = date('d/m/Y', strtotime($model->fecha_inicio));
        $fechaFin = date('d/m/Y', strtotime($model->fecha_fin));
        $enlacePerfil = Url::to(['cliente/cuenta'], true); // o la ruta que tengas
        $adminEmail = $conf->email ?? 'manueljustinia@gmail.com'; // Asegurate de tener este campo

        $mensaje = '';
        $mensaje .= Html::tag('h2', "Hola $nombreCliente,", ['style' => 'color: #ffffff; margin-bottom: 10px;']);
        $mensaje .= Html::tag('p', "Queremos informarte que la campaña <strong>\"$nombreProyecto\"</strong> ha sido creado con éxito.", ['style' => 'color: #ffffff; font-size: 15px;']);
        $mensaje .= Html::tag('p', "El gestor a cargo será: <strong>$nombreGestor</strong>", ['style' => 'color: #ffffff; font-size: 14px;']);
        $mensaje .= Html::tag('p', "Fecha de inicio: <strong>$fechaInicio</strong><br> Fecha estimada de finalización: <strong>$fechaFin</strong>", ['style' => 'color: #ffffff; font-size: 14px;']);
        $mensaje .= Html::tag('p', "Puede hacer seguimiento y comunicarte con el equipo desde tu perfil:", ['style' => 'color: #ffffff;']);
        $mensaje .= Html::a('Acceder a mi cuenta', $enlacePerfil, ['style' => 'display:inline-block; padding:10px 20px; background-color:#ffffff; color:#000000; text-decoration:none; font-weight:bold; margin-top:10px;']);

        $result = Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoCliente)
            ->setBcc($adminEmail) // Aquí envías copia oculta al administrador
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Campaña creada')
            ->send();

        if ($result) {
            return true;
        } else {
            Yii::error("El correo no pudo ser enviado por razones desconocidas.", __METHOD__);
            return false;
        }
    }



    public function enviarCorreodeAsignacionGestorcampania($model, $correoGestor)
    {
        $conf = Configuracion::find()->one();
        $nombreGestor = $model->gestor->nombrecompleto ?? 'gestor';
        $nombreProyecto = $model->titulo;
        $fechaInicio = date('d/m/Y', strtotime($model->fecha_inicio));
        $fechaFin = date('d/m/Y', strtotime($model->fecha_fin));
        $enlacePerfil = Url::to(['gestor/cuenta'], true); // O la ruta correcta para el gestor

        $mensaje = '';
        $mensaje .= Html::tag('h2', "Hola $nombreGestor,", ['style' => 'color: #ffffff; margin-bottom: 10px;']);
        $mensaje .= Html::tag('p', "Se te ha asignado la campaña <strong>\"$nombreProyecto\"</strong> como gestor responsable.", ['style' => 'color: #ffffff; font-size: 15px;']);
        $mensaje .= Html::tag('p', "Fecha de inicio: <strong>$fechaInicio</strong><br>Fecha estimada de finalización: <strong>$fechaFin</strong>", ['style' => 'color: #ffffff; font-size: 14px;']);
        $mensaje .= Html::tag('p', "Puedes revisar los detalles de la campaña y comenzar a gestionarlo desde tu cuenta:", ['style' => 'color: #ffffff;']);
        $mensaje .= Html::a('Ir a mi panel', $enlacePerfil, ['style' => 'display:inline-block; padding:10px 20px; background-color:#ffffff; color:#000000; text-decoration:none; font-weight:bold; margin-top:10px;']);

        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoGestor)

            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Campaña asignada')
            ->send();
    }




    public function enviarCorreodeCreacionproyecto($model, $correoCliente)
    {
        $conf = Configuracion::find()->one();
        $nombreCliente = $model->cliente->nombrecompleto ?? 'cliente';
        $nombreGestor = $model->gestor->nombrecompleto ?? 'nuestro equipo';
        $nombreProyecto = $model->titulo;
        $fechaInicio = date('d/m/Y', strtotime($model->fecha_inicio));
        $fechaFin = date('d/m/Y', strtotime($model->fecha_fin));
        $enlacePerfil = Url::to(['cliente/cuenta'], true); // o la ruta que tengas
        $adminEmail = $conf->email ?? 'manueljustinia@gmail.com'; // Asegurate de tener este campo

        $mensaje = '';
        $mensaje .= Html::tag('h2', "Hola $nombreCliente,", ['style' => 'color: #ffffff; margin-bottom: 10px;']);
        $mensaje .= Html::tag('p', "Queremos informarte que el proyecto <strong>\"$nombreProyecto\"</strong> ha sido creado con éxito.", ['style' => 'color: #ffffff; font-size: 15px;']);
        $mensaje .= Html::tag('p', "El gestor a cargo será: <strong>$nombreGestor</strong>", ['style' => 'color: #ffffff; font-size: 14px;']);
        $mensaje .= Html::tag('p', "Fecha de inicio: <strong>$fechaInicio</strong><br> Fecha estimada de finalización: <strong>$fechaFin</strong>", ['style' => 'color: #ffffff; font-size: 14px;']);
        $mensaje .= Html::tag('p', "Puede hacer seguimiento y comunicarte con el equipo desde tu perfil:", ['style' => 'color: #ffffff;']);
        $mensaje .= Html::a('Acceder a mi cuenta', $enlacePerfil, ['style' => 'display:inline-block; padding:10px 20px; background-color:#ffffff; color:#000000; text-decoration:none; font-weight:bold; margin-top:10px;']);

        $result = Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoCliente)
            ->setBcc($adminEmail) // Aquí envías copia oculta al administrador
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Proyecto creado')
            ->send();

        if ($result) {
            return true;
        } else {
            Yii::error("El correo no pudo ser enviado por razones desconocidas.", __METHOD__);
            return false;
        }
    }


    public function enviarCorreoAsignacionProyectoGestor($model, $correoGestor)
    {
        $conf = Configuracion::find()->one();
        $nombreGestor = $model->gestor->nombrecompleto ?? 'gestor';
        $nombreProyecto = $model->titulo;
        $fechaInicio = date('d/m/Y', strtotime($model->fecha_inicio));
        $fechaFin = date('d/m/Y', strtotime($model->fecha_fin));
        $enlacePerfil = Url::to(['gestor/cuenta'], true); // O la ruta correcta para el gestor

        $mensaje = '';
        $mensaje .= Html::tag('h2', "Hola $nombreGestor,", ['style' => 'color: #ffffff; margin-bottom: 10px;']);
        $mensaje .= Html::tag('p', "Se te ha asignado el proyecto <strong>\"$nombreProyecto\"</strong> como gestor responsable.", ['style' => 'color: #ffffff; font-size: 15px;']);
        $mensaje .= Html::tag('p', "Fecha de inicio: <strong>$fechaInicio</strong><br>Fecha estimada de finalización: <strong>$fechaFin</strong>", ['style' => 'color: #ffffff; font-size: 14px;']);
        $mensaje .= Html::tag('p', "Puedes revisar los detalles del proyecto y comenzar a gestionarlo desde tu cuenta:", ['style' => 'color: #ffffff;']);
        $mensaje .= Html::a('Ir a mi panel', $enlacePerfil, ['style' => 'display:inline-block; padding:10px 20px; background-color:#ffffff; color:#000000; text-decoration:none; font-weight:bold; margin-top:10px;']);

        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoGestor)

            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Proyecto asignado')
            ->send();
    }




    public function enviarCorreodeEdicionproyecto($model, $correoCliente, $cambios)
    {
        $conf = Configuracion::find()->one();
        $nombreCliente = $model->cliente->nombrecompleto ?? 'Cliente';
        $nombreProyecto = $model->titulo;
        $enlacePerfil = Url::to(['cliente/cuenta'], true);
        $adminEmail = $conf->email ?? 'manueljustinia@gmail.com'; // Asegurate de tener este campo

        $mensaje = Html::tag('h2', "Hola $nombreCliente,", ['style' => 'color:#ffffff']);
        $mensaje .= Html::tag('p', "El proyecto <strong>\"$nombreProyecto\"</strong> ha sido actualizado recientemente.", ['style' => 'color:#ffffff']);
        $mensaje .= Html::tag('p', 'Cambios realizados:', ['style' => 'color:#ffffff']);
        $mensaje .= Html::tag('ul', implode('', array_map(fn($c) => "<li style='color:#ffffff;'>$c</li>", $cambios)));
        $mensaje .= Html::tag('p', "Puedes ver más detalles desde tu cuenta:", ['style' => 'color:#ffffff']);
        $mensaje .= Html::a('Acceder a mi cuenta', $enlacePerfil, [
            'style' => 'display:inline-block; padding:10px 20px; background-color:#ffffff; color:#000000; text-decoration:none; font-weight:bold;'
        ]);


        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoCliente)
            ->setBcc($adminEmail) // Aquí envías copia oculta al administrador
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Actualización del Proyecto')
            ->send();

    }


    public function enviarCorreoActualizacionProyectoGestor($model, $correoGestor, $cambios)
    {
        $conf = Configuracion::find()->one();
        $nombreGestor = $model->gestor->nombrecompleto ?? 'Gestor';
        $nombreProyecto = $model->titulo;
        $enlaceProyecto = Url::to(['proyectos/view', 'id' => $model->idproyecto], true);

        $mensaje = Html::tag('h2', "Hola $nombreGestor,", ['style' => 'color:#ffffff']);
        $mensaje .= Html::tag('p', "Se han realizado cambios en el proyecto <strong>\"$nombreProyecto\"</strong> que tienes asignado.", ['style' => 'color:#ffffff']);
        $mensaje .= Html::tag('p', 'Cambios registrados:', ['style' => 'color:#ffffff']);
        $mensaje .= Html::tag('ul', implode('', array_map(fn($c) => "<li style='color:#ffffff;'>$c</li>", $cambios)));
        $mensaje .= Html::tag('p', "Puedes revisar los detalles desde el sistema:", ['style' => 'color:#ffffff']);
        $mensaje .= Html::a('Ver proyecto', $enlaceProyecto, [
            'style' => 'display:inline-block; padding:10px 20px; background-color:#ffffff; color:#000000; text-decoration:none; font-weight:bold;'
        ]);


        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoGestor)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Cambios en Proyecto Asignado')
            ->send();
    }







    public function enviarCorreodeEdicioncampania($model, $correoCliente, $cambios)
    {
        $conf = Configuracion::find()->one();
        $nombreCliente = $model->cliente->nombrecompleto ?? 'Cliente';
        $nombreProyecto = $model->titulo;
        $enlacePerfil = Url::to(['cliente/cuenta'], true);
        $adminEmail = $conf->email ?? 'manueljustinia@gmail.com'; // Asegurate de tener este campo

        $mensaje = Html::tag('h2', "Hola $nombreCliente,", ['style' => 'color:#ffffff']);
        $mensaje .= Html::tag('p', "La campaña <strong>\"$nombreProyecto\"</strong> ha sido actualizado recientemente.", ['style' => 'color:#ffffff']);
        $mensaje .= Html::tag('p', 'Cambios realizados:', ['style' => 'color:#ffffff']);
        $mensaje .= Html::tag('ul', implode('', array_map(fn($c) => "<li style='color:#ffffff;'>$c</li>", $cambios)));
        $mensaje .= Html::tag('p', "Puedes ver más detalles desde tu cuenta:", ['style' => 'color:#ffffff']);
        $mensaje .= Html::a('Acceder a mi cuenta', $enlacePerfil, [
            'style' => 'display:inline-block; padding:10px 20px; background-color:#ffffff; color:#000000; text-decoration:none; font-weight:bold;'
        ]);


        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoCliente)
            ->setBcc($adminEmail) // Aquí envías copia oculta al administrador
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Actualización de la Campaña')
            ->send();

    }







    public function enviarCorreoActualizacionCampaniaGestor($model, $correoGestor, $cambios)
    {
        $conf = Configuracion::find()->one();
        $nombreGestor = $model->gestor->nombrecompleto ?? 'Gestor';
        $nombreProyecto = $model->titulo;
        $enlaceProyecto = Url::to(['campanias/view', 'id' => $model->idcampania], true);

        $mensaje = Html::tag('h2', "Hola $nombreGestor,", ['style' => 'color:#ffffff']);
        $mensaje .= Html::tag('p', "Se han realizado cambios en la campaña <strong>\"$nombreProyecto\"</strong> que tienes asignado.", ['style' => 'color:#ffffff']);
        $mensaje .= Html::tag('p', 'Cambios registrados:', ['style' => 'color:#ffffff']);
        $mensaje .= Html::tag('ul', implode('', array_map(fn($c) => "<li style='color:#ffffff;'>$c</li>", $cambios)));
        $mensaje .= Html::tag('p', "Puedes revisar los detalles desde el sistema:", ['style' => 'color:#ffffff']);
        $mensaje .= Html::a('Ver proyecto', $enlaceProyecto, [
            'style' => 'display:inline-block; padding:10px 20px; background-color:#ffffff; color:#000000; text-decoration:none; font-weight:bold;'
        ]);


        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoGestor)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Cambios en la Campaña asignada')
            ->send();
    }
















    public function enviarCorreoAvisoAceptaciondetarea($correoGestor, array $datos)
    {
        $conf = Configuracion::find()->one();
        $adminEmail = $conf->email ?? 'manueljustinia@gmail.com';

        // Determinar tipo de origen
        $tipo = isset($datos['tipo']) && $datos['tipo'] === 'campania' ? 'Campaña' : 'Proyecto';

        $mensaje = "
    <h2 style='color: white;'>Notificación de inicio de tarea</h2>
    <p style='color: white;'>Estimado/a,</p>
    <p style='color: white;'>
        El consultor <strong style='color: white;'>{$datos['consultor']}</strong> ha comenzado la ejecución de la siguiente tarea:
    </p>
    <ul>
        <li><strong style='color: white;'>Tarea:</strong> <span style='color: white;'>{$datos['tituloTarea']}</span></li>
        <li><strong style='color: white;'>{$tipo}:</strong> <span style='color: white;'>{$datos['proyecto']}</span></li>
    </ul>
    <p style='color: white;'>Este cambio ha sido registrado correctamente en el sistema.</p>
    <br>
    <p style='font-size: 12px; color: #cccccc;'>Este es un mensaje automático del sistema de gestión de tareas.</p>
";

        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoGestor)
            ->setBcc($adminEmail)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Tarea iniciada por consultor')
            ->send();
    }






    public function enviarCorreoAvisoEliminacionAsignacionGestor($correo, $idtarea, $descripcion)
    {
        $conf = Configuracion::find()->one();
        $tarea = Tareas::findOne($idtarea);
        $adminEmail = $conf->email ?? 'manueljustinia@gmail.com'; // Asegurate de tener este campo

        $mensaje = '
        <p><span style="color:white"><strong>Buenas,</strong></span></p>
        <p><span style="color:white">Se notificó la eliminación de la asignación correspondiente a la tarea: <strong>' . Html::encode($tarea->titulo) . '</strong>.</span></p>
        <p><span style="color:white">' . Html::encode($descripcion) . '</span></p>
        <p><span style="color:white">Esta acción se realizó de forma puntual, debido a que la asignación fue generada por error o no se requerirá en el proceso operativo.</span></p>
        <p><span style="color:white">El consultor fue notificado automáticamente sobre esta modificación.</span></p>
    ';

        $result = Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correo)
            ->setBcc($adminEmail) // Aquí envías copia oculta al administrador
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Aviso de eliminación de asignación')
            ->send();

        if ($result) {
            return true;
        } else {
            Yii::error("No se pudo enviar el correo de eliminación al gestor.", __METHOD__);
            return false;
        }
    }


    public function enviarCorreoAvisoEliminacionAsignacionConsultor($correo, $idtarea, $descripcion)
    {
        $conf = Configuracion::find()->one();
        $tarea = Tareas::findOne($idtarea);

        $mensaje = '
        <p><span style="color:white"><strong>Buenas,</strong></span></p>
        <p><span style="color:white">Le informamos que una asignación correspondiente a la tarea: <strong>' . Html::encode($tarea->titulo) . '</strong> ha sido eliminada.</span></p>
        <p><span style="color:white">' . Html::encode($descripcion) . '</span></p>
        <p><span style="color:white">Esta acción se realizó de forma excepcional, ya que la asignación fue creada por error o se determinó que no será utilizada en el flujo de trabajo.</span></p>
        <p><span style="color:white">Lamentamos las molestias ocasionadas y agradecemos su comprensión.</span></p>
    ';

        $result = Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correo)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Aviso de eliminación de asignación')
            ->send();

        if ($result) {
            return true;
        } else {
            Yii::error("No se pudo enviar el correo de eliminación al consultor.", __METHOD__);
            return false;
        }
    }


    public function enviarCorreodeAsignacionproyecto($model, $correoConsultor)
    {
        $conf = Configuracion::find()->one();
        $proyecto = $model->proyecto ?? null;
        $campania = $model->campania ?? null;
        $tarea = $model->tarea ?? null;

        $mensaje = '<div style="color:white; font-family:Arial, sans-serif;">';
        $mensaje .= '<h1 style="color:white;">¡Estimado consultor!</h1>';
        $mensaje .= '<p>Se le ha asignado una nueva tarea dentro del sistema.</p>';

        if ($proyecto) {
            $mensaje .= '<p><strong>Proyecto:</strong> ' . Html::encode($proyecto->titulo) . '</p>';
        } elseif ($campania) {
            $mensaje .= '<p><strong>Campaña:</strong> ' . Html::encode($campania->titulo) . '</p>';
        } else {
            $mensaje .= '<p><strong>Origen:</strong> No disponible.</p>';
        }

        $mensaje .= '<p><strong>Tarea:</strong> ' . Html::encode($tarea->titulo ?? '-') . '</p>';
        $mensaje .= '<p>Por favor, acceda a su cuenta para revisar los detalles.</p>';
        $mensaje .= '<br><p>Gracias por su trabajo.</p>';
        $mensaje .= '</div>';

        $result = Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoConsultor)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Nueva asignación de tarea')
            ->send();

        if ($result) {
            return true;
        } else {
            Yii::error("El correo no pudo ser enviado por razones desconocidas.", __METHOD__);
            return false;
        }
    }



    public function enviarCorreoAGestorSobreAsignacion($model, $correoGestor)
    {
        $conf = Configuracion::find()->one();

        $proyecto = $model->proyecto ?? null;
        $campania = $model->campania ?? null;
        $tarea = $model->tarea ?? null;
        $consultor = $model->consultor ?? null;

        // Determinar el gestor según el tipo
        $gestor = $proyecto->gestor ?? $campania->gestor ?? null;

        $adminEmail = $conf->email ?? 'manueljustinia@gmail.com';

        if (!$gestor || empty($gestor->email)) return false;

        $mensaje = '<div style="color:white; font-family:Arial, sans-serif;">';
        $mensaje .= '<h1 style="color:white;">Notificación de nueva asignación</h1>';
        $mensaje .= '<p>Se ha asignado una nueva tarea en el sistema.</p>';

        if ($proyecto) {
            $mensaje .= '<p><strong>Proyecto:</strong> ' . Html::encode($proyecto->titulo) . '</p>';
        } elseif ($campania) {
            $mensaje .= '<p><strong>Campaña:</strong> ' . Html::encode($campania->titulo) . '</p>';
        } else {
            $mensaje .= '<p><strong>Origen:</strong> No disponible.</p>';
        }

        $mensaje .= '<p><strong>Tarea:</strong> ' . Html::encode($tarea->titulo ?? '-') . '</p>';
        $mensaje .= '<p><strong>Consultor asignado:</strong> ' . Html::encode($consultor->nombrecompleto ?? '-') . '</p>';
        $mensaje .= '<p>Acceda al sistema para verificar la asignación.</p>';
        $mensaje .= '</div>';

        $result = Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($gestor->email)
            ->setBcc($adminEmail)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Asignación registrada')
            ->send();

        if ($result) {
            return true;
        } else {
            Yii::error("El correo no pudo ser enviado por razones desconocidas.", __METHOD__);
            return false;
        }
    }






    public function enviarCorreodeActualizacionAsignacion($id, $correoConsultor)
    {
        $conf = Configuracion::find()->one();
        $mensaje = Html::tag('h1', '¡Estimado: Consultor');
        $mensaje .= Html::tag('p', 'Comentarle que se le asigno, o modifico una tarea del proyecto');
        $mensaje .= Html::tag('p', 'Entrar al a su perfil ');

        $result = Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoConsultor)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Actualizacion de Asignacion de proyecto  ')
            ->setHtmlBody($mensaje)
            ->send();
        if ($result) {
            return true; // Retorna true si el envío fue exitoso.
        } else {
            Yii::error("El correo no pudo ser enviado por razones desconocidas.", __METHOD__);
            return false;
        }

    }


    public function enviarCorreoModificacionAsignacionGestor($model, $correoGestor, $consultorAnterior = null)
    {
        $conf = Configuracion::find()->one();
        $esProyecto = !empty($model->idproyecto) && $model->proyecto;
        $esCampania = !empty($model->idcampania) && $model->campania;
        $adminEmail = $conf->email ?? 'manueljustinia@gmail.com';
        $tituloEntidad = $esProyecto ? $model->proyecto->titulo : ($esCampania ? $model->campania->titulo : 'Sin título');
        $tipoEntidad = $esProyecto ? 'proyecto' : ($esCampania ? 'campaña' : 'asignación');

        $mensaje = Html::tag('h2', 'Se modificó una asignación de tarea en el ' . Html::encode($tipoEntidad) . ': ' . Html::encode($tituloEntidad), ['style' => 'color: white']);
        $mensaje .= Html::tag('ul',
            Html::tag('li', '<strong>Tarea:</strong> ' . Html::encode($model->tarea->titulo), ['style' => 'color: white']) .
            Html::tag('li', '<strong>Fecha de inicio:</strong> ' . Yii::$app->formatter->asDate($model->fechainicio), ['style' => 'color: white']) .
            Html::tag('li', '<strong>Fecha de fin:</strong> ' . Yii::$app->formatter->asDate($model->fechafin), ['style' => 'color: white']) .
            ($consultorAnterior ? Html::tag('li', '<strong>Consultor anterior:</strong> ' . Html::encode($consultorAnterior->nombrecompleto), ['style' => 'color: white']) : '') .
            Html::tag('li', '<strong>Consultor actual:</strong> ' . Html::encode($model->consultor->nombrecompleto), ['style' => 'color: white']) .
            Html::tag('li', '<strong>Motivo:</strong> ' . Html::encode($model->comentario), ['style' => 'color: white'])
        );

        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoGestor)
            ->setBcc($adminEmail)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Modificación de asignación de tarea')
            ->send();
    }





    public function enviarCorreoModificacionAsignacionCliente($model, $correoCliente)
    {
        $conf = Configuracion::find()->one();
        $tarea = $model->tarea;
        $consultor = $model->consultor;

        $esProyecto = !empty($model->idproyecto) && $model->proyecto;
        $esCampania = !empty($model->idcampania) && $model->campania;

        $tituloEntidad = $esProyecto ? $model->proyecto->titulo : ($esCampania ? $model->campania->titulo : 'Sin título');
        $tipoEntidad = $esProyecto ? 'proyecto' : ($esCampania ? 'campaña' : 'asignación');
        $asunto = $conf->titulo_pagina . ' - Actualización en su ' . $tipoEntidad;

        $mensaje = Html::tag('h2', 'Actualización en la asignación de tareas de su ' . $tipoEntidad, ['style' => 'color: white']);
        $mensaje .= Html::tag('p', 'Estimado cliente, se ha actualizado una asignación dentro de su ' . $tipoEntidad . ': <strong style="color:white">' . Html::encode($tituloEntidad) . '</strong>.', ['style' => 'color: white']);
        $mensaje .= Html::tag('p', 'Detalles de la asignación actualizada:', ['style' => 'color: white']);
        $mensaje .= Html::tag('ul',
            Html::tag('li', '<strong>Tarea:</strong> ' . Html::encode($tarea->titulo), ['style' => 'color: white']) .
            Html::tag('li', '<strong>Consultor asignado:</strong> ' . Html::encode($consultor->nombrecompleto), ['style' => 'color: white']) .
            Html::tag('li', '<strong>Fechas:</strong> del ' . Yii::$app->formatter->asDate($model->fechainicio) . ' hasta el ' . Yii::$app->formatter->asDate($model->fechafin), ['style' => 'color: white']) .
            Html::tag('li', '<strong>Motivo:</strong> ' . Html::encode($model->comentario), ['style' => 'color: white'])
        );
        $mensaje .= Html::tag('p', 'Si tiene alguna consulta o desea hacer modificaciones, por favor contáctese con su gestor asignado.', ['style' => 'color: white']);
        $mensaje .= Html::tag('p', 'Gracias por confiar en nosotros.', ['style' => 'color: white']);

        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoCliente)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($asunto)
            ->send();
    }



    public function enviarCorreoModificacionAsignacion($model, $correoConsultor)
    {
        $conf = Configuracion::find()->one();
        $tarea = $model->tarea;
        $esProyecto = !empty($model->idproyecto) && $model->proyecto;
        $esCampania = !empty($model->idcampania) && $model->campania;

        $tituloEntidad = $esProyecto ? $model->proyecto->titulo : ($esCampania ? $model->campania->titulo : 'Sin título');
        $tipoEntidad = $esProyecto ? 'proyecto' : ($esCampania ? 'campaña' : 'asignación');

        $mensaje = Html::tag('h2', 'Actualización en su tarea asignada', ['style' => 'color: white']);
        $mensaje .= Html::tag('p', 'Se han realizado modificaciones en una tarea que tiene asignada dentro de su <strong style="color:white">' . $tipoEntidad . '</strong>: <strong style="color:white">' . Html::encode($tituloEntidad) . '</strong>.', ['style' => 'color: white']);
        $mensaje .= Html::tag('ul',
            Html::tag('li', '<strong>Tarea:</strong> ' . Html::encode($tarea->titulo), ['style' => 'color: white']) .
            Html::tag('li', '<strong>Fecha de inicio:</strong> ' . Yii::$app->formatter->asDate($model->fechainicio), ['style' => 'color: white']) .
            Html::tag('li', '<strong>Fecha de fin:</strong> ' . Yii::$app->formatter->asDate($model->fechafin), ['style' => 'color: white']) .
            Html::tag('li', '<strong>Motivo:</strong> ' . Html::encode($model->comentario), ['style' => 'color: white'])
        );
        $mensaje .= Html::tag('p', 'Por favor revise los detalles y comuníquese con el gestor si es necesario.', ['style' => 'color: white']);

        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoConsultor)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Modificación de tarea asignada')
            ->send();
    }



    public function enviarCorreoEdicionRecurso($model, $correoCliente)
    {
        $conf = Configuracion::find()->one();
        $asignacion = $model->asignacion;
        $tarea = $asignacion->tareas;
        $creador = $model->usuario;

        $esProyecto = $asignacion->idproyecto !== null;
        $tituloEntidad = $esProyecto
            ? Html::encode($asignacion->proyecto->titulo)
            : Html::encode($asignacion->campania->titulo);
        $tipoEntidad = $esProyecto ? 'proyecto' : 'campaña';

        $mensaje = Html::tag('h2', 'Recurso actualizado en su ' . $tipoEntidad, ['style' => 'color:white']);

        $mensaje .= Html::tag('p',
            'El recurso correspondiente a la tarea <strong style="color:white">' . Html::encode($tarea->titulo) . '</strong>, dentro del ' . $tipoEntidad .
            ' <strong style="color:white">' . $tituloEntidad . '</strong>, ha sido modificado por el consultor.',
            ['style' => 'color:white']
        );

        $mensaje .= Html::tag('p', 'A continuación se detallan los datos actuales del recurso:', ['style' => 'color:white']);

        $mensaje .= Html::tag('ul',
            Html::tag('li', '<strong style="color:white">Título actualizado:</strong> ' . Html::encode($model->titulo), ['style' => 'color:white']) .
            Html::tag('li', '<strong style="color:white">Fecha de entrega:</strong> ' . Yii::$app->formatter->asDate($model->fechaentrega), ['style' => 'color:white']) .
            Html::tag('li', '<strong style="color:white">Editado por:</strong> ' . Html::encode($creador->nombrecompleto), ['style' => 'color:white'])
        );

        $mensaje .= Html::tag('p',
            'La edición fue permitida ya que el recurso aún no contaba con materiales asociados.',
            ['style' => 'color:white']
        );

        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoCliente)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Recurso actualizado en su ' . $tipoEntidad)
            ->send();
    }



    public function enviarCorreoEliminacionRecurso($model, $correoCliente)
    {
        $conf = Configuracion::find()->one();
        $asignacion = $model->asignacion;
        $tarea = $asignacion->tareas;
        $creador = $model->usuario;

        // Detectar si pertenece a proyecto o campaña
        $esProyecto = $asignacion->idproyecto !== null;
        $tituloEntidad = $esProyecto
            ? Html::encode($asignacion->proyecto->titulo)
            : Html::encode($asignacion->campania->titulo);
        $tipoEntidad = $esProyecto ? 'proyecto' : 'campaña';

        $mensaje = Html::tag('h2', 'Recurso eliminado de su ' . $tipoEntidad, ['style' => 'color: white;']);

        $mensaje .= Html::tag('p',
            'Se ha eliminado un recurso que pertenecía a la tarea <strong style="color: white;">' . Html::encode($tarea->titulo) . '</strong>, dentro de su ' . $tipoEntidad . ' <strong style="color: white;">' . $tituloEntidad . '</strong>.',
            ['style' => 'color: white;']
        );

        $mensaje .= Html::tag('ul',
            Html::tag('li', '<strong style="color: white;">Título del recurso:</strong> ' . Html::encode($model->titulo)),
            ['style' => 'color: white;']
        );

        $mensaje .= Html::tag('p',
            'La eliminación se realizó porque el recurso aún no contaba con materiales adjuntos o no era necesario continuar con su gestión.',
            ['style' => 'color: white;']
        );

        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoCliente)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Recurso eliminado del ' . $tipoEntidad)
            ->send();
    }




    public function enviarCorreoEliminarRecursoGestor($model, $correoGestor)
    {
        $conf = Configuracion::find()->one();
        $asignacion = $model->asignacion;
        $tarea = $asignacion->tareas;
        $creador = $model->usuario;
        $adminEmail = $conf->email ?? 'manueljustinia@gmail.com';

        // Detectar si pertenece a proyecto o campaña
        $esProyecto = $asignacion->idproyecto !== null;
        $tituloEntidad = $esProyecto
            ? Html::encode($asignacion->proyecto->titulo)
            : Html::encode($asignacion->campania->titulo);
        $tipoEntidad = $esProyecto ? 'proyecto' : 'campaña';

        $mensaje = Html::tag('h2', 'Recurso eliminado de una tarea asignada', ['style' => 'color: white;']);

        $mensaje .= Html::tag('p',
            'El recurso correspondiente a la tarea <strong style="color: white;">' . Html::encode($tarea->titulo) . '</strong>, de su ' . $tipoEntidad . ' <strong style="color: white;">' . $tituloEntidad . '</strong>, ha sido eliminado por el consultor <strong style="color: white;">' . Html::encode($creador->nombrecompleto) . '</strong>.',
            ['style' => 'color: white;']
        );

        $mensaje .= Html::tag('ul',
            Html::tag('li', '<strong style="color: white;">Título del recurso eliminado:</strong> ' . Html::encode($model->titulo)),
            ['style' => 'color: white;']
        );

        $mensaje .= Html::tag('p',
            'La eliminación se realizó porque el recurso no contaba con materiales adjuntos o ya no era necesario para la gestión de la tarea. Puede realizar seguimiento desde el sistema si lo considera necesario.',
            ['style' => 'color: white;']
        );

        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoGestor)
            ->setBcc($adminEmail)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Recurso eliminado por el consultor')
            ->send();
    }




    public function enviarCorreoNuevoRecurso($model, $correoCliente)
    {
        $conf = Configuracion::find()->one();
        $proyecto = $model->asignacion->proyecto;
        $tarea = $model->asignacion->tareas;
        $creador = $model->usuario;
        $asignacion = $model->asignacion;
        $esProyecto = $asignacion->idproyecto !== null;
        $tituloEntidad = $esProyecto
            ? Html::encode($asignacion->proyecto->titulo)
            : Html::encode($asignacion->campania->titulo);
        $tipoEntidad = $esProyecto ? 'proyecto' : 'campaña';


        $mensaje = Html::tag('h2', 'Nuevo recurso creado para su revisión', ['style' => 'color:white']);

        $mensaje .= Html::tag('p',
            'Se ha creado un nuevo recurso dentro de la tarea: <strong style="color:white">' . Html::encode($tarea->titulo) .
            '</strong>, correspondiente al ' . $tipoEntidad . ' <strong style="color:white">' . $tituloEntidad . '</strong>.',
            ['style' => 'color:white']
        );

        $mensaje .= Html::tag('ul',
            Html::tag('li', '<strong style="color:white">Título del recurso:</strong> ' . Html::encode($model->titulo), ['style' => 'color:white']) .
            Html::tag('li', '<strong style="color:white">Fecha de entrega:</strong> ' . Yii::$app->formatter->asDate($model->fechaentrega), ['style' => 'color:white']) .
            Html::tag('li', '<strong style="color:white">Creado por:</strong> ' . Html::encode($creador->nombrecompleto), ['style' => 'color:white'])
        );


        $mensaje .= Html::tag('p',
            'Se ha creado un nuevo recurso en la tarea: <strong style="color:white">' . Html::encode($tarea->titulo) .
            '</strong>, del ' . $tipoEntidad . ' <strong style="color:white">' . $tituloEntidad . '</strong>.',
            ['style' => 'color:white']
        );


        $mensaje .= Html::tag('p',
            'Por favor, revise este recurso, y si está conforme, puede aprobarlo o dejar comentarios para su mejora.',
            ['style' => 'color:white']
        );

        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoCliente)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Nuevo recurso disponible')
            ->send();
    }



    public function enviarCorreoNuevoRecursoGestor($model, $correoGestor)
    {
        $conf = Configuracion::find()->one();
        $asignacion = $model->asignacion;
        $tarea = $asignacion->tareas;
        $creador = $model->usuario;
        $adminEmail = $conf->email ?? 'manueljustinia@gmail.com';

        // Detectar si es proyecto o campaña
        $esProyecto = $asignacion->idproyecto !== null;
        $tituloEntidad = $esProyecto
            ? Html::encode($asignacion->proyecto->titulo)
            : Html::encode($asignacion->campania->titulo);
        $tipoEntidad = $esProyecto ? 'proyecto' : 'campaña';

        $mensaje = Html::tag('h2', 'Nuevo recurso registrado en una tarea asignada', ['style' => 'color:white']);

        $mensaje .= Html::tag('p',
            'Se ha creado un nuevo recurso en la tarea: <strong style="color:white">' . Html::encode($tarea->titulo) .
            '</strong>, del ' . $tipoEntidad . ' <strong style="color:white">' . $tituloEntidad . '</strong>.',
            ['style' => 'color:white']
        );

        $mensaje .= Html::tag('ul',
            Html::tag('li', '<strong style="color:white">Título del recurso:</strong> ' . Html::encode($model->titulo), ['style' => 'color:white']) .
            Html::tag('li', '<strong style="color:white">Fecha de entrega:</strong> ' . Yii::$app->formatter->asDate($model->fechaentrega), ['style' => 'color:white']) .
            Html::tag('li', '<strong style="color:white">Creado por:</strong> ' . Html::encode($creador->nombrecompleto), ['style' => 'color:white']),
            ['style' => 'color:white']
        );

        $mensaje .= Html::tag('p',
            'Este recurso está disponible para revisión del cliente. Usted puede hacer seguimiento desde el sistema.',
            ['style' => 'color:white']
        );

        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoGestor)
            ->setBcc($adminEmail)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Nuevo recurso creado en una tarea')
            ->send();
    }



    public function enviarCorreoEditarRecursoGestor($model, $correoGestor)
    {
        $conf = Configuracion::find()->one();
        $asignacion = $model->asignacion;
        $tarea = $asignacion->tareas;
        $creador = $model->usuario;
        $adminEmail = $conf->email ?? 'manueljustinia@gmail.com';

        // Determinar si es proyecto o campaña
        $esProyecto = $asignacion->idproyecto !== null;
        $tituloEntidad = $esProyecto
            ? Html::encode($asignacion->proyecto->titulo)
            : Html::encode($asignacion->campania->titulo);

        $tipoEntidad = $esProyecto ? 'proyecto' : 'campaña';

        $mensaje = Html::tag('h2', 'Recurso actualizado en la tarea asignada', ['style' => 'color: white;']);

        $mensaje .= Html::tag('p',
            'El recurso correspondiente a la tarea <strong style="color: white;">' . Html::encode($tarea->titulo) . '</strong>, del ' . $tipoEntidad . ' <strong style="color: white;">' . $tituloEntidad . '</strong>, ha sido actualizado por el consultor <strong style="color: white;">' . Html::encode($creador->nombrecompleto) . '</strong>.'
        );

        $mensaje .= Html::tag('p', 'A continuación, se detallan los datos actuales del recurso:', ['style' => 'color: white;']);

        $mensaje .= Html::tag('ul',
            Html::tag('li', '<strong style="color: white;">Título actualizado:</strong> ' . Html::encode($model->titulo), ['style' => 'color:white']) .
            Html::tag('li', '<strong style="color: white;">Descripción actual:</strong> ' . Html::encode($model->descripcion), ['style' => 'color:white']) .
            Html::tag('li', '<strong style="color: white;">Fecha de entrega:</strong> ' . Yii::$app->formatter->asDate($model->fechaentrega), ['style' => 'color:white']),
            ['style' => 'color:white']
        );

        $mensaje .= Html::tag('p', 'Puede ingresar al sistema para revisar los cambios y dar seguimiento al recurso.', ['style' => 'color: white;']);

        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoGestor)
            ->setBcc($adminEmail)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Edición de recurso actualizado')
            ->send();
    }






    public function enviarCorreoDesasignacionConsultor($model, $correoAnterior)
    {
        $conf = Configuracion::find()->one();
        $tarea = $model->tarea;
        $esProyecto = !empty($model->idproyecto) && $model->proyecto;
        $esCampania = !empty($model->idcampania) && $model->campania;

        $tituloEntidad = $esProyecto ? $model->proyecto->titulo : ($esCampania ? $model->campania->titulo : 'Sin título');
        $tipoEntidad = $esProyecto ? 'proyecto' : ($esCampania ? 'campaña' : 'asignación');

        $mensaje = Html::tag('h2', 'Notificación de modificación en asignación', ['style' => 'color: white']);
        $mensaje .= Html::tag('p', 'Le informamos que ya no está asignado a la siguiente tarea de la <strong style="color:white">' . $tipoEntidad . '</strong>: <strong style="color:white">' . Html::encode($tituloEntidad) . '</strong>.', ['style' => 'color: white']);
        $mensaje .= Html::tag('ul',
            Html::tag('li', '<strong>Tarea:</strong> ' . Html::encode($tarea->titulo), ['style' => 'color: white']) .
            Html::tag('li', '<strong>Motivo:</strong> ' . Html::encode($model->comentario), ['style' => 'color: white'])
        );
        $mensaje .= Html::tag('p', 'Gracias por su trabajo hasta el momento. Si tiene dudas, por favor contacte con el gestor asignado.', ['style' => 'color: white']);

        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoAnterior)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Removido de tarea asignada')
            ->send();
    }



    public function enviarCorreoCambioConsultorAsignacion($model, $correoNuevoConsultor)
    {
        $conf = Configuracion::find()->one();
        $tarea = $model->tarea;
        $esProyecto = !empty($model->idproyecto) && $model->proyecto;
        $esCampania = !empty($model->idcampania) && $model->campania;

        $tituloEntidad = $esProyecto ? $model->proyecto->titulo : ($esCampania ? $model->campania->titulo : 'Sin título');
        $tipoEntidad = $esProyecto ? 'proyecto' : ($esCampania ? 'campaña' : 'asignación');

        $mensaje = Html::tag('h2', 'Notificación de modificación en asignación', ['style' => 'color: white']);
        $mensaje .= Html::tag('p', 'Le informamos que fue asignado a una tarea de la <strong style="color:white">' . $tipoEntidad . '</strong>: <strong style="color:white">' . Html::encode($tituloEntidad) . '</strong>.', ['style' => 'color: white']);
        $mensaje .= Html::tag('ul',
            Html::tag('li', '<strong>Tarea:</strong> ' . Html::encode($tarea->titulo), ['style' => 'color: white']) .
            Html::tag('li', '<strong>Motivo:</strong> ' . Html::encode($model->comentario), ['style' => 'color: white'])
        );
        $mensaje .= Html::tag('p', 'Por favor revise los detalles. Si tiene dudas, contacte con el gestor asignado.', ['style' => 'color: white']);

        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correoNuevoConsultor)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Nueva tarea asignada')
            ->send();
    }


    public function enviarCorreoRechazoRecurso($recurso, $comentario, $destinatarios)
    {
        $conf = Configuracion::find()->one();
        $asignacion = $recurso->asignacion;
        $proyecto = $asignacion->proyecto ?? null;
        $campania = $asignacion->campania ?? null;
        $tarea = $asignacion->tarea;
        $cliente = Yii::$app->user->identity;
        $adminEmail = $conf->email ?? 'manueljustinia@gmail.com';

        $mensaje = Html::tag('h2', 'Recurso rechazado por el cliente', ['style' => 'color:white;']);

        $mensaje .= Html::tag('p',
            'El cliente <strong style="color:white;">' . Html::encode($cliente->nombrecompleto) . '</strong> ha rechazado un recurso de la tarea:',
            ['style' => 'color:white;']
        );

        $lista = '';
        if ($proyecto) {
            $lista .= Html::tag('li', '<strong style="color:white;">Proyecto:</strong> ' . Html::encode($proyecto->titulo), ['style' => 'color:white;']);
        } elseif ($campania) {
            $lista .= Html::tag('li', '<strong style="color:white;">Campaña:</strong> ' . Html::encode($campania->titulo), ['style' => 'color:white;']);
        }

        $lista .= Html::tag('li', '<strong style="color:white;">Tarea:</strong> ' . Html::encode($tarea->titulo), ['style' => 'color:white;']);
        $lista .= Html::tag('li', '<strong style="color:white;">Recurso:</strong> ' . Html::encode($recurso->titulo), ['style' => 'color:white;']);
        $lista .= Html::tag('li', '<strong style="color:white;">Motivo:</strong> ' . Html::encode($comentario), ['style' => 'color:white;']);

        $mensaje .= Html::tag('ul', $lista);
        $mensaje .= Html::tag('p', 'Por favor, revise el historial del recurso y tome las acciones necesarias.', ['style' => 'color:white;']);

        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($destinatarios)
            ->setBcc($adminEmail)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject("Recurso rechazado: {$recurso->titulo}")
            ->send();
    }




    public function enviarCorreoAprobacionRecurso($recurso, $comentario, $destinatarios)
    {
        $conf = Configuracion::find()->one();
        $asignacion = $recurso->asignacion;
        $proyecto = $asignacion->proyecto ?? null;
        $campania = $asignacion->campania ?? null;
        $tarea = $asignacion->tarea;
        $cliente = Yii::$app->user->identity;
        $adminEmail = $conf->email ?? 'manueljustinia@gmail.com';
        $mensaje = Html::tag('h2', 'Recurso aprobado por el cliente', ['style' => 'color:white;']);

        $mensaje .= Html::tag('p',
            'El cliente <strong style="color:white;">' . Html::encode($cliente->nombrecompleto) . '</strong> ha aprobado un recurso de la tarea:',
            ['style' => 'color:white;']
        );

        $lista = '';
        if ($proyecto) {
            $lista .= Html::tag('li', '<strong style="color:white;">Proyecto:</strong> ' . Html::encode($proyecto->titulo), ['style' => 'color:white;']);
        } elseif ($campania) {
            $lista .= Html::tag('li', '<strong style="color:white;">Campaña:</strong> ' . Html::encode($campania->titulo), ['style' => 'color:white;']);
        }

        $lista .= Html::tag('li', '<strong style="color:white;">Tarea:</strong> ' . Html::encode($tarea->titulo), ['style' => 'color:white;']);
        $lista .= Html::tag('li', '<strong style="color:white;">Recurso:</strong> ' . Html::encode($recurso->titulo), ['style' => 'color:white;']);

        $mensaje .= Html::tag('ul', $lista);

        if ($comentario) {
            $mensaje .= Html::tag('p', '<strong style="color:white;">Comentario adicional:</strong> ' . Html::encode($comentario), ['style' => 'color:white;']);
        }

        $mensaje .= Html::tag('p', 'Puede revisar el historial del recurso para más información.', ['style' => 'color:white;']);

        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($destinatarios)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setBcc($adminEmail)
            ->setSubject("Recurso aprobado: {$recurso->titulo}")
            ->send();
    }


    public function enviarCorreoFinalizacionTarea($asignacion, $correo)
    {
        $conf = Configuracion::find()->one();
        $proyecto = $asignacion->proyecto;
        $campania = $asignacion->campania;
        $tarea = $asignacion->tarea;
        $consultor = $asignacion->consultor;
        $adminEmail = $conf->email ?? 'manueljustinia@gmail.com';

        $tituloGeneral = $proyecto ? 'Proyecto' : 'Campaña';
        $nombreGeneral = $proyecto ? $proyecto->titulo : $campania->titulo;
        $cliente = $proyecto ? $proyecto->cliente : $campania->cliente;

        $mensaje = Html::tag('h2', '<span style="color:white">Tarea finalizada automáticamente</span>');

        $mensaje .= Html::tag('p', '<span style="color:white">La siguiente tarea fue marcada como finalizada porque todos los recursos asignados fueron aprobados por el cliente:</span>');

        $mensaje .= Html::tag('ul',
            Html::tag('li', '<span style="color:white"><strong>' . $tituloGeneral . ':</strong> ' . Html::encode($nombreGeneral) . '</span>') .
            Html::tag('li', '<span style="color:white"><strong>Tarea:</strong> ' . Html::encode($tarea->titulo) . '</span>') .
            Html::tag('li', '<span style="color:white"><strong>Consultor asignado:</strong> ' . Html::encode($consultor->nombrecompleto) . '</span>') .
            Html::tag('li', '<span style="color:white"><strong>Cliente:</strong> ' . Html::encode($cliente->nombrecompleto) . '</span>') .
            Html::tag('li', '<span style="color:white"><strong>Motivo:</strong> ' . Html::encode($asignacion->comentario) . '</span>') .
            Html::tag('li', '<span style="color:white"><strong>Fecha de finalización:</strong> ' . Yii::$app->formatter->asDate($asignacion->fechaestadofinal, 'php:d/m/Y') . '</span>')
        );

        $mensaje .= Html::tag('p', '<span style="color:white">Puede revisar los recursos y el historial de la tarea en el sistema para más detalles.</span>');

        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correo)
            ->setBcc($adminEmail)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Tarea finalizada')
            ->send();
    }


    public function enviarCorreoSolicitudFin($asignacion, $correo)
    {
        $conf = Configuracion::find()->one();
        $campania = $asignacion->campania;
        $tarea = $asignacion->tarea;
        $cliente = $campania->cliente ?? null;
        $consultor = $asignacion->consultor ?? null;
        $adminEmail = $conf->email ?? 'manueljustinia@gmail.com';

        $mensaje = Html::tag('h2', '<span style="color:white">Solicitud de finalización de campaña</span>');

        $mensaje .= Html::tag('p', '<span style="color:white">El cliente ha solicitado finalizar la campaña:</span>');

        $mensaje .= Html::tag('ul',
            Html::tag('li', '<span style="color:white"><strong>Campaña:</strong> ' . Html::encode($campania->titulo) . '</span>') .
            Html::tag('li', '<span style="color:white"><strong>Tarea:</strong> ' . Html::encode($tarea->titulo) . '</span>') .
            Html::tag('li', '<span style="color:white"><strong>Consultor asignado:</strong> ' . Html::encode($consultor->nombrecompleto) . '</span>') .
            Html::tag('li', '<span style="color:white"><strong>Cliente:</strong> ' . Html::encode($cliente->nombrecompleto) . '</span>') .
            Html::tag('li', '<span style="color:white"><strong>Motivo de la solicitud:</strong> ' . Html::encode($asignacion->comentario) . '</span>') .
            Html::tag('li', '<span style="color:white"><strong>Fecha de solicitud:</strong> ' . Yii::$app->formatter->asDate($asignacion->fechaestadofinal, 'php:d/m/Y') . '</span>')
        );

        $mensaje .= Html::tag('p', '<span style="color:white">Se recomienda revisar la campaña antes de finalizarla oficialmente.</span>');

        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correo)
            ->setBcc($adminEmail)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Solicitud de finalización de campaña')
            ->send();
    }




    public function enviarCorreoReversionTarea($asignacion, $destinatarios)
    {
        $conf = Configuracion::find()->one();
        $tarea = $asignacion->tarea;
        $consultor = $asignacion->consultor;
        $adminEmail = $conf->email ?? 'manueljustinia@gmail.com';

        // Determinar si es proyecto o campaña
        $tituloEntidad = '';
        if ($asignacion->proyecto) {
            $tituloEntidad = 'Proyecto: ' . $asignacion->proyecto->titulo;
        } elseif ($asignacion->campania) {
            $tituloEntidad = 'Campaña: ' . $asignacion->campania->titulo;
        }

        $mensaje = Html::tag('h2', 'Tarea devuelta a estado anterior', ['style' => 'color:white']);
        $mensaje .= Html::tag('p',
            'Le informamos que la tarea fue revertida al estado "En progreso" por el consultor ' .
            '<strong style="color:white">' . Html::encode($consultor->nombrecompleto) . '</strong>.',
            ['style' => 'color:white']
        );

        $mensaje .= Html::tag('ul',
            Html::tag('li', '<strong style="color:white">Tarea:</strong> ' . Html::encode($tarea->titulo), ['style' => 'color:white']) .
            Html::tag('li', '<strong style="color:white">' . Html::encode(explode(':', $tituloEntidad)[0]) . ':</strong> ' . Html::encode(explode(':', $tituloEntidad)[1]), ['style' => 'color:white']) .
            Html::tag('li', '<strong style="color:white">Motivo:</strong> ' . Html::encode($asignacion->comentario), ['style' => 'color:white']) .
            Html::tag('li', '<strong style="color:white">ID Asignación:</strong> ' . Html::encode($asignacion->idasignartarea), ['style' => 'color:white']) .
            Html::tag('li', '<strong style="color:white">Fecha de reversión:</strong> ' . date('d/m/Y H:i'), ['style' => 'color:white'])
        );

        $mensaje .= Html::tag('p', 'Por favor, revise la tarea y coordine los próximos pasos si es necesario.', ['style' => 'color:white']);

        return Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($destinatarios)
            ->setBcc($adminEmail)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - Tarea devuelta a estado anterior')
            ->send();
    }


    public function enviarCorreoFinalizacionProyecto($proyecto, $correo)
    {
        $conf = Configuracion::find()->one();
        $adminEmail = $conf->email ?? 'manueljustinia@gmail.com';

        $mensaje = Html::tag('h2', 'Proyecto finalizado', [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);

        $mensaje .= Html::tag('p', 'El proyecto <strong>' . $proyecto->titulo . '</strong> ha sido finalizado.', [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);

        $mensaje .= Html::tag('p', '<strong>Comentario:</strong> ' . $proyecto->motivodefinal, [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);

        $mensaje .= Html::tag('p', '<strong>Fecha:</strong> ' . $proyecto->fechafinalizacion, [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);

        // Mensaje de cierre
        $mensaje .= Html::tag('p', 'Gracias por confiar en Zebra Brand Consulting. Estamos a tu disposición para futuras colaboraciones.', [
            'style' => 'color: white; font-family: Arial, sans-serif; font-style: italic; margin-top: 20px;'
        ]);

        Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correo)
            ->setBcc($adminEmail)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject("Proyecto finalizado: " . $proyecto->titulo)
            ->send();
    }




    public function enviarCorreoFinalizacionCampania($campania, $correo)
    {
        $conf = Configuracion::find()->one();
        $adminEmail = $conf->email ?? 'manueljustinia@gmail.com';

        $mensaje = Html::tag('h2', 'Campaña finalizada', [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);

        $mensaje .= Html::tag('p', 'La campala <strong>' . $campania->titulo . '</strong> ha sido finalizada.', [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);

        $mensaje .= Html::tag('p', '<strong>Comentario:</strong> ' . $campania->motivodefinal, [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);

        $mensaje .= Html::tag('p', '<strong>Fecha:</strong> ' . $campania->fechafinalizacion, [
            'style' => 'color: white; font-family: Arial, sans-serif;'
        ]);

        // Mensaje de cierre
        $mensaje .= Html::tag('p', 'Gracias por confiar en Zebra Brand Consulting. Estamos a tu disposición para futuras colaboraciones.', [
            'style' => 'color: white; font-family: Arial, sans-serif; font-style: italic; margin-top: 20px;'
        ]);

        Yii::$app->mailer->compose('layouts/template2', [
            'content' => $mensaje,
            'config' => $conf,
        ])
            ->setTo($correo)
            ->setBcc($adminEmail)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject("Campaña finalizado: " . $campania->titulo)
            ->send();
    }


    public function enviarCorreosNotificacion($remitente, $recurso, $hayComentario, $hayArchivo)
    {
        $usuariosAEnviar = [];

        $asignacion = $recurso->asignacion;
        if (!$asignacion) return;

        $consultor = $asignacion->consultor;
        $proyecto = $asignacion->proyecto ?? null;
        $campania = $asignacion->campania ?? null;
        $gestor = $proyecto?->gestor ?? $campania?->gestor ?? null;
        $cliente = $proyecto?->cliente ?? $campania?->cliente ?? null;

        if ($remitente->esGestor()) {
            if ($cliente && $cliente->idusuario !== $remitente->idusuario) $usuariosAEnviar[] = $cliente;
            if ($consultor && $consultor->idusuario !== $remitente->idusuario) $usuariosAEnviar[] = $consultor;
        } elseif ($remitente->esCliente()) {
            if ($gestor && $gestor->idusuario !== $remitente->idusuario) $usuariosAEnviar[] = $gestor;
            if ($consultor && $consultor->idusuario !== $remitente->idusuario) $usuariosAEnviar[] = $consultor;
        } elseif ($remitente->esConsultor()) {
            if ($gestor && $gestor->idusuario !== $remitente->idusuario) $usuariosAEnviar[] = $gestor;
            if ($cliente && $cliente->idusuario !== $remitente->idusuario) $usuariosAEnviar[] = $cliente;
        }

        // -------------------------------------
        // Preparar contenido
        $asunto = 'Nuevo mensaje en el recurso "' . $recurso->titulo . '"';
        $contenido = "<div style='color: white;'>";

        $comentarioReciente = \app\models\HistoriaRecursos::find()
            ->where(['idrecurso' => $recurso->idrecurso, 'idusuario' => $remitente->idusuario])
            ->andWhere(['not', ['comentario' => null]])
            ->orderBy(['idhistorial' => SORT_DESC])
            ->limit(1)
            ->one();

        // Cuerpo dinámico
        $contenido .= "<p><strong>{$remitente->nombrecompleto}</strong> ha enviado ";

        if ($hayComentario && $comentarioReciente) {
            $comentario = strip_tags($comentarioReciente->comentario);
            $resumen = mb_strimwidth($comentario, 0, 100, '...');
            $contenido .= "un comentario: <em>“{$resumen}”</em>";
            if ($hayArchivo) {
                $contenido .= " y también ha adjuntado un archivo.";
            } else {
                $contenido .= ".";
            }
        } elseif ($hayArchivo) {
            $contenido .= "un archivo relacionado al recurso.";
        }

        // Datos adicionales
        $contenido .= "</p><p style='color: white;'><strong>Tarea:</strong> {$asignacion->tarea->titulo}</p>";
        if ($proyecto) {
            $contenido .= "<p style='color: white;'><strong>Proyecto:</strong> {$proyecto->titulo}</p>";
        } elseif ($campania) {
            $contenido .= "<p style='color: white;'><strong>Campaña:</strong> {$campania->titulo}</p>";
        }

        $contenido .= "<p style='color: white;'>Ingrese al sistema para revisar el historial completo del recurso y continuar con la colaboración.</p>";
        $contenido .= "</div>";

        $yaSeEnvioAlAdmin = false;

        foreach ($usuariosAEnviar as $destinatario) {
            $this->enviarCorreo(
                $destinatario->email,
                $asunto,
                $contenido,
                !$yaSeEnvioAlAdmin // true solo la primera vez
            );
            $yaSeEnvioAlAdmin = true;
        }

    }






    public function enviarCorreo($destinatarioEmail, $asunto, $cuerpoHtml, $incluirAdmin = false)
    {
        $conf = Configuracion::find()->one();
        $adminEmail = $conf->email ?? 'manueljustinia@gmail.com';

        $correo = Yii::$app->mailer->compose('layouts/template2', [
            'content' => $cuerpoHtml,
            'config' => $conf,
        ])
            ->setTo($destinatarioEmail)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($asunto);

        if ($incluirAdmin) {
            $correo->setBcc($adminEmail);
        }

        return $correo->send();
    }






}
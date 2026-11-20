<?php

namespace app\components;

use app\models\Configuracion;
use app\models\Usuarios;
use Yii;
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

    private function enviarHuk(string $email, string $asunto, string $cuerpo, ?array $responderA = null): bool
    {
        $conf = Configuracion::find()->one();
        $mensaje = Yii::$app->mailer->compose('layouts/template2', ['content' => $cuerpo, 'config' => $conf])
            ->setTo($email)
            ->setFrom([$conf->email => $conf->titulo_pagina])
            ->setSubject($conf->titulo_pagina . ' - ' . $asunto);
        if ($responderA !== null) {
            $mensaje->setReplyTo($responderA); // "Responder" le contesta a quien escribió
        }
        try {
            $ok = $mensaje->send();
        } catch (\Throwable $e) { // servidor de correo caído o mal configurado: no rompe la página
            Yii::error('Error del servidor de correo: ' . $e->getMessage(), __METHOD__);
            $ok = false;
        }
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
            "Hola <strong>$nombre</strong>, se creó su cuenta en el panel de HUK-THE-ICHI GLOBAL GROUP"
                . ($rol !== '' ? " como <strong>$rol</strong>." : '.'),
            $this->recuadroHuk('Usuario: <strong>' . Html::encode($model->usuario) . '</strong><br>Email: <strong>' . Html::encode($model->email) . '</strong>'),
            'Puede ingresar con su usuario o con su email.',
            'Si no conoce su contraseña, en la pantalla de ingreso toque <strong>«¿Olvidó su contraseña?»</strong> y le llegará un enlace para crearla.',
        ], ['Ingresar al panel', $this->urlPanel()]);

        return $this->enviarHuk($model->email, 'Bienvenido/a', $cuerpo);
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



    /** Bloqueo hecho por un administrador (con motivo). */
    public function enviarCorreoDeBloqueo($model, $motivo)
    {
        $cuerpo = $this->cuerpoHuk('Su cuenta fue bloqueada', [
            'Hola <strong>' . Html::encode($model->nombrecompleto) . '</strong>, un administrador bloqueó su acceso al panel por este motivo:',
            $this->recuadroHuk(nl2br(Html::encode($motivo))),
            'Si cree que es un error, comuníquese con el administrador.',
        ]);
        return $this->enviarHuk($model->email, 'Cuenta bloqueada', $cuerpo);
    }

    /** Activación hecha por un administrador (con motivo). */
    public function enviarCorreoDedesBloqueo($model, $motivo)
    {
        $cuerpo = $this->cuerpoHuk('Su cuenta fue activada', [
            'Hola <strong>' . Html::encode($model->nombrecompleto) . '</strong>, ya puede volver a ingresar al panel. Motivo:',
            $this->recuadroHuk(nl2br(Html::encode($motivo))),
        ], ['Ingresar al panel', $this->urlPanel()]);
        return $this->enviarHuk($model->email, 'Cuenta activada', $cuerpo);
    }

    /** Enlace de 30 minutos para crear una contraseña nueva. */
    public function enviarCorreoRecuperacion($id, $enlace)
    {
        $usuario = Usuarios::findOne(['idusuario' => $id]);
        $cuerpo = $this->cuerpoHuk('Recuperación de contraseña', [
            'Hola <strong>' . Html::encode($usuario->nombrecompleto) . '</strong>, recibimos una solicitud para restablecer su contraseña.',
            'Haga clic en el botón para crear una nueva. El enlace vence en <strong>30 minutos</strong> y sirve una sola vez.',
        ], ['Crear nueva contraseña', $enlace])
            . Html::tag('p', 'Si el botón no funciona, copie este enlace en su navegador:', ['style' => 'color: #ffffff; margin: 0 0 6px;'])
            . Html::tag('p', Html::encode($enlace), ['style' => 'color: #e6c27a; word-break: break-all; font-size: 13px; margin: 0 0 14px;'])
            . Html::tag('p', 'Si no pidió este cambio, ignore este mensaje: su contraseña actual sigue funcionando.', ['style' => 'color: #ffffff; margin: 0 0 14px;']);
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
        return Html::tag('p', 'Si el botón no funciona, copie este enlace en su navegador:', ['style' => 'color: #ffffff; margin: 0 0 6px;'])
            . Html::tag('p', Html::encode($enlace), ['style' => 'color: #e6c27a; word-break: break-all; font-size: 13px; margin: 0 0 14px;']);
    }

    /** Registro desde la web: confirmar el correo. */
    public function enviarCorreoVerificacionInversor($usuario, $enlace)
    {
        $cuerpo = $this->cuerpoHuk('Confirme su correo', [
            'Hola <strong>' . Html::encode($usuario->nombrecompleto) . '</strong>, gracias por su interés en invertir con nosotros.',
            'Para enviar su solicitud, confirme su correo con el botón. El enlace vence en <strong>24 horas</strong>.',
        ], ['Confirmar mi correo', $enlace]) . $this->enlaceTexto($enlace)
            . Html::tag('p', 'Si no hizo este registro, ignore este mensaje.', ['style' => 'color: #ffffff; margin: 0 0 14px;']);
        return $this->enviarHuk($usuario->email, 'Confirme su correo', $cuerpo);
    }

    /** Alta desde el panel: el inversor crea su contraseña. */
    public function enviarCorreoActivacionInversor($usuario, $enlace)
    {
        $cuerpo = $this->cuerpoHuk('Active su cuenta de inversor', [
            'Hola <strong>' . Html::encode($usuario->nombrecompleto) . '</strong>, nuestro equipo registró su solicitud de inversor.',
            'Para entrar a la plataforma, cree su contraseña con el botón. El enlace vence en <strong>72 horas</strong> y sirve una sola vez.',
        ], ['Crear mi contraseña', $enlace]) . $this->enlaceTexto($enlace);
        return $this->enviarHuk($usuario->email, 'Active su cuenta de inversor', $cuerpo);
    }

    public function enviarCorreoRecuperacionInversor($usuario, $enlace)
    {
        $cuerpo = $this->cuerpoHuk('Recuperación de contraseña', [
            'Hola <strong>' . Html::encode($usuario->nombrecompleto) . '</strong>, recibimos una solicitud para restablecer su contraseña.',
            'Haga clic en el botón para crear una nueva. El enlace vence en <strong>30 minutos</strong> y sirve una sola vez.',
        ], ['Crear nueva contraseña', $enlace]) . $this->enlaceTexto($enlace)
            . Html::tag('p', 'Si no pidió este cambio, ignore este mensaje: su contraseña actual sigue funcionando.', ['style' => 'color: #ffffff; margin: 0 0 14px;']);
        return $this->enviarHuk($usuario->email, 'Recuperación de contraseña', $cuerpo);
    }

    /** Consulta del formulario de Contacto ("Responder" le contesta a quien escribió). */
    public function enviarCorreoContacto(array $datos, string $destino)
    {
        $fila = static fn (string $etiqueta, $valor) => $valor !== null && $valor !== ''
            ? '<strong>' . $etiqueta . ':</strong> ' . Html::encode($valor) . '<br>' : '';
        $cuerpo = $this->cuerpoHuk('Nueva consulta desde la web', [
            $this->recuadroHuk(
                $fila('Nombre', $datos['nombre'] ?? '')
                . $fila('Empresa', $datos['empresa'] ?? '')
                . $fila('Teléfono', $datos['telefono'] ?? '')
                . $fila('Correo', $datos['email'] ?? '')
            ),
            nl2br(Html::encode($datos['mensaje'] ?? '')),
            'Para contestar, responda este correo: le llega directo a la persona.',
        ]);
        return $this->enviarHuk($destino, 'Consulta de ' . ($datos['nombre'] ?? 'la web'), $cuerpo, [$datos['email'] => $datos['nombre']]);
    }

    /** Lista de áreas para un correo: "• Minería<br>• Energía" (escapada). */
    private function listaAreas(array $areas): string
    {
        return implode('<br>', array_map(fn($a) => '• ' . Html::encode($a), $areas));
    }

    /** Tabla de datos (rótulo => valor) para el aviso de solicitud nueva. Valores escapados acá. */
    private function tablaDatos(array $datos): string
    {
        $filas = '';
        foreach ($datos as $rotulo => $valor) {
            if ($valor === null || $valor === '') {
                continue;
            }
            $filas .= '<tr><td style="padding: 6px 12px 6px 0; color: #e6c27a; font-size: 13px; white-space: nowrap; vertical-align: top;">' . Html::encode($rotulo) . '</td>'
                . '<td style="padding: 6px 0; color: #ffffff; font-size: 14px;">' . nl2br(Html::encode($valor)) . '</td></tr>';
        }
        return '<table role="presentation" cellpadding="0" cellspacing="0" style="width: 100%; background-color: #1a1610; border-left: 3px solid #bd944f; border-radius: 4px; padding: 10px 14px;">' . $filas . '</table>';
    }

    /**
     * Solicitud nueva (el inversor confirmó su correo): al correo del sistema, a los Superadmin y a los distribuidores.
     * Lleva el resumen; el documento de identidad NO se adjunta (se ve solo en el panel).
     */
    public function enviarCorreoNuevaSolicitudInversor(array $emails, $usuario, $enlacePanel, array $resumen = [])
    {
        $cuerpo = $this->cuerpoHuk('Nueva solicitud de inversor', array_values(array_filter([
            '<strong>' . Html::encode($usuario->nombrecompleto) . '</strong> confirmó su correo y su solicitud está <strong>pendiente de revisión</strong>.',
            $resumen ? $this->tablaDatos($resumen) : null,
            'Revísela en el panel (ahí está también su documento de identidad) para coordinar con la persona, asignarle áreas y aprobarla o rechazarla.',
        ])), ['Revisar en el panel', $enlacePanel]);
        $ok = true;
        foreach (array_unique(array_filter($emails)) as $email) {
            $ok = $this->enviarHuk($email, 'Nueva solicitud de inversor: ' . $usuario->nombrecompleto, $cuerpo) && $ok;
        }
        return $ok;
    }

    public function enviarCorreoInteresInversor($usuario, $proyecto, $interes, string $quienContacta)
    {
        $cuerpo = $this->cuerpoHuk('Recibimos su interés en invertir', [
            'Hola <strong>' . Html::encode($usuario->nombrecompleto) . '</strong>, gracias por su interés en <strong>' . Html::encode($proyecto->titulo) . '</strong>.',
            $this->tablaDatos(array_filter([
                'Monto estimado' => $interes->montoTexto(),
                'Prefiere que lo contactemos por' => $interes->medioTexto() . ($interes->contacto_horario ? ' · ' . $interes->contacto_horario : ''),
            ])),
            Html::encode($quienContacta) . ' se comunicará con usted en las próximas <strong>' . \app\models\InteresInversion::HORAS_CONTACTO . ' horas hábiles</strong>.',
            'Esto es una manifestación de interés: no lo compromete a invertir. Puede ver el estado en <strong>Mi cuenta → Mis inversiones</strong>.',
        ], ['Ver el proyecto', Url::to(['/proyectos/ver', 'slug' => $proyecto->slug], true)]);
        return $this->enviarHuk($usuario->email, 'Recibimos su interés: ' . $proyecto->titulo, $cuerpo);
    }

    public function enviarCorreoInteresEquipo(array $emails, $usuario, $proyecto, array $resumen, $enlacePanel, bool $nuevo = true)
    {
        $cuerpo = $this->cuerpoHuk($nuevo ? 'Un inversor quiere invertir' : 'Un inversor actualizó su interés', [
            '<strong>' . Html::encode($usuario->nombrecompleto) . '</strong> ' . ($nuevo ? 'quiere invertir' : 'actualizó su interés') . ' en <strong>'
                . Html::encode($proyecto->titulo) . '</strong>.',
            $this->tablaDatos($resumen),
            'Le prometimos contactarlo en <strong>' . \app\models\InteresInversion::HORAS_CONTACTO . ' horas hábiles</strong>. '
                . 'Registre el seguimiento en el panel (pestaña <strong>Interesados</strong> del proyecto).',
        ], ['Ver en el panel', $enlacePanel]);
        $asunto = ($nuevo ? 'Quiere invertir: ' : 'Actualizó su interés: ') . $usuario->nombrecompleto . ' · ' . $proyecto->titulo;
        $ok = true;
        foreach (array_unique(array_filter($emails)) as $email) {
            $ok = $this->enviarHuk($email, $asunto, $cuerpo) && $ok;
        }
        return $ok;
    }

    /** Pedido de más áreas desde Mi cuenta: a quienes asignan áreas (no a los gerentes: todavía no está asignado). */
    public function enviarCorreoPedidoAreas(array $emails, $usuario, array $areas, $enlacePanel)
    {
        $cuerpo = $this->cuerpoHuk('Un inversor pide más áreas', [
            '<strong>' . Html::encode($usuario->nombrecompleto) . '</strong>, inversor aprobado, pidió acceso a:',
            $this->listaAreas($areas),
            'Si corresponde, asígnelas desde su ficha en el panel (sección <strong>Áreas que puede ver</strong>). El inversor recibe un correo cuando se actualizan.',
        ], ['Ver en el panel', $enlacePanel]);
        $ok = true;
        foreach (array_unique(array_filter($emails)) as $email) {
            $ok = $this->enviarHuk($email, 'Pedido de más áreas: ' . $usuario->nombrecompleto, $cuerpo) && $ok;
        }
        return $ok;
    }

    public function enviarCorreoContrasenaCambiadaInversor($usuario)
    {
        $cuerpo = $this->cuerpoHuk('Su contraseña fue cambiada', [
            'Hola <strong>' . Html::encode($usuario->nombrecompleto) . '</strong>, le confirmamos que la contraseña de su cuenta de inversor se cambió el '
                . date('d/m/Y \a \l\a\s H:i') . ' desde <strong>Mi cuenta</strong>.',
            'Si <strong>no</strong> fue usted, recupere el acceso con «¿Olvidó su contraseña?» y escríbanos de inmediato.',
        ], ['¿Olvidó su contraseña?', Url::to(['/cuenta/acceso/olvide'], true)]);
        return $this->enviarHuk($usuario->email, 'Su contraseña fue cambiada', $cuerpo);
    }

    /** Aprobado: las áreas habilitadas y, si pidió otras que no se le asignaron, cuáles. */
    public function enviarCorreoInversorAprobado($usuario, array $areas, array $noIncluidas = [])
    {
        $cuerpo = $this->cuerpoHuk('¡Su solicitud fue aprobada!', array_values(array_filter([
            'Hola <strong>' . Html::encode($usuario->nombrecompleto) . '</strong>, revisamos sus datos y <strong>su solicitud fue aprobada</strong>. Ya puede ver los proyectos de estas áreas:',
            $areas ? $this->recuadroHuk($this->listaAreas($areas)) : null,
            $noIncluidas ? 'Por ahora <strong>no fuiste seleccionado/a</strong> para: ' . Html::encode(implode(', ', $noIncluidas))
                . '. Si más adelante se habilitan, le avisaremos por este medio.' : null,
            'Al entrar por primera vez leerá y aceptará el acuerdo de confidencialidad.',
        ])), ['Ingresar a mi cuenta', $this->urlCuentaInversor()]);
        return $this->enviarHuk($usuario->email, 'Su solicitud fue aprobada', $cuerpo);
    }

    /** Rechazado: el motivo y las áreas que había pedido (no fue seleccionado/a para ellas). */
    public function enviarCorreoInversorRechazado($usuario, $motivo, array $areasPedidas = [])
    {
        $cuerpo = $this->cuerpoHuk('Sobre su solicitud de inversor', array_values(array_filter([
            'Hola <strong>' . Html::encode($usuario->nombrecompleto) . '</strong>, gracias por su interés. Revisamos su solicitud y por ahora <strong>no pudimos aprobarla</strong>.',
            $areasPedidas ? 'No fue seleccionado/a para las áreas que pidió:' : null,
            $areasPedidas ? $this->recuadroHuk($this->listaAreas($areasPedidas)) : null,
            $this->recuadroHuk('<strong>Motivo:</strong> ' . Html::encode($motivo)),
            'Si desea más información, responda este correo o escríbanos por los medios de contacto de la web.',
        ])));
        return $this->enviarHuk($usuario->email, 'Sobre su solicitud de inversor', $cuerpo);
    }

    /** Cambio de áreas de un inversor aprobado: cuáles se sumaron, cuáles se sacaron y cómo queda. */
    public function enviarCorreoAreasActualizadas($usuario, array $ahora, array $nuevas, array $quitadas, array $noAprobadas = [])
    {
        $cuerpo = $this->cuerpoHuk('Actualizamos sus áreas', array_values(array_filter([
            'Hola <strong>' . Html::encode($usuario->nombrecompleto) . '</strong>, actualizamos las áreas de su cuenta de inversor.',
            $nuevas ? '<strong>Ahora también puede ver:</strong>' : null,
            $nuevas ? $this->recuadroHuk($this->listaAreas($nuevas)) : null,
            $quitadas ? 'Ya no tiene acceso a: ' . Html::encode(implode(', ', $quitadas)) . '.' : null,
            $noAprobadas ? 'Sobre su pedido: por ahora no fue habilitado/a para ' . Html::encode(implode(', ', $noAprobadas))
                . '. Si desea más información, responda este correo.' : null,
            'Sus áreas habilitadas:',
            $this->recuadroHuk($this->listaAreas($ahora)),
        ])), ['Ver mis proyectos', $this->urlCuentaInversor()]);
        return $this->enviarHuk($usuario->email, 'Actualizamos sus áreas', $cuerpo);
    }

    /** A un gerente: un inversor fue habilitado en sus áreas. */
    public function enviarCorreoGerenteNuevoInversor($gerente, $inversor, array $areas, $enlacePanel)
    {
        $cuerpo = $this->cuerpoHuk('Nuevo inversor en su área', [
            'Hola <strong>' . Html::encode($gerente->nombrecompleto) . '</strong>, <strong>' . Html::encode($inversor->nombrecompleto)
                . '</strong> fue habilitado/a como inversor en ' . (count($areas) > 1 ? 'sus áreas' : 'su área') . ':',
            $this->recuadroHuk($this->listaAreas($areas)),
            'Desde ahora puede ver los proyectos publicados de ' . (count($areas) > 1 ? 'esas áreas' : 'esa área') . '.',
        ], ['Ver el inversor en el panel', $enlacePanel]);
        return $this->enviarHuk($gerente->email, 'Nuevo inversor en su área: ' . $inversor->nombrecompleto, $cuerpo);
    }

    public function enviarCorreoCuentaInversor($usuario, $bloqueada, $motivo)
    {
        $cuerpo = $this->cuerpoHuk($bloqueada ? 'Su cuenta fue suspendida' : 'Su cuenta fue reactivada', [
            'Hola <strong>' . Html::encode($usuario->nombrecompleto) . '</strong>, ' . ($bloqueada
                ? 'suspendimos el acceso a su cuenta de inversor.' : 'su cuenta de inversor vuelve a estar activa.'),
            $this->recuadroHuk('<strong>Motivo:</strong> ' . Html::encode($motivo)),
        ], $bloqueada ? null : ['Entrar a mi cuenta', $this->urlCuentaInversor()]);
        return $this->enviarHuk($usuario->email, $bloqueada ? 'Su cuenta fue suspendida' : 'Su cuenta fue reactivada', $cuerpo);
    }

    /** Bloqueo automático por intentos fallidos. */
    public function enviarCorreoDeBloqueoporintentos($id)
    {
        $usuario = Usuarios::findOne(['idusuario' => $id]);
        $cuerpo = $this->cuerpoHuk('Cuenta bloqueada temporalmente', [
            'Hola <strong>' . Html::encode($usuario->nombrecompleto) . '</strong>, hubo <strong>' . AuthService::MAX_INTENTOS
                . ' intentos fallidos</strong> de ingreso con su cuenta, así que la bloqueamos por <strong>'
                . AuthService::MINUTOS_BLOQUEO . ' minutos</strong> por seguridad.',
            'Si fue usted y no recuerda su contraseña, puede crear una nueva con «¿Olvidó su contraseña?».',
            'Si <strong>no</strong> fue usted, cambie su contraseña apenas vuelva a ingresar.',
        ], ['¿Olvidó su contraseña?', Url::to([(int) $usuario->idrol === Rol::INVERSOR ? '/cuenta/acceso/olvide' : '/admin/cuenta/forget'], true)]);
        return $this->enviarHuk($usuario->email, 'Cuenta bloqueada temporalmente', $cuerpo);
    }

    /** Un administrador quitó el bloqueo por intentos. */
    public function enviarCorreoDesbloqueoPorIntentos($usuario)
    {
        $cuerpo = $this->cuerpoHuk('Cuenta desbloqueada', [
            'Hola <strong>' . Html::encode($usuario->nombrecompleto) . '</strong>, nuestro equipo desbloqueó su cuenta, que estaba bloqueada por intentos fallidos. Ya puede ingresar.',
            'Si no reconoce esos intentos, cambie su contraseña desde <strong>' . ((int) $usuario->idrol === Rol::INVERSOR ? 'Mi cuenta → Seguridad' : 'Mi perfil') . '</strong>.',
        ], (int) $usuario->idrol === Rol::INVERSOR ? ['Ingresar', $this->urlCuentaInversor()] : ['Ingresar al panel', $this->urlPanel()]);
        return $this->enviarHuk($usuario->email, 'Cuenta desbloqueada', $cuerpo);
    }






}
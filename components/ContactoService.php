<?php

namespace app\components;

use app\models\Configuracion;
use app\models\ContactoForm;
use Yii;
use yii\httpclient\Client;

/**
 * Formulario de contacto (/contacto).
 * · Destino: "Email de contacto" de Datos generales (si está vacío, el email del sistema). Responder al correo le contesta a la persona.
 * · Antispam: campo trampa oculto (ContactoForm::sitio), máximo ENVIOS_MAX mensajes por IP cada VENTANA segundos,
 *   y Google reCAPTCHA v2 ("No soy un robot") si están las claves en config/params.php → 'recaptcha'.
 *   Sin claves el formulario funciona igual, sin el recuadro de Google.
 */
class ContactoService implements InterfaceContacto
{
    const ENVIOS_MAX = 5;
    const VENTANA = 600; // 10 minutos

    private InterfaceCorreos $correos;

    public function __construct(InterfaceCorreos $correos)
    {
        $this->correos = $correos;
    }

    public function recaptchaSiteKey(): ?string
    {
        $clave = trim((string) (Yii::$app->params['recaptcha']['siteKey'] ?? ''));
        return $clave !== '' && $this->recaptchaSecret() !== null ? $clave : null;
    }

    public function enviar(ContactoForm $form, ?string $token, ?string $ip): array
    {
        if (!$form->validate()) {
            return ['exito' => false, 'mensaje' => 'Revise los campos marcados.'];
        }
        $clave = 'contacto-envios-' . md5((string) $ip);
        $envios = (int) Yii::$app->cache->get($clave);
        if ($envios >= self::ENVIOS_MAX) {
            return ['exito' => false, 'mensaje' => 'Ya recibimos varios mensajes suyos. Pruebe de nuevo en unos minutos.'];
        }
        if ($this->recaptchaSiteKey() !== null && !$this->recaptchaValido($token, $ip)) {
            return ['exito' => false, 'mensaje' => 'Marque la casilla "No soy un robot" y vuelva a enviar.'];
        }

        $conf = Configuracion::find()->orderBy('idconfig')->one();
        $destino = trim((string) ($conf->email_web ?? '')) ?: trim((string) ($conf->email ?? ''));
        if ($destino === '') {
            Yii::error('Formulario de contacto: no hay email de destino en Datos generales.', __METHOD__);
            return ['exito' => false, 'mensaje' => 'No pudimos enviar su mensaje. Escríbanos directo al correo o al teléfono de esta página.'];
        }
        if (!$this->correos->enviarCorreoContacto($form->getAttributes(['nombre', 'empresa', 'telefono', 'email', 'mensaje']), $destino)) {
            return ['exito' => false, 'mensaje' => 'No pudimos enviar su mensaje. Pruebe de nuevo en un rato o escríbanos directo al correo.'];
        }
        Yii::$app->cache->set($clave, $envios + 1, self::VENTANA);
        return ['exito' => true, 'mensaje' => '¡Gracias, ' . $form->nombre . '! Recibimos su consulta y le responderemos a la brevedad.'];
    }

    private function recaptchaSecret(): ?string
    {
        $clave = trim((string) (Yii::$app->params['recaptcha']['secretKey'] ?? ''));
        return $clave !== '' ? $clave : null;
    }

    /** Pregunta a Google si la casilla se marcó bien (https://developers.google.com/recaptcha/docs/verify). */
    private function recaptchaValido(?string $token, ?string $ip): bool
    {
        if ($token === null || $token === '') {
            return false;
        }
        try {
            $respuesta = (new Client(['transport' => 'yii\httpclient\CurlTransport']))->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $this->recaptchaSecret(),
                'response' => $token,
                'remoteip' => (string) $ip,
            ])->setOptions(['timeout' => 8])->send();
            return $respuesta->isOk && !empty($respuesta->data['success']);
        } catch (\Throwable $e) {
            Yii::error('reCAPTCHA: no se pudo verificar (' . $e->getMessage() . ').', __METHOD__);
            return false;
        }
    }
}

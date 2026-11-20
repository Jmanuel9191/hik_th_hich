<?php

namespace app\components;

use app\models\ContactoForm;

/**
 * Formulario de contacto de la web: valida, controla los envíos seguidos, verifica reCAPTCHA (si hay claves) y manda el correo.
 */
interface InterfaceContacto
{
    /**
     * @param string|null $token respuesta de reCAPTCHA (g-recaptcha-response)
     * @return array ['exito' => bool, 'mensaje' => string]
     */
    public function enviar(ContactoForm $form, ?string $token, ?string $ip): array;

    /** Clave pública de reCAPTCHA, o null si no está configurada (entonces el formulario no lo muestra). */
    public function recaptchaSiteKey(): ?string;
}

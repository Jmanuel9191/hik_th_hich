<?php

namespace app\components;

use Yii;
use yii\helpers\Url;

/**
 * Junta los datos del header y el footer de la web pública.
 * No consulta tablas: cada dato lo da el servicio dueño (Configuración, Páginas, Ecosistemas).
 */
class SitioService implements InterfaceSitio
{
    const EMPRESA_POR_DEFECTO = 'HUK-THE-ICHI GLOBAL GROUP';

    /** Imágenes fijas del layout (assets_b/img). */
    const IMAGENES = [
        'logo'    => 'logo.png',
        'logoPie' => 'logo-footer.png',
        'favicon' => 'favicon.png',
        'fenix'   => 'fenix.gif',
    ];

    private InterfaceConfiguracion $configuracion;
    private InterfacePaginas $paginas;
    private InterfaceEcosistemas $ecosistemas;

    public function __construct(InterfaceConfiguracion $configuracion, InterfacePaginas $paginas, InterfaceEcosistemas $ecosistemas)
    {
        $this->configuracion = $configuracion;
        $this->paginas = $paginas;
        $this->ecosistemas = $ecosistemas;
    }

    public function layout(): array
    {
        $conf = $this->configuracion->paraWeb();
        $empresa = ($conf['datos']['titulo_pagina'] ?? '') ?: self::EMPRESA_POR_DEFECTO;

        return [
            'config'        => $conf['datos'],
            'empresa'       => $empresa,
            'nombreEmpresa' => ($conf['datos']['nombre_empresa'] ?? '') ?: $empresa,
            'redes'         => $conf['redes'],
            'pie'           => $this->paginas->pieWeb(),
            'ecosistemas'   => $this->ecosistemas->paraMenu(),
            'imagenes'      => $this->imagenes(),
        ];
    }

    public function imagen(string $clave): ?string
    {
        $archivo = self::IMAGENES[$clave] ?? null;
        return $archivo !== null && is_file(Yii::getAlias('@webroot/assets_b/img/' . $archivo)) ? Url::to('@web/assets_b/img/' . $archivo) : null;
    }

    /** URL de cada imagen, o null si el archivo no está (así no se pide y no da 404). */
    private function imagenes(): array
    {
        $urls = [];
        foreach (array_keys(self::IMAGENES) as $clave) {
            $urls[$clave] = $this->imagen($clave);
        }
        return $urls;
    }
}

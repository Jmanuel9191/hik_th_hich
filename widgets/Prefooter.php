<?php

namespace app\widgets;

use app\components\InterfaceBanners;
use app\components\InterfaceSitio;
use yii\base\Widget;

/**
 * Prefooter: banner de cierre arriba del footer (logo, dos líneas de título y botón).
 * Se administra en Panel → Banners → pestaña "Prefooter" (sale el primero vigente de la lista).
 * Va solo en las páginas donde lo pongas:  <?= \app\widgets\Prefooter::widget() ?>
 * Si no hay banner vigente no muestra nada. Vista: views/site/widgets/prefooter.php · estilos: huk.css sección 23.
 */
class Prefooter extends Widget
{
    /** Pestaña de Banners de donde sale (por si un día hay otro prefooter). */
    public $ubicacion = 'prefooter';

    private InterfaceBanners $banners;
    private InterfaceSitio $sitio;

    public function __construct(InterfaceBanners $banners, InterfaceSitio $sitio, $config = [])
    {
        $this->banners = $banners;
        $this->sitio = $sitio;
        parent::__construct($config);
    }

    public function run()
    {
        $banner = $this->banners->encabezado($this->ubicacion);
        if ($banner === null) {
            return '';
        }
        return $this->render('@app/views/site/widgets/prefooter', [
            'banner' => $banner,
            'logo' => $this->sitio->imagen('logoPie'),
        ]);
    }
}

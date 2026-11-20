<?php

namespace app\widgets;

use app\components\InterfaceProyectosWeb;
use app\models\Ecosistema;
use Yii;
use yii\base\Widget;

/**
 * Proyectos en la página de un ecosistema:
 *  · inversor con acceso a ese ecosistema y proyectos visibles → carrusel de destacados (de uno en uno);
 *  · cualquier otro caso → el llamado de siempre (LlamadoProyectos: registrarse / ingresar / ver proyectos).
 * Uso: <?= \app\widgets\ProyectosEcosistema::widget(['ecosistema' => $ecosistema]) ?>
 * Vista: views/site/widgets/proyecto_carrusel.php · estilos: huk.css sección 28.
 */
class ProyectosEcosistema extends Widget
{
    public Ecosistema $ecosistema;

    public function run()
    {
        $carrusel = Yii::$container->get(InterfaceProyectosWeb::class)->carrusel((int) $this->ecosistema->idecosistema);
        if ($carrusel === null) {
            return LlamadoProyectos::widget();
        }
        return $this->render('@app/views/site/widgets/proyecto_carrusel', $carrusel + ['ecosistema' => $this->ecosistema]);
    }
}

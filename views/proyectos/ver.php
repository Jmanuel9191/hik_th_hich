<?php

/**
 * Página de un proyecto (/proyectos/<slug>), también la vista previa del panel.
 * Banner (_banner) → menú de anclas fijo (_menu) → Ficha automática (_ficha) → secciones (_seccion, cada una con sus filas de bloques) → pie.
 * Estilos: assets_b/css/huk-proyecto.css · menú, scroll suave y efectos: assets_b/js/huk-proyecto.js.
 * Fotos y documentos: siempre por $medios (la web o el panel, según quién mira).
 *
 * @var yii\web\View $this
 * @var app\models\Proyecto $proyecto
 * @var array|null $ficha   Proyecto::datosFicha() (null si está apagada o no hay datos)
 * @var array $secciones  [['seccion' => ProyectoSeccion, 'filas' => array]]
 * @var array $menu       ancla => nombre
 * @var array $biblioteca  fotos, videos y documentos del proyecto (ProyectoWebService::biblioteca)
 * @var app\components\MediosProyecto $medios
 * @var bool $vistaPrevia
 * @var array|null $interes  "Quiero invertir": previo, puede, form (no viene en la vista previa del panel)
 */

use app\assets_b\ProyectoAsset;
use yii\helpers\Html;
use yii\helpers\Url;

ProyectoAsset::register($this);

$this->title = $proyecto->titulo . ' · Proyectos';
$contexto = ['proyecto' => $proyecto, 'medios' => $medios, 'biblioteca' => $biblioteca];
$interes = $vistaPrevia ? null : ($interes ?? null);
?>

<?= $this->render('_banner', ['proyecto' => $proyecto, 'medios' => $medios, 'interes' => $interes]) ?>

<?php if ($menu): ?>
    <?= $this->render('_menu', ['menu' => $menu]) ?>
<?php endif; ?>

<div class="huk-pro" data-pro-pagina>
    <?php if ($ficha): ?>
        <?= $this->render('_ficha', ['ficha' => $ficha]) ?>
    <?php endif; ?>

    <?php foreach ($secciones as $a): ?>
        <?= $this->render('_seccion', ['seccion' => $a['seccion'], 'filas' => $a['filas'], 'contexto' => $contexto]) ?>
    <?php endforeach; ?>

    <?php if (!$secciones && !$ficha): ?>
        <section class="huk-pro-sec is-blanco">
            <div class="container">
                <p class="huk-pro-vacio" data-reveal>
                    <?= $vistaPrevia ? 'Todavía no hay secciones visibles. Agréguelas en la pestaña «Página web».' : 'Muy pronto encontrará aquí toda la información de este proyecto.' ?>
                </p>
            </div>
        </section>
    <?php endif; ?>
</div>

<?php if ($interes): ?>
    <section class="huk-pro-sec is-invertir">
        <div class="container" data-reveal>
            <?= $this->render('_interes', ['proyecto' => $proyecto, 'previo' => $interes['previo'], 'puede' => $interes['puede'], 'lugar' => 'pie']) ?>
        </div>
    </section>
    <?= $this->render('_interes_modal', ['proyecto' => $proyecto, 'form' => $interes['form'], 'previo' => $interes['previo']]) ?>
<?php endif; ?>

<nav class="huk-pro-pie" aria-label="Más proyectos">
    <div class="container">
        <?php if (!$vistaPrevia): ?>
            <?= Html::a('<i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Todos mis proyectos', ['/proyectos/index'], ['class' => 'huk-pro-pie-link']) ?>
            <?= Html::a('Más de ' . Html::encode($proyecto->ecosistema->nombre ?? 'este ecosistema') . ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>',
                ['/proyectos/index', 'ecosistema' => $proyecto->ecosistema->slug ?? null], ['class' => 'huk-pro-pie-link']) ?>
        <?php endif; ?>
    </div>
</nav>

<?php /* Franja fija de la vista previa: va al final para que el banner siga siendo lo primero (se mete debajo del menú) */ ?>
<?php if ($vistaPrevia): ?>
    <div class="huk-pro-previa" role="status">
        <span><i class="fa-solid fa-eye" aria-hidden="true"></i> Vista previa · <b><?= Html::encode($proyecto->estadoNombre()) ?></b> · Así lo va a ver el inversor.</span>
        <?= Html::a('Volver al panel', ['/admin/banco-proyectos/view', 'id' => $proyecto->idproyecto, 'tab' => 'pagina']) ?>
    </div>
<?php endif; ?>

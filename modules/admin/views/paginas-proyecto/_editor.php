<?php

/**
 * Editor de la página web (de un proyecto o de una plantilla). Lo usan banco-proyectos/_pagina.php y
 * plantillas-proyecto/editar.php. Arriba el banner (solo proyectos); después las secciones con sus bloques.
 * Todo lo que cambia va por huk-pagina-proyecto.js → PaginasProyectoController (JSON) y recarga en el mismo lugar.
 *
 * @var yii\web\View $this
 * @var string $tipo proyecto | plantilla
 * @var app\models\Proyecto|app\models\ProyectoPlantilla $contenedor
 * @var app\models\ProyectoSeccion[] $secciones (con bloques)
 * @var app\models\BannerProyectoForm|null $banner
 * @var array $plantillas id => nombre
 * @var int|null $sugerida
 * @var bool $puedeEditar
 * @var bool $puedeGuardarPlantilla
 * @var app\components\MediosProyecto $medios
 */

use yii\helpers\Html;
use yii\helpers\Url;

\app\assets_b\PaginaProyectoAdminAsset::register($this);

$esProyecto = $tipo === 'proyecto';
$id = $esProyecto ? (int) $contenedor->idproyecto : (int) $contenedor->idplantilla;
$numero = 0;
?>
<div class="huk-pe" data-pe>
    <?php if ($esProyecto): ?>
        <?= $this->render('_banner_form', ['proyecto' => $contenedor, 'banner' => $banner, 'puedeEditar' => $puedeEditar]) ?>
    <?php endif; ?>

    <div class="huk-pe-barra">
        <div>
            <h3 class="huk-pe-barra-titulo"><i class="fa fa-list-alt"></i> Secciones <span class="badge"><?= count($secciones) ?></span></h3>
            <p class="huk-pe-ayuda">Cada sección tiene su título y sus bloques. Las numeradas llevan 01, 02… solas; las que van al menú aparecen en la barra de anclas.</p>
        </div>
        <div class="huk-pe-barra-botones">
            <?php if ($secciones): ?>
                <button type="button" class="btn btn-default" data-pe-plegar-todo title="Plegar o desplegar todas"><i class="fa fa-compress"></i> Plegar todo</button>
            <?php endif; ?>
            <?php if ($puedeEditar && $esProyecto && $secciones && $plantillas): ?>
                <button type="button" class="btn btn-default" data-toggle="collapse" data-target="#pe-plantilla"><i class="fa fa-clone"></i> Cargar plantilla</button>
            <?php endif; ?>
            <?php if ($puedeEditar && $esProyecto && $secciones && $puedeGuardarPlantilla): ?>
                <button type="button" class="btn btn-default" data-pe-guardar-plantilla="<?= Url::to(['/admin/paginas-proyecto/guardar-como-plantilla', 'id' => $id]) ?>"
                        data-ecosistema="<?= Html::encode($contenedor->ecosistema->nombre ?? '') ?>"><i class="fa fa-bookmark-o"></i> Guardar como plantilla</button>
            <?php endif; ?>
            <?php if ($esProyecto): ?>
                <?= Html::a('<i class="fa fa-eye"></i> Vista previa', ['/admin/paginas-proyecto/vista-previa', 'id' => $id], ['class' => 'btn btn-default', 'target' => '_blank', 'rel' => 'noopener']) ?>
            <?php endif; ?>
            <?php if ($puedeEditar): ?>
                <button type="button" class="btn btn-success" data-pe-abrir="<?= Url::to(['/admin/paginas-proyecto/seccion-form', 'tipo' => $tipo, 'id' => $id]) ?>">
                    <i class="fa fa-plus"></i> Agregar sección
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($puedeEditar && $esProyecto && $plantillas): ?>
        <div id="pe-plantilla" class="collapse<?= $secciones ? '' : ' in' ?>">
            <?= $this->render('_plantilla_form', ['id' => $id, 'plantillas' => $plantillas, 'sugerida' => $sugerida, 'conSecciones' => (bool) $secciones]) ?>
        </div>
    <?php endif; ?>

    <?php if (!$secciones): ?>
        <div class="huk-pe-vacio">
            <i class="fa fa-file-text-o"></i>
            <p><?= $esProyecto ? 'La página todavía no tiene secciones. Cargue una plantilla (arriba) o agregue la primera sección.' : 'Esta plantilla no tiene secciones todavía.' ?></p>
        </div>
    <?php endif; ?>

    <div class="huk-pe-secciones">
        <?php foreach ($secciones as $i => $s): ?>
            <?php $num = $s->numerada === '1' && $s->visible === '1' ? ++$numero : null; ?>
            <?= $this->render('_seccion', [
                'seccion' => $s, 'numero' => $num, 'primera' => $i === 0, 'ultima' => $i === count($secciones) - 1,
                'puedeEditar' => $puedeEditar, 'medios' => $medios,
            ]) ?>
        <?php endforeach; ?>
    </div>

    <?php if ($puedeEditar): ?>
        <?= $this->render('_catalogo') ?>
        <!-- Modal donde se cargan los formularios (sección y bloque) -->
        <div class="modal fade huk-pe-modal" id="pe-modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog modal-lg" role="document"><div class="modal-content" data-pe-modal-contenido></div></div>
        </div>
    <?php endif; ?>
</div>

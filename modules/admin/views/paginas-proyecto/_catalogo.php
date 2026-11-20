<?php

/**
 * Catálogo de bloques (modal): miniatura, nombre y para qué sirve, por grupo. Al elegir uno se abre su formulario
 * para la sección desde la que se abrió (huk-pagina-proyecto.js, data-pe-catalogo / data-pe-tipo).
 *
 * @var yii\web\View $this
 */

use app\models\bloques\Catalogo;
use yii\helpers\Html;
use yii\helpers\Url;

$miniaturas = require __DIR__ . '/_miniaturas.php';
?>
<div class="modal fade huk-pe-modal" id="pe-catalogo" tabindex="-1" role="dialog" aria-labelledby="pe-catalogo-titulo" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="pe-catalogo-titulo"><i class="fa fa-th-large"></i> Agregar bloque</h4>
                <p class="huk-pe-ayuda">Elija qué desea poner. El diseño ya está resuelto: usted solo carga el contenido.</p>
            </div>
            <div class="modal-body">
                <?php foreach (Catalogo::porGrupo() as $grupo => $tipos): ?>
                    <h5 class="huk-pe-cat-grupo"><?= Html::encode(Catalogo::GRUPOS[$grupo]) ?></h5>
                    <div class="huk-pe-cat">
                        <?php foreach ($tipos as $tipo => $clase): ?>
                            <button type="button" class="huk-pe-cat-item" data-pe-tipo="<?= Html::encode($tipo) ?>"
                                    data-url="<?= Url::to(['/admin/paginas-proyecto/bloque-form', 'tipo' => $tipo]) ?>">
                                <span class="huk-pe-cat-mini"><?= $miniaturas[$tipo] ?? '<i class="fa fa-' . $clase::ICONO . '"></i>' ?></span>
                                <strong><i class="fa fa-<?= $clase::ICONO ?>"></i> <?= Html::encode($clase::NOMBRE) ?></strong>
                                <small><?= Html::encode($clase::DESCRIPCION) ?></small>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

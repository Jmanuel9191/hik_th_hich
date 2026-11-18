<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Materiales de: ' . $model->titulo;
?>

<section class="content-header">
    <h1><i class="fa fa-cogs"></i> <?= Html::encode($this->title) ?></h1>
</section>

<section class="content">
<div class="row">
<div class="col-xs-12">
<div class="box" style="padding:10px 20px;">

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger"><?= Yii::$app->session->getFlash('error') ?></div>
    <?php endif; ?>

    <div class="row" style="margin-bottom:10px; margin-top:10px;">
        <div class="col-sm-6">
            <h4 style="margin:0;">Materiales actuales</h4>
        </div>
    </div>

    <?php if (empty($vinculos)): ?>
        <p><em>Este examen todavía no tiene materiales vinculados.</em></p>
    <?php else: ?>
        <table class="table table-bordered table-condensed">
            <thead>
                <tr>
                    <th>Material</th>
                    <th style="width:150px">Stock actual</th>
                    <th style="width:150px">Cantidad usada</th>
                    <th style="width:100px"></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($vinculos as $v): ?>
                <tr>
                    <td><?= Html::encode($v->material->titulo ?? '(material eliminado)') ?></td>
                    <td>
                        <?php
                            $stock = $v->material ? \app\models\InventarioLote::stockTotal($v->material->idproducto) : 0;
                        ?>
                        <span class="label label-<?= $stock < 10 ? 'danger' : 'success' ?>">
                            <?= $stock ?>
                        </span>
                    </td>
                    <td>
                        <form method="post" action="<?= Url::to(['materiales-editar', 'id' => $model->idproducto, 'idvinculo' => $v->idproducto_material_rel]) ?>" style="display:flex; gap:5px;">
                            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                            <input type="number" step="0.01" min="0.01" name="cantidad" value="<?= $v->cantidad ?>" class="form-control input-sm" style="width:90px">
                            <button type="submit" class="btn btn-xs btn-primary"><i class="fa fa-save"></i></button>
                        </form>
                    </td>
                    <td>
                        <a href="#" class="btn btn-xs btn-danger" onclick="
                            if(confirm('¿Quitar este material del examen?')){
                                fetch('<?= Url::to(['materiales-quitar', 'id' => $model->idproducto, 'idvinculo' => $v->idproducto_material_rel]) ?>', {
                                    method: 'POST',
                                    headers: { 'X-CSRF-Token': $('meta[name=csrf-token]').attr('content') }
                                })
                                .then(r=>r.json()).then(d=>{ if(d.exito) location.reload(); else alert(d.mensaje); });
                            }
                            return false;
                        ">Quitar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <hr>
    <h4>Agregar material</h4>
    <form method="post" action="<?= Url::to(['materiales-crear', 'id' => $model->idproducto]) ?>">
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
        <div class="row">
            <div class="col-sm-6">
                <select name="idproducto_material" class="form-control" required>
                    <option value="">-- Elegí un material --</option>
                    <?php foreach ($materialesDisponibles as $mat): ?>
                        <option value="<?= $mat->idproducto ?>"><?= Html::encode($mat->titulo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-3">
                <input type="number" step="0.01" min="0.01" name="cantidad" value="1" class="form-control" placeholder="Cantidad" required>
            </div>
            <div class="col-sm-3">
                <button type="submit" class="btn btn-success btn-block"><i class="fa fa-plus"></i> Agregar</button>
            </div>
        </div>
    </form>

</div>
</div>
</div>
</section>
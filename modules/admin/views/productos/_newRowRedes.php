<?php

use yii\helpers\Html;

?>
<div class="redes-input form-group">
    <?= Html::activeHiddenInput($model, "[$index]idtalla") ?>
    <div class="col-xs-6">
        <div <?= ($model->hasErrors('valor')) ? 'class="has-error"' : ''; ?> >
            <?= Html::activeTextInput($model, "[$index]valor", ['class' => 'form-control', 'id' => 'trabajo_' . $index, 'placeholder' => 'talla']); ?>
        </div>
    </div>
    <div class="col-xs-4">
        <div <?= ($model->hasErrors('estado')) ? 'class="has-error"' : ''; ?> >
            <div class="checkbox">
                <?= Html::activeCheckbox($model, "[$index]estado", ['id' => 'trabajo_' . $index]); ?>
            </div>
        </div>
    </div>

    <div class="col-xs-1">
        <?= Html::a('<i class="fa fa-remove"></i>', '#',
            [
                'class' => 'btn btn-danger btn-sm redes-input-remove',
            ]
        ) . Html::hiddenInput('cancel', $index, ['class' => 'redes-input-index']);
        ?>
    </div>
</div>

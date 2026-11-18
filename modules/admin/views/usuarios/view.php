<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Usuarios */

$this->title = $model->idusuario;
$this->params['breadcrumbs'][] = ['label' => 'Usuarios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<section class="content-header">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>


</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">

                <div class="box-header">
                    <?= Html::a('Atras', Yii::$app->request->referrer, [
                        'class' => 'btn btn-primary',
                    ]) ?>

                    <?= Html::a('Borrar', ['delete', 'id' => $model->idusuario], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'esta reguro?',
                            'method' => 'post',
                        ],
                    ]) ?>
                </div>

                <div class="box-body ">

                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            //'idusuario',
                            'nombre',
                            'apellido',
                            'direccion',
                            'telefono',
                            'pais',
                            'ciudad',
                            'email:email',
                            'contrasena',
                            'fecha_nacimiento',
                            'movil',
                            'fecha_registro',
                            //'estado',
                            'sexo',
                            //'activo',
                            // NUEVO: sello/firma. Mismo ajuste de ruta que en
                            // los formularios (el valor guardado no trae el
                            // baseUrl de la app, hay que agregárselo a mano).
                            [
                                'attribute' => 'foto_sello',
                                'label'     => 'Sello / firma',
                                'format'    => 'raw',
                                'value'     => function ($model) {
                                    if (empty($model->foto_sello)) {
                                        return '<span class="text-muted">Sin sello cargado</span>';
                                    }
                                    $src = $model->foto_sello;
                                    if (strpos($src, '/imagen/') === 0) {
                                        $src = Yii::$app->request->baseUrl . $src;
                                    }
                                    return Html::img($src, [
                                        'style' => 'max-width:350px; max-height:200px; border:1px solid #ddd; padding:4px; background:#fff;',
                                    ]);
                                },
                            ],
                        ],
                    ]) ?>

                </div>
            </div>
        </div>
</section>
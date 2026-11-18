<?php

/**
 * Mi perfil: mis datos y lo que pasó con mi cuenta.
 *
 * @var yii\web\View $this
 * @var app\models\Usuarios $model
 * @var yii\data\ActiveDataProvider $historial
 */

use app\components\Rol;
use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = 'Mi perfil';
$fecha = fn($v, $hora = true) => $v ? date($hora ? 'd/m/Y H:i' : 'd/m/Y', strtotime($v)) : '—';
?>

<section class="content-header">
    <h1><i class="fa fa-user-circle"></i> Mi perfil</h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-5">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Html::encode($model->nombrecompleto) ?></h3>
                    <span class="huk-clave-usuario"><i class="fa fa-id-badge"></i> <?= Html::encode($model->rol->nombre ?? '') ?></span>
                </div>
                <div class="box-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped detail-view'],
                        'attributes' => [
                            'usuario',
                            'email:email',
                            [
                                'label' => 'Ecosistemas a cargo',
                                'value' => implode(', ', array_map(fn($e) => $e->nombre, $model->ecosistemas)) ?: '—',
                                'visible' => (int) $model->idrol === Rol::GERENTE,
                            ],
                            'telefono',
                            'movil',
                            'pais_operacion',
                            'ciudad',
                            'direccion',
                            'cargo',
                            ['label' => 'Fecha de nacimiento', 'value' => $fecha($model->fecha_nacimiento, false)],
                            ['label' => 'Fecha de registro', 'value' => $fecha($model->fecha_registro)],
                            [
                                'label' => 'Último acceso',
                                'value' => $model->ultimo_acceso
                                    ? $fecha($model->ultimo_acceso) . ($model->ultimo_acceso_ip ? ' · IP ' . $model->ultimo_acceso_ip : '')
                                    : 'Nunca ingresó',
                            ],
                        ],
                    ]) ?>
                </div>
                <div class="box-footer">
                    <?= Html::a('<i class="fa fa-pencil"></i> Editar mis datos', ['updateperfil'], ['class' => 'btn btn-info']) ?>
                    <?= Html::a('<i class="fa fa-key"></i> Cambiar mi contraseña', ['updatepasswordperfil'], ['class' => 'btn btn-default']) ?>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-history"></i> Actividad de mi cuenta</h3></div>
                <div class="box-body">
                    <?= $this->render('_historial', ['historial' => $historial]) ?>
                </div>
            </div>
        </div>
    </div>
</section>

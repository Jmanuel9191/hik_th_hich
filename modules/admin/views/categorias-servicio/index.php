<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Análisis / Categorías';
?>

<section class="content-header">
    <h1><i class="fa fa-tags"></i> <?= Html::encode($this->title) ?></h1>
</section>

<section class="content">
<div class="row">
<div class="col-xs-12">
<div class="box">

<div class="box-header with-border">
    <?= Html::a('<i class="fa fa-plus"></i> Nueva Categoría', ['create'], ['class' => 'btn btn-success']) ?>
</div>

<div class="box-body">
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel'  => $searchModel,
    'columns'      => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'nombre',
            'header'    => 'Nombre',
        ],
        [
            'attribute' => 'alias',
            'header'    => 'Alias',
            'value'     => fn($m) => $m->alias,
        ],
        [
            'header' => 'Servicios vinculados',
            'value'  => function($m) {
                $cant = \app\models\Productos::find()
                    ->where(['idcategoria' => $m->idcategoria])
                    ->count();
                return $cant . ' servicio(s)';
            },
        ],
        /*[
            'attribute' => 'estado',
            'header'    => 'Estado',
            'format'    => 'raw',
            'value'     => function($m) {
                $cls   = $m->estado == 1 ? 'success' : 'danger';
                $label = $m->estado == 1 ? 'Activo' : 'Inactivo';
                return Html::a(
                    '<span class="label label-'.$cls.'">'.$label.'</span>',
                    '#',
                    [
                        'class'   => 'btn-estado',
                        'data-id' => $m->idcategoria,
                        'onclick' => "toggleEstado({$m->idcategoria}); return false;",
                    ]
                );
            },
            'filter' => [1 => 'Activo', 0 => 'Inactivo'],
        ],*/
        'fecha_registro',
        [
            'class'    => 'yii\grid\ActionColumn',
            'header'   => 'Operaciones',
            'template' => '<div class="btn-group">{update}{delete}</div>',
            'buttons'  => [
                'update' => fn($url, $m) => Html::a(
                    '<i class="fa fa-pencil"></i>',
                    $url,
                    ['class' => 'btn btn-info', 'title' => 'Editar']
                ),
                'delete' => fn($url, $m) => Html::a(
                    '<i class="fa fa-trash"></i>', '#', [
                    'class'   => 'btn btn-danger',
                    'title'   => 'Eliminar',
                    'onclick' => "if(confirm('¿Eliminar esta categoría?')){
                        fetch('{$url}', {
                            method:'POST',
                            headers:{'X-CSRF-Token':document.querySelector('meta[name=csrf-token]').content}
                        }).then(r=>r.json()).then(d=>{
                            if(d.exito) location.reload();
                            else alert(d.mensaje);
                        });
                    } return false;",
                ]),
            ],
        ],
    ],
]); ?>
</div>
</div>
</div>
</div>
</section>

<?php
$urlEstado = Url::to(['estado', 'id' => 0]);
$this->registerJs("
function toggleEstado(id) {
    var url = '{$urlEstado}'.replace('/0', '/' + id);
    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-Token': document.querySelector('meta[name=csrf-token]').content }
    }).then(r => r.json()).then(d => { location.reload(); });
}
");
?>

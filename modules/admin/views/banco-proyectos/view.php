<?php

/**
 * Proyecto: cabecera con estado y acciones + pestañas (ficha, página web, fotos, videos, documentos, historial).
 * Estado y eliminar: huk-proyectos.js (data-proyecto-estado / data-proyecto-eliminar).
 *
 * @var yii\web\View $this
 * @var app\models\Proyecto $model
 * @var string $tab
 * @var array|null $inversiones resumen de la ronda (pestaña Inversiones)
 * @var array $pestanas
 * @var bool $puedeEditar
 * @var bool $puedeEliminar
 * @var array $transiciones estado => [texto, ícono, clase, aviso]
 * @var yii\data\ActiveDataProvider|null $historial
 * @var array $descargas iddocumento => cantidad
 * @var app\models\ProyectoAvance[] $avances
 * @var int $limiteServidor bytes
 * @var array|null $editor datos del editor de la página web (pestaña pagina)
 * @var app\models\InteresInversion[] $interesados  "Quiero invertir" (pestaña Interesados)
 * @var bool $gestionarInteres
 * @var array $pagados idusuario => lo que ya pagó (Interesados)
 * @var bool $registrarPago
 */

use yii\helpers\Html;
use yii\helpers\Url;

\app\assets_b\ProyectosAdminAsset::register($this);

$this->title = $model->titulo;
$cantidad = [
    'fotos' => count($model->imagenes),
    'videos' => count($model->videos),
    'documentos' => count($model->documentos),
    'avances' => (int) $model->getAvances()->count(),
    'inversiones' => (int) $model->getPagos()->where(['estado' => 'vigente'])->count(),
];
?>

<section class="content-header">
    <h1><i class="fa fa-briefcase"></i> <?= Html::encode($model->titulo) ?></h1>
    <p class="huk-inicio-sub">
        <span class="huk-proyecto-codigo"><?= Html::encode($model->codigo) ?></span>
        · <?= Html::encode($model->ecosistema->nombre ?? '') ?>
        · <span class="huk-proyecto-tipo-label is-<?= Html::encode($model->tipo) ?>"><?= Html::encode(\app\models\Proyecto::TIPOS[$model->tipo] ?? '') ?></span>
        · <span class="label <?= $model->estadoClase() ?>"><?= Html::encode($model->estadoNombre()) ?></span>
    </p>
</section>

<section class="content">
    <div class="huk-barra-acciones">
        <?= Html::a('<i class="fa fa-arrow-left"></i> Volver', ['index'], ['class' => 'btn btn-default']) ?>
        <?php if ($puedeEditar): ?>
            <?= Html::a('<i class="fa fa-pencil"></i> Editar ficha', ['update', 'id' => $model->idproyecto], ['class' => 'btn btn-info']) ?>
        <?php endif; ?>
        <?= Html::a('<i class="fa fa-eye"></i> Vista previa', ['/admin/paginas-proyecto/vista-previa', 'id' => $model->idproyecto],
            ['class' => 'btn btn-default', 'target' => '_blank', 'rel' => 'noopener', 'title' => 'La página web como la va a ver el inversor']) ?>
        <?php foreach ($transiciones as $estado => [$texto, $icono, $clase, $aviso]): ?>
            <?= Html::button('<i class="fa fa-' . $icono . '"></i> ' . Html::encode($texto), [
                'class' => 'btn ' . $clase,
                'data-proyecto-estado' => Url::to(['estado', 'id' => $model->idproyecto, 'estado' => $estado]),
                'data-texto' => $texto,
                'data-aviso' => $aviso,
            ]) ?>
        <?php endforeach; ?>
        <?php if ($puedeEliminar): ?>
            <?= Html::button('<i class="fa fa-trash"></i> Eliminar', [
                'class' => 'btn btn-danger huk-boton-derecha',
                'data-proyecto-eliminar' => Url::to(['delete', 'id' => $model->idproyecto]),
                'data-volver' => Url::to(['index']),
                'data-nombre' => $model->titulo,
            ]) ?>
        <?php endif; ?>
    </div>

    <ul class="nav nav-tabs huk-tabs">
        <?php foreach ($pestanas as $id => $nombre): ?>
            <li class="<?= $id === $tab ? 'active' : '' ?>">
                <?= Html::a(Html::encode($nombre) . (isset($cantidad[$id]) ? ' <span class="badge">' . $cantidad[$id] . '</span>' : ''),
                    ['view', 'id' => $model->idproyecto, 'tab' => $id]) ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <?= $this->render('_' . $tab, [
        'model' => $model,
        'puedeEditar' => $puedeEditar,
        'historial' => $historial,
        'descargas' => $descargas,
        'avances' => $avances,
        'inversiones' => $inversiones,
        'interesados' => $interesados,
        'pagados' => $pagados,
        'gestionarInteres' => $gestionarInteres,
        'registrarPago' => $registrarPago,
        'limiteServidor' => $limiteServidor,
        'editor' => $editor,
    ]) ?>
</section>

<?php

/**
 * Encabezado común de las subpáginas de Nosotros: banner de su pestaña + breadcrumb Inicio / Nosotros / Subpágina.
 *
 * @var yii\web\View $this
 * @var app\models\Banners|null $encabezado
 * @var string $titulo
 */

$this->title = $titulo;
?>
<?= $this->render('//site/widgets/encabezado', [
    'banner' => $encabezado,
    'titulo' => $titulo,
    'migas' => [
        ['label' => 'Inicio', 'url' => ['/site/index']],
        ['label' => 'Nosotros', 'url' => ['/nosotros/index']],
        ['label' => $titulo],
    ],
]) ?>

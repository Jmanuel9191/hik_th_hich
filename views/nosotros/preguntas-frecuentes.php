<?php

/**
 * Nosotros → Preguntas frecuentes.
 *
 * @var yii\web\View $this
 * @var string $titulo
 * @var app\models\Banners|null $encabezado
 * @var app\models\PaginaSeccion|null $faq
 */

use app\widgets\Prefooter;
?>
<?= $this->render('_encabezado', ['encabezado' => $encabezado, 'titulo' => $titulo]) ?>

<?= $this->render('_faq', ['faq' => $faq]) ?>

<?= Prefooter::widget() ?>

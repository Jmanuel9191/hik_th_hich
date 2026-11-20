<?php

/**
 * Nosotros → Valores y Principios.
 *
 * @var yii\web\View $this
 * @var string $titulo
 * @var app\models\Banners|null $encabezado
 * @var app\models\PaginaSeccion|null $valores
 * @var app\models\PaginaSeccion|null $principios
 */

use app\widgets\Prefooter;
?>
<?= $this->render('_encabezado', ['encabezado' => $encabezado, 'titulo' => $titulo]) ?>

<?= $this->render('_valores', ['valores' => $valores, 'principios' => $principios]) ?>

<?= Prefooter::widget() ?>

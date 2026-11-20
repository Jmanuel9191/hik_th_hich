<?php

/**
 * Nosotros → Liderazgo (Panel → Equipo), las tarjetas Conectar · Transformar · Multiplicar y el prefooter.
 *
 * @var yii\web\View $this
 * @var string $titulo
 * @var app\models\Banners|null $encabezado
 * @var app\models\Equipo[] $equipo
 * @var app\models\PaginaItem[] $tarjetas
 */

use app\widgets\Prefooter;
?>
<?= $this->render('_encabezado', ['encabezado' => $encabezado, 'titulo' => $titulo]) ?>

<?= $this->render('_equipo', ['titulo' => $titulo, 'equipo' => $equipo]) ?>

<?= $this->render('//site/widgets/tarjetas', ['tarjetas' => $tarjetas, 'conBanner' => false]) ?>

<?= Prefooter::widget() ?>

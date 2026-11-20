<?php

/**
 * Nosotros → Propósito, Visión y Misión: tres tarjetas, Valores y Principios, las tarjetas
 * Conectar · Transformar · Multiplicar y el prefooter. Lo que esté oculto o vacío en el panel no sale.
 *
 * @var yii\web\View $this
 * @var string $titulo
 * @var app\models\Banners|null $encabezado
 * @var app\models\PaginaSeccion|null $proposito
 * @var app\models\PaginaSeccion|null $vision
 * @var app\models\PaginaSeccion|null $mision
 * @var app\models\PaginaSeccion|null $valores
 * @var app\models\PaginaSeccion|null $principios
 * @var app\models\PaginaItem[] $tarjetas
 */

use app\widgets\Prefooter;
?>
<?= $this->render('_encabezado', ['encabezado' => $encabezado, 'titulo' => $titulo]) ?>

<?= $this->render('_pvm', ['titulo' => $titulo, 'proposito' => $proposito, 'vision' => $vision, 'mision' => $mision]) ?>

<?= $this->render('_valores', ['valores' => $valores, 'principios' => $principios]) ?>

<?= $this->render('//site/widgets/tarjetas', ['tarjetas' => $tarjetas, 'conBanner' => false]) ?>

<?= Prefooter::widget() ?>

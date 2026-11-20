<?php

/**
 * Nosotros → Quiénes somos: encabezado, Quiénes somos, Mensaje del CEO, tarjetas (Conectar · Transformar ·
 * Multiplicar), bloques adicionales y el prefooter. Lo que esté oculto o vacío en el panel no sale.
 *
 * @var yii\web\View $this
 * @var string $titulo
 * @var app\models\Banners|null $encabezado
 * @var array $quienes  ['historia', 'destacados', 'cifras']
 * @var app\models\PaginaItem[] $tarjetas
 * @var app\models\PaginaSeccion|null $ceo
 * @var app\models\PaginaItem[] $bloques
 */

use app\widgets\Prefooter;
?>
<?= $this->render('_encabezado', ['encabezado' => $encabezado, 'titulo' => $titulo]) ?>

<?= $this->render('//site/widgets/quienes', $quienes + ['conBoton' => false]) ?>

<?= $this->render('_ceo', ['ceo' => $ceo]) ?>

<?= $this->render('//site/widgets/tarjetas', ['tarjetas' => $tarjetas, 'conBanner' => false]) ?>

<?= $this->render('_bloques', ['bloques' => $bloques]) ?>

<?= Prefooter::widget() ?>

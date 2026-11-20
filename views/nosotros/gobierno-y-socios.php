<?php

/**
 * Nosotros → Gobierno y socios: gobierno corporativo y ESG, propuesta de valor para socios,
 * ¿por qué asociarse? y el prefooter. Lo que esté oculto o vacío en el panel no sale.
 *
 * @var yii\web\View $this
 * @var string $titulo
 * @var app\models\Banners|null $encabezado
 * @var app\models\PaginaSeccion|null $gobierno
 * @var app\models\PaginaSeccion|null $impacto
 * @var app\models\PaginaSeccion|null $socios
 * @var app\models\PaginaSeccion|null $razones
 */

use app\widgets\Prefooter;
?>
<?= $this->render('_encabezado', ['encabezado' => $encabezado, 'titulo' => $titulo]) ?>

<?= $this->render('_gobierno', ['gobierno' => $gobierno, 'impacto' => $impacto]) ?>

<?= $this->render('_socios', ['socios' => $socios]) ?>

<?= $this->render('_razones', ['razones' => $razones]) ?>

<?= Prefooter::widget() ?>

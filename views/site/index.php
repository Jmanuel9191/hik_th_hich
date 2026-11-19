<?php

/**
 * Inicio.
 *
 * @var yii\web\View $this
 * @var app\models\Banners[] $banners    banners vigentes (Panel → Banners)
 * @var app\models\PaginaItem[] $tarjetas Conectar · Transformar · Multiplicar (Panel → Páginas → Nosotros)
 * @var array $quienes  resumen de Quiénes somos: historia, destacados y cifras (Panel → Páginas → Nosotros)
 */
?>

<?= $this->render('widgets/banner_mid', ['banners' => $banners, 'conTarjetas' => !empty($tarjetas)]) ?>

<?= $this->render('widgets/tarjetas', ['tarjetas' => $tarjetas, 'conBanner' => !empty($banners)]) ?>

<?= $this->render('widgets/quienes', $quienes) ?>

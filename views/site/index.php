<?php

/**
 * Inicio, en este orden (lo que esté oculto o vacío en el panel no sale):
 *  banner · tarjetas (Conectar · Transformar · Multiplicar) · Quiénes somos · Mensaje del CEO (plomo claro) ·
 *  Ecosistemas (negro) · Proyectos destacados (blanco; solo inversor aprobado, si no el llamado) ·
 *  Modelo de creación de valor (plomo) · Visión de expansión (blanco) · Noticias destacadas (plomo claro).
 * Los fondos los pone huk.css ("Inicio: fondos de cada bloque").
 *
 * @var yii\web\View $this
 * @var app\models\Banners[] $banners    banners vigentes (Panel → Banners)
 * @var app\models\PaginaItem[] $tarjetas Conectar · Transformar · Multiplicar (Panel → Páginas → Quiénes somos)
 * @var array $quienes  resumen de Quiénes somos: historia, destacados y cifras (Panel → Páginas → Quiénes somos)
 * @var app\models\PaginaSeccion|null $ceo
 * @var app\models\Ecosistema[] $ecosistemas
 * @var array|null $proyectos  null = no puede ver proyectos
 * @var app\models\PaginaSeccion|null $modelo
 * @var app\models\PaginaSeccion|null $propuesta
 * @var app\models\PaginaSeccion|null $expansion
 * @var app\models\PaginaSeccion|null $fases
 * @var app\models\Noticia[] $noticias
 */
?>

<?= $this->render('widgets/banner_mid', ['banners' => $banners, 'conTarjetas' => !empty($tarjetas)]) ?>

<?= $this->render('widgets/tarjetas', ['tarjetas' => $tarjetas, 'conBanner' => !empty($banners)]) ?>

<?= $this->render('widgets/quienes', $quienes) ?>

<div class="huk-ini is-claro"><?= $this->render('//nosotros/_ceo', ['ceo' => $ceo]) ?></div>

<?= $this->render('widgets/inicio_ecosistemas', ['ecosistemas' => $ecosistemas]) ?>

<?= $this->render('widgets/inicio_proyectos', ['proyectos' => $proyectos]) ?>

<div class="huk-ini is-plomo"><?= $this->render('widgets/modelo_valor', ['modelo' => $modelo, 'propuesta' => $propuesta]) ?></div>

<div class="huk-ini is-blanco"><?= $this->render('//nosotros/_expansion', ['expansion' => $expansion, 'fases' => $fases]) ?></div>

<div class="huk-ini is-claro"><?= $this->render('widgets/inicio_noticias', ['noticias' => $noticias]) ?></div>

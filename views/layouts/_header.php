<?php

/**
 * Header público HUK (estructura tipo Industo, colores HUK).
 * 3 piezas:
 *   1. Barra superior negra   (bienvenida · horario · redes · idioma)
 *   2. Franja principal       (logo + 3 datos de contacto + menú dorado con borde inclinado)
 *   3. Header compacto        (logo + menú) que baja al hacer scroll — lo activa huk.js
 * En celular: logo + botón ☰ que abre un menú lateral (offcanvas de Bootstrap).
 *
 * @var \yii\web\View $this
 * @var callable $c        lector de configuración: $c('email')
 * @var string   $empresa
 * @var ?string  $logoUrl
 * @var array    $menu     [['label'=>..., 'url'=>[...], 'items'=>[...]?], ...]
 * @var string   $ruta     controlador/acción actual
 * @var array    $redes    ['fa-facebook-f' => 'https://...']
 * @var callable $red      dibuja un ícono de red social
 * @var bool     $esPanel
 */

use yii\helpers\Html;
use yii\helpers\Url;

// ¿El ítem (o alguno de sus hijos) es la página actual?
$esActivo = static function (array $item) use ($ruta): bool {
    if (($item['url'][0] ?? null) === $ruta) {
        return true;
    }
    foreach ($item['items'] ?? [] as $hijo) {
        if (($hijo['url'][0] ?? null) === $ruta) {
            return true;
        }
    }
    return false;
};

// Dibuja el menú. $variante: 'main' (franja dorada) o 'sticky' (header compacto).
$dibujarMenu = static function (string $variante) use ($menu, $esActivo): string {
    $html = '';
    foreach ($menu as $item) {
        $activo = $esActivo($item);
        $hijos = $item['items'] ?? [];
        $clase = 'huk-nav-link' . ($activo ? ' active' : '');
        $texto = Html::encode($item['label'])
            . ($hijos ? ' <i class="fa-solid fa-chevron-down huk-caret" aria-hidden="true"></i>' : '');

        $html .= '<li class="huk-nav-item' . ($hijos ? ' has-children' : '') . '">';
        $html .= Html::a($texto, $item['url'], [
            'class' => $clase,
            'aria-current' => $activo ? 'page' : null,
        ]);
        if ($hijos) {
            $html .= '<ul class="huk-submenu">';
            foreach ($hijos as $hijo) {
                $html .= '<li>' . Html::a(Html::encode($hijo['label']), $hijo['url']) . '</li>';
            }
            $html .= '</ul>';
        }
        $html .= '</li>';
    }
    return '<ul class="huk-nav huk-nav-' . $variante . '">' . $html . '</ul>';
};

$logo = static function (string $clase) use ($logoUrl, $empresa): string {
    $contenido = $logoUrl
        ? Html::img($logoUrl, ['alt' => $empresa, 'class' => $clase])
        : Html::tag('span', Html::encode($empresa), ['class' => 'huk-logo-text']);
    return Html::a($contenido, Url::home(), ['class' => 'huk-brand', 'aria-label' => $empresa . ' - Inicio']);
};

$botonCuenta = Yii::$app->user->isGuest
    ? Html::a('Acceso inversores <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i>', ['cuenta/login'], ['class' => 'huk-hd-cta'])
    : Html::a('<i class="fa-solid fa-user me-1" aria-hidden="true"></i> Mi cuenta', ['cuenta/index'], ['class' => 'huk-hd-cta']);

$telefonoLink = 'tel:' . preg_replace('/[^0-9+]/', '', $c('telefono_empresa'));

// ── Idiomas con bandera (agregado) ──────────────────────────────────────────
// código GTranslate => [nombre, archivo en assets_b/img/flags/]
// Bandera de Bolivia para Español: cambiá 'es.png' por 'bo.png'.
// Es la ÚNICA lista de idiomas: gtranslate-config.js la lee de acá (atributos data-lang).
$idiomas = [
    'es'    => ['Español',   'es.png'],
    'en'    => ['English',   'gb.png'],
    'pt'    => ['Português', 'br.png'],
    'fr'    => ['Français',  'fr.png'],
    'de'    => ['Deutsch',   'de.png'],
    'it'    => ['Italiano',  'it.png'],
    'ja'    => ['日本語',     'jp.png'],
    'zh-CN' => ['中文',       'cn.png'],
];
$flagUrl = static fn (string $archivo): string => Url::to('@web/assets_b/img/flags/' . $archivo);
?>

<!-- 1. Barra superior -->
<div class="huk-hd-top">
    <div class="container">
        <p class="huk-hd-welcome mb-0">BIENVENIDO A <b><?= Html::encode($empresa) ?></b></p>
        <div class="huk-hd-top-right">
            <?php if ($c('horarios')): ?>
                <span class="huk-hd-hours d-none d-md-inline"><i class="fa-regular fa-clock me-2" aria-hidden="true"></i><?= Html::encode($c('horarios')) ?></span>
                <span class="huk-hd-slash d-none d-md-inline-block" aria-hidden="true"></span>
            <?php endif; ?>
            <?php if ($redes): ?>
                <div class="huk-hd-social">
                    <?php foreach ($redes as $icono => $url): ?>
                        <?= $red($icono, $url, 'huk-hd-social-link') ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if (!$esPanel): ?>
                <!-- Selector de idioma con banderas (agregado). Usa GTranslate por detrás. -->
                <div class="huk-lang-switch notranslate" data-lang-switch>
                    <button type="button" class="huk-lang-btn" aria-haspopup="listbox" aria-expanded="false" aria-label="Elegir idioma">
                        <img src="<?= $flagUrl($idiomas['es'][1]) ?>" alt="" width="20" height="15" data-lang-flag>
                        <span data-lang-name><?= Html::encode($idiomas['es'][0]) ?></span>
                        <i class="fa-solid fa-chevron-down huk-lang-caret" aria-hidden="true"></i>
                    </button>
                    <ul class="huk-lang-list" role="listbox" aria-label="Idiomas" hidden>
                        <?php foreach ($idiomas as $codigo => [$nombre, $bandera]): ?>
                            <li role="option" tabindex="-1" data-lang="<?= Html::encode($codigo) ?>" aria-selected="<?= $codigo === 'es' ? 'true' : 'false' ?>">
                                <img src="<?= $flagUrl($bandera) ?>" alt="" width="20" height="15">
                                <span><?= Html::encode($nombre) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="huk-lang gtranslate_wrapper"></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 2. Franja principal -->
<header class="huk-hd-main">
    <div class="container">
        <div class="huk-hd-brandrow">
            <?= $logo('huk-logo') ?>

            <div class="huk-hd-info">
                <?php if ($c('telefono_empresa')): ?>
                    <a class="huk-hd-info-item" href="<?= Html::encode($telefonoLink) ?>">
                        <i class="fa-solid fa-phone-volume" aria-hidden="true"></i>
                        <span><b>Teléfono</b><?= Html::encode($c('telefono_empresa')) ?></span>
                    </a>
                <?php endif; ?>
                <?php if ($c('direccion_empresa')): ?>
                    <div class="huk-hd-info-item">
                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                        <span><b>Dirección</b><?= $c('direccion_empresa') ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($c('email')): ?>
                    <a class="huk-hd-info-item" href="mailto:<?= Html::encode($c('email')) ?>">
                        <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                        <span><b>Email</b><?= Html::encode($c('email')) ?></span>
                    </a>
                <?php endif; ?>
            </div>

            <button class="huk-hd-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#hukMenuMovil" aria-controls="hukMenuMovil" aria-label="Abrir menú">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>
        </div>

        <nav class="huk-hd-navwrap" aria-label="Menú principal">
            <div class="huk-hd-nav">
                <?= $dibujarMenu('main') ?>
                <?= $botonCuenta ?>
            </div>
        </nav>
    </div>
</header>

<!-- 3. Header compacto (aparece al bajar) -->
<div class="huk-sticky" aria-hidden="true">
    <div class="container">
        <?= $logo('huk-logo-sm') ?>
        <nav class="huk-sticky-nav d-flex align-items-center" aria-label="Menú principal (fijo)">
            <?= $dibujarMenu('sticky') ?>
            <?= $botonCuenta ?>
        </nav>
        <button class="huk-hd-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#hukMenuMovil" aria-controls="hukMenuMovil" aria-label="Abrir menú" tabindex="-1">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>
    </div>
</div>

<!-- Menú en celular -->
<div class="offcanvas offcanvas-end huk-offcanvas" tabindex="-1" id="hukMenuMovil" aria-labelledby="hukMenuMovilTitulo">
    <div class="offcanvas-header">
        <span id="hukMenuMovilTitulo"><?= $logo('huk-logo-sm') ?></span>
        <button type="button" class="huk-offcanvas-close" data-bs-dismiss="offcanvas" aria-label="Cerrar menú"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
    </div>
    <div class="offcanvas-body">
        <?= $dibujarMenu('mobile') ?>
        <div class="mt-4"><?= $botonCuenta ?></div>
        <?php if ($c('telefono_empresa') || $c('email')): ?>
            <div class="huk-offcanvas-contact">
                <?php if ($c('telefono_empresa')): ?>
                    <a href="<?= Html::encode($telefonoLink) ?>"><i class="fa-solid fa-phone-volume me-2" aria-hidden="true"></i><?= Html::encode($c('telefono_empresa')) ?></a>
                <?php endif; ?>
                <?php if ($c('email')): ?>
                    <a href="mailto:<?= Html::encode($c('email')) ?>"><i class="fa-regular fa-envelope me-2" aria-hidden="true"></i><?= Html::encode($c('email')) ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

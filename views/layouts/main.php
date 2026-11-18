<?php

/**
 * Layout público HUK-THE-ICHI (sitio web + panel del inversor).
 *
 * @var \yii\web\View $this
 * @var string $content
 */

use app\assets_b\AppAsset;
use app\assets_b\GTranslateAsset;
use app\models\Configuracion;
use yii\helpers\Html;
use yii\helpers\Url;

AppAsset::register($this);

// Datos generales del sitio (tabla configuracion).
$cfg = Configuracion::find()->asArray()->one() ?? [];
$c = static fn (string $k): string => trim((string) ($cfg[$k] ?? ''));

$empresa = $c('nombre_empresa') ?: 'HUK-THE-ICHI GLOBAL GROUP';

// Logo y favicon: si el archivo no existe, no se pide (evita el 404).
$web = Yii::getAlias('@webroot');
$logoUrl = is_file($web . '/assets_b/img/logo.png') ? Url::to('@web/assets_b/img/logo.png') : null;
$faviconUrl = is_file($web . '/assets_b/img/favicon.png') ? Url::to('@web/assets_b/img/favicon.png') : null;

// Menú principal: editá rutas y textos acá.
$ruta = Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;
// En el panel del inversor no se carga el traductor (datos confidenciales).
$esPanel = Yii::$app->controller->module->id === 'cuenta';
if (!$esPanel) {
    GTranslateAsset::register($this); // traductor: solo en el sitio público
}
$menu = [
    ['label' => 'Inicio',      'url' => ['site/index']],
    ['label' => 'Nosotros',    'url' => ['site/nosotros'], 'items' => [
        ['label' => 'Quiénes somos', 'url' => ['site/nosotros']],
        ['label' => 'Equipo',        'url' => ['site/equipo']],
    ]],
    ['label' => 'Ecosistemas', 'url' => ['site/ecosistemas']],
    ['label' => 'Contacto',    'url' => ['site/contacto']],
];

// Redes: solo se muestran las que tienen URL cargada.
$redes = array_filter([
    'fa-facebook-f'  => $c('facebook'),
    'fa-instagram'   => $c('instagram'),
    'fa-linkedin-in' => $c('LinkedIn'),
    'fa-x-twitter'   => $c('twitter'),
    'fa-tiktok'     => $c('tiktok'),
]);
$red = static function (string $icono, string $url, string $clase): string {
    return Html::a(
        Html::tag('i', '', ['class' => "fa-brands {$icono}", 'aria-hidden' => 'true']),
        $url,
        ['class' => $clase, 'target' => '_blank', 'rel' => 'noopener noreferrer', 'aria-label' => $icono]
    );
};
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="no-js">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title ? $this->title . ' · ' . $empresa : $empresa) ?></title>
    <?php if ($c('meta_descripcion')): ?>
        <meta name="description" content="<?= Html::encode($c('meta_descripcion')) ?>">
    <?php endif; ?>
    <?php if ($faviconUrl): ?>
        <link rel="icon" type="image/png" href="<?= $faviconUrl ?>">
    <?php endif; ?>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<?= $this->render('_header', [
    'c' => $c, 'empresa' => $empresa, 'logoUrl' => $logoUrl, 'menu' => $menu,
    'ruta' => $ruta, 'redes' => $redes, 'red' => $red, 'esPanel' => $esPanel,
]) ?>

<main id="contenido">
    <?= $content ?>
</main>

<!-- Footer -->
<footer class="container-fluid footer bg-dark py-5">
    <div class="container py-5">
        <div class="row g-5">
            <div class="col-md-6 col-xl-4">
                <h3 class="text-white mb-4"><?= Html::encode($empresa) ?></h3>
                <p class="mb-3">Sembramos progreso, extraemos futuro.</p>
                <?php if ($redes): ?>
                    <div class="d-flex">
                        <?php foreach ($redes as $icono => $url): ?>
                            <?= $red($icono, $url, 'btn btn-light btn-md-square me-2') ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="footer-item d-flex flex-column">
                    <h4 class="text-white mb-4">Enlaces</h4>
                    <?php foreach ($menu as $item): ?>
                        <?php foreach ($item['items'] ?? [$item] as $link): ?>
                            <?= Html::a(Html::encode($link['label']), $link['url']) ?>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="footer-item d-flex flex-column">
                    <h4 class="text-white mb-4">Contacto</h4>
                    <?php if ($c('direccion_empresa')): ?>
                        <span><i class="fa-solid fa-location-dot text-primary me-2" aria-hidden="true"></i><?= Html::encode($c('direccion_empresa')) ?></span>
                    <?php endif; ?>
                    <?php if ($c('email')): ?>
                        <?= Html::mailto('<i class="fa-solid fa-envelope text-primary me-2" aria-hidden="true"></i>' . Html::encode($c('email')), $c('email')) ?>
                    <?php endif; ?>
                    <?php if ($c('telefono_empresa')): ?>
                        <?= Html::a(
                            '<i class="fa-solid fa-phone text-primary me-2" aria-hidden="true"></i>' . Html::encode($c('telefono_empresa')),
                            'tel:' . preg_replace('/[^0-9+]/', '', $c('telefono_empresa'))
                        ) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</footer>

<div class="container-fluid copyright py-4">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <span class="text-body">&copy; <?= date('Y') ?> <?= Html::encode($empresa) ?>. Todos los derechos reservados.</span>
            </div>
            <div class="col-md-6 text-center text-md-end text-body">
                <?php /* Licencia gratuita de HTML Codex: este crédito NO se puede quitar
                         salvo que se compre la licencia de remoción. */ ?>
                Designed By <a class="border-bottom text-white" href="https://htmlcodex.com" rel="noopener">HTML Codex</a>
            </div>
        </div>
    </div>
</div>

<a href="#" class="huk-back-top" aria-label="Volver arriba"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i></a>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

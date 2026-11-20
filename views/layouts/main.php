<?php

/**
 * Layout público HUK-THE-ICHI (sitio web + panel del inversor).
 *
 * @var \yii\web\View $this
 * @var string $content
 */

use app\assets_b\AppAsset;
use app\assets_b\GTranslateAsset;
use app\components\InterfaceSitio;
use app\models\PaginaSeccion;
use yii\helpers\Html;
use yii\helpers\Url;

AppAsset::register($this);

// Datos del header y el footer: los arma SitioService (configuración, pie, ecosistemas, imágenes).
$sitio = Yii::$container->get(InterfaceSitio::class)->layout();
$cfg = $sitio['config'];
$c = static fn (string $k): string => $cfg[$k] ?? '';

$empresa = $sitio['empresa'];
$nombreEmpresa = $sitio['nombreEmpresa']; // solo para el © del pie

// Texto y lemas del pie: Panel → Páginas → Pie de página (si el bloque está oculto, viene null).
$pie = $sitio['pie'];

// Logo, favicon y fénix: null si el archivo no existe (así no se pide y no da 404).
$logoUrl = $sitio['imagenes']['logo'];
$logoUrlf = $sitio['imagenes']['logoPie'];
$faviconUrl = $sitio['imagenes']['favicon'];
$fenixUrl = $sitio['imagenes']['fenix'];

// Ciudad y país: barra superior del header y footer. Editalo acá.
$ubicacion = 'Santa Cruz, Bolivia';

// Menú principal: editá rutas y textos acá.
$ruta = '/' . Yii::$app->controller->route; // absoluta: así el menú funciona igual dentro de /cuenta
// En el panel del inversor no se carga el traductor (datos confidenciales).
$esPanel = Yii::$app->controller->module->id === 'cuenta';
if (!$esPanel) {
    GTranslateAsset::register($this); // traductor: solo en el sitio público
}
// Ecosistemas activos del panel (por número): submenú del header y columna del footer.
$linksEcosistemas = array_map(static fn ($e) => ['label' => $e->nombre, 'url' => ['/ecosistemas/ver', 'slug' => $e->slug]], $sitio['ecosistemas']);

// Subpáginas de Nosotros (NosotrosController): mismo orden y nombres que PaginaSeccion::NOSOTROS_WEB.
$linksNosotros = [];
foreach (PaginaSeccion::NOSOTROS_WEB as $accion => [$nombre]) {
    $linksNosotros[] = ['label' => $nombre, 'url' => ['/nosotros/' . $accion]];
}

$menu = [
    ['label' => 'Inicio',      'url' => ['/site/index']],
    ['label' => 'Nosotros',    'url' => ['/nosotros/index'], 'items' => $linksNosotros],
    ['label' => 'Ecosistemas', 'url' => ['/ecosistemas/index'], 'items' => $linksEcosistemas, 'enFooter' => false],
    ['label' => 'Proyectos',   'url' => ['/proyectos/index']],
    ['label' => 'Blog',        'url' => ['/noticias/index']],
    ['label' => 'Contacto',    'url' => ['/site/contacto']],
];

// Redes: solo las que tienen URL cargada (las filtra el servicio).
$redes = $sitio['redes'];
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
    <?php $descripcion = $this->params['descripcion'] ?? $c('meta_descripcion'); // una página puede poner la suya (ej. una noticia) ?>
    <?php if ($descripcion): ?>
        <meta name="description" content="<?= Html::encode($descripcion) ?>">
    <?php endif; ?>
    <?php if ($faviconUrl): ?>
        <link rel="icon" type="image/png" href="<?= $faviconUrl ?>">
    <?php endif; ?>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<div class="huk-progress" aria-hidden="true"></div>

<?= $this->render('_header', [
    'c' => $c, 'empresa' => $empresa, 'ubicacion' => $ubicacion, 'logoUrl' => $logoUrl, 'menu' => $menu,
    'ruta' => $ruta, 'redes' => $redes, 'red' => $red, 'esPanel' => $esPanel,
]) ?>

<main id="contenido">
    <?= $content ?>
    <!-- Líneas de fondo con trazos que suben y bajan (CSS sección 20 · huk.js punto 13). Empiezan debajo del banner. -->
    <div class="huk-lineas" aria-hidden="true" data-lineas>
        <div class="container"><span></span><span></span><span></span><span></span><span></span><span></span></div>
    </div>
</main>

<!-- Footer (estructura de la plantilla, colores HUK) -->
<footer class="huk-ft">
    <div class="container">
        <div class="huk-ft-cols">
            <div data-reveal="left">
                <?= Html::a($logoUrlf ? Html::img($logoUrlf, ['alt' => $empresa]) : Html::tag('span', Html::encode($empresa), ['class' => 'huk-logo-text']), Url::home(), ['class' => 'huk-ft-logo', 'aria-label' => $empresa . ' - Inicio']) ?>
                <?php if ($pie && $pie->texto): ?>
                    <p class="huk-ft-texto"><?= nl2br(Html::encode($pie->texto)) ?></p>
                <?php endif; ?>
                <?php if ($pie && $pie->frase): ?>
                    <p class="huk-ft-lema"><?= Html::encode($pie->frase) ?></p>
                <?php endif; ?>
                <?php if ($redes): ?>
                    <div class="huk-ft-redes">
                        <?php foreach ($redes as $icono => $url): ?>
                            <?= $red($icono, $url, '') ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div data-reveal="left" data-reveal-delay="150">
                <h5>Información</h5>
                <?php if ($c('telefono_empresa')): ?>
                    <?= Html::a('<i class="fa-solid fa-phone" aria-hidden="true"></i><span>' . Html::encode($c('telefono_empresa')) . '</span>', 'tel:' . preg_replace('/[^0-9+]/', '', $c('telefono_empresa')), ['class' => 'huk-ft-dato']) ?>
                <?php endif; ?>
                <?php if ($c('email')): ?>
                    <?= Html::a('<i class="fa-solid fa-envelope" aria-hidden="true"></i><span>' . Html::encode($c('email')) . '</span>', 'mailto:' . $c('email'), ['class' => 'huk-ft-dato']) ?>
                <?php endif; ?>
                <p class="huk-ft-dato">
                    <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                    <span><?php if ($c('direccion_empresa')): ?><?= Html::encode($c('direccion_empresa')) ?><br><?php endif; ?><?= Html::encode($ubicacion) ?></span>
                </p>

            </div>

            <div data-reveal="right" data-reveal-delay="150">
                <h5>Ecosistemas</h5>
                <?php foreach ($linksEcosistemas as $link): ?>
                    <?= Html::a(Html::encode($link['label']), $link['url']) ?>
                <?php endforeach; ?>
            </div>

            <div data-reveal="right">
                <h5>Enlaces</h5>
                <?php foreach ($menu as $item): ?>
                    <?php if ($item['enFooter'] ?? true): ?>
                        <?= Html::a(Html::encode($item['label']), $item['url']) ?>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?= Html::a('Acceso inversores', ['/cuenta/acceso/ingresar']) ?>
            </div>
        </div>

        <?php if ($fenixUrl): ?>
            <div class="huk-ft-fenix" data-reveal="nada" data-reveal-delay="200"><img src="<?= $fenixUrl ?>" alt="Emblema HUK-THE-ICHI" width="110" height="132" loading="lazy"></div>
        <?php endif; ?>

        <div class="huk-ft-bot" data-reveal data-reveal-delay="300">
        <span class="huk-ft-bot-lema">&copy; <?= date('Y') ?>  <?= Html::encode($nombreEmpresa) ?></span>
            <?php /* Licencia gratuita de HTML Codex: este crédito NO se puede quitar
                     salvo que se compre la licencia de remoción. */ ?>
            <span>Desarrollado por <a href="#" rel="noopener" target="_blank">Zebra Brand Consulting</a></span>
        </div>
    </div>
</footer>

<a href="#" class="huk-back-top" aria-label="Volver arriba"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i></a>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

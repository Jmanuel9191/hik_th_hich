<?php

/**
 * Layout del panel admin (tema HUK).
 * Estilos y scripts: app\assets_b\AdminPanelAsset (AdminLTE + huk-admin.css + SweetAlert2 local + huk-admin.js).
 * Mensajes flash: setFlash('flash', ['type' => 'success', 'message' => '...']) → <body data-flash> → huk-admin.js.
 *
 * @var \yii\web\View $this
 * @var string $content
 */

use yii\helpers\Html;
use yii\helpers\Url;

\app\assets_b\AdminPanelAsset::register($this);

$usuario = Yii::$app->user->identity;
$idrol = $usuario->idrol ?? null;

$logoUrl = Url::to('@web/assets_b/img/logo.png');
$faviconUrl = is_file(Yii::getAlias('@webroot/assets_b/img/favicon.png'))
    ? Url::to('@web/assets_b/img/favicon.png')
    : $logoUrl;

$flash = Yii::$app->session->getFlash('flash');
$bodyOptions = ['class' => 'hold-transition skin-red sidebar-mini'];
if (is_array($flash) && !empty($flash['message'])) {
    $bodyOptions['data-flash'] = ['type' => $flash['type'] ?? 'info', 'message' => (string) $flash['message']];
}

/* ---------- Menú lateral ---------- */
// Solo los módulos en uso. Los demás están apagados en Module::CONTROLADORES_ACTIVOS.
$permisos = Yii::$container->get(\app\components\InterfacePermisosService::class);
$esSuperadmin = (int) $idrol === \app\components\Rol::SUPERADMIN;
$ctrl = Yii::$app->controller->id;

$items = [
    ['label' => 'INICIO', 'url' => ['/admin/default/index'], 'icon' => 'home'],
];

// Ecosistemas: Superadmin, Editor y Gerente (según permisos).
if ($usuario && $permisos->tienePermiso('ecosistemas_listar')) {
    $items[] = ['label' => 'ECOSISTEMAS', 'url' => ['/admin/ecosistemas/index'], 'icon' => 'th-large', 'active' => $ctrl === 'ecosistemas'];
}

if ($usuario && $permisos->tienePermiso('banners_listar')) {
    $items[] = ['label' => 'BANNERS', 'url' => ['/admin/banners/index'], 'icon' => 'picture-o', 'active' => $ctrl === 'banners'];
}
if ($usuario && $permisos->tienePermiso('equipo_listar')) {
    $items[] = ['label' => 'EQUIPO', 'url' => ['/admin/equipo/index'], 'icon' => 'id-badge', 'active' => $ctrl === 'equipo'];
}
if ($esSuperadmin) {
    $items[] = ['label' => 'USUARIOS', 'url' => ['/admin/usuarios/index'], 'icon' => 'users', 'active' => $ctrl === 'usuarios' && Yii::$app->controller->action->id !== 'cuenta'];
}

$configuracion = [];
if ($esSuperadmin) {
    $configuracion[] = ['label' => 'ROLES Y PERMISOS', 'url' => ['/admin/roles/index'], 'icon' => 'key', 'active' => $ctrl === 'roles'];
}
if ($usuario && $permisos->tienePermiso('configuracion_editar')) {
    $configuracion[] = ['label' => 'DATOS GENERALES', 'url' => ['/admin/datos-generales/index'], 'icon' => 'globe', 'active' => $ctrl === 'datos-generales'];
}
if ($configuracion) {
    $items[] = ['label' => 'CONFIGURACIÓN', 'icon' => 'cogs', 'url' => '#', 'options' => ['class' => 'treeview'], 'items' => $configuracion];
}
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title ? $this->title . ' · Panel HUK' : 'Panel HUK') ?></title>
    <link rel="icon" type="image/png" href="<?= Html::encode($faviconUrl) ?>">
    <?php $this->head() ?>
</head>
<?= Html::beginTag('body', $bodyOptions) ?>
<?php $this->beginBody() ?>

<div class="wrapper">

    <!-- Header -->
    <header class="main-header">
        <?= Html::a(
            Html::img($logoUrl, ['alt' => 'HUK-THE-ICHI GLOBAL GROUP']),
            ['/admin/default/index'],
            ['class' => 'logo']
        ) ?>

        <nav class="navbar navbar-static-top" role="navigation">
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Mostrar / ocultar menú</span>
            </a>

            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <span class="hidden-xs"><?= Html::encode($usuario->usuario ?? '') ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="user-footer">
                                <?= Html::a('<i class="fa fa-user" aria-hidden="true"></i> Ver perfil', ['/admin/usuarios/cuenta'], [
                                    'class' => 'btn btn-default btn-flat',
                                    'data-method' => 'post',
                                ]) ?>
                                <?= Html::a('<i class="fa fa-sign-out" aria-hidden="true"></i> Salir', ['/admin/default/logout'], [
                                    'class' => 'btn btn-default btn-flat',
                                    'data-method' => 'post',
                                ]) ?>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Menú lateral -->
    <aside class="main-sidebar">
        <section class="sidebar">
            <?= \dmstr\widgets\Menu::widget([
                'items'   => $items,
                'options' => [
                    'class'       => 'sidebar-menu tree',
                    'data-widget' => 'tree',
                ],
            ]) ?>
        </section>

        <div class="foents">
            <p>&copy; <?= date('Y') ?> HUK-THE-ICHI GLOBAL GROUP</p>
        </div>
    </aside>

    <!-- Contenido -->
    <div class="content-wrapper">
        <?= $content ?>
    </div>

</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

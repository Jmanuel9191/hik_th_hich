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
// Blog: noticias, nueva entrada y categorías (según permisos)
if ($usuario && $permisos->tienePermiso('noticias_listar')) {
    $accion = Yii::$app->controller->action->id ?? '';
    $blog = [['label' => 'NOTICIAS', 'url' => ['/admin/noticias/index'], 'icon' => 'newspaper-o', 'active' => $ctrl === 'noticias' && $accion !== 'create']];
    if ($permisos->tienePermiso('noticias_crear')) {
        $blog[] = ['label' => 'NUEVA ENTRADA', 'url' => ['/admin/noticias/create'], 'icon' => 'plus', 'active' => $ctrl === 'noticias' && $accion === 'create'];
    }
    if ($permisos->tienePermiso('noticias_categorias')) {
        $blog[] = ['label' => 'CATEGORÍAS', 'url' => ['/admin/noticias-categorias/index'], 'icon' => 'tags', 'active' => $ctrl === 'noticias-categorias'];
    }
    $items[] = ['label' => 'BLOG', 'icon' => 'newspaper-o', 'url' => '#', 'options' => ['class' => 'treeview'], 'items' => $blog];
}
if ($usuario && $permisos->tienePermiso('equipo_listar')) {
    $items[] = ['label' => 'EQUIPO', 'url' => ['/admin/equipo/index'], 'icon' => 'id-badge', 'active' => $ctrl === 'equipo'];
}
if ($usuario && $permisos->tienePermiso('inversores_listar')) {
    $items[] = ['label' => 'INVERSORES', 'url' => ['/admin/inversores/index'], 'icon' => 'handshake-o', 'active' => $ctrl === 'inversores'];
}
if ($usuario && $permisos->tienePermiso('inversiones_listar')) {
    $items[] = ['label' => 'INVERSIONES', 'url' => ['/admin/inversiones/index'], 'icon' => 'money', 'active' => $ctrl === 'inversiones'];
}
// Proyectos: banco de proyectos y, si tiene el permiso, las plantillas de la página web
if ($usuario && $permisos->tienePermiso('plantillas_editar')) {
    $proyectos = [];
    if ($permisos->tienePermiso('proyectos_listar')) {
        $proyectos[] = ['label' => 'BANCO DE PROYECTOS', 'url' => ['/admin/banco-proyectos/index'], 'icon' => 'briefcase', 'active' => $ctrl === 'banco-proyectos'];
    }
    $proyectos[] = ['label' => 'PLANTILLAS', 'url' => ['/admin/plantillas-proyecto/index'], 'icon' => 'clone', 'active' => $ctrl === 'plantillas-proyecto'];
    $items[] = ['label' => 'PROYECTOS', 'icon' => 'briefcase', 'url' => '#', 'options' => ['class' => 'treeview'], 'items' => $proyectos];
} elseif ($usuario && $permisos->tienePermiso('proyectos_listar')) {
    $items[] = ['label' => 'PROYECTOS', 'url' => ['/admin/banco-proyectos/index'], 'icon' => 'briefcase', 'active' => $ctrl === 'banco-proyectos'];
}
// Páginas institucionales: un ítem por página de app\models\PaginaSeccion::PAGINAS, en el orden del submenú Nosotros de la web.
// Las de "enlace" (Liderazgo) llevan a su módulo, si el usuario tiene el permiso.
if ($usuario && $permisos->tienePermiso('paginas_ver')) {
    $paginaActual = $ctrl === 'paginas' ? ($this->params['pagina'] ?? null) : null; // lo ponen las vistas de paginas/
    $paginas = [];
    foreach (\app\models\PaginaSeccion::PAGINAS as $idPagina => $p) {
        if (isset($p['enlace'])) {
            if ($permisos->tienePermiso($p['permiso'])) {
                $paginas[] = ['label' => mb_strtoupper($p['nombre']), 'url' => $p['enlace'], 'icon' => $p['icono'], 'active' => false];
            }
            continue;
        }
        $paginas[] = ['label' => mb_strtoupper($p['nombre']), 'url' => ['/admin/paginas/index', 'pagina' => $idPagina], 'icon' => $p['icono'], 'active' => $paginaActual === $idPagina];
    }
    $items[] = ['label' => 'PÁGINAS', 'icon' => 'files-o', 'url' => '#', 'options' => ['class' => 'treeview'], 'items' => $paginas];
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
if ($usuario && ($permisos->tienePermiso('nda_editar') || $permisos->tienePermiso('inversores_aprobar'))) {
    $configuracion[] = ['label' => 'ACUERDO DE CONFIDENCIALIDAD', 'url' => ['/admin/nda/index'], 'icon' => 'file-text-o', 'active' => $ctrl === 'nda'];
}
if ($usuario && ($permisos->tienePermiso('terminos_editar') || $permisos->tienePermiso('inversores_aprobar'))) {
    $configuracion[] = ['label' => 'TÉRMINOS Y PRIVACIDAD', 'url' => ['/admin/terminos/index'], 'icon' => 'balance-scale', 'active' => $ctrl === 'terminos'];
}
if ($usuario && $permisos->tienePermiso('monedas_editar')) {
    $configuracion[] = ['label' => 'MONEDAS', 'url' => ['/admin/monedas/index'], 'icon' => 'money', 'active' => $ctrl === 'monedas'];
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

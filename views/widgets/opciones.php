<?php
use yii\helpers\Url;

$user = Yii::$app->user->identity;

$catalogosSubitems = [
    ['label' => 'Sucursales', 'url' => ['/catalogos/sucursal-listar'], 'options' => ['class' => 'nav-item']],
    ['label' => 'Sectores', 'url' => ['/catalogos/sector-listar'], 'options' => ['class' => 'nav-item']],
    ['label' => 'Categorías de Servicio', 'url' => ['/catalogos/categoria-servicio-listar'], 'options' => ['class' => 'nav-item']],
    ['label' => 'Audiencias', 'url' => ['/catalogos/audiencia-listar'], 'options' => ['class' => 'nav-item']],
    ['label' => 'Canales de Marketing', 'url' => ['/catalogos/canal-marketing-listar'], 'options' => ['class' => 'nav-item']],
    ['label' => 'Tipo de Tarea', 'url' => ['/catalogos/tipo-tarea-listar'], 'options' => ['class' => 'nav-item']],
    ['label' => 'Roles', 'url' => ['/catalogos/rol-listar'], 'options' => ['class' => 'nav-item']],
];

if ($user->idrol == '1') {
    echo $this->render('../widgets/menu', [
        'lista' => [
            'items' => [
                ['label' => 'Inicio', 'url' => ['/administrador/index'], 'options' => ['class' => 'nav-item']],
                ['label' => 'Mi cuenta', 'url' => ['/administrador/cuenta'], 'options' => ['class' => 'nav-item']],
                ['label' => 'Tareas', 'url' => ['/tareas/index'], 'options' => ['class' => 'nav-item']],
                ['label' => 'Proyectos', 'url' => ['/proyectos/index'], 'options' => ['class' => 'nav-item']],
                ['label' => 'Campañas', 'url' => ['/campanias/index'], 'options' => ['class' => 'nav-item']],
                ['label' => 'Calendario', 'url' => ['/calendar/listgeneral'], 'options' => ['class' => 'nav-item']],
                ['label' => 'Usuarios', 'url' => ['/administrador/usuarioslist'], 'options' => ['class' => 'nav-item']],

                [
                    'label' => 'Catálogos',
                    'url' => '#',
                    'items' => $catalogosSubitems,
                    'options' => ['class' => 'nav-item has-submenu'],
                    //'submenuTemplate' => "\n<ul class=\"submenu-list list-unstyled collapse submenu\" id=\"submenu-catalogos\" data-bs-parent=\"#menu-accordion\">\n{items}\n</ul>\n",
                   // 'linkTemplate' => '<a class="nav-link submenu-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#submenu-catalogos" aria-expanded="false" aria-controls="submenu-catalogos">{label} <span class="submenu-arrow"></span></a>',
                ],
            ],
        ],
        'tipousuario' => 'administrador',
    ]);
}elseif ($user->idrol == '2') {
    echo $this->render('../widgets/menu', [
        'lista' => [
            'items' => [
                ['label' => 'Inicio', 'url' => ['/gestor/index'], 'options' => ['class' => 'nav-item']],
                ['label' => 'Mi cuenta', 'url' => ['/gestor/cuenta'], 'options' => ['class' => 'nav-item']],
                ['label' => 'Tareas', 'url' => ['/tareas/index'], 'options' => ['class' => 'nav-item']],
                ['label' => 'Proyectos', 'url' => ['/proyectos/index'], 'options' => ['class' => 'nav-item']],
                ['label' => 'Campañas', 'url' => ['/campanias/index'], 'options' => ['class' => 'nav-item']],
                ['label' => 'Calendario', 'url' => ['/calendar/listgeneral'], 'options' => ['class' => 'nav-item']],
               // ['label' => 'Catálogos', 'url' => '#', 'items' => $catalogosSubitems, 'options' => ['class' => 'nav-item']],
            ],
        ],
        'tipousuario' => 'gestor',
    ]);
} elseif ($user->idrol == '3') {
    echo $this->render('../widgets/menu', [
        'lista' => [
            'items' => [
                ['label' => 'Inicio', 'url' => ['/consultor/index'], 'options' => ['class' => 'nav-item']],
                ['label' => 'Mi cuenta', 'url' => ['/consultor/cuenta'], 'options' => ['class' => 'nav-item']],
                ['label' => 'Proyectos', 'url' => ['/proyectos/misproyectos'], 'options' => ['class' => 'nav-item']],
                ['label' => 'Campañas', 'url' => ['/campanias/miscampanias'], 'options' => ['class' => 'nav-item']],
                ['label' => 'Calendario', 'url' => ['/calendar/micalendario'], 'options' => ['class' => 'nav-item']],
            ],
        ],
        'tipousuario' => 'consultor',
    ]);
} elseif ($user->idrol == '4') {
    echo $this->render('../widgets/menu', [
        'lista' => [
            'items' => [
                ['label' => 'Inicio', 'url' => ['/cliente/index'], 'options' => ['class' => 'nav-item']],
                ['label' => 'Mi cuenta', 'url' => ['/cliente/cuenta'], 'options' => ['class' => 'nav-item']],
                ['label' => 'Proyectos', 'url' => ['/proyectos/misproyectos'], 'options' => ['class' => 'nav-item']],
                ['label' => 'Campañas', 'url' => ['/campanias/miscampanias'], 'options' => ['class' => 'nav-item']],
                ['label' => 'Calendario', 'url' => ['/calendar/micalendario'], 'options' => ['class' => 'nav-item']],
            ],
        ],
        'tipousuario' => 'cliente',
    ]);
}
?>

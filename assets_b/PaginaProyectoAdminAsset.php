<?php

declare(strict_types=1);

namespace app\assets_b;

use yii\web\AssetBundle;

/**
 * Editor de la página web de los proyectos y de las plantillas (panel): modal de formularios, catálogo de bloques,
 * filas repetibles, fotos con vista previa y editor de texto simple (huk-pagina-proyecto.js).
 * Lo registra modules/admin/views/paginas-proyecto/_editor.php. Estilos: huk-admin.css sección 24.
 */
class PaginaProyectoAdminAsset extends AssetBundle
{
    public $basePath = '@webroot/assets_b';
    public $baseUrl = '@web/assets_b';

    public $js = [
        'js/huk-pagina-proyecto.js',
    ];

    public $depends = [
        'app\assets_b\AdminPanelAsset',
        'app\assets_b\TinyMceAsset',
    ];
}

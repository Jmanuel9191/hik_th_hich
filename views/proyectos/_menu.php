<?php

/**
 * Menú de anclas: queda fijo arriba al bajar (debajo del header compacto), baja suave hasta cada sección
 * y subraya en dorado la sección en la que estás. En el celular se desliza de costado.
 * Lo arma solo ProyectoWebService con las secciones que tienen "mostrar en el menú". JS: huk-proyecto.js.
 *
 * @var yii\web\View $this
 * @var array $menu ancla => nombre
 */

use yii\helpers\Html;
?>
<nav class="huk-pro-menu" aria-label="Secciones del proyecto" data-pro-menu>
    <div class="container">
        <ul class="huk-pro-menu-lista">
            <?php foreach ($menu as $ancla => $nombre): ?>
                <li><?= Html::a(Html::encode($nombre), '#' . $ancla, ['data-pro-ancla' => $ancla]) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>

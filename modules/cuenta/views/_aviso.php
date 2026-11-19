<?php

/**
 * Aviso de una sola vez (flash "cuenta": [tipo, mensaje]). tipo: success | danger | info.
 *
 * @var yii\web\View $this
 */

use yii\helpers\Html;

$flash = Yii::$app->session->getFlash('cuenta');
if ($flash) {
    [$tipo, $texto] = $flash;
    $icono = ['success' => 'circle-check', 'danger' => 'circle-exclamation', 'info' => 'circle-info'][$tipo] ?? 'circle-info';
    echo Html::tag('div', '<i class="fa-solid fa-' . $icono . ' mt-1" aria-hidden="true"></i><span>' . Html::encode($texto) . '</span>',
        ['class' => 'huk-aviso huk-aviso-' . Html::encode($tipo), 'role' => 'alert']);
}

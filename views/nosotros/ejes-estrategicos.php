<?php

/**
 * Nosotros → Ejes estratégicos: ejes de crecimiento (Ecosistemas destacados), modelo de creación de valor,
 * visión de expansión con sus fases y el prefooter. Lo que esté oculto o vacío en el panel no sale.
 *
 * @var yii\web\View $this
 * @var string $titulo
 * @var app\models\Banners|null $encabezado
 * @var app\models\Ecosistema[] $ecosistemas  destacados, por número
 * @var app\models\PaginaSeccion|null $ejes
 * @var app\models\PaginaSeccion|null $modelo
 * @var app\models\PaginaSeccion|null $propuesta
 * @var app\models\PaginaSeccion|null $expansion
 * @var app\models\PaginaSeccion|null $fases
 */

use app\widgets\Prefooter;
?>
<?= $this->render('_encabezado', ['encabezado' => $encabezado, 'titulo' => $titulo]) ?>

<?= $this->render('_ejes', ['ejes' => $ejes, 'ecosistemas' => $ecosistemas]) ?>

<?= $this->render('//site/widgets/modelo_valor', ['modelo' => $modelo, 'propuesta' => $propuesta]) ?>

<?= $this->render('_expansion', ['expansion' => $expansion, 'fases' => $fases]) ?>

<?= Prefooter::widget() ?>

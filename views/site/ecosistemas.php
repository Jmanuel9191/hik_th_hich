<?php

/**
 * Ecosistemas: encabezado (Panel → Banners → Ecosistemas), los ecosistemas activos de a 4 por número
 * (Panel → Ecosistemas), el Modelo de creación de valor, el llamado a ver los proyectos y el prefooter.
 *
 * @var yii\web\View $this
 * @var app\models\Ecosistema[] $ecosistemas
 * @var app\models\Banners|null $encabezado
 * @var app\models\PaginaSeccion|null $modelo
 * @var app\models\PaginaSeccion|null $propuesta
 */

use app\widgets\LlamadoProyectos;
use app\widgets\Prefooter;

$this->title = 'Ecosistemas';
$efectos = ['left', '', '', 'right'];
?>

<?= $this->render('widgets/encabezado', [
    'banner' => $encabezado,
    'titulo' => 'Ecosistemas',
    'migas' => [['label' => 'Inicio', 'url' => ['/site/index']], ['label' => 'Ecosistemas']],
]) ?>

<?php if ($ecosistemas): ?>
    <section class="huk-ecos" aria-label="Ecosistemas empresariales">
        <div class="container">
            <div class="huk-ecos-grid">
                <?php foreach ($ecosistemas as $i => $e): ?>
                    <?= $this->render('widgets/ecosistema_tarjeta', ['ecosistema' => $e, 'efecto' => $efectos[$i % 4], 'retraso' => ($i % 4) * 110]) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?= $this->render('widgets/modelo_valor', ['modelo' => $modelo, 'propuesta' => $propuesta]) ?>

<?= LlamadoProyectos::widget() ?>

<?= Prefooter::widget() ?>

<?php

/**
 * Nosotros: encabezado + todos los bloques de Panel → Páginas → Nosotros + Liderazgo (Panel → Equipo).
 * Cada parte se omite sola si el bloque está oculto o vacío. Estilos: huk.css secciones 19 (Quiénes somos),
 * 18 (tarjetas), 21 (encabezado) y 22 (Nosotros).
 *
 * @var yii\web\View $this
 * @var app\models\Banners|null $encabezado  Panel → Banners → Nosotros (el primero vigente)
 * @var array $nosotros  PaginasService::nosotrosWeb()
 * @var app\models\Equipo[] $equipo
 */

$this->title = 'Nosotros';
$n = $nosotros;
?>

<?= $this->render('widgets/encabezado', [
    'banner' => $encabezado,
    'titulo' => 'Nosotros',
    'migas' => [['label' => 'Inicio', 'url' => ['/site/index']], ['label' => 'Nosotros']],
]) ?>

<?= $this->render('widgets/quienes', $n['quienes'] + ['conBoton' => false]) ?>

<?= $this->render('widgets/tarjetas', ['tarjetas' => $n['tarjetas'], 'conBanner' => false]) ?>

<?= $this->render('nosotros/_bloques', ['bloques' => $n['bloques']]) ?>

<?= $this->render('nosotros/_pvm', ['proposito' => $n['proposito'], 'vision' => $n['vision'], 'mision' => $n['mision']]) ?>

<?= $this->render('nosotros/_valores', ['valores' => $n['valores'], 'principios' => $n['principios']]) ?>

<?= $this->render('nosotros/_ceo', ['ceo' => $n['ceo']]) ?>

<?= $this->render('nosotros/_equipo', ['equipo' => $equipo]) ?>

<?= $this->render('nosotros/_faq', ['faq' => $n['faq']]) ?>

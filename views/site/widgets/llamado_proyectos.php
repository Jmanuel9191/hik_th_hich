<?php

/**
 * Llamado a ver los proyectos (lo dibuja app\widgets\LlamadoProyectos). Franja negra con brillo dorado:
 * frase a la izquierda y botones a la derecha. Efectos: el texto entra de la izquierda, los botones de la derecha;
 * el fondo tiene un brillo dorado que se mueve despacio.
 *
 * @var yii\web\View $this
 * @var bool $conSesion  hay un inversor con la sesión abierta
 * @var string|null $nombre
 */

use yii\helpers\Html;

$primerNombre = $nombre ? strtok(trim($nombre), ' ') : '';
?>
<section class="huk-llamado" aria-label="Proyectos de inversión">
    <div class="container huk-llamado-grid">
        <div class="huk-llamado-txt" data-reveal="left">
            <p class="huk-nos-etiqueta">Proyectos de inversión</p>
            <?php if ($conSesion): ?>
                <h2><?= $primerNombre !== '' ? Html::encode($primerNombre) . ', sus' : 'Sus' ?> proyectos le esperan</h2>
                <p>Ingrese a su cuenta para ver los proyectos de sus áreas, sus documentos y cómo avanzan.</p>
            <?php else: ?>
                <h2>Regístrese y conozca los proyectos de cada ecosistema</h2>
                <p>Como inversor, usted accede a los proyectos, sus documentos y sus avances. El registro es gratis y lo revisa nuestro equipo.</p>
            <?php endif; ?>
        </div>
        <div class="huk-llamado-botones" data-reveal="right" data-reveal-delay="150">
            <?php if ($conSesion): ?>
                <?= Html::a('Ver proyectos <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>', ['/proyectos/index'], ['class' => 'btn btn-primary huk-llamado-btn huk-shine']) ?>
            <?php else: ?>
                <?= Html::a('Registrarme <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>', ['/cuenta/acceso/registro'], ['class' => 'btn btn-primary huk-llamado-btn huk-shine']) ?>
                <?= Html::a('Ya tengo cuenta', ['/cuenta/acceso/ingresar'], ['class' => 'huk-llamado-link']) ?>
            <?php endif; ?>
        </div>
    </div>
</section>

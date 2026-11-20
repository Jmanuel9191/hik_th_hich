<?php

/**
 * Contacto: encabezado (Panel → Banners → Contacto), a la izquierda los datos de contacto (Panel → Configuración →
 * Datos generales: teléfono, WhatsApp, correo, dirección, horarios y redes) y a la derecha el formulario; abajo el mapa
 * a todo el ancho (Datos generales → Mapa de Google). Lo que esté vacío no sale. Estilos: huk.css sección 24.
 * Efectos: los datos entran de la izquierda de a uno, el formulario de la derecha y el mapa con zoom
 * (en blanco y negro; toma color al pasar el mouse).
 *
 * @var yii\web\View $this
 * @var app\models\ContactoForm $form
 * @var app\models\Banners|null $encabezado
 * @var array $sitio  ConfiguracionService::paraWeb(): ['datos' => columna => texto, 'redes' => ícono => URL]
 * @var string|null $recaptcha  clave pública de Google reCAPTCHA (null: sin el recuadro)
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Contacto';
$d = $sitio['datos'];
$c = static fn (string $k): string => $d[$k] ?? '';
$soloNumeros = static fn (string $t): string => preg_replace('/[^0-9+]/', '', $t);

// Datos de la izquierda: [ícono, título, valor (HTML), enlace]
$datos = [];
if ($c('telefono_empresa') !== '') {
    $datos[] = ['fa-phone', 'Teléfono', Html::encode($c('telefono_empresa')), 'tel:' . $soloNumeros($c('telefono_empresa'))];
}
if ($c('fax_empresa') !== '') { // la columna fax_empresa guarda el WhatsApp
    $datos[] = ['fa-brands fa-whatsapp', 'WhatsApp', 'Escríbanos por WhatsApp', 'https://wa.me/' . $soloNumeros($c('fax_empresa'))];
}
if ($c('telefonos_otros') !== '') {
    $datos[] = ['fa-phone-volume', 'Otros teléfonos', nl2br(Html::encode($c('telefonos_otros'))), null];
}
if ($c('email') !== '') {
    $datos[] = ['fa-envelope', 'Correo', Html::encode($c('email')), 'mailto:' . $c('email')];
}
if ($c('direccion_empresa') !== '') {
    $datos[] = ['fa-location-dot', 'Dirección', Html::encode($c('direccion_empresa')), null];
}
if ($c('horarios') !== '') {
    $datos[] = ['fa-clock', 'Horarios de atención', nl2br(Html::encode($c('horarios'))), null];
}
$campo = ['options' => ['class' => 'huk-contacto-campo'], 'labelOptions' => ['class' => 'visually-hidden'],
    'errorOptions' => ['class' => 'invalid-feedback d-block']];
$entrada = static fn (string $placeholder, array $extra = []): array => array_merge(['class' => 'form-control', 'placeholder' => $placeholder], $extra);

if ($recaptcha) {
    $this->registerJsFile('https://www.google.com/recaptcha/api.js?hl=es', ['async' => true, 'defer' => true]);
}
?>

<?= $this->render('widgets/encabezado', [
    'banner' => $encabezado,
    'titulo' => 'Contacto',
    'migas' => [['label' => 'Inicio', 'url' => ['/site/index']], ['label' => 'Contacto']],
]) ?>

<section class="huk-contacto" aria-label="Contacto">
    <div class="container huk-contacto-grid">
        <div class="huk-contacto-info">
            <header data-reveal="left">
                <p class="huk-nos-etiqueta">Estamos para ayudarle</p>
                <h2 class="huk-nos-titulo">Información de contacto</h2>
            </header>
            <?php if ($datos): ?>
                <ul class="huk-contacto-datos">
                    <?php foreach ($datos as $n => [$icono, $titulo, $valor, $enlace]): ?>
                        <li data-reveal="left" data-reveal-delay="<?= 120 + $n * 90 ?>">
                            <span class="huk-contacto-icono" aria-hidden="true"><i class="<?= str_starts_with($icono, 'fa-brands') ? $icono : 'fa-solid ' . $icono ?>"></i></span>
                            <div>
                                <small><?= Html::encode($titulo) ?></small>
                                <?= $enlace
                                    ? Html::a($valor, $enlace, str_starts_with($enlace, 'http') ? ['target' => '_blank', 'rel' => 'noopener noreferrer'] : [])
                                    : Html::tag('span', $valor) ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <?php if ($sitio['redes']): ?>
                <div class="huk-contacto-redes" data-reveal="zoom" data-reveal-delay="200">
                    <small>Síganos</small>
                    <div>
                        <?php foreach ($sitio['redes'] as $icono => $url): ?>
                            <?= Html::a(Html::tag('i', '', ['class' => 'fa-brands ' . $icono, 'aria-hidden' => 'true']), $url, [
                                'target' => '_blank', 'rel' => 'noopener noreferrer', 'aria-label' => $icono,
                            ]) ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="huk-contacto-form" id="formulario" data-reveal="right" data-reveal-delay="150">
            <h2>Formulario de contacto</h2>
            <p class="huk-contacto-sub">Escríbanos su consulta y le responderemos a la brevedad.</p>

            <?php foreach (['contacto-ok' => 'is-ok', 'contacto-error' => 'is-error'] as $flash => $clase): ?>
                <?php if (Yii::$app->session->hasFlash($flash)): ?>
                    <div class="huk-contacto-aviso <?= $clase ?>" role="status">
                        <i class="fa-solid <?= $clase === 'is-ok' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>" aria-hidden="true"></i>
                        <span><?= Html::encode(Yii::$app->session->getFlash($flash)) ?></span>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php $f = ActiveForm::begin(['id' => 'contacto-form', 'action' => ['contacto', '#' => 'formulario'], 'fieldConfig' => $campo]); ?>
            <div class="huk-contacto-fila">
                <?= $f->field($form, 'nombre')->textInput($entrada('Nombre completo *', ['autocomplete' => 'name', 'maxlength' => 150])) ?>
                <?= $f->field($form, 'empresa')->textInput($entrada('Empresa', ['autocomplete' => 'organization', 'maxlength' => 150])) ?>
            </div>
            <div class="huk-contacto-fila">
                <?= $f->field($form, 'telefono')->textInput($entrada('Teléfono', ['type' => 'tel', 'autocomplete' => 'tel', 'maxlength' => 40])) ?>
                <?= $f->field($form, 'email')->textInput($entrada('Correo electrónico *', ['type' => 'email', 'autocomplete' => 'email', 'maxlength' => 150])) ?>
            </div>
            <?= $f->field($form, 'mensaje')->textarea($entrada('Su consulta *', ['rows' => 6, 'maxlength' => 3000])) ?>
            <?php /* Trampa para robots: oculto para las personas (huk.css sección 24) */ ?>
            <div class="huk-contacto-trampa" aria-hidden="true">
                <?= Html::activeTextInput($form, 'sitio', ['tabindex' => '-1', 'autocomplete' => 'off']) ?>
            </div>
            <?php if ($recaptcha): ?>
                <div class="g-recaptcha huk-contacto-captcha" data-sitekey="<?= Html::encode($recaptcha) ?>"></div>
            <?php endif; ?>
            <?= Html::submitButton('Enviar mensaje <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>', ['class' => 'btn btn-primary huk-contacto-btn huk-shine']) ?>
            <p class="huk-contacto-nota">* Campos obligatorios.</p>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</section>

<?php if ($c('mapa_embed') !== ''): ?>
    <section class="huk-contacto-mapa" aria-label="Mapa" data-reveal="zoom">
        <iframe src="<?= Html::encode($c('mapa_embed')) ?>" title="Cómo llegar" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
    </section>
<?php endif; ?>

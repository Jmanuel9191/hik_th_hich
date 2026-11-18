<?php
use yii\helpers\Html;

/** @var $model app\models\HistoriaRecursos */

// Usuario actual logueado
$usuarioActual = Yii::$app->user->identity;
$esMio = $model->idusuario == $usuarioActual->idusuario;
$claseMensaje = $esMio ? 'chat-message-right' : 'chat-message-left';
$nombre = $model->usuario ? Html::encode($model->usuario->nombrecompleto) : 'Usuario';
$zonaHoraria = Yii::$app->user->identity->zona_horaria ?? 'America/La_Paz';
// Crear objeto de fecha sin asumir UTC (porque ya está guardada en hora local)
$dt = new DateTime($model->fecha_registro);
$dt->setTimezone(new DateTimeZone($zonaHoraria));
$hora = $dt->format('d/m/Y H:i'); // Formato 24 horas
// Archivo adjunto
$archivo = $model->archivo;
$rutaArchivo = Yii::getAlias('@web') . '/' . ltrim($archivo, '/');

?>




<div class="<?= $claseMensaje ?> mb-4 nuevo-mensaje">
    <div class="chat-bubble">
        <div class="font-weight-bold mb-1 text-right"><?= $esMio ? 'Tú' : $nombre ?></div>
        <div class="text-muted small text-nowrap mt-1 text-right">Enviado el: <?= $hora ?></div>

              <?php if ($model->comentario): ?>
            <p class="mb-1">
                <?php if ($model->tipomensaje == 2): ?>
                    <strong class=" apenfap"> Recurso aprobado:</strong>
                <?php elseif ($model->tipomensaje == 3): ?>
                    <strong class="rechazdo"> Recurso rechazado:</strong>
                <?php endif; ?>
                <?= Html::encode($model->comentario) ?>
            </p>
        <?php endif; ?>



        <?php if (!empty($archivo)): ?>
            <?php $ext = pathinfo($archivo, PATHINFO_EXTENSION); ?>
            <div>
                <?php if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                    <a data-fancybox="galeria-chat" href="<?= $rutaArchivo ?>">
                        <img src="<?= $rutaArchivo ?>" style="max-width: 200px;">
                    </a>

                <?php else: ?>
                    <a href="<?= $rutaArchivo ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2" style="padding: 0">
                        Descargar archivo (<?= strtoupper($ext) ?>)
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
$this->registerCssFile('https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js', [
    'depends' => [\yii\web\JqueryAsset::class],
]);

$this->registerCss("
    .fancybox__nav {
        opacity: 1 !important;
        visibility: visible !important;
    }
");
$this->registerJs(<<<JS
Fancybox.bind("[data-fancybox='galeria-chat']", {
    Toolbar: {
        display: ["zoom", "close"]
    },
    animated: true,
    dragToClose: false
});
JS);
?>




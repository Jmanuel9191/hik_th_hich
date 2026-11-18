<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;
$this->title = 'TAREAS';
$format = <<< SCRIPT
function format(state) {
if (!state.id) return state.text; // optgroup
return state.text;
}
SCRIPT;
$escape = new \yii\web\JsExpression("function(m) { return m; }");
$this->registerJs($format, \yii\web\View::POS_HEAD);

?>

<?php
$this->registerJsFile(
    'https://cdn.jsdelivr.net/npm/sweetalert2@11',
    ['position' => \yii\web\View::POS_HEAD]
);
$flash = Yii::$app->session->getFlash('flash'); //

if ($flash && is_array($flash)) {
    $type = $flash['type'] ?? 'info';
    $message = addslashes(trim($flash['message'] ?? 'Mensaje no disponible'));

    echo <<<HTML
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: '$type',
        title: '$message',
        showConfirmButton: true,
        position: 'center',
        backdrop: true
    }).then(() => {
        // ✅ Desapareció el popup, ahora borramos el flash forzado desde JavaScript
        fetch(window.location.href + '?flashclear=1', { method: 'GET' });
    });
});
</script>
HTML;
}
?>

<?= $this->render('../widgets/opciones') ?>

<div class="app-wrapper">

    <?php
    if (isset($render)) {
        switch ($render) {
            case 'listgeneral':
                echo $this->render('listgeneral',['model' => $model]);
                break;
            case 'micalendario':
                echo $this->render('micalendario', [
                    'model' => $model,
                    'hayEventosNuevos' => $hayEventosNuevos ?? false,
                    'usuario' => $usuario,

                ]);



        }
    }
    ?>
    <!--/container-main-->

</div>



<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales-all.min.js"></script>

<?php

$this->registerJsFile(
    '@web/assets_b/js/index.global.js',
    ['depends' => [\yii\web\JqueryAsset::class]]
);
$this->registerJsFile(
    '@web/assets_b/js/index.global.min.js',
    ['depends' => [\yii\web\JqueryAsset::class]]
);
$this->registerJsFile('@web/assets_b/js/es.global.js', [
    'depends' => [\yii\web\JqueryAsset::class]
]);

$script = <<<JS
JS;
$this->registerJs($script, \yii\web\View::POS_HEAD);


?>



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
                case 'listatotaltareas':
                    echo $this->render('listatotaltareas', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
                    break;
                case 'view':
                    echo $this->render('view', ['model' => $model, 'searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
                    break;
                case 'create':
                    echo $this->render('create', ['model' => $model]);
                    break;
                case 'update':
                    echo $this->render('update', ['model' => $model]);
                    break;


            }
        }
        ?>
        <!--/container-main-->

</div>

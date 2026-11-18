<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;
$this->title = 'CAMPAÑAS';
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
        switch ($render ?? 'listac') {

                case 'listac':
                    echo $this->render('listac', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider,  'campaniasFinalizables' => $campaniasFinalizables,]);
                    break;
            case 'historial':
                echo $this->render('historial', ['model' => $model, 'searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
                break;

                case 'milista':
                    echo $this->render('milista', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
                    break;
                case 'view':
                    echo $this->render('view', ['model' => $model, 'asignacionesFinalizables' => $asignacionesFinalizables,  'porprgres' => $porprgres, 'finalizadas' => $finalizadas, 'asignacionesPorHacer' => $asignacionesPorHacer,]);
                    break;
                case 'historialmaterial':
                    echo $this->render('historialmaterial', [
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                        'recurso' => $recurso,
                        'esCliente' => $esCliente,
                        'ultimoId' => $ultimoId,
                        'model' => new \app\models\HistoriaRecursos(), // <- Esto es lo que faltaba
                    ]);
                    break;

                case 'create':
                    echo $this->render('create', ['model' => $model]);
                    break;
                case 'update':
                    echo $this->render('update', ['model' => $model]);
                    break;
                case 'asignar':
                    echo $this->render('asignar', ['model' => $model]);
                    break;
                case 'editasignar':
                    echo $this->render('editasignar', ['model' => $model]);
                    break;


            }
        ?>
        <!--/container-main-->

    </div><!--//app-wrapper-->


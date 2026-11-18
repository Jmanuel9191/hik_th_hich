<?php
use yii\helpers\Html;

$this->title = 'CATÁLOGOS';

$flash = Yii::$app->session->getFlash('flash');
foreach (Yii::$app->session->getAllFlashes() as $type => $message) {
    $message = addslashes(trim($message));

    echo <<<HTML
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: '$type',
        title: '$message',
        showConfirmButton: true,
        position: 'center',
        backdrop: true
    }).then(() => {
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
            case 'listasucursal':
                echo $this->render('listasucursal', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
                break;
            case 'createsucursal':
                echo $this->render('createsucursal', ['model' => $model]);
                break;
            case 'viewsucursal':
                echo $this->render('viewsucursal', ['model' => $model]);
                break;
            case 'updatesucursal':
                echo $this->render('updatesucursal', ['model' => $model]);
                break;

            case 'listasector':
                echo $this->render('listasector', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
                break;
            case 'createsector':
                echo $this->render('createsector', ['model' => $model]);
                break;
            case 'viewsector':
                echo $this->render('viewsector', ['model' => $model]);
                break;
            case 'updatesector':
                echo $this->render('updatesector', ['model' => $model]);
                break;

            case 'listacategoriaservicio':
                echo $this->render('listacategoriaservicio', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
                break;
            case 'createcategoriaservicio':
                echo $this->render('createcategoriaservicio', ['model' => $model]);
                break;
            case 'viewcategoriaservicio':
                echo $this->render('viewcategoriaservicio', ['model' => $model]);
                break;
            case 'updatecategoriaservicio':
                echo $this->render('updatecategoriaservicio', ['model' => $model]);
                break;

            case 'listaaudiencia':
                echo $this->render('listaaudiencia', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
                break;
            case 'createaudiencia':
                echo $this->render('createaudiencia', ['model' => $model]);
                break;
            case 'viewaudiencia':
                echo $this->render('viewaudiencia', ['model' => $model]);
                break;
            case 'updateaudiencia':
                echo $this->render('updateaudiencia', ['model' => $model]);
                break;

            case 'listacanalmarketing':
                echo $this->render('listacanalmarketing', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
                break;
            case 'createcanalmarketing':
                echo $this->render('createcanalmarketing', ['model' => $model]);
                break;
            case 'viewcanalmarketing':
                echo $this->render('viewcanalmarketing', ['model' => $model]);
                break;
            case 'updatecanalmarketing':
                echo $this->render('updatecanalmarketing', ['model' => $model]);
                break;

            case 'listatipotarea':
                echo $this->render('listatipotarea', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
                break;
            case 'createtipotarea':
                echo $this->render('createtipotarea', ['model' => $model]);
                break;
            case 'viewtipotarea':
                echo $this->render('viewtipotarea', ['model' => $model]);
                break;
            case 'updatetipotarea':
                echo $this->render('updatetipotarea', ['model' => $model]);
                break;

            case 'listarol':
                echo $this->render('listarol', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
                break;
            case 'createrol':
                echo $this->render('createrol', ['model' => $model]);
                break;
            case 'viewrol':
                echo $this->render('viewrol', ['model' => $model]);
                break;
            case 'updaterol':
                echo $this->render('updaterol', ['model' => $model]);
                break;
        }
    }
    ?>
</div>

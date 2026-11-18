<?php

$user = Yii::$app->user->identity;
use yii\helpers\Url;
use yii\helpers\Html;
use app\helpers\PermisosGeneralesHelper;

$format = <<< SCRIPT
function format(state) {
if (!state.id) return state.text; // optgroup
return state.text;
}
SCRIPT;
$escape = new \yii\web\JsExpression("function(m) { return m; }");
$this->registerJs($format, \yii\web\View::POS_HEAD);
use kartik\select2\Select2;
use kartik\form\ActiveForm;
?>


<div class="card">
    <div class="card-body">
        <h5 class="card-title pohacer">POR HACER  (<?= \app\models\Asignacion::find()->where(['idproyecto'=>$model->idproyecto, 'estado' =>'1'])->count() ?>)</h5>
              <?php $porhae = \app\models\Asignacion::find()->where(['idproyecto'=> $model->idproyecto, 'estado' =>'1'])->all()?>
            <?php foreach ($porhae as $item):?>


            <div class="carse">



                <?php if (in_array($user->rol->alias, ['administrador', 'gestor'])): ?>
                    <?= Html::a('<i class="fas fa-edit"></i>', ['proyectos/editasignaciondetareas', 'id' => $item->idasignartarea, 'idproyecto' => $model->idproyecto], [
                        'class' => 'breditasig',
                        'title' => 'Editar',
                        'data-toggle' => 'tooltip'
                    ]) ?>

                    <?= Html::a('<i class="fas fa-trash-alt"></i>', ['proyectos/deletasignaciondetareas', 'id' => $item->idasignartarea, 'idproyecto' => $model->idproyecto], [
                        'class' => 'breditasigdelete',
                        'title' => 'Eliminar',
                        'data-toggle' => 'tooltip',
                        'data-confirm' => '¿Estás seguro de eliminar esta asignación?',
                        'data-method' => 'post'
                    ]) ?>
                <?php endif; ?>

                <div class="cartas"> <strong class="moresmas"> <?php echo $item->tareas->titulo ?> </strong>
                    <button type="button" class="btn app-btn-secondary brncmores" data-bs-toggle="modal" data-bs-target="#basicModal<?= $item['idasignartarea'] ?>">
                        <i class="fa-solid fa-circle-plus"></i>
                    </button>


                        <?php
                        switch ($item->prioridad) {
                            case 'Alta':
                                $clasePrioridad = 'pralta';
                                break;
                            case 'Media':
                                $clasePrioridad = 'prmedia';
                                break;
                            case 'Baja':
                                $clasePrioridad = 'prbaja';
                                break;
                            default:
                                $clasePrioridad = 'sinprioridad';
                                break;
                        }
                        ?>
                        <br>
                        <span class=" <?= $clasePrioridad ?> consultoresasi">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <?php echo $item->prioridad  ?>
                        </span>

                        <br><span class="consultoresasi"><i class="fa-solid fa-user-tie"></i>  <?php echo $item->consultor->nombrecompleto ?></span>
                        <br> <?= $item->rangoFechasAsignacion ?>


                    <br>
                    <?php if (PermisosGeneralesHelper::puedeCrearRecurso($user, $item)): ?>

                        <a href="#"
                           class="btn aceptar-tarea"
                           data-url="<?= \yii\helpers\Url::to(['proyectos/aceptar-tarea', 'id' => $item->idasignartarea]) ?>">
                            Empezar
                        </a>
                    <?php endif; ?>

                    </div>
                <?php  echo $this->render('../widgets/modalgeneraldetareas', ['item' => $item]); ?>


            </div>

            <?php endforeach;?>

    </div>
</div>
    <style>
        div:where(.swal2-container) button:where(.swal2-styled):where(.swal2-confirm) {     background-color: #2879dd!important;     font-size: 1.2em!important}
        div:where(.swal2-container) button:where(.swal2-styled):where(.swal2-cancel){      font-size: 1.2em!important;    }
        div:where(.swal2-container) div:where(.swal2-popup) {      font-size: 1.3rem!important;   }


    </style>

<?php
$this->registerJsFile(
    'https://cdn.jsdelivr.net/npm/sweetalert2@11',
    ['position' => \yii\web\View::POS_HEAD]
);
$this->registerJsFile(
    '@web/assets_b/js/estadosc.js',
    ['depends' => [\yii\web\JqueryAsset::class]]
);
?>
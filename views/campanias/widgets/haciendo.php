<?php
$user = Yii::$app->user->identity;
use yii\helpers\Url;
use yii\helpers\Html;
use app\helpers\PermisosGeneralesHelper;

?>
<div class="card">
    <div class="card-body">
        <h5 class="card-title hacendo">EN PROGRESO  (<?= count($asignacionesPorHacer) ?>)</h5>

        <?php foreach ($asignacionesPorHacer as $item):?>

            <?php  $recursos = $item->recursos; ?>



            <div class="cartas ">
                <strong class="moresmas"> <?php echo $item->tareas->titulo ?> </strong>
                <button type="button" class="btn app-btn-secondary brncmores" data-bs-toggle="modal" data-bs-target="#basicModal<?= $item['idasignartarea'] ?>">
                    <i class="fa-solid fa-circle-plus"></i>
                </button>

               <br>
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

                <span class=" <?= $clasePrioridad ?> consultoresasi">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <?php echo $item->prioridad  ?>
                 </span>

                <br><span class="consultoresasi"><i class="fa-solid fa-user-tie"></i>  <?php echo $item->consultor->nombrecompleto ?></span>
                <br> <?= $item->rangoFechasAsignacion ?>
                <br>

                <?php if (PermisosGeneralesHelper::puedeFinalizarTarea($user, $item, $asignacionesFinalizables)): ?>
                    <?= Html::a('Finalizar Tarea <i class="fa fa-check"></i> ', ['campanias/finalizartarea'], [
                        'class' => 'btn btn-success btn-finalizar-tarea breditasigginalziarn',
                        'data-idasignacion' => $item->idasignartarea,
                        'data-idcampania' => $model->idcampania,
                    ]) ?>
                <?php endif; ?> <br>
                <!--Espacio de Recursos -->

                <div class="recursos"> <span class="spanrecrs"> Recursos </span>

                    <?php if (PermisosGeneralesHelper::puedeCrearRecurso($user, $item)): ?>
                    <button type="button" class="btn createrecr float-end" data-bs-toggle="modal" data-bs-target="#modalCrearRecurso<?= $item['idasignartarea'] ?>">
                        <i class="fa fa-plus"></i>
                    </button>
                    <?php endif; ?>




                    <div class="col-12 recursos-lista">
                        <?php if (count($recursos) > 0): ?>
                            <?php foreach ($recursos as $index => $recursoitem):
                                $fechaEntrega = $recursoitem->fechaentrega ? date("d/m/Y", strtotime($recursoitem->fechaentrega)) : 'No definida';
                                ?>
                                <div class="col-12 cajarecurso <?php if  ($recursoitem->estado == 1): echo ' '; elseif (($recursoitem->estado == 2)):  echo 'cajaverde'; elseif (($recursoitem->estado == 3)):  echo 'cajaroja'; endif; ?>" >
                                    <div class="col-9">
                                        <p><strong>#<?= $index + 1 ?>.</strong> <a href="<?= \yii\helpers\Url::to(['campanias/historialmaterial', 'idrecurso' => $recursoitem->idrecurso]) ?>" title="Ver"> <?= $recursoitem->titulo ?></a> <br>
                                            <span>Fecha límite: <?= $fechaEntrega ?></span></p>

                                    </div>
                                    <div class="col-3 opcionesrec">


                                        <?php if (PermisosGeneralesHelper::puedeEditarOEliminarRecurso($user, $item, $recursoitem)): ?>
                                            <a href="<?= Url::to(['campanias/historialmaterial', 'idrecurso' => $recursoitem->idrecurso]) ?>"
                                               title="Ver recurso" class="eyesr">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>

                                            <button type="button" class="btn penrec" data-bs-toggle="modal" data-bs-target="#modalEditarRecurso<?= $recursoitem['idrecurso'] ?>">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <a href="#" class="trasrec" data-id="<?= $recursoitem->idrecurso ?>" title="Eliminar recurso">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>

                                        <?php else: ?>
                                            <a href="<?= Url::to(['campanias/historialmaterial', 'idrecurso' => $recursoitem->idrecurso]) ?>"
                                               title="Ver recurso" class="eyesr recursovera">
                                                Ver más
                                            </a>
                                        <?php endif; ?>





                                    </div>
                                </div>

                                <?= $this->render('../_form_editar_recurso', [
                                'modelRecurso' => $recursoitem,
                                'idasignartarea' => $item->idasignartarea,
                                'tarea' => $item->tareas->titulo,
                                'asignacion' => $item,
                                'fechafinTarea' => $item->fechafin,
                            ]) ?>

                            <?php endforeach; ?>

                        <?php else: ?>
                            <div class="text-muted">Sin recursos creados aún.</div>
                        <?php endif; ?>
                    </div>


                </div>


                <?= $this->render('../_form_recurso', [
                    'modelRecurso' => new \app\models\Recursos(),
                    'idasignartarea' => $item->idasignartarea,
                    'tarea' => $item->tareas->titulo,
                    'asignacion' => $item,
                    'fechafinTarea' => $item->fechafin,

                ]) ?>


                <!--Fin Espacio de Recursos -->
            </div>
            <?php  echo $this->render('../widgets/modalgeneraldetareas', ['item' => $item]); ?>
        <?php endforeach;?>
    </div>
</div>





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

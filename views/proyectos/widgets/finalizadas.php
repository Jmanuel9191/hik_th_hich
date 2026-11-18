<?php
$user = Yii::$app->user->identity;;
use yii\helpers\Url;
use yii\helpers\Html;
use app\helpers\PermisosGeneralesHelper;

?>
<div class="card">
    <div class="card-body">
        <h5 class="card-title finalizada">FINALIZADAS (<?= count($finalizadas) ?>)</h5>

        <?php foreach ($finalizadas as $item):?>

            <?php $recursos = $item->recursos;?>




            <div  class="cartas">
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
                <?php if (PermisosGeneralesHelper::puedeVolverAtras($user, $item, $model)): ?>
                    <?= Html::a('<i class="fa fa-check"></i> Volver atrás', ['proyectos/volvarahacer'], [
                        'class' => 'btn btn-success btn-finalizar-volverhacer breditasigginalziarnatras',
                        'data-idasignacion' => $item->idasignartarea,
                        'data-idproyecto' => $model->idproyecto,
                    ]) ?>
                <?php endif; ?>   <br>

                <div class="recursos"> <span class="spanrecrs"> Recursos </span>


                    <div class="col-12 recursos-lista">
                        <?php if (count($recursos) > 0): ?>
                            <?php foreach ($recursos as $index => $recursoitem):
                                $fechaEntrega = $recursoitem->fechaentrega ? date("d/m/Y", strtotime($recursoitem->fechaentrega)) : 'No definida';
                                ?>
                                <div class="col-12 cajarecurso <?php if  ($recursoitem->estado == 1): echo ' '; elseif (($recursoitem->estado == 2)):  echo 'cajaverde'; elseif (($recursoitem->estado == 3)):  echo 'cajaroja'; endif; ?>" >
                                    <div class="col-9">
                                        <p><strong>#<?= $index + 1 ?>.</strong> <a href="<?= \yii\helpers\Url::to(['proyectos/historialmaterial', 'idrecurso' => $recursoitem->idrecurso]) ?>" title="Ver"> <?= $recursoitem->titulo ?></a> <br>
                                            <?php
                                            $fechaAprobado = $recursoitem->fechaAprobacionCliente;
                                            ?>
                                            <span>
    <?= $fechaAprobado ? 'Aprobado el: ' . date('d/m/Y', strtotime($fechaAprobado)) : 'No aprobado aún' ?>
</span>

                                        </p>

                                    </div>
                                    <div class="col-3 opcionesrec">



                                            <a href="<?= \yii\helpers\Url::to(['proyectos/historialmaterial', 'idrecurso' => $recursoitem->idrecurso]) ?>"
                                               title="Ver recurso"
                                               class="eyesr recursovera">Ver más</a>

                                    </div>
                                </div>



                            <?php endforeach; ?>

                        <?php else: ?>
                            <div class="text-muted">Sin recursos creados aún.</div>
                        <?php endif; ?>
                    </div>


                </div>




            </div>
            <?php  echo $this->render('../widgets/modalgeneraldetareas', ['item' => $item]); ?>
        <?php endforeach;?>

    </div>
</div>

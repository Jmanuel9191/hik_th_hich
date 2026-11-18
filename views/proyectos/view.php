<?php
use yii\helpers\Url;
$user =  Yii::$app->user->identity;;



$this->title = 'PROYECTO';
?>


<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">

        <div class="row">
        <div class="col-12 ">
            <h1 class="app-page-title  text-left">PROYECTO: <span class=""> <?= $model['titulo']?> </span></h1>

        </div>

        <div class="col-12 pdd10">
            <div class="app-card-footer p-4 mt-auto">
                <button type="button" class="btn app-btn-secondary" data-bs-toggle="modal" data-bs-target="#basicModal">
                    Ver detalles
                </button>
                <?php if (in_array($user->rol->alias, ['administrador', 'gestor']) && $model['finalizado'] !== '2'): ?>
                    <a class="btn app-btn-secondary" href="<?= Url::to(['proyectos/update?id='.$model['idproyecto']]) ?>">Editar Proyecto</a>
                <a class="btn app-btn-secondary" href="<?= Url::to(['proyectos/asignaciondetareas', 'idproyecto' => $model['idproyecto']]) ?>">Asignar tareas y consultores</a>
                <?php endIf; ?>

            </div>

        </div>
        </div>


        <div class="col-12">
            <div class="row">
        <div class="card">
            <div class="card-body">

                <!-- Bordered Tabs Justified -->
                <ul class="nav nav-tabs nav-tabs-bordered d-flex" id="borderedTabJustified" role="tablist">
                    <li class="nav-item flex-" role="presentation">
                        <button class="nav-link " id="informacion-b" data-bs-toggle="tab" data-bs-target="#informacion" type="button" role="tab" aria-controls="home" aria-selected="false" tabindex="-1">INFORMACIÓN</button>
                    </li>
                    <li class="nav-item flex-" role="presentation">
                        <button class="nav-link active" id="tablero-b" data-bs-toggle="tab" data-bs-target="#tablero" type="button" role="tab" aria-controls="profile" aria-selected="false" tabindex="-1">TABLERO DE TAREAS</button>
                    </li>
                </ul>
                <div class="tab-content pt-2" id="borderedTabJustifiedContent">
                    <div class="tab-pane " id="informacion" role="tabpanel" aria-labelledby="informacion-b">
                        <div class="row gy-4">
                            <div class="col-12 cardtareas ">


                                <div class="col-xxl-12 col-md-12">
                                <p style="display: block!important;"> <strong> Total Tareas:  </strong>  <?= \app\models\Asignacion::find()->where(['idproyecto'=>$model->idproyecto])->count();?>
                                </p>
                                </div>
                                <div class="col-12 flexdesl">

                                <div class="col-xxl-3 col-md-3 c">
                                    <?= $this->render('../widgets/tarjetas', ['titulo' => 'TOTAL TAREAS EN ESPERA','icono' => '<i class="fa fa-list" aria-hidden="true"></i>', 'numero' =>  \app\models\Asignacion::find()->where(['idproyecto'=>$model->idproyecto, 'estado' =>'1'])->count()]) ?>
                                </div>

                                <div class="col-xxl-3 col-md-3 col-xl-">
                                    <?= $this->render('../widgets/tarjetas', ['titulo' => 'TOTAL TAREAS EN CURSO','icono' => '<i class="fa fa-list" aria-hidden="true"></i>', 'numero' =>  \app\models\Asignacion::find()->where(['idproyecto'=>$model->idproyecto, 'estado' =>'2'])->count()]) ?>
                                </div>

                                <div class="col-xxl-3 col-md-3 col-xm-">
                                    <?= $this->render('../widgets/tarjetas', ['titulo' => 'TOTAL TAREAS COMPLETADAS','icono' => '<i class="fa fa-list" aria-hidden="true"></i>', 'numero' =>  \app\models\Asignacion::find()->where(['idproyecto'=>$model->idproyecto, 'estado' =>'3'])->count()]) ?>
                                </div>

                                <div class="col-xxl-3 col-md-3 col-xl-">
                                    <?= $this->render('../widgets/tarjetas', ['titulo' => 'TOTAL TAREAS VENCIDAS','icono' => '<i class="fa fa-list" aria-hidden="true"></i>', 'numero' =>  \app\models\Asignacion::find()
                                        ->where(['idproyecto' => $model->idproyecto])
                                        ->andWhere(['in', 'estado', ['1', '2']]) // tareas no finalizadas
                                        ->andWhere(['<', 'fechafin', new \yii\db\Expression('NOW()')])
                                        ->count()]) ?>
                                </div>
                                </div>
                            </div><!--//col-->






                       </div><!--//row-->
                    </div>



                    <div class="tab-pane active tablesckai" id="tablero" role="tabpanel" aria-labelledby="tablero-b">
                        <div class="row gy-4">
                            <div class="col-12 cardtareas">
                                <div class="col-lg-4 col-xs-12">
                                    <?= $this->render('widgets/porhacer', ['model' =>  $model]) ?>
                                </div>
                                <div class="col-lg-4 col-xs-12">
                                    <?= $this->render('widgets/haciendo', ['model' =>  $model, 'asignacionesFinalizables' => $asignacionesFinalizables,     'porprgres' => $porprgres,]) ?>
                                </div>
                                <div class="col-lg-4 col-xs-12">
                                    <?= $this->render('widgets/finalizadas', ['model' =>  $model, 'finalizadas' => $finalizadas,]) ?>
                                </div>

                            </div>
                        </div>
                    </div>
                </div><!-- End Bordered Tabs Justified -->







            </div>
        </div>
        </div>
        </div>


    </div><!--//container-fluid-->
</div><!--//app-content-->




<div class="modal " id="basicModal" tabindex="-1">
    <div class="modal-dialog modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-center">DETALLES DEL PROYECTO</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body desproyecto">



                <p><strong>Proyecto:</strong> <?= $model->titulo ?></p>
                <p><strong>Cliente:</strong> <?= $model->usuario->nombrecompleto ?> - <?= $model->usuario->empresa ?></p>
                <p><strong>Gestor encargado:</strong> <?= $model->gestor->nombrecompleto ?></p>
                <p><strong>Prioridad:</strong> <?= strip_tags($model->prioridad) ?></p>
                <p><strong>Estado del proyecto:</strong> <?= $model->estado == '1' ? 'Activo' : 'Bloqueado' ?></p>
                <p><strong>Fecha Inicio:</strong> <?= $model->fechaInicioFormateada ?></p>
                <p><strong>Fecha Fin:</strong> <?= $model->fechaFinFormateada?></p>
                <p><strong>Fecha de registro:</strong> <?= $model->fechaRegistroFormateada?></p>
                <p><strong>Sucursal(es):</strong>
                    <?php
                    $sucurs = $model->sucursalesRelacionadas;
                    echo empty($sucurs)
                        ? '-'
                        : implode('<br>', array_map(fn($s) => "{$s->pais} ({$s->ciudad} - {$s->nombre})", $sucurs));
                    ?>
                </p>


                <p><strong>Sector(es):</strong>


                    <?php
                    $sects = $model->sectoresRelacionados;
                    echo empty($sects)
                        ? '-'
                        :  implode(', ', array_map(fn($s) => $s->nombre, $sects));
                    ?>
                </p>




                <p><strong>Categoría de servicio:</strong> <?= $model->categoriaServicio->nombre ?? '-' ?></p>
                <p><strong>Descripción u objetivo:</strong> <?= strip_tags($model->descripcion) ?></p>

                <?php if ($model->estado == '0') : ?>
                    <p><strong>Motivo del bloqueo:</strong> <?= $model->motivobloqueo ?></p>
                <?php endif; ?>

                <?php if ($model->finalizado == '2') : ?>
                    <p><strong>Comentario de finalización:</strong> <?= $model->motivodefinal ?></p>
                    <p><strong>Fecha de finalización:</strong> <?= $model->finalizacionFormateada ?></p>
                <?php endif; ?>





            </div>

        </div>
    </div>
</div><!-- End Basic Modal-->

<?php



?>
<?php
use yii\helpers\Url;

$this->title = 'PERFIL USUARIO';
$proyectoscomocliente = $model->proyectosComoCliente;
$proyectoscomogestor = $model->proyectosComoGestor;
$proyectoscomoconsultor = $model->proyectosComoConsultor;
?>



<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">
        <div class="col-12 col-sm-6">
            <h1 class="app-page-title">Usuario: <?php echo $model['nombrecompleto'] ?></h1>
        </div>
        <div class="col-12 col-sm-6">
            <div class="app-card-footer p-4 mt-auto">
                <a class="btn app-btn-secondary" href="<?= Url::to(['administrador/update?id='. $model['idusuario']]) ?>">Editar Perfil</a>
            </div><!--//app-card-footer-->
        </div>
        <div class="row gy-4">
            <div class="col-12">
                <div class="app-card-account shadow-sm d-flex flex-column align-items-start">
                    <div class="app-card-body px-4 w-100">
                        <h1 class="app-page-title">INFORMACION GENERAL:</h1>
                        <div class="col-12 flexdesl" style="padding: 0">
                        <?= $this->render('../widgets/appcuenta', ['label' => 'Nombre Completo', 'datos' =>  $model['nombrecompleto']]) ?>
                        <?= $this->render('../widgets/appcuenta', ['label' => 'Usuario', 'datos' =>  $model['usuario']]) ?>
                            </div>

                        <div class="col-12 flexdesl" style="padding: 0">

                        <?= $this->render('../widgets/appcuenta', ['label' => 'Tipo usuario', 'datos' => $model->rol->nombre ?? 'Rol no definido']) ?>
                        <?= $this->render('../widgets/appcuenta', ['label' => 'Email', 'datos' =>  $model['email']]) ?>
                        </div>
                           <div class="col-12 flexdesl" style="padding: 0">
                        <?= $this->render('../widgets/appcuenta', ['label' => 'Telefono', 'datos' =>  $model['telefono']]) ?>
                        <?= $this->render('../widgets/appcuenta', ['label' => 'Movil', 'datos' =>  $model['movil']]) ?>
                           </div>
                               <div class="col-12 flexdesl" style="padding: 0">
                        <?= $this->render('../widgets/appcuenta', ['label' => 'Sexo', 'datos' =>  $model['sexo']]) ?>
                        <?= $this->render('../widgets/appcuenta', ['label' => 'Fecha de nacimiento', 'datos' => $model->fechaNacimientoFormateada]) ?>
                               </div>
                                   <div class="col-12 flexdesl" style="padding: 0">
                        <?= $this->render('../widgets/appcuenta', ['label' => 'Fecha de registro', 'datos' => $model->fechaRegistroFormateada]) ?>
                        <?= $this->render('../widgets/appcuenta', ['label' => 'Empresa', 'datos' =>  $model['nombrecompleto']]) ?>
                                   </div>
                                        <div class="col-12 flexdesl" style="padding: 0">
                        <?= $this->render('../widgets/appcuenta', ['label' => 'Cargo', 'datos' =>  $model['cargo']]) ?>
                        <?= $this->render('../widgets/appcuenta', ['label' => 'Pais', 'datos' =>  $model['pais']]) ?>

                                        </div>
                                            <div class="col-12 flexdesl" style="padding: 0">
                        <?= $this->render('../widgets/appcuenta', ['label' => 'Ciudad', 'datos' =>  $model['ciudad']]) ?>
                        <?= $this->render('../widgets/appcuenta', ['label' => 'Direccion', 'datos' =>  $model['direccion']]) ?>

                                            </div>
                        <?= $this->render('../widgets/appcuenta', ['label' => 'Zona horaria', 'datos' =>  $model['zona_horaria']]) ?>






                    </div><!--//app-card-body-->


                </div><!--//app-card-->
            </div><!--//col-->

            <div class="col-12">
                <div class="app-card-account shadow-sm d-flex flex-column align-items-start">
                    <div class="app-card-body tanlaprusi px-4 w-100">

                <h1 class="app-page-title ">PROYECTO/CAMPAÑAS:</h1>
                        <?php $elementos = $model->elementosRelacionadosPorRol;
                        ?>
                        <?php if (!empty($elementos)): ?>
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Título</th>
                                    <th>Tareas Asignadas</th>
                                    <th>Acción</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($elementos as $elemento): ?>
                                    <tr>
                                        <td><?= ucfirst($elemento['tipo']) ?></td>
                                        <td><?= htmlspecialchars($elemento['modelo']->titulo) ?></td>
                                        <td>
                                            <ul>
                                                <?php foreach ($elemento['tareas'] as $tarea): ?>
                                                    <li><?= htmlspecialchars($tarea->titulo) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </td>
                                        <td>
                                            <a href="<?= Url::to([$elemento['tipo'] === 'proyecto' ? 'proyectos/view' : 'campanias/view', 'id' => $elemento['modelo']->getPrimaryKey()]) ?>"
                                               class="btn btn-primary" style="color: white!important;">Ver</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No hay elementos relacionados.</p>
                        <?php endif; ?>






                    </div>
            </div>
            </div>


        </div><!--//row-->

    </div><!--//container-fluid-->
</div><!--//app-content-->



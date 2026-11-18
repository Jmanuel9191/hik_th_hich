<?php
use yii\helpers\Html;
use yii\helpers\Url;
\kartik\select2\Select2Asset::register($this);
$this->title = 'Parámetros de: ' . $model->titulo;

$nombresExistentes = \app\models\Parametros::find()->select('nombre')->column();
$dataSelect2 = array_map(fn($n) => ['id' => $n, 'text' => $n], $nombresExistentes);
?>

<section class="content-header">
    <h1><?= Html::encode($this->title) ?></h1>
</section>

<section class="content">
<div class="row">
<div class="col-xs-12">
<div class="box" style="padding:10px 20px;">

<form method="post" action="<?= Url::to(['parametros-guardar', 'id' => $model->idproducto]) ?>">
    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

    <!-- Header con botón nuevo parámetro -->
    <div class="row" style="margin-bottom:10px; margin-top:10px;">
        <div class="col-sm-6">
            <h4 style="margin:0;">Parámetros actuales</h4>
        </div>
        <div class="col-sm-6 text-right">
            <button type="button" class="btn btn-success btn-sm"
                    data-toggle="modal" data-target="#modal-nuevo-parametro">
                <i class="fa fa-plus"></i> Nuevo parámetro
            </button>
        </div>
    </div>

    <?php if (empty($vinculos)): ?>
        <p><em>Todavía no tiene ninguno.</em></p>
    <?php else: ?>
        <table class="table table-bordered table-condensed">
            <thead>
                <tr>
                    <th style="width:80px">Orden</th>
                    <th>Nombre</th>
                    <th>Unidad</th>
                    <th class="text-center">Negrita</th>
                    <th class="text-center">Subtítulo</th>
                    <th>Referencia</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($vinculos as $v): ?>
                <tr>
                    <td>
                        <input type="number" name="OrdenExistente[<?= $v->idparametro ?>]"
                               value="<?= $v->orden ?>" class="form-control" style="width:70px">
                    </td>
                 <td>
    <?php if (trim($v->parametro->nombre ?? '') !== ''): ?>
        <?= Html::encode($v->parametro->nombre) ?>
    <?php elseif (in_array($v->parametro->tipo_campo ?? 'texto', ['select', 'select_texto'])): ?>
        <em class="text-muted">
            Opciones: <?= Html::encode(str_replace("\n", ', ', trim($v->parametro->opciones ?? ''))) ?>
        </em>
    <?php else: ?>
        <em class="text-muted"><?= Html::encode($v->parametro->valor_default ? '(' . mb_substr($v->parametro->valor_default, 0, 60) . '...)' : '(campo de texto libre)') ?></em>
    <?php endif; ?>
</td>
                    <td><?= Html::encode($v->parametro->unidad ?: '—') ?></td>
                    <td class="text-center">
                        <?= ($v->parametro->es_negrita ?? 0)
                            ? '<i class="fa fa-check text-success"></i>'
                            : '—' ?>
                    </td>
                    <td class="text-center">
                        <?= ($v->parametro->es_subtitulo ?? 0)
                            ? '<i class="fa fa-check text-success"></i>'
                            : '—' ?>
                    </td>
      <td style="font-size:11px; color:#555; max-width:220px; word-wrap:break-word;">
    <?= $v->parametro->referencia_texto ?: '—' ?>
</td>
                    <td>

                     <button type="button"
            class="btn btn-xs btn-warning btn-editar-param"
            data-idparametro="<?= $v->idparametro ?>"
            data-url-datos="<?= Url::to(['parametros-datos', 'idparametro' => $v->idparametro]) ?>">
        <i class="fa fa-pencil"></i> Editar
    </button>


                        <a href="#" class="btn btn-xs btn-danger" onclick="
                            if(confirm('¿Quitar este parámetro?')){
                                fetch('<?= Url::to(['parametros-quitar', 'id' => $model->idproducto, 'idparametro' => $v->idparametro]) ?>', {
                                    method: 'POST',
                                    headers: { 'X-CSRF-Token': $('meta[name=csrf-token]').attr('content') }
                                })
                                .then(r=>r.json()).then(d=>{ if(d.exito) location.reload(); else alert(d.mensaje); });
                            }
                            return false;
                        ">Quitar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!--<hr>
    <h4>Agregar parámetro existente</h4>
    <div id="filas-container"></div>
    <button type="button" id="btn-agregar-fila" class="btn btn-default btn-sm" style="margin:10px 0">
        <i class="fa fa-plus"></i> Agregar fila
    </button>   <br><br> -->
  
    <?= Html::submitButton('Guardar todo', ['class' => 'btn btn-primary']) ?>
</form>

</div>
</div>
</div>
</section>

<!-- ============================================ -->
<!-- MODAL: Crear nuevo parámetro                 -->
<!-- ============================================ -->
<div class="modal fade" id="modal-nuevo-parametro" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-plus-circle"></i> Nuevo parámetro
                </h4>
            </div>
            <form method="post" action="<?= Url::to(['parametros-crear', 'id' => $model->idproducto]) ?>">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" class="form-control"
                                       placeholder="Ej: Glucosa, Hematocrito...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Unidad</label>
                                <input type="text" name="unidad" class="form-control"
                                       placeholder="mg/dl, %, mm3">
                            </div>
                        </div>
                        <div class="col-md-2" style="padding-top:30px;">
                            <label style="font-weight:normal;">
                                <input type="checkbox" name="es_negrita" value="1"> Negrita
                            </label>
                        </div>
                        <div class="col-md-2" style="padding-top:30px;">
                            <label style="font-weight:normal;">
                                <input type="checkbox" name="es_subtitulo" value="1"> Subtítulo
                            </label>
                        </div>
                        <div class="col-md-2" style="padding-top:30px;">
                            <label style="font-weight:normal;">
                                <input type="checkbox" name="salto_linea" value="1"> Juntar con el ant.
                            </label>
                        </div>
                    </div>

                    <div class="row" style="margin-top:10px;">
    <div class="col-md-3">
        <div class="form-group">
            <label>Tipo de campo</label>
            <select name="tipo_campo" id="tipo_campo_modal" class="form-control">
    <option value="texto">Texto libre</option>
    <option value="select">Lista desplegable</option>
</select>
        </div>
    </div>
    <div class="col-md-9" id="grupo_opciones_modal" style="display:none;">
        <div class="form-group">
            <label>Opciones (una por línea)</label>
            <textarea name="opciones" id="opciones_modal" class="form-control" rows="3"
                      placeholder="Visible&#10;No visible"></textarea>
        </div>
    </div>


    
</div>

<div class="row" id="grupo_default_modal" style="margin-top:10px;">
    <div class="col-md-12">
        <div class="form-group">
            <label>Texto predeterminado (opcional)</label>
            <textarea name="valor_default" id="valor_default_modal" class="form-control" rows="2"
                      placeholder="Se precarga en Resultados, el técnico puede editarlo"></textarea>
        </div>
    </div>
</div>

                    <div class="form-group">
                        <label>Valores de Referencia</label>
                        <textarea name="referencia_texto" id="referencia_texto_modal"
                                  class="form-control" rows="4"
                                  placeholder="Ej: Hombres de 13.0 - 18.0 gr/dl"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Orden en este producto</label>
                                <input type="number" name="orden" class="form-control"
                                       value="<?= count($vinculos) + 1 ?>">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> Crear y vincular al producto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>









<!-- MODAL: Editar parámetro -->
<div class="modal fade" id="modal-editar-parametro" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-pencil"></i> Editar parámetro</h4>
            </div>
            <form method="post" id="form-editar-param" action="">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Nombre <span class="text-danger">*</span></label>
                                <input type="text" id="edit-nombre" name="nombre"
                                       class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Unidad</label>
                                <input type="text" id="edit-unidad" name="unidad"
                                       class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2" style="padding-top:30px;">
                            <label style="font-weight:normal;">
                                <input type="checkbox" id="edit-negrita"
                                       name="es_negrita" value="1"> Negrita
                            </label>
                        </div>
                        <div class="col-md-2" style="padding-top:30px;">
                            <label style="font-weight:normal;">
                                <input type="checkbox" id="edit-subtitulo"
                                       name="es_subtitulo" value="1"> Subtítulo
                            </label>
                        </div>
                        <div class="col-md-2" style="padding-top:30px;">
                            <label style="font-weight:normal;">
                                <input type="checkbox" id="edit-salto-linea"
                                       name="salto_linea" value="1"> Juntar con el ant.
                            </label>
                        </div>
                    </div>
                  <div class="row" style="margin-top:10px;">
    <div class="col-md-3">
        <div class="form-group">
            <label>Tipo de campo</label>
            <select name="tipo_campo" id="edit-tipo-campo" class="form-control">
    <option value="texto">Texto libre</option>
    <option value="select">Lista desplegable</option>
</select>
        </div>
    </div>
    <div class="col-md-9" id="grupo-opciones-edit" style="display:none;">
        <div class="form-group">
            <label>Opciones (una por línea)</label>
            <textarea id="edit-opciones" name="opciones" class="form-control" rows="3"></textarea>
        </div>
    </div>
</div>

<div class="row" id="grupo-default-edit" style="margin-top:10px;">
    <div class="col-md-12">
        <div class="form-group">
            <label>Texto predeterminado (opcional)</label>
            <textarea id="edit-default" name="valor_default" class="form-control" rows="2"></textarea>
        </div>
    </div>
</div>


<div class="form-group">
    <label>Valores de Referencia</label>
    <textarea name="referencia_texto" id="edit-referencia"
              class="form-control" rows="4"></textarea>
</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>







<?php
$urlEditarBase = json_encode(Url::to(['parametros-editar', 'id' => $model->idproducto]));

?>
<?php
$dataJson = json_encode($dataSelect2);

$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/summernote/0.9.1/summernote.min.css');
$this->registerJsFile(
    'https://cdnjs.cloudflare.com/ajax/libs/summernote/0.9.1/summernote.min.js',
    ['depends' => [\yii\web\JqueryAsset::class]]
);

$this->registerJs("
    var dataParametros = {$dataJson};
    var filaIndex = 0;

    function agregarFila() {
        var idx = filaIndex++;
        var html = '<div class=\"row fila-parametro\" data-idx=\"' + idx + '\" style=\"margin-bottom:8px\">' +
            '<div class=\"col-sm-6\"><select name=\"Parametro[' + idx + '][titulo]\" class=\"form-control select2-fila\" style=\"width:100%\"></select></div>' +
            '<div class=\"col-sm-3\"><input type=\"number\" name=\"Parametro[' + idx + '][orden]\" class=\"form-control\" placeholder=\"Orden\" value=\"' + (idx + 1) + '\"></div>' +
            '<div class=\"col-sm-3\"><button type=\"button\" class=\"btn btn-danger btn-quitar-fila\">&times;</button></div>' +
            '</div>';
        \$('#filas-container').append(html);
        \$('.fila-parametro[data-idx=\"' + idx + '\"] .select2-fila').select2({
            tags: true,
            data: dataParametros,
            placeholder: 'Elegí uno existente o escribí uno nuevo...'
        });
    }

    \$('#btn-agregar-fila').on('click', agregarFila);
    \$(document).on('click', '.btn-quitar-fila', function() {
        \$(this).closest('.fila-parametro').remove();
    });


$(document).on('change', '#tipo_campo_modal', function() {
    var v = $(this).val();
    $('#grupo_opciones_modal').toggle(v === 'select' || v === 'select_texto');
    $('#grupo_default_modal').toggle(v === 'texto');
});

$(document).on('change', '#edit-tipo-campo', function() {
    var v = $(this).val();
    $('#grupo-opciones-edit').toggle(v === 'select' || v === 'select_texto');
    $('#grupo-default-edit').toggle(v === 'texto');
});

// Resetea el modal de \"Nuevo parámetro\" cada vez que se abre, para que no arrastre
// checkboxes/valores de un parámetro creado anteriormente en la misma sesión
\$('#modal-nuevo-parametro').on('show.bs.modal', function() {
    \$(this).find('form')[0].reset();
    \$('#grupo_opciones_modal').hide();
    \$('#grupo_default_modal').show();
});

    // Summernote dentro del modal
    \$('#modal-nuevo-parametro').on('shown.bs.modal', function() {
        if (!\$('#referencia_texto_modal').next('.note-editor').length) {
            \$('#referencia_texto_modal').summernote({
                height: 150,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline']],
                    ['para', ['ul', 'ol', 'paragraph']]
                ]
            });
        }
    });


 var urlEditarBase = {$urlEditarBase};

\$(document).on('click', '.btn-editar-param', function() {
    var idparametro = \$(this).data('idparametro');
    var urlDatos    = \$(this).data('url-datos');

    \$.getJSON(urlDatos, function(d) {
        if (!d.exito) { alert('Error al cargar los datos.'); return; }

        \$('#edit-nombre').val(d.nombre);
        \$('#edit-unidad').val(d.unidad);
        \$('#edit-negrita').prop('checked', d.es_negrita == 1);
        \$('#edit-subtitulo').prop('checked', d.es_subtitulo == 1);
        $('#edit-tipo-campo').val(d.tipo_campo || 'texto');
$('#edit-opciones').val(d.opciones || '');
var tc = d.tipo_campo || 'texto';
$('#grupo-opciones-edit').toggle(tc === 'select' || tc === 'select_texto');
$('#edit-salto-linea').prop('checked', (d.salto_linea === undefined || d.salto_linea === null) ? true : d.salto_linea == 1);
$('#edit-default').val(d.valor_default || '');
$('#grupo-default-edit').toggle(tc === 'texto');

        // Inicializar Summernote si no está ya inicializado
        if (!\$('#edit-referencia').next('.note-editor').length) {
            \$('#edit-referencia').summernote({
                height: 150,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline']],
                    ['para', ['ul', 'ol', 'paragraph']]
                ]
            });
        }

        // Cargar el HTML en Summernote (no en el textarea)
        \$('#edit-referencia').summernote('code', d.referencia_texto);

        \$('#form-editar-param').attr('action', urlEditarBase + '&idparametro=' + idparametro);
        \$('#modal-editar-parametro').modal('show');
    });
});

", \yii\web\View::POS_READY);
?>
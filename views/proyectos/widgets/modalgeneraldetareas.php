

<div class="modal" id="basicModal<?= $item['idasignartarea'] ?>" tabindex="-1">
    <div class="modal-dialog modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-center"> <?php echo $item->tareas->titulo ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body desproyecto">

                <?php if ($item['descripcion']) : ?>
                    <p> <strong>Detalles:</strong> <br> <?= $item['descripcion'] ?></p>
                <?php endif; ?>
                <?php if ($item['comentario']) : ?>
                    <p> <strong>Detalle de modificación de la tarea:</strong> <br> <?= $item['comentario'] ?></p>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div><!-- End Basic Modal-->
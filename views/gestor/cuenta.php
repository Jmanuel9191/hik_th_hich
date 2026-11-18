<?php $this->title = 'CUENTA'; ?>
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
                        case 'perfil':
                            echo $this->render('../cuenta/perfil', ['model' => $model, 'tipousuario' => 'gestor']);
                            break;
                        case 'updateperfil':
                            echo $this->render('../cuenta/updateperfil', ['model' => $model]);
                            break;
                        case 'updatepasswordperfil':
                            echo $this->render('../cuenta/updatepasswordperfil', ['model' => $model]);
                            break;


                    }
                }
                ?>
                <!--/container-main-->


            </div><!--//app-wrapper-->

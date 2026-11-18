<?php
use yii\helpers\Url;
$user = Yii::$app->session->get('user');

?>

<div class="col-12 col-sm-12 mspeo" style="">
<div class="item border-bottom py-3 app-card">
    <div class=" text-left">
        <div class="col-auto" style="padding: 10px 0px;">
            <div class="item-label"><strong><?php echo $label ?></strong></div>
            <div class="item-data"><?php echo $datos ?></div>
        </div><!--//col-->

    </div><!--//row-->
</div><!--//item-->
</div>